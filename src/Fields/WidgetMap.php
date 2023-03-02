<?php

namespace Cheesegrits\FilamentGoogleMaps\Fields;

use Cheesegrits\FilamentGoogleMaps\Helpers\MapsHelper;
use Closure;
use Exception;
use Filament\Forms\Components\Field;
use JsonException;

class WidgetMap extends Field
{
    protected string $view = 'filament-google-maps::fields.filament-google-widget-map';

    protected int $precision = 8;

    protected array|Closure|null $center = [0, 0];

    protected Closure|int $zoom = 8;

    protected Closure|bool $draggable = true;

    protected Closure|bool $clickable = false;

    protected Closure|array $mapControls = [];

    protected Closure|array $layers = [];

    protected Closure|string $height = '350px';

    protected Closure|bool $clustering = false;

    protected Closure|bool $fitToBounds = false;

    protected Closure|array $markers = [];

    /**
     * Main field config variables
     */
    private array $mapConfig = [
        'draggable' => false,
        'center'    => [
            'lat' => 15.3419776,
            'lng' => 44.2171392,
        ],
        'zoom'       => 8,
        'fit'        => true,
        'gmaps'      => '',
        'clustering' => true,
    ];

    public array $controls = [
        'mapTypeControl'    => true,
        'scaleControl'      => true,
        'streetViewControl' => true,
        'rotateControl'     => true,
        'fullscreenControl' => true,
        'searchBoxControl'  => false,
        'zoomControl'       => false,
    ];

    public function getHeading(): string
    {
        return 'get heading here';
    }

    public function getFilters(): array
    {
        return [];
    }

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
     * Set the default location for new maps, accepts an array of either [$lat, $lng] or ['lat' => $lat, 'lng' => $lng],
     * or a closure which returns this
     *
     *
     * @return $this
     */
    public function center(Closure|array $center): static
    {
        $this->center = $center;

        return $this;
    }

    public function getCenter(): array
    {
        $position = $this->evaluate($this->center);

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
    public function zoom(Closure|int $zoom): static
    {
        $this->zoom = $zoom;

        return $this;
    }

    public function getZoom(): int
    {
        return $this->evaluate($this->zoom);
    }

    /**
     * An array of markers as ...
     *
     * [
     *      'location' = > [ 'lat' => 12.34, 'lng' => -12.34 ],
     *      'label' => 'Foo bar',
     *      'icon' => [ 'url' => 'path/to/foo.svg', 'type' => 'svg', 'scale' = [35,35] ]
     * ]
     *
     * @param  Closure|array  $markers
     * @return $this
     */
    public function markers(Closure|int $markers): static
    {
        $this->markers = $markers;

        return $this;
    }

    public function getMarkers(): array
    {
        return $this->evaluate($this->markers);
    }

    public function getCachedData()
    {
        return $this->getMarkers();
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

    /**
     * Clusters the map markers.
     *
     *
     * @return $this
     */
    public function clustering(Closure|bool $clustering = true): static
    {
        $this->clustering = $clustering;

        return $this;
    }

    public function getClustering(): bool
    {
        return $this->evaluate($this->clustering);
    }

    /**
     * Fit the map bounds to all markers.
     *
     *
     * @return $this
     */
    public function fitToBounds(Closure|bool $fitToBounds = true): static
    {
        $this->fitToBounds = $fitToBounds;

        return $this;
    }

    public function getFitToBounds(): bool
    {
        return $this->evaluate($this->fitToBounds);
    }

    /**
     * Create json configuration string
     */
    public function getMapConfig(): string
    {
        //		return json_encode(
        //			array_merge($this->mapConfig, [
        //				'clustering' => self::getClustering(),
        //				'layers'     => $this->getLayers(),
        //				'zoom'       => $this->getZoom(),
        //				'controls'   => $this->controls,
        //				'fit'        => $this->getFitToBounds(),
        //				'gmaps'      => MapsHelper::mapsUrl(),
        //			])
        //		);

        $config = json_encode(
            array_merge($this->mapConfig, [
                'clustering' => self::getClustering(),
                'layers'     => $this->getLayers(),
                'zoom'       => $this->getZoom(),
                'controls'   => $this->getMapControls(),
                //				'center'     => $this->getCenter(),
                'fit'   => $this->getFitToBounds(),
                'gmaps' => MapsHelper::mapsUrl(),
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

    public function hasJs(): bool
    {
        return true;
    }

    public function jsUrl(): string
    {
        $manifest = json_decode(file_get_contents(__DIR__.'/../../dist/mix-manifest.json'), true);

        return url($manifest['/cheesegrits/filament-google-maps/filament-google-maps-widget.js']);
    }

    public function hasCss(): bool
    {
        return false;
    }

    public function cssUrl(): string
    {
        $manifest = json_decode(file_get_contents(__DIR__.'/../../dist/mix-manifest.json'), true);

        return url($manifest['/cheesegrits/filament-google-maps/filament-google-maps-widget.css']);
    }
}
