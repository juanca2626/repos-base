<template>
  <div class="generic-image-component">
    <div class="generic-image-component-container">
      <div class="generic-image-component-header">
        <span class="generic-image-component-title">Imágenes para el servicio</span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts"></script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .generic-image-component {
    display: flex;
    flex-direction: column;
    gap: 10px;
  }
</style>
