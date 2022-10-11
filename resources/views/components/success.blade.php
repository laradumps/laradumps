@if(Session::get('success'))
    <div x-data="{ show: true }" x-show="show" 
        x-init="setTimeout(() => show = false, 5000)" 
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-90"
        class="rounded-md bg-green-50 p-2 m-2">
        <div class="flex">
            <div class="flex-shrink-0">
                <!-- Heroicon name: mini/check-circle -->
                <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-green-800">{{ Session::get('success') }}</p>
            </div>
        </div>
    </div>
@endif