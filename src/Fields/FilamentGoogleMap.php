<?php

namespace Cheesegrits\FilamentGoogleMaps\Fields;

use Closure;
use Exception;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Contracts\CanConcealComponents;
use Filament\Forms\Components\Field;
use JsonException;

class FilamentGoogleMap extends Field
{

    protected string $view = 'filament-google-maps::fields.filament-google-maps';

    protected int $precision = 8;

    protected array|Closure|null $defaultLocation;

    protected Closure|int $defaultZoom = 8;

    protected Closure|bool $draggable = true;

    protected Closure|bool $clickable = false;

    protected Closure|array $mapControls = [];

    protected Closure|string $height = '350px';

    protected Closure|string|bool $autocomplete = false;

    protected Closure|string|bool $autocompleteReverse = false;

    private array $componentTree = [];

    /**
     * Main field config variables
     * @var array
     */
    private array $mapConfig = [
        'statePath'           => '',
        'draggable'           => true,
        'defaultLocation'     => [
            'lat' => 15.3419776,
            'lng' => 44.2171392,
        ],
        'defaultZoom'         => 8,
        'gmaps'               => '',
        'autocomplete'        => false,
        'autocompleteReverse' => false,
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
     * @param Closure|string $fieldName
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
     * @param Closure|string $autoCompleteReverse
     * @return $this
     */
    public function autocompleteReverse(Closure|string $autoCompleteReverse): static
    {
        $this->autocompleteReverse = $autoCompleteReverse;

        return $this;
    }

    public function getAutocompleteReverse(): string|null
    {
        return $this->evaluate($this->autocompleteReverse);
    }

    /**
     * Set the default location for new maps, accepts an array of either [$lat, $lng] or ['lat' => $lat, 'lng' => $lng],
     * or a closure which returns this
     *
     * @param Closure|array $location
     * @return $this
     */
    public function defaultLocation(\Closure|array $location): static
    {
        $this->defaultLocation = $location;

        return $this;
    }

    public function getDefaultLocation(): array
    {
        $position = $this->evaluate($this->defaultLocation);

        if (is_array($position))
        {
            if (array_key_exists('lat', $position) && array_key_exists('lng', $position))
            {
                return $position;
            }
            elseif (is_numeric($position[0]) && is_numeric($position[1]))
            {
                return [
                    'lat' => is_string($position[0]) ? round(floatVal($position[0]), $this->precision) : $position[0],
                    'lng' => is_string($position[1]) ? round(floatVal($position[1]), $this->precision) : $position[1],
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
     * @param Closure|int $defaultZoom
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
     * @param Closure|bool $draggable
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
     * Sets whether clicking on the map sets the marker location, can be used by itself or in conjunction with
     * draggable, default is false
     *
     * @param Closure|bool $clickable
     * @return $this
     */
    public function clickable(Closure|bool $clickable = false): static
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

    private function getTopComponent(Component $component): Component
    {
        $parentComponent = $component->getContainer()->getParentComponent();

        return $parentComponent ?? $this->getTopComponent($parentComponent);
    }

    public function getFlatFields(): array
    {
        $topComponent = $this->getTopComponent($this->getContainer()->getParentComponent());

        $flatFields = [];

        foreach ($topComponent->getContainer()->getComponents() as $component)
        {
            foreach ($component->getChildComponentContainers() as $container)
            {
                if ($container->isHidden())
                {
                    continue;
                }

                $flatFields = array_merge($flatFields, $container->getFlatFields());
            }
        }

        return $flatFields;
    }

    private function getAutocompleteId(): string|false
    {
        $autoCompleteId    = false;
        $autoCompleteField = $this->getAutocomplete();

        if ($autoCompleteField)
        {
            $flatFields = $this->getFlatFields();

            if (array_key_exists($autoCompleteField, $flatFields))
            {
                $autoCompleteId = $flatFields[$autoCompleteField]->getId();
            }
        }

        return $autoCompleteId;
    }

    /**
     * Create json configuration string
     * @return string
     */
    public function getMapConfig(): string
    {
        $gmaps = 'https://maps.googleapis.com/maps/api/js'
            . '?key=' . config('filament-google-maps.key')
            . '&libraries=places'
            . '&v=weekly'
            . '&language=' . app()->getLocale();

        $config = json_encode(
            array_merge($this->mapConfig, [
                'autocomplete'        => $this->getAutocompleteId(),
                'autocompleteReverse' => $this->getAutocompleteReverse(),
                'draggable'           => $this->getDraggable(),
                'clickable'           => $this->getClickable(),
                'defaultLocation'     => $this->getDefaultLocation(),
                'statePath'           => $this->getStatePath(),
                'controls'            => $this->getMapControls(),
                'gmaps'               => $gmaps,
            ])
        );

        //ray($config);

        return $config;
    }

    public function getState()
    {
        $state = parent::getState();

        if (is_array($state))
        {
            return $state;
        }
        else
        {
            try
            {
                return @json_decode($state, true, 512, JSON_THROW_ON_ERROR);
            } catch (Exception $e)
            {
                return [
                    'lat' => 0,
                    'lng' => 0
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
        $manifest = json_decode(file_get_contents(__DIR__ . '/../../dist/mix-manifest.json'), true);
        return url($manifest['/cheesegrits/filament-google-maps/filament-google-maps.js']);
    }

    public function hasCss(): bool
    {
        return false;
    }

    public function cssUrl(): string
    {
        $manifest = json_decode(file_get_contents(__DIR__ . '/../../dist/mix-manifest.json'), true);
        return url($manifest['/cheesegrits/filament-google-maps/filament-google-maps.css']);
    }

}
