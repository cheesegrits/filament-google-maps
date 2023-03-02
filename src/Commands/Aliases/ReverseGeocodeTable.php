<?php

namespace Cheesegrits\FilamentGoogleMaps\Commands\Aliases;

use Cheesegrits\FilamentGoogleMaps\Commands;

class ReverseGeocodeTable extends Commands\ReverseGeocodeTable
{
    protected $hidden = true;

    protected $signature = 'fgm:reverse-geocode-table {model?} {--lat=} {--lng=} {--fields=*} {--processed=} {--rate-limit=} {--verbose?}}';
}
