<template>
  <CalendarDetailViewComponent
    :country-name="countryName"
    :year-from="yearFrom"
    :year-to="yearTo"
  />
</template>

<script lang="ts" setup>
  import { ref, onMounted } from 'vue';
  import { useRoute } from 'vue-router';
  import CalendarDetailViewComponent from '../components/CalendarDetailViewComponent.vue';

  const route = useRoute();

  // These values would come from the route params or a store
  const countryName = ref('Perú');
  const yearFrom = ref(2023);
  const yearTo = ref(2024);

  onMounted(() => {
    // Get data from route query if available
    if (route.query.country) {
      countryName.value = route.query.country as string;
    }
    if (route.query.yearFrom) {
      yearFrom.value = Number(route.query.yearFrom);
    }
    if (route.query.yearTo) {
      yearTo.value = Number(route.query.yearTo);
    }
  });
</script>
