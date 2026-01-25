<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div
        x-ignore
        x-load
        x-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('filament-google-maps-widget', 'cheesegrits/filament-google-maps') }}"
        x-data="filamentGoogleMapsWidget({
                    apiKey: @js(\Cheesegrits\FilamentGoogleMaps\Helpers\MapsHelper::mapsKey()),
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
            id="map-{{ $getMapId() }}"
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
