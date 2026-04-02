<template>
  <component :is="currentLayout">
    <router-view :key="$route.fullPath"></router-view>
  </component>
</template>

<script setup lang="ts">
  import { computed } from 'vue';
  import { useRoute } from 'vue-router';

  const route = useRoute();

  const currentLayout = computed(() => {
    return route.meta.layout;
  });
</script>

<style scoped lang="scss"></style>
