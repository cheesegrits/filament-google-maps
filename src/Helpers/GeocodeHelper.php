<?php

namespace Cheesegrits\FilamentGoogleMaps\Helpers;

use Geocoder\Formatter\StringFormatter;
use Geocoder\Provider\GoogleMaps\GoogleMaps;
use Geocoder\Query\ReverseQuery;
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
	public static array $formats = [
		"Street Number"                => '%n',
		"Street Name"                  => '%S',
		"City (Locality)"              => '%L',
		"City District (Sub-Locality)" => '%D',
		"Zipcode (Postal Code)"        => '%z',
		"Admin Level Name"             => '%A1, %A2, %A3, %A4, %A5',
		"Admin Level Code"             => '%a1, %a2, %a3, %a4, %a5',
		"Country"                      => '%C',
		"Country Code"                 => '%c',
		"Timezone"                     => '%T',
	];

	public static array $formatSymbols = [
		'%n', '%S', '%L', '%D', '%z', '%A1', '%A2', '%A3', '%A4', '%A5',
		'%a1', '%a2', '%a3', '%a4', '%a5', '%C', '%c', '%T',
	];

	public static function getFormats(): array
	{
		$formats = [];

		foreach (static::$formats as $name => $symbols)
		{
			$formats[] = [
				$name,
				$symbols,
			];
		}

		return $formats;
	}

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

//		$records = $model::where(
//			fn($query) => $query->where([$latField => 0])->orWhere([$latField => ''])->orWhereNull($latField)
//		)
//			->orWhere(
//				fn($query) => $query->where([$lngField => 0])->orWhere([$lngField => ''])->orWhereNull($lngField)
//			)
//			->get();

		$records = $model::all();

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

	public static function batchReverseGeocode(Model $model, $latField, $lngField, $fields, $rateLimit = 300, $verbose = false): array
	{
		$lookups   = 0;
		$processed = 0;

		$stack = HandlerStack::create();
		$stack->push(RateLimiterMiddleware::perMinute($rateLimit));

		$httpClient = new Client(['handler' => $stack, 'timeout' => 30.0]);
		$provider   = new GoogleMaps($httpClient, null, config('filament-google-maps.key'));
		$geocoder   = new StatefulGeocoder($provider, 'en');
		$formatter  = new StringFormatter();

		$table   = $model->getTable();
		$joins   = [];
		$formats = [];

		// find any dotted field names and bust them into table and field in the $joins array
		foreach ($fields as $field)
		{
			$parts = explode('=', trim($field));

			if (count($parts) === 2)
			{
				$fieldName           = $parts[0];
				$format              = $parts[1];
				$formats[$fieldName] = $format;

				$fieldParts = explode('.', $fieldName);

				if (count($fieldParts) === 2)
				{
					$joins[$field] = [
						'table' => $fieldParts[0],
						'field' => $fieldParts[1],
					];
				}
			}
		}

		DB::table($table)->lazyById(10)->each(
			function ($record) use ($geocoder, &$lookups, &$processed, $joins, $latField, $lngField, $model, $fields, $formats, $formatter) {
				$lookups++;
				$result = $geocoder->reverseQuery(ReverseQuery::fromCoordinates($record->{$latField}, $record->{$lngField}))?->first();

				if ($result)
				{
					$data     = [];
					$joinData = [];

					foreach ($formats as $field => $format)
					{
						if (array_key_exists($field, $joins))
						{
							$joinData[$field] = $formatter->format($result, $format);
						}
						else
						{
							$data[$field] = $formatter->format($result, $format);
						}
					}


					if (!empty($data) || !empty($joinData))
					{
						$processed++;

						$modelRecord = $model->find($record->id);

						if (!empty($data))
						{
							$modelRecord->update($data);
						}

						if (!empty($joinData))
						{
							foreach ($joinData as $field => $value)
							{
								$modelRecord->{$joins[$field]['table']}->update([
									$joins[$field]['field'] => $value,
								]);
							}
						}
					}
				}
			}
		);
		
		return [$lookups, $processed];
	}

	public static function reverseGeocode(array $latLng)
	{
		$httpClient = new Client();
		$provider   = new GoogleMaps($httpClient, null, config('filament-google-maps.key'));
		$geocoder   = new StatefulGeocoder($provider, 'en');
		$result = $geocoder->reverseQuery(ReverseQuery::fromCoordinates($latLng['lat'], $latLng['lng']))?->first();

		if ($result)
		{
			return $result->getFormattedAddress();
		}

		return '';
	}

	public static function geocode(string $address): array
	{
		$latLng = [
			'lat' => 0,
			'long' => 0,
		];

		$httpClient = new Client();
		$provider   = new GoogleMaps($httpClient, null, config('filament-google-maps.key'));
		$geocoder   = new StatefulGeocoder($provider, 'en');
		$result = $geocoder->geocodeQuery(GeocodeQuery::create($address))->first();

		if ($result)
		{
			$latLng['lat'] = $result->getCoordinates()?->getLatitude();
			$latLng['lng'] = $result->getCoordinates()?->getLongitude();
		}

		return $latLng;
	}

	public static function testReverseGeocode(Model $model, $id, $latField, $lngField): array
	{
		$formats = [];

		$record = $model->find($id);

		if ($record)
		{
			$httpClient = new Client();
			$provider   = new GoogleMaps($httpClient, null, config('filament-google-maps.key'));
			$geocoder   = new StatefulGeocoder($provider, 'en');
			$formatter  = new StringFormatter();

			$result = $geocoder->reverseQuery(ReverseQuery::fromCoordinates($record->{$latField}, $record->{$lngField}))?->first();

			if ($result)
			{
				foreach (static::$formatSymbols as $symbol)
				{
					$formats[] = [
						$symbol,
						$formatter->format($result, $symbol),
					];
				}
			}
		}


		return $formats;
	}

}
