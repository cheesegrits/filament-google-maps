<?php

namespace Cheesegrits\FilamentGoogleMaps\Commands;

use Filament\Support\Commands\Concerns\CanManipulateFiles;
use Filament\Support\Commands\Concerns\CanValidateInput;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeWidgetCommand extends Command
{
    use CanManipulateFiles;
    use CanValidateInput;

    private $widgetClasses = ['MapWidget', 'MapTableWidget'];

    protected $description = 'Creates a Filament Google Maps widget class.';

    protected $signature = 'make:filament-google-maps-widget {name?} {model?} {--R|resource=} {--M|map} {--T|table} {--F|force}';

    public function handle(): int
    {
        $path              = config('filament.widgets.path', app_path('Filament/Widgets/'));
        $resourcePath      = config('filament.resources.path', app_path('Filament/Resources/'));
        $namespace         = config('filament.widgets.namespace', 'App\\Filament\\Widgets');
        $resourceNamespace = config('filament.resources.namespace', 'App\\Filament\\Resources');

        $type      = false;
        $typeMap   = $this->option('map');
        $typeTable = $this->option('table');

        if ($typeMap) {
            $type = 'map';
        } elseif ($typeTable) {
            $type = 'table';
        } else {
            $type = $this->choice(
                'Widget type (just a map, or map with integrated table',
                ['Map', 'Map & Table'],
                0,
                $maxAttempts             = null,
                $allowMultipleSelections = false
            );

            $type = $type === 'Map' ? 'map' : 'table';
        }

        $widget = (string) Str::of($this->argument('name') ?? $this->askRequired('Name (e.g. `DealershipMap`)', 'name'))
            ->trim('/')
            ->trim('\\')
            ->trim(' ')
            ->replace('/', '\\');

        $widgetClass = (string) Str::of($widget)->afterLast('\\');

        if (in_array($widgetClass, $this->widgetClasses)) {
            $this->error("Sorry, you can't call your widget any of: ".implode(', ', $this->widgetClasses));

            return static::INVALID;
        }

        $widgetNamespace = Str::of($widget)->contains('\\') ?
            (string) Str::of($widget)->beforeLast('\\') :
            '';

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
            $model     = new ('\\App\\Models\\'.$modelName)();
            $modelName = '\\App\\Models\\'.$modelName;
        } catch (\Throwable) {
            try {
                $model = new $modelName;
            } catch (\Throwable) {
                echo "Can't find class $modelName or \\App\\Models\\$modelName\n";

                return static::INVALID;
            }
        }

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $latLongFields = $modelName::getLatLngAttributes();
            /** @noinspection PhpUndefinedMethodInspection */
            $locationField = $modelName::getComputedLocation();
        } catch (\Exception $e) {
            $this->line("Can't find your model's lat and lng attributes, did you run the fgm:model-code command and paste it into your model?");

            return static::INVALID;
        }

        $resource      = null;
        $resourceClass = null;

        $resourceInput = $this->option('resource') ?? $this->ask('(Optional) Resource (e.g. `LocationResource`)');

        if ($resourceInput !== null) {
            $resource = (string) Str::of($resourceInput)
                ->studly()
                ->trim('/')
                ->trim('\\')
                ->trim(' ')
                ->replace('/', '\\');

            if (! Str::of($resource)->endsWith('Resource')) {
                $resource .= 'Resource';
            }

            $resourceClass = (string) Str::of($resource)
                ->afterLast('\\');
        }

        $view = Str::of($widget)->prepend(
            (string) Str::of($resource === null ? "{$namespace}\\" : "{$resourceNamespace}\\{$resource}\\widgets\\")
                ->replace('App\\', '')
        )
            ->replace('\\', '/')
            ->explode('/')
            ->map(fn ($segment) => Str::lower(Str::kebab($segment)))
            ->implode('.');

        $path = (string) Str::of($widget)
            ->prepend('/')
            ->prepend($resource === null ? $path : "{$resourcePath}\\{$resource}\\Widgets\\")
            ->replace('\\', '/')
            ->replace('//', '/')
            ->append('.php');

        $viewPath = resource_path(
            (string) Str::of($view)
                ->replace('.', '/')
                ->prepend('views/')
                ->append('.blade.php'),
        );

        if (! $this->option('force') && $this->checkForCollision([$path, $viewPath])) {
            return static::INVALID;
        }

        if ($type === 'table') {
            $this->copyStubToApp('MapTableWidget', $path, [
                'location'  => $locationField,
                'og-model'  => $ogModelName,
                'model'     => $modelName,
                'class'     => $widgetClass,
                'pk'        => $model->getKeyName(),
                'namespace' => filled($resource) ? "{$resourceNamespace}\\{$resource}\\Widgets".($widgetNamespace !== '' ? "\\{$widgetNamespace}" : '') : $namespace.($widgetNamespace !== '' ? "\\{$widgetNamespace}" : ''),
            ] + $latLongFields);
        } else {
            $this->copyStubToApp('MapWidget', $path, [
                'model'     => $modelName,
                'class'     => $widgetClass,
                'namespace' => filled($resource) ? "{$resourceNamespace}\\{$resource}\\Widgets".($widgetNamespace !== '' ? "\\{$widgetNamespace}" : '') : $namespace.($widgetNamespace !== '' ? "\\{$widgetNamespace}" : ''),
            ] + $latLongFields);
        }

        if ($resource !== null) {
            $this->info("Successfully created the {$widget} in your {$resourceClass} resource class.");
            $this->newLine();
            $this->info("Make sure to register the widget both in `{$resourceClass}::getWidgets()`,");
            $this->info("and in either `getHeaderWidgets()` or `getFooterWidgets()` of any `{$resourceClass}` page.");
        } else {
            $livewire   = (string) Str::of($widget)->snake();
            $widgetPath = (string) Str::of($resourceNamespace)->replace('\\', '/').'/'.$widget.'.php';
            $this->info("Your widget has been created as: $widgetPath");
            $this->newLine();
            $this->info('If you want to use it on the front end, copy/move it to somewhere in your Livewire folder, say ...');
            $this->newLine();
            $this->info('/Http/Livewire/Widgets/'.$widget.'.php');
            $this->newLine();
            $this->info('... and then invoke it from a front end Blade template like ...');
            $this->newLine();
            $this->info("@livewire('widgets.{$livewire}')");
        }

        return static::SUCCESS;
    }
}
