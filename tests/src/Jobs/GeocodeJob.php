<?php

namespace Cheesegrits\FilamentGoogleMaps\Tests\Jobs;

use Cheesegrits\FilamentGoogleMaps\Helpers\Geocoder;
use Cheesegrits\FilamentGoogleMaps\Tests\Models\Location;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * An example queueable job for running batch geocoding of a table, see ...
 *
 * https://github.com/cheesegrits/filament-google-maps#batch-commands-1
 */
class GeocodeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $limit;

    protected int $rateLimit;

    public function __construct(?int $limit, ?int $rateLimit)
    {
        $this->limit     = $limit ?? 1000;
        $this->rateLimit = $rateLimit ?? 50;
    }

    public function handle()
    {
        $geocoder = new Geocoder($this->rateLimit);

        $results = $geocoder->geocodeBatch(
            Location::class,
            'lat',
            'lng',
            'street,city,state,zip',
            'processed',
            $this->limit,
            true
        );
    }
}
