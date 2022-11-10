<?php

namespace Cheesegrits\FilamentGoogleMaps\Tests;

use BladeUI\Heroicons\BladeHeroiconsServiceProvider;
use BladeUI\Icons\BladeIconsServiceProvider;
use Cheesegrits\FilamentGoogleMaps\Tests\Models\User;
use Filament\FilamentServiceProvider;
use Filament\Forms\FormsServiceProvider;
use Filament\Support\SupportServiceProvider;
use Filament\Tables\TablesServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Cheesegrits\FilamentGoogleMaps\FilamentGoogleMapsServiceProvider;
use Livewire\LivewireServiceProvider;
use Cheesegrits\FilamentGoogleMaps\Tests\Columns\ColumnsServiceProvider;
use Geocoder\Laravel\Providers\GeocoderService;


class TestCase extends BaseTestCase
{
	protected function getPackageProviders($app): array
	{
		return [
			FilamentGoogleMapsServiceProvider::class,
			LivewireServiceProvider::class,
			ColumnsServiceProvider::class,
			FilamentServiceProvider::class,
			TablesServiceProvider::class,
			SupportServiceProvider::class,
			BladeHeroiconsServiceProvider::class,
			BladeIconsServiceProvider::class,
			FormsServiceProvider::class,
			GeocoderService::class,
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
			[__DIR__ . '/../resources/views'],
		));
		$app['config']->set('realaddress.countries.united-states-of-america', [
			'cities' => ['New York, NY', 'Los Angeles, CA', 'San Francisco, CA', 'Dallas, TX', 'Chicago, IL', 'Houston, TX', 'Phoenix, AZ', 'San Diego, CA'],
		]);
		$app['config']->set('realaddress.rate-limiter', 100);
	}
}