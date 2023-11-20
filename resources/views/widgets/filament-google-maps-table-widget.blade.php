@php
    $heading     = $this->getHeading();
    $filters     = $this->getFilters();
    $icon        = $this->getIcon();
    $collapsible = $this->getCollapsible();
@endphp

<x-filament-widgets::widget>
    <x-filament::section
        class="filament-google-maps-widget"
        :icon="$icon"
        :collapsible="$collapsible"
    >
        <x-slot name="heading">
            {{ $heading }}
        </x-slot>

        @if ($filters)
            <x-slot name="headerEnd">
                <x-filament::input.wrapper
                    inline-prefix
                    wire:target="filter"
                    class="-my-2"
                >
                    <x-filament::input.select
                        inline-prefix
                        wire:model.live="filter"
                    >
                        @foreach ($filters as $value => $label)
                            <option value="{{ $value }}">
                                {{ $label }}
                            </option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </x-slot>
        @endif

        <div>
            <div
                wire:key="{{ rand() }}"
                x-ignore
                ax-load
                ax-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('filament-google-maps-widget', 'cheesegrits/filament-google-maps') }}"
                x-data="filamentGoogleMapsWidget({
                    cachedData: {{  json_encode($this->getCachedData()) }},
                    config: {{ $this->getMapConfig()}},
                    mapEl: $refs.map,
{{--                    mapFilterIds: {{ $this->mapIsFilter() ? 'wire:@entangle("mapFilterIds")' : null}}--}}
                })"
                wire:ignore
                @if ($maxHeight = $this->getMaxHeight())
                    style=" max-height: {{ $maxHeight }}"
                @endif
            >
                <div
                    @if ($this->mapIsFilter())
                        wire: @entangle('mapFilterIds')
                    @endif
                    wire:ignore
                    id="map-{{ $this->getMapId() }}"
                    x-ref="map"
                    class="w-full"
                    style="
                        min-height: {{ $this->getMinHeight() }};
                        z-index: 1 !important;
                    "
                ></div>
            </div>
        </div>
    </x-filament::section>

    <x-filament::section class="filament-google-maps-widget-table mt-2">
        {{ $this->table }}
    </x-filament::section>
</x-filament-widgets::widget>
