<?php

use Cheesegrits\FilamentGoogleMaps\Tests\Columns\Fixtures\LocationTable;
use Cheesegrits\FilamentGoogleMaps\Tests\Columns\TestCase;
use Cheesegrits\FilamentGoogleMaps\Tests\Models\Location;
use Illuminate\Support\Facades\Cache;
use function Pest\Livewire\livewire;

uses(TestCase::class);

it('can render page', function () {
    livewire(LocationTable::class)->assertSuccessful();
});

it('can render map column', function () {
    Location::factory()->count(5)->create();

    livewire(LocationTable::class)
        ->assertCanRenderTableColumn('location');
});

it('can cache static map images', function () {
    Location::factory()->count(5)->create();

    $cache = Cache::getStore();
    $this->assertCount(0, invade($cache)->storage);

    livewire(LocationTable::class);

    $this->assertCount(5, invade($cache)->storage);
});

it('only caches once for each record', function () {
    Location::factory()->count(5)->create();
    $cache = Cache::getStore();
    $this->assertCount(0, invade($cache)->storage);

    livewire(LocationTable::class);
    $this->assertCount(5, invade($cache)->storage);

    livewire(LocationTable::class);
    $this->assertCount(5, invade($cache)->storage);
});
