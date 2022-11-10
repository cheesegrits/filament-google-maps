<?php

namespace Cheesegrits\FilamentGoogleMaps\Helpers;

class GeocodeHelper
{
	public static function getCountyFromAddress(string $address): string
	{
		$geocoder = new Geocoder();
		$result = $geocoder->geocodeQuery($address)->first();

		if ($result)
		{
			return $geocoder->formatter->format($result, '%A2');
		}

		return '';
	}
}