<?php

namespace Cheesegrits\FilamentGoogleMaps\Helpers;

use Geocoder\Exception\InvalidServerResponse;
use Geocoder\Formatter\StringFormatter;
use Geocoder\Provider\GoogleMaps\GoogleMaps;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\StatefulGeocoder;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\GuzzleRateLimiterMiddleware\RateLimiterMiddleware;

class Geocoder
{
    private static array $formats = [
        'Street Number'                => '%n',
        'Street Name'                  => '%S',
        'City (Locality)'              => '%L',
        'City District (Sub-Locality)' => '%D',
        'Zipcode (Postal Code)'        => '%z',
        'Admin Level Name'             => '%A1, %A2, %A3, %A4, %A5',
        'Admin Level Code'             => '%a1, %a2, %a3, %a4, %a5',
        'Country'                      => '%C',
        'Country Code'                 => '%c',
        'Timezone'                     => '%T',
    ];

    private static array $formatSymbols = [
        '%n', '%S', '%L', '%D', '%z', '%A1', '%A2', '%A3', '%A4', '%A5',
        '%a1', '%a2', '%a3', '%a4', '%a5', '%C', '%c', '%T',
    ];

    private static array $symbolComponents = [
        '%n'  => 'street_number',
        '%S'  => 'street_address',
        '%L'  => 'locality',
        '%D'  => 'sublocality',
        '%z'  => 'postal_code',
        '%A1' => 'administrative_area_level_1',
        '%A2' => 'administrative_area_level_2',
        '%A3' => 'administrative_area_level_3',
        '%A4' => 'administrative_area_level_4',
        '%A5' => 'administrative_area_level_5',
        '%a1' => 'administrative_area_level_1',
        '%a2' => 'administrative_area_level_2',
        '%a3' => 'administrative_area_level_3',
        '%a4' => 'administrative_area_level_4',
        '%a5' => 'administrative_area_level_5',
        '%C'  => 'country',
        '%c'  => 'country',
        '%T'  => 'timezone',
    ];

    protected HandlerStack $stack;

    protected Client $httpClient;

    protected GoogleMaps $provider;

    protected StatefulGeocoder $geocoder;

    public StringFormatter $formatter;

    protected $isCaching = true;

    public function __construct(?int $rateLimit = null)
    {
        $this->stack = HandlerStack::create();
        $this->stack->push(
            RateLimiterMiddleware::perMinute(
                $rateLimit ?? config('filament-google-maps.rate-limit', 150)
            )
        );
        $this->httpClient = new Client(['handler' => $this->stack, 'timeout' => 30.0]);
        $this->provider   = new GoogleMaps($this->httpClient, null, MapsHelper::mapsKey(true));
        $this->geocoder   = new StatefulGeocoder($this->provider, config('filament-google-maps.locale.language'));
        $this->formatter  = new StringFormatter();
    }

    public static function getFormats(): array
    {
        $formats = [];

        foreach (static::$formats as $name => $symbols) {
            $formats[] = [
                $name,
                $symbols,
            ];
        }

        return $formats;
    }

    public function geocode(string $address): array
    {
        $latLng = [
            'lat' => 0,
            'lng' => 0,
        ];

        $result = $this->geocodeQuery($address)->first();

        if ($result) {
            $latLng['lat'] = $result->getCoordinates()?->getLatitude();
            $latLng['lng'] = $result->getCoordinates()?->getLongitude();
        }

        return $latLng;
    }

    public function geocodeQuery(string $address): Collection
    {
        $query = GeocodeQuery::create($address);

        $cacheKey = serialize($address);

        return $this->cacheRequest($cacheKey, [$query], 'geocodeQuery');
    }

    public function reverse(array|string $lat, ?string $lng = null): string
    {
        $result = $this->reverseQuery(MapsHelper::getLatLng($lat, $lng))->first();

        if ($result) {
            return $result->getFormattedAddress();
        }

        return '';
    }

    public function reverseQuery(array $latLng): Collection
    {
        $query = ReverseQuery::fromCoordinates($latLng['lat'], $latLng['lng']);

        $cacheKey = serialize($query);

        return $this->cacheRequest($cacheKey, [$query], 'reverseQuery');
    }

