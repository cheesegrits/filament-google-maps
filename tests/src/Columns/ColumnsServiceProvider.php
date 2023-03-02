<?php

namespace Cheesegrits\FilamentGoogleMaps\Tests\Columns;

use Cheesegrits\FilamentGoogleMaps\Tests\Columns\Fixtures\LocationTable;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class ColumnsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Livewire::component(LocationTable::getName(), LocationTable::class);
    }
}
