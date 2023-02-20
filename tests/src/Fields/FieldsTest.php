<?php

use Cheesegrits\FilamentGoogleMaps\Fields\Geocomplete;
use Cheesegrits\FilamentGoogleMaps\Tests\Fields\Fixtures\Livewire;
use Cheesegrits\FilamentGoogleMaps\Tests\Models\Location;
use Cheesegrits\FilamentGoogleMaps\Tests\TestCase;
use Illuminate\Contracts\View\View;
use function Pest\Livewire\livewire;

uses(TestCase::class);

//it('can create form with geocode field', function () {
//	livewire(TestComponentWithGeocomplete::class)
//		->assertFormExists();
//});

it('can load a record with geocodeOnLoad', function () {
    $location = Location::factory()->withRealAddressAndLatLng()->create();

    livewire(TestComponentWithGeocodeOnLoad::class, [
        'id' => $location->getKey(),
    ])
        ->assertFormSet([
            'lat'               => round($location->lat, 8),
            'lng'               => round($location->lng, 8),
            'location'          => $location->formatted_address,
            'street'            => $location->street,
            'city'              => $location->city,
            'state'             => $location->state,
            'zip'               => $location->zip,
            'formatted_address' => $location->formatted_address,
        ]);
});

it('can load a record without geocodeOnLoad', function () {
    $location = Location::factory()->create();

    livewire(TestComponentWithoutGeocodeOnLoad::class, [
        'id' => $location->getKey(),
    ])
        ->assertFormSet([
            'lat'               => $location->lat,
            'lng'               => $location->lng,
            'location'          => '',
            'street'            => $location->street,
            'city'              => $location->city,
            'state'             => $location->state,
            'zip'               => $location->zip,
            'formatted_address' => $location->formatted_address,
        ]);
});

it('can save a record with isLocation', function () {
    $location    = Location::factory()->create();
    $newLocation = Location::factory()->make();

    livewire(TestComponentWithoutGeocodeOnLoad::class, [
        'id' => $location->getKey(),
    ])
        ->assertFormSet([
            'lat'               => $location->lat,
            'lng'               => $location->lng,
            'location'          => '',
            'street'            => $location->street,
            'city'              => $location->city,
            'state'             => $location->state,
            'zip'               => $location->zip,
            'formatted_address' => $location->formatted_address,
        ])
        ->fillForm([
            'location' => [
                'lat' => $newLocation->lat,
                'lng' => $newLocation->lng,
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($location->refresh())
        ->lat->toBe((string) $newLocation->lat)
        ->lng->toBe((string) $newLocation->lng);
});

class TestComponentWithGeocodeOnLoad extends Livewire
{
    public Location $location;

    public $data;

    public function mount($id): void
    {
        $this->record = $this->location = Location::find($id);
        $this->form->fill(
            $this->location->toArray()
        );
    }

    public function getFormSchema(): array
    {
        return [
            Geocomplete::make('location')
                ->isLocation()
                ->geocodeOnLoad(),
        ];
    }

    protected function getFormModel(): Location
    {
        return $this->location;
    }

    protected function getFormStatePath(): string
    {
        return 'data';
    }

    public function save()
    {
        $this->location->update(
            $this->form->getState(),
        );
    }

    public function render(): View
    {
        return view('forms.fixtures.form');
    }
}

class TestComponentWithoutGeocodeOnLoad extends Livewire
{
    public Location $location;

    public $data;

    public function mount($id): void
    {
        $this->record = $this->location = Location::find($id);
        $this->form->fill(
            $this->location->toArray()
        );
    }

    public function getFormSchema(): array
    {
        return [
            Geocomplete::make('location')
                ->isLocation(),
        ];
    }

    protected function getFormModel(): Location
    {
        return $this->location;
    }

    protected function getFormStatePath(): string
    {
        return 'data';
    }

    public function save()
    {
        $this->location->update(
            $this->form->getState(),
        );
    }

    public function render(): View
    {
        return view('forms.fixtures.form');
    }
}
