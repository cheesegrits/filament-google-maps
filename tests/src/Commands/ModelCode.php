<?php

use Cheesegrits\FilamentGoogleMaps\Tests\Commands\TestCase;
use Illuminate\Support\Facades\Artisan;

uses(TestCase::class);

it ('asks the right questions for the artisan model-code command', function () {

	$this->artisan('filament-google-maps:model-code')
		->expectsQuestion(
			'Model (e.g. `Location` or `Maps/Dealership`)',
			'Cheesegrits\FilamentGoogleMaps\Tests\Models\Location'
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
		);
});

it ('outputs the fillable array model-code', function () {
	$this->artisan(
		'filament-google-maps:model-code',
		[
			'model' => 'Cheesegrits/FilamentGoogleMaps/Tests/Models/Location',
			'--lat' => 'lat',
			'--lng' => 'lng',
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

it ('outputs the get attribute model-code', function () {

	$this->artisan(
			'filament-google-maps:model-code',
			[
				'model' => 'Cheesegrits/FilamentGoogleMaps/Tests/Models/Location',
				'--lat' => 'lat',
				'--lng' => 'lng',
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

it ('outputs the set attribute model-code', function () {
	$this->artisan(
		'filament-google-maps:model-code',
		[
			'model' => 'Cheesegrits/FilamentGoogleMaps/Tests/Models/Location',
			'--lat' => 'lat',
			'--lng' => 'lng',
			'--location' => 'location',
		]
	)
		->expectsOutputToContain(convertNewlines('
    function setLocationAttribute(array $location): void
    {
        $this->attributes[\'lat\'] = $location[\'lat\'];
        $this->attributes[\'lng\'] = $location[\'lng\'];
        $this->attributes[\'location\'] = json_encode($location);
    }'));
});

function convertNewlines($text)
{
	$text = implode("\n", explode("\r\n", $text));

	return $text;
}

