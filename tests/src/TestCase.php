<?php

namespace Cheesegrits\FilamentGoogleMaps\Tests;

use BladeUI\Heroicons\BladeHeroiconsServiceProvider;
use BladeUI\Icons\BladeIconsServiceProvider;
use Cheesegrits\FilamentGoogleMaps\FilamentGoogleMapsServiceProvider;
use Cheesegrits\FilamentGoogleMaps\Tests\Columns\ColumnsServiceProvider;
use Cheesegrits\FilamentGoogleMaps\Tests\Models\User;
use Filament\Actions\ActionsServiceProvider;
use Filament\FilamentServiceProvider;
use Filament\Forms\FormsServiceProvider;
use Filament\Infolists\InfolistsServiceProvider;
use Filament\Notifications\NotificationsServiceProvider;
use Filament\Support\SupportServiceProvider;
use Filament\Tables\TablesServiceProvider;
use Filament\Widgets\WidgetsServiceProvider;
use Geocoder\Laravel\Providers\GeocoderService;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;
use RyanChandler\BladeCaptureDirective\BladeCaptureDirectiveServiceProvider;

class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            FilamentGoogleMapsServiceProvider::class,
            ColumnsServiceProvider::class,
            GeocoderService::class,

            ActionsServiceProvider::class,
            BladeCaptureDirectiveServiceProvider::class,
            BladeHeroiconsServiceProvider::class,
            BladeIconsServiceProvider::class,
            FilamentServiceProvider::class,
            FormsServiceProvider::class,
            InfolistsServiceProvider::class,
            LivewireServiceProvider::class,
            NotificationsServiceProvider::class,
            SupportServiceProvider::class,
            TablesServiceProvider::class,
            WidgetsServiceProvider::class,
            AdminPanelProvider::class,
        ];
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('auth.providers.users.model', User::class);
        $app['config']->set('view.paths', array_merge(
            $app['config']->get('view.paths'),
            [__DIR__ . '/../resources/views']
        ));
        $app['config']->set('realaddress.countries.united-states-of-america', [
            'cities' => ['New York, NY', 'Los Angeles, CA', 'San Francisco, CA', 'Dallas, TX', 'Chicago, IL', 'Houston, TX', 'Phoenix, AZ', 'San Diego, CA'],
        ]);
        $app['config']->set('realaddress.rate-limiter', 100);
    }
}
