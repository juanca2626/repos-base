<template>
  <transition name="fade">
    <slot />
  </transition>
</template>
<script setup lang="ts"></script>
<style scoped lang="scss">
  .fade-slide-enter-from,
  .fade-slide-leave-to {
    opacity: 0;
    transform: translateY(-4px) scale(0.95);
  }

  .fade-slide-enter-active,
  .fade-slide-leave-active {
    transition:
      opacity 0.3s ease-out,
      transform 0.3s ease-out;
  }
</style>
