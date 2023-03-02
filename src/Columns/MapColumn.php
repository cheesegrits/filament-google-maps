<?php

namespace Cheesegrits\FilamentGoogleMaps\Columns;

use Cheesegrits\FilamentGoogleMaps\Helpers\MapsHelper;
use Closure;
use Exception;
use Filament\Tables\Columns\Column;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\ComponentAttributeBag;
use Mastani\GoogleStaticMap\GoogleStaticMap;

class MapColumn extends Column
{
    protected string $view = 'filament-google-maps::columns.filament-google-maps-column';

    protected string|Closure|null $icon = null;

    protected string|Closure|null $type = null;

    protected int|Closure $height = 150;

    protected int|Closure $width = 200;

    protected int|string|Closure|null $zoom = 13;

    protected array|Closure $extraImgAttributes = [];

    protected int|Closure $ttl = 60 * 60 * 24 * 30;

    /**
     * Fully qualified URL to a PNG icon to use for the marker pin
     *
     *
     * @return $this
     */
    public function icon(string|Closure|null $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    public function getIcon(): string|null
    {
        return $this->evaluate($this->icon);
    }

    /**
     * One of 'satellite', 'hybrid', 'roadmap' or 'terrain', defaults to 'roadmap'
     *
     *
     * @return $this
     */
    public function type(string|Closure|null $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getType(): string
    {
        $type = $this->evaluate($this->type);

        if (! in_array($type, ['satellite', 'hybrid', 'roadmap', 'terrain'])) {
            $type = 'roadmap';
        }

        return $type;
    }

    /**
     * Height in PX for the image (integer value only, passed to the Google API, only understands px as int)
     *
     *
     * @return $this
     */
    public function height(int|Closure $height): static
    {
        $this->height = $height;

        return $this;
    }

    public function getHeight(): int
    {
        return $this->evaluate($this->height);
    }

    /**
     * Width in PX for the image (integer value only, passed to the Google API, only understands px as int)
     *
     *
     * @return $this
     */
    public function width(int|Closure $width): static
    {
        $this->width = $width;

        return $this;
    }

    public function getWidth(): int
    {
        return $this->evaluate($this->width);
    }

    /**
     * Convenience method, sets width and height the same in PX
     * (integer value only, passed to the Google API, only understands px as int)
     *
     *
     * @return $this
     */
    public function size(int|string|Closure $size): static
    {
        $this->width($size);
        $this->height($size);

        return $this;
    }

    /**
     * Zoom level, between 1 and 20
     * (roughly ... 1 is world, 5 is landmass/continent, 10 is city, 15 is streets, 20 is houses)
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

    public function ttl(Closure|int $ttl): static
    {
        $this->ttl = $ttl;

        return $this;
    }

    /**
     * Time in seconds to cache the image from the Maps API, default is 30 days (60 * 60 * 24 * 30) which is the max
     * that Google allows.  Be careful setting this too low, as it can generate a LOT of API hits, which could incur
     * significant cost.
     */
    public function getTtl(): int
    {
        return $this->evaluate($this->ttl);
    }

    private function getMarker($location): string
    {
        $marker = $location;

        $icon = $this->getIcon();

        if ($icon) {
            $marker = 'icon:'.$icon.'|'.$marker;
        }

        return $marker;
    }

    /**
     * An optional array of additional attributes to apply to the img tag
     *
     *
     * @return $this
     */
    public function extraImgAttributes(array|Closure $attributes): static
    {
        $this->extraImgAttributes = $attributes;

        return $this;
    }

    public function getExtraImgAttributes(): array
    {
        return $this->evaluate($this->extraImgAttributes);
    }

    public function getExtraImgAttributeBag(): ComponentAttributeBag
    {
        return new ComponentAttributeBag($this->getExtraImgAttributes());
    }

    private function getStaticMapURL(): string|null
    {
        $location = $this->getState();

        if (MapsHelper::isLocationEmpty($location)) {
            return null;
        }

        $map = new GoogleStaticMap(MapsHelper::mapsKey(true));

        $url = $map->setCenterLatLng($location['lat'], $location['lng'])
            ->setZoom($this->getZoom())
            ->setMapType($this->getType())
            ->setSize($this->getWidth(), $this->getHeight());

        if ($this->getIcon()) {
            $url->addMarkerLatLngWithIcon($location['lat'], $location['lng'], $this->getIcon());
        } else {
            $url->addMarkerLatLng($location['lat'], $location['lng'], '1', 'red');
        }

        if (MapsHelper::hasSigningKey()) {
            $url->setSecret(MapsHelper::mapsSigningKey());
        }

        $src = $url->make();

        if ($language = MapsHelper::mapsLanguage(true)) {
            $src .= '&language='.$language;
        }

        return $src;
    }

    public static function cacheImage($url): string|null
    {
        $cacheKey = 'fgm-'.md5($url);

        if (! Cache::has($cacheKey)) {
            $map = file_get_contents($url);

            $store    = config('filament-google-maps.cache.store', null);
            $duration = config('filament-google-maps.cache.duration', 0);

            if ($map) {
                Cache::store($store)->put($cacheKey, $map, $duration);
            } else {
                return null;
            }
        }

        return $cacheKey;
    }

    public function getImagePath(): ?string
    {
        $url = $this->getStaticMapURL();

        if (empty($url)) {
            return null;
        }

        $cacheKey = static::cacheImage($url);

        if (empty($cacheKey)) {
            return null;
        }

        return url('/cheesegrits/filament-google-maps/'.$cacheKey.'.png');
    }

    public function getState()
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
