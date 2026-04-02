<template>
  <div class="base-card">
    <slot />
  </div>
</template>

<script setup></script>

<style scoped>
  .base-card {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;

    border-radius: 6px;

    transition-duration: 0.3s;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  }
</style>
