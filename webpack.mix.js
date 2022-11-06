const mix = require('laravel-mix');

mix.disableSuccessNotifications()
mix.options({
    terser: {
        extractComments: false,
    },
})
mix.setPublicPath('dist')
mix.setResourceRoot('/cheesegrits/filament-google-maps')
mix.sourceMaps()
mix.version()

mix.js('resources/js/filament-google-maps.js', 'dist/cheesegrits/filament-google-maps')
mix.js('resources/js/filament-google-geocomplete.js', 'dist/cheesegrits/filament-google-maps')
mix.js( 'resources/js/filament-google-maps-widget.js', 'dist/cheesegrits/filament-google-maps')

mix.postCss('resources/css/filament-google-maps.css', 'dist/cheesegrits/filament-google-maps').options({
    processCssUrls: false
})
