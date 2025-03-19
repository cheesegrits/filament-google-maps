<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    @php
        $statePath = $getStatePath();
    @endphp

    <div
        x-ignore
        x-load-css="[@js(\Filament\Support\Facades\FilamentAsset::getStyleHref('filament-google-maps', 'cheesegrits/filament-google-maps'))]"
        ax-load
        ax-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('filament-google-maps-field', 'cheesegrits/filament-google-maps') }}"
        x-data="filamentGoogleMapsField({
                    state: $wire.{{ $applyStateBindingModifiers("\$entangle('{$getStatePath()}')") }},
                    setStateUsing: (path, state) => {
                        return $wire.set(path, state)
                    },
                    getStateUsing: (path) => {
                        return $wire.get(path)
                    },
                    reverseGeocodeUsing: (results) => {
                        $wire.reverseGeocodeUsing(@js($statePath), results)
                    },
                    placeUpdatedUsing: (results) => {
                        $wire.placeUpdatedUsing(@js($statePath), results)
                    },
                    autocomplete: @js($getAutocompleteId()),
                    autocompleteReverse: @js($getAutocompleteReverse()),
                    geolocate: @js($getGeolocate()),
                    geolocateOnLoad: @js($getGeolocateOnLoad()),
                    geolocateLabel: @js($getGeolocateLabel()),
                    geolocatePosition: @js($getGeolocatePosition()),
                    draggable: @js($getDraggable()),
                    clickable: @js($getClickable()),
                    defaultLocation: @js($getDefaultLocation()),
                    statePath: @js($getStatePath()),
                    controls: @js($getMapControls(false)),
                    layers: @js($getLayers()),
                    reverseGeocodeFields: @js($getReverseGeocode()),
                    hasReverseGeocodeUsing: @js($getReverseGeocodeUsing()),
                    hasPlaceUpdatedUsing: @js($getPlaceUpdatedUsing()),
                    defaultZoom: @js($getDefaultZoom()),
                    types: @js($getTypes()),
                    countries: @js($getCountries()),
                    placeField: @js($getPlaceField()),
                    drawingControl: @js($getDrawingControl()),
                    drawingControlPosition: @js($getDrawingControlPosition()),
                    drawingModes: @js($getDrawingModes()),
                    drawingField: @js($getDrawingField()),
                    geoJson: @js($getGeoJsonFile()),
                    geoJsonField: @js($getGeoJsonField()),
                    geoJsonProperty: @js($getGeoJsonProperty()),
                    geoJsonVisible: @js($getGeoJsonVisible()),
                    debug: @js($getDebug()),
                    gmaps: @js($getMapsUrl()),
                    mapEl: $refs.map,
                    pacEl: $refs.pacinput,
                    polyOptions: @js($getPolyOptions()),
                    circleOptions: @js($getCircleOptions()),
                    rectangleOptions: @js($getRectangleOptions()),
                    mapType: @js($getType()),
                })"
        id="{{ $getId() . '-alpine' }}"
        wire:ignore
    >
        @if ($isSearchBoxControlEnabled())
            <input
                class="modern-look"
                x-ref="pacinput"
                type="text"
                placeholder="{{ __('filament-google-maps::fgm.map.search_placeholder') }}"
            />
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
