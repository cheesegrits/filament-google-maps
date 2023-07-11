@php
    $isLocation = $getIsLocation();
	$id = $getIsLocation() ? $getId() . '-fgm-address' : $getId();
    $isConcealed = $isConcealed();
    $statePath = $getStatePath();
    $prefixIcon = $getPrefixIcon();
    $prefixLabel = $getPrefixLabel();
    $suffixIcon = $getSuffixIcon();
    $suffixLabel = $getSuffixLabel();
@endphp

<x-dynamic-component
        :component="$getFieldWrapperView()"
        :field="$field"
>
    <x-filament-forms::affixes
            :state-path="$statePath"
            :prefix="$prefixLabel"
            :prefix-actions="$getPrefixActions()"
            :prefix-icon="$prefixIcon"
            :suffix="$suffixLabel"
            :suffix-actions="$getSuffixActions()"
            :suffix-icon="$suffixIcon"
            class="filament-forms-text-input-component"
            :attributes="\Filament\Support\prepare_inherited_attributes($getExtraAttributeBag())"
    >
        <div class="w-full"
             x-ignore
             ax-load
             ax-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('filament-google-maps-geocomplete', 'cheesegrits/filament-google-maps') }}"
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
                debug: @js($getDebug()),
                gmaps: @js($getMapsUrl()),
             })"
             wire:ignore
        >
            <input
                    x-bind:class="{
                    'border-gray-300 dark:border-gray-600': ! (@js($statePath) in $wire.__instance.serverMemo.errors),
                    'border-danger-600 ring-danger-600': (@js($statePath) in $wire.__instance.serverMemo.errors),
                }"
                    {{
                        $getExtraInputAttributeBag()
                            ->merge($getExtraAlpineAttributes(), escape: false)
                            ->merge([
                                'autocapitalize' => $getAutocapitalize(),
                                'autocomplete' => $getAutocomplete(),
                                'autofocus' => $isAutofocused(),
                                'disabled' => $isDisabled(),
                                'dusk' => "filament.forms.{$statePath}",
                                'id' => $id,
                                'inputmode' => $getInputMode(),
                                'list' => null,
                                'max' => null,
                                'maxlength' => null,
                                'min' => null,                                
                                'minlength' => null,
                                'placeholder' => $getPlaceholder(),
                                'readonly' => $isReadOnly(),
                                'required' => $isRequired() && (! $isConcealed),
                                'type' => 'text',
                                $applyStateBindingModifiers('wire:model') => (! $isLocation) ? $statePath : null,
                            ], escape: false)
                            ->class([
                                'filament-forms-input block w-full transition duration-75 shadow-sm outline-none sm:text-sm focus:relative focus:z-[1] focus:ring-1 focus:ring-inset disabled:opacity-70 dark:bg-gray-700 dark:text-white',
                                'rounded-s-lg' => ! ($prefixLabel || $prefixIcon),
                                'rounded-e-lg' => ! ($suffixLabel || $suffixIcon),
                            ])
                    }}
            />

            @if($getIsLocation())
                <input
                {{ $applyStateBindingModifiers('wire:model') }}="{{ $getStatePath() }}"
                type="hidden"
                id="{{ $id }}"
                />
            @endif
        </div>
    </x-filament-forms::affixes>
</x-dynamic-component>
