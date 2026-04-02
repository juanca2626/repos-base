<template>
  <div class="main-container">
    <ProductFormSidebar />
    <ProductFormManagerComponent />
  </div>
</template>
<script lang="ts" setup>
  import ProductFormSidebar from '@/modules/negotiations/products/general/components/ProductFormSidebar.vue';
  import ProductFormManagerComponent from '@/modules/negotiations/products/general/components/ProductFormManagerComponent.vue';
</script>
<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .main-container {
    display: grid;
    grid-template-columns: 22.5% 77.5%;
  }
</style>
