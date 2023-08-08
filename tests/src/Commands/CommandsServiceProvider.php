<?php

namespace Cheesegrits\FilamentGoogleMaps\Tests\Commands;

use Cheesegrits\FilamentGoogleMaps\Tests\Columns\Fixtures\LocationTable;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Livewire\Mechanisms\ComponentRegistry;

class CommandsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Livewire::component(app(ComponentRegistry::class)->getName(LocationTable::class), LocationTable::class);
    }
}
