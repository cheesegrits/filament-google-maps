<?php

namespace Cheesegrits\FilamentGoogleMaps\Commands\Aliases;

use Cheesegrits\FilamentGoogleMaps\Commands;

class Geocode extends Commands\Geocode
{
    protected $hidden = true;

    protected $signature = 'fgm:geocode {--address=} {--A|array} {--C|command} {--G|args}';
}
