@php
    $isLocation            = $getIsLocation();
    $datalistOptions       = [];
    $extraAlpineAttributes = $getExtraAlpineAttributes();
    $id                    = $getIsLocation() ? $getId() . '-fgm-address' : $getId();
    $isConcealed           = $isConcealed();
    $isDisabled            = $isDisabled();
    $isPrefixInline        = $isPrefixInline();
    $isSuffixInline        = $isSuffixInline();
    $statePath             = $getStatePath();
    $prefixActions         = $getPrefixActions();
    $prefixIcon            = $getPrefixIcon();
    $prefixLabel           = $getPrefixLabel();
    $suffixActions         = $getSuffixActions();
    $suffixIcon            = $getSuffixIcon();
    $suffixLabel           = $getSuffixLabel();
    $mask                  = null;
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <x-filament::input.wrapper
        :disabled="$isDisabled"
        :inline-prefix="$isPrefixInline"
        :inline-suffix="$isSuffixInline"
        :prefix="$prefixLabel"
        :prefix-actions="$prefixActions"
        :prefix-icon="$prefixIcon"
        :suffix="$suffixLabel"
        :suffix-actions="$suffixActions"
        :suffix-icon="$suffixIcon"
        :valid="! $errors->has($statePath)"
        class="fi-fo-text-input"
        :attributes="
            \Filament\Support\prepare_inherited_attributes($getExtraAttributeBag())
                ->class(['overflow-hidden'])
        "
    >

