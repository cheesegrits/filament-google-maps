<?php

namespace Cheesegrits\FilamentGoogleMaps\Widgets;

use Closure;
use Filament\Tables;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class MapTableWidget extends MapWidget implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable {
        getTableRecords as traitGetTableRecords;
    }

    protected static string $view = 'filament-google-maps::widgets.filament-google-maps-table-widget';

    protected static ?string $heading = null;

    protected static ?bool $filtered = true;

    public ?bool $mapIsFilter = false;

    public array $mapFilterIds = [];

    public bool $mapFilterFirstTime = true;

    public function mapIsFilter(): bool
    {
        return $this->mapIsFilter;
    }

    protected function getTableHeading(): string|Htmlable|Closure|null
    {
        return static::$heading ?? (string) Str::of(class_basename(static::class))
            ->beforeLast('Widget')
            ->kebab()
            ->replace('-', ' ')
            ->title();
    }

    protected function paginateTableQuery(Builder $query): Paginator
    {
        return $query->simplePaginate($this->getTableRecordsPerPage() == -1 ? $query->count() : $this->getTableRecordsPerPage());
    }

    protected function getRecords()
    {
        if (static::$filtered) {
            return $this->traitGetTableRecords();
        } else {
            return $this->getTableModel()::all();
        }
    }

    public function getTableRecords(): Collection|Paginator
    {
        return $this->traitGetTableRecords();
    }

    public function getConfig(): array
    {
        $config = parent::getConfig();

        $config = array_merge($config, [
            'mapIsFilter' => $this->mapIsFilter,
        ]);

        return $config;
    }
}
