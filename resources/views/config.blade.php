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
    <body class="antialiased px-10 py-8 bg-gray-50">
        <div class="p-3 bg-gray-50 mb-3 rounded-md shadow-md text-gray-700 border border-gray-400">
          <h1>LaraDumps Config</h1>

          For other configurations open the file 
          <a href="{{ $configFile }}" 
          class="relative leading-7 text-yellow-700 cursor-pointer"
          style='text-decoration: underline; transition: all 0.3s ease 0s; word-break: break-word; list-style: outside;''        
              rel="nofollow" 
              target="_blank" >config/laradumps.php</a>!
              <form method="POST" action="{{  route('laradumps.store') }}">
                @csrf
             
               <!-- Privacy section -->
               <div class="divide-y divide-gray-200 pt-3">
                <div class="px-4 sm:px-3">
                  @foreach ($configKeys as $configKey)
                  <ul role="list" class="mt-2 divide-y divide-gray-200">
                    <li class="flex items-center justify-between py-4">
                      <div class="flex flex-col">
                        <p class="text-sm font-medium text-gray-900" id="privacy-option-1-label">{{ $configKey['title'] }}
                          <a href="{{ $configKey['doc_link'] }}" 
                          class="text-xs relative leading-7 text-yellow-700 cursor-pointer" rel="nofollow" target="_blank" >[↗]</a>
                        </p>
                        <p class="text-sm text-gray-500" id="privacy-option-1-description">{{ $configKey['description'] }}</p>
                      </div>

         
                        @if($configKey['type'] == 'select')
                        <select name="{{ $configKey['env_key'] }}" id="{{ $configKey['env_key'] }}">

                          @foreach ($configKey['options'] as $option => $label)

                          <option value="{{ $option }}" {{ $option  ==  $configKey['current_value'] ? 'selected' : '' }}> {{  $label }} </option>

                          @endforeach
                        </select>
                        @endif

                      @if($configKey['type'] == 'toggle')
                      <x-laradumps::switch :configKey=$configKey />

                      @endif
                    </li>
                    @endforeach

                  </ul>
                </div>
                <div class="mt-4 flex justify-end py-4 px-4 sm:px-6">
                  <button type="submit" class="ml-5 inline-flex justify-center rounded-md border border-transparent bg-yellow-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-yellow-800 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2">Save</button>
                </div>
              </div>

              <p class="leading-loose">
  
                ⭐ Enjoying LaraDumps? Star our 
                <a href="https://github.com/Power-Components/livewire-powergrid" 
                class="relative leading-7 text-yellow-700 cursor-pointer"
                style='text-decoration: underline; transition: all 0.3s ease 0s; word-break: break-word; list-style: outside;''        
                    rel="nofollow" 
                    target="_blank" >Repository</a>!
                </p>
        </div>
        <script src="//unpkg.com/alpinejs" defer></script>
        <script>
            window.addEventListener('showAlert', event => {
                alert(event.detail.message);
            })
        </script>
    </body>
</html>
