<?php

namespace Cheesegrits\FilamentGoogleMaps\Helpers;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;

class MapsHelper
{
	public static function mapsKey($server = false): string
	{
		return $server ? config('filament-google-maps.keys.server_key') : config('filament-google-maps.keys.web_key');
	}

	public static function mapsSigningKey(): string|null
	{
		return config('filament-google-maps.keys.signing_key', null);
	}
	
	public static function hasSigningKey(): bool
	{
		return !empty(self::mapsSigningKey());
	}

	public static function mapsLanguage($server = false): string|null
	{
		return $server ? config('filament-google-maps.locale.language') : null;
	}

	public static function mapsRegion($server = false): string|null
	{
		return config('filament-google-maps.locale.region');
	}

	public static function mapsUrl($server = false): string
	{
		$gmaps = (Request::getScheme() ?? 'https') . '://maps.googleapis.com/maps/api/js'
			. '?key=' . self::mapsKey($server)
			. '&libraries=places'
			. '&v=weekly';

		/**
		 * https://developers.google.com/maps/faq#languagesupport
		 */
		if ($server && $language = self::mapsLanguage())
		{
			$gmaps .= '&language=' . $language;
		}

		/**
		 * https://developers.google.com/maps/coverage
		 */
		if ($region = self::mapsRegion())
		{
			$gmaps .= '&region=' . $region;
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

		if ($result)
		{
			return $geocoder->formatter->format($result, '%A2');
		}

		return '';
	}

	public static function getCountyFromLatLng(array|string $lat, ?string $lng = null): string
	{
		$geocoder = new Geocoder();
		$result   = $geocoder->reverseQuery(self::getLatLng($lat, $lng))->first();

		if ($result)
		{
			return $geocoder->formatter->format($result, '%A2');
		}

		return '';
	}

	public static function getLatLng(array|string $lat, ?string $lng = null): array
	{
		if (is_array($lat))
		{
			if (array_key_exists('lat', $lat) && array_key_exists('lng', $lat))
			{
				return $lat;
			}
			else if (count($lat) === 2)
			{
				return [
					'lat' => $lat[0],
					'lng' => $lat[1],
				];
			}
		}
		else if (isset($lng))
		{
			return [
				'lat' => $lat,
				'lng' => $lng,
			];
		}

		return [0, 0];
	}

	public static function isLocationEmpty($location): bool
	{
		if (empty($location))
		{
			return true;
		}

		if (array_key_exists('lat', $location) && array_key_exists('lng', $location))
		{
			return empty($location['lat']) && empty($location['lng']);
		}

		if (is_array($location) && is_numeric($location[0] && is_numeric($location[1])))
		{
			return empty($location[0] && empty($location[1]));
		}

		return true;
	}
}