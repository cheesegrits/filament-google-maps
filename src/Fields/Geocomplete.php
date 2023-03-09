<?php

namespace Cheesegrits\FilamentGoogleMaps\Fields;

use Cheesegrits\FilamentGoogleMaps\Helpers\FieldHelper;
use Cheesegrits\FilamentGoogleMaps\Helpers\MapsHelper;
use Closure;
use Exception;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Concerns;
use Filament\Forms\Components\Contracts\CanBeLengthConstrained;
use Filament\Forms\Components\Field;

class Geocomplete extends Field implements CanBeLengthConstrained
{
    use Concerns\CanBeLengthConstrained;
    use Concerns\HasAffixes;
    use Concerns\HasExtraInputAttributes;
    use Concerns\HasInputMode;
    use Concerns\HasPlaceholder;

    protected string $view = 'filament-google-maps::fields.filament-google-geocomplete';

    protected int $precision = 8;

    protected Closure|string|null $filterName = null;

    protected Closure|string|null $placeField = null;

    protected Closure|bool $isLocation = false;

    protected Closure|bool $geocodeOnLoad = false;

    protected Closure|bool $geolocate = false;

    protected Closure|string $geolocateIcon = 'heroicon-s-map';

    protected Closure|array $reverseGeocode = [];

    protected Closure|bool $updateLatLng = false;

    protected Closure|array $types = [];

    protected Closure|array $countries = [];

    protected Closure|bool $debug = false;

    /**
     * DO NOT USE!  Only used by the Radius Filter, to set the state path for the filter form data.
     *
     *
     * @return $this
     */
    public function filterName(Closure|string $name): static
    {
        $this->filterName = $name;

        return $this;
    }

    public function getFilterName(): string|null
    {
        $name = $this->evaluate($this->filterName);

        if ($name) {
            return 'tableFilters.'.$name;
        }

        return null;
    }

    /**
     * Prints out reverse geocode components on the debug console, useful for figuring out the format
     * strings to use.
     *
     *
     * @return $this
     */
    public function debug(Closure|bool $debug = true): static
    {
        $this->debug = $debug;

        return $this;
    }

    public function getDebug(): bool
    {
        return $this->evaluate($this->debug);
    }

    /**
     * If set to true, will update lat and lng fields on the form when a place is selected from the dropdown.  Requires
     * the getLatLngAttributes() method on the model, as per the filament-google-maps:model-code Artisan command.
     *
     * @param  Closure|bool  $debug
     * @return $this
     */
    public function updateLatLng(Closure|bool $updateLatLng = true): static
    {
        $this->updateLatLng = $updateLatLng;

        return $this;
    }

    public function getUpdateLatLng(): bool
    {
        return $this->evaluate($this->updateLatLng);
    }

    private function getUpdateLatLngFields(): array
    {
        $statePaths = [];

        if ($this->getUpdateLatLng()) {
            /** @noinspection PhpUndefinedMethodInspection */
            $fields = $this->getModel()::getLatLngAttributes();

            foreach ($fields as $fieldKey => $field) {
                $fieldId = FieldHelper::getFieldId($field, $this);

                if ($fieldId) {
                    $statePaths[$fieldKey] = $fieldId;
                }
            }
        }

        return $statePaths;
    }

    /**
     * Optionally set this to true, if you want the geocomplete to update lat/lng fields on your form
     *
     * @param  Closure|string  $name
     * @return $this
     */
    public function isLocation(Closure|bool $isLocation = true): static
    {
        $this->isLocation = $isLocation;

        return $this;
    }

    public function getIsLocation(): bool|null
    {
        return $this->evaluate($this->isLocation);
    }

    /**
     * If set to true, the current location (lat/lng) will be reverse geocoded to this field a formatted address.
     * This incurs and extra server side API call, and requires that you have an API key set to allow your server IP.
     * Defaults to false.
     *
     *
     * @return $this
     */
    public function geocodeOnLoad(Closure|bool $geocodeOnLoad = true): static
    {
        $this->geocodeOnLoad = $geocodeOnLoad;

        return $this;
    }

    public function getGeocodeOnLoad(): bool|null
    {
        return $this->evaluate($this->geocodeOnLoad);
    }

    /**
     * Adds a configurable suffix button to the field which requests the user's location, and if granted will reverse
     * geocode the resulting coordinates and fill the field with the formatted_address.
     *
     * @return $this
     */
    public function geolocate(Closure|bool $geolocate = true): static
    {
        $this->geolocate = $geolocate;

        return $this;
    }

    public function getGeolocate(): bool|null
    {
        return $this->evaluate($this->geolocate);
    }

    /**
     * Override the icon to use for the geolocate feature, defaults to heroicon-s-map
     *
     * @return $this
     */
    public function geolocateIcon(Closure|string $geolocateIcon): static
    {
        $this->geolocateIcon = $geolocateIcon;

        return $this;
    }

    public function getGeolocateIcon(): string
    {
        return $this->evaluate($this->geolocateIcon);
    }

