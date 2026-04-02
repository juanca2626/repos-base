<template>
  <div class="progress-container">
    <span class="progress-title"> (0 de 3 completados) </span>
  </div>
</template>
<script setup lang="ts"></script>
<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .progress-container {
    padding: 4px 0px 16px 0px;

    .progress-title {
      font-size: 16px;
      font-weight: 400;
      color: $color-black-10;
    }
  }
</style>
