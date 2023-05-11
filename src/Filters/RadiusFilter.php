<?php

namespace Cheesegrits\FilamentGoogleMaps\Filters;

use Cheesegrits\FilamentGoogleMaps\Fields\Geocomplete;
use Closure;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\BaseFilter;
use Filament\Tables\Filters\Concerns\HasRelationship;
use Illuminate\Database\Eloquent\Builder;

class RadiusFilter extends BaseFilter
{
    use HasRelationship;

    protected string|Closure|null $latitude = null;

    protected string|Closure|null $longitude = null;

    protected bool|Closure|null $kilometers = false;

    protected bool|Closure|null $selectUnit = false;

    protected bool|string|Closure|null $section = null;

    protected int|Closure|null $radius = null;

    protected string|Closure|null $attribute = null;

    public function getColumns(): array|int|null
    {
        return 4;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->columnSpan(2);

        //		$this->getTable()->getFiltersFormWidth('7xl');

        $this->indicateUsing(function (RadiusFilter $filter, array $state): array {
            if (blank($state['geocomplete'] ?? null)) {
                return [];
            }

            if (blank($state['radius'] ?? null)) {
                return [];
            }

            $label = __('filament-google-maps::fgm.radius_filter.indicate', [
                'radius'  => $state['radius'],
                'units'   => $state['unit'],
                'address' => $state['geocomplete'],
            ]);

            return ["{$this->getIndicator()}: {$label}"];
        });
    }

    public function apply(Builder $query, array $data = []): Builder
    {
        $latitude  = $data['latitude'] ?? null;
        $longitude = $data['longitude'] ?? null;
        $distance  = $data['radius'] ?? null;

        if ($latitude && $longitude && $distance) {
            $kilometers = $this->getKilometers();

            if ($this->getSelectUnit()) {
                $kilometers = ($data['unit'] ?? null) === 'km';
            }

            $latName = $this->getLatitude();
            $lngName = $this->getLongitude();

            //			$sql = "((ACOS(SIN(? * PI() / 180) * SIN(" . $latName . " * PI() / 180) + COS(? * PI() / 180) * COS(" .
            //				$latName . " * PI() / 180) * COS((? - " . $lngName . ") * PI() / 180)) * 180 / PI()) * 60 * ?) as distance";

            $sql = "((ACOS(SIN($latitude * PI() / 180) * SIN(".$latName." * PI() / 180) + COS($latitude * PI() / 180) * COS(".
                $latName." * PI() / 180) * COS(($longitude - ".$lngName.") * PI() / 180)) * 180 / PI()) * 60 * %f) < $distance";

            $sql = sprintf($sql, $kilometers ? (1.1515 * 1.609344) : 1.1515);

            if (! $this->queriesRelationships()) {
                $query->whereIn(
                    $query->getModel()->getKeyName(),
                    function ($builder) use ($sql, $query) {
                        $builder->select($query->getModel()->getKeyName())
                            ->from($query->getModel()->getTable());
                        $builder->whereRaw($sql);
                    }
                );
            } else {
                $query->whereHas(
                    $this->getRelationshipName(),
                    function ($builder) use ($sql) {
                        $builder->select($this->getRelationshipKey())
                            ->from($this->getRelationship()->getModel()->getTable());
                        $builder->whereRaw($sql);
                    }
                );
            }
        }

        return $query;
    }

    public function getFormSchema(): array
    {
        $form = [
            Group::make()->schema([
                Geocomplete::make('geocomplete')
                    ->label(__('filament-google-maps::fgm.radius_filter.address'))
                    ->filterName($this->getName())
                    ->lazy(),
                Group::make()->schema([
                    TextInput::make('radius')
                        ->label(__('filament-google-maps::fgm.radius_filter.distance'))
                        ->numeric()
                        ->default($this->getRadius() ?? 10)
                        ->lazy(),
                    Select::make('unit')
                        ->label(__('filament-google-maps::fgm.radius_filter.unit'))
                        ->options([
                            'mi' => __('filament-google-maps::fgm.radius_filter.miles'),
                            'km' => __('filament-google-maps::fgm.radius_filter.kilometers'),
                        ])
                        ->default(
                            $this->getKilometers() ? 'km' : 'mi'
                        )
                        ->visible(fn () => $this->getSelectUnit()),
                ])
                    ->columns($this->getSelectUnit() ? 2 : 1),
                Group::make()->schema([
                    Hidden::make('latitude'),
                    Hidden::make('longitude'),
                ]),
            ])
                ->columnSpan('full'),
        ];

        if ($this->hasSection()) {
            $form = [
                Fieldset::make($this->getSection())->schema($form),
            ];
        }

        return $form;
    }

    public function kilometers(bool|Closure $kilometers = true): static
    {
        $this->kilometers = $kilometers;

        return $this;
    }

    public function getKilometers(): string
    {
        return $this->evaluate($this->kilometers);
    }

    public function radius(bool|Closure $radius = true): static
    {
        $this->radius = $radius;

        return $this;
    }

    public function getRadius(): int|null
    {
        return $this->evaluate($this->radius);
    }

    public function selectUnit(bool|Closure $selectUnit = true): static
    {
        $this->selectUnit = $selectUnit;

        return $this;
    }

    public function getSelectUnit(): string
    {
        return $this->evaluate($this->selectUnit);
    }

    public function latitude(string|Closure|null $name): static
    {
        $this->latitude = $name;

        return $this;
    }

    public function getLatitude(): string
    {
        return $this->evaluate($this->latitude) ??
            ! $this->queriesRelationships() ? $this->getTable()->getModel()::getLatLngAttributes()['lat']
            : $this->getRelationship()->getModel()->getLatLngAttributes()['lat'];
    }

    public function longitude(string|Closure|null $name): static
    {
        $this->longitude = $name;

        return $this;
    }

    public function getLongitude(): string
    {
        return $this->evaluate($this->longitude) ??
            ! $this->queriesRelationships() ? $this->getTable()->getModel()::getLatLngAttributes()['lng']
            : $this->getRelationship()->getModel()->getLatLngAttributes()['lng'];
    }

    public function section(bool|string|Closure|null $section = true): static
    {
        $this->section = $section;

        return $this;
    }

    public function getSection(): string|null
    {
        $section = $this->evaluate($this->section);

        if ($section === true) {
            $section = __('fgm.radius_filter.title');
        }

        return $section;
    }

    private function hasSection(): bool
    {
        return ! empty($this->getSection());
    }

    //public function relation(bool|Closure $relationship = true): static
    //{
    //    $this->relationship = $relationship;
    //
    //    return $this;
    //}
    //
    //public function getRelationship(): string
    //{
    //    return $this->evaluate($this->relationship);
    //}

    public function isRelationship(): bool
    {
        return ! empty($this->getRelationship());
    }

    public function attribute(string|Closure|null $name): static
    {
        $this->attribute = $name;

        return $this;
    }

    public function getAttribute(): string
    {
        return $this->evaluate($this->attribute) ?? $this->getName();
    }
}
