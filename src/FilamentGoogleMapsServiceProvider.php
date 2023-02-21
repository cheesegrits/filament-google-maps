<?php

namespace Cheesegrits\FilamentGoogleMaps;

use Cheesegrits\FilamentGoogleMaps\Widgets\MapTableWidget;
use Cheesegrits\FilamentGoogleMaps\Widgets\MapWidget;
use Filament\PluginServiceProvider;
use Spatie\LaravelPackageTools\Package;

class FilamentGoogleMapsServiceProvider extends PluginServiceProvider
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
            $class = 'Cheesegrits\\FilamentGoogleMaps\\Commands\\Aliases\\'.class_basename($command);

            if (! class_exists($class)) {
                continue;
            }

            $aliases[] = $class;
        }

        return array_merge($commands, $aliases);
    }

    public function packageRegistered(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/filament-google-maps.php', 'filament-google-maps');
    }
}
