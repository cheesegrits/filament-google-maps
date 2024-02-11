export default function filamentGoogleMapsField({
  state,
  defaultLocation,
  controls,
  layers,
  defaultZoom,
  gmaps,
  mapEl,
  drawingField,
  geoJson,
  geoJsonField,
  geoJsonProperty,
  geoJsonVisible,
}) {
  return {
    state,
    map: null,
    geocoder: null,
    marker: null,
    markerLocation: null,
    layers: null,
    symbols: {
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
    },
    drawingManager: null,
    overlays: [],
    dataLayer: null,
    geoJsonDataLayer: null,

    loadGMaps: function () {
      if (
        !document.getElementById("filament-google-maps-google-maps-entry-js")
      ) {
        const script = document.createElement("script");
        script.id = "filament-google-maps-google-maps-entry-js";
        window.filamentGoogleMapsAsyncLoad = this.createMap.bind(this);
        script.src = gmaps + "&callback=filamentGoogleMapsAsyncLoad";
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

        waitForGlobal(
          "filamentGoogleMapsAPILoaded",
          function () {
            this.createMap();
          }.bind(this)
        );
      }
    },

    init: function () {
      this.loadGMaps();
    },

    createMap: function () {
      window.filamentGoogleMapsAPILoaded = true;

      this.map = new google.maps.Map(mapEl, {
        center: this.getCoordinates(),
        zoom: defaultZoom,
        ...controls,
      });

      this.marker = new google.maps.Marker({
        map: this.map,
      });

      this.marker.setPosition(this.getCoordinates());

      if (layers) {
        this.layers = layers.map((layerUrl) => {
          const kmlLayer = new google.maps.KmlLayer({
            url: layerUrl,
            map: this.map,
          });

          kmlLayer.addListener("click", (kmlEvent) => {
            const text = kmlEvent.featureData.description;
          });
        });
      }

      if (geoJson) {
        if (geoJsonVisible) {
          this.geoJsonDataLayer = this.map.data;
        } else {
          this.geoJsonDataLayer = new google.maps.Data();
        }

        if (/^http/.test(geoJson)) {
          this.geoJsonDataLayer.loadGeoJson(geoJson);
        } else {
          this.geoJsonDataLayer.addGeoJson(JSON.parse(geoJson));
        }
      }

      // if (drawingField) {
      //   this.dataLayer = new google.maps.Data();
      //
      //   let geoJSON = getStateUsing(drawingField);
      //   geoJSON && this.loadFeaturesCollection(JSON.parse(geoJSON));
      //
      //   google.maps.event.addListener(
      //     this.drawingManager,
      //     "overlaycomplete",
      //     (event) => {
      //       event.overlay.type = event.type;
      //       event.overlay.id = this.guid();
      //       event.overlay.feature = this.instanceFeature(event.overlay);
      //       this.addOverlayEvents(event.overlay);
      //       this.overlays.push(event.overlay);
      //
      //       if (event.type != google.maps.drawing.OverlayType.MARKER) {
      //         // Switch back to non-drawing mode after drawing a shape.
      //         this.drawingManager.setDrawingMode(null);
      //         this.setSelection(event.overlay);
      //       }
      //
      //       this.drawingModified();
      //     }
      //   );
      // }
    },

    getCoordinates: function () {
      if (this.state === null || !this.state.hasOwnProperty("lat")) {
        this.state = { lat: defaultLocation.lat, lng: defaultLocation.lng };
      }
      return this.state;
    },

    instanceOverlay: function (feature) {
      var instance = null;
      switch (feature.properties.type) {
        case google.maps.drawing.OverlayType.MARKER:
          instance = new google.maps.Marker({
            id: feature.properties.id,
            type: feature.properties.type,
            position: new google.maps.LatLng(
              feature.geometry.coordinates[1],
              feature.geometry.coordinates[0]
            ),
            draggable: true,
          });
          break;
        case google.maps.drawing.OverlayType.RECTANGLE:
          var NE = new google.maps.LatLng(
            feature.geometry.coordinates[0][2][1],
            feature.geometry.coordinates[0][2][0]
          );
          var SW = new google.maps.LatLng(
            feature.geometry.coordinates[0][0][1],
            feature.geometry.coordinates[0][0][0]
          );
          instance = new google.maps.Rectangle(
            Object.assign({}, this.polyOptions, {
              id: feature.properties.id,
              type: feature.properties.type,
              // fillColor: feature.properties.color,
              bounds: new google.maps.LatLngBounds(SW, NE),
              editable: false,
            })
          );
          break;
        case google.maps.drawing.OverlayType.POLYGON:
          instance = new google.maps.Polygon(
            Object.assign({}, this.polyOptions, {
              id: feature.properties.id,
              type: feature.properties.type,
              // fillColor: feature.properties.color,
              paths: this.transformToMVCArray(feature.geometry.coordinates),
              editable: false,
            })
          );
          break;
        case google.maps.drawing.OverlayType.POLYLINE:
          instance = new google.maps.Polyline({
            id: feature.properties.id,
            type: feature.properties.type,
            // strokeColor: feature.properties.color,
            path: this.transformToMVCArray([
              feature.geometry.coordinates,
            ]).getAt(0),
            draggable: true,
            editable: false,
          });
          break;
        case google.maps.drawing.OverlayType.CIRCLE:
          instance = new google.maps.Circle(
            Object.assign({}, this.polyOptions, {
              id: feature.properties.id,
              type: feature.properties.type,
              // fillColor: feature.properties.color,
              center: new google.maps.LatLng(
                feature.geometry.coordinates[1],
                feature.geometry.coordinates[0]
              ),
              radius: feature.properties.radius,
              editable: false,
            })
          );
          break;
      }
      // instance.zIndex = this.overlays.length + 1;
      return instance;
    },

    instanceFeature: function (overlay) {
      var calculatedOverlay = this.calculateGeometry(overlay);
      return this.dataLayer.add(
        new google.maps.Data.Feature({
          geometry: calculatedOverlay.geometry,
          properties: Object.assign(
            {
              id: this.guid(),
              type: overlay.type,
            },
            calculatedOverlay.hasOwnProperty("properties")
              ? calculatedOverlay.properties
              : {}
          ),
        })
      );
    },

    calculateGeometry: function (overlay, geometryOnly) {
      switch (overlay.type) {
        case google.maps.drawing.OverlayType.MARKER:
          return geometryOnly
            ? new google.maps.Data.Point(overlay.getPosition())
            : {
                geometry: new google.maps.Data.Point(overlay.getPosition()),
              };
        case google.maps.drawing.OverlayType.RECTANGLE:
          let b = overlay.getBounds(),
            p = [
              b.getSouthWest(),
              {
                lat: b.getSouthWest().lat(),
                lng: b.getNorthEast().lng(),
              },
              b.getNorthEast(),
              {
                lng: b.getSouthWest().lng(),
                lat: b.getNorthEast().lat(),
              },
            ];
          return geometryOnly
            ? new google.maps.Data.Polygon([p])
            : {
                geometry: new google.maps.Data.Polygon([p]),
              };
        case google.maps.drawing.OverlayType.POLYGON:
          return geometryOnly
            ? new google.maps.Data.Polygon([overlay.getPath().getArray()])
            : {
                geometry: new google.maps.Data.Polygon([
                  overlay.getPath().getArray(),
                ]),
              };
        case google.maps.drawing.OverlayType.POLYLINE:
          return geometryOnly
            ? new google.maps.Data.LineString(overlay.getPath().getArray())
            : {
                geometry: new google.maps.Data.LineString(
                  overlay.getPath().getArray()
                ),
              };
        case google.maps.drawing.OverlayType.CIRCLE:
          return geometryOnly
            ? new google.maps.Data.Point(overlay.getCenter())
            : {
                properties: {
                  radius: overlay.getRadius(),
                },
                geometry: new google.maps.Data.Point(overlay.getCenter()),
              };
      }
    },

    transformToMVCArray: function (a) {
      let clone = new google.maps.MVCArray();

      function transform($a, parent) {
        if ($a.length == 2 && !Array.isArray($a[0]) && !Array.isArray($a[1]))
          parent.push(new google.maps.LatLng($a[1], $a[0]));
        for (let a = 0; a < $a.length; a++) {
          if (!Array.isArray($a[a])) continue;
          transform(
            $a[a],
            parent
              ? $a[a].length == 2 &&
                !Array.isArray($a[a][0]) &&
                !Array.isArray($a[a][1])
                ? parent
                : parent.getAt(parent.push(new google.maps.MVCArray()) - 1)
              : clone.getAt(clone.push(new google.maps.MVCArray()) - 1)
          );
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
        let bounds = new google.maps.LatLngBounds();
        for (let f = 0; f < geoJSON.features.length; f++) {
          let overlay = this.instanceOverlay(geoJSON.features[f]);
          overlay.feature = this.instanceFeature(overlay);
          this.addOverlayEvents(overlay);
          overlay.feature.getGeometry().forEachLatLng(function (latlng) {
            bounds.extend(latlng);
          });
          // overlay.feature.setProperty("color", features[f].properties.color);
          overlay.setMap(this.map);
          this.overlays.push(overlay);
        }
        this.map.fitBounds(bounds);
      }
    },

    guid: function () {
      function s4() {
        return Math.floor((1 + Math.random()) * 0x10000)
          .toString(16)
          .substring(1);
      }

      return (
        s4() +
        s4() +
        "-" +
        s4() +
        "-" +
        s4() +
        "-" +
        s4() +
        "-" +
        s4() +
        s4() +
        s4()
      );
    },
  };
}
