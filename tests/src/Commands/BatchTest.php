<?php

use Cheesegrits\FilamentGoogleMaps\Helpers\Geocoder;
use Cheesegrits\FilamentGoogleMaps\Tests\Commands\TestCase;
use Cheesegrits\FilamentGoogleMaps\Tests\Models\Location;
use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertEquals;

uses(TestCase::class);

it('can reverse geocode a table', function () {
    Location::factory()->withRealLatLng()->count(5)->create();

    $locations = Location::where(['processed' => 1])->get();
    assertCount(0, $locations);

    $geocode                         = new Geocoder(config('filament-google-maps.rate-limit', 150));
    [$records, $lookups, $processed] = $geocode->reverseBatch(
        Location::class,
        'lat',
        'lng',
        [
            'street=%n %S',
            'city=%L',
            'state=%A2',
            'zip=%z',
        ],
        'processed',
    );

    assertEquals(5, $processed);
    $locations = Location::where(['processed' => 1])->get();
    assertCount(5, $locations);
});

it('it can reverse geocode a table twice and only process once', function () {
    Location::factory()->withRealLatLng()->count(5)->create();

    $locations = Location::where(['processed' => 1])->get();
    assertCount(0, $locations);

    $geocode                         = new Geocoder(config('filament-google-maps.rate-limit', 150));
    [$records, $lookups, $processed] = $geocode->reverseBatch(
        Location::class,
        'lat',
        'lng',
        [
            'street=%n %S',
            'city=%L',
            'state=%A2',
            'zip=%z',
        ],
        'processed',
    );

    assertEquals(5, $processed);
    $locations = Location::where(['processed' => 1])->get();
    assertCount(5, $locations);

    [$records, $lookups, $processed] = $geocode->reverseBatch(
        Location::class,
        'lat',
        'lng',
        [
            'street=%n %S',
            'city=%L',
            'state=%A2',
            'zip=%z',
        ],
        'processed',
    );

    assertEquals($processed, 0);
});

it('can geocode a table', function () {
    Location::factory()->withRealAddress()->count(5)->create();

    $locations = Location::where(['processed' => 1])->get();
    assertCount(0, $locations);

    $geocode                         = new Geocoder(config('filament-google-maps.rate-limit', 150));
    [$records, $lookups, $processed] = $geocode->geocodeBatch(
        Location::class,
        'lat',
        'lng',
        'street,city,state,zip',
        'processed',
    );

    assertEquals(5, $processed);
    $locations = Location::where(['processed' => 1])->get();
    assertCount(5, $locations);
});

it('it can geocode a table twice and only process once', function () {
    Location::factory()->withRealAddress()->count(5)->create();

    $locations = Location::where(['processed' => 1])->get();
    assertCount(0, $locations);

    $geocode                         = new Geocoder(config('filament-google-maps.rate-limit', 150));
    [$records, $lookups, $processed] = $geocode->geocodeBatch(
        Location::class,
        'lat',
        'lng',
        'street,city,state,zip',
        'processed',
    );

    assertEquals(5, $processed);
    $locations = Location::where(['processed' => 1])->get();
    assertCount(5, $locations);

    [$records, $lookups, $processed] = $geocode->reverseBatch(
        Location::class,
        'lat',
        'lng',
        [
            'street=%n %S',
            'city=%L',
            'state=%A2',
            'zip=%z',
        ],
        'processed',
    );

    assertEquals($processed, 0);
});
