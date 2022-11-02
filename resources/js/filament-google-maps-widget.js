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

        loadGMaps: function () {
            if (!document.getElementById('filament-google-maps-widget-google-maps-js')) {
                const script = document.createElement('script');
                script.id = 'filament-google-maps-widget-google-maps-js';
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

        init: function (data, mapEl) {
            this.mapEl = document.getElementById(mapEl) || mapEl;
            this.data = data;
            this.loadGMaps();
        },

        createMap: function () {
            window.filamentGoogleMapsAPILoaded = true;
            this.infoWindow = new google.maps.InfoWindow({
                content: "",
                disableAutoPan: true,
            });

            this.map = new google.maps.Map(this.mapEl, {
                center: config.center,
                zoom: config.zoom,
                ...config.controls
            });

            this.bounds = new google.maps.LatLngBounds();

            this.createMarkers();

            this.createClustering();

            this.createLayers();

            this.map.fitBounds(this.bounds);
        },
        createLayers: function () {
            this.layers = config.layers.map((layerUrl) => {
                return new google.maps.KmlLayer({
                    url: layerUrl,
                    map: this.map,
                });
            })
        },
        createMarkers: function () {
            this.markers = this.data.map((location, i) => {
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
            if (config.clustering) {
                // use default algorithm and renderer
                this.clusterer = new MarkerClusterer({
                    map: this.map,
                    markers: this.markers
                });
            }
        },
        updateClustering: function () {
            if (config.clustering) {
                this.clusterer.clearMarkers();
                this.clusterer.addMarkers(this.markers);
            }
        },
        update: function (data) {
            this.data = data;
            this.removeMarkers();
            this.createMarkers();
            this.updateClustering();
            this.map.fitBounds(this.bounds);
        },

        fetchData: async function () {
            const response = await fetch('/cheesegrits/filament-google-maps/')
            return response.json();
        }
    }
}