<?php

namespace Cheesegrits\FilamentGoogleMaps\Tests\Commands;

use Cheesegrits\FilamentGoogleMaps\Tests\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return array_merge(parent::getPackageProviders($app), [
            CommandsServiceProvider::class,
        ]);
    }
}
