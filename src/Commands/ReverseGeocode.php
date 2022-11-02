<?php

namespace Cheesegrits\FilamentGoogleMaps\Commands;


use Cheesegrits\FilamentGoogleMaps\Helpers\GeocodeHelper;
use Filament\Support\Commands\Concerns\CanValidateInput;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ReverseGeocode extends Command
{
    use CanValidateInput;

    protected $signature = 'filament-google-maps:reverse-geocode {model?} {--lat=} {--lng=} {--fields=*} {--rate-limit=} {--verbose?}}';

    protected $description = 'Geocode a table';

    public function handle()
    {
		$prompted = false;
        $verbose = $this->option('verbose');

        $ogModelName = $modelName = (string)Str::of($this->argument('model')
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

	    $lat = $this->option('lat')
		    ?? $this->askRequired(
			    'Name of latitude element on table (e.g. `latitude`)',
			    'lat'
		    ) && $prompted = true;

	    $lng = $this->option('lng')
		    ?? $this->askRequired(
			    'Name of longitude element on table (e.g. `latitude`)',
			    'fields'
		    ) && $prompted = true;

	    $fields = $this->option('fields');

		if (empty($fields))
		{
			$prompted = true;

			$this->table(
				['Component', 'Format'],
				GeocodeHelper::getFormats()
			);
			
			$this->info('Use the table above to enter your address component mapping.');
			$this->newLine();
			$this->line('Google returns a complex set of address components.  You need to tell us how you want');
			$this->line('those components mapped on to your database fields.  We use a standard symbolic format');
			$this->line('as summarixed in the table above to extract the address components.');
			$this->newLine();
			$this->line('Each mapping should be of the form <field name>=<format symbol(s)>, for example');
			$this->line('to map (say) a street address to your `street_name` field, you would need ...');
			$this->line('street_name=%n %S');
			$this->line('... and you might also add ...');
			$this->line('city=%L');
			$this->line('state=%A2');
			$this->line('zip=%z');
			$this->line('... or just ...');
			$this->line('formatted_address=%s %S, %L, %A2, %z');
			$this->newLine();
			$this->line('You may enter as many mappings as you need, enter a blank line to continue.');


			$this->newLine();
			$this->info('Test your field mapping.');
			$this->newLine();
			$this->line('Yes.  This is complicated.  If you would like us to look up an example record from your table');
			$this->line('and show you what all those formats translate to, enter an ID here.  If not, just press enter.');
			
			$id = $this->ask('ID (primary key on table)');
			
			if (!empty($id))
			{
				$formats = GeocodeHelper::testReverseGeocode($model, $id, $lat, $lng);
				
				$this->table(
					['Symbol', 'Result'],
					$formats,
				);
			}
			
			
			$field = '';

			do
			{
				$field = $this->ask('Field mapping (e.g. city=%L), blank line to continue');
				
				if (!empty($field))
				{
					$fields[] = $field;
				}
			} while (!empty($field));
		}

        $rateLimit = (int) $this->option('rate-limit');

        while ($rateLimit > 300 || $rateLimit < 1)
        {
			$prompted = true;

            $rateLimit = (int) $this->askRequired(
                'Rate limit as API calls per minute (max 300)',
                'rate-limit'
            );
        }

        list($processed, $updated) = GeocodeHelper::batchReverseGeocode($model, $lat, $lng, $fields, $rateLimit, $verbose);

		$this->info('Results');
		$this->line('API Lookups: ' . $processed);
		$this->line('Records Updated: ' . $updated);
		
		if ($prompted)
		{

			$summary = sprintf(
				'php artisan filament-google-maps:reverse-geocode %s %s --lat=%s --lng=%s --rate-limit=%s',
				$ogModelName,
				implode(' ', array_map(fn ($field) => '--fields=' . $field, $fields)),
				$lat,
				$lng,
				$rateLimit
			);
			$this->newLine();
			$this->info('Command summary - you may wish to copy and save this somewhere!');
			$this->line($summary);
		}

        return static::SUCCESS;
    }
}
