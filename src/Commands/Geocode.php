<?php

namespace Cheesegrits\FilamentGoogleMaps\Commands;


use Cheesegrits\FilamentGoogleMaps\Helpers\GeocodeHelper;
use Filament\Support\Commands\Concerns\CanValidateInput;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Geocode extends Command
{
    use CanValidateInput;

    protected $signature = 'filament-google-maps:geocode {model?} {lat?} {lng?} {--fields=} {--rate-limit=} {--sleep-time=} {--verbose?}}';

    protected $description = 'Geocode a table';

    public function handle()
    {
        $verbose = $this->option('verbose');

        $modelName = (string)Str::of($this->argument('model')
            ?? $this->askRequired('Model (e.g. `Location` or `Maps/Dealership`)', 'model'))
            ->studly()
            ->trim('/')
            ->trim('\\')
            ->trim(' ')
            ->studly()
            ->replace('/', '\\');

        try
        {
            $model = new $modelName();
        } catch (\Throwable $e)
        {
            try
            {
                $model = new ('\\App\\Models\\' . $modelName)();
            } catch (\Throwable $e)
            {
                echo "Can't find class {$modelName} or \\App\\Models\\{$modelName}\n";

                return static::INVALID;
            }
        }

        $fields = $this->option('fields')
            ?? $this->askRequired(
                'Comma separated list oif fields to concatenate for the address (e.g. `address,city,state`)',
                'fields'
            );

        $lat = $this->argument('lat')
            ?? $this->askRequired(
                'Name of latitude element on table (e.g. `latitude`)',
                'lat'
            );

        $lng = $this->argument('lat')
            ?? $this->askRequired(
                'Name of latitude element on table (e.g. `latitude`)',
                'fields'
            );

        $rateLimit = (int) $this->option('rate-limit');

        while ($rateLimit > 300 || $rateLimit < 1)
        {
            $rateLimit = (int) $this->askRequired(
                'Rate limit as API calls per minute (max 300)',
                'rate-limit'
            );
        }

        $sleepTime = (int)  $this->option('sleep-time');

        while ($sleepTime > 60 || $sleepTime < 1)
        {
            $sleepTime = (int) $this->askRequired(
                    'Time in seconds to sleep if rate-limit is reached (min 1, max 60)',
                    'sleep-time'
                );
        }

        GeocodeHelper::batchGeocode($model, $lat, $lng, $fields, $rateLimit, $sleepTime, $verbose);

        return static::SUCCESS;
    }
}
