// stores/routeStore.ts
import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useRouteStore = defineStore('route', () => {
  const currentPath = ref('');
  const currentRouteName = ref('');

  function updateCurrentRoute(path: string, name: string) {
    currentPath.value = path;
    currentRouteName.value = name;
  }

  return { currentPath, currentRouteName, updateCurrentRoute };
});
