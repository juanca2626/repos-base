<template>
  <div class="base-indicator-circle" :class="`base-indicator-circle--${type}`"></div>
</template>

<script setup>
  defineProps({
    type: {
      type: String,
      default: 'default',
      validator(typeValue) {
        return ['default', 'success', 'error'].includes(typeValue);
      },
    },
  });
</script>

<style scoped lang="scss">
  .base-indicator-circle {
    width: 8px;
    height: 8px;
    background-color: #fff;
    border: 2px solid transparent;
    border-radius: 100px;
    margin-right: 4px;

    &--default {
      // background-color: var(--files-default-dark);
      border-color: var(--files-default-dark);
      color: var(--files-default-dark);
    }
    &--success {
      // background-color: var(--files-success-1);
      border-color: var(--files-success-1);
      color: var(--files-success-1);
    }
    &--error {
      // background-color: var(--files-main-color);
      border-color: var(--files-main-color);
      color: var(--files-main-color);
    }
  }
</style>
