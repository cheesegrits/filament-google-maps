@php
    $affixLabelClasses = [
        'whitespace-nowrap group-focus-within:text-primary-500',
        'text-gray-400' => ! $errors->has($getStatePath()),
        'text-danger-400' => $errors->has($getStatePath()),
    ];
@endphp

<x-dynamic-component
        :component="$getFieldWrapperView()"
        :id="$getId()"
        :label="$getLabel()"
        :label-sr-only="$isLabelHidden()"
        :helper-text="$getHelperText()"
        :hint="$getHint()"
        :hint-action="$getHintAction()"
        :hint-color="$getHintColor()"
        :hint-icon="$getHintIcon()"
        :required="$isRequired()"
        :state-path="$getStatePath()"
>
    <div {{ $attributes->merge($getExtraAttributes())->class(['filament-forms-text-input-component flex items-center space-x-2 rtl:space-x-reverse group']) }}>
        @if (($prefixAction = $getPrefixAction()) && (! $prefixAction->isHidden()))
            {{ $prefixAction }}
        @endif

        @if ($icon = $getPrefixIcon())
            <x-dynamic-component :component="$icon" class="w-5 h-5" />
        @endif

        @if ($label = $getPrefixLabel())
            <span @class($affixLabelClasses)>
                {{ $label }}
            </span>
        @endif

            <div class="w-full"
                 x-data="{
            fgm: {},
        }"

                 x-init="
            (async () => {
                @if($hasCss())
                    if(!document.getElementById('filament-google-geocomplete-css')){
                        const link  = document.createElement('link');
                        link.id   = 'filament-google-maps-css';
                        link.rel  = 'stylesheet';
                        link.type = 'text/css';
                        link.href = '{{ $cssUrl() }}';
                        link.media = 'all';
                        document.head.appendChild(link);
                    }
                @endif
                @if($hasJs())
                    if(!document.getElementById('filament-google-geocomplete-js')){
                        const script = document.createElement('script');
                        script.id   = 'filament-google-maps-js';
                        script.src = '{{ $jsUrl() }}';
                        document.head.appendChild(script);
                    }
                 @endif

                do {
                    await (new Promise(resolve => setTimeout(resolve, 100)));
                } while (window.filamentGoogleGeocomplete === undefined);
                fgm = filamentGoogleGeocomplete($wire, {{ $getMapConfig()}});
                fgm.init();
            })()

        "
                 wire:ignore
            >
        <div class="flex-1">
            <input x-data="{}"
            {{ $applyStateBindingModifiers('wire:model') }}="{{ $getStatePath() }}"
            type="text"

            dusk="filament.forms.{{ $getStatePath() }}"
            {!! $isAutofocused() ? 'autofocus' : null !!}
            {!! $isDisabled() ? 'disabled' : null !!}
            id="{{ $getId() }}"
            {!! ($inputMode = $getInputMode()) ? "inputmode=\"{$inputMode}\"" : null !!}
            {!! ($placeholder = $getPlaceholder()) ? "placeholder=\"{$placeholder}\"" : null !!}
            @if (! $isConcealed())
                {!! filled($length = $getMaxLength()) ? "maxlength=\"{$length}\"" : null !!}
                {!! filled($length = $getMinLength()) ? "minlength=\"{$length}\"" : null !!}
                {!! $isRequired() ? 'required' : null !!}
            @endif
            {{ $getExtraInputAttributeBag()->class([
                'block w-full transition duration-75 rounded-lg shadow-sm focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 disabled:opacity-70',
                'dark:bg-gray-700 dark:text-white dark:focus:border-primary-500' => config('forms.dark_mode'),
            ]) }}
            x-bind:class="{
                    'border-gray-300': ! (@js($getStatePath()) in $wire.__instance.serverMemo.errors),
                    'dark:border-gray-600': ! (@js($getStatePath()) in $wire.__instance.serverMemo.errors) && @js(config('forms.dark_mode')),
                    'border-danger-600 ring-danger-600': (@js($getStatePath()) in $wire.__instance.serverMemo.errors),
                }"
            />
        </div>
            </div>

        @if ($label = $getSuffixLabel())
            <span @class($affixLabelClasses)>
                {{ $label }}
            </span>
        @endif

        @if ($icon = $getSuffixIcon())
            <x-dynamic-component :component="$icon" class="w-5 h-5" />
        @endif

        @if (($suffixAction = $getSuffixAction()) && (! $suffixAction->isHidden()))
            {{ $suffixAction }}
        @endif
    </div>
</x-dynamic-component>

