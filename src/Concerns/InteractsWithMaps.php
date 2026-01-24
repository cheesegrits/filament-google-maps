<?php

namespace Cheesegrits\FilamentGoogleMaps\Concerns;

use Cheesegrits\FilamentGoogleMaps\Fields\Geocomplete;
use Cheesegrits\FilamentGoogleMaps\Fields\Map;
use Filament\Schemas\Components\Component;

trait InteractsWithMaps
{
    public function reverseGeocodeUsing(string $statePath, array $results): bool
    {
        foreach ($this->getCachedSchemas() as $form) {
            if ($this->reverseGeocodeUpdated($form, $statePath, $results)) {
                return true;
            }
        }

        return false;
    }

    public function reverseGeocodeUpdated($container, string $statePath, array $results): bool
    {
        foreach ($container->getComponents() as $component) {
            if (($component instanceof Map || $component instanceof Geocomplete) && $component->getStatePath() === $statePath) {
                $component->reverseGeocodeUpdated($results);

                return true;
            }

            if (! $component instanceof Component) {
                return false;
            }

            foreach ($component->getChildSchemas() as $childComponentContainer) {
                if ($childComponentContainer->isHidden()) {
                    continue;
                }

                if ($this->reverseGeocodeUpdated($childComponentContainer, $statePath, $results)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function placeUpdatedUsing(string $statePath, array $results): bool
    {
        foreach ($this->getCachedSchemas() as $form) {
            if ($this->placeUpdated($form, $statePath, $results)) {
                return true;
            }
        }

        return false;
    }

    public function placeUpdated($container, string $statePath, array $results): bool
    {
        foreach ($container->getComponents() as $component) {
            if ($component instanceof Map && $component->getStatePath() === $statePath) {
                $component->placeUpdated($results);

                return true;
            }

            if (! $component instanceof Component) {
                return false;
            }

            foreach ($component->getChildSchemas() as $childComponentContainer) {
                if ($childComponentContainer->isHidden()) {
                    continue;
                }

                if ($this->placeUpdated($childComponentContainer, $statePath, $results)) {
                    return true;
                }
            }
        }

        return false;
    }
}
