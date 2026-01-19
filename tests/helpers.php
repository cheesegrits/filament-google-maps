<?php

namespace Cheesegrits\FilamentGoogleMaps\Tests;

use Livewire\Component;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

if (! function_exists('\Cheesegrits\FilamentGoogleMaps\Tests\livewire')) {
    function livewire(string|Component $component, array $props = []): Testable
    {
        return Livewire::test($component, $props);
    }
}
