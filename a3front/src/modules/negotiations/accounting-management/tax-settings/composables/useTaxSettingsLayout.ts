import { ref, provide, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import type { FilterDatesInterface } from '@/modules/negotiations/interfaces/filter-dates.interface';
import { useRouteUpdate } from '@/composables/useRouteUpdate';
import { usePermissionRedirect } from '@/composables/usePermissionRedirect';
import { ModulePermissionEnum } from '@/enums/module-permission.enum';
import { RouteNameEnum } from '@/enums/route-name.enum';

export const useTaxSettingsLayout = () => {
  const showDrawerLaw = ref<boolean>(false);
  const showDrawerAssignLawSupplier = ref<boolean>(false);
  const showDrawer = ref<boolean>(false);
  const route = useRoute();
  const router = useRouter();
  const activeTab = ref(route.name);
  const filters = ref<FilterDatesInterface>({
    from: '',
    to: '',
  });

  const { currentPath, currentRouteName, updateCurrentRoute } = useRouteUpdate();

  const { redirectOnPermissionCheck } = usePermissionRedirect();

  const isLoading = ref(false);
  provide('isLoading', isLoading);

  const handleChangeTab = (key: string) => {
    router.push({ name: key });
    updateCurrentRoute(router.currentRoute.value.fullPath, key);
  };

  const updateFilters = (newFilters: { from: string; to: string }) => {
    filters.value = newFilters;
  };

  const handlerShowDrawerLaw = (show: boolean) => {
    showDrawerLaw.value = show;
  };

  const handlerShowDrawer = (show: boolean) => {
    showDrawer.value = show;
  };

  const handlerShowDrawerAssignLawSupplier = (show: boolean) => {
    showDrawerAssignLawSupplier.value = show;
  };

  const redirectAllowedRoute = (newRouteName: string) => {
    if (newRouteName === RouteNameEnum.TAX) {
      redirectOnPermissionCheck([
        {
          routeName: RouteNameEnum.TAX_GENERAL,
          permission: ModulePermissionEnum.TAX_GENERAL,
        },
        {
          routeName: RouteNameEnum.TAX_SUPPLIERS,
          permission: ModulePermissionEnum.TAX_SUPPLIER_TYPE,
        },
      ]);
    }
  };

  watch(
    [currentPath, currentRouteName],
    ([newPath, newRouteName]) => {
      redirectAllowedRoute(newRouteName);
      console.info('newRouteName', newPath);
      activeTab.value = newRouteName;
    },
    { immediate: true }
  );

  const navigateToRouteList = () => {
    router.push({ name: RouteNameEnum.TAX_SUPPLIERS });
  };

  return {
    showDrawerLaw,
    showDrawer,
    showDrawerAssignLawSupplier,
    route,
    activeTab,
    handleChangeTab,
    handlerShowDrawerLaw,
    handlerShowDrawer,
    handlerShowDrawerAssignLawSupplier,
    updateFilters,
    navigateToRouteList,
  };
};
