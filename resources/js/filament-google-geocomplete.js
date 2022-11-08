window.filamentGoogleGeocomplete = ($wire, config) => {
    return {
        geocoder: null,
        mapEl: null,
        config: {
            statePath: '',
            gmaps: '',
            filterName: null,
            reverseGeocodeFields: {},
            location: null,
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
                fields: ["address_components", "formatted_address", "geometry", "name"],
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

                    this.setLocation(place);
                    this.updateReverseGeocode(place);
                });
            }
        },
        setLocation: function (place) {
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

                        $wire.set(field, replaced)
                    }

                }
            }
        },
        getReplacements: function (address_components) {
            const symbols = {
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
                '%L': ["locality"],
                '%D': ["sublocality"],
                '%C': ["country"],
                '%c': ["country"],
            };

            let replacements = {};

            address_components.forEach(component => {
                for (const symbol in symbols) {
                    if (symbols[symbol].indexOf(component.types[0]) !== -1) {
                        if (symbol  === symbol.toLowerCase()) {
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