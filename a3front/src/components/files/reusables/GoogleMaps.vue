<template>
  <a-card class="map-container">
    <div ref="mapRef" class="map"></div>
  </a-card>
</template>

<script setup lang="ts">
  import { onMounted, ref } from 'vue';
  import { Loader } from '@googlemaps/js-api-loader';

  const API_KEY = import.meta.env.VITE_GOOGLE_MAPS_API_KEY;

  // Props
  const props = defineProps({
    routes: {
      type: Array as () => Array<{
        order: number;
        pickup_point: {
          location: { coordinates: [number, number, number?] };
          fullname: string;
          properties: {
            formatted_address: string;
          };
          type: string;
        };
      }>,
      default: () => [],
    },
    defaultLocation: {
      type: Object as () => {
        lat: number;
        lng: number;
        address?: string;
      },
      required: false,
    },
  });

  // Emits
  const emit = defineEmits(['place-selected']);

  // Referencias
  const mapRef = ref<HTMLDivElement | null>(null);
  const map = ref<google.maps.Map | null>(null);
  const markers = ref<google.maps.Marker[]>([]);
  const placesService = ref<google.maps.places.PlacesService | null>(null);

  // Función para añadir marcador
  const addMarker = (location: { lat: number; lng: number }, title?: string) => {
    if (!map.value) return;

    const marker = new google.maps.Marker({
      position: location,
      map: map.value,
      title: title || 'Ubicación seleccionada',
      icon: {
        url: 'https://maps.google.com/mapfiles/ms/icons/red-dot.png',
        scaledSize: new google.maps.Size(40, 40),
      },
    });

    markers.value.push(marker);
    return marker;
  };

  // Función para limpiar marcadores (manteniendo tu implementación original)
  const clearMarkers = () => {
    markers.value.forEach((marker) => {
      marker.setMap(null);
      google.maps.event.clearInstanceListeners(marker);
      try {
        marker['setVisible'](false);
        marker['unbindAll']();
      } catch (e) {
        console.warn('Error al limpiar marcador:', e);
      }
    });
    markers.value = [];
  };

  // Función para obtener componentes de dirección
  const getAddressComponent = (components: any[] | undefined, type: string): string | null => {
    if (!components || !Array.isArray(components)) {
      console.warn('Componentes de dirección no válidos:', components);
      return null;
    }
    const component = components.find((c) => {
      return c?.types && Array.isArray(c.types) && c.types.includes(type);
    });
    return component?.long_name || null;
  };
  // Función para buscar lugares (expuesta al padre)
  const searchPlace = async (query: string) => {
    if (!query.trim() || !placesService.value || !map.value) return;

    const geocoder = new google.maps.Geocoder();

    return new Promise<void>((resolve) => {
      geocoder.geocode({ address: query }, (results, status) => {
        if (status === 'OK' && results?.[0]) {
          const place = results[0];
          const location = {
            lat: place.geometry.location.lat(),
            lng: place.geometry.location.lng(),
          };

          map.value?.panTo(location);
          map.value?.setZoom(15);
          clearMarkers();
          addMarker(location, place.formatted_address);

          const city = getCityFromAddressComponents(place.address_components);

          emit('place-selected', {
            location: { coordinates: [location.lng, location.lat] },
            fullname: place.formatted_address || 'Lugar seleccionado',
            properties: {
              formatted_address: place.formatted_address,
              city: city || extractCityFromAddress(place.formatted_address),
              place: place,
            },
            type: 'custom',
          });
        }
        resolve();
      });
    });
  };

  const extractCityFromAddress = (address: string): string | null => {
    if (!address) return null;

    // Expresión regular para encontrar ciudad en dirección típica
    const cityMatch = address.match(/,\s*([^,]+),\s*\w{2}\s*\d+/); // Ej: "..., Lima, PE 15001"
    if (cityMatch && cityMatch[1]) {
      return cityMatch[1].trim();
    }

    return null;
  };

  const getCityFromAddressComponents = (
    components: google.maps.GeocoderAddressComponent[] | undefined
  ): string | null => {
    if (!components || !Array.isArray(components)) {
      console.warn('Componentes de dirección no válidos');
      return null;
    }

    // Orden de prioridad para buscar la ciudad
    const cityTypes = [
      'administrative_area_level_2', // Provincia/Departamento
      'locality', // Ciudad principal (ej. "Lima")
      'sublocality_level_1', // Distrito principal (ej. "Miraflores")
      'administrative_area_level_1', // Región/Estado (ej. "Lima Metropolitan")
      'neighborhood', // Barrio
      'postal_town', // Ciudad postal (alternativa)
    ];

    // Buscar en los componentes por orden de prioridad
    for (const type of cityTypes) {
      const component = components.find(
        (c) => c?.types && Array.isArray(c.types) && c.types.includes(type)
      );
      if (component) {
        return component.long_name;
      }
    }

    // Si no se encontró ningún componente de ciudad
    console.warn('No se encontró componente de ciudad en:', components);
    return null;
  };
  // Inicialización del mapa
  onMounted(async () => {
    const loader = new Loader({
      apiKey: API_KEY,
      version: 'weekly',
      language: 'es',
      libraries: ['places', 'geocoding', 'marker'],
    });

    await loader.load();

    if (!mapRef.value || !window.google?.maps) {
      console.error('Google Maps no se ha cargado correctamente.');
      return;
    }

    // Establecer centro inicial
    const center = props.defaultLocation
      ? { lat: props.defaultLocation.lat, lng: props.defaultLocation.lng }
      : props.routes.length
        ? {
            lat: props.routes[0].pickup_point.location.coordinates[1],
            lng: props.routes[0].pickup_point.location.coordinates[0],
          }
        : { lat: 0, lng: 0 };

    // Crear instancia del mapa
    map.value = new google.maps.Map(mapRef.value, {
      disableDefaultUI: false,
      center,
      zoom: 15,
      zoomControl: true,
      zoomControlOptions: {
        position: google.maps.ControlPosition.RIGHT_TOP,
      },
      fullscreenControl: false,
      streetViewControl: true,
      streetViewControlOptions: {
        position: google.maps.ControlPosition.RIGHT_TOP,
      },
      mapTypeControl: true,
    });

    // Inicializar servicio de Places
    placesService.value = new google.maps.places.PlacesService(map.value);

    // Configurar evento de clic en el mapa
    map.value.addListener('click', (event: google.maps.MapMouseEvent) => {
      if (!event.latLng) return;

      clearMarkers();
      const selectedLocation = {
        lat: event.latLng.lat(),
        lng: event.latLng.lng(),
      };

      // Añadir marcador en la ubicación seleccionada
      addMarker(selectedLocation);

      // Geocodificación inversa
      new google.maps.Geocoder().geocode({ location: selectedLocation }, (results, status) => {
        if (status === 'OK' && results?.[0]) {
          emit('place-selected', {
            location: { coordinates: [selectedLocation.lng, selectedLocation.lat] },
            fullname: 'Lugar seleccionado',
            properties: {
              formatted_address: results[0].formatted_address,
              city: getAddressComponent(
                results[0].address_components,
                'administrative_area_level_2'
              ),
            },
            type: 'custom',
          });
        }
      });
    });
  });

  const updateMapCenter = (location: { lat: string; lng: string; address: string | undefined }) => {
    if (!map.value) return;

    const newCenter = {
      lat: parseFloat(location.lat),
      lng: parseFloat(location.lng),
    };

    // Validar coordenadas
    if (isNaN(newCenter.lat) || isNaN(newCenter.lng)) {
      console.error('Coordenadas inválidas:', location);
      return;
    }

    // Mover el mapa
    map.value.panTo(newCenter);
    map.value.setZoom(16);

    // Limpiar y agregar marcador
    clearMarkers();
    addMarker(newCenter, location.address);
    console.log('aqui agregando mapa');
  };

  // Exponer métodos al componente padre
  defineExpose({
    searchPlace,
    updateMapCenter,
  });
</script>

<style scoped>
  .map-container {
    width: 100%;
    margin: auto;
    background-color: #ffffff;
    position: relative;
  }

  .map {
    width: 100%;
    height: 440px;
    border-radius: 8px;
  }

  /* Estilos para el popup de información */
  .custom-popup {
    font-family: 'Arial', sans-serif;
    max-width: 200px;
  }

  .custom-popup h3 {
    margin: 0;
    font-size: 16px;
    color: #1890ff;
  }

  .custom-popup p {
    font-size: 14px;
    margin: 5px 0;
  }
</style>
