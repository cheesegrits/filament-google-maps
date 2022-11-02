window.filamentGoogleMaps = ($wire, config) => {
    return {
        map: null,
        geocoder: null,
        marker: null,
        markerLocation: null,
        layers: null,
        mapEl: null,
        pacEl: null,

        loadGMaps: function () {
            if (!document.getElementById('filament-google-maps-google-maps-js')) {
                const script = document.createElement('script');
                script.id = 'filament-google-maps-google-maps-js';
                window.filamentGoogleMapsAsyncLoad = this.createMap.bind(this);
                script.src = config.gmaps + '&callback=filamentGoogleMapsAsyncLoad';
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
                    this.createMap();
                }.bind(this));
            }
        },

        init: function (mapEl, pacEl) {
            this.mapEl = mapEl;
            this.pacEl = pacEl;
            this.loadGMaps();
        },

        createMap: function () {
            window.filamentGoogleMapsAPILoaded = true;

            let position = this.getCoordinates();

            this.map = new google.maps.Map(this.mapEl, {
                center: this.getCoordinates(),
                zoom: config.defaultZoom,
                ...config.controls
            });


            this.marker = new google.maps.Marker({
                draggable: config.draggable,
                map: this.map
            });

            this.marker.setPosition(this.getCoordinates());

            if (config.clickable) {
                this.map.addListener('click', (event) => {
                    this.markerLocation = event.latLng.toJSON();
                    this.setCoordinates(this.markerLocation);
                    this.updateAutocomplete(this.markerLocation);
                    //this.updateMap(this.markerLocation);
                    this.map.panTo(this.markerLocation);
                });
            }

            if (config.draggable) {
                google.maps.event.addListener(this.marker, 'dragend', (event) => {
                    this.markerLocation = event.latLng.toJSON();
                    this.setCoordinates(this.markerLocation);
                    this.updateAutocomplete(this.markerLocation);
                    // this.updateMap(this.markerLocation);
                    this.map.panTo(this.markerLocation);
                });
            }

            if (config.controls.searchBoxControl) {
                const input = this.pacEl;
                const searchBox = new google.maps.places.SearchBox(input);
                this.map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);
                searchBox.addListener("places_changed", () => {
                    input.value = ''
                    this.markerLocation = searchBox.getPlaces()[0].geometry.location
                })
            }

            const geocompleteOptions = {
                fields: ["formatted_address", "geometry", "name"],
                strictBounds: false,
                types: ["geocode"],
            };


            if (config.autocomplete) {
                if (config.autocompleteReverse) {
                    this.geocoder = new google.maps.Geocoder();
                }

                const geoComplete = document.getElementById(config.autocomplete);

                if (geoComplete) {
                    window.addEventListener('keydown', function (e) {
                        if (e.keyIdentifier === 'U+000A' || e.keyIdentifier === 'Enter' || e.keyCode === 13) {
                            if (e.target.nodeName == 'INPUT' && e.target.type == 'text') {
                                e.preventDefault();
                                return false;
                            }
                        }
                    }, true);

                    const autocomplete = new google.maps.places.Autocomplete(geoComplete, geocompleteOptions);

                    autocomplete.addListener("place_changed", (ev) => {
                        const place = autocomplete.getPlace();

                        if (!place.geometry || !place.geometry.location) {
                            // User entered the name of a Place that was not suggested and
                            // pressed the Enter key, or the Place Details request failed.
                            window.alert("No details available for input: '" + place.name + "'");
                            return;
                        }

                        // If the place has a geometry, then present it on a map.
                        if (place.geometry.viewport) {
                            this.map.fitBounds(place.geometry.viewport);
                        } else {
                            this.map.setCenter(place.geometry.location);
                        }

                        this.marker.setPosition(place.geometry.location);
                        this.markerLocation = place.geometry.location;
                        this.setCoordinates(place.geometry.location);
                    });
                }
            }

            if (config.kmlLayers) {
                this.layers = config.kmlLayers.map((layerUrl) => {
                    return new google.maps.KmlLayer({
                        url: layerUrl,
                        map: this.map,
                    });
                })
            }

        },
        updateMapFromAlpine: function () {
            const location = this.getCoordinates();
            const markerLocation = this.marker.getPosition();

            if (!(location.lat === markerLocation.lat() && location.lng === markerLocation.lng())) {
                this.updateAutocomplete(location)
                this.updateMap(location);
            }
        },
        updateMap: function (position) {
            this.marker.setPosition(position);
            this.map.panTo(position);
        },
        updateAutocomplete: function (position) {
            if (config.autocomplete && config.autocompleteReverse) {
                this.geocoder
                    .geocode({location: position})
                    .then((response) => {
                        if (response.results[0]) {
                            $wire.set(config.autocomplete, response.results[0].formatted_address);
                        }
                    })
            }
        },
        setCoordinates: function (position) {
            $wire.set(config.statePath, position, false);
        },
        getCoordinates: function () {
            let location = $wire.get(config.statePath)
            if (location === null || !location.hasOwnProperty('lat')) {
                location = {lat: config.defaultLocation.lat, lng: config.defaultLocation.lng}
            }
            return location;
        },
    }
}