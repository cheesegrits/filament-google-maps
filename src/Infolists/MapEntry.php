<?php

namespace Cheesegrits\FilamentGoogleMaps\Infolists;

use Cheesegrits\FilamentGoogleMaps\Helpers\FieldHelper;
use Cheesegrits\FilamentGoogleMaps\Helpers\MapsHelper;
use Closure;
use Exception;
use Filament\Infolists\Components\Entry;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use JsonException;

class MapEntry extends Entry
{
    protected string $view = 'filament-google-maps::infolists.filament-google-maps-entry';

    protected int $precision = 8;

    protected array|Closure|null $defaultLocation = [0, 0];

    protected Closure|int $defaultZoom = 8;

    protected Closure|array $mapControls = [];

    protected Closure|array $layers = [];

    protected Closure|string $height = '350px';

    protected Closure|string|null $autocomplete = null;

    protected Closure|string|null $geoJsonFile = null;

    protected Closure|string|null $geoJsonDisk = null;

    protected Closure|bool $geoJsonVisible = true;

    protected Closure|string|null $drawingField = null;

    /**
     * Main field config variables
     */
    private array $mapConfig = [
        'defaultLocation' => [
            'lat' => 15.3419776,
            'lng' => 44.2171392,
        ],
        'controls'     => [],
        'drawingField' => null,
        'statePath'    => '',
        'layers'       => [],
        'defaultZoom'  => 8,
        'gmaps'        => '',
    ];

    private array $componentTree = [];

    public array $controls = [
        'mapTypeControl'    => true,
        'scaleControl'      => true,
        'streetViewControl' => true,
        'rotateControl'     => true,
        'fullscreenControl' => true,
        'searchBoxControl'  => false,
        'zoomControl'       => true,
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
     * Form field to update with GeoJSON (ish) representing drawing coordinates
     *
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

    public function getGeoJsonFile(): ?string
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

    public function getGeoJsonVisible(): ?string
    {
        return $this->evaluate($this->geoJsonVisible);
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

    public function getMapsUrl(): string
    {
        return MapsHelper::mapsUrl(false);
    }

    /**
     * Create json configuration string
     */
    public function getMapConfig(): string
    {
        $config = array_merge($this->mapConfig, [
            'defaultLocation' => $this->getDefaultLocation(),
            'statePath'       => $this->getStatePath(),
            'controls'        => $this->getMapControls(false),
            'drawingField'    => $this->getDrawingField(),
            'layers'          => $this->getLayers(),
            'defaultZoom'     => $this->getDefaultZoom(),
            'geoJson'         => $this->getGeoJsonFile(),
            'geoJsonProperty' => $this->getGeoJsonProperty(),
            'geoJsonVisible'  => $this->getGeoJsonVisible(),
            'gmaps'           => MapsHelper::mapsUrl(false, $this->getDrawingControl() ? ['drawing'] : []),
        ]);

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
                return $this->getDefaultLocation();
            }
        }
    }
}
