<?php

namespace Cheesegrits\FilamentGoogleMaps\Actions;

use Cheesegrits\FilamentGoogleMaps\Fields\WidgetMap;
use Filament\Forms;
use Filament\Support\Actions\Concerns\CanCustomizeProcess;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class WidgetMapAction extends BulkAction
{
    use CanCustomizeProcess;

    public $markers = [];

    public static function getDefaultName(): ?string
    {
        return 'widget_map';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('filament-google-maps::fgm.widget_map_action.button.label'));

        $this->modalHeading(fn (): string => __('filament-google-maps::fgm.widget_map_action.modal.heading', ['label' => $this->getPluralModelLabel()]));

        $this->modalButton(__('filament-google-maps::fgm.widget_map_action.modal.label'));

        $this->successNotificationTitle(__('filament-google-maps::fgm.widget_map_action.modal.success'));

        $this->color('danger');

        $this->icon('heroicon-s-trash');

        //		$this->requiresConfirmation();

        $this->size('lg');

        $this->mountUsing(function (Forms\ComponentContainer $form, $records) {
            $markers      = [];
            $latLngFields = $this->getModel()::getLatLngAttributes();

            $records->each(function (Model $record) use (&$markers, $latLngFields) {
                $latField = $latLngFields['lat'];
                $lngField = $latLngFields['lng'];

                $markers[] = [
                    'location' => [
                        'lat' => $record->{$latField} ? round(floatval($record->{$latField}), 8) : 0,
                        'lng' => $record->{$lngField} ? round(floatval($record->{$lngField}), 8) : 0,
                    ],
                ];
            });

            $this->markers = $markers;

            //			$form->fill([
            //				Forms\Components\Hidden::make('markers')
            //					->afterStateHydrated(function () {
            //						json_encode($markers);
            //					}),
            //			]);
        });

        $this->form([
            Forms\Components\Card::make()->schema([
                WidgetMap::make('widget_map')
                    ->markers(function (callable $get) {
                        return $this->markers;
                    }),
            ])
                ->columns(1),
        ])->size('lg');

        $this->action(function (): void {
            $this->process(function (array $data, Collection $records) {
                //
            });

            $this->success();
        });
    }
}
