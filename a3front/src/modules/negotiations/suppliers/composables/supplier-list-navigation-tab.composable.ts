import { computed, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { navigationTabsByGroup } from '@/modules/negotiations/suppliers/constants/supplier-navigation-tabs';
import { useSelectedSupplierClassificationStore } from '@/modules/negotiations/supplier-new/store/negotiations/supplier-registration/selected-supplier-classification.store';

export const useSupplierListNavigationTab = () => {
  const route = useRoute();
  const router = useRouter();
  const selectedSupplierClassificationStore = useSelectedSupplierClassificationStore();

  const activeNavigationTab = ref<string | null>();

  const handleChangeTab = (key: string) => {
    router.push({ name: key });
  };

  const activeGroupTabs = computed(() => {
    const group = route.meta.supplierClassificationGroup as
      | keyof typeof navigationTabsByGroup
      | undefined;
    return group ? navigationTabsByGroup[group] : null;
  });

  // Helper para obtener el supplierClassificationId de la ruta actual o padres
  const getSupplierClassificationIdFromRoute = (): number | null => {
    // Primero intenta obtenerlo de la ruta actual
    if (route.meta.supplierClassificationId) {
      return route.meta.supplierClassificationId as number;
    }

    // Si no está en la ruta actual, busca en las rutas padres (matched)
    for (let i = route.matched.length - 1; i >= 0; i--) {
      const matchedRoute = route.matched[i];
      if (matchedRoute.meta?.supplierClassificationId) {
        return matchedRoute.meta.supplierClassificationId as number;
      }
    }

    return null;
  };

  // Actualizar el store cuando cambia la ruta
  watch(
    () => route.name,
    (newName) => {
      activeNavigationTab.value = typeof newName === 'string' ? newName : null;

      // Actualizar el supplierClassificationId en el store inmediatamente
      const classificationId = getSupplierClassificationIdFromRoute();
      if (classificationId !== null) {
        selectedSupplierClassificationStore.setSelectedClassificationId(classificationId);
      }
    },
    { immediate: true }
  );

  return {
    activeGroupTabs,
    activeNavigationTab,
    handleChangeTab,
  };
};
