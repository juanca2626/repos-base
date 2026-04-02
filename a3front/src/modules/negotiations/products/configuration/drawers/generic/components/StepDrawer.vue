<template>
  <ConfigurationDrawerSteps :step-number="stepNumber" />
</template>

<script setup lang="ts">
  import ConfigurationDrawerSteps from './ConfigurationDrawerSteps.vue';

  defineProps<{
    stepNumber: number;
  }>();
</script>
