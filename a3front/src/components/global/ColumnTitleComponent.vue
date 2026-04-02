<template>
  <div>
    <div>{{ title }}</div>
    <div style="font-size: 12px; color: #e4e5e6">{{ subtitle }}</div>
  </div>
</template>

<script setup lang="ts">
  defineProps({
    title: String,
    subtitle: String,
  });
</script>
