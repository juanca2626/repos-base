<template>
  <a-card class="map-container">
    <!-- <pre>
        {{ routes }}
    </pre> -->
    <div ref="mapRef" class="map"></div>
  </a-card>
</template>

<script setup lang="ts">
  import { onMounted, ref, defineProps } from 'vue';
  import { Loader } from '@googlemaps/js-api-loader';

  // 🔹 API Key de Google Maps (Reemplázala con la tuya)
  const API_KEY = 'AIzaSyAnQ9faN-VhBWrcMG2gswmU4NB7VOus9zQ';

  // 🔹 Recibe las rutas como `props`
  const props = defineProps<{
    routes: Array<{
      order: number;
      pickup_point: {
        location: { coordinates: [number, number, number?] };
        fullname: string;
        properties: {
          formatted_address: string;
        };
        type: string;
      };
    }>;
  }>();

  // 🔹 Referencia al mapa
  const mapRef = ref<HTMLDivElement | null>(null);

  const getCenterPoint = () => {
    if (!props.routes.length) return { lat: 0, lng: 0 };

    const totalPoints = props.routes.length;
    const sumCoords = props.routes.reduce(
      (acc, route) => {
        const [lng, lat] = route.pickup_point.location.coordinates;
        acc.lat += lat;
        acc.lng += lng;
        return acc;
      },
      { lat: 0, lng: 0 }
    );

    return {
      lat: sumCoords.lat / totalPoints,
      lng: sumCoords.lng / totalPoints,
    };
  };

  onMounted(async () => {
    if (!mapRef.value) return;

    const loader = new Loader({
      apiKey: API_KEY,
      version: 'weekly',
    });

    await loader.load();

    if (!window.google || !window.google.maps) {
      console.error('Google Maps no se ha cargado correctamente.');
      return;
    }

    // 🔹 Obtener la ubicación central (el primer punto)
    // const center = props.routes.length
    //   ? {
    //       lat: props.routes[0].pickup_point.location.coordinates[1],
    //       lng: props.routes[0].pickup_point.location.coordinates[0],
    //     }
    //   : { lat: 0, lng: 0 };

    // 🔹 Crear el mapa
    const center = getCenterPoint();
    const map = new window.google.maps.Map(mapRef.value, {
      center,
      zoom: 12,
    });

    // 🔹 Agregar marcadores con popups
    props.routes.forEach((route) => {
      const { coordinates } = route.pickup_point.location;
      const position = { lat: coordinates[1], lng: coordinates[0] };

      const marker = new window.google.maps.Marker({
        position,
        map,
        title: route.pickup_point.fullname,
        label: route.order.toString(),
      });

      // 🔹 Crear el contenido del popup con Ant Design Vue
      const infoWindow = new window.google.maps.InfoWindow({
        content: `
        <div class="custom-popup">
          <h5 style="margin:0;padding:0;width:150px;"><strong>${route.pickup_point.fullname}</strong></h5>
          <p style="margin:0;padding:0;">${route.pickup_point.properties.formatted_address}</p>
        </div>
      `,
      });

      // 🔹 Mostrar el popup al hacer clic en el marcador
      marker.addListener('click', () => {
        infoWindow.open(map, marker);
      });
    });
  });
</script>

<style scoped>
  .map-container {
    width: 100%;
    margin: auto;
    background-color: #ffffff;
  }

  .map {
    width: 100%;
    height: 440px;
  }

  /* 🔹 Estilos personalizados para el popup */
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
    /* margin: 5px 0; */
    font-size: 14px;
  }
</style>
