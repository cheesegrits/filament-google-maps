<?php

namespace Cheesegrits\FilamentGoogleMaps\Tests\Columns;

use Cheesegrits\FilamentGoogleMaps\Tests\Columns\Fixtures\LocationTable;
use Illuminate\Support\ServiceProvider;
use Livewire\Finder\Finder;
use Livewire\Livewire;

class ColumnsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        [$namespace, $componentName] = app(Finder::class)->parseNamespaceAndName(LocationTable::class);

        Livewire::component($componentName, LocationTable::class);
    }
}
