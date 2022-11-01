<?php

namespace Cheesegrits\FilamentGoogleMaps\Commands;

use App\Support\Helpers\IrwinHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class Geocode extends Command
{
    protected $signature = 'filament-google-maps:geocode {--batch=} {--rate-limit=} {--sleep-time=} {--v}}';

    protected $description = 'Geocode models';

    public function handle()
    {
        $full = $this->option('full');
        $batch = $this->option('batch');
        $rate = $this->option('rate-limit');
        $sleep = $this->option('sleep-time');
        $verbose = $this->option('v');

        Log::channel('irwin')->info("Irwin job dispatched");

        IrwinHelper::getIrwin($batch ?? 100, $rate ?? 28800, $sleep ?? 10, $verbose, !$full);

        return 0;
    }
}
