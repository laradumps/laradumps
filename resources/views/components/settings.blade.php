@php
if (isset($tab)) {
  $configKeys = $configKeys->filter(function ($config) use($tab) {
   return $config['tab'] === $tab;
  });
}
@endphp
<div class="px-4 sm:px-3">
  <ul role="list" class="divide-y divide-slate-200"> @foreach ($configKeys as $configKey) 
    <li class="flex items-center justify-between py-4">
      <div class="flex flex-col">
        <p class="text-base font-medium text-slate-900" id="privacy-option-1-label">{{ $configKey['title'] }}
          <a href="{{ $configKey['doc_link'] }}" class="text-xs relative leading-7 text-yellow-700 cursor-pointer" rel="nofollow" target="_blank">
            &#9032;
          </a>
        </p>
        <p class="text-sm text-slate-500" id="privacy-option-1-description">{{ $configKey['description'] }}</p>
      </div>
          @if($configKey['type'] == 'text') 
          <input type="text" 
              required="required" 
                name="{{ $configKey['env_key'] }}" id="{{ $configKey['env_key'] }}" 
                value="{{ $configKey['current_value'] }}"
                class="appearance-none block p-2 mb-6 w-auto text-sm text-slate-900 bg-slate-50 rounded-lg border border-slate-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-700 dark:border-slate-600 dark:placeholder-slate-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"/> 
          @endif

          @if($configKey['type'] == 'select') 
            <select name="{{ $configKey['env_key'] }}" 
                    id="{{ $configKey['env_key'] }}"
                    class="appearance-none block p-2 mb-6 w-auto text-sm text-slate-900 bg-slate-50 rounded-lg border border-slate-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-700 dark:border-slate-600 dark:placeholder-slate-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                @foreach ($configKey['options'] as $option => $label)
                    <option value="{{ $option }}" {{ $option  ==  $configKey['current_value'] ? 'selected' : '' }}> {{ $label }} </option>
                @endforeach
            </select>
          @endif

          @if($configKey['type'] == 'toggle')
            <x-laradumps::switch :configKey=$configKey />
          @endif
    </li> 
    @endforeach 
  </ul>
  <hr>

  <div class="text-right mt-3">
    <button type="submit" class="text-white bg-green-700 hover:bg-green-800 focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-green-600 dark:hover:bg-green-700 dark:focus:ring-green-800"> Save & Apply </button>
  </div>
</div>
