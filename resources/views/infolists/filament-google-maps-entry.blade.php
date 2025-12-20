<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    @php
        $statePath = $getStatePath();
    @endphp

    <script>
        (g=>{var h,a,k,p="The Google Maps JavaScript API",c="google",l="importLibrary",q="__ib__",m=document,b=window;b=b[c]||(b[c]={});var d=b.maps||(b.maps={}),r=new Set,e=new URLSearchParams,u=()=>h||(h=new Promise(async(f,n)=>{await (a=m.createElement("script"));e.set("libraries",[...r]+"");for(k in g)e.set(k.replace(/[A-Z]/g,t=>"_"+t[0].toLowerCase()),g[k]);e.set("callback",c+".maps."+q);a.src=`https://maps.${c}apis.com/maps/api/js?`+e;d[q]=f;a.onerror=()=>h=n(Error(p+" could not load."));a.nonce=m.querySelector("script[nonce]")?.nonce||"";m.head.append(a)}));d[l]?console.warn(p+" only loads once. Ignoring:",g):d[l]=(f,...n)=>r.add(f)&&u().then(()=>d[l](f,...n))})({
            key: "{{ \Cheesegrits\FilamentGoogleMaps\Helpers\MapsHelper::mapsKey() }}",
            v: "weekly",
        });
    </script>

    <div
        x-ignore
        x-load
        x-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('filament-google-maps-entry', 'cheesegrits/filament-google-maps') }}"
        x-data="filamentGoogleMapsField({
                    state: @js($getState()),
                    statePath: @js($getStatePath()),
                    defaultLocation: @js($getDefaultLocation()),
                    controls: @js($getMapControls(false)),
                    kmlLayers: @js($getLayers()),
                    defaultZoom: @js($getDefaultZoom()),
                    geoJson: @js($getGeoJsonFile()),
                    geoJsonVisible: @js($getGeoJsonVisible()),
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