    /**
     * @return int[]
     */
    public function geocodeBatch(
        string $modelName,
        string $latField,
        string $lngField,
        string $fields,
        ?string $processedField = null,
        ?int $limit = null,
        ?bool $verbose = false): array
    {
        Log::channel(config('filament-google-maps.log.channel'))->info('geocodeBatch started');

        $lookups   = 0;
        $processed = 0;
        $records   = 0;

        $model = new $modelName();

        // turn the comma separated $fields string into an array of trimmed strings
        $fields = array_map(fn ($field) => trim($field), explode(',', $fields));
        $joins  = $this->getJoins($fields);

        $query = DB::table($model->getTable())->select(['*']);

        // if a $processedField name is provided, only select where this field has a truthy value
        if ($processedField) {
            $query->where(
                fn (Builder $query) => $query->whereNull($processedField)
                    ->orWhere([$processedField => 0])
                    ->orWhere([$processedField => ''])
            );
        }

        if ($limit) {
            $query->limit($limit);
        }

        // lazily fetch the records 10 at a time (could bump this)
        $query->lazyById(10)->each(
            function ($record) use (&$lookups, &$processed, &$records, $joins, $latField, $lngField, $processedField, $model, $fields) {
                $records++;

                // stitch the address together from the record
                $address = $this->getAddressFromModel($record, $fields, $joins);

                if (! empty($address)) {
                    $lookups++;
                    $result = $this->geocodeQuery($address)->first();
                    $lat    = $result->getCoordinates()?->getLatitude();
                    $lng    = $result->getCoordinates()?->getLongitude();

                    if ($lat && $lng) {
                        // yay!  we got a lat/lng, so update and set processed if specified
                        $model->find($record->id)?->update(
                            array_merge(
                                [
                                    $latField => $lat,
                                    $lngField => $lng,
                                ],
                                $processedField ? [$processedField => 1] : []
                            )
                        );
                        $processed++;
                    }
                }
            }
        );

        Log::channel(config('filament-google-maps.log.channel'))->info(
            sprintf('geocodeBatch completed, %d API calls, %d records updated', $lookups, $processed)
        );

        return [$records, $lookups, $processed];
    }

    public function reverseBatch(
        string $modelName,
        string $latField,
        string $lngField,
        array $fields,
        ?string $processedField = null,
        ?int $limit = null,
        ?bool $verbose = false): array
    {
        Log::channel(config('filament-google-maps.log.channel'))->info('reverseBatch started');

        $lookups   = 0;
        $processed = 0;
        $records   = 0;

        // allow fields to be either keyed by field name like ['name' => '%format'] or ['name=%format'],
        // convert to keyed version here if the latter
        $fields = $this->reKeyFields($fields);

        $model = new $modelName();

        // get an array of any dotted field name (which we have to update as relations)
        // and reverse format strings by field name
        $joins = $this->getJoinsReverse($fields);

        // build a query, fetching the PK and lat/lng fields
        $query = DB::table($model->getTable())->select([$model->getKeyName(), $latField, $lngField]);

        // if a $processedField name is provided, only select where this field has a truthy value
        if ($processedField) {
            $query->where(
                fn (Builder $query) => $query->whereNull($processedField)
                    ->orWhere([$processedField => 0])
                    ->orWhere([$processedField => ''])
            );
        }

        if ($limit) {
            $query->limit($limit);
        }

        $query->lazyById(10)->each(
            function ($record) use (&$lookups, &$processed, &$records, $joins, $latField, $lngField, $processedField, $model, $fields) {
                $records++;
                $lat = $record->{$latField} ?? null;
                $lng = $record->{$lngField} ?? null;

                // if we got sane lat and lng ...
                if (is_numeric($lat) && is_numeric($lng) && ! (empty($lng) && empty($lat))) {
                    $lookups++;
                    $result = $this->reverseQuery([
                        'lat' => $lat,
                        'lng' => $lng,
                    ])?->first();

                    if ($result) {
                        $data     = [];
                        $joinData = [];

                        // loop through our $formats (like ['city' => '%L']) and format them into data arrays
                        foreach ($fields as $fieldName => $format) {
                            if (array_key_exists($fieldName, $joins)) {
                                $joinData[$fieldName] = $this->formatter->format($result, $format);
                            } else {
                                $data[$fieldName] = $this->formatter->format($result, $format);
                            }
                        }

                        // if we got some formatted field data ...
                        if (! empty($data) || ! empty($joinData)) {
                            $processed++;

                            $modelRecord = $model->find($record->{$model->getKeyName()});

                            // update with the simple parent table data
                            if (! empty($data)) {
                                $modelRecord->update($data);
                            }

                            // update the joined data ...
                            if (! empty($joinData)) {
                                foreach ($joinData as $field => $value) {
                                    // $joins record is ['relation' => 'foo', 'field' => 'bar']
                                    $modelRecord->{$joins[$field]['relation']}->update([
                                        $joins[$field]['field'] => $value,
                                    ]);
                                }
                            }

                            if (! empty($processedField)) {
                                $modelRecord->update([$processedField => 1]);
                            }
                        }
                    }
                }
            }
        );

        Log::channel(config('filament-google-maps.log.channel'))->info(
            sprintf('reverseBatch completed, %d API calls, %d records updated', $lookups, $processed)
        );

        return [$records, $lookups, $processed];
    }

