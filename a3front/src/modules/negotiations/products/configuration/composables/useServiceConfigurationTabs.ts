import { computed, ref, type Component } from 'vue';
import { useRoute } from 'vue-router';
import { storeToRefs } from 'pinia';
import { useConfigurationStore } from '@/modules/negotiations/products/configuration/stores/useConfigurationStore';
import { useNavigationStore } from '@/modules/negotiations/products/configuration/stores/useNavegationStore';
import { resolveDrawerComponent } from '@/modules/negotiations/products/configuration/drawers/resolvers/drawerComponentResolver';

export const useServiceConfigurationTabs = () => {
  const route = useRoute();

  const navigationStore = useNavigationStore();
  const configurationStore = useConfigurationStore();

  const { tabs } = storeToRefs(navigationStore);

  const {
    supplierSummary,
    productSupplierType,
    isServiceTypeMultiDays,
    isServiceTypeTrain,
    isServiceTypeGeneral,
  } = storeToRefs(configurationStore);

  // Drawer state
  const showDrawerForm = ref(false);

  const componentDrawer = computed<Component | undefined>(() => {
    return resolveDrawerComponent(productSupplierType.value, 'EDIT');
  });

  const productSupplierId = computed(() => {
    return (route.params.id as string) || null;
  });

  const supplierOriginalId = computed(() => {
    return supplierSummary.value?.originalId || null;
  });

  const handleOpenDrawer = () => {
    showDrawerForm.value = true;
  };

  const handleTabChange = (tabKey: string) => {
    navigationStore.setActiveTabKey(tabKey);
  };

  return {
    tabs,
    isServiceTypeMultiDays,
    isServiceTypeTrain,
    isServiceTypeGeneral,
    handleTabChange,
    showDrawerForm,
    productSupplierId,
    supplierOriginalId,
    handleOpenDrawer,

    componentDrawer,
  };
};
