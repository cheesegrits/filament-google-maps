<?php

namespace Cheesegrits\FilamentGoogleMaps\Tests\Fields;

use Cheesegrits\FilamentGoogleMaps\Tests\Models\User;
use Cheesegrits\FilamentGoogleMaps\Tests\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    protected function getPackageProviders($app): array
    {
        return array_merge(parent::getPackageProviders($app), [
            FieldsServiceProvider::class,
        ]);
    }
}
