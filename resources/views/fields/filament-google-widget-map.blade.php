<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div
        x-ignore
        ax-load
        ax-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('filament-google-maps-widget', 'cheesegrits/filament-google-maps') }}"
        x-data="filamentGoogleMapsWidget({
                    cachedData: {{ json_encode($getMarkers()) }},
                    config: {{ $getMapConfig() }},
                    mapEl: $refs.map,
                })"
        id="{{ $getId().'-alpine' }}"
        wire:ignore
    >
        @if ($isSearchBoxControlEnabled())
            <input x-ref="pacinput" type="text" placeholder="Search Box" />
        @endif

        <div
            x-ref="map"
            class="w-full"
            style="
                height: {{ $getHeight() }};
                min-height: 30vh;
                z-index: 1 !important;
            "
        ></div>
    </div>
</x-dynamic-component>