    /**
     * Optionally provide an array of field names and format strings as key and value, if you would like the map to reverse geocode
     * address components to individual fields on your form.  See documentation for full explanation of format strings.
     *
     * ->reverseGeocode(['street' => '%n %s', 'city' => '%L', 'state' => %A1', 'zip' => '%z'])
     *
     * Street Number: %n
     * Street Name: %S
     * City (Locality): %L
     * City District (Sub-Locality): %D
     * Zipcode (Postal Code): %z
     * Admin Level Name: %A1, %A2, %A3, %A4, %A5
     * Admin Level Code: %a1, %a2, %a3, %a4, %a5
     * Country: %C
     * Country Code: %c
     *
     *
     * @return $this
     */
    public function reverseGeocode(Closure|array $reverseGeocode): static
    {
        $this->reverseGeocode = $reverseGeocode;

        return $this;
    }

    public function getReverseGeocode(): array
    {
        $fields     = $this->evaluate($this->reverseGeocode);
        $statePaths = [];

        foreach ($fields as $field => $format) {
            $fieldId = FieldHelper::getFieldId($field, $this);

            if ($fieldId) {
                $statePaths[$fieldId] = $format;
            }
        }

        return $statePaths;
    }

    /**
     * And array of place types, see "Constrain Place Types" section of Google Places API doc:
     *
     * https://developers.google.com/maps/documentation/javascript/place-autocomplete
     *
     * In particular, note the restrictions on number of types (5), and not mixing from tables 1 or 2 with
     * table 3.
     *
     * Defaults to 'geocode'
     *
     *
     * @return $this
     */
    public function types(Closure|array $types): static
    {
        $this->types = $types;

        return $this;
    }

    public function getTypes(): array
    {
        $types = $this->evaluate($this->types);

        if (count($types) === 0) {
            $types = ['geocode'];
        }

        return $types;
    }

    /**
     * And array of countries that will show up in autocomplete, see "Place Autocomplete Restricted to Multiple
     * Countries" section of Google Places API doc:
     *
     * https://developers.google.com/maps/documentation/javascript/examples/places-autocomplete-multiple-countries
     *
     *
     * Defaults is empty array
     *
     *
     * @return $this
     */
    public function countries(Closure|array $countries = []): static
    {
        $this->countries = $countries;

        return $this;
    }

    public function getCountries(): array
    {
        return $this->evaluate($this->countries);
    }

    public function placeField(Closure|string $placeField): static
    {
        $this->placeField = $placeField;

        return $this;
    }

    public function getPlaceField(): string|null
    {
        return $this->evaluate($this->placeField) ?? 'formatted_address';
    }

    public function getSuffixAction(): ?Action
    {
        if ($this->getGeolocate()) {
            return Action::make('geolocate')
                ->iconButton()
                ->icon($this->getGeolocateIcon())
                ->extraAttributes(['id' => $this->getId().'-geolocate']);
        }

        return null;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->afterStateHydrated(static function (Geocomplete $component, $state) {
            if ($component->getIsLocation()) {
                if ($component->getGeocodeOnLoad()) {
                    $state = static::getLocationState($state);

                    if (! MapsHelper::isLocationEmpty($state)) {
                        $state = MapsHelper::reverseGeocode($state);
                    } else {
                        $state = '';
                    }
                } else {
                    $state = '';
                }

                $component->state((string) $state);
            }
        });

        $this->dehydrateStateUsing(static function (string|array|null $state, $record, $model, Geocomplete $component) {
            //			if (!blank($state))
            //			{
            //				if ($component->getIsLocation())
            //				{
            //					if ($latLang = MapsHelper::geocode($state))
            //					{
            //						return $latLang;
            //					}
            //				}
            //			}

            return $state;
        });
    }

    /**
     * Create json configuration string
     */
    public function getGeocompleteConfig(): string
    {
        $config = json_encode([
            'filterName'           => $this->getFilterName(),
            'statePath'            => $this->getStatePath(),
            'isLocation'           => $this->getIsLocation(),
            'reverseGeocodeFields' => $this->getReverseGeocode(),
            'latLngFields'         => $this->getUpdateLatLngFields(),
            'types'                => $this->getTypes(),
            'countries'            => $this->getCountries(),
            'placeField'           => $this->getPlaceField(),
            'debug'                => $this->getDebug(),
            'gmaps'                => MapsHelper::mapsUrl(),
        ]);

        //ray($config);

        return $config;
    }

    public static function getLocationState($state)
    {
        if (is_array($state)) {
            return $state;
        } else {
            try {
                return @json_decode($state, true, 512, JSON_THROW_ON_ERROR);
            } catch (Exception $e) {
                return [
                    'lat' => 0,
                    'lng' => 0,
                ];
            }
        }
    }

    public function geoHasJs(): bool
    {
        return true;
    }

    public function geoJsUrl(): string
    {
        $manifest = json_decode(file_get_contents(__DIR__.'/../../dist/mix-manifest.json'), true);

        return url($manifest['/cheesegrits/filament-google-maps/filament-google-geocomplete.js']);
    }

    public function geoHasCss(): bool
    {
        return false;
    }

    public function geoCssUrl(): string
    {
        $manifest = json_decode(file_get_contents(__DIR__.'/../../dist/mix-manifest.json'), true);

        return url($manifest['/cheesegrits/filament-google-maps/filament-google-geocomplete.css']);
    }
}
