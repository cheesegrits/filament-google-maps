<?php

namespace Cheesegrits\FilamentGoogleMaps\Tests\Fields\Fixtures\Resources\MapResource\Pages;

use Cheesegrits\FilamentGoogleMaps\Tests\Fields\Fixtures\Resources\MapResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditMap extends EditRecord
{
    protected static string $resource = MapResource::class;

    protected function getActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
