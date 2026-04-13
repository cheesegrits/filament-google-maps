<?php

/**
 * Derived from:
 *
 * https://github.com/mastani/laravel-google-static-map
 *
 * The author seems to have abandoned the project.
 */

namespace Cheesegrits\FilamentGoogleMaps\Helpers\GoogleStaticMap;

class GoogleStaticMap
{
    private $apiKey;

    private $apiSecret;

    private $center;

    private $zoom = 15;

    private $scale = '1';

    private $size = '600x300';

    private $mapType = MapType::RoadMap;

    private $mapId = null;

    private $format = Format::JPG;

    private $markers = [];

    /**
     * Construct class.
     *
     * @param  string  $apiKey
     * @return $this
     */
    public function __construct($apiKey = '')
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    /**
     * Set signing secret key.
     *
     * @param  string  $apiSecret
     * @return $this
     */
    public function setSecret($apiSecret)
    {
        $this->apiSecret = $apiSecret;

        return $this;
    }

    /**
     * Set map center with address.
     *
     * @param  string  $location
     * @return $this
     */
    public function setCenter($location)
    {
        $this->center = $location;

        return $this;
    }

    /**
     * Set map center with latitude and longitude.
     *
     * @param  float  $latitude
     * @param  float  $longitude
     * @return $this
     */
    public function setCenterLatLng($latitude, $longitude)
    {
        $this->center = $latitude . ',' . $longitude;

        return $this;
    }

    /**
     * Set map zoom.
     *
     * @param  int  $zoom
     * @return $this
     */
    public function setZoom($zoom)
    {
        $this->zoom = $zoom;

        return $this;
    }

    /**
     * Set map scale.
     *
     * @param  int  $scale
     * @return $this
     */
    public function setScale($scale)
    {
        $this->scale = $scale;

        return $this;
    }

    /**
     * Set map size.
     *
     * @param  int  $width
     * @param  int  $height
     * @return $this
     */
    public function setSize($width, $height)
    {
        $this->size = $width . 'x' . $height;

        return $this;
    }

    /**
     * Set map type.
     *
     * @param  string  $mapType
     * @return $this
     */
    public function setMapType($mapType)
    {
        $this->mapType = $mapType;

        return $this;
    }

    /**
     * Set map ID.
     *
     * @param  string  $mapId
     * @return $this
     */
    public function setMapId($mapId)
    {
        $this->mapId = $mapId;

        return $this;
    }

    /**
     * Set map image format.
     *
     * @param  string  $format
     * @return $this
     */
    public function setFormat($format)
    {
        $this->format = $format;

        return $this;
    }

    /**
     * Add marker to map.
     *
     * @param  string  $center
     * @param  string  $label
     * @param  string  $color
     * @param  string  $size
     * @return $this
     */
    public function addMarker($center, $label, $color, $size = Size::Medium)
    {
        $marker['type']     = 'simple';
        $marker['location'] = $center;
        $marker['label']    = $label;
        $marker['color']    = $color;
        $marker['size']     = $size;

        $this->markers[] = $marker;

        return $this;
    }

    /**
     * Add marker to map with latitude and longitude.
     *
     * @param  float  $latitude
     * @param  float  $longitude
     * @param  string  $label
     * @param  string  $color
     * @param  string  $size
     * @return $this
     */
    public function addMarkerLatLng($latitude, $longitude, $label, $color, $size = Size::Medium)
    {
        $marker['type']     = 'simple';
        $marker['location'] = $latitude . ',' . $longitude;
        $marker['label']    = $label;
        $marker['color']    = $color;
        $marker['size']     = $size;

        $this->markers[] = $marker;

        return $this;
    }

    /**
     * Add custom marker to map.
     *
     * @param  string  $center
     * @param  string  $icon
     * @param  bool  $shadow
     * @return $this
     */
    public function addMarkerWithIcon($center, $icon, $shadow = false)
    {
        $marker['type']     = 'icon';
        $marker['location'] = $center;
        $marker['icon']     = $icon;
        $marker['shadow']   = $shadow;

        $this->markers[] = $marker;

        return $this;
    }

