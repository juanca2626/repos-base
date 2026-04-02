<template>
  <div class="step2-container">
    <BuscarServicios />
    <ServiciosCompuestos />
  </div>
</template>

<script setup lang="ts">
  import BuscarServicios from './buscar-servicios.vue';
  import ServiciosCompuestos from './servicios-compuestos.vue';

  defineOptions({ name: 'Step2Container' });
</script>

<style lang="scss" scoped>
  .step2-container {
    display: flex;
    flex-direction: column;
    gap: 16px;
    width: 100%;
  }
</style>
