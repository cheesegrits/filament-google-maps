import {MarkerClusterer} from "@googlemaps/markerclusterer";

window.filamentGoogleMapsWidget = ($wire, config) => {
    return {
        map: null,
        bounds: null,
        infoWindow: null,
        mapEl: null,
        data: null,
        markers: [],
        layers: [],
        clusterer: null,
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
            this.config = {...this.config, ...config};
            this.loadGMaps();
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

            this.bounds = new google.maps.LatLngBounds();

            this.createMarkers();

            this.createClustering();

            this.createLayers();

            this.show();
        },
        show: function () {
            if (this.config.fit) {
                this.map.fitBounds(this.bounds);
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
        createMarkers: function () {
            this.markers = this.data.map((location) => {
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
                    map: this.map,
                    title: label,
                    ...markerIcon && {icon: markerIcon},
                });

                this.bounds.extend(point);

                // markers can only be keyboard focusable when they have click listeners
                // open info window when marker is clicked
                marker.addListener("click", () => {
                    this.infoWindow.setContent(label);
                    this.infoWindow.open(this.map, marker);
                });
                return marker;
            });
        },
        removeMarkers: function () {
            for (let i = 0; i < this.markers.length; i++) {
                this.markers[i].setMap(null);
            }

            this.markers = [];
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
        update: function (data) {
            this.data = data;
            this.removeMarkers();
            this.createMarkers();
            this.updateClustering();
            this.show();
        }
    }
}