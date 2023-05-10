window.filamentGoogleGeocomplete = ($wire, config) => {
    return {
        geocoder: null,
        mapEl: null,
        config: {
            debug: false,
            statePath: '',
            gmaps: '',
            filterName: null,
            reverseGeocodeFields: {},
            latLngFields: {},
            types: [],
            countries: [],
            isLocation: false,
            placeField: 'formatted_address',
        },
        symbols: {
            '%n': ["street_number"],
            '%z': ["postal_code"],
            '%S': ["street_address", "route"],
            '%A1': ["administrative_area_level_1"],
            '%A2': ["administrative_area_level_2"],
            '%A3': ["administrative_area_level_3"],
            '%A4': ["administrative_area_level_4"],
            '%A5': ["administrative_area_level_5"],
            '%a1': ["administrative_area_level_1"],
            '%a2': ["administrative_area_level_2"],
            '%a3': ["administrative_area_level_3"],
            '%a4': ["administrative_area_level_4"],
            '%a5': ["administrative_area_level_5"],
            '%L': ["locality", "postal_town"],
            '%D': ["sublocality"],
            '%C': ["country"],
            '%c': ["country"],
            '%p': ['premise'],
            '%P': ['premise'],
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

            let fields = ["address_components", "formatted_address", "geometry", "name"];

            if (!fields.includes(this.config.placeField)) {
                fields.push(this.config.placeField);
            }

            const geocompleteOptions = {
                fields: fields,
                strictBounds: false,
                types: this.config.types,
            };

            const geocompleteEl = this.config.isLocation ? this.config.statePath + '-fgm-address' : this.config.statePath;
            const geoComplete = document.getElementById(geocompleteEl);

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

                autocomplete.setComponentRestrictions({
                    country: this.config.countries,
                })

                autocomplete.addListener("place_changed", () => {
                    const place = autocomplete.getPlace();

                    if (!place.geometry || !place.geometry.location) {
                        window.alert("No details available for input: '" + place.name + "'");
                        return;
                    }

                    this.setLocation(place);
                    this.updateReverseGeocode(place);
                    this.updateLatLng(place);
                });

                const geoLocate = document.getElementById(this.config.statePath + '-geolocate');

                if (geoLocate)  {
                    this.geocoder = new google.maps.Geocoder();

                    geoLocate.addEventListener('click',  (event) => {
                        if ("geolocation" in navigator){
                            navigator.geolocation.getCurrentPosition((position) => {
                                var currentLatitude = position.coords.latitude;
                                var currentLongitude = position.coords.longitude;
                                var currentLocation = { lat: currentLatitude, lng: currentLongitude };

                                this.geocoder
                                    .geocode({location: currentLocation})
                                    .then((response) => {
                                        if (response.results[0]) {
                                            geoComplete.setAttribute('value', response.results[0].formatted_address)
                                            this.setLocation(response.results[0]);
                                            this.updateReverseGeocode(response.results[0]);
                                            this.updateLatLng(response.results[0]);
                                        }
                                    });
                            });
                        }
                    })
                }
            }
        },
        setLocation: function (place) {
            if (this.config.isLocation) {
                $wire.set(this.config.statePath, place.geometry.location);
            } else {
                $wire.set(this.config.statePath, place[this.config.placeField]);
            }

            if (this.config.filterName) {
                const latPath = this.config.filterName + '.latitude';
                const lngPath = this.config.filterName + '.longitude';
                const lat = document.getElementById(latPath);
                const lng = document.getElementById(lngPath);

                if (lat && lng) {
                    lat.setAttribute('value', place.geometry.location.lat().toString());
                    lng.setAttribute('value', place.geometry.location.lng().toString());
                    $wire.set(latPath, place.geometry.location.lat().toString());
                    $wire.set(lngPath, place.geometry.location.lng().toString());

                }
            }
        },
        updateReverseGeocode: function (place) {
            if (Object.keys(this.config.reverseGeocodeFields).length > 0) {
                if (place.address_components) {
                    //$wire.set(config.autocomplete, response.results[0].formatted_address);
                    const replacements = this.getReplacements(place.address_components);

                    for (const field in this.config.reverseGeocodeFields) {
                        let replaced = this.config.reverseGeocodeFields[field];

                        for (const replacement in replacements) {
                            replaced = replaced.split(replacement).join(replacements[replacement]);
                        }

                        for (const symbol in this.symbols) {
                            replaced = replaced.split(symbol).join('');
                        }

                        replaced = replaced.trim();
                        $wire.set(field, replaced)
                    }

                }
            }
        },
        updateLatLng: function(place) {
            if (Object.keys(this.config.latLngFields).length > 0) {
                if (place.geometry) {
                    $wire.set(this.config.latLngFields.lat, place.geometry.location.lat().toString())
                    $wire.set(this.config.latLngFields.lng, place.geometry.location.lng().toString())
                }
            }
        },
        getReplacements: function (address_components) {
            let replacements = {};

            address_components.forEach(component => {
                for (const symbol in this.symbols) {
                    if (this.symbols[symbol].indexOf(component.types[0]) !== -1) {
                        if (symbol === symbol.toLowerCase()) {
                            replacements[symbol] = component.short_name;
                        } else {
                            replacements[symbol] = component.long_name;
                        }
                    }
                }
            });

            if (this.config.debug) {
                console.log(replacements);
            }

            return replacements;
        }

    }
}