    /**
     * Add marker to map with latitude and longitude.
     *
     * @param  float  $latitude
     * @param  float  $longitude
     * @param  string  $icon
     * @param  bool  $shadow
     * @return $this
     */
    public function addMarkerLatLngWithIcon($latitude, $longitude, $icon, $shadow = false)
    {
        $marker['type']     = 'icon';
        $marker['location'] = $latitude . ',' . $longitude;
        $marker['icon']     = $icon;
        $marker['shadow']   = $shadow;

        $this->markers[] = $marker;

        return $this;
    }

    /**
     * Make url string.
     *
     * @return string
     */
    public function make()
    {
        $baseUrl = 'https://maps.googleapis.com';
        $url     = '/maps/api/staticmap?';

        if (strlen($this->apiKey) > 0) {
            $url .= 'key=' . $this->apiKey . '&';
        }
        if ($this->center != null) {
            $url .= 'center=' . $this->center;
        }
        if ($this->zoom != 0) {
            $url .= '&zoom=' . $this->zoom;
        }
        if ($this->scale != 0) {
            $url .= '&scale=' . $this->scale;
        }
        $url .= '&size=' . $this->size;
        $url .= '&maptype=' . $this->mapType;
        if (! is_null($this->mapId)) {
            $url .= '&map_id=' . $this->mapId;
        }
        $url .= '&format=' . $this->format;
        $url .= '&visual_refresh=true';

        foreach ($this->markers as $marker) {
            $decode = '';

            if ($marker['type'] == 'simple') {
                if (! empty($marker['size'])) {
                    $decode .= 'size:' . $marker['size'] . '%7C';
                }

                if (! empty($marker['color'])) {
                    $decode .= 'color:' . $marker['color'] . '%7C';
                }

                if (! empty($marker['label'])) {
                    $decode .= 'label:' . $marker['label'] . '%7C';
                }

                if (! empty($marker['location'])) {
                    $decode .= $marker['location'];
                }
            } elseif ($marker['type'] == 'icon') {
                if (! empty($marker['icon'])) {
                    $decode .= 'icon:' . $marker['icon'] . '%7C';
                }

                if (isset($marker['shadow'])) {
                    $decode .= 'shadow:' . ($marker['shadow'] ? 'true' : 'false') . '%7C';
                }

                if (! empty($marker['location'])) {
                    $decode .= $marker['location'];
                }
            }

            $url .= '&markers=' . $decode;
        }

        if (isset($this->apiSecret) && strlen($this->apiSecret) > 0) {
            $url = $this->signUrl($url);
        }

        return $baseUrl . $url;
    }

    /**
     * Download map image.
     *
     * @param  string  $path
     * @param  int  $name_length
     * @return string
     */
    public function download($path = '.' . DIRECTORY_SEPARATOR, $name_length = 10)
    {
        $name = $this->generateRandomString($name_length) . '.' . $this->format;
        $path = rtrim(realpath($path), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $name;

        $url     = $this->make();
        $content = file_get_contents($url);
        file_put_contents($path, $content);

        return $path;
    }

    /**
     * @see https://github.com/geocoder-php/Geocoder/blob/21e562a5ad595c6fee7a33ae90e0b42dc8866c23/src/Geocoder/Provider/GoogleMapsBusinessProvider.php#L82
     */
    protected function signUrl($url)
    {
        // Decode the private key into its binary format
        $decodedKey = base64_decode(str_replace(['-', '_'], ['+', '/'], $this->apiSecret));
        // Create a signature using the private key and the URL-encoded
        // string using HMAC SHA1. This signature will be binary.
        $signature        = hash_hmac('sha1', $url, $decodedKey, true);
        $encodedSignature = str_replace(['+', '/'], ['-', '_'], base64_encode($signature));

        return sprintf('%s&signature=%s', $url, $encodedSignature);
    }

    /**
     * @see https://stackoverflow.com/a/4356295/4487671
     */
    protected function generateRandomString($length = 10)
    {
        $characters       = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString     = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }
}
