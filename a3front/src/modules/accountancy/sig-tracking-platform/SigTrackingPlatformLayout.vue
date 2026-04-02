<template>
  <div class="sig-tracking-layout">
    <div class="sidebar">
      <div class="title-sig">
        <h4>SEGUIMIENTO DE GESTIÓN {{ currentTitle }}</h4>
      </div>
    </div>
    <div class="content">
      <router-view />
    </div>
  </div>
</template>

<script setup>
  import { computed } from 'vue';
  import { useRoute } from 'vue-router';

  const route = useRoute();

  const currentTitle = computed(() => {
    // Obtener el breadcrumb de la ruta actual
    return route.meta.breadcrumb.toUpperCase() !== 'SEGUIMIENTO DE GESTIÓN'
      ? '- ' + route.meta.breadcrumb.toUpperCase()
      : '';
  });
</script>
<style scoped>
  .title-sig {
    padding-top: 49px;
    padding-left: 49px;
  }
</style>
