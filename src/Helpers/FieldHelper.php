<?php

namespace Cheesegrits\FilamentGoogleMaps\Helpers;

use Filament\Forms\Components\Field;
use Filament\Schemas\Components\Component;

class FieldHelper
{
    public static function getTopComponent(Component $component): Component
    {
        $parentComponent = $component->getContainer()->getParentComponent();

        return $parentComponent ? static::getTopComponent($parentComponent) : $component;
    }

    public static function getFlatFields($topComponent): array
    {
        $flatFields = $topComponent->getContainer()->getFlatFields();

        foreach ($topComponent->getContainer()->getComponents() as $component) {
            foreach ($component->getChildComponentContainers() as $container) {
                if ($container->isHidden()) {
                    continue;
                }

                $flatFields = array_merge($flatFields, $container->getFlatFields());
            }
        }

        return $flatFields;
    }

    public static function getFieldId(string $field, Component $component): ?string
    {
        $topComponent = self::getTopComponent($component);
        $flatFields   = static::getFlatFields($topComponent);
        $flatFields = collect($flatFields)
            ->whereInstanceOf(Field::class)->keyBy(fn($field) => $field->getName());

        if ($flatFields->has($field)) {
            return $flatFields->get($field)->getStatePath();
        }

        return null;
    }
}
