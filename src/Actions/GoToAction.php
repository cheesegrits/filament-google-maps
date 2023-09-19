<?php

namespace Cheesegrits\FilamentGoogleMaps\Actions;

use Closure;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

class GoToAction extends Action
{
    protected bool|Closure|null $isLivewireClickHandlerEnabled = false;

    public null|Closure|int $zoom = null;

    public static function getDefaultName(): ?string
    {
        return 'setmapcenter';
    }

    public function zoom($zoom): static
    {
        $this->zoom = $zoom;

        return $this;
    }

    public function getZoom(): ?int
    {
        return $this->evaluate($this->zoom) ?? 8;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('filament-google-maps::fgm.goto_action.button.label'));

        $this->color($this->getColor() ?? 'danger');

        $this->icon($this->getIcon() ?? 'heroicon-s-map');

        $this->extraAttributes(function (Model $record) {
            $latLngFields = $record::getLatLngAttributes();

            return [
                'x-on:click' => new HtmlString(
                    sprintf("\$dispatch('filament-google-maps::widget/setMapCenter', {lat: %f, lng: %f, zoom: %d})",
                        round(floatval($record->{$latLngFields['lat']}), 8),
                        round(floatval($record->{$latLngFields['lng']}), 8),
                        $this->getZoom()
                    )
                ),
            ];
        });
    }
}
