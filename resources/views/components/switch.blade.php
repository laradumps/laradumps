<div>
  <style>
    /* CHECKBOX TOGGLE SWITCH */
    /* @apply rules for documentation, these do not work as inline style */
    .toggle-checkbox:checked {
      @apply: right-0 border-yellow-400;
      right: 0;
      border-color: #68D391;
    }
    .toggle-checkbox:checked + .toggle-label {
      @apply: bg-yellow-400;
      background-color: #68D391;
    }
    </style>
    <div class="relative inline-block w-10 mr-2 align-middle select-none transition duration-200 ease-in">
      <input type="hidden" name="{{ $configKey['env_key'] }}" id="{{ $configKey['env_key'] }}" value="0">
        <input @checked($configKey['current_value']) value="1" type="checkbox" name="{{ $configKey['env_key'] }}" id="{{ $configKey['env_key'] }}" class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer"/>
        <label for="toggle" class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer"></label>
    </div>
</div>