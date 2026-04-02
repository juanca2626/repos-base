<template>
  <button class="button-component btn-md" :class="type" :disabled="disabled">
    <slot></slot>
  </button>
</template>
<script setup>
  // eslint-disable-next-line no-unused-vars
  defineProps({
    type: String,
    disabled: Boolean,
  });
</script>
