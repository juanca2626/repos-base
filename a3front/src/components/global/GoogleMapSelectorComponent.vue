<template>
  <div class="google-map-selector">
    <GoogleMap
      ref="mapRef"
      :api-key="apiKey"
      :libraries="libraries"
      :style="mapStyle"
      :center="mapCenter"
      :zoom="zoom"
      :map-id="mapId"
      @click="handleMapClick"
      @idle="applyMapOptions"
    >
      <AdvancedMarker :options="{ position: mapCenter }" />
    </GoogleMap>
  </div>
</template>

<script setup lang="ts">
  import { ref, onMounted, watch } from 'vue';
  import { GoogleMap, AdvancedMarker } from 'vue3-google-map';
  import type { PropType } from 'vue';
  import type { Libraries } from '@googlemaps/js-api-loader';

  interface LatLngLiteral {
    lat: number;
    lng: number;
  }

  interface GoogleMapRef {
    map: google.maps.Map;
  }

  defineOptions({
    name: 'GoogleMapSelectorComponent',
  });

  const props = defineProps({
    apiKey: {
      type: String,
      default: 'AIzaSyAnQ9faN-VhBWrcMG2gswmU4NB7VOus9zQ',
    },
    libraries: {
      type: Array as PropType<Libraries>,
      default: () => ['places', 'marker'] as Libraries,
    },
    mapId: {
      type: String,
      default: '8f6623e88b1fa095',
    },
    initialCenter: {
      type: Object as PropType<LatLngLiteral | null>,
      default: null,
    },
    zoom: {
      type: Number,
      default: 12,
    },
    mapStyle: {
      type: String,
      default: 'width: 100%; height: 100%',
    },
  });

  const emit = defineEmits(['update:location', 'location-changed']);

  // Centro del mapa - usa initialCenter si existe, sino usa coordenadas neutras (0,0)
  const mapCenter = ref<LatLngLiteral>(props.initialCenter || { lat: 0, lng: 0 });
  const addressName = ref('');
  const mapRef = ref<GoogleMapRef | null>(null);

  const getAddressFromCoordinates = async (lat: number, lng: number) => {
    try {
      addressName.value = 'Obteniendo dirección...';
      const response = await fetch(
        `https://maps.googleapis.com/maps/api/geocode/json?latlng=${lat},${lng}&key=${props.apiKey}`
      );

      const data = await response.json();

      if (data.status === 'OK' && data.results && data.results.length > 0) {
        addressName.value = data.results[0].formatted_address;
        emitLocationUpdate();
      } else {
        addressName.value = 'Dirección no encontrada';
        console.error('Geocoding error:', data.status);
      }
    } catch (error) {
      addressName.value = 'Error al obtener la dirección';
      console.error('Error getting address:', error);
    }
  };

  const getCoordinatesFromAddress = async (
    address: string
  ): Promise<{ lat: number; lng: number } | null> => {
    try {
      const response = await fetch(
        `https://maps.googleapis.com/maps/api/geocode/json?address=${encodeURIComponent(address)}&key=${props.apiKey}`
      );

      const data = await response.json();

      if (data.status === 'OK' && data.results && data.results.length > 0) {
        const location = data.results[0].geometry.location;
        return {
          lat: location.lat,
          lng: location.lng,
        };
      } else {
        console.error('Geocoding error:', data.status);
        return null;
      }
    } catch (error) {
      console.error('Error getting coordinates:', error);
      return null;
    }
  };

  const handleMapClick = (event: google.maps.MapMouseEvent) => {
    if (event.latLng) {
      const lat = event.latLng.lat();
      const lng = event.latLng.lng();

      mapCenter.value = { lat, lng };
      getAddressFromCoordinates(lat, lng);
    }
  };

  const emitLocationUpdate = () => {
    const locationData = {
      lat: mapCenter.value.lat,
      lng: mapCenter.value.lng,
      address: addressName.value,
    };

    emit('update:location', locationData);
    emit('location-changed', locationData);
  };

  const applyMapOptions = () => {
    if (mapRef.value && mapRef.value.map) {
      const map = mapRef.value.map;

      map.setOptions({
        mapTypeControl: false,
        streetViewControl: false,
        fullscreenControl: false,
        zoomControl: true,
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        gestureHandling: 'cooperative',
        draggableCursor: 'crosshair',
        draggingCursor: 'grabbing',
        disableDefaultUI: true,
        scaleControl: false,
        rotateControl: false,
      });
    }
  };

  onMounted(() => {
    getAddressFromCoordinates(mapCenter.value.lat, mapCenter.value.lng);
    // Asegurar que las opciones se apliquen cuando el mapa esté completamente cargado
    setTimeout(applyMapOptions, 500);
  });

  watch(
    () => props.initialCenter,
    (newValue) => {
      if (
        newValue &&
        (newValue.lat !== mapCenter.value.lat || newValue.lng !== mapCenter.value.lng)
      ) {
        mapCenter.value = { ...newValue };
        getAddressFromCoordinates(mapCenter.value.lat, mapCenter.value.lng);
      }
    },
    { deep: true }
  );

  defineExpose({
    getLocation: () => ({
      lat: mapCenter.value.lat,
      lng: mapCenter.value.lng,
      address: addressName.value,
    }),
    updateLocation: (lat: number, lng: number) => {
      mapCenter.value = { lat, lng };
      getAddressFromCoordinates(lat, lng);
    },
    searchAddress: async (address: string) => {
      const coordinates = await getCoordinatesFromAddress(address);
      if (coordinates) {
        mapCenter.value = coordinates;
        await getAddressFromCoordinates(coordinates.lat, coordinates.lng);
        return true;
      }
      return false;
    },
  });
</script>

<style lang="scss" scoped>
  .google-map-selector {
    width: 422px;
    height: 210px;
  }
</style>
