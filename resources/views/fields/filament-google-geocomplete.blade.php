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
    <x-filament::input.affixes
            :state-path="$statePath"
            :prefix="$prefixLabel"
            :prefix-actions="$getPrefixActions()"
            :prefix-icon="$prefixIcon"
            :suffix="$suffixLabel"
            :suffix-actions="$getSuffixActions()"
            :suffix-icon="$suffixIcon"
            class="filament-forms-text-input-component"
            :attributes="$getExtraAttributeBag()"
    >
        <div class="w-full"
             x-ignore
             ax-load
             ax-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('filament-google-maps-geocomplete', 'cheesegrits/filament-google-maps') }}"
             x-data="filamentGoogleGeocomplete({
                setStateUsing: async (path, state) => {
                    return await $wire.set(path, state)
                },
                filterName: @js($getFilterName()),
                statePath: @js($getStatePath()),
                isLocation: @js($getIsLocation()),
                reverseGeocodeFields: @js($getReverseGeocode()),
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
                        ->merge([
                            'autocapitalize' => $getAutocapitalize(),
                            'autocomplete' => $getAutocomplete(),
                            'autofocus' => $isAutofocused(),
                            'disabled' => $isDisabled(),
                            'dusk' => "filament.forms.{$statePath}",
                            'id' => $id,
                            'inputmode' => $getInputMode(),
                            'list' => null,
                            'maxlength' => (! $isConcealed) ? $getMaxLength() : null,
                            'minlength' => (! $isConcealed) ? $getMinLength() : null,
                            'placeholder' => $getPlaceholder(),
                            'readonly' => $isReadOnly(),
                            'required' => $isRequired() && (! $isConcealed),
                            'type' => 'text',
                            $applyStateBindingModifiers('wire:model') => (! $isLocation) ? $statePath : null,
                        ], escape: false)
                        ->class([
                            'block w-full transition duration-75 shadow-sm outline-none sm:text-sm focus:border-primary-500 focus:relative focus:z-[1] focus:ring-1 focus:ring-inset focus:ring-primary-500 disabled:opacity-70 dark:bg-gray-700 dark:text-white dark:focus:border-primary-500',
                            'rounded-l-lg' => ! ($prefixLabel || $prefixIcon),
                            'rounded-r-lg' => ! ($suffixLabel || $suffixIcon),
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
    </x-filament::input.affixes>
</x-dynamic-component>