    public function testReverse(array|string $lat, ?string $lng = null, $withComponents = false): array
    {
        $formats = [];

        $result = $this->reverseQuery(MapsHelper::getLatLng($lat, $lng))->first();

        if ($result) {
            foreach (static::$symbolComponents as $symbol => $component) {
                $formats[] = [
                    $symbol,
                    $this->formatter->format($result, $symbol),
                    ...($withComponents ? [$component] : []),
                ];
            }
        }

        return $formats;
    }

    public function testReverseModel(string $model, $id, $latField, $lngField): array
    {
        $formats = [];

        /** @noinspection PhpUndefinedMethodInspection */
        $record = $model::find($id);

        if ($record) {
            $formats = $this->testReverse([
                'lat' => $record->{$latField},
                'lng' => $record->{$lngField},
            ]);
        }

        return $formats;
    }

    public function doNotCache(): self
    {
        $this->isCaching = false;

        return $this;
    }

    private function cacheRequest(string $cacheKey, array $queryElements, string $queryType): ?Collection
    {
        if (! $this->isCaching) {
            $this->isCaching = true;

            return collect($this->geocoder->{$queryType}(...$queryElements));
        }

        $hashedCacheKey = sha1($cacheKey);
        $duration       = config('filament-google-maps.cache.duration', 0);
        $store          = config('filament-google-maps.cache.store');

        try {
            $result = app('cache')
                ->store($store)
                ->remember(
                    $hashedCacheKey,
                    $duration,
                    function () use ($cacheKey, $queryElements, $queryType) {
                        return [
                            'key'   => $cacheKey,
                            'value' => collect($this->geocoder->{$queryType}(...$queryElements)),
                        ];
                    }
                );
        } catch (InvalidServerResponse $e) {
            Log::channel(config('filament-google-maps.log.channel', 'null'))
                ->error('Error in Maps API call: '.$e->getMessage());

            if (App::runningInConsole()) {
                echo 'Error from Maps API: '.$e->getMessage();

                exit;
            }

            return null;
        }

        $result = $this->preventCacheKeyHashCollision(
            $result,
            $hashedCacheKey,
            $cacheKey,
            $queryElements,
            $queryType
        );

        $this->removeEmptyCacheEntry($result, $hashedCacheKey);

        return $result;
    }

    private function preventCacheKeyHashCollision(array $result, string $hashedCacheKey, string $cacheKey, array $queryElements, string $queryType)
    {
        if ($result['key'] === $cacheKey) {
            return $result['value'];
        }

        app('cache')
            ->store(config('filament-google-maps.cache.store'))
            ->forget($hashedCacheKey);

        return $this->cacheRequest($cacheKey, $queryElements, $queryType);
    }

    private function removeEmptyCacheEntry(Collection $result, string $cacheKey): void
    {
        if ($result->isEmpty()) {
            app('cache')
                ->store(config('filament-google-maps.cache.store'))
                ->forget($cacheKey);
        }
    }

    private function reKeyFields($fields)
    {
        if (! $this->hasStringKeys($fields)) {
            $new = [];

            foreach ($fields as $field) {
                $parts = explode('=', trim($field));

                if (count($parts) === 2) {
                    $fieldName       = $parts[0];
                    $format          = $parts[1];
                    $new[$fieldName] = $format;
                }
            }

            return $new;
        }

        return $fields;
    }

    private function getJoinsReverse(array $fields): array
    {
        $joins = [];

        // find any dotted field names and bust them into table and field in the $joins array
        foreach ($fields as $fieldName => $format) {
            $fieldParts = explode('.', $fieldName);

            if (count($fieldParts) === 2) {
                $joins[$fieldName] = [
                    'relation' => $fieldParts[0],
                    'field'    => $fieldParts[1],
                ];
            }
        }

        return $joins;
    }

    private function getJoins(array $fields): array
    {
        $joins = [];

        foreach ($fields as $field) {
            $parts = explode('.', $field);

            if (count($parts) === 2) {
                $joins[$field] = [
                    'relation' => $parts[0],
                    'field'    => $parts[1],
                ];
            }
        }

        return $joins;
    }

    private function getAddressFromModel(object $record, array $fields, array $joins): string
    {
        $address = [];

        foreach ($fields as $field) {
            // if the field name is a dotted join, attempt to get it through the relationship
            if (array_key_exists($field, $joins)) {
                $address[] = $record->{$joins[$field]['relation']}?->{$joins[$field]['field']};
            } else {
                $address[] = $record->{$field};
            }
        }

        return implode(',', array_filter($address));
    }

    private function hasStringKeys(array $array): bool
    {
        return count(array_filter(array_keys($array), 'is_string')) > 0;
    }
}
