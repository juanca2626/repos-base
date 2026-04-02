<template>
  <div class="empty-placeholder">
    <font-awesome-icon icon="fa-solid fa-circle-info" />
    <span class="text">
      {{ message }}
    </span>
  </div>
</template>
<script setup lang="ts">
  defineProps({
    message: {
      required: false,
      type: String,
      default: 'No tiene datos registrados',
    },
  });
</script>
<style scoped lang="scss">
  .empty-placeholder {
    border: 1px solid #d1d5db;
    padding: 12px;
    border-radius: 8px;
    color: #95999b;
    font-size: 13px;

    .text {
      margin-left: 4px;
    }
  }
</style>
