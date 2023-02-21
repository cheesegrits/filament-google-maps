<?php

use Cheesegrits\FilamentGoogleMaps\Tests\Columns\Fixtures\LocationTable;
use Cheesegrits\FilamentGoogleMaps\Tests\Columns\TestCase;
use Cheesegrits\FilamentGoogleMaps\Tests\Models\Location;
use function Pest\Livewire\livewire;

uses(TestCase::class);

it('can filter records by radius', function () {
    $east = Location::factory()->withRealAddressAndLatLng('united-states-of-america', 'New York, NY')->count(5)->create();
    $west = Location::factory()->withRealAddressAndLatLng('united-states-of-america', 'Los Angeles, CA')->count(5)->create();

    livewire(LocationTable::class)
        ->assertCanSeeTableRecords($east)
        ->filterTable(
            'radius',
            [
                'latitude'  => '34.0',
                'longitude' => '-118.2',
                'unit'      => 'km',
                'radius'    => 500,
            ]
        )
        ->assertCanSeeTableRecords($west)
        ->assertCanNotSeeTableRecords($east);
});

it('can reset radius filter', function () {
    $locations = Location::factory()->count(10)->create();

    livewire(LocationTable::class)
        ->filterTable(
            'radius',
            [
                'latitude'  => '0.1',
                'longitude' => '-0.1',
                'unit'      => 'km',
                'radius'    => 1,
            ]
        )
        ->assertCanNotSeeTableRecords($locations)
        ->resetTableFilters()
        ->assertCanSeeTableRecords($locations);
});

it('can remove radius filter', function () {
    $locations = Location::factory()->count(10)->create();

    livewire(LocationTable::class)
        ->assertCanSeeTableRecords($locations)
        ->filterTable(
            'radius',
            [
                'latitude'  => '0.1',
                'longitude' => '-0.1',
                'unit'      => 'km',
                'radius'    => 1,
            ]
        )
        ->assertCanNotSeeTableRecords($locations)
        ->removeTableFilter('radius')
        ->assertCanSeeTableRecords($locations);
});
