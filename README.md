<a name="readme-top"></a>

# Filament Google Maps

This package provides a comprehensive set of tools for using Google Maps within the
[Filament PHP](https://filamentphp.com) ecosystem (an application builder for [Laravel](https://laravel.com)),
either as part of an admin panel, or in standalone front end forms, tables and dashboards.

<!-- ABOUT THE PROJECT -->
## About The Project

### API Usage

**IMPORTANT NOTE** - some features of this package could potentially drive up your
API bill.  If you have large tables that you display static maps on, and you clear your
cache frequently.  Or if you allow public access to your forms, and get hit by bots.

We suggest you keep an eye on your API usage in the Google Console.  We are not liable if you get a surprise bill!

### Components

The FilamentGoogleMaps field displays (unsurprisingly) a Google map, with a comprehensive set of configuration
options.  It supports coordinate updating both ways between map and form, as well as forward and reverse
geocoding.

![Filament Google Maps Form](images/form-map.png)

The FilamentGoogleMapsWidget displays a filterable set of locations from a model, with 
optional clustering, templatable labels, customizable icons, etc.

![Filament Google Maps Widget](images/widget-maps.png)

The FilamentGoogleMapsTableWidget displays a map widget, along with a Filament Table,
and reacts to all filtering and searching on the table.

![Filament Google Maps Table Widget](images/widget-table-map.png)

The FilamentGoogleMapsColumn displays a customizable static map image, with the images cached locally
to reduce API overhead.

![Filament Google Maps Column](images/table-map.png)

<p align="right">(<a href="#readme-top">back to top</a>)</p>


<!-- GETTING STARTED -->
### Prerequisites

This package is built on Filament V2, and Laravel 9.  It may run on earlier versions of
Laravel, but has not been tested.

### Installation

You can install this project via composer:

```sh
composer install cheesegrits/filament-google-maps
```

### Assets

This package handles asynchronous loading of JS and CSS assets, in both the Filament Admin Panel
and standalone pages, with no need to publish anything or modify your project.

### Preparing Models

To simplify working with coordinate data, we require a computed property on any model
being used for map data, which converts between separate lat and lng fields on the table,
and a Google Point style array of 'lat' and 'lng' keys.

To prepare your model, use the Artisan command:

```sh
php artisan filament-google-maps:model-code
```

... which will prompt you for your model class, the computed property name (such as 'location',
which will be the name you use for map fields and columns), and the lat and lng field names (model attributes)
on your table.  It will then spit out the code for you to copy and paste to your model class.

### Publish the configuration

You may publish the package configuration, in order to set your key in the next step,
although this can be done with an environment variable, so is not required.

```sh
php artisan vendor:publish --tag="filament-google-maps-config"
```
... which can then be found in ./config/filament-google-maps.php

### Setting your Google Maps API Key

All use of the Google Maps API requires an API key.  If you don't have one, refer to
[Google's documentation](https://developers.google.com/maps/documentation/javascript/get-api-key).

Once you have a key, either add it to your .env file as:

```php
GOOGLE_MAPS_API_KEY=your_map_key_here
```
... or publish and edit the filament-google-maps.php config file.  We recommend using
an environment variable.

<p align="right">(<a href="#readme-top">back to top</a>)</p>

<!-- USAGE EXAMPLES -->
## Usage

### Form Field

The form field can be used with no options, by simply adding this to your Filament
Form schema:

```php
use Cheesegrits\FilamentGoogleMaps\Fields\FilamentGoogleMap
...
->schema[
    ...
    FilamentGoogleMap::make('location')
    ...
]
```
The name used for make() must be the one you set up as your model's computed location
property.  Note that you can have multiple maps on a form, by adding a second computed
property referencing a second pair of lat/lng fields.

The full set of options is as follows:

```php
FilamentGoogleMap::make('location')
    ->mapControls([
        'mapTypeControl'    => true,
        'scaleControl'      => true,
        'streetViewControl' => true,
        'rotateControl'     => true,
        'fullscreenControl' => true,
        'searchBoxControl'  => false, // creates geocomplete field inside map
        'zoomControl'       => false,
    ])
    ->height(fn () => '400px') // map height (width is controlled by Filament options)
    ->defaultZoom(5) // default zoom level when opening form
    ->autocomplete('full_address') // field on form to use as Places geocompletion field
    ->autocompleteReverse(true) // reverse geocode marker location to autocomplete field
    ->defaultLocation([39.526610, -107.727261]) // default for new forms
    ->draggable() // allow dragging to move marker
    ->clickable(false) // allow clicking to move marker
```

All option methods support closures, as well as direct values.

### Table Column

The table column displays a static Google map image.  The images are created on the
server side through calls to the Maps API, and cached locally on the server (using
Laravel's default cache driver) for a default of 30 days, to prevent excessive API usage.
See the warning at the top of this page about API usage.

```php
use Cheesegrits\FilamentGoogleMaps\Columns\FilamentGoogleMapColumn;

...

FilamentGoogleMapColumn::make('location')
    ->extraAttributes([
      'class' => 'my-funky-class'
    ]) // set any additional attributes, merged into the wrapper div around the image tag
    ->extraImgAttributes(
        fn ($record): array => ['title' => $record->latitude . ',' . $record->longitude]
    ) // set any additional attributes you want on the img tag
    ->height('150') // API setting for map height in PX
    ->width('250') // API setting got map width in PX
    ->type('hybrid') // API setting for map type (hybrid, satellite, roadmap, tarrain)
    ->zoom(15) // API setting for zoom (1 through 20)
    ->ttl(60 * 60 * 24 * 30), // number of seconds to cache image before refetching from API
```
NOTE that options marked as 'API Setting' are used as part of the cache key, so changing
any of these will force a cache refresh for all images in the table (as they are displayed).

### Map Widget

The map widget can be used either in the Filament Admin panel (see Filament docs), or
standalone as a normal Livewire component.

Here is An example of using the widget on a front end dashboard.  Create a component somewhere
in your Livewire folder, like ./Http/Livewire/Widgets/Dealerships.php, which extends the
FilamentGoogleMapsWidget class:

```php
<?php

namespace App\Http\Livewire\Widgets;

use App\Models\Dealerships;
use Cheesegrits\FilamentGoogleMaps\Widgets\FilamentGoogleMapsWidget;

class DealershipMap extends FilamentGoogleMapsWidget
{
    protected static ?string $heading = 'Dealership Locations';

    protected static ?bool $clustering = true;

    protected function getData(): array
    {
        $dealerships = Dealerships::all();

        $data = [];

        foreach ($dealerships as $dealership)
        {
            if ($dealership->latitude && $dealership->longitude)
            {
                $data[] = [
                    'location'  => [
                        'lat' => $dealership->latitude,
                        'lng' => $dealership->longitude,
                    ],
                    'label' => $dealership->name,
                ];               
            }


        }

        return $data;
    }
}
```
... then call it in your front end Blade template ...

```php
    <div class="bg-gray-200 bg-opacity-25 grid grid-cols-1 md:grid-cols-2">
        <div class="p-6 col-span-2">
            @livewire('widgets.dealerships-map')
        </div>
    </div>
```

You can render your labels as a Blade templates, and provide an optional icon ...

```php
                $data[] = [
                    'location'  => [
                        'lat' => $dealership->latitude,
                        'lng' => $dealership->longitude,
                    ],
                    'label'     => view(
                        'widgets.dealership-label',
                        [
                            'dealershipId'   => $dealership->id,
                            'dealershipName' => $dealership->name,
                            'dealershipIcon' => $dealership->icon,
                        ]
                    )->render(),
                    'icon' => [
                        'url' => url('images/dealership.svg'),
                        'type' => 'svg',
                        'scale' => [35,35],
                    ],
                ]; 
```

See the parent component code for further methods and variables you can override.

### Map Table Widget

The map table widget has all the features of the vanilla widget, but with the addition
of a Filament table underneath it.  The map responds to all filtering and searching
on the table, which is done with standard Filament Table methods and schemas.

To display a Dealership table map, you would use the same code from above, but extend
the FilamentGoogleMapsTableWidget, and add standard Filament table methods:

```php
   protected function getTableQuery(): Builder
    {
        return Dealer::all();
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('name'),
            Tables\Columns\TextColumn::make('state.name'),
            Tables\Columns\TextColumn::make('phone')
                ->searchable(),
            Tables\Columns\TextColumn::make('email')
                ->searchable(),
        ];
    }

    protected function getTableFilters(): array
    {
        return [
            Tables\Filters\SelectFilter::make('state')
                ->label('State')
                ->relationship('state','state_name'),
        ];
    }

```

<p align="right">(<a href="#readme-top">back to top</a>)</p>

<!-- ROADMAP -->
## Roadmap

- [ ] Add option for which cache store to use for static maps
- [ ] Add Artisan commands for geocoding / reverse geocoding tables, useful when source tables have addreeses but no coords, or vice versa
- [ ] Add optional request signing of API calls
- [ ] Add more geocoding options for form fields, for individual address components (street, city, zip, etc)
- [ ] Write test suite

<!-- ISSUES -->

## Issues

If (when) you find bugs, please report them on the [issues page](https://github.com/cheesegrits/filament-google-maps/issues)
and we'll fix them ASAP.

<p align="right">(<a href="#readme-top">back to top</a>)</p>

<!-- CONTRIBUTING -->
## Contributing

If you have a suggestion that would make this better, please fork the repo and create a pull request. You can also simply open an issue with the tag "enhancement".

1. Fork the Project
2. Create your Feature Branch (`git checkout -b feature/AmazingFeature`)
3. Commit your Changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the Branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

<p align="right">(<a href="#readme-top">back to top</a>)</p>



<!-- LICENSE -->
## License

Distributed under the MIT License. See `LICENSE.txt` for more information.

<p align="right">(<a href="#readme-top">back to top</a>)</p>



<!-- CONTACT -->
## Contact

Hugh Messenger - [@cheesegrits](https://twitter.com/@cheesegrits) - hugh.messenger@gmail.com

Project Link: [https://github.com/cheesegrits/filament-google-maps](https://github.com/cheesegrits/filament-google-maps)

<p align="right">(<a href="#readme-top">back to top</a>)</p>



<!-- ACKNOWLEDGMENTS -->
## Acknowledgments

* [Filament PHP](https://filamentphp.com)
* [Laravel](https://laravel.com)


<p align="right">(<a href="#readme-top">back to top</a>)</p>



<!-- MARKDOWN LINKS & IMAGES -->
<!-- https://www.markdownguide.org/basic-syntax/#reference-style-links -->
[contributors-shield]: https://img.shields.io/github/contributors/cheesegrits/filament-google-maps.svg?style=for-the-badge
[contributors-url]: https://github.com/cheesegrits/filament-google-maps/graphs/contributors
[forks-shield]: https://img.shields.io/github/forks/cheesegrits/filament-google-maps.svg?style=for-the-badge
[forks-url]: https://github.com/cheesegrits/filament-google-maps/network/members
[stars-shield]: https://img.shields.io/github/stars/cheesegrits/filament-google-maps.svg?style=for-the-badge
[stars-url]: https://github.com/cheesegrits/filament-google-maps/stargazers
[issues-shield]: https://img.shields.io/github/issues/cheesegrits/filament-google-maps.svg?style=for-the-badge
[issues-url]: https://github.com/cheesegrits/filament-google-maps/issues
[license-shield]: https://img.shields.io/github/license/cheesegrits/filament-google-maps.svg?style=for-the-badge
[license-url]: https://github.com/cheesegrits/filament-google-maps/blob/master/LICENSE.txt
[linkedin-shield]: https://img.shields.io/badge/-LinkedIn-black.svg?style=for-the-badge&logo=linkedin&colorB=555
[linkedin-url]: https://linkedin.com/in/linkedin_username
[product-screenshot]: images/screenshot.png
[Next.js]: https://img.shields.io/badge/next.js-000000?style=for-the-badge&logo=nextdotjs&logoColor=white
[Next-url]: https://nextjs.org/
[React.js]: https://img.shields.io/badge/React-20232A?style=for-the-badge&logo=react&logoColor=61DAFB
[React-url]: https://reactjs.org/
[Vue.js]: https://img.shields.io/badge/Vue.js-35495E?style=for-the-badge&logo=vuedotjs&logoColor=4FC08D
[Vue-url]: https://vuejs.org/
[Angular.io]: https://img.shields.io/badge/Angular-DD0031?style=for-the-badge&logo=angular&logoColor=white
[Angular-url]: https://angular.io/
[Svelte.dev]: https://img.shields.io/badge/Svelte-4A4A55?style=for-the-badge&logo=svelte&logoColor=FF3E00
[Svelte-url]: https://svelte.dev/
[Laravel.com]: https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white
[Laravel-url]: https://laravel.com
[Bootstrap.com]: https://img.shields.io/badge/Bootstrap-563D7C?style=for-the-badge&logo=bootstrap&logoColor=white
[Bootstrap-url]: https://getbootstrap.com
[JQuery.com]: https://img.shields.io/badge/jQuery-0769AD?style=for-the-badge&logo=jquery&logoColor=white
[JQuery-url]: https://jquery.com 