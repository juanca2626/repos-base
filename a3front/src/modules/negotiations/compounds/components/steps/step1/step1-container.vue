<template>
  <div class="step1-container">
    <DatosSection />
    <ConfiguracionSection />
  </div>
</template>

<script setup lang="ts">
  import DatosSection from './datos-section.vue';
  import ConfiguracionSection from './configuracion-section.vue';

  defineOptions({ name: 'Step1Container' });
</script>

<style lang="scss" scoped>
  .step1-container {
    display: flex;
    flex-direction: column;
    gap: 16px;
    width: 100%;
  }
</style>
