import {MarkerClusterer} from "@googlemaps/markerclusterer";
import debounce from "underscore/modules/debounce.js";
import {importLibrary, setOptions} from "@googlemaps/js-api-loader";

export default function filamentGoogleMapsWidget(
    {
        apiKey,
        cachedData,
        config,
        mapEl,
    }) {
    let map = null
    let infoWindow = null
    let data = null
    let markers = []
    let layers = []
    let modelIds = []
    let clusterer = null
    let center = null
    let myConfig = {
        center: {
            lat: 0,
                lng: 0,
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
            gmaps: "",
            layers: [],
            zoom: 12,
            markerAction: null,
            mapConfig: [],
    }
    
    return {
        mapEl: null,
        
        init: function () {
            mapEl = document.getElementById(mapEl) || mapEl;
            data = cachedData;
            myConfig = {...myConfig, ...config};
            this.createMap();
        },

        callWire: function (thing) {
        },

        async createMap() {
            setOptions({ key: apiKey })
            const {Map} = await importLibrary("maps");
            const {PlacesService} = await importLibrary("places");
            const {AdvancedMarkerElement} = await importLibrary("marker");
            
            infoWindow = new google.maps.InfoWindow({
                content: "",
                disableAutoPan: true,
            });

            map = new Map(mapEl, {
                mapId: mapEl.id,
                center: myConfig.center,
                zoom: myConfig.zoom,
                ...myConfig.controls,
                ...myConfig.mapConfig,
            });

            center = myConfig.center;

            this.createMarkers();

            this.createClustering();

            this.createLayers();

            this.idle();

            window.addEventListener(
                "filament-google-maps::widget/setMapCenter",
                (event) => {
                    this.recenter(event.detail);
                }
            );

            this.show(true);
        },
        
        show: function (force = false) {
            if (markers.length > 0 && myConfig.fit) {
                this.fitToBounds(force);
            } else {
                if (markers.length > 0) {
                    map.setCenter(markers[0].getPosition());
                } else {
                    map.setCenter(myConfig.center);
                }
            }
        },
        
        createLayers: function () {
            layers = myConfig.layers.map((layerUrl) => {
                return new google.maps.KmlLayer({
                    url: layerUrl,
                    map: map,
                });
            });
        },
        
        createMarker: function (location) {
            let markerIcon;

            if (location.icon && typeof location.icon === "object") {
                if (location.icon.hasOwnProperty("url")) {
                    markerIcon = {
                        url: location.icon.url,
                    };

                    if (
                        location.icon.hasOwnProperty("type") &&
                        location.icon.type === "svg" &&
                        location.icon.hasOwnProperty("scale")
                    ) {
                        markerIcon.scaledSize = new google.maps.Size(
                            location.icon.scale[0],
                            location.icon.scale[1]
                        );
                    }
                }
            }

            const point = location.location;
            const label = location.label;

            const marker = new google.maps.Marker({
                position: point,
                title: label,
                model_id: location.id,
                ...(markerIcon && {icon: markerIcon}),
            });

            if (modelIds.indexOf(location.id) === -1) {
                modelIds.push(location.id);
            }

            return marker;
        },
        
        createMarkers: function () {
            markers = data.map((location) => {
                const marker = this.createMarker(location);
                marker.setMap(map);

                if (myConfig.markerAction) {
                    google.maps.event.addListener(marker, "click", (event) => {
                        this.$wire.mountAction(myConfig.markerAction, {
                            model_id: marker.model_id,
                        });
                    });
                }

                return marker;
            });
        },
        
        removeMarker: function (marker) {
            marker.setMap(null);
        },
        
        removeMarkers: function () {
            for (let i = 0; i < markers.length; i++) {
                markers[i].setMap(null);
            }

            markers = [];
        },
        
        mergeMarkers: function () {
            const operation = (list1, list2, isUnion = false) =>
                list1.filter(
                    (a) =>
                        isUnion ===
                        list2.some(
                            (b) =>
                                a.getPosition().lat() === b.getPosition().lat() &&
                                a.getPosition().lng() === b.getPosition().lng()
                        )
                );

            const inBoth = (list1, list2) => operation(list1, list2, true),
                inFirstOnly = operation,
                inSecondOnly = (list1, list2) => inFirstOnly(list2, list1);

            const newMarkers = data.map((location) => {
                let marker = this.createMarker(location);
                marker.addListener("click", () => {
                    infoWindow.setContent(location.label);
                    infoWindow.open(map, marker);
                });

                return marker;
            });

            if (!myConfig.mapIsFilter) {
                const oldMarkersRemove = inSecondOnly(newMarkers, markers);

                for (let i = oldMarkersRemove.length - 1; i >= 0; i--) {
                    oldMarkersRemove[i].setMap(null);
                    const index = markers.findIndex(
                        (marker) =>
                            marker.getPosition().lat() ===
                            oldMarkersRemove[i].getPosition().lat() &&
                            marker.getPosition().lng() ===
                            oldMarkersRemove[i].getPosition().lng()
                    );
                    markers.splice(index, 1);
                }
            }

            const newMarkersCreate = inSecondOnly(markers, newMarkers);

            for (let i = 0; i < newMarkersCreate.length; i++) {
                newMarkersCreate[i].setMap(map);
                markers.push(newMarkersCreate[i]);
            }

            this.fitToBounds();
        },
        
        fitToBounds: function (force = false) {
            if (
                markers.length > 0 &&
                myConfig.fit &&
                (force || !myConfig.mapIsFilter)
            ) {
                this.bounds = new google.maps.LatLngBounds();

                for (const marker of markers) {
                    this.bounds.extend(marker.getPosition());
                }

                map.fitBounds(this.bounds);
            }
        },
        
        createClustering: function () {
            if (markers.length > 0 && myConfig.clustering) {
                // use default algorithm and renderer
                clusterer = new MarkerClusterer({
                    map: map,
                    markers: markers,
                });
            }
        },
        
        updateClustering: function () {
            if (myConfig.clustering) {
                clusterer.clearMarkers();
                clusterer.addMarkers(markers);
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

            console.log("moved");

            const bounds = map.getBounds();
            const visible = markers.filter((marker) => {
                return bounds.contains(marker.getPosition());
            });
            const ids = visible.map((marker) => marker.model_id);

            if (!areEqual(modelIds, ids)) {
                modelIds = ids;
                console.log(ids);
                this.$wire.set("mapFilterIds", ids);
            }
        },
        
        idle: function () {
            if (myConfig.mapIsFilter) {
                let that = self;
                const debouncedMoved = debounce(this.moved, 1000).bind(this);

                google.maps.event.addListener(map, "idle", (event) => {
                    if (self.isMapDragging) {
                        self.idleSkipped = true;
                        return;
                    }
                    self.idleSkipped = false;
                    debouncedMoved();
                });
                google.maps.event.addListener(map, "dragstart", (event) => {
                    self.isMapDragging = true;
                });
                google.maps.event.addListener(map, "dragend", (event) => {
                    self.isMapDragging = false;
                    if (self.idleSkipped === true) {
                        debouncedMoved();
                        self.idleSkipped = false;
                    }
                });
                google.maps.event.addListener(map, "bounds_changed", (event) => {
                    self.idleSkipped = false;
                });
            }
        },
        
        update: function (data) {
            data = data;
            this.mergeMarkers();
            this.updateClustering();
            this.show();
        },
        
        recenter: function (data) {
            map.panTo({lat: data.lat, lng: data.lng});
            map.setZoom(data.zoom);
        },
    };
}
