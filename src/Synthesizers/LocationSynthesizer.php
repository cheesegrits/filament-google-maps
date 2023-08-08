<?php

namespace Cheesegrits\FilamentGoogleMaps\Synthesizers;

use Livewire\Mechanisms\HandleComponents\Synthesizers\Synth;

class LocationSynthesizer extends Synth
{
    public static $key = 'fgmloc';

    public static function match($target)
    {
        return is_array($target) && count($target) === 2 && array_key_exists('lat', $target) && array_key_exists('lng', $target);
    }

    public function dehydrate($target)
    {
        return [
            [
                'lat' => $target['lat'],
                'lng' => $target['lng'],
            ], []
        ];
    }

    public function hydrate($value)
    {
        return $value;
    }

    public function get(&$target, $key)
    {
        return $target;
    }

    public function set(&$target, $key, $value)
    {
        if ($key === 'lat' || $key === 'lng') {
            $target[$key] = $value;
        }
    }
}