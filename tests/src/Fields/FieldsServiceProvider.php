<?php

namespace Cheesegrits\FilamentGoogleMaps\Tests\Fields;

use Cheesegrits\FilamentGoogleMaps\Tests\Fields\Fixtures\Resources\LocationResource;
use Filament\PluginServiceProvider;

//use Filament\Tests\Admin\Fixtures\Resources\UserResource;

class FieldsServiceProvider extends PluginServiceProvider
{
    public static string $name = 'resources';

    protected function getResources(): array
    {
        return [
            LocationResource::class,
            //			UserResource::class,
        ];
    }
}
