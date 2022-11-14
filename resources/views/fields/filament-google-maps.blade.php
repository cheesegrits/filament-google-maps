<x-forms::field-wrapper
        :id="$getId()"
        :label="$getLabel()"
        :label-sr-only="$isLabelHidden()"
        :helper-text="$getHelperText()"
        :hint="$getHint()"
        :required="$isRequired()"
        :state-path="$getStatePath()"
>
    <div
        x-data="{

            location: $wire.entangle('{{ $getStatePath() }}'),
            fgm: {},
        }"

        id="{{  $getId() . '-alpine' }}"

        x-init="
            (async () => {
                @if($mapsHasCss())
                    if(!document.getElementById('filament-google-maps-css')){
                        const link  = document.createElement('link');
                        link.id   = 'filament-google-maps-css';
                        link.rel  = 'stylesheet';
                        link.type = 'text/css';
                        link.href = '{{ $mapsCssUrl() }}';
                        link.media = 'all';
                        document.head.appendChild(link);
                    }
                @endif
                @if($mapsHasJs())
                    if(!document.getElementById('filament-google-maps-js')){
                        const script = document.createElement('script');
                        script.id   = 'filament-google-maps-js';
                        script.src = '{{ $mapsJsUrl() }}';
                        document.head.appendChild(script);
                    }
                 @endif

                do {
                    await (new Promise(resolve => setTimeout(resolve, 100)));
                } while (window.filamentGoogleMaps === undefined);
                fgm = filamentGoogleMaps($wire, {{ $getMapConfig()}});
                fgm.init($refs.map, $refs.pacinput);
            })()

            $watch('location', value => fgm.updateMapFromAlpine())
        "
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
</x-forms::field-wrapper>
