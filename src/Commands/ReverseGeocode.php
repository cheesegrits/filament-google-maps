<?php

namespace Cheesegrits\FilamentGoogleMaps\Commands;

use Cheesegrits\FilamentGoogleMaps\Helpers\Geocoder;
use Illuminate\Console\Command;

use function Laravel\Prompts\text;

class ReverseGeocode extends Command
{
    protected $signature = 'filament-google-maps:reverse-geocode {--lat=} {--lng=} {--C|components}';

    protected $description = 'Geocode a single lat/lng tuple';

    public function handle()
    {
        $withComponents = $this->option('components');

        $lat = $this->option('lat');

        if (empty($lat)) {
            $prompted = true;

            $lat = text(
                label: 'Latitude (e.g. `34.38461`)',
                placeholder: 'lat',
                required: true
            );
        }

        $lng = $this->option('lng');

        if (empty($lng)) {
            $prompted = true;

            $lng = text(
                label: 'Longitude (e.g. `-83.185639`)',
                placeholder: 'lng',
                required: true
            );
        }

        $geocoder = new Geocoder;

        if ($formats = $geocoder->testReverse($lat, $lng, $withComponents)) {
            $this->table(
                ['Symbol', 'Result'],
                $formats,
            );
        }

        return static::SUCCESS;
    }
}
