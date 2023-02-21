<?php

namespace Cheesegrits\FilamentGoogleMaps\Commands;

use Cheesegrits\FilamentGoogleMaps\Helpers\Geocoder;
use Filament\Support\Commands\Concerns\CanValidateInput;
use Illuminate\Console\Command;

class ReverseGeocode extends Command
{
    use CanValidateInput;

    protected $signature = 'filament-google-maps:reverse-geocode {--lat=} {--lng=} {--C|components}';

    protected $description = 'Geocode a single lat/lng tuple';

    public function handle()
    {
        $withComponents = $this->option('components');

        $lat = $this->option('lat');

        if (empty($lat)) {
            $prompted = true;

            $lat = $this->askRequired(
                'Latitude (e.g. `34.38461`)',
                'lat'
            );
        }

        $lng = $this->option('lng');

        if (empty($lng)) {
            $prompted = true;

            $lng = $this->askRequired(
                'Longitude (e.g. `-83.185639`)',
                'lng'
            );
        }

        $geocoder = new Geocoder();

        if ($formats = $geocoder->testReverse($lat, $lng, $withComponents)) {
            $this->table(
                ['Symbol', 'Result'],
                $formats,
            );
        }

        return static::SUCCESS;
    }
}
