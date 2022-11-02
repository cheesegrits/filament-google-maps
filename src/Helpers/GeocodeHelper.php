<?php

namespace Cheesegrits\FilamentGoogleMaps\Helpers;

use Geocoder\Formatter\StringFormatter;
use Geocoder\Provider\GoogleMaps\GoogleMaps;
use Geocoder\StatefulGeocoder;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Geocoder\Query\GeocodeQuery;
use Spatie\GuzzleRateLimiterMiddleware\RateLimiterMiddleware;

class GeocodeHelper
{
	public static function batchGeocode(Model $model, $latField, $lngField, $fields, $rateLimit = 300, $verbose = false): void
	{
		$lookups   = 0;
		$processed = 0;

		$stack = HandlerStack::create();
		$stack->push(RateLimiterMiddleware::perMinute($rateLimit));

		$httpClient = new Client(['handler' => $stack, 'timeout' => 30.0]);
		$provider   = new GoogleMaps($httpClient, null, config('filament-google-maps.key'));
		$geocoder   = new StatefulGeocoder($provider, 'en');
		$formatter  = new StringFormatter();

		$table  = $model->getTable();
		$fields = array_map(fn($field) => trim($field), explode(',', $fields));
		$joins  = [];

		// find any dotted field names and bust them into table and field in the $joins array
		foreach ($fields as $field)
		{
			$parts = explode('.', $field);

			if (count($parts) === 2)
			{
				$joins[$field] = [
					'table' => $parts[0],
					'field' => $parts[1],
				];
			}
		}

		$records = $model::where(
			fn($query) => $query->where([$latField => 0])->orWhere([$latField => ''])->orWhereNull($latField)
		)
			->orWhere(
				fn($query) => $query->where([$lngField => 0])->orWhere([$lngField => ''])->orWhereNull($lngField)
			)
			->get();

//		$records = $model::all();

		$addresses = [];

		foreach ($records as $record)
		{
			$address = [];

			foreach ($fields as $field)
			{
				// if the field name is a dotted join, attempt to get it through the relationship
				if (array_key_exists($field, $joins))
				{
					$address[] = $record->{$joins[$field]['table']}?->{$joins[$field]['field']};
				}
				else
				{
					$address[] = $record->{$field};
				}
			}

			$addresses[$record->id] = implode(',', array_filter($address));
		}

		// do the geocode query, and if we get a lat and lng, update the model
		foreach ($addresses as $modelId => $address)
		{
			$lookups++;
			$result = $geocoder->geocodeQuery(GeocodeQuery::create($address))->first();

			$lat = $result->getCoordinates()?->getLatitude();
			$lng = $result->getCoordinates()?->getLongitude();

			if ($latField && $lngField)
			{
				$processed++;

				$model->find($modelId)?->update([
					$latField => $lat,
					$lngField => $lng,
				]);
			}
		}
	}

}
