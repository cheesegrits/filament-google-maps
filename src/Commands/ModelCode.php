<?php

namespace Cheesegrits\FilamentGoogleMaps\Commands;

use Filament\Support\Commands\Concerns\CanValidateInput;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ModelCode extends Command
{
    use CanValidateInput;

    protected $signature = 'filament-google-maps:model-code {model?} {--lat=} {--lng=} {--location=} ';

    protected $description = 'Produce computed attribute code for a model to work with Filament Google Maps';

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


	    $guardedStr = '';
		$guarded = $model->getGuarded();

		if (in_array($locationField, $guarded))
	    {
		    unset($guarded[array_search($locationField, $guarded)]);
		    $guardedAttributes = implode(",\n        ", array_map(fn ($item) => "'{$item}'", $guarded));
		    $guardedStr = <<<EOT

    protected \$guarded = [
        {$guardedAttributes},
    ];
EOT;
	    }

		$fillableStr = '';
        $fillable = $model->getFillable();

        if (count($fillable) > 0 && !in_array($locationField, $fillable))
        {
            $fillable[] = $locationField;
	        $fillableAttributes = implode(",\n        ", array_map(fn ($item) => "'{$item}'", $fillable));
			$fillableStr = <<<EOT

    protected \$fillable = [
        {$fillableAttributes},
    ];
EOT;
        }

	    $appendsStr = '';
	    $appends = $model->getAppends();

	    if (!in_array($locationField, $appends))
	    {
		    $appends[] = $locationField;
		    $appendsAttributes = implode(",\n        ", array_map(fn ($item) => "'{$item}'", $appends));
		    $appendsStr = <<<EOT
    protected \$appends = [
        {$appendsAttributes},
    ];
EOT;
	    }

        $locationStr = Str::studly($locationField);
	    $modelCode = '';

		if (!empty($guardedStr) || !empty($fillableStr) || !empty($appendsStr))
		{
			$modelCode .= <<<EOT
    /**
     * REPLACE THE FOLLOWING ARRAYS IN YOUR MODEL
     *
     * Replace your existing \$fillable and/or \$guarded and/or \$appends arrays with these - we already merged
     * any existing attributes from your model, and only included the one(s) that need changing.
     */
{$fillableStr}
{$guardedStr}
{$appendsStr}

EOT;
		}

        $modelCode .= <<<EOT
    /**
     * ADD THE FOLLOWING METHODS TO YOUR MODEL
     *
     * The '{$latField}' and '{$lngField}' attributes should exist as fields in your table schema,
     * holding standard decimal latitude and longitude coordinates.
     *
     * The '{$locationField}' attribute should NOT exist in your table schema, rather it is a computed attribute,
     * which you will use as the field name for your Filament Google Maps form fields and table columns.
     *
     * You may of course strip all comments, if you don't feel verbose.
     */
    
    /**
    * Returns the '{$latField}' and '{$lngField}' attributes as the computed '{$locationField}' attribute,
    * as a standard Google Maps style Point array with 'lat' and 'lng' attributes.
    * 
    * Used by the Filament Google Maps package.
    * 
    * Requires the '{$locationField}' attribute be included in this model's \$fillable array.
    * 
    * @return array
    */
    function get{$locationStr}Attribute(): array
    {
        return [
            "lat" => (float)\$this->{$latField},
            "lng" => (float)\$this->{$lngField},
        ];
    }

    /**
    * Takes a Google style Point array of 'lat' and 'lng' values and assigns them to the
    * '{$latField}' and '{$lngField}' attributes on this model.
    * 
    * Used by the Filament Google Maps package.
    *
    * Requires the '{$locationField}' attribute be included in this model's \$fillable array.
    * 
    * @param ?array \$location
    * @return void
    */
    function set{$locationStr}Attribute(?array \$location): void
    {
        if (is_array(\$location))
        {
            \$this->attributes['{$latField}'] = \$location['lat'];
            \$this->attributes['{$lngField}'] = \$location['lng'];
            \$this->attributes['{$locationField}'] = json_encode(\$location);
        }
    }
    
    /**
     * Get the lat and lng attribute/field names used on this table
     *
     * Used by the Filament Google Maps package.
     *
     * @return string[]
     */
    public static function getLatLngAttributes(): array
    {
        return [
            'lat' => '{$latField}',
            'lng' => '{$lngField}',
        ];
    }
    
    /**
     * Get the name of the computed location attribute
     *
     * Used by the Filament Google Maps package.
     * 
     * @return string
     */
    public static function getComputedLocation(): string
    {
        return '{$locationField}';
    }


EOT;

		$this->line($modelCode);

        return static::SUCCESS;
    }
}
