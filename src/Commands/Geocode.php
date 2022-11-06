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

    protected $signature = 'filament-google-maps:geocode {model?} {--lat=} {--lng=} {--fields=} {--rate-limit=} {--verbose?}}';

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
			$modelName .= '::class';
        } catch (\Throwable $e)
        {
            try
            {
                $model = new ('\\App\\Models\\' . $modelName)();
	            $modelName = '\\App\\Models\\' . $modelName . '::class';
            } catch (\Throwable $e)
            {
                echo "Can't find class {$modelName} or \\App\\Models\\{$modelName}\n";

                return static::INVALID;
            }
        }

        $fields = $this->option('fields')
            ?? $this->askRequired(
                'Comma separated list of fields to concatenate for the address (e.g. `address,city,state`)',
                'fields'
            );

        $lat = $this->option('lat')
            ?? $this->askRequired(
                'Name of latitude element on table (e.g. `latitude`)',
                'lat'
            );

        $lng = $this->option('lng')
            ?? $this->askRequired(
                'Name of longitude element on table (e.g. `longitude`)',
                'fields'
            );

        $rateLimit = (int) $this->option('rate-limit');

        while ($rateLimit > 300 || $rateLimit < 1)
        {
            $rateLimit = (int) $this->askRequired(
                'Rate limit as API calls per minute (max 300)',
                'rate-limit',
	                '150',
            );
        }

        GeocodeHelper::batchGeocode($model, $lat, $lng, $fields, $rateLimit, $verbose);

        return static::SUCCESS;
    }
}
