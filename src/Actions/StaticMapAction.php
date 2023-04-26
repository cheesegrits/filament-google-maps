<?php

namespace Cheesegrits\FilamentGoogleMaps\Actions;

use Cheesegrits\FilamentGoogleMaps\Columns\MapColumn;
use Cheesegrits\FilamentGoogleMaps\Helpers\MapsHelper;
use Filament\Forms;
use Filament\Support\Actions\Concerns\CanCustomizeProcess;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;
use Mastani\GoogleStaticMap\GoogleStaticMap;

class StaticMapAction extends BulkAction
{
    use CanCustomizeProcess;

    public static function getDefaultName(): ?string
    {
        return 'static_map';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('filament-google-maps::fgm.static_map_action.button.label'));

        $this->modalHeading(fn (): string => __('filament-google-maps::fgm.static_map_action.modal.heading', ['label' => $this->getPluralModelLabel()]));

        $this->modalButton(__('filament-google-maps::fgm.static_map_action.modal.label'));

        $this->successNotificationTitle(__('filament-google-maps::fgm.static_map_action.modal.success'));

        $this->color('danger');

        $this->icon('heroicon-s-trash');

        $this->requiresConfirmation();

        //		$this->mountUsing(function (Forms\ComponentContainer $form, $records) {
        //			$form->fill([]);
        //		});

        $this->form([
            Forms\Components\Card::make()->schema([
                Forms\Components\TextInput::make('width')
                    ->integer()
                    ->minValue(100)
                    ->maxValue(640)
                    ->default(600),
                Forms\Components\TextInput::make('height')
                    ->integer()
                    ->minValue(100)
                    ->maxValue(640)
                    ->default(450),
                Forms\Components\Select::make('scale')
                    ->options([
                        1 => '1',
                        2 => '2',
                    ])
                    ->default(1),
                Forms\Components\Select::make('type')
                    ->options([
                        'satellite' => 'Satellite',
                        'hybrid'    => 'Hybrid',
                        'roadmap'   => 'Roadmap',
                        'terrain'   => 'Terrain',
                    ])
                    ->default('roadmap'),
            ])
                ->columns(2),

        ]);

        $this->action(function (): void {
            $this->process(function (array $data, Collection $records) {
                $markers = [];
                $map     = new GoogleStaticMap(MapsHelper::mapsKey(true));

                $url = $map
                    ->setZoom(0)
                    ->setMapType($data['type'])
                    ->setScale($data['scale'])
                    ->setSize($data['width'], $data['height']);

                $latLngFields = $this->getModel()::getLatLngAttributes();

                $records->each(function (Model $record) use ($map, $latLngFields) {
                    $latField = $latLngFields['lat'];
                    $lngField = $latLngFields['lng'];

                    $map->addMarkerLatLng(
                        $record->{$latField},
                        $record->{$lngField},
                        '1',
                        'red',
                    );
                });

                if (MapsHelper::hasSigningKey()) {
                    $url->setSecret(MapsHelper::mapsSigningKey());
                }

                $src = $url->make();

                if ($language = MapsHelper::mapsLanguage(true)) {
                    $src .= '&language='.$language;
                }

                $cacheKey = MapColumn::cacheImage($src);

                //				return Response::streamDownload(
                //					function () use ($cacheKey) {
                //						echo Cache::store(config('filament-google-maps.cache.store', null))
                //							->get($cacheKey);
                //					},
                //					'foobar',
                //					[
                //						'Content-Type' => 'image/png'
                //					]
                //				);

                return redirect()->to('/cheesegrits/filament-google-maps/'.$cacheKey.'.png');
            });

            $this->success();
        });
    }
}
