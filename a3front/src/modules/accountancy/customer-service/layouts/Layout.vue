<template>
  <div class="module-content">
    <transition name="page-fade" mode="out-in">
      <router-view />
    </transition>
  </div>
</template>

<script setup>
  // Layout base para el módulo de Servicio al Cliente
</script>

<style scoped lang="scss">
  .customer-service-layout {
    display: flex;
    min-height: 100vh;
    background-color: #f4f7f9;
  }

  .module-content {
    flex: 1;
    width: 100%;
  }

  // Global page transitions
  .page-fade-enter-active,
  .page-fade-leave-active {
    transition:
      opacity 0.3s ease,
      transform 0.3s ease;
  }

  .page-fade-enter-from {
    opacity: 0;
    transform: translateY(10px);
  }

  .page-fade-leave-to {
    opacity: 0;
    transform: translateY(-10px);
  }
</style>
