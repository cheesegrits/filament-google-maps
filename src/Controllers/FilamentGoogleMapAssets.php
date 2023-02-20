<?php

namespace Cheesegrits\FilamentGoogleMaps\Controllers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;
use Livewire\Controllers\CanPretendToBeAFile;

class FilamentGoogleMapAssets
{
    use CanPretendToBeAFile;

    public function __invoke($file)
    {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'png') {
            $cacheKey = pathinfo($file, PATHINFO_FILENAME);

            if (Cache::has($cacheKey)) {
                return Response::streamDownload(
                    function () use ($cacheKey) {
                        echo Cache::get($cacheKey);
                    },
                    $file,
                    [
                        'Content-Type' => 'image/png',
                    ]
                );
            }
        } else {
            switch ($file) {
                case 'filament-google-maps.css':
                    return $this->pretendResponseIsFile(__DIR__.'/../../dist/cheesegrits/filament-google-maps/filament-google-maps.css', 'text/css; charset=utf-8');
                case 'filament-google-maps.css.map':
                    return $this->pretendResponseIsFile(__DIR__.'/../../dist/cheesegrits/filament-google-maps/filament-google-maps.css.map', 'application/json; charset=utf-8');
                case 'filament-google-maps.js':
                    return $this->pretendResponseIsFile(__DIR__.'/../../dist/cheesegrits/filament-google-maps/filament-google-maps.js', 'application/javascript; charset=utf-8');
                case 'filament-google-maps.js.map':
                    return $this->pretendResponseIsFile(__DIR__.'/../../dist/cheesegrits/filament-google-maps/filament-google-maps.js.map', 'application/json; charset=utf-8');
                case 'filament-google-geocomplete.js':
                    return $this->pretendResponseIsFile(__DIR__.'/../../dist/cheesegrits/filament-google-maps/filament-google-geocomplete.js', 'application/javascript; charset=utf-8');
                case 'filament-google-geocomplete.js.map':
                    return $this->pretendResponseIsFile(__DIR__.'/../../dist/cheesegrits/filament-google-maps/filament-google-geocomplete.js.map', 'application/json; charset=utf-8');
                case 'filament-google-maps-widget.css':
                    return $this->pretendResponseIsFile(__DIR__.'/../../dist/cheesegrits/filament-google-maps/filament-google-maps-widget.css', 'text/css; charset=utf-8');
                case 'filament-google-maps-widget.css.map':
                    return $this->pretendResponseIsFile(__DIR__.'/../../dist/cheesegrits/filament-google-maps/filament-google-maps-widget.css.map', 'application/json; charset=utf-8');
                case 'filament-google-maps-widget.js':
                    return $this->pretendResponseIsFile(__DIR__.'/../../dist/cheesegrits/filament-google-maps/filament-google-maps-widget.js', 'application/javascript; charset=utf-8');
                case 'filament-google-maps-widget.js.map':
                    return $this->pretendResponseIsFile(__DIR__.'/../../dist/cheesegrits/filament-google-maps/filament-google-maps-widget.js.map', 'application/json; charset=utf-8');
            }
        }

        abort(404);
    }
}
