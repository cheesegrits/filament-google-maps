<?php

namespace Cheesegrits\FilamentGoogleMaps\Fields;

use Cheesegrits\FilamentGoogleMaps\Helpers\FieldHelper;
use Cheesegrits\FilamentGoogleMaps\Helpers\MapsHelper;
use Closure;
use Exception;
use Filament\Forms\Components\Field;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use JsonException;

class Map extends Field
{
    protected string $view = 'filament-google-maps::fields.filament-google-maps';

    protected int $precision = 8;

    protected array|Closure|null $defaultLocation = [0, 0];

    protected Closure|int $defaultZoom = 8;

    protected Closure|bool $draggable = true;

    protected Closure|bool $clickable = false;

    protected Closure|array $mapControls = [];

    protected Closure|array $layers = [];

    protected Closure|string $height = '350px';

    protected Closure|string|null $autocomplete = null;

    protected Closure|array $types = [];

    protected Closure|string|null $placeField = null;

    protected Closure|array $countries = [];

    protected Closure|bool $autocompleteReverse = false;

    protected Closure|bool $geolocate = false;

    protected Closure|bool $geolocateOnLoad = false;

    protected Closure|bool $geolocateOnLoadAlways = false;

    protected Closure|string|null $geolocateLabel = null;

    protected Closure|array $reverseGeocode = [];

    protected Closure|bool $debug = false;

    protected Closure|bool $drawingControl = false;

    protected Closure|int $drawingControlPosition = MapsHelper::POSITION_TOP_CENTER;

    protected Closure|string|null $geoJsonFile = null;

    protected Closure|string|null $geoJsonDisk = null;

    protected Closure|string|null $geoJsonField = null;

    protected Closure|string|null $geoJsonProperty = null;

    protected Closure|bool $geoJsonVisible = true;

    protected Closure|null $reverseGeocodeUsing = null;

    protected Closure|null $placeUpdatedUsing = null;

    protected Closure|array $drawingModes = [
        'marker'    => true,
        'circle'    => true,
        'polygon'   => true,
        'polyline'  => true,
        'rectangle' => true,
    ];

    protected Closure|string|null $drawingField = null;

    /**
     * Main field config variables
     */
    private array $mapConfig = [
        'autocomplete'        => false,
        'autocompleteReverse' => false,
        'geolocate'           => false,
        'geolocateOnLoad'     => false,
        'geolocateLabel'      => '',
        'draggable'           => true,
        'clickable'           => false,
        'defaultLocation'     => [
            'lat' => 15.3419776,
            'lng' => 44.2171392,
        ],
        'controls'       => [],
        'drawingControl' => false,
        'drawingModes'   => [
            'marker'    => true,
            'circle'    => true,
            'rectangle' => true,
            'polygon'   => true,
            'polyline'  => true,
        ],
        'drawingField'         => null,
        'statePath'            => '',
        'layers'               => [],
        'defaultZoom'          => 8,
        'reverseGeocodeFields' => [],
        'debug'                => false,
        'gmaps'                => '',
    ];

    //	protected Closure|string|bool $geocodeFieldsReverse = false;

    private array $componentTree = [];

    public array $controls = [
        'mapTypeControl'    => true,
        'scaleControl'      => true,
        'streetViewControl' => true,
        'rotateControl'     => true,
        'fullscreenControl' => true,
        'searchBoxControl'  => false,
        'zoomControl'       => false,
    ];

    public function height(Closure|string $height): static
    {
        $this->height = $height;

        return $this;
    }

    public function getHeight(): string
    {
        return $this->evaluate($this->height);
    }

    /**
     * If specified, the $fieldName on your form will be set up as a Google Places geocomplete field, and the map marker
     * will be updated when a place is selected.
     *
     * You may optionally specify additional settings for the autocomplete with the $types, $placeField and $countries
     * params.  See documentation for the Geocomplete field for details.
     *
     * Use the autoCompleteReverse() method to enable reverse geocoding to this field, such that when the marker is
     * moved, this field will be updated with the 'formatted_address' response attribute from a reverse geocode.
     *
     *
     * @return $this
     */
    public function autocomplete(Closure|string $fieldName, Closure|array $types = [], Closure|string|null $placeField = null, Closure|array $countries = []): static
    {
        $this->autocomplete = $fieldName;
        $this->types        = $types;
        $this->placeField   = $placeField;
        $this->countries    = $countries;

        return $this;
    }

    public function getAutocomplete(): string|null
    {
        return $this->evaluate($this->autocomplete);
    }

    public function getTypes(): array
    {
        $types = $this->evaluate($this->types);

        if (count($types) === 0) {
            $types = ['geocode'];
        }

        return $types;
    }

