<div>
  <script>
    window.onload=()=>{
      // Send uncheck toggles as hidden inputs
      const form = document.getElementById('form');
      const toggles = [...document.querySelectorAll(".toggle-checkbox")];
            
      form.addEventListener('submit', (e) => {
        e.preventDefault();

        toggles.forEach((toggle) => {
          if (toggle.checked === false) {
            form.append(Object.assign(document.createElement('input'),{type: 'hidden', name: toggle.name, value: '0'}));
          }
        });
        
        form.submit();
      });
    };
  </script>
  <style>
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
        <input @checked($configKey['current_value']) value="1" type="checkbox" name="{{ $configKey['env_key'] }}" id="{{ $configKey['env_key'] }}" class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer"/>
        <label for="{{ $configKey['env_key'] }}" class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer"></label>
    </div>
</div>
