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
  <body class="mx-auto max-w-xl px-6 mt-2 bg-gradient-to-r from-slate-400 via-gray-300 to-gray-400">
    <x-laradumps::success />
    <div x-data="{ openTab: 'general' }" class="p-6">
      <ul class="hidden text-sm font-medium text-center text-gray-500 rounded-lg divide-x divide-gray-200 shadow sm:flex dark:divide-gray-700 dark:text-gray-400 mb-3">
        <li class="w-full">
          <a @click="openTab = 'general'" x-bind:class="{ 'bg-gray-200': openTab === 'general' }" href="#" class="inline-block p-4 w-full bg-white hover:text-gray-700 bg-white text-gray-900  rounded-l-lg   active focus:outline-none dark:bg-gray-700 dark:text-white dark:bg-gray-800 dark:hover:bg-gray-700" aria-current="page">General</a>
        </li>
        <li class="w-full">
          <a @click="openTab = 'livewire'" x-bind:class="{ 'bg-gray-200': openTab === 'livewire' }" href="#" class="inline-block p-4 w-full bg-white hover:text-gray-700 hover:bg-gray-50   focus:outline-none dark:hover:text-white dark:bg-gray-800 dark:hover:bg-gray-700">Livewire</a>
        </li>
        <li class="w-full">
          <a @click="openTab = 'export'" x-bind:class="{ 'bg-gray-200': openTab === 'export' }" href="#" class="inline-block p-4 w-full bg-white rounded-r-lg hover:text-gray-700 hover:bg-gray-50  focus:outline-none  dark:hover:text-white dark:bg-gray-800 dark:hover:bg-gray-700">Export</a>
        </li>
      </ul>
      <div class="p-3 bg-slate-50 mb-3 rounded-md shadow-lg text-slate-700 border border-slate-400">
        <h1 class="text-lg">LaraDumps Config for {{ config('app.name') }}</h1>
        <form name="form" id="form" method="POST" action="{{ route('laradumps.store') }}"> 
          @csrf 
          <div x-show="openTab === 'general'">
            <div class="p-2 text-slate-500 text-left mt-6 mb-4 text-sm">
              <p>In this section you can configure and adjust LaraDumps settings.</p>
              <p>All configuration is stored in <a href="{{ $configFile }}" class="relative leading-7 text-yellow-700 cursor-pointer" rel="nofollow" target="_blank">config/laradumps.php</a>.</p>
            </div>
            <hr>
              <x-laradumps::settings :configKeys=$configKeys tab='general'/>
          </div>
          <div x-show="openTab === 'livewire'">
            <div class="p-2 text-slate-500 text-left mt-6 mb-4 text-sm">
              <p>In this section you can configure and adjust how LaraDumps debug Livewire Components.</p>
              <p>All configuration is stored in <a href="{{ $configFile }}" class="relative leading-7 text-yellow-700 cursor-pointer" rel="nofollow" target="_blank">config/laradumps.php</a>.</p>
            </div>
            <hr>
            <x-laradumps::settings :configKeys=$configKeys tab='livewire'/>
          </div>
        </form>
        <div x-show="openTab === 'export'">
          <div class="p-4 text-slate-500 text-left mt-6 mb-6">
            <x-laradumps::export />
          </div>
        </div>

          <br/>
          <hr class="my-2 border-gray-300">
          <div class="flex flex-wrap items-center md:justify-between justify-center">
            <div class="w-full px-8 mx-auto text-center text-xs text-gray-400">
              <p>
                ⭐ Support LaraDumps, star our <a href="https://github.com/laradumps/laradumps" class="relative leading-7 text-yellow-700 cursor-pointer" rel="nofollow" target="_blank">repository</a>.
              </p>
            </div>
          </div>
        <!--end of pannel -->
      </div>
    </div>
    <script src="//unpkg.com/alpinejs" defer></script>
  </body>
</html>