<?php

namespace Cheesegrits\FilamentGoogleMaps\Commands;

use Filament\Support\Commands\Concerns\CanValidateInput;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ModelCode extends Command
{
    use CanValidateInput;

    protected $signature = 'filament-google-maps:model-code {model?} {--lat=} {--lng=} {--location=} ';

    protected $description = 'Geocode models';

    public function handle()
    {
        $modelName = (string) Str::of($this->argument('model')
            ?? $this->askRequired('Model (e.g. `Location` or `Maps/Dealership`)', 'model'))
            ->studly()
            ->trim('/')
            ->trim('\\')
            ->trim(' ')
            ->studly()
            ->replace('/', '\\');

        try {
            $model = new $modelName();
        }
        catch (\Throwable $e)
        {
            try
            {
                $model = new ('\\App\\Models\\' . $modelName)();
            }
            catch (\Throwable $e)
            {
                echo "Can't find class {$modelName} or \\App\\Models\\{$modelName}\n";

                return static::INVALID;
            }
        }

        $latField   = $this->option('lat')
            ?? $this->askRequired('Latitude table field name (e.g. `lat`)', 'lat');

        $lngField   = $this->option('lng')
            ?? $this->askRequired('Longitude table field name (e.g. `lat`)', 'lng');

        $locationField = $this->option('location')
            ?? $this->askRequired('Computed location attribute name (e.g. `location`)', 'location');


        $appends = $model->getAppends();

        if (!in_array($locationField, $appends))
        {
            $appends[] = $locationField;
        }

        $appendStr = implode(",\n        ", array_map(fn ($item) => "'{$item}'", $appends));
        $locationStr = Str::studly($locationField);

        echo <<<EOT
    /**
     * Insert this code in your model, overwriting any existing \$appends array (we already merged any existing
     * append attributes from your model here).
     *
     * The '{$latField}' and '{$lngField}' attributes should exist as fields in your table schema,
     * holding standard decimal latitude and longitude coordinates.
     *
     * The '{$locationField}' attribute should NOT exist in your table schema, rather it is a computed attribute,
     * which you will use as the field name for your Filament Google Maps form fields and table columns.
     *
     * You may of course strip all comments, if you don't feel verbose.
     */
    
    protected \$appends = [
        {$appendStr},
    ];
    
    /**
    * Returns the '{$latField}' and '{$lngField}' attributes as the computed '{$locationField}' attribute,
    * as a standard Google Maps style Point array with 'lat' and 'lng' attributes, JSON encoded.
    * 
    * Used by the Filament Google Maps package.
    * 
    * Requires the '{$locationField}' attribute be included in this model's \$appends array.
    * 
    * @return string
    */
    function get{$locationStr}Attribute(): string
    {
        return json_encode([
            "lat" => (float)\$this->{$latField},
            "lng" => (float)\$this->{$lngField},
        ]);
    }

    /**
    * Takes a Google style Point array of 'lat' and 'lng' values and assigns them to the
    * '{$latField}' and '{$lngField}' attributes on this model.
    * 
    * Used by the Filament Google Maps package.
    *
    * Requires the '{$locationField}' attribute be included in this model's \$appends array.
    * 
    * @param array \$location
    * @return void
    */
    function set{$locationStr}Attribute(array \$location): void
    {
        \$this->attributes['{$latField}'] = \$location['lat'];
        \$this->attributes['{$lngField}'] = \$location['lng'];
    }


EOT;

        return static::SUCCESS;
    }
}
