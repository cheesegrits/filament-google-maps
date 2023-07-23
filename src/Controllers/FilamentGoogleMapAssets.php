<?php

namespace Cheesegrits\FilamentGoogleMaps\Controllers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;

class FilamentGoogleMapAssets
{
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
        }

        abort(404);
    }
}
