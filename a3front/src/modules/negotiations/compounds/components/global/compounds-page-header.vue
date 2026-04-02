<template>
  <div class="compounds-page-header">
    <span class="page-title">Estructura para compuestos</span>
    <CompoundsStepper />
  </div>
</template>

<script setup lang="ts">
  import CompoundsStepper from './compounds-stepper.vue';

  defineOptions({ name: 'CompoundsPageHeader' });
</script>

<style lang="scss" scoped>
  .compounds-page-header {
    margin-bottom: 24px;

    .page-title {
      font-size: 24px;
      font-weight: 600;
      color: #212121;
      line-height: 28px;
      font-family: 'Inter', sans-serif;
      display: block;
      margin-bottom: 16px;
    }
  }
</style>
