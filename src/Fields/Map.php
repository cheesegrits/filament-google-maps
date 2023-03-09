<?php

namespace Cheesegrits\FilamentGoogleMaps\Fields;

use Cheesegrits\FilamentGoogleMaps\Helpers\FieldHelper;
use Cheesegrits\FilamentGoogleMaps\Helpers\MapsHelper;
use Closure;
use Exception;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Field;
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

    protected Closure|bool $autocompleteReverse = false;

    protected Closure|bool $geolocate = false;

    protected Closure|string|null $geolocateLabel = null;

    protected Closure|array $reverseGeocode = [];

    protected Closure|bool $debug = false;

    /**
     * Main field config variables
     */
    private array $mapConfig = [
        'autocomplete'        => false,
        'autocompleteReverse' => false,
        'geolocate'           => false,
        'geolocateLabel'      => '',
        'draggable'           => true,
        'clickable'           => false,
        'defaultLocation'     => [
            'lat' => 15.3419776,
            'lng' => 44.2171392,
        ],
        'controls'             => [],
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
     * Use the autoCompleteReverse() method to enable reverse geocoding to this field, such that when the marker is
     * moved, this field will be updated with the 'formatted_address' response attribute from a reverse geocode.
     *
     *
     * @return $this
     */
    public function autocomplete(Closure|string $fieldName): static
    {
        $this->autocomplete = $fieldName;

        return $this;
    }

    public function getAutocomplete(): string|null
    {
        return $this->evaluate($this->autocomplete);
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
        return $this->controls['searchBoxControl'];
    }

    public function mapControls(Closure|array $controls): static
    {
        $this->mapControls = $controls;

        return $this;
    }

    /**
     * @throws JsonException
     */
    public function getMapControls(): string
    {
        $controls = $this->evaluate($this->mapControls);

        return json_encode(array_merge($this->controls, $controls), JSON_THROW_ON_ERROR);
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

    public function getAutocompleteId(): string|null
    {
        $autoCompleteField = $this->getAutocomplete();

        if (! blank($autoCompleteField)) {
            return FieldHelper::getFieldId($autoCompleteField, $this);
        }

        return null;
    }

    public function getMapsUrl(): string
    {
        return MapsHelper::mapsUrl();
    }

    /**
     * Create json configuration string
     */
    public function getMapConfig(): string
    {
        $config = json_encode(
            array_merge($this->mapConfig, [
                'autocomplete'         => $this->getAutocompleteId(),
                'autocompleteReverse'  => $this->getAutocompleteReverse(),
                'geolocate'            => $this->getGeolocate(),
                'geolocateLabel'       => $this->getGeolocateLabel(),
                'draggable'            => $this->getDraggable(),
                'clickable'            => $this->getClickable(),
                'defaultLocation'      => $this->getDefaultLocation(),
                'statePath'            => $this->getStatePath(),
                'controls'             => $this->getMapControls(),
                'layers'               => $this->getLayers(),
                'reverseGeocodeFields' => $this->getReverseGeocode(),
                'defaultZoom'          => $this->getDefaultZoom(),
                'debug'                => $this->getDebug(),
                'gmaps'                => MapsHelper::mapsUrl(),
            ])
        );

        //ray($config);

        return $config;
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
}