    public function getPlaceField(): string|null
    {
        return $this->evaluate($this->placeField) ?? 'formatted_address';
    }

    public function getCountries(): array
    {
        return $this->evaluate($this->countries);
    }

    /**
     * If autocomplete() is enabled, this will enable reverse geocoding for that field.
     *
     *
     * @return $this
     */
    public function autocompleteReverse(Closure|bool $autoCompleteReverse = true): static
    {
        $this->autocompleteReverse = $autoCompleteReverse;

        return $this;
    }

    public function getAutocompleteReverse(): string|null
    {
        return $this->evaluate($this->autocompleteReverse);
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
     * Adds a geolocate button to the map which requests the user's location, and if granted will set the map marker
     * accordingly.
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
     * Request the user's location and set map marker accordingly on form load.
     *
     * @return $this
     */
    public function geolocateOnLoad(Closure|bool $geolocateOnLoad = true, Closure|bool $always = false): static
    {
        $this->geolocateOnLoad       = $geolocateOnLoad;
        $this->geolocateOnLoadAlways = $always;

        return $this;
    }

    public function getGeolocateOnLoad(): bool|null
    {
        if ($this->evaluate($this->geolocateOnLoad)) {
            $always = $this->evaluate($this->geolocateOnLoadAlways);
            $state  = parent::getState();

            if ($always || is_null($state)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Override the label to use for the geolocate feature, defaults to 'Set Current Location'
     *
     * @return $this
     */
    public function geolocateLabel(Closure|string $geolocateLabel): static
    {
        $this->geolocateLabel = $geolocateLabel;

        return $this;
    }

    public function getGeolocateLabel(): string
    {
        return $this->evaluate($this->geolocateLabel) ?? __('filament-google-maps::fgm.geolocate.label');
    }

    /**
     * Add drawing controls to the map
     *
     * @return $this
     */
    public function drawingControl(Closure|bool $drawingControl = true): static
    {
        $this->drawingControl = $drawingControl;

        return $this;
    }

    public function getDrawingControl(): bool
    {
        return $this->evaluate($this->drawingControl);
    }

    /**
     * Form field to update with GeoJSON (ish) representing drawing coordinates
     *
     * @param  Closure|string|null  $drawingField
     * @return $this
     */
    public function drawingField(Closure|string|null $drawingField = null): static
    {
        $this->drawingField = $drawingField;

        return $this;
    }

    public function getDrawingField(): ?string
    {
        $drawingField = $this->evaluate($this->drawingField);

        if ($drawingField) {
            return FieldHelper::getFieldId($drawingField, $this);
        }

        return null;
    }

    /**
     * Drawing modes, as an array of properties ...
     * [
     *    'circle' => true,
     *    'marker' => true,
     *    'polygon' => true,
     *    'polyline' => true,
     *    'rectangle' => true,
     * ]
     *
     * @return $this
     */
    public function drawingModes(Closure|array $drawingModes): static
    {
        $this->drawingModes = $drawingModes;

        return $this;
    }

    public function getDrawingModes(): array
    {
        return $this->evaluate($this->drawingModes);
    }

    /**
     * Drawing control position, using MapsHelper constants
     *
     * https://developers.google.com/maps/documentation/javascript/reference/control#ControlPosition
     *
     * @return $this
     */
    public function drawingControlPosition(Closure|int $drawingControlPosition): static
    {
        $this->drawingControlPosition = $drawingControlPosition;

        return $this;
    }

    public function getDrawingControlPosition(): int
    {
        return $this->evaluate($this->drawingControlPosition);
    }

    /**
     * Add a GeoJSON layer to the map.  $file can be a local file on the server (optional second argument specifies the
     * storage disk, defaults to 'public'), a URL, or rawdog GeoJSON.
     *
     * @return $this
     */
    public function geoJson(Closure|string $file, Closure|string $disk = 'public'): static
    {
        $this->geoJsonFile = $file;

        $this->geoJsonDisk = $disk;

        return $this;
    }

    public function getGeoJsonFile(): string|null
    {
        $file = $this->evaluate($this->geoJsonFile);

        if (filled($file)) {
            if (Str::startsWith($file, ['{', '['])) {
                return $file;
            }

            $url = parse_url($file, PHP_URL_SCHEME);

            if ($url) {
                return $file;
            } elseif (Storage::disk($this->geoJsonDisk)->exists($file)) {
                return Storage::disk($this->geoJsonDisk)->get($file);
            }
        }

        return null;
    }

    /**
     * This method controls whether the GeoJSON layer is visible or not.  Only useful if you just wish to track containing polygons
     * without displaying them.
     *
     * @return $this
     */
    public function geoJsonVisible(Closure|bool $visiblw = true): static
    {
        $this->geoJsonVisible = $visiblw;

        return $this;
    }

    public function getGeoJsonVisible(): string|null
    {
        return $this->evaluate($this->geoJsonVisible);
    }

    /**
     * This method allows you to record which polygon(s) from the GeoJSON layer the map marker is contained by.  The
     * $field arg is a field name on your form (which can be a Hidden field type).  Whenever the marker is moved, this field is
     * updated to show which polygons now contain the marker.  If no $property is given as the second argument, the data saved
     * in the field will be a GeoJSON FeatureCollection of the containing features.  If a $property is given, the data saved
     * will be a simple JSON array of that property's value from each of the containing polygons.  In both cases this will save
     * an empty collection/array if the marker is not within any polygon.
     *
     * @return $this
     */
    public function geoJsonContainsField(Closure|string|null $field = null, Closure|string|null $property = null): static
    {
        $this->geoJsonField = $field;

        $this->geoJsonProperty = $property;

        return $this;
    }

    public function getGeoJsonField(): string|null
    {
        $jsonField = $this->evaluate($this->geoJsonField);

        if ($jsonField) {
            return FieldHelper::getFieldId($jsonField, $this);
        }

        return null;
    }

    public function getGeoJsonProperty(): string|null
    {
        return $this->evaluate($this->geoJsonProperty);
    }

    //public function handleGeoJson(array $features): void
    //{
    //    $geoJsonHandler = $this->getGeoJsonHandler();
    //
    //    $this->evaluate($geoJsonHandler, [
    //        'features' => $features,
    //    ]);
    //}

    /**
     * Set the default location for new maps, accepts an array of either [$lat, $lng] or ['lat' => $lat, 'lng' => $lng],
     * or a closure which returns this
     *
     *
     * @return $this
     */
    public function defaultLocation(Closure|array $location): static
    {
        $this->defaultLocation = $location;

        return $this;
    }

    public function getDefaultLocation(): array
    {
        $position = $this->evaluate($this->defaultLocation);

        if (is_array($position)) {
            if (array_key_exists('lat', $position) && array_key_exists('lng', $position)) {
                return $position;
            } elseif (is_numeric($position[0]) && is_numeric($position[1])) {
                return [
                    'lat' => is_string($position[0]) ? round(floatval($position[0]), $this->precision) : $position[0],
                    'lng' => is_string($position[1]) ? round(floatval($position[1]), $this->precision) : $position[1],
                ];
            }
        }

        return [
            'lat' => 0,
            'lng' => 0,
        ];
    }

    /**
     * Set the default zoom level for new maps, between 1 (most distant, world level) and 20 (closest)
     *
     *
     * @return $this
     */
    public function defaultZoom(Closure|int $defaultZoom): static
    {
        $this->defaultZoom = $defaultZoom;

        return $this;
    }

    public function getDefaultZoom(): int
    {
        return $this->evaluate($this->defaultZoom);
    }

    /**
     * Sets whether the marker can be moved by dragging, default is true
     *
     *
     * @return $this
     */
    public function draggable(Closure|bool $draggable = true): static
    {
        $this->draggable = $draggable;

        return $this;
    }

    public function getDraggable(): bool
    {
        return $this->evaluate($this->draggable);
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
     * Sets whether clicking on the map sets the marker location, can be used by itself or in conjunction with
     * draggable, default is false
     *
     *
     * @return $this
     */
    public function clickable(Closure|bool $clickable = true): static
    {
        $this->clickable = $clickable;

        return $this;
    }

    public function getClickable(): bool
    {
        return $this->evaluate($this->clickable);
    }

    public function isSearchBoxControlEnabled(): bool
    {
        $controls = $this->getMapControls(false);

        return $controls['searchBoxControl'];
    }

    public function mapControls(Closure|array $controls): static
    {
        $this->mapControls = $controls;

        return $this;
    }

    /**
     * @throws JsonException
     */
    public function getMapControls($encode = true): string|array
    {
        $controls = $this->evaluate($this->mapControls);

        return $encode ? json_encode(array_merge($this->controls, $controls), JSON_THROW_ON_ERROR) : array_merge($this->controls, $controls);
    }

    public function layers(Closure|array $layers): static
    {
        $this->layers = $layers;

        return $this;
    }

    /**
     * @throws JsonException
     */
    public function getLayers(): array
    {
        return $this->evaluate($this->layers);
    }

//	private function getTopComponent(Component $component): Component
//	{
//		$parentComponent = $component->getContainer()->getParentComponent();
//
//		return $parentComponent ? $this->getTopComponent($parentComponent) : $component;
//	}
//
//	public function getFlatFields(): array
//	{
//		$topComponent = $this->getTopComponent($this->getContainer()?->getParentComponent());
//
//		$flatFields = [];
//
//		foreach ($topComponent->getContainer()->getComponents() as $component)
//		{
//			foreach ($component->getChildComponentContainers() as $container)
//			{
//				if ($container->isHidden())
//				{
//					continue;
//				}
//
//				$flatFields = array_merge($flatFields, $container->getFlatFields());
//			}
//		}
//
//		return $flatFields;
//	}

    private function getAutocompleteId(): string|null
    {
        $autoCompleteField = $this->getAutocomplete();

        if (! blank($autoCompleteField)) {
            return FieldHelper::getFieldId($autoCompleteField, $this);
        }

        return null;
    }

    /**
     * As an alternative to the built-in symbol based reverse geocode handling, you may provide a closure which will be
     * called with the 'results' array from the Google API response, and use a $set closure to update fields on the form.
     *
     * @return $this
     */
    public function reverseGeocodeUsing(?Closure $closure): static
    {
        $this->reverseGeocodeUsing = $closure;

        return $this;
    }

    public function reverseGeocodeUpdated(array $results): static
    {
        $callback = $this->reverseGeocodeUsing;

        if (! $callback) {
            return $this;
        }

        $this->evaluate($callback, [
            'results' => $results,
        ]);

        return $this;
    }

    /**
     * As an alternative to the built-in symbol based reverse geocode handling, you may provide a closure which will be
     * called with the 'results' array from the Google API response, and use a $set closure to update fields on the form.
     *
     * @return $this
     */
    public function placeUpdatedUsing(?Closure $closure): static
    {
        $this->placeUpdatedUsing = $closure;

        return $this;
    }

    public function placeUpdated(array $place): static
    {
        $callback = $this->placeUpdatedUsing;

        if (! $callback) {
            return $this;
        }

        $this->evaluate($callback, [
            'place' => $place,
        ]);

        return $this;
    }

    /**
     * Create json configuration string
     */
    public function getMapConfig(): string
    {
        $config = array_merge($this->mapConfig, [
            'autocomplete'           => $this->getAutocompleteId(),
            'types'                  => $this->getTypes(),
            'countries'              => $this->getCountries(),
            'placeField'             => $this->getPlaceField(),
            'autocompleteReverse'    => $this->getAutocompleteReverse(),
            'geolocate'              => $this->getGeolocate(),
            'geolocateLabel'         => $this->getGeolocateLabel(),
            'geolocateOnLoad'        => $this->getGeolocateOnLoad(),
            'draggable'              => $this->getDraggable(),
            'clickable'              => $this->getClickable(),
            'defaultLocation'        => $this->getDefaultLocation(),
            'statePath'              => $this->getStatePath(),
            'controls'               => $this->getMapControls(),
            'drawingControl'         => $this->getDrawingControl(),
            'drawingControlPosition' => $this->getDrawingControlPosition(),
            'drawingModes'           => $this->getDrawingModes(),
            'drawingField'           => $this->getDrawingField(),
            'layers'                 => $this->getLayers(),
            'reverseGeocodeFields'   => $this->getReverseGeocode(),
            'reverseGeocodeUsing'    => $this->reverseGeocodeUsing !== null,
            'placeUpdatedUsing'      => $this->placeUpdatedUsing !== null,
            'defaultZoom'            => $this->getDefaultZoom(),
            'geoJson'                => $this->getGeoJsonFile(),
            'geoJsonField'           => $this->getGeoJsonField(),
            'geoJsonProperty'        => $this->getGeoJsonProperty(),
            'geoJsonVisible'         => $this->getGeoJsonVisible(),
            'debug'                  => $this->getDebug(),
            'gmaps'                  => MapsHelper::mapsUrl(false, $this->getDrawingControl() ? ['drawing'] : []),
        ]);

        //ray($config);

        return json_encode($config);
    }

    public function getState(): mixed
    {
        $state = parent::getState();

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

    public function mapsHasJs(): bool
    {
        return true;
    }

    public function mapsJsUrl(): string
    {
        $manifest = json_decode(file_get_contents(__DIR__.'/../../dist/mix-manifest.json'), true);

        return url($manifest['/cheesegrits/filament-google-maps/filament-google-maps.js']);
    }

    public function mapsHasCss(): bool
    {
        return true;
    }

    public function mapsCssUrl(): string
    {
        $manifest = json_decode(file_get_contents(__DIR__.'/../../dist/mix-manifest.json'), true);

        return url($manifest['/cheesegrits/filament-google-maps/filament-google-maps.css']);
    }
}
