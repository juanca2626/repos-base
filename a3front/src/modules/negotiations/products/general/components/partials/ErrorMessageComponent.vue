<template>
  <div class="error-container">
    <font-awesome-icon :icon="['fa', 'circle-info']" class="error-icon" />
    <span class="error-message">
      {{ errorMessage }}
    </span>
  </div>
</template>
<script setup lang="ts">
  defineProps({
    errorMessage: {
      type: String,
      required: true,
    },
  });
</script>
<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .error-container {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    height: 32px;
    border-radius: 4px;
    padding: 6px 8px;
    background-color: $color-error-ultra-light;

    .error-icon {
      color: $color-error-strong;
    }

    .error-message {
      color: $color-error-strong;
      font-weight: 400;
      font-size: 14px;
    }
  }
</style>
