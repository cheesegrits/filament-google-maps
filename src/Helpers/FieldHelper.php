<?php

namespace Cheesegrits\FilamentGoogleMaps\Helpers;

use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;

class FieldHelper
{
    public static function getFieldStatePath(string $field, Component $component): ?string
    {
        return static::findField($field, $component)?->getStatePath();
    }

    public static function getFieldElementId(string $field, Component $component): ?string
    {
        return static::findField($field, $component)?->getId();
    }

    protected static function findField(string $fieldName, Component $component): ?Component
    {

        $container = $component->getContainer();

        while ($container instanceof Schema) {
            if ($found = $container->getComponent($fieldName)) {
                return $found;
            }

            $container = $container->getParentComponent()?->getContainer();
        }

        return null;
    }
}
