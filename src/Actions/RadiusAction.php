<?php

namespace Cheesegrits\FilamentGoogleMaps\Actions;

use Cheesegrits\FilamentGoogleMaps\Helpers\MapsHelper;
use Filament\Actions\Concerns\CanCustomizeProcess;
use Filament\Tables\Actions\Action;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Model;

class RadiusAction extends Action
{
    use CanCustomizeProcess;

    public static function getDefaultName(): ?string
    {
        return 'radius';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('filament-google-maps::fgm.radius_action.button.label'));

        $this->color('danger');

        $this->icon('heroicon-s-magnifying-glass-circle');

        $this->action(function (): void {
            $this->process(function (HasTable $livewire, Model $record): void {
                $latLngFields = $record::getLatLngAttributes();

                //				$livewire->tableFilters['radius']['latitude']  = $record->{$latLngFields['lat']};
                //				$livewire->tableFilters['radius']['longitude'] = $record->{$latLngFields['lng']};

                //				$address = MapsHelper::reverseGeocode([
                //					'lat' => $record->{$latLngFields['lat']},
                //					'lng' => $record->{$latLngFields['lng']},
                //				]);

                //				$livewire->emit('centerMapWidget', [
                //					'center' => [
                //						'lat' => $record->{$latLngFields['lat']},
                //						'lng' => $record->{$latLngFields['lng']},
                //					],
                //				]);

                $address = MapsHelper::reverseGeocode([
                    'lat' => $record->{$latLngFields['lat']},
                    'lng' => $record->{$latLngFields['lng']},
                ]);

                $locationField = $record->getComputedLocation();
                $lat           = $record->{$latLngFields['lat']};
                $lng           = $record->{$latLngFields['lng']};

                $form                                 = $livewire->getTableFiltersForm();
                $state                                = $form->getState();
                $state[$locationField]['geocomplete'] = $address;
                $form->fill($state);

                $livewire->tableFilters[$record->getComputedLocation()]['latitude']  = $record->{$latLngFields['lat']};
                $livewire->tableFilters[$record->getComputedLocation()]['longitude'] = $record->{$latLngFields['lng']};
            });

            $this->success();
        });
    }
}
