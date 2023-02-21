<?php

namespace Cheesegrits\FilamentGoogleMaps\Commands\Aliases;

use Cheesegrits\FilamentGoogleMaps\Commands;

class GeocodeTable extends Commands\GeocodeTable
{
    protected $hidden = true;

    protected $signature = 'fgm:geocode-table {model?} {--lat=} {--lng=} {--fields=} {--processed=} {--rate-limit=} {--verbose?}}';
}
