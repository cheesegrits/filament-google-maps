window.filamentGoogleMaps = ($wire, config) => {
    return {
        map: null,
        geocoder: null,
        marker: null,
        markerLocation: null,
        layers: null,
        mapEl: null,
        pacEl: null,
        config: {
            debug: false,
            autocomplete: '',
            autocompleteReverse: false,
            geolocate: true,
            geolocateLabel: 'Set Current Location',
            draggable: true,
            clickable: false,
            defaultLocation: {
                lat: 0,
                lng: 0
            },
            statePath: '',
            controls: {
                mapTypeControl: true,
                scaleControl: true,
                streetViewControl: true,
                rotateControl: true,
                fullscreenControl: true,
                searchBoxControl: false,
                zoomControl: false,
            },
            drawingModeL: null,
            drawingControl: false,
            drawingModes: {
                marker: true,
                circle: true,
                rectangle: true,
                polygon: true,
                polyline: true,
            },
            drawingField: null,
            layers: [],
            reverseGeocodeFields: {},
            defaultZoom: 8,
            gmaps: '',
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
            '%L': ["locality"],
            '%D': ["sublocality"],
            '%C': ["country"],
            '%c': ["country"],
        },
        overlays: [],
        dataLayer: null,
        polyOptions: {
            strokeWeight: 0,
            fillOpacity: 0.45,
            draggable: false,
            editable: false,
            zIndex: 1,
        },

        loadGMaps: function () {
            if (!document.getElementById('filament-google-maps-google-maps-js')) {
                const script = document.createElement('script');
                script.id = 'filament-google-maps-google-maps-js';
                window.filamentGoogleMapsAsyncLoad = this.createMap.bind(this);
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
                    this.createMap();
                }.bind(this));
            }
        },

        init: function (mapEl, pacEl) {
            this.mapEl = mapEl;
            this.pacEl = pacEl;
            this.config = {...this.config, ...config};
            this.loadGMaps();
        },

        createMap: function () {
            window.filamentGoogleMapsAPILoaded = true;

            if (this.config.autocompleteReverse || Object.keys(this.config.reverseGeocodeFields).length > 0) {
                this.geocoder = new google.maps.Geocoder();
            }

            this.map = new google.maps.Map(this.mapEl, {
                center: this.getCoordinates(),
                zoom: this.config.defaultZoom,
                ...this.config.controls
            });


            this.marker = new google.maps.Marker({
                draggable: this.config.draggable,
                map: this.map
            });

            this.marker.setPosition(this.getCoordinates());

            if (this.config.clickable) {
                this.map.addListener('click', (event) => {
                    this.markerLocation = event.latLng.toJSON();
                    this.setCoordinates(this.markerLocation);
                    this.updateAutocomplete(this.markerLocation);
                    this.updateGeocode(this.markerLocation);
                    //this.updateMap(this.markerLocation);
                    this.map.panTo(this.markerLocation);
                });
            }

            if (this.config.draggable) {
                google.maps.event.addListener(this.marker, 'dragend', (event) => {
                    this.markerLocation = event.latLng.toJSON();
                    this.setCoordinates(this.markerLocation);
                    this.updateAutocomplete(this.markerLocation);
                    this.updateGeocode(this.markerLocation);
                    // this.updateMap(this.markerLocation);
                    this.map.panTo(this.markerLocation);
                });
            }

            if (this.config.controls.searchBoxControl) {
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


            if (this.config.autocomplete) {
                const geoComplete = document.getElementById(this.config.autocomplete);

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

                        if (place.geometry.viewport) {
                            this.map.fitBounds(place.geometry.viewport);
                        } else {
                            this.map.setCenter(place.geometry.location);
                        }

                        this.marker.setPosition(place.geometry.location);
                        this.markerLocation = place.geometry.location;
                        this.setCoordinates(place.geometry.location);
                        this.updateGeocode(this.markerLocation);
                    });
                }
            }

            if (this.config.layers) {
                this.layers = this.config.layers.map((layerUrl) => {
                    return new google.maps.KmlLayer({
                        url: layerUrl,
                        map: this.map,
                    });
                })
            }

            if (this.config.geolocate && "geolocation" in navigator) {
                const locationButton = document.createElement("button");

                locationButton.textContent = this.config.geolocateLabel;
                locationButton.classList.add("custom-map-control-button");
                this.map.controls[google.maps.ControlPosition.TOP_CENTER].push(locationButton);

                locationButton.addEventListener("click", (e) => {
                    e.preventDefault()
                    navigator.geolocation.getCurrentPosition((position) => {
                        this.markerLocation = {
                            lat: position.coords.latitude,
                            lng: position.coords.longitude
                        };
                        this.setCoordinates(this.markerLocation);
                        this.updateAutocomplete(this.markerLocation);
                        this.updateGeocode(this.markerLocation);
                        this.map.panTo(this.markerLocation);
                    });
                });
            }

            if (this.config.drawingControl) {
                const drawingManager = new google.maps.drawing.DrawingManager({
                    drawingMode: google.maps.drawing.OverlayType.MARKER,
                    drawingControl: true,
                    drawingControlOptions: {
                        position: google.maps.ControlPosition.TOP_CENTER,
                        drawingModes: [
                            ...(this.config.drawingModes.marker ? [google.maps.drawing.OverlayType.MARKER] : []),
                            ...(this.config.drawingModes.circle ? [google.maps.drawing.OverlayType.CIRCLE] : []),
                            ...(this.config.drawingModes.polygon ? [google.maps.drawing.OverlayType.POLYGON] : []),
                            ...(this.config.drawingModes.polyline ? [google.maps.drawing.OverlayType.POLYLINE] : []),
                            ...(this.config.drawingModes.rectangle ? [google.maps.drawing.OverlayType.RECTANGLE] : []),
                        ],
                    },
                });

                drawingManager.setMap(this.map);

                if (this.config.drawingField) {
                    this.dataLayer = new google.maps.Data();

                    let geoJSON = $wire.get(this.config.drawingField);
                    geoJSON && this.loadFeaturesCollection(JSON.parse(geoJSON));

                    google.maps.event.addListener(drawingManager, 'overlaycomplete', (event) => {
                        event.overlay.type = event.type;
                        event.overlay.feature = this.instanceFeature(event.overlay);
                        this.overlays.push(event.overlay);

                        this.dataLayer.toGeoJson((obj) => {
                            $wire.set(this.config.drawingField, JSON.stringify(obj));
                        });
                    });
                }
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
        updateGeocode: function (position) {
            if (Object.keys(this.config.reverseGeocodeFields).length > 0) {
                this.geocoder
                    .geocode({location: position})
                    .then((response) => {
                        if (response.results[0]) {
                            //$wire.set(config.autocomplete, response.results[0].formatted_address);
                            const replacements = this.getReplacements(response.results[0].address_components);

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
                    })
                    .catch((error) => {
                        console.log(error.message);
                    })
            }
        },
        updateAutocomplete: function (position) {
            if (this.config.autocomplete && this.config.autocompleteReverse) {
                this.geocoder
                    .geocode({location: position})
                    .then((response) => {
                        if (response.results[0]) {
                            $wire.set(this.config.autocomplete, response.results[0].formatted_address);
                        }
                    })
                    .catch((error) => {
                        console.log(error.message);
                    })
            }
        },
        setCoordinates: function (position) {
            $wire.set(this.config.statePath, position);
        },
        getCoordinates: function () {
            let location = $wire.get(this.config.statePath)
            if (location === null || !location.hasOwnProperty('lat')) {
                location = {lat: this.config.defaultLocation.lat, lng: this.config.defaultLocation.lng}
            }
            return location;
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
        },

        instanceOverlay: function (feature) {
            var instance = null;
            switch (feature.properties.type) {
                case google.maps.drawing.OverlayType.MARKER:
                    instance = new google.maps.Marker({
                        type: feature.properties.type,
                        position: new google.maps.LatLng(feature.geometry.coordinates[1], feature.geometry.coordinates[0]),
                        draggable: true
                    });
                    break;
                case google.maps.drawing.OverlayType.RECTANGLE:
                    var NE = new google.maps.LatLng(feature.geometry.coordinates[0][2][1], feature.geometry.coordinates[0][2][0]);
                    var SW = new google.maps.LatLng(feature.geometry.coordinates[0][0][1], feature.geometry.coordinates[0][0][0]);
                    instance = new google.maps.Rectangle(Object.assign({}, this.polyOptions, {
                        type: feature.properties.type,
                        fillColor: feature.properties.color,
                        bounds: new google.maps.LatLngBounds(SW, NE),
                        editable: false
                    }));
                    break;
                case google.maps.drawing.OverlayType.POLYGON:
                    instance = new google.maps.Polygon(Object.assign({}, this.polyOptions, {
                        type: feature.properties.type,
                        fillColor: feature.properties.color,
                        paths: this.transformToMVCArray(feature.geometry.coordinates),
                        editable: false
                    }));
                    break;
                case google.maps.drawing.OverlayType.POLYLINE:
                    instance = new google.maps.Polyline({
                        type: feature.properties.type,
                        strokeColor: feature.properties.color,
                        path: this.transformToMVCArray([feature.geometry.coordinates]).getAt(0),
                        draggable: true,
                        editable: false
                    });
                    break;
                case google.maps.drawing.OverlayType.CIRCLE:
                    instance = new google.maps.Circle(Object.assign({}, this.polyOptions, {
                        type: feature.properties.type,
                        fillColor: feature.properties.color,
                        center: new google.maps.LatLng(feature.geometry.coordinates[1], feature.geometry.coordinates[0]),
                        radius: feature.properties.radius,
                        editable: false
                    }));
                    break;
            }
            return instance;
        },

        instanceFeature: function (overlay) {
            var calculatedOverlay = this.calculateGeometry(overlay);
            return this.dataLayer.add(new google.maps.Data.Feature({
                id: this.guid(),
                geometry: calculatedOverlay.geometry,
                properties: Object.assign({
                    type: overlay.type
                }, calculatedOverlay.hasOwnProperty('properties') ? calculatedOverlay.properties : {})
            }));
        },

        calculateGeometry: function (overlay, geometryOnly) {
            switch (overlay.type) {
                case google.maps.drawing.OverlayType.MARKER:
                    return geometryOnly ? new google.maps.Data.Point(overlay.getPosition()) : {
                        geometry: new google.maps.Data.Point(overlay.getPosition())
                    };
                case google.maps.drawing.OverlayType.RECTANGLE:
                    var b = overlay.getBounds(),
                        p = [b.getSouthWest(), {
                            lat: b.getSouthWest().lat(),
                            lng: b.getNorthEast().lng()
                        }, b.getNorthEast(), {
                            lng: b.getSouthWest().lng(),
                            lat: b.getNorthEast().lat()
                        }];
                    return geometryOnly ? new google.maps.Data.Polygon([p]) : {
                        geometry: new google.maps.Data.Polygon([p])
                    };
                case google.maps.drawing.OverlayType.POLYGON:
                    return geometryOnly ? new google.maps.Data.Polygon([overlay.getPath().getArray()]) : {
                        geometry: new google.maps.Data.Polygon([overlay.getPath().getArray()])
                    };
                case google.maps.drawing.OverlayType.POLYLINE:
                    return geometryOnly ? new google.maps.Data.LineString(overlay.getPath().getArray()) : {
                        geometry: new google.maps.Data.LineString(overlay.getPath().getArray())
                    };
                case google.maps.drawing.OverlayType.CIRCLE:
                    return geometryOnly ? new google.maps.Data.Point(overlay.getCenter()) : {
                        properties: {
                            radius: overlay.getRadius()
                        },
                        geometry: new google.maps.Data.Point(overlay.getCenter())
                    };
            }
        },

        transformToMVCArray: function (a) {
            var clone = new google.maps.MVCArray();

            function transform($a, parent) {
                if ($a.length == 2 && (!Array.isArray($a[0]) && !Array.isArray($a[1])))
                    parent.push(new google.maps.LatLng($a[1], $a[0]));
                for (var a = 0; a < $a.length; a++) {
                    if (!Array.isArray($a[a])) continue;
                    transform($a[a], (parent) ? ($a[a].length == 2 && (!Array.isArray($a[a][0]) && !Array.isArray($a[a][1]))) ? parent : parent.getAt(parent.push(new google.maps.MVCArray()) - 1) : clone.getAt(clone.push(new google.maps.MVCArray()) - 1));
                }
            }

            function isMVCArray(array) {
                return array instanceof google.maps.MVCArray;
            }

            transform(a);

            return clone;
        },

        loadFeaturesCollection: function (geoJSON) {
            if (Array.isArray(geoJSON.features) && geoJSON.features.length > 0) {
                var bounds = new google.maps.LatLngBounds();
                var overlay = null;
                for (var f = 0; f < geoJSON.features.length; f++) {
                    overlay = this.instanceOverlay(geoJSON.features[f]);
                    overlay.feature = this.instanceFeature(overlay);
                    // overlay.feature.getGeometry().forEachLatLng(function (latlng) {
                    //     bounds.extend(latlng);
                    // });
                    // overlay.feature.setProperty("color", features[f].properties.color);
                    overlay.setMap(this.map);
                    this.overlays.push(overlay);
                }
            }
        },

        guid: function () {
            function s4() {
                return Math.floor((1 + Math.random()) * 0x10000)
                    .toString(16)
                    .substring(1);
            }

            return s4() + s4() + '-' + s4() + '-' + s4() + '-' +
                s4() + '-' + s4() + s4() + s4();
        }
    }
}
