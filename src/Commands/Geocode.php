<?php

namespace Cheesegrits\FilamentGoogleMaps\Commands;

use Cheesegrits\FilamentGoogleMaps\Helpers\Geocoder;
use Filament\Support\Commands\Concerns\CanValidateInput;
use Illuminate\Console\Command;

class Geocode extends Command
{
    use CanValidateInput;

    protected $signature = 'filament-google-maps:geocode {--address=} {--A|array} {--C|command} {--G|args}';

    protected $description = 'Geocode a single address';

    public function handle()
    {
        $array   = $this->option('array');
        $command = $this->option('command');
        $args    = $this->option('args');

        $address = $this->option('address');

        if (empty($address)) {
            $address = $this->askRequired(
                'Enter an address to geocode (e.g. `123 Some Street, Mapville, TN 12345`)',
                'address'
            );
        }

        $geocoder = new Geocoder();

        if ($response = $geocoder->geocode($address)) {
            $this->line('lat: '.$response['lat']);
            $this->line('lng: '.$response['lng']);

            if ($array) {
                $this->newLine();
                $this->line('[');
                $this->line("    'lat' => ".$response['lat']);
                $this->line("    'lng' => ".$response['lng']);
                $this->line('[');
            }

            if ($args) {
                $this->newLine();
                $this->line('--lat='.$response['lat'].' --lng='.$response['lng']);
            }

            if ($command) {
                $this->newLine();
                $this->line('php artisan filament-google-maps:reverse-geocode --lat='.$response['lat'].' --lng='.$response['lng']);
            }
        }

        return static::SUCCESS;
    }
}
