<?php

namespace Cheesegrits\FilamentGoogleMaps\Commands\Aliases;

use Cheesegrits\FilamentGoogleMaps\Commands;

class ModelCode extends Commands\ModelCode
{
    protected $hidden = true;

    protected $signature = 'fgm:model-code {model?} {--lat=} {--lng=} {--location=} ';
}
