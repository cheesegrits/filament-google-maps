<?php

namespace Cheesegrits\FilamentGoogleMaps\Commands;

use Cheesegrits\FilamentGoogleMaps\Helpers\Geocoder;
use Filament\Support\Commands\Concerns\CanValidateInput;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Throwable;

class ReverseGeocodeTable extends Command
{
    use CanValidateInput;

    protected $signature = 'filament-google-maps:reverse-geocode-table {model?} {--lat=} {--lng=} {--fields=*} {--processed=} {--rate-limit=} {--verbose?}}';

    protected $description = 'Reverse geocode a table';

    public function handle(): int
    {
        $prompted = false;
        $verbose  = $this->option('verbose');

        $ogModelName = $modelName = (string) Str::of($this->argument('model')
            ?? $this->askRequired('Model (e.g. `Location` or `Maps/Dealership`)', 'model'))
            ->studly()
            ->trim('/')
            ->trim('\\')
            ->trim(' ')
            ->studly()
            ->replace('/', '\\');

        try {
            /** @noinspection PhpUnusedLocalVariableInspection */
            $model = new $modelName();
        } catch (Throwable $e) {
            try {
                /** @noinspection PhpUnusedLocalVariableInspection */
                $model     = new ('\\App\\Models\\'.$modelName)();
                $modelName = '\\App\\Models\\'.$modelName;
            } catch (Throwable $e) {
                echo "Can't find class $modelName or \\App\\Models\\$modelName\n";

                return static::INVALID;
            }
        }

        $rateLimit = (int) $this->option('rate-limit');

        while ($rateLimit > 300 || $rateLimit < 1) {
            $prompted = true;

            $rateLimit = (int) $this->askRequired(
                'Rate limit as API calls per minute (max 300)',
                'rate-limit',
                config('filament-google-maps.rate-limit', 150),
            );
        }

        $geocoder = new Geocoder($rateLimit);

        $lat = $this->option('lat');

        if (empty($lat)) {
            $prompted = true;

            $lat = $this->askRequired(
                'Name of latitude element on table (e.g. `latitude`)',
                'lat'
            );
        }

        $lng = $this->option('lng');

        if (empty($lng)) {
            $prompted = true;

            $lng = $this->askRequired(
                'Name of longitude element on table (e.g. `longitude`)',
                'lng'
            );
        }

        $processedField = $this->option('processed');

        if (empty($processedField)) {
            $prompted = true;

            $processedField = $this->ask(
                'Optional name of field to set to 1 when record is processed (e.g. `processed`)',
            );
        }

        if (empty($processedField) || $processedField === 'no-processed-field') {
            $processedField = null;
        }

        $fields = $this->option('fields');

        if (empty($fields)) {
            $prompted = true;

            $this->table(
                ['Component', 'Format'],
                Geocoder::getFormats()
            );

            $this->info('Use the table above to enter your address component mapping.');
            $this->newLine();
            $this->line('Google returns a complex set of address components.  You need to tell us how you want');
            $this->line('those components mapped on to your database fields.  We use a standard symbolic format');
            $this->line('as summarized in the table above to extract the address components.');
            $this->newLine();
            $this->line('Each mapping should be of the form <field name>=<format symbol(s)>, for example');
            $this->line('to map (say) a street address to your `street_name` field, you would need ...');
            $this->newLine();
            $this->line('street_name=%n %S');
            $this->newLine();
            $this->line('... and you might also add ...');
            $this->newLine();
            $this->line('city=%L');
            $this->line('state=%A2');
            $this->line('zip=%z');
            $this->newLine();
            $this->line('... or just ...');
            $this->newLine();
            $this->line('formatted_address=%s %S, %L, %A2, %z');
            $this->newLine();
            $this->line('You may enter as many mappings as you need, enter a blank line to continue.');

            $this->newLine();
            $this->info('Test your field mapping.');
            $this->newLine();
            $this->line('Yes.  This is complicated.  If you would like us to look up an example record from your table');
            $this->line('and show you what all those formats translate to, enter an ID here.  If not, just press enter.');

            $id = $this->ask('ID (primary key on table)');

            if (! empty($id)) {
                $formats = $geocoder->testReverseModel($modelName, $id, $lat, $lng);

                $this->table(
                    ['Symbol', 'Result'],
                    $formats,
                );
            }

            do {
                $field = $this->ask('Field mapping (e.g. city=%L), blank line to continue');

                if (! empty($field)) {
                    $fields[] = $field;
                }
            } while (! empty($field));
        }

        [$records, $processed, $updated] = $geocoder->reverseBatch($modelName, $lat, $lng, $fields, $processedField, null, $verbose);

        $this->info('Results');
        $this->line('API Lookups: '.$processed);
        $this->line('Records Updated: '.$updated);

        if ($prompted) {
            $summary = sprintf(
                'php artisan filament-google-maps:reverse-geocode %s %s --lat=%s --lng=%s --processed=%s',
                $ogModelName,
                implode(' ', array_map(fn ($field) => '--fields="'.$field.'"', $fields)),
                $lat,
                $lng,
                $processedField ? $processedField : 'no-processed-field',
                $rateLimit
            );
            $this->newLine();
            $this->info('Command summary - you may wish to copy and save this somewhere!');
            $this->line($summary);
        }

        return static::SUCCESS;
    }
}
