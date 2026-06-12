/**
 * Google Maps + Places: location field autocomplete and city-biased suggestions.
 * Requires maps API script with libraries=places and callback=initNbAutocomplete (see footer).
 */
(function () {
  var state = {
    geocoder: null
  };

  function getPanelRoot(locInput) {
    var panel = locInput.closest('.nb-search-filters-panel');
    if (panel) {
      return panel;
    }
    return null;
  }

  function getCitySelectFor(locInput) {
    var root = getPanelRoot(locInput);
    if (root) {
      return root.querySelector('.nb-city-select-sync, select[name="city_id"]');
    }
    return document.getElementById('city-select');
  }

  function biasAutocompleteToCity(autocomplete, citySelect) {
    if (!autocomplete || typeof google === 'undefined' || !google.maps) return;
    if (!citySelect || !citySelect.value) return;
    var opt = citySelect.options[citySelect.selectedIndex];
    var label = opt ? opt.textContent.trim() : '';
    if (!label) return;
    if (!state.geocoder) {
      state.geocoder = new google.maps.Geocoder();
    }
    state.geocoder.geocode({ address: label + ', India', region: 'IN' }, function (results, status) {
      if (status !== 'OK' || !results || !results[0]) return;
      var g = results[0].geometry;
      if (g.viewport && autocomplete.setBounds) {
        autocomplete.setBounds(g.viewport);
      } else if (g.location && autocomplete.setBounds) {
        var circle = new google.maps.Circle({ center: g.location, radius: 45000 });
        autocomplete.setBounds(circle.getBounds());
      }
    });
  }

  function syncCityFromPlace(place, citySelect) {
    if (!citySelect || !place.address_components) return;
    var cityName = '';
    place.address_components.forEach(function (c) {
      if (c.types.indexOf('locality') >= 0) cityName = c.long_name;
      if (!cityName && c.types.indexOf('administrative_area_level_2') >= 0) cityName = c.long_name;
      if (!cityName && c.types.indexOf('administrative_area_level_1') >= 0) cityName = c.long_name;
    });
    if (!cityName) return;
    Array.prototype.forEach.call(citySelect.options, function (opt) {
      if (opt.value && opt.text.toLowerCase().indexOf(cityName.toLowerCase()) >= 0) {
        citySelect.value = opt.value;
        citySelect.dispatchEvent(new Event('change', { bubbles: true }));
      }
    });
  }

  function fillHiddenPlaceFields(place, locInput) {
    var root = getPanelRoot(locInput);
    var latEl;
    var lngEl;
    var locEl;
    var searchLat;
    var searchLng;
    if (root) {
      latEl = root.querySelector('.nb-input-latitude');
      lngEl = root.querySelector('.nb-input-longitude');
      locEl = root.querySelector('.nb-input-locality');
      searchLat = root.querySelector('.nb-search-lat');
      searchLng = root.querySelector('.nb-search-lng');
    } else {
      latEl = document.getElementById('input-latitude');
      lngEl = document.getElementById('input-longitude');
      locEl = document.getElementById('input-locality');
      searchLat = document.getElementById('search-lat');
      searchLng = document.getElementById('search-lng');
    }
    if (!place.geometry) return;
    var lat = place.geometry.location.lat();
    var lng = place.geometry.location.lng();
    if (latEl) latEl.value = lat;
    if (lngEl) lngEl.value = lng;
    if (locEl) locEl.value = place.formatted_address || '';
    if (searchLat) searchLat.value = lat;
    if (searchLng) searchLng.value = lng;
    var pid = document.getElementById('input-google-place-id');
    if (pid && place.place_id) {
      pid.value = place.place_id;
    }
  }

  function initOneLocationAutocomplete(loc) {
    if (!loc || loc.dataset.nbAcInit === '1') return;
    if (typeof google === 'undefined' || !google.maps || !google.maps.places) return;

    loc.dataset.nbAcInit = '1';

    var ac = new google.maps.places.Autocomplete(loc, {
      componentRestrictions: { country: 'in' },
      fields: ['formatted_address', 'geometry', 'address_components', 'name', 'place_id']
    });

    var citySelect = getCitySelectFor(loc);
    if (citySelect) {
      citySelect.addEventListener('change', function () {
        biasAutocompleteToCity(ac, citySelect);
      });
      if (citySelect.value) {
        biasAutocompleteToCity(ac, citySelect);
      }
    }

    ac.addListener('place_changed', function () {
      var place = ac.getPlace();
      if (!place.geometry) return;
      fillHiddenPlaceFields(place, loc);
      syncCityFromPlace(place, getCitySelectFor(loc));
    });

    document.addEventListener('nb-cities-loaded', function () {
      biasAutocompleteToCity(ac, getCitySelectFor(loc));
    });
  }

  function initLocationAutocomplete() {
    var list = document.querySelectorAll('.nb-location-autocomplete');
    if (list.length) {
      Array.prototype.forEach.call(list, initOneLocationAutocomplete);
      return;
    }
    var legacy = document.getElementById('location-search');
    if (legacy) {
      legacy.classList.add('nb-location-autocomplete');
      initOneLocationAutocomplete(legacy);
    }
  }

  function initPropertyDetailMap() {
    var mapEl = document.getElementById('property-map');
    if (!mapEl || typeof google === 'undefined' || !google.maps) return;
    var lat = parseFloat(mapEl.dataset.lat, 10);
    var lng = parseFloat(mapEl.dataset.lng, 10);
    if (isNaN(lat) || isNaN(lng)) return;
    var map = new google.maps.Map(mapEl, { center: { lat: lat, lng: lng }, zoom: 15 });
    new google.maps.Marker({ position: { lat: lat, lng: lng }, map: map, title: mapEl.dataset.title || '' });
  }

  window.initNbAutocomplete = function () {
    initLocationAutocomplete();
    initPropertyDetailMap();
  };
})();
