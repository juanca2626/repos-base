<template>
  <div class="content-card">
    <RangeGroup v-for="(range, index) in model.rangos" :key="index" :range="range" :index="index" />
  </div>
</template>

<script setup lang="ts">
  import RangeGroup from './RangeGroup.vue';

  interface Props {
    model: any;
  }

  defineProps<Props>();
</script>
