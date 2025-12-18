<?php

namespace Cheesegrits\FilamentGoogleMaps\Tests\Fields\Fixtures\Resources\GeocompleteResource\Pages;

use Cheesegrits\FilamentGoogleMaps\Tests\Fields\Fixtures\Resources\GeocompleteResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditGeocomplete extends EditRecord
{
    protected static string $resource = GeocompleteResource::class;

    protected function getActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
