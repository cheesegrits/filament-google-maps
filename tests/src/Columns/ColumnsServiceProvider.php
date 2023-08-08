<?php

namespace Cheesegrits\FilamentGoogleMaps\Tests\Columns;

use Cheesegrits\FilamentGoogleMaps\Tests\Columns\Fixtures\LocationTable;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Livewire\Mechanisms\ComponentRegistry;

class ColumnsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Livewire::component(app(ComponentRegistry::class)->getName(LocationTable::class), LocationTable::class);
    }
}
