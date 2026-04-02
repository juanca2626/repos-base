<template>
  <div>
    <span class="header-title">{{ title }}</span>
  </div>
</template>

<script setup lang="ts">
  defineProps<{
    title: string;
  }>();
</script>

<style scoped lang="scss">
  .header-title {
    font-size: 18px;
    font-weight: 600;
    color: #1a1a1a;
  }
</style>
