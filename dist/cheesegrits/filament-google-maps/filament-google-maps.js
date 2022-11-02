/*
 * ATTENTION: An "eval-source-map" devtool has been used.
 * This devtool is neither made for production nor for readable output files.
 * It uses "eval()" calls to create a separate source file with attached SourceMaps in the browser devtools.
 * If you are trying to read the output file, select a different devtool (https://webpack.js.org/configuration/devtool/)
 * or disable the default devtool with "devtool: false".
 * If you are looking for production-ready output files, see mode: "production" (https://webpack.js.org/configuration/mode/).
 */
/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./resources/js/filament-google-maps.js":
/*!**********************************************!*\
  !*** ./resources/js/filament-google-maps.js ***!
  \**********************************************/
/***/ (() => {

eval("function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); enumerableOnly && (symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; })), keys.push.apply(keys, symbols); } return keys; }\nfunction _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = null != arguments[i] ? arguments[i] : {}; i % 2 ? ownKeys(Object(source), !0).forEach(function (key) { _defineProperty(target, key, source[key]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)) : ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } return target; }\nfunction _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }\nwindow.filamentGoogleMaps = function ($wire, config) {\n  return {\n    map: null,\n    geocoder: null,\n    marker: null,\n    markerLocation: null,\n    mapEl: null,\n    pacEl: null,\n    loadGMaps: function loadGMaps() {\n      if (!document.getElementById('filament-google-maps-google-maps-js')) {\n        var script = document.createElement('script');\n        script.id = 'filament-google-maps-google-maps-js';\n        window.filamentGoogleMapsAsyncLoad = this.createMap.bind(this);\n        script.src = config.gmaps + '&callback=filamentGoogleMapsAsyncLoad';\n        document.head.appendChild(script);\n      } else {\n        var waitForGlobal = function waitForGlobal(key, callback) {\n          if (window[key]) {\n            callback();\n          } else {\n            setTimeout(function () {\n              waitForGlobal(key, callback);\n            }, 100);\n          }\n        };\n        waitForGlobal(\"filamentGoogleMapsAPILoaded\", function () {\n          this.createMap();\n        }.bind(this));\n      }\n    },\n    init: function init(mapEl, pacEl) {\n      this.mapEl = mapEl;\n      this.pacEl = pacEl;\n      this.loadGMaps();\n    },\n    createMap: function createMap() {\n      var _this = this;\n      window.filamentGoogleMapsAPILoaded = true;\n      var position = this.getCoordinates();\n      this.map = new google.maps.Map(this.mapEl, _objectSpread({\n        center: this.getCoordinates(),\n        zoom: config.defaultZoom\n      }, config.controls));\n      this.marker = new google.maps.Marker({\n        draggable: config.draggable,\n        map: this.map\n      });\n      this.marker.setPosition(this.getCoordinates());\n      if (config.clickable) {\n        this.map.addListener('click', function (event) {\n          _this.markerLocation = event.latLng.toJSON();\n          _this.setCoordinates(_this.markerLocation);\n          _this.updateAutocomplete(_this.markerLocation);\n          //this.updateMap(this.markerLocation);\n          _this.map.panTo(_this.markerLocation);\n        });\n      }\n      if (config.draggable) {\n        google.maps.event.addListener(this.marker, 'dragend', function (event) {\n          _this.markerLocation = event.latLng.toJSON();\n          _this.setCoordinates(_this.markerLocation);\n          _this.updateAutocomplete(_this.markerLocation);\n          // this.updateMap(this.markerLocation);\n          _this.map.panTo(_this.markerLocation);\n        });\n      }\n      if (config.controls.searchBoxControl) {\n        var input = this.pacEl;\n        var searchBox = new google.maps.places.SearchBox(input);\n        this.map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);\n        searchBox.addListener(\"places_changed\", function () {\n          input.value = '';\n          _this.markerLocation = searchBox.getPlaces()[0].geometry.location;\n        });\n      }\n      var geocompleteOptions = {\n        fields: [\"formatted_address\", \"geometry\", \"name\"],\n        strictBounds: false,\n        types: [\"geocode\"]\n      };\n      if (config.autocomplete) {\n        if (config.autocompleteReverse) {\n          this.geocoder = new google.maps.Geocoder();\n        }\n        var geoComplete = document.getElementById(config.autocomplete);\n        if (geoComplete) {\n          window.addEventListener('keydown', function (e) {\n            if (e.keyIdentifier === 'U+000A' || e.keyIdentifier === 'Enter' || e.keyCode === 13) {\n              if (e.target.nodeName == 'INPUT' && e.target.type == 'text') {\n                e.preventDefault();\n                return false;\n              }\n            }\n          }, true);\n          var autocomplete = new google.maps.places.Autocomplete(geoComplete, geocompleteOptions);\n          autocomplete.addListener(\"place_changed\", function (ev) {\n            var place = autocomplete.getPlace();\n            if (!place.geometry || !place.geometry.location) {\n              // User entered the name of a Place that was not suggested and\n              // pressed the Enter key, or the Place Details request failed.\n              window.alert(\"No details available for input: '\" + place.name + \"'\");\n              return;\n            }\n\n            // If the place has a geometry, then present it on a map.\n            if (place.geometry.viewport) {\n              _this.map.fitBounds(place.geometry.viewport);\n            } else {\n              _this.map.setCenter(place.geometry.location);\n            }\n            _this.marker.setPosition(place.geometry.location);\n            _this.markerLocation = place.geometry.location;\n            _this.setCoordinates(place.geometry.location);\n          });\n        }\n      }\n    },\n    updateMapFromAlpine: function updateMapFromAlpine() {\n      var location = this.getCoordinates();\n      var markerLocation = this.marker.getPosition();\n      if (!(location.lat === markerLocation.lat() && location.lng === markerLocation.lng())) {\n        this.updateAutocomplete(location);\n        this.updateMap(location);\n      }\n    },\n    updateMap: function updateMap(position) {\n      this.marker.setPosition(position);\n      this.map.panTo(position);\n    },\n    updateAutocomplete: function updateAutocomplete(position) {\n      if (config.autocomplete && config.autocompleteReverse) {\n        this.geocoder.geocode({\n          location: position\n        }).then(function (response) {\n          if (response.results[0]) {\n            $wire.set(config.autocomplete, response.results[0].formatted_address);\n          }\n        });\n      }\n    },\n    setCoordinates: function setCoordinates(position) {\n      $wire.set(config.statePath, position, false);\n    },\n    getCoordinates: function getCoordinates() {\n      var location = $wire.get(config.statePath);\n      if (location === null || !location.hasOwnProperty('lat')) {\n        location = {\n          lat: config.defaultLocation.lat,\n          lng: config.defaultLocation.lng\n        };\n      }\n      return location;\n    }\n  };\n};//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiLi9yZXNvdXJjZXMvanMvZmlsYW1lbnQtZ29vZ2xlLW1hcHMuanMuanMiLCJuYW1lcyI6WyJ3aW5kb3ciLCJmaWxhbWVudEdvb2dsZU1hcHMiLCIkd2lyZSIsImNvbmZpZyIsIm1hcCIsImdlb2NvZGVyIiwibWFya2VyIiwibWFya2VyTG9jYXRpb24iLCJtYXBFbCIsInBhY0VsIiwibG9hZEdNYXBzIiwiZG9jdW1lbnQiLCJnZXRFbGVtZW50QnlJZCIsInNjcmlwdCIsImNyZWF0ZUVsZW1lbnQiLCJpZCIsImZpbGFtZW50R29vZ2xlTWFwc0FzeW5jTG9hZCIsImNyZWF0ZU1hcCIsImJpbmQiLCJzcmMiLCJnbWFwcyIsImhlYWQiLCJhcHBlbmRDaGlsZCIsIndhaXRGb3JHbG9iYWwiLCJrZXkiLCJjYWxsYmFjayIsInNldFRpbWVvdXQiLCJpbml0IiwiZmlsYW1lbnRHb29nbGVNYXBzQVBJTG9hZGVkIiwicG9zaXRpb24iLCJnZXRDb29yZGluYXRlcyIsImdvb2dsZSIsIm1hcHMiLCJNYXAiLCJjZW50ZXIiLCJ6b29tIiwiZGVmYXVsdFpvb20iLCJjb250cm9scyIsIk1hcmtlciIsImRyYWdnYWJsZSIsInNldFBvc2l0aW9uIiwiY2xpY2thYmxlIiwiYWRkTGlzdGVuZXIiLCJldmVudCIsImxhdExuZyIsInRvSlNPTiIsInNldENvb3JkaW5hdGVzIiwidXBkYXRlQXV0b2NvbXBsZXRlIiwicGFuVG8iLCJzZWFyY2hCb3hDb250cm9sIiwiaW5wdXQiLCJzZWFyY2hCb3giLCJwbGFjZXMiLCJTZWFyY2hCb3giLCJDb250cm9sUG9zaXRpb24iLCJUT1BfTEVGVCIsInB1c2giLCJ2YWx1ZSIsImdldFBsYWNlcyIsImdlb21ldHJ5IiwibG9jYXRpb24iLCJnZW9jb21wbGV0ZU9wdGlvbnMiLCJmaWVsZHMiLCJzdHJpY3RCb3VuZHMiLCJ0eXBlcyIsImF1dG9jb21wbGV0ZSIsImF1dG9jb21wbGV0ZVJldmVyc2UiLCJHZW9jb2RlciIsImdlb0NvbXBsZXRlIiwiYWRkRXZlbnRMaXN0ZW5lciIsImUiLCJrZXlJZGVudGlmaWVyIiwia2V5Q29kZSIsInRhcmdldCIsIm5vZGVOYW1lIiwidHlwZSIsInByZXZlbnREZWZhdWx0IiwiQXV0b2NvbXBsZXRlIiwiZXYiLCJwbGFjZSIsImdldFBsYWNlIiwiYWxlcnQiLCJuYW1lIiwidmlld3BvcnQiLCJmaXRCb3VuZHMiLCJzZXRDZW50ZXIiLCJ1cGRhdGVNYXBGcm9tQWxwaW5lIiwiZ2V0UG9zaXRpb24iLCJsYXQiLCJsbmciLCJ1cGRhdGVNYXAiLCJnZW9jb2RlIiwidGhlbiIsInJlc3BvbnNlIiwicmVzdWx0cyIsInNldCIsImZvcm1hdHRlZF9hZGRyZXNzIiwic3RhdGVQYXRoIiwiZ2V0IiwiaGFzT3duUHJvcGVydHkiLCJkZWZhdWx0TG9jYXRpb24iXSwic291cmNlUm9vdCI6IiIsInNvdXJjZXMiOlsid2VicGFjazovL2ZpbGFtZW50LWdvb2dsZS1tYXBzLy4vcmVzb3VyY2VzL2pzL2ZpbGFtZW50LWdvb2dsZS1tYXBzLmpzP2UzZTIiXSwic291cmNlc0NvbnRlbnQiOlsid2luZG93LmZpbGFtZW50R29vZ2xlTWFwcyA9ICgkd2lyZSwgY29uZmlnKSA9PiB7XG4gICAgcmV0dXJuIHtcbiAgICAgICAgbWFwOiBudWxsLFxuICAgICAgICBnZW9jb2RlcjogbnVsbCxcbiAgICAgICAgbWFya2VyOiBudWxsLFxuICAgICAgICBtYXJrZXJMb2NhdGlvbjogbnVsbCxcbiAgICAgICAgbWFwRWw6IG51bGwsXG4gICAgICAgIHBhY0VsOiBudWxsLFxuXG4gICAgICAgIGxvYWRHTWFwczogZnVuY3Rpb24gKCkge1xuICAgICAgICAgICAgaWYgKCFkb2N1bWVudC5nZXRFbGVtZW50QnlJZCgnZmlsYW1lbnQtZ29vZ2xlLW1hcHMtZ29vZ2xlLW1hcHMtanMnKSkge1xuICAgICAgICAgICAgICAgIGNvbnN0IHNjcmlwdCA9IGRvY3VtZW50LmNyZWF0ZUVsZW1lbnQoJ3NjcmlwdCcpO1xuICAgICAgICAgICAgICAgIHNjcmlwdC5pZCA9ICdmaWxhbWVudC1nb29nbGUtbWFwcy1nb29nbGUtbWFwcy1qcyc7XG4gICAgICAgICAgICAgICAgd2luZG93LmZpbGFtZW50R29vZ2xlTWFwc0FzeW5jTG9hZCA9IHRoaXMuY3JlYXRlTWFwLmJpbmQodGhpcyk7XG4gICAgICAgICAgICAgICAgc2NyaXB0LnNyYyA9IGNvbmZpZy5nbWFwcyArICcmY2FsbGJhY2s9ZmlsYW1lbnRHb29nbGVNYXBzQXN5bmNMb2FkJztcbiAgICAgICAgICAgICAgICBkb2N1bWVudC5oZWFkLmFwcGVuZENoaWxkKHNjcmlwdCk7XG4gICAgICAgICAgICB9IGVsc2Uge1xuICAgICAgICAgICAgICAgIGNvbnN0IHdhaXRGb3JHbG9iYWwgPSBmdW5jdGlvbiAoa2V5LCBjYWxsYmFjaykge1xuICAgICAgICAgICAgICAgICAgICBpZiAod2luZG93W2tleV0pIHtcbiAgICAgICAgICAgICAgICAgICAgICAgIGNhbGxiYWNrKCk7XG4gICAgICAgICAgICAgICAgICAgIH0gZWxzZSB7XG4gICAgICAgICAgICAgICAgICAgICAgICBzZXRUaW1lb3V0KGZ1bmN0aW9uICgpIHtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICB3YWl0Rm9yR2xvYmFsKGtleSwgY2FsbGJhY2spO1xuICAgICAgICAgICAgICAgICAgICAgICAgfSwgMTAwKTtcbiAgICAgICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgIH07XG5cbiAgICAgICAgICAgICAgICB3YWl0Rm9yR2xvYmFsKFwiZmlsYW1lbnRHb29nbGVNYXBzQVBJTG9hZGVkXCIsIGZ1bmN0aW9uICgpIHtcbiAgICAgICAgICAgICAgICAgICAgdGhpcy5jcmVhdGVNYXAoKTtcbiAgICAgICAgICAgICAgICB9LmJpbmQodGhpcykpO1xuICAgICAgICAgICAgfVxuICAgICAgICB9LFxuXG4gICAgICAgIGluaXQ6IGZ1bmN0aW9uIChtYXBFbCwgcGFjRWwpIHtcbiAgICAgICAgICAgIHRoaXMubWFwRWwgPSBtYXBFbDtcbiAgICAgICAgICAgIHRoaXMucGFjRWwgPSBwYWNFbDtcbiAgICAgICAgICAgIHRoaXMubG9hZEdNYXBzKCk7XG4gICAgICAgIH0sXG5cbiAgICAgICAgY3JlYXRlTWFwOiBmdW5jdGlvbiAoKSB7XG4gICAgICAgICAgICB3aW5kb3cuZmlsYW1lbnRHb29nbGVNYXBzQVBJTG9hZGVkID0gdHJ1ZTtcblxuICAgICAgICAgICAgbGV0IHBvc2l0aW9uID0gdGhpcy5nZXRDb29yZGluYXRlcygpO1xuXG4gICAgICAgICAgICB0aGlzLm1hcCA9IG5ldyBnb29nbGUubWFwcy5NYXAodGhpcy5tYXBFbCwge1xuICAgICAgICAgICAgICAgIGNlbnRlcjogdGhpcy5nZXRDb29yZGluYXRlcygpLFxuICAgICAgICAgICAgICAgIHpvb206IGNvbmZpZy5kZWZhdWx0Wm9vbSxcbiAgICAgICAgICAgICAgICAuLi5jb25maWcuY29udHJvbHNcbiAgICAgICAgICAgIH0pO1xuXG5cbiAgICAgICAgICAgIHRoaXMubWFya2VyID0gbmV3IGdvb2dsZS5tYXBzLk1hcmtlcih7XG4gICAgICAgICAgICAgICAgZHJhZ2dhYmxlOiBjb25maWcuZHJhZ2dhYmxlLFxuICAgICAgICAgICAgICAgIG1hcDogdGhpcy5tYXBcbiAgICAgICAgICAgIH0pO1xuXG4gICAgICAgICAgICB0aGlzLm1hcmtlci5zZXRQb3NpdGlvbih0aGlzLmdldENvb3JkaW5hdGVzKCkpO1xuXG4gICAgICAgICAgICBpZiAoY29uZmlnLmNsaWNrYWJsZSkge1xuICAgICAgICAgICAgICAgIHRoaXMubWFwLmFkZExpc3RlbmVyKCdjbGljaycsIChldmVudCkgPT4ge1xuICAgICAgICAgICAgICAgICAgICB0aGlzLm1hcmtlckxvY2F0aW9uID0gZXZlbnQubGF0TG5nLnRvSlNPTigpO1xuICAgICAgICAgICAgICAgICAgICB0aGlzLnNldENvb3JkaW5hdGVzKHRoaXMubWFya2VyTG9jYXRpb24pO1xuICAgICAgICAgICAgICAgICAgICB0aGlzLnVwZGF0ZUF1dG9jb21wbGV0ZSh0aGlzLm1hcmtlckxvY2F0aW9uKTtcbiAgICAgICAgICAgICAgICAgICAgLy90aGlzLnVwZGF0ZU1hcCh0aGlzLm1hcmtlckxvY2F0aW9uKTtcbiAgICAgICAgICAgICAgICAgICAgdGhpcy5tYXAucGFuVG8odGhpcy5tYXJrZXJMb2NhdGlvbik7XG4gICAgICAgICAgICAgICAgfSk7XG4gICAgICAgICAgICB9XG5cbiAgICAgICAgICAgIGlmIChjb25maWcuZHJhZ2dhYmxlKSB7XG4gICAgICAgICAgICAgICAgZ29vZ2xlLm1hcHMuZXZlbnQuYWRkTGlzdGVuZXIodGhpcy5tYXJrZXIsICdkcmFnZW5kJywgKGV2ZW50KSA9PiB7XG4gICAgICAgICAgICAgICAgICAgIHRoaXMubWFya2VyTG9jYXRpb24gPSBldmVudC5sYXRMbmcudG9KU09OKCk7XG4gICAgICAgICAgICAgICAgICAgIHRoaXMuc2V0Q29vcmRpbmF0ZXModGhpcy5tYXJrZXJMb2NhdGlvbik7XG4gICAgICAgICAgICAgICAgICAgIHRoaXMudXBkYXRlQXV0b2NvbXBsZXRlKHRoaXMubWFya2VyTG9jYXRpb24pO1xuICAgICAgICAgICAgICAgICAgICAvLyB0aGlzLnVwZGF0ZU1hcCh0aGlzLm1hcmtlckxvY2F0aW9uKTtcbiAgICAgICAgICAgICAgICAgICAgdGhpcy5tYXAucGFuVG8odGhpcy5tYXJrZXJMb2NhdGlvbik7XG4gICAgICAgICAgICAgICAgfSk7XG4gICAgICAgICAgICB9XG5cbiAgICAgICAgICAgIGlmIChjb25maWcuY29udHJvbHMuc2VhcmNoQm94Q29udHJvbCkge1xuICAgICAgICAgICAgICAgIGNvbnN0IGlucHV0ID0gdGhpcy5wYWNFbDtcbiAgICAgICAgICAgICAgICBjb25zdCBzZWFyY2hCb3ggPSBuZXcgZ29vZ2xlLm1hcHMucGxhY2VzLlNlYXJjaEJveChpbnB1dCk7XG4gICAgICAgICAgICAgICAgdGhpcy5tYXAuY29udHJvbHNbZ29vZ2xlLm1hcHMuQ29udHJvbFBvc2l0aW9uLlRPUF9MRUZUXS5wdXNoKGlucHV0KTtcbiAgICAgICAgICAgICAgICBzZWFyY2hCb3guYWRkTGlzdGVuZXIoXCJwbGFjZXNfY2hhbmdlZFwiLCAoKSA9PiB7XG4gICAgICAgICAgICAgICAgICAgIGlucHV0LnZhbHVlID0gJydcbiAgICAgICAgICAgICAgICAgICAgdGhpcy5tYXJrZXJMb2NhdGlvbiA9IHNlYXJjaEJveC5nZXRQbGFjZXMoKVswXS5nZW9tZXRyeS5sb2NhdGlvblxuICAgICAgICAgICAgICAgIH0pXG4gICAgICAgICAgICB9XG5cbiAgICAgICAgICAgIGNvbnN0IGdlb2NvbXBsZXRlT3B0aW9ucyA9IHtcbiAgICAgICAgICAgICAgICBmaWVsZHM6IFtcImZvcm1hdHRlZF9hZGRyZXNzXCIsIFwiZ2VvbWV0cnlcIiwgXCJuYW1lXCJdLFxuICAgICAgICAgICAgICAgIHN0cmljdEJvdW5kczogZmFsc2UsXG4gICAgICAgICAgICAgICAgdHlwZXM6IFtcImdlb2NvZGVcIl0sXG4gICAgICAgICAgICB9O1xuXG5cbiAgICAgICAgICAgIGlmIChjb25maWcuYXV0b2NvbXBsZXRlKSB7XG4gICAgICAgICAgICAgICAgaWYgKGNvbmZpZy5hdXRvY29tcGxldGVSZXZlcnNlKSB7XG4gICAgICAgICAgICAgICAgICAgIHRoaXMuZ2VvY29kZXIgPSBuZXcgZ29vZ2xlLm1hcHMuR2VvY29kZXIoKTtcbiAgICAgICAgICAgICAgICB9XG5cbiAgICAgICAgICAgICAgICBjb25zdCBnZW9Db21wbGV0ZSA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkKGNvbmZpZy5hdXRvY29tcGxldGUpO1xuXG4gICAgICAgICAgICAgICAgaWYgKGdlb0NvbXBsZXRlKSB7XG4gICAgICAgICAgICAgICAgICAgIHdpbmRvdy5hZGRFdmVudExpc3RlbmVyKCdrZXlkb3duJywgZnVuY3Rpb24gKGUpIHtcbiAgICAgICAgICAgICAgICAgICAgICAgIGlmIChlLmtleUlkZW50aWZpZXIgPT09ICdVKzAwMEEnIHx8IGUua2V5SWRlbnRpZmllciA9PT0gJ0VudGVyJyB8fCBlLmtleUNvZGUgPT09IDEzKSB7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgaWYgKGUudGFyZ2V0Lm5vZGVOYW1lID09ICdJTlBVVCcgJiYgZS50YXJnZXQudHlwZSA9PSAndGV4dCcpIHtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgZS5wcmV2ZW50RGVmYXVsdCgpO1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICByZXR1cm4gZmFsc2U7XG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgICAgICAgICB9LCB0cnVlKTtcblxuICAgICAgICAgICAgICAgICAgICBjb25zdCBhdXRvY29tcGxldGUgPSBuZXcgZ29vZ2xlLm1hcHMucGxhY2VzLkF1dG9jb21wbGV0ZShnZW9Db21wbGV0ZSwgZ2VvY29tcGxldGVPcHRpb25zKTtcblxuICAgICAgICAgICAgICAgICAgICBhdXRvY29tcGxldGUuYWRkTGlzdGVuZXIoXCJwbGFjZV9jaGFuZ2VkXCIsIChldikgPT4ge1xuICAgICAgICAgICAgICAgICAgICAgICAgY29uc3QgcGxhY2UgPSBhdXRvY29tcGxldGUuZ2V0UGxhY2UoKTtcblxuICAgICAgICAgICAgICAgICAgICAgICAgaWYgKCFwbGFjZS5nZW9tZXRyeSB8fCAhcGxhY2UuZ2VvbWV0cnkubG9jYXRpb24pIHtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAvLyBVc2VyIGVudGVyZWQgdGhlIG5hbWUgb2YgYSBQbGFjZSB0aGF0IHdhcyBub3Qgc3VnZ2VzdGVkIGFuZFxuICAgICAgICAgICAgICAgICAgICAgICAgICAgIC8vIHByZXNzZWQgdGhlIEVudGVyIGtleSwgb3IgdGhlIFBsYWNlIERldGFpbHMgcmVxdWVzdCBmYWlsZWQuXG4gICAgICAgICAgICAgICAgICAgICAgICAgICAgd2luZG93LmFsZXJ0KFwiTm8gZGV0YWlscyBhdmFpbGFibGUgZm9yIGlucHV0OiAnXCIgKyBwbGFjZS5uYW1lICsgXCInXCIpO1xuICAgICAgICAgICAgICAgICAgICAgICAgICAgIHJldHVybjtcbiAgICAgICAgICAgICAgICAgICAgICAgIH1cblxuICAgICAgICAgICAgICAgICAgICAgICAgLy8gSWYgdGhlIHBsYWNlIGhhcyBhIGdlb21ldHJ5LCB0aGVuIHByZXNlbnQgaXQgb24gYSBtYXAuXG4gICAgICAgICAgICAgICAgICAgICAgICBpZiAocGxhY2UuZ2VvbWV0cnkudmlld3BvcnQpIHtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICB0aGlzLm1hcC5maXRCb3VuZHMocGxhY2UuZ2VvbWV0cnkudmlld3BvcnQpO1xuICAgICAgICAgICAgICAgICAgICAgICAgfSBlbHNlIHtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICB0aGlzLm1hcC5zZXRDZW50ZXIocGxhY2UuZ2VvbWV0cnkubG9jYXRpb24pO1xuICAgICAgICAgICAgICAgICAgICAgICAgfVxuXG4gICAgICAgICAgICAgICAgICAgICAgICB0aGlzLm1hcmtlci5zZXRQb3NpdGlvbihwbGFjZS5nZW9tZXRyeS5sb2NhdGlvbik7XG4gICAgICAgICAgICAgICAgICAgICAgICB0aGlzLm1hcmtlckxvY2F0aW9uID0gcGxhY2UuZ2VvbWV0cnkubG9jYXRpb247XG4gICAgICAgICAgICAgICAgICAgICAgICB0aGlzLnNldENvb3JkaW5hdGVzKHBsYWNlLmdlb21ldHJ5LmxvY2F0aW9uKTtcbiAgICAgICAgICAgICAgICAgICAgfSk7XG4gICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgfVxuXG5cbiAgICAgICAgfSxcbiAgICAgICAgdXBkYXRlTWFwRnJvbUFscGluZTogZnVuY3Rpb24gKCkge1xuICAgICAgICAgICAgY29uc3QgbG9jYXRpb24gPSB0aGlzLmdldENvb3JkaW5hdGVzKCk7XG4gICAgICAgICAgICBjb25zdCBtYXJrZXJMb2NhdGlvbiA9IHRoaXMubWFya2VyLmdldFBvc2l0aW9uKCk7XG5cbiAgICAgICAgICAgIGlmICghKGxvY2F0aW9uLmxhdCA9PT0gbWFya2VyTG9jYXRpb24ubGF0KCkgJiYgbG9jYXRpb24ubG5nID09PSBtYXJrZXJMb2NhdGlvbi5sbmcoKSkpIHtcbiAgICAgICAgICAgICAgICB0aGlzLnVwZGF0ZUF1dG9jb21wbGV0ZShsb2NhdGlvbilcbiAgICAgICAgICAgICAgICB0aGlzLnVwZGF0ZU1hcChsb2NhdGlvbik7XG4gICAgICAgICAgICB9XG4gICAgICAgIH0sXG4gICAgICAgIHVwZGF0ZU1hcDogZnVuY3Rpb24gKHBvc2l0aW9uKSB7XG4gICAgICAgICAgICB0aGlzLm1hcmtlci5zZXRQb3NpdGlvbihwb3NpdGlvbik7XG4gICAgICAgICAgICB0aGlzLm1hcC5wYW5Ubyhwb3NpdGlvbik7XG4gICAgICAgIH0sXG4gICAgICAgIHVwZGF0ZUF1dG9jb21wbGV0ZTogZnVuY3Rpb24gKHBvc2l0aW9uKSB7XG4gICAgICAgICAgICBpZiAoY29uZmlnLmF1dG9jb21wbGV0ZSAmJiBjb25maWcuYXV0b2NvbXBsZXRlUmV2ZXJzZSkge1xuICAgICAgICAgICAgICAgIHRoaXMuZ2VvY29kZXJcbiAgICAgICAgICAgICAgICAgICAgLmdlb2NvZGUoe2xvY2F0aW9uOiBwb3NpdGlvbn0pXG4gICAgICAgICAgICAgICAgICAgIC50aGVuKChyZXNwb25zZSkgPT4ge1xuICAgICAgICAgICAgICAgICAgICAgICAgaWYgKHJlc3BvbnNlLnJlc3VsdHNbMF0pIHtcbiAgICAgICAgICAgICAgICAgICAgICAgICAgICAkd2lyZS5zZXQoY29uZmlnLmF1dG9jb21wbGV0ZSwgcmVzcG9uc2UucmVzdWx0c1swXS5mb3JtYXR0ZWRfYWRkcmVzcyk7XG4gICAgICAgICAgICAgICAgICAgICAgICB9XG4gICAgICAgICAgICAgICAgICAgIH0pXG4gICAgICAgICAgICB9XG4gICAgICAgIH0sXG4gICAgICAgIHNldENvb3JkaW5hdGVzOiBmdW5jdGlvbiAocG9zaXRpb24pIHtcbiAgICAgICAgICAgICR3aXJlLnNldChjb25maWcuc3RhdGVQYXRoLCBwb3NpdGlvbiwgZmFsc2UpO1xuICAgICAgICB9LFxuICAgICAgICBnZXRDb29yZGluYXRlczogZnVuY3Rpb24gKCkge1xuICAgICAgICAgICAgbGV0IGxvY2F0aW9uID0gJHdpcmUuZ2V0KGNvbmZpZy5zdGF0ZVBhdGgpXG4gICAgICAgICAgICBpZiAobG9jYXRpb24gPT09IG51bGwgfHwgIWxvY2F0aW9uLmhhc093blByb3BlcnR5KCdsYXQnKSkge1xuICAgICAgICAgICAgICAgIGxvY2F0aW9uID0ge2xhdDogY29uZmlnLmRlZmF1bHRMb2NhdGlvbi5sYXQsIGxuZzogY29uZmlnLmRlZmF1bHRMb2NhdGlvbi5sbmd9XG4gICAgICAgICAgICB9XG4gICAgICAgICAgICByZXR1cm4gbG9jYXRpb247XG4gICAgICAgIH0sXG4gICAgfVxufSJdLCJtYXBwaW5ncyI6Ijs7O0FBQUFBLE1BQU0sQ0FBQ0Msa0JBQWtCLEdBQUcsVUFBQ0MsS0FBSyxFQUFFQyxNQUFNLEVBQUs7RUFDM0MsT0FBTztJQUNIQyxHQUFHLEVBQUUsSUFBSTtJQUNUQyxRQUFRLEVBQUUsSUFBSTtJQUNkQyxNQUFNLEVBQUUsSUFBSTtJQUNaQyxjQUFjLEVBQUUsSUFBSTtJQUNwQkMsS0FBSyxFQUFFLElBQUk7SUFDWEMsS0FBSyxFQUFFLElBQUk7SUFFWEMsU0FBUyxFQUFFLHFCQUFZO01BQ25CLElBQUksQ0FBQ0MsUUFBUSxDQUFDQyxjQUFjLENBQUMscUNBQXFDLENBQUMsRUFBRTtRQUNqRSxJQUFNQyxNQUFNLEdBQUdGLFFBQVEsQ0FBQ0csYUFBYSxDQUFDLFFBQVEsQ0FBQztRQUMvQ0QsTUFBTSxDQUFDRSxFQUFFLEdBQUcscUNBQXFDO1FBQ2pEZixNQUFNLENBQUNnQiwyQkFBMkIsR0FBRyxJQUFJLENBQUNDLFNBQVMsQ0FBQ0MsSUFBSSxDQUFDLElBQUksQ0FBQztRQUM5REwsTUFBTSxDQUFDTSxHQUFHLEdBQUdoQixNQUFNLENBQUNpQixLQUFLLEdBQUcsdUNB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\n//# sourceURL=webpack-internal:///./resources/js/filament-google-maps.js\n");

/***/ }),

