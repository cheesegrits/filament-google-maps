<?php

namespace Cheesegrits\FilamentGoogleMaps\Tests\Database\Factories;

use Cheesegrits\FilamentGoogleMaps\Tests\Models\Customer;
use Cheesegrits\FilamentGoogleMaps\Tests\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'name'        => $this->faker->name(),
            'location_id' => Location::inRandomOrder()->first()->id,
            'created_at'  => Carbon::now(),
            'updated_at'  => Carbon::now(),
        ];
    }

    public function location(Location $location)
    {
        return $this->state(function (array $attributes) use ($location) {
            return [
                'location_id' => $location->id,
            ];
        });
    }
}
