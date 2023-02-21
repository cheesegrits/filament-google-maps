<?php

namespace Cheesegrits\FilamentGoogleMaps\Commands\Aliases;

use Cheesegrits\FilamentGoogleMaps\Commands;

class MakeWidgetCommand extends Commands\MakeWidgetCommand
{
    protected $hidden = true;

    protected $signature = 'fgm:make-widget {name?} {model?} {--R|resource=} {--M|map} {--T|table} {--F|force}';
}
