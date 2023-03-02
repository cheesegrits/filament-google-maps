<?php

namespace Cheesegrits\FilamentGoogleMaps\Tests\Commands;

use Cheesegrits\FilamentGoogleMaps\Tests\Columns\Fixtures\LocationTable;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class CommandsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Livewire::component(LocationTable::getName(), LocationTable::class);
    }
}
