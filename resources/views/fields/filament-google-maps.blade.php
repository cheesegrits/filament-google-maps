<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div
        x-ignore
        ax-load
        ax-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('filament-google-maps-field', 'cheesegrits/filament-google-maps') }}"
        x-data="filamentGoogleMapsField({
                state: $wire.entangle('{{ $getStatePath() }}'),
                setStateUsing: (path, state) => {
                    return $wire.set(path, state)
                },
                getStateUsing: (path) => {
                    return $wire.get(path)
                },
                autocomplete: @js($getAutocompleteId()),
                autocompleteReverse: @js($getAutocompleteReverse()),
                geolocate: @js($getGeolocate()),
                geolocateLabel: @js($getGeolocateLabel()),
                draggable: @js($getDraggable()),
                clickable: @js($getClickable()),
                defaultLocation: @js($getDefaultLocation()),
                statePath: @js($getStatePath()),
                controls: @js($getMapControls()),
                layers: @js($getLayers()),
                reverseGeocodeFields: @js($getReverseGeocode()),
                defaultZoom: @js($getDefaultZoom()),
                debug: @js($getDebug()),
                gmaps: @js($getMapsUrl()),
                mapEl: $refs.map,
                pacEl: $refs.pacinput,
            })"
        id="{{  $getId() . '-alpine' }}"
        wire:ignore
    >
        @if($isSearchBoxControlEnabled())
            <input x-ref="pacinput" type="text" placeholder="Search Box"/>
        @endif

        <div
            x-ref="map"
            class="w-full" style="height: {{ $getHeight() }}; min-height: 30vh; z-index: 1 !important;">
        </div>
    </div>
</x-dynamic-component>
