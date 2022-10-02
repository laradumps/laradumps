<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>LaraDumps Config</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Styles -->
    <style>
        body {
            font-family: 'Nunito', sans-serif;
        }
    </style>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="mx-auto max-w-xl px-6 mt-2">
<div class="p-3 bg-slate-50 mb-3 rounded-md shadow-lg text-slate-700 border border-slate-400">
    <h1 class="text-lg">LaraDumps Config</h1>
    <span class="text-slate-500">For other configurations open the file</span>
    <a href="{{ $configFile }}"
       class="relative leading-7 text-yellow-700 cursor-pointer"
       rel="nofollow" target="_blank">config/laradumps.php</a>!

    @if(Session::get('success'))
        <div class="rounded-md bg-green-50 p-4 mt-2">
            <div class="flex">
                <div class="flex-shrink-0">
                    <!-- Heroicon name: mini/check-circle -->
                    <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800">Successfully uploaded</p>
                </div>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('laradumps.store') }}">
        @csrf
        <div>
            <div class="px-4 sm:px-3">
                <ul role="list" class="divide-y divide-slate-200">
                    @foreach ($configKeys as $configKey)
                        <li class="flex items-center justify-between py-4">
                            <div class="flex flex-col">
                                <p class="text-base font-medium text-slate-900"
                                   id="privacy-option-1-label">{{ $configKey['title'] }}
                                    <a href="{{ $configKey['doc_link'] }}"
                                       class="text-xs relative leading-7 text-yellow-700 cursor-pointer" rel="nofollow"
                                       target="_blank">[↗]</a>
                                </p>
                                <p class="text-sm text-slate-500"
                                   id="privacy-option-1-description">{{ $configKey['description'] }}</p>
                            </div>

                            @if($configKey['type'] == 'select')
                                <select name="{{ $configKey['env_key'] }}" id="{{ $configKey['env_key'] }}"
                                        class="appearance-none block p-2 mb-6 w-auto text-sm text-slate-900 bg-slate-50 rounded-lg border border-slate-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-700 dark:border-slate-600 dark:placeholder-slate-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">

                                    @foreach ($configKey['options'] as $option => $label)
                                        <option value="{{ $option }}" {{ $option  ==  $configKey['current_value'] ? 'selected' : '' }}> {{  $label }} </option>
                                    @endforeach
                                </select>
                            @endif

                            @if($configKey['type'] == 'toggle')
                                <x-laradumps::switch :configKey=$configKey/>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="text-right mt-3">
                <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                    Save
                </button>
            </div>
        </div>

        <p class="leading-loose">
            ⭐ Enjoying LaraDumps? Star our
            <a href="https://github.com/Power-Components/livewire-powergrid" class="relative leading-7 text-yellow-700 cursor-pointer"
               rel="nofollow"
               target="_blank" >Repository</a>!
        </p>
    </form>
</div>
<script src="//unpkg.com/alpinejs" defer></script>
<script>
    window.addEventListener('showAlert', event => {
        alert(event.detail.message);
    })
</script>
</body>
</html>