/***/ "./resources/css/filament-google-maps.css":
/*!************************************************!*\
  !*** ./resources/css/filament-google-maps.css ***!
  \************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n// extracted by mini-css-extract-plugin\n//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiLi9yZXNvdXJjZXMvY3NzL2ZpbGFtZW50LWdvb2dsZS1tYXBzLmNzcy5qcyIsIm1hcHBpbmdzIjoiO0FBQUEiLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly9maWxhbWVudC1nb29nbGUtbWFwcy8uL3Jlc291cmNlcy9jc3MvZmlsYW1lbnQtZ29vZ2xlLW1hcHMuY3NzP2FjZWYiXSwic291cmNlc0NvbnRlbnQiOlsiLy8gZXh0cmFjdGVkIGJ5IG1pbmktY3NzLWV4dHJhY3QtcGx1Z2luXG5leHBvcnQge307Il0sIm5hbWVzIjpbXSwic291cmNlUm9vdCI6IiJ9\n//# sourceURL=webpack-internal:///./resources/css/filament-google-maps.css\n");

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = __webpack_modules__;
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/chunk loaded */
/******/ 	(() => {
/******/ 		var deferred = [];
/******/ 		__webpack_require__.O = (result, chunkIds, fn, priority) => {
/******/ 			if(chunkIds) {
/******/ 				priority = priority || 0;
/******/ 				for(var i = deferred.length; i > 0 && deferred[i - 1][2] > priority; i--) deferred[i] = deferred[i - 1];
/******/ 				deferred[i] = [chunkIds, fn, priority];
/******/ 				return;
/******/ 			}
/******/ 			var notFulfilled = Infinity;
/******/ 			for (var i = 0; i < deferred.length; i++) {
/******/ 				var [chunkIds, fn, priority] = deferred[i];
/******/ 				var fulfilled = true;
/******/ 				for (var j = 0; j < chunkIds.length; j++) {
/******/ 					if ((priority & 1 === 0 || notFulfilled >= priority) && Object.keys(__webpack_require__.O).every((key) => (__webpack_require__.O[key](chunkIds[j])))) {
/******/ 						chunkIds.splice(j--, 1);
/******/ 					} else {
/******/ 						fulfilled = false;
/******/ 						if(priority < notFulfilled) notFulfilled = priority;
/******/ 					}
/******/ 				}
/******/ 				if(fulfilled) {
/******/ 					deferred.splice(i--, 1)
/******/ 					var r = fn();
/******/ 					if (r !== undefined) result = r;
/******/ 				}
/******/ 			}
/******/ 			return result;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/jsonp chunk loading */
/******/ 	(() => {
/******/ 		// no baseURI
/******/ 		
/******/ 		// object to store loaded and loading chunks
/******/ 		// undefined = chunk not loaded, null = chunk preloaded/prefetched
/******/ 		// [resolve, reject, Promise] = chunk loading, 0 = chunk loaded
/******/ 		var installedChunks = {
/******/ 			"/cheesegrits/filament-google-maps/filament-google-maps": 0,
/******/ 			"cheesegrits/filament-google-maps/filament-google-maps": 0
/******/ 		};
/******/ 		
/******/ 		// no chunk on demand loading
/******/ 		
/******/ 		// no prefetching
/******/ 		
/******/ 		// no preloaded
/******/ 		
/******/ 		// no HMR
/******/ 		
/******/ 		// no HMR manifest
/******/ 		
/******/ 		__webpack_require__.O.j = (chunkId) => (installedChunks[chunkId] === 0);
/******/ 		
/******/ 		// install a JSONP callback for chunk loading
/******/ 		var webpackJsonpCallback = (parentChunkLoadingFunction, data) => {
/******/ 			var [chunkIds, moreModules, runtime] = data;
/******/ 			// add "moreModules" to the modules object,
/******/ 			// then flag all "chunkIds" as loaded and fire callback
/******/ 			var moduleId, chunkId, i = 0;
/******/ 			if(chunkIds.some((id) => (installedChunks[id] !== 0))) {
/******/ 				for(moduleId in moreModules) {
/******/ 					if(__webpack_require__.o(moreModules, moduleId)) {
/******/ 						__webpack_require__.m[moduleId] = moreModules[moduleId];
/******/ 					}
/******/ 				}
/******/ 				if(runtime) var result = runtime(__webpack_require__);
/******/ 			}
/******/ 			if(parentChunkLoadingFunction) parentChunkLoadingFunction(data);
/******/ 			for(;i < chunkIds.length; i++) {
/******/ 				chunkId = chunkIds[i];
/******/ 				if(__webpack_require__.o(installedChunks, chunkId) && installedChunks[chunkId]) {
/******/ 					installedChunks[chunkId][0]();
/******/ 				}
/******/ 				installedChunks[chunkId] = 0;
/******/ 			}
/******/ 			return __webpack_require__.O(result);
/******/ 		}
/******/ 		
/******/ 		var chunkLoadingGlobal = self["webpackChunkfilament_google_maps"] = self["webpackChunkfilament_google_maps"] || [];
/******/ 		chunkLoadingGlobal.forEach(webpackJsonpCallback.bind(null, 0));
/******/ 		chunkLoadingGlobal.push = webpackJsonpCallback.bind(null, chunkLoadingGlobal.push.bind(chunkLoadingGlobal));
/******/ 	})();
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module depends on other loaded chunks and execution need to be delayed
/******/ 	__webpack_require__.O(undefined, ["cheesegrits/filament-google-maps/filament-google-maps"], () => (__webpack_require__("./resources/js/filament-google-maps.js")))
/******/ 	var __webpack_exports__ = __webpack_require__.O(undefined, ["cheesegrits/filament-google-maps/filament-google-maps"], () => (__webpack_require__("./resources/css/filament-google-maps.css")))
/******/ 	__webpack_exports__ = __webpack_require__.O(__webpack_exports__);
/******/ 	
/******/ })()
;