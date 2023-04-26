<?php

namespace Cheesegrits\FilamentGoogleMaps\Actions;

use Cheesegrits\FilamentGoogleMaps\Helpers\MapsHelper;
use Filament\Support\Actions\Concerns\CanCustomizeProcess;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\Concerns\InteractsWithRelationship;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Model;

class RadiusAction extends Action
{
    use CanCustomizeProcess;
    use InteractsWithRelationship;

    public static function getDefaultName(): ?string
    {
        return 'radius';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('filament-google-maps::fgm.radius_action.button.label'));

        //		$this->modalHeading(fn (): string => __('filament-support::actions/detach.single.modal.heading', ['label' => $this->getRecordTitle()]));
        //
        //		$this->modalButton(__('filament-support::actions/detach.single.modal.actions.detach.label'));
        //
        //		$this->successNotificationTitle(__('filament-support::actions/detach.single.messages.detached'));

        $this->color('danger');

        $this->icon('heroicon-s-search-circle');

        //		$this->requiresConfirmation();

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
