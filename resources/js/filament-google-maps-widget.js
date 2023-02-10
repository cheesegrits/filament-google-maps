import {MarkerClusterer} from "@googlemaps/markerclusterer";
import debounce from 'underscore/modules/debounce.js'

window.filamentGoogleMapsWidget = ($wire, config) => {
    return {
        wire: null,
        map: null,
        bounds: null,
        infoWindow: null,
        mapEl: null,
        data: null,
        markers: [],
        layers: [],
        modelIds: [],
        mapIsFilter: false,
        clusterer: null,
        center: null,
        isMapDragging: false,
        isIdleSkipped: false,
        config: {
            center: {
                lat: 0,
                lng: 0
            },
            clustering: false,
            controls: {
                mapTypeControl: true,
                scaleControl: true,
                streetViewControl: true,
                rotateControl: true,
                fullscreenControl: true,
                searchBoxControl: false,
                zoomControl: false,
            },
            fit: true,
            mapIsFilter: false,
            gmaps: '',
            layers: [],
            zoom: 12,
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

        init: function (data, mapEl) {
            this.mapEl = document.getElementById(mapEl) || mapEl;
            this.data = data;
            this.wire = $wire;
            this.config = {...this.config, ...config};
            this.loadGMaps();
        },

        callWire: function (thing) {
        },

        createMap: function () {
            window.filamentGoogleMapsAPILoaded = true;
            this.infoWindow = new google.maps.InfoWindow({
                content: "",
                disableAutoPan: true,
            });

            this.map = new google.maps.Map(this.mapEl, {
                center: this.config.center,
                zoom: this.config.zoom,
                ...this.config.controls
            });

            this.center = this.config.center,

                this.createMarkers();

            this.createClustering();

            this.createLayers();

            this.idle();

            this.show(true);
        },
        show: function (force = false) {
            if (this.config.fit) {
                this.fitToBounds(force);
            } else {
                if (this.markers.length > 0) {
                    this.map.setCenter(this.markers[0].getPosition());
                }
            }
        },
        createLayers: function () {
            this.layers = this.config.layers.map((layerUrl) => {
                return new google.maps.KmlLayer({
                    url: layerUrl,
                    map: this.map,
                });
            })
        },
        createMarker: function (location) {
            let markerIcon;

            if (location.icon && typeof location.icon === 'object') {
                if (location.icon.hasOwnProperty('url')) {
                    markerIcon = {
                        url: location.icon.url,
                    };

                    if (location.icon.hasOwnProperty('type') && location.icon.type === 'svg' && location.icon.hasOwnProperty('scale')) {
                        markerIcon.scaledSize = new google.maps.Size(location.icon.scale[0], location.icon.scale[1]);
                    }
                }
            }

            const point = location.location;
            const label = location.label;

            const marker = new google.maps.Marker({
                position: point,
                title: label,
                model_id: location.id,
                ...markerIcon && {icon: markerIcon},
            });

            if (this.modelIds.indexOf(location.id) === -1) {
                this.modelIds.push(location.id)
            }

            return marker;
        },
        createMarkers: function () {
            let self = this

            this.markers = this.data.map((location) => {
                const marker = this.createMarker(location);
                marker.setMap(this.map)
                let that = self;

                google.maps.event.addListener(marker, 'click', (event) => {
                    self.wire.mountTableAction('edit', marker.model_id)
                    // this.infoWindow.setContent(location.label);
                    // this.infoWindow.open(this.map, marker);
                })

                // marker.addListener("click", () => {
                //     // this.infoWindow.setContent(location.label);
                //     // this.infoWindow.open(this.map, marker);
                //     this.$wire("mountTableAction('edit', " + marker.model_id + ")")
                // });

                return marker;
            });
        },
        removeMarker: function (marker) {
            marker.setMap(null);
        },
        removeMarkers: function () {
            for (let i = 0; i < this.markers.length; i++) {
                this.markers[i].setMap(null);
            }

            this.markers = [];
        },
        mergeMarkers: function () {
            const operation = (list1, list2, isUnion = false) =>
                list1.filter(a => isUnion === list2.some(
                    b => a.getPosition().lat() === b.getPosition().lat() && a.getPosition().lng() === b.getPosition().lng()
                ));

            const inBoth = (list1, list2) => operation(list1, list2, true),
                inFirstOnly = operation,
                inSecondOnly = (list1, list2) => inFirstOnly(list2, list1);

            const newMarkers = this.data.map((location) => {
                let marker = this.createMarker(location);
                marker.addListener("click", () => {
                    this.infoWindow.setContent(location.label);
                    this.infoWindow.open(this.map, marker);
                });

                return marker;
            });

            if (!this.config.mapIsFilter) {
                const oldMarkersRemove = inSecondOnly(newMarkers, this.markers);

                for (let i = oldMarkersRemove.length - 1; i >= 0; i--) {
                    oldMarkersRemove[i].setMap(null);
                    const index = this.markers.findIndex(marker =>
                        marker.getPosition().lat() === oldMarkersRemove[i].getPosition().lat() &&
                        marker.getPosition().lng() === oldMarkersRemove[i].getPosition().lng()
                    );
                    this.markers.splice(index, 1)
                }
            }

            const newMarkersCreate = inSecondOnly(this.markers, newMarkers);

            for (let i = 0; i < newMarkersCreate.length; i++) {
                newMarkersCreate[i].setMap(this.map);
                this.markers.push(newMarkersCreate[i]);
            }

            this.fitToBounds();
        },
        fitToBounds: function (force = false) {
            if (this.config.fit && (force || !this.config.mapIsFilter)) {
                this.bounds = new google.maps.LatLngBounds();

                for (const marker of this.markers) {
                    this.bounds.extend(marker.getPosition());
                }

                this.map.fitBounds(this.bounds);
            }
        },
        createClustering: function () {
            if (this.config.clustering) {
                // use default algorithm and renderer
                this.clusterer = new MarkerClusterer({
                    map: this.map,
                    markers: this.markers
                });
            }
        },
        updateClustering: function () {
            if (this.config.clustering) {
                this.clusterer.clearMarkers();
                this.clusterer.addMarkers(this.markers);
            }
        },
        moved: function () {
            function areEqual(array1, array2) {
                if (array1.length === array2.length) {
                    return array1.every((element, index) => {
                        if (element === array2[index]) {
                            return true;
                        }

                        return false;
                    });
                }

                return false;
            }

            console.log('moved');

            const bounds = this.map.getBounds()
            const visible = this.markers.filter((marker) => {
                return bounds.contains(marker.getPosition())
            })
            const ids = visible.map(marker => marker.model_id)

            if (!areEqual(this.modelIds, ids)) {
                this.modelIds = ids;
                console.log(ids)
                $wire.set('mapFilterIds', ids)
            }
        },
        idle: function () {
            if (this.config.mapIsFilter) {
                let that = self;
                const debouncedMoved = debounce(this.moved, 1000).bind(this);

                google.maps.event.addListener(this.map, 'idle', (event) => {
                    if (self.isMapDragging) {
                        self.idleSkipped = true;
                        return;
                    }
                    self.idleSkipped = false;
                    debouncedMoved();
                });
                google.maps.event.addListener(this.map, 'dragstart', (event) => {
                    self.isMapDragging = true;
                });
                google.maps.event.addListener(this.map, 'dragend', (event) => {
                    self.isMapDragging = false;
                    if (self.idleSkipped === true) {
                        debouncedMoved();
                        self.idleSkipped = false;
                    }
                });
                google.maps.event.addListener(this.map, 'bounds_changed', (event) => {
                    self.idleSkipped = false;
                });
            }
        },
        update: function (data) {
            this.data = data;
            this.mergeMarkers();
            this.updateClustering();
            this.show();
        },
        recenter: function (data) {
            this.map.panTo({lat: data.lat, lng: data.lng});
            this.map.setZoom(data.zoom)
        },
    }
}