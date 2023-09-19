<?php

namespace Cheesegrits\FilamentGoogleMaps\Actions;

use Cheesegrits\FilamentGoogleMaps\Helpers\MapsHelper;
use Closure;
use Filament\Actions\Concerns\CanCustomizeProcess;
use Filament\Tables\Actions\Action;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Model;

class RadiusAction extends Action
{
    use CanCustomizeProcess;

    protected Closure|string|null $relationship = null;

    public static function getDefaultName(): ?string
    {
        return 'radius';
    }

    public function relationship(Closure|string $relationship): static
    {
        $this->relationship = $relationship;

        return $this;
    }

    public function getRelationship(): ?string
    {
        return $this->evaluate($this->relationship);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('filament-google-maps::fgm.radius_action.button.label'));

        $this->color($this->getColor() ?? 'danger');

        $this->icon($this->getIcon() ?? 'heroicon-s-magnifying-glass-circle');

        $this->action(function (): void {
            $this->process(function (HasTable $livewire, Model $record): void {
                if ($relationship = $this->getRelationship()) {
                    $latLngFields = $record->{$relationship}::getLatLngAttributes();
                    $lat          = $record->{$relationship}->{$latLngFields['lat']};
                    $lng          = $record->{$relationship}->{$latLngFields['lng']};
                } else {
                    $latLngFields = $record::getLatLngAttributes();
                    $lat          = $record->{$latLngFields['lat']};
                    $lng          = $record->{$latLngFields['lng']};
                }
                $address = MapsHelper::reverseGeocode([
                    'lat' => $lat,
                    'lng' => $lng,
                ]);

                $form                                   = $livewire->getTableFiltersForm();
                $state                                  = $form->getState();
                $state[$this->getName()]['geocomplete'] = $address;
                $form->fill($state);

                $livewire->tableFilters[$this->getName()]['latitude']  = $lat;
                $livewire->tableFilters[$this->getName()]['longitude'] = $lng;
            });

            $this->success();
        });
    }
}
