<?php

use Cheesegrits\FilamentGoogleMaps\Tests\Commands\TestCase;
use Illuminate\Support\Facades\Artisan;

uses(TestCase::class);

it('asks the right questions for the artisan model-code command', function () {
    $this->artisan('filament-google-maps:model-code')
        ->expectsQuestion(
            'Model (e.g. `Location` or `Maps/Dealership`)',
            'Cheesegrits/FilamentGoogleMaps/Tests/Models/Location'
        )
        ->expectsQuestion(
            'Latitude table field name (e.g. `lat`)',
            'lat'
        )
        ->expectsQuestion(
            'Longitude table field name (e.g. `lat`)',
            'lng'
        )
        ->expectsQuestion(
            'Computed location attribute name (e.g. `location`)',
            'lng'
        )
        ->expectsQuestion(
            'Include comments in the code?',
            'no'
        );
});

it('outputs the appends array model-code', function () {
    $this->artisan(
        'filament-google-maps:model-code',
        [
            'model'      => 'Cheesegrits/FilamentGoogleMaps/Tests/Models/LocationFillable',
            '--lat'      => 'lat',
            '--lng'      => 'lng',
            '--location' => 'location',
        ]
    )
        ->expectsOutputToContain(convertNewlines('
    protected $appends = [
        \'location\',
    ];'));
});

it('outputs the fillable array model-code', function () {
    $this->artisan(
        'filament-google-maps:model-code',
        [
            'model'      => 'Cheesegrits/FilamentGoogleMaps/Tests/Models/LocationFillable',
            '--lat'      => 'lat',
            '--lng'      => 'lng',
            '--location' => 'location',
        ]
    )
        ->expectsOutputToContain(convertNewlines('
    protected $fillable = [
        \'name\',
        \'lat\',
        \'lng\',
        \'street\',
        \'city\',
        \'state\',
        \'zip\',
        \'formatted_address\',
        \'processed\',
        \'location\',
    ];'));
});

it('outputs the guarded array model-code', function () {
    $this->artisan(
        'filament-google-maps:model-code',
        [
            'model'      => 'Cheesegrits/FilamentGoogleMaps/Tests\Models/LocationGuarded',
            '--lat'      => 'lat',
            '--lng'      => 'lng',
            '--location' => 'location',
        ]
    )
        ->expectsOutputToContain(convertNewlines('
    protected $guarded = [
        \'id\',
    ];'));
});

it('outputs the get attribute model-code', function () {
    $this->artisan(
        'filament-google-maps:model-code',
        [
            'model'      => 'Cheesegrits/FilamentGoogleMaps/Tests/Models/Location',
            '--lat'      => 'lat',
            '--lng'      => 'lng',
            '--location' => 'location',
        ]
    )
        ->expectsOutputToContain(convertNewlines('
    function getLocationAttribute(): array
    {
        return [
            "lat" => (float)$this->lat,
            "lng" => (float)$this->lng,
        ];
    }'));
});

it('outputs the set attribute model-code', function () {
    //	$this->withoutMockingConsoleOutput()->artisan(
    //		'filament-google-maps:model-code',
    //		[
    //			'model'      => 'Cheesegrits/FilamentGoogleMaps/Tests/Models/LocationFillable',
    //			'--lat'      => 'lat',
    //			'--lng'      => 'lng',
    //			'--location' => 'location',
    //			'--terse'
    //		]
    //	);
    //
    //	$result = Artisan::output();
    $this->artisan(
        'filament-google-maps:model-code',
        [
            'model'      => 'Cheesegrits/FilamentGoogleMaps/Tests/Models/LocationFillable',
            '--lat'      => 'lat',
            '--lng'      => 'lng',
            '--location' => 'location',
            '--terse',
        ]
    )->expectsOutputToContain(convertNewlines('
    function setLocationAttribute(?array $location): void
    {
        if (is_array($location))
        {
            $this->attributes[\'lat\'] = $location[\'lat\'];
            $this->attributes[\'lng\'] = $location[\'lng\'];
            unset($this->attributes[\'location\']);
        }
    }'));
});

it('outputs the get lat lng model-code', function () {
    $this->artisan(
        'filament-google-maps:model-code',
        [
            'model'      => 'Cheesegrits/FilamentGoogleMaps/Tests/Models/LocationFillable',
            '--lat'      => 'lat',
            '--lng'      => 'lng',
            '--location' => 'location',
        ]
    )
        ->expectsOutputToContain(convertNewlines('
    public static function getLatLngAttributes(): array
    {
        return [
            \'lat\' => \'lat\',
            \'lng\' => \'lng\',
        ];
    }'));
});

it('outputs the get computed location model-code', function () {
    $this->artisan(
        'filament-google-maps:model-code',
        [
            'model'      => 'Cheesegrits/FilamentGoogleMaps/Tests/Models/LocationFillable',
            '--lat'      => 'lat',
            '--lng'      => 'lng',
            '--location' => 'location',
        ]
    )
        ->expectsOutputToContain(convertNewlines('
    public static function getComputedLocation(): string
    {
        return \'location\';
    }'));
});

function convertNewlines($text)
{
    $text = implode("\n", explode("\r\n", $text));

    return $text;
}
