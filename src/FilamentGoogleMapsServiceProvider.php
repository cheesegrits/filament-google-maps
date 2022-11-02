<?php

namespace Cheesegrits\FilamentGoogleMaps;

use Cheesegrits\FilamentGoogleMaps\Controllers\FilamentGoogleMapAssets;
use Cheesegrits\FilamentGoogleMaps\Widgets\FilamentGoogleMapsWidget;
use Filament\PluginServiceProvider;
use Illuminate\Support\Facades\Route;
use Spatie\LaravelPackageTools\Package;

class FilamentGoogleMapsServiceProvider extends PluginServiceProvider
{
    protected array $widgets = [
        FilamentGoogleMapsWidget::class,
    ];

    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament-google-maps')
            ->hasCommands($this->getCommands())
            ->hasConfigFile()
            ->hasRoutes(['web'])
//            ->hasTranslations()
            ->hasViews();
    }

    protected function getCommands(): array
    {
        return [
            Commands\ModelCode::class,
            Commands\Geocode::class,
            Commands\ReverseGeocode::class,
        ];
    }

    public function packageRegistered(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/filament-google-maps.php', 'filament-google-maps');
    }
}
