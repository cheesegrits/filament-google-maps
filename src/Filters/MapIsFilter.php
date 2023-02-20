<?php

namespace Cheesegrits\FilamentGoogleMaps\Filters;

use Closure;
use Filament\Tables\Filters\BaseFilter;
use Illuminate\Database\Eloquent\Builder;

class MapIsFilter extends BaseFilter
{
    protected string|Closure|null $latitude = null;

    protected string|Closure|null $longitude = null;

    protected bool|Closure|null $kilometers = false;

    protected bool|Closure|null $selectUnit = false;

    protected bool|string|Closure|null $section = null;

    protected int|Closure|null $radius = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->columnSpan(2);

        //		$this->getTable()->getFiltersFormWidth('7xl');

        $this->indicateUsing(function (MapIsFilter $filter, array $state): array {
            if ($this->getLivewire()->mapIsFilter) {
                if (! $this->getLivewire()->mapFilterFirstTime) {
                    if ($count = count($this->getLivewire()->mapFilterIds)) {
                        $label = __('filament-google-maps::fgm.map_is_filter.indicate', [
                            'count' => $count,
                        ]);

                        return ["{$this->getIndicator()}: {$label}"];
                    }
                }
            }

            return [];
        });
    }

    public function apply(Builder $query, array $data = []): Builder
    {
        if ($this->getLivewire()->mapIsFilter) {
            if ($this->getLivewire()->mapFilterFirstTime) {
                $this->getLivewire()->mapFilterFirstTime = false;
            } else {
                $query->whereIn(
                    $query->getModel()->getKeyName(),
                    $this->getLivewire()->mapFilterIds
                );
            }
        }

        return $query;
    }

    public function getFormSchema(): array
    {
        $form = [];

        return $form;
    }
}
