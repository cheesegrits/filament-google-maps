<div {{ $attributes->merge($getExtraAttributes())->class([
    'filament-google-maps-column',
    'px-4 py-3' => ! $isInline(),
]) }}>
    @php
        use Filament\Support\Enums\Alignment;

        $height = $getHeight();
        $width = $getWidth();
        $alignment = $getAlignment();

        if (! $alignment instanceof Alignment) {
            $alignment = filled($alignment) ? (Alignment::tryFrom($alignment) ?? $alignment) : null;
        }
    @endphp

    <div
        @class([
            'flex items-center',
            match ($alignment) {
                Alignment::Start, Alignment::Left => 'text-start justify-start',
                Alignment::Center => 'text-center justify-center',
                Alignment::End, Alignment::Right => 'text-end justify-end',
                Alignment::Between, Alignment::Justify => 'text-justify justify-between',
                default => $alignment,
            },
        ])
        style="
            {!! $height !== null ? "height: {$height}px;" : null !!}
            {!! $width !== null ? "width: {$width}px;" : null !!}
        "
    >
        @if ($path = $getImagePath())
            <img
                src="{{ $path }}"
                style="
                    {!! $height !== null ? "height: {$height}px;" : null !!}
                    {!! $width !== null ? "width: {$width}px;" : null !!}
                "
                {{ $getExtraImgAttributeBag() }}
            >
        @elseif (($placeholder = $getPlaceholder()) !== null)
            <x-filament-tables::columns.placeholder>
                {{ $placeholder }}
            </x-filament-tables::columns.placeholder>
        @endif
    </div>
</div>
