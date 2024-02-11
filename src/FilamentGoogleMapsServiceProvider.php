<?php

namespace Cheesegrits\FilamentGoogleMaps;

use Cheesegrits\FilamentGoogleMaps\Widgets\MapTableWidget;
use Cheesegrits\FilamentGoogleMaps\Widgets\MapWidget;
use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Assets\AssetManager;
use Filament\Support\Facades\FilamentAsset;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentGoogleMapsServiceProvider extends PackageServiceProvider
{
    protected array $widgets = [
        MapWidget::class,
        MapTableWidget::class,
    ];

    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament-google-maps')
            ->hasCommands($this->getCommands())
            ->hasConfigFile()
            ->hasRoutes(['web'])
            ->hasTranslations()
            ->hasViews();
    }

    protected function getCommands(): array
    {
        $commands = [
            Commands\ModelCode::class,
            Commands\GeocodeTable::class,
            Commands\Geocode::class,
            Commands\ReverseGeocodeTable::class,
            Commands\ReverseGeocode::class,
            Commands\MakeWidgetCommand::class,
        ];

        $aliases = [];

        foreach ($commands as $command) {
            $class = 'Cheesegrits\\FilamentGoogleMaps\\Commands\\Aliases\\' . class_basename($command);

            if (! class_exists($class)) {
                continue;
            }

            $aliases[] = $class;
        }

        return array_merge($commands, $aliases);
    }

    public function packageRegistered(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/filament-google-maps.php', 'filament-google-maps');
        $this->app->resolving(AssetManager::class, function () {
            FilamentAsset::register([
                AlpineComponent::make('filament-google-maps-geocomplete', __DIR__ . '/../dist/cheesegrits/filament-google-maps/filament-google-geocomplete.js'),
                AlpineComponent::make('filament-google-maps-field', __DIR__ . '/../dist/cheesegrits/filament-google-maps/filament-google-maps.js'),
                AlpineComponent::make('filament-google-maps-widget', __DIR__ . '/../dist/cheesegrits/filament-google-maps/filament-google-maps-widget.js'),
                AlpineComponent::make('filament-google-maps-entry', __DIR__ . '/../dist/cheesegrits/filament-google-maps/filament-google-maps-entry.js'),
                //                Js::make('filament-google-maps-field', __DIR__.'/../dist/cheesegrits/filament-google-maps/filament-google-maps.js'),
                //                Js::make('filament-google-maps-geocomplete', __DIR__.'/../dist/cheesegrits/filament-google-maps/filament-google-geocomplete.js'),
                //                Js::make('filament-google-maps-widget', __DIR__.'/../dist/cheesegrits/filament-google-maps/filament-google-maps-widget.js'),
            ], 'cheesegrits/filament-google-maps');
        });
    }
}
