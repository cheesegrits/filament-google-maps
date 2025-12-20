import { setOptions, importLibrary } from "@googlemaps/js-api-loader";

export default function filamentGoogleMapsField(
    {
        state,
        defaultLocation,
        controls,
        kmlLayers,
        defaultZoom,
        mapEl,
        geoJson,
        geoJsonVisible,
        statePath,
    }) {
    let map = null
    let marker = null
    let layers = null
    let geoJsonDataLayer = null
    
    const symbols = {
        "%n": ["street_number"],
        "%z": ["postal_code"],
        "%S": ["street_address", "route"],
        "%A1": ["administrative_area_level_1"],
        "%A2": ["administrative_area_level_2"],
        "%A3": ["administrative_area_level_3"],
        "%A4": ["administrative_area_level_4"],
        "%A5": ["administrative_area_level_5"],
        "%a1": ["administrative_area_level_1"],
        "%a2": ["administrative_area_level_2"],
        "%a3": ["administrative_area_level_3"],
        "%a4": ["administrative_area_level_4"],
        "%a5": ["administrative_area_level_5"],
        "%L": ["locality", "postal_town"],
        "%D": ["sublocality"],
        "%C": ["country"],
        "%c": ["country"],
        "%p": ["premise"],
        "%P": ["premise"],
    }

    return {
        state,


        init: function () {
            this.createMap();
        },

        async createMap() {
            setOptions({ key: apiKey })
            const {Map} = await importLibrary("maps");
            const {PlacesService} = await importLibrary("places");
            const {AdvancedMarkerElement} = await importLibrary("marker");

            map = new Map(mapEl, {
                mapId: statePath,
                center: this.getCoordinates(),
                zoom: defaultZoom,
                ...controls,
            });

            marker = new AdvancedMarkerElement({
                map: map,
                position: this.getCoordinates(),
            });

            if (layers) {
                this.layers = layers.map((layerUrl) => {
                    const kmlLayer = new google.maps.KmlLayer({
                        url: layerUrl,
                        map: map,
                    });

                    kmlLayer.addListener("click", (kmlEvent) => {
                        const text = kmlEvent.featureData.description;
                    });
                });
            }

            if (geoJson) {
                if (geoJsonVisible) {
                    geoJsonDataLayer = map.data;
                } else {
                    geoJsonDataLayer = new google.maps.Data();
                }

                if (/^http/.test(geoJson)) {
                    geoJsonDataLayer.loadGeoJson(geoJson);
                } else {
                    geoJsonDataLayer.addGeoJson(JSON.parse(geoJson));
                }
            }
        },

        getCoordinates: function () {
            if (this.state === null || !this.state.hasOwnProperty("lat")) {
                this.state = {lat: defaultLocation.lat, lng: defaultLocation.lng};
            }
            return this.state;
        },
    };
}
