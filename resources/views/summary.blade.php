<div class="mx-1">
    @if(!$error)
        No ds() found.
    @endif
    <div class="flex space-x-1">
        <span class="flex-1 content-repeat-[─] text-gray"></span>
    </div>
    <div>
        <span>
            <div class="flex space-x-2 mx-1 mb-1">
                @if($error)
                    <span class="p-2 bg-red text-white">
                        [ERROR] Found {{ $total.' '. \Illuminate\Support\Str::of('error')->plural($total) }} / {{ $totalFiles }} {{ \Illuminate\Support\Str::of('file')->plural($totalFiles) }}
                    </span>
                @else
                    <span class="px-2 bg-green text-white uppercase font-bold">
                        ✓ SUCCESS
                    </span>
                @endif
            </div>
        </span>
    </div>
</div>