{{--        <script>--}}
{{--            (g=>{var h,a,k,p="The Google Maps JavaScript API",c="google",l="importLibrary",q="__ib__",m=document,b=window;b=b[c]||(b[c]={});var d=b.maps||(b.maps={}),r=new Set,e=new URLSearchParams,u=()=>h||(h=new Promise(async(f,n)=>{await (a=m.createElement("script"));e.set("libraries",[...r]+"");for(k in g)e.set(k.replace(/[A-Z]/g,t=>"_"+t[0].toLowerCase()),g[k]);e.set("callback",c+".maps."+q);a.src=`https://maps.${c}apis.com/maps/api/js?`+e;d[q]=f;a.onerror=()=>h=n(Error(p+" could not load."));a.nonce=m.querySelector("script[nonce]")?.nonce||"";m.head.append(a)}));d[l]?console.warn(p+" only loads once. Ignoring:",g):d[l]=(f,...n)=>r.add(f)&&u().then(()=>d[l](f,...n))})({--}}
{{--                key: "{{ \Cheesegrits\FilamentGoogleMaps\Helpers\MapsHelper::mapsKey() }}",--}}
{{--                v: "weekly",--}}
{{--            });--}}
{{--        </script>--}}
        
        <div
            class="w-full"
            x-ignore
            x-load
            x-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('filament-google-maps-geocomplete', 'cheesegrits/filament-google-maps') }}"
            x-data="filamentGoogleGeocomplete({
                        setStateUsing: async (path, state) => {
                            return await $wire.set(path, state)
                        },
                        reverseGeocodeUsing: (results) => {
                            $wire.reverseGeocodeUsing(@js($statePath), results)
                        },
                        filterName: @js($getFilterName()),
                        statePath: @js($getStatePath()),
                        isLocation: @js($getIsLocation()),
                        reverseGeocodeFields: @js($getReverseGeocode()),
                        hasReverseGeocodeUsing: @js($getReverseGeocodeUsing()),
                        latLngFields: @js($getUpdateLatLngFields()),
                        types: @js($getTypes()),
                        placeField: @js($getPlaceField()),
                        countries: @js($getCountries()),
                        debug: @js($getDebug()),
                        apiKey: @js(\Cheesegrits\FilamentGoogleMaps\Helpers\MapsHelper::mapsKey()),
                        minChars: @js($getMinChars()),
                        fieldId: @js($id),
                    })"
            wire:ignore
        >
            {{-- <input --}}
            {{-- x-bind:class="{ --}}
            {{-- 'border-gray-300 dark:border-gray-600': ! (@js($statePath) in $wire.__instance.serverMemo.errors), --}}
            {{-- 'border-danger-600 ring-danger-600': (@js($statePath) in $wire.__instance.serverMemo.errors), --}}
            {{-- }" --}}
            {{-- {{ --}}
            {{-- $getExtraInputAttributeBag() --}}
            {{-- ->merge($getExtraAlpineAttributes(), escape: false) --}}
            {{-- ->merge([ --}}
            {{-- 'autocapitalize' => $getAutocapitalize(), --}}
            {{-- 'autocomplete' => $getAutocomplete(), --}}
            {{-- 'autofocus' => $isAutofocused(), --}}
            {{-- 'disabled' => $isDisabled, --}}
            {{-- 'dusk' => "filament.forms.{$statePath}", --}}
            {{-- 'id' => $id, --}}
            {{-- 'inputmode' => $getInputMode(), --}}
            {{-- 'list' => null, --}}
            {{-- 'max' => null, --}}
            {{-- 'maxlength' => null, --}}
            {{-- 'min' => null, --}}
            {{-- 'minlength' => null, --}}
            {{-- 'placeholder' => $getPlaceholder(), --}}
            {{-- 'readonly' => $isReadOnly(), --}}
            {{-- 'required' => $isRequired() && (! $isConcealed), --}}
            {{-- 'type' => 'text', --}}
            {{-- $applyStateBindingModifiers('wire:model') => (! $isLocation) ? $statePath : null, --}}
            {{-- ], escape: false) --}}
            {{-- ->class([ --}}
            {{-- 'filament-forms-input block w-full transition duration-75 shadow-sm outline-none sm:text-sm focus:relative focus:z-[1] focus:ring-1 focus:ring-inset disabled:opacity-70 dark:bg-gray-700 dark:text-white', --}}
            {{-- 'rounded-s-lg' => ! ($prefixLabel || $prefixIcon), --}}
            {{-- 'rounded-e-lg' => ! ($suffixLabel || $suffixIcon), --}}
            {{-- ]) --}}
            {{-- }} --}}
            {{-- /> --}}
            <x-filament::input
                :attributes="
                    \Filament\Support\prepare_inherited_attributes($getExtraInputAttributeBag())
                        ->merge($extraAlpineAttributes, escape: false)
                        ->merge([
                            'autocapitalize'                                                        => $getAutocapitalize(),
                            'autocomplete'                                                          => $getAutocomplete(),
                            'autofocus'                                                             => $isAutofocused(),
                            'disabled'                                                              => $isDisabled,
                            'id'                                                                    => $id,
                            'inlinePrefix'                                                          => $isPrefixInline && (count($prefixActions) || $prefixIcon || filled($prefixLabel)),
                            'inlineSuffix'                                                          => $isSuffixInline && (count($suffixActions) || $suffixIcon || filled($suffixLabel)),
                            'inputmode'                                                             => $getInputMode(),
                            'list'                                                                  => $datalistOptions ? $id . '-list' : null,
                            'max'                                                                   => null,
                            'maxlength'                                                             => null,
                            'min'                                                                   => null,
                            'minlength'                                                             => null,
                            'placeholder'                                                           => $getPlaceholder(),
                            'readonly'                                                              => $isReadOnly(),
                            'required'                                                              => $isRequired() && (! $isConcealed),
                            'step'                                                                  => null,
                            'type'                                                                  => 'text',
                            $applyStateBindingModifiers('wire:model')                               => (! $isLocation) ? $statePath : null,
                            'x-data'                                                                => (count($extraAlpineAttributes) || filled($mask)) ? '{}' : null,
                            'x-mask' . ($mask instanceof \Filament\Support\RawJs ? ':dynamic' : '') => filled($mask) ? $mask : null,
                            'value'                                                                 => $isLocation ? $getFormattedState() : null,
                        ], escape: false)
                "
            />

            @if ($getIsLocation())
                <input
                    {{ $applyStateBindingModifiers('wire:model') }}="{{ $getStatePath() }}"
                    type="hidden"
                    id="{{ $getId() }}"
                />
            @endif
        </div>
    </x-filament::input.wrapper>
</x-dynamic-component>
