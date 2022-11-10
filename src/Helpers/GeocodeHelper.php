<?php

namespace Cheesegrits\FilamentGoogleMaps\Helpers;

class GeocodeHelper
{
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
		$result   = $geocoder->reverseQuery(static::getLatLng($lat, $lng))->first();

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
}