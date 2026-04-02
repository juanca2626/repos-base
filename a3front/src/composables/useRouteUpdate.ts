// composables/useRouteUpdate.ts
import { ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import { useRouteStore } from '@/stores/global/routeStore';

export function useRouteUpdate() {
  const route = useRoute();
  const routeStore = useRouteStore();

  const currentPath = ref(route.fullPath);
  const currentRouteName = ref(route.name as string);

  const updateCurrentRoute = (path: string, name: string) => {
    currentPath.value = path;
    currentRouteName.value = name;
    routeStore.updateCurrentRoute(path, name);
  };

  watch(
    () => route.fullPath,
    (newPath) => {
      updateCurrentRoute(newPath, route.name as string);
    },
    { immediate: true }
  );

  return { currentPath, currentRouteName, updateCurrentRoute };
}
