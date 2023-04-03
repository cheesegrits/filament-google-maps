<?php

namespace Cheesegrits\FilamentGoogleMaps\Helpers;

use Illuminate\Support\Facades\Request;

class MapsHelper
{
    const POSITION_BOTTOM_CENTER = 11;

    const POSITION_BOTTOM_LEFT = 10;

    const POSITION_BOTTOM_RIGHT = 12;

    const POSITION_LEFT_CENTER = 4;

    const POSITION_LEFT_TOP = 5;

    const POSITION_RIGHT_BOTTOM = 9;

    const POSITION_RIGHT_CENTER = 8;

    const POSITION_RIGHT_TOP = 7;

    const POSITION_TOP_CENTER = 2;

    const POSITION_TOP_LEFT = 1;

    const POSITION_TOP_RIGHT = 3;

    public static function mapsKey($server = false): string
    {
        return $server ? config('filament-google-maps.keys.server_key') : config('filament-google-maps.keys.web_key');
    }

    public static function mapsSigningKey(): ?string
    {
        return config('filament-google-maps.keys.signing_key', null);
    }

    public static function hasSigningKey(): bool
    {
        return ! empty(self::mapsSigningKey());
    }

    public static function mapsLanguage($server = false): string|null
    {
        if ($server) {
            return config('filament-google-maps.locale.api') ?? config('filament-google-maps.locale.language');
        } else {
            return config('filament-google-maps.locale.api');
        }
    }

    public static function mapsRegion($server = false): string|null
    {
        return config('filament-google-maps.locale.region');
    }

    public static function mapsUrl($server = false, array $libraries = []): string
    {
        $libraries = implode(',', array_unique(
            array_filter(
                array_merge(
                    ['places'],
                    explode(',', config('filament-google-maps.libraries')),
                    $libraries
                )
            )
        ));

        $gmaps = (Request::getScheme() ?? 'https').'://maps.googleapis.com/maps/api/js'
            .'?key='.self::mapsKey($server)
            .'&libraries='.$libraries
            .'&v=weekly';

        /**
         * https://developers.google.com/maps/faq#languagesupport
         */
        if ($language = self::mapsLanguage($server)) {
            $gmaps .= '&language='.$language;
        }

        /**
         * https://developers.google.com/maps/coverage
         */
        if ($region = self::mapsRegion()) {
            $gmaps .= '&region='.$region;
        }

        return $gmaps;
    }

    public static function reverseGeocode(array|string $lat, ?string $lng = null): string
    {
        return (new Geocoder())->reverse(MapsHelper::getLatLng($lat, $lng));
    }

    public static function geocode(string $address): array
    {
        return (new Geocoder())->geocode($address);
    }

    public static function getCountyFromAddress(string $address): string
    {
        $geocoder = new Geocoder();
        $result   = $geocoder->geocodeQuery($address)->first();

        if ($result) {
            return $geocoder->formatter->format($result, '%A2');
        }

        return '';
    }

    public static function getCountyFromLatLng(array|string $lat, ?string $lng = null): string
    {
        $geocoder = new Geocoder();
        $result   = $geocoder->reverseQuery(self::getLatLng($lat, $lng))->first();

        if ($result) {
            return $geocoder->formatter->format($result, '%A2');
        }

        return '';
    }

    public static function getLatLng(array|string $lat, ?string $lng = null): array
    {
        if (is_array($lat)) {
            if (array_key_exists('lat', $lat) && array_key_exists('lng', $lat)) {
                return $lat;
            } elseif (count($lat) === 2) {
                return [
                    'lat' => $lat[0],
                    'lng' => $lat[1],
                ];
            }
        } elseif (isset($lng)) {
            return [
                'lat' => $lat,
                'lng' => $lng,
            ];
        }

        return [0, 0];
    }

    public static function isLocationEmpty($location): bool
    {
        if (empty($location)) {
            return true;
        }

        if (array_key_exists('lat', $location) && array_key_exists('lng', $location)) {
            return empty($location['lat']) && empty($location['lng']);
        }

        if (is_array($location) && is_numeric($location[0] && is_numeric($location[1]))) {
            return empty($location[0] && empty($location[1]));
        }

        return true;
    }
}
