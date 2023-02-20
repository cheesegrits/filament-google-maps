<?php

namespace Cheesegrits\FilamentGoogleMaps\Commands\Aliases;

use Cheesegrits\FilamentGoogleMaps\Commands;

class ReverseGeocode extends Commands\ReverseGeocode
{
    protected $hidden = true;

    protected $signature = 'fgm:reverse-geocode {--lat=} {--lng=} {--C|components}';
}
