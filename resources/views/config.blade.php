<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
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
  <body class="mx-auto max-w-xl px-6 mt-2 bg-gradient-to-r from-slate-400 via-gray-300 to-gray-400" >
    <x-laradumps::success/>
    <div class="p-3 bg-slate-50 mb-3 rounded-md shadow-lg text-slate-700 border border-slate-400">
      <h1 class="text-lg">LaraDumps Config for {{ config('app.name') }}</h1>

      <div class="p-4 text-slate-500 text-left mt-2">
        <p>Configure general LaraDumps settings for this project.</p>
        <br/>
        <p> All the configuration settings are stored in <a href="{{ $configFile }}" class="relative leading-7 text-yellow-700 cursor-pointer" rel="nofollow" target="_blank">config/laradumps.php</a>. </p>
      </div>

      <form method="POST" action="{{ route('laradumps.store') }}"> @csrf <div>
          <div class="px-4 sm:px-3">
            <ul role="list" class="divide-y divide-slate-200"> @foreach ($configKeys as $configKey) <li class="flex items-center justify-between py-4">
                <div class="flex flex-col">
                  <p class="text-base font-medium text-slate-900" id="privacy-option-1-label">{{ $configKey['title'] }}
                    <a href="{{ $configKey['doc_link'] }}" class="text-xs relative leading-7 text-yellow-700 cursor-pointer" rel="nofollow" target="_blank">[↗]</a>
                  </p>
                  <p class="text-sm text-slate-500" id="privacy-option-1-description">{{ $configKey['description'] }}</p>
                </div> @if($configKey['type'] == 'text') <input type="text" required="required" name="{{ $configKey['env_key'] }}" id="{{ $configKey['env_key'] }}" value="{{ $configKey['current_value'] }}" class="appearance-none block p-2 mb-6 w-auto text-sm text-slate-900 bg-slate-50 rounded-lg border border-slate-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-700 dark:border-slate-600 dark:placeholder-slate-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" /> @endif @if($configKey['type'] == 'select') <select name="{{ $configKey['env_key'] }}" id="{{ $configKey['env_key'] }}" class="appearance-none block p-2 mb-6 w-auto text-sm text-slate-900 bg-slate-50 rounded-lg border border-slate-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-700 dark:border-slate-600 dark:placeholder-slate-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"> @foreach ($configKey['options'] as $option => $label) <option value="{{ $option }}" {{ $option  ==  $configKey['current_value'] ? 'selected' : '' }}> {{ $label }} </option> @endforeach </select> @endif @if($configKey['type'] == 'toggle')
                <x-laradumps::switch :configKey=$configKey /> @endif
              </li> @endforeach </ul>
          </div>
          <div class="text-right mt-3">
            <button type="submit" class="text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-green-800"> Save & Apply </button>
          </div>
        </div>
        <div class="text-left text-sm ml-3 mt-5">
          <p> ⭐ Support LaraDumps, star our <a href="https://github.com/laradumps/laradumps" class="relative leading-7 text-yellow-700 cursor-pointer" rel="nofollow" target="_blank">GitHub repository</a>! </p>
        </div>
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