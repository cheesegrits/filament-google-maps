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

	protected $signature = 'make:filament-google-maps-widget {name?} {model?} {--R|resource=} {--M|map} {--T|map-table}  {--F|force}';

	public function handle(): int
	{
		$path = config('filament.widgets.path', app_path('Filament/Widgets/'));
		$resourcePath = config('filament.resources.path', app_path('Filament/Resources/'));
		$namespace = config('filament.widgets.namespace', 'App\\Filament\\Widgets');
		$resourceNamespace = config('filament.resources.namespace', 'App\\Filament\\Resources');

		$widget = (string) Str::of($this->argument('name') ?? $this->askRequired('Name (e.g. `DealershipMap`)', 'name'))
			->trim('/')
			->trim('\\')
			->trim(' ')
			->replace('/', '\\');

		$widgetClass = (string) Str::of($widget)->afterLast('\\');

		if (in_array($widgetClass, $this->widgetClasses))
		{
			$this->error("Sorry, you can't call your widget any of: " . implode(', ', $this->widgetClasses));

			return static::INVALID;
		}

		$widgetNamespace = Str::of($widget)->contains('\\') ?
			(string) Str::of($widget)->beforeLast('\\') :
			'';

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
			/** @noinspection PhpUnusedLocalVariableInspection */
			$model     = new $modelName();
		}
		catch (\Throwable $e)
		{
			try
			{
				/** @noinspection PhpUnusedLocalVariableInspection */
				$model     = new ('\\App\\Models\\' . $modelName)();
				$modelName = '\\App\\Models\\' . $modelName;
			}
			catch (\Throwable $e)
			{
				echo "Can't find class $modelName or \\App\\Models\\$modelName\n";

				return static::INVALID;
			}
		}

		try
		{
			$latLongFields = $modelName::getLatLngAttributes();
			$locationField = $modelName::getComputedLocation();
		}
		catch (\Exception $e)
		{
			$this->line("Can't find your model's lat and lng attributes, did you run the fgm:model-code command and paste it into your model?");

			return static::INVALID;
		}

		$resource = null;
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

		if (! $this->option('force') && $this->checkForCollision([
				$path,
				($this->option('map') || $this->option('map-table')) ?: $viewPath,
			])) {
			return static::INVALID;
		}

		if ($this->option('map-table')) {
			$this->copyStubToApp('MapTableWidget', $path, [
					'location' => $locationField,
					'og-model' => $ogModelName,
					'model' => $modelName,
					'class' => $widgetClass,
					'namespace' => filled($resource) ? "{$resourceNamespace}\\{$resource}\\Widgets" . ($widgetNamespace !== '' ? "\\{$widgetNamespace}" : '') : $namespace . ($widgetNamespace !== '' ? "\\{$widgetNamespace}" : ''),
				] + $latLongFields);
		}
		else {
			$this->copyStubToApp('MapWidget', $path, [
					'model' => $modelName,
					'class' => $widgetClass,
					'namespace' => filled($resource) ? "{$resourceNamespace}\\{$resource}\\Widgets" . ($widgetNamespace !== '' ? "\\{$widgetNamespace}" : '') : $namespace . ($widgetNamespace !== '' ? "\\{$widgetNamespace}" : ''),
				] + $latLongFields);
		}

		$this->info("Successfully created {$widget}!");

		if ($resource !== null) {
			$this->info("Make sure to register the widget in `{$resourceClass}::getWidgets()`, and then again in `getHeaderWidgets()` or `getFooterWidgets()` of any `{$resourceClass}` page.");
		}

		return static::SUCCESS;
	}
}
