<?php


use Cheesegrits\FilamentGoogleMaps\Helpers\GeocodeHelper;
use Cheesegrits\FilamentGoogleMaps\Tests\Fields\Fixtures\Resources\GeocompleteResource;
use Cheesegrits\FilamentGoogleMaps\Tests\Fields\Fixtures\Resources\GeocompleteResource\Pages\CreateGeocomplete;
use Cheesegrits\FilamentGoogleMaps\Tests\Fields\Fixtures\Resources\GeocompleteResource\Pages\EditGeocomplete;
use Cheesegrits\FilamentGoogleMaps\Tests\Fields\Fixtures\Resources\LocationResource;
use Cheesegrits\FilamentGoogleMaps\Tests\Fields\Fixtures\Resources\LocationResource\Pages\CreateLocation;
use Cheesegrits\FilamentGoogleMaps\Tests\Fields\Fixtures\Resources\LocationResource\Pages\EditLocation;
use Cheesegrits\FilamentGoogleMaps\Tests\Fields\TestCase;
use Cheesegrits\FilamentGoogleMaps\Tests\Models\Location;
use Filament\Facades\Filament;

use Illuminate\Contracts\View\View;
use Nonsapiens\RealAddressFactory\RealAddressFactory;
use function Pest\Livewire\livewire;

uses(TestCase::class);

beforeEach(function () {
	Filament::registerResources([
		LocationResource::class,
		GeocompleteResource::class,
	]);
});

it('can create geocomplete field as computed location attribute', function () {
	$f       = new RealAddressFactory();
	$address = $f->make(1, 'united-states-of-america', 'Chicago, IL')->first();
	$latLang = GeocodeHelper::geocode($address->getFormattedAddress());

	livewire(CreateLocation::class)
		->fillForm([
			'location'          => $address->getFormattedAddress(),
			'street'            => $address->getStreetAddress(),
			'city'              => $address->getLocality(),
			'state'             => $address->getAdminLevels()->get(1)->getName(),
			'zip'               => $address->getPostalCode(),
			'formatted_address' => $address->getFormattedAddress(),
		])
		->call('create')
		->assertHasNoFormErrors();

	$this->assertDatabaseHas(Location::class, [
		'lat' => $latLang['lat'],
		'lng' => $latLang['lng'],
	]);
});

it('can create geocomplete field as normal field', function () {
	$f       = new RealAddressFactory();
	$address = $f->make(1, 'united-states-of-america', 'Chicago, IL')->first();

	livewire(CreateGeocomplete::class)
		->fillForm([
			'street'            => $address->getStreetAddress(),
			'city'              => $address->getLocality(),
			'state'             => $address->getAdminLevels()->get(1)->getName(),
			'zip'               => $address->getPostalCode(),
			'formatted_address' => $address->getFormattedAddress(),
		])
		->call('create')
		->assertHasNoFormErrors();

	$this->assertDatabaseHas(Location::class, [
		'formatted_address' => $address->getFormattedAddress(),
	]);
});

it('can edit geocomplete field as computed location attribute', function () {
	$location = Location::factory()->withRealAddressAndLatLng()->create();

	livewire(EditLocation::class, [
		'record' => $location->getKey(),
	])
		->assertFormSet([
//			'location'          => $location->formatted_address,
			'street'            => $location->street,
			'city'              => $location->city,
			'state'             => $location->state,
			'zip'               => $location->zip,
			'formatted_address' => $location->formatted_address,
		]);
});

it('can edit geocomplete field as normal field', function () {
	$location = Location::factory()->withRealAddressAndLatLng()->create();

	livewire(EditGeocomplete::class, [
		'record' => $location->getKey(),
	])
		->assertFormSet([
			'street'            => $location->street,
			'city'              => $location->city,
			'state'             => $location->state,
			'zip'               => $location->zip,
			'formatted_address' => $location->formatted_address,
		]);
});