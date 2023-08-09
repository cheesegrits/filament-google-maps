<?php

use Cheesegrits\FilamentGoogleMaps\Tests\Commands\TestCase;

uses(TestCase::class);

it('makes a widget with the artisan make-widget command', function () {
    expect(app_path('Filament/Widgets/') . 'LocationMap.php')->not->toBeFile();

    $this->artisan('make:filament-google-maps-widget')
        ->expectsQuestion(
            'Widget type (just a map, or map with integrated table',
            'map'
        )
        ->expectsQuestion(
            'Name (e.g. `DealershipMap`)',
            'LocationMap'
        )
        ->expectsQuestion(
            'Model (e.g. `Location` or `Maps/Dealership`)',
            'Cheesegrits/FilamentGoogleMaps/Tests/Models/Location'
        )
        ->expectsQuestion(
            '(Optional) Resource (e.g. `LocationResource`)',
            ''
        );

    expect(app_path('Filament/Widgets/') . 'LocationMap.php')->toBeFile();
});

function convertNewlines($text)
{
    $text = implode("\n", explode("\r\n", $text));

    return $text;
}
