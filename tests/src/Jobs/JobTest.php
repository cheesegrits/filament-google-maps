<?php

use Cheesegrits\FilamentGoogleMaps\Tests\Commands\TestCase;
use Cheesegrits\FilamentGoogleMaps\Tests\Jobs\GeocodeJob;
//use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use TiMacDonald\Log\LogEntry;
use TiMacDonald\Log\LogFake;

uses(TestCase::class);

it('can run a geocode job', function () {
    Log::swap(new LogFake);

    GeocodeJob::dispatch(50, 50);

    Log::channel(config('filament-google-maps.log.channel'))->assertLogged(
        fn (LogEntry $log) => $log->level === 'info' && Str::startsWith($log->message, 'geocodeBatch')
    );
});
