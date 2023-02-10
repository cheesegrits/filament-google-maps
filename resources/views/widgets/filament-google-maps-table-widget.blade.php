@php
    $heading = $this->getHeading();
    $filters = $this->getFilters();
@endphp

<x-filament::widget class="filament-google-maps-widget">
    <x-filament::card>
        @if ($heading || $filters)
            <div class="flex items-center justify-between gap-8">
                @if ($heading)
                    <x-filament::card.heading>
                        {{ $heading }}
                    </x-filament::card.heading>
                @endif

                @if ($filters)
                    <select
                            wire:model="filter"
                            @class([
                                'text-gray-900 border-gray-300 block h-10 transition duration-75 rounded-lg shadow-sm focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500',
                                'dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 dark:focus:border-primary-500' => config('filament.dark_mode'),
                            ])
                            wire:loading.class="animate-pulse"
                    >
                        @foreach ($filters as $value => $label)
                            <option value="{{ $value }}">
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                @endif
            </div>

            <x-filament::hr/>
        @endif

        {{--        <div {!! ($pollingInterval = $this->getPollingInterval()) ? "wire:poll.{$pollingInterval}=\"updateMapData\"" : '' !!}>--}}
        <div class="">
            <div
                wire:key="{{ rand() }}"
                x-data="{
                    fgm: {},
                }"
                    x-init="
        (async () => {
            @if($this->hasCss())
                if(!document.getElementById('filament-google-maps-css')){
                    const link  = document.createElement('link');
                    link.id   = 'filament-google-maps-css';
                    link.rel  = 'stylesheet';
                    link.type = 'text/css';
                    link.href = '{{ $this->cssUrl() }}';
                    link.media = 'all';
                    document.head.appendChild(link);
                }
            @endif
            @if($this->hasJs())
                if(!document.getElementById('filament-google-maps-js')){
                    const script = document.createElement('script');
                    script.id   = 'filament-google-maps-js';
                    script.src = '{{ $this->jsUrl() }}';
                    document.head.appendChild(script);
                }
             @endif

            do {
                await (new Promise(resolve => setTimeout(resolve, 100)));
            } while (window.filamentGoogleMapsWidget === undefined);

            if (!window.fgm{{ $this->getMapId() }}) {
                window.fgm{{ $this->getMapId() }} = filamentGoogleMapsWidget($wire, {{ $this->getMapConfig()}});
                window.fgm{{ $this->getMapId() }}.init({{ json_encode($this->getCachedData()) }}, 'map-{{ $this->getMapId() }}');
            }
            else {
                window.fgm{{ $this->getMapId() }}.update({{ json_encode($this->getCachedData()) }});
            }


        })()
        "

                    wire:ignore
                    @if ($maxHeight = $this->getMaxHeight())
                        style=" max-height: {{ $maxHeight }}"
                    @endif
            >
            </div>
            <div
                    @if($this->mapIsFilter())
                        wire:@entangle('mapFilterIds')
                    @endif

                    @setmapcenter.window='window.fgm{{ $this->getMapId() }}.recenter($event.detail)'
                    wire:ignore
                    id="map-{{ $this->getMapId() }}" x-ref="map" class="w-full"
                    style="min-height: 50vh; z-index: 1 !important;"
            ></div>

        </div>
    </x-filament::card>

    <x-filament::card>
        {{ $this->table }}
    </x-filament::card>
</x-filament::widget>
