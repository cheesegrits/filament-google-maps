<?php

namespace Cheesegrits\FilamentGoogleMaps\Tests\Fields;

use Cheesegrits\FilamentGoogleMaps\Tests\Fields\Fixtures\Resources\LocationResource;
use Filament\FilamentServiceProvider;

//use Filament\Tests\Admin\Fixtures\Resources\UserResource;

class FieldsServiceProvider extends FilamentServiceProvider
{
    public static string $name = 'resources';

    public function boot()
    {
        // if parent boot() runs, we get ../routes/web.php errors thrown in testing
    }

    protected function getResources(): array
    {
        return [
            LocationResource::class,
            //			UserResource::class,
        ];
    }
}
