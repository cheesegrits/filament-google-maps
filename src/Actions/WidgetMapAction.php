<?php

namespace Cheesegrits\FilamentGoogleMaps\Actions;

use Filament\Actions\BulkAction;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Cheesegrits\FilamentGoogleMaps\Fields\WidgetMap;
use Filament\Actions\Concerns\CanCustomizeProcess;
use Filament\Forms;
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

        $this->mountUsing(function (Schema $schema, $records) {
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
            Section::make()->schema([
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
