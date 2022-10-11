@php 
$command = \LaraDumps\LaraDumps\Actions\ExportConfigToCommand::handle();
@endphp
<div>
  <script src="https://cdn.jsdelivr.net/npm/clipboard@2.0.10/dist/clipboard.min.js"></script>
  <script>
    let clipboard = new ClipboardJS('.copyBtn');

    clipboard.on('success', (e) => {
      let text = e.trigger.innerHTML;
      e.trigger.innerHTML = 'Copied!';
      setTimeout(() => e.trigger.innerHTML = text, 1500);
    });

  </script>
  <div class="block mt-2 mb-2 text-sm font-medium text-gray-900 dark:text-gray-400">
    <p>To re-use your configuration in a Laravel setup script, just copy the command below:</p>
    <br />
    <textarea readonly id="config" rows="5" class="block p-2.5 w-full text-sm text-gray-900 bg-yellow-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">{{ $command }}</textarea>
    <div class="text-right mt-3">
      <button data-clipboard-target="#config" id="copyConfig" class="copyBtn text-white bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-800">Copy to clipboard</button>
    </div>
  </div>
  <hr>
  <div class="block mt-4 mb-2 text-sm font-medium text-gray-900 dark:text-gray-400">
    <p>You can install and configure LaraDumps with a single command.</p>
    <br/>
    <p>Just add the following line to your <em>.bashrc</em> file to create a bash alias called <em>laradumps</em>:</p>
    <br/>
    <textarea readonly id="alias" rows="6" class="block p-2.5 w-full text-sm text-gray-900 bg-yellow-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">alias laradumps="composer require --dev laradumps/laradumps && {{ $command }}"</textarea>
    <div class="text-right mt-3">
      <button data-clipboard-target="#alias" id="copyConfig" class="copyBtn text-white bg-gray-700 hover:bg-gray-800 focus:ring-4 focus:outline-none focus:ring-gray-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-gray-600 dark:hover:bg-gray-700 dark:focus:ring-gray-800">Copy to clipboard</button>
    </div>
  </div>
</div>