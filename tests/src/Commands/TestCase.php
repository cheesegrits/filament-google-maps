<?php

namespace Cheesegrits\FilamentGoogleMaps\Tests\Commands;

use Cheesegrits\FilamentGoogleMaps\Tests\TestCase as BaseTestCase;
use Illuminate\Support\Facades\File;

class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->beforeApplicationDestroyed(function () {
            File::cleanDirectory(app_path());
        });
    }

    protected function getPackageProviders($app): array
    {
        return array_merge(parent::getPackageProviders($app), [
            CommandsServiceProvider::class,
        ]);
    }
}
