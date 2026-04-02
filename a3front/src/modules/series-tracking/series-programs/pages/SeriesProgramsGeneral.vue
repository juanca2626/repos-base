<template>
  <SeriesProgramsListComponent :filters="filters" />
</template>

<script setup lang="ts">
  import { ref } from 'vue';
  import type { FiltersInputsInterface } from '../interfaces/filters-inputs.interface';
  import SeriesProgramsListComponent from '../components/SeriesProgramsListComponent.vue';

  const filters = ref<FiltersInputsInterface>({
    name: '',
    status: null,
  });
</script>

<style scoped>
  .filters-spacing {
    margin-top: 24px;
  }
</style>
