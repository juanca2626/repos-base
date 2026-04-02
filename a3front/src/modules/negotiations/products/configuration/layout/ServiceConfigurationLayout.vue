<template>
  <div class="module-negotiations">
    <component :is="currentLayout">
      <router-view></router-view>
    </component>
  </div>
</template>

<script setup lang="ts">
  import { computed } from 'vue';
  import { useRoute } from 'vue-router';

  const route = useRoute();

  const currentLayout = computed(() => {
    return route.meta.layout || 'div';
  });
</script>
