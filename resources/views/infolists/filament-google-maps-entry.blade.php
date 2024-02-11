<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    @php
        $statePath = $getStatePath();
    @endphp

    <div
        x-ignore
        ax-load
        ax-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('filament-google-maps-entry', 'cheesegrits/filament-google-maps') }}"
        x-data="filamentGoogleMapsField({
                    state: @js($getState()),
                    defaultLocation: @js($getDefaultLocation()),
                    controls: @js($getMapControls(false)),
                    layers: @js($getLayers()),
                    defaultZoom: @js($getDefaultZoom()),
                    drawingField: @js($getDrawingField()),
                    geoJson: @js($getGeoJsonFile()),
                    geoJsonVisible: @js($getGeoJsonVisible()),
                    gmaps: @js($getMapsUrl()),
                    mapEl: $refs.map,
                })"
        id="{{ $getId() . '-alpine' }}"
        wire:ignore
    >
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
