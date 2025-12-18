<?php

namespace Cheesegrits\FilamentGoogleMaps\Tests\Fields\Fixtures\Resources\MapResource\Pages;

use Cheesegrits\FilamentGoogleMaps\Tests\Fields\Fixtures\Resources\MapResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMap extends ViewRecord
{
    protected static string $resource = MapResource::class;

    protected function getActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
