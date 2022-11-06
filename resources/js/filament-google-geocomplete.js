window.filamentGoogleGeocomplete = ($wire, config) => {
    return {
        geocoder: null,
        mapEl: null,
        config: {
            statePath: '',
            gmaps: '',
            filterName: null,
        },

        loadGMaps: function () {
            if (!document.getElementById('filament-google-maps-google-maps-js')) {
                const script = document.createElement('script');
                script.id = 'filament-google-maps-google-maps-js';
                window.filamentGoogleMapsAsyncLoad = this.createAutocomplete.bind(this);
                script.src = this.config.gmaps + '&callback=filamentGoogleMapsAsyncLoad';
                document.head.appendChild(script);
            } else {
                const waitForGlobal = function (key, callback) {
                    if (window[key]) {
                        callback();
                    } else {
                        setTimeout(function () {
                            waitForGlobal(key, callback);
                        }, 100);
                    }
                };

                waitForGlobal("filamentGoogleMapsAPILoaded", function () {
                    this.createAutocomplete();
                }.bind(this));
            }
        },

        init: function (mapEl) {
            this.mapEl = mapEl;
            this.config = {...this.config, ...config};
            this.loadGMaps();
        },

        createAutocomplete: function () {
            window.filamentGoogleMapsAPILoaded = true;

            const geocompleteOptions = {
                fields: ["formatted_address", "geometry", "name"],
                strictBounds: false,
                types: ["geocode"],
            };

            const geoComplete = document.getElementById(this.config.statePath);

            if (geoComplete) {
                window.addEventListener('keydown', function (e) {
                    if (e.key === 'U+000A' || e.key === 'Enter' || e.code === 'Enter') {
                        if (e.target.nodeName === 'INPUT' && e.target.type === 'text') {
                            e.preventDefault();
                            return false;
                        }
                    }
                }, true);

                const autocomplete = new google.maps.places.Autocomplete(geoComplete, geocompleteOptions);

                autocomplete.addListener("place_changed", () => {
                    const place = autocomplete.getPlace();

                    if (!place.geometry || !place.geometry.location) {
                        window.alert("No details available for input: '" + place.name + "'");
                        return;
                    }

                    this.setCoordinates(place);
                });
            }
        },
        setCoordinates: function (place) {
            //$wire.set(this.config.statePath, position, false);
            $wire.set(this.config.statePath, place.formatted_address);

            if (this.config.filterName) {
                const latPath = this.config.filterName + '.latitude';
                const lngPath = this.config.filterName + '.longitude';
                const lat = document.getElementById(latPath);
                const lng = document.getElementById(lngPath);

                if (lat && lng)
                {
                    lat.setAttribute('value', place.geometry.location.lat().toString());
                    lng.setAttribute('value', place.geometry.location.lng().toString());
                    $wire.set(latPath, place.geometry.location.lat().toString());
                    $wire.set(lngPath, place.geometry.location.lng().toString());

                }
            }
        },
    }
}