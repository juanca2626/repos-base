import { ref, computed, type Component } from 'vue';
import { storeToRefs } from 'pinia';
import { useRoute } from 'vue-router';
import { useConfigurationStore } from '@/modules/negotiations/products/configuration/stores/useConfigurationStore';
import { useNavigationStore } from '@/modules/negotiations/products/configuration/stores/useNavegationStore';
import { resolveDrawerComponent } from '@/modules/negotiations/products/configuration/drawers/resolvers/drawerComponentResolver';

export const useServiceConfigurationSidebar = () => {
  const route = useRoute();
  const configurationStore = useConfigurationStore();
  const navigationStore = useNavigationStore();

  const { getSectionsKeyActive, isSidebarCollapsed } = storeToRefs(navigationStore);

  const { toggleSection, setActiveSectionItem } = navigationStore;

  const {
    supplierSummary,
    productSupplierType,
    isServiceTypeTrain,
    isServiceTypeMultiDays,
    isServiceTypeGeneral,
  } = storeToRefs(configurationStore);

  const componentDrawer = computed<Component | undefined>(() => {
    return resolveDrawerComponent(productSupplierType.value, 'ADD');
  });

  // Drawer state
  const showCategoryDrawer = ref(false);
  const sections = ref<any[]>([]);

  const productSupplierId = computed(() => {
    return (route.params.id as string) || null;
  });

  const supplierOriginalId = computed(() => {
    return supplierSummary.value?.originalId || null;
  });

  const handleOpenCategoryDrawer = () => {
    showCategoryDrawer.value = true;
  };

  const toggleSidebar = () => {
    isSidebarCollapsed.value = !isSidebarCollapsed.value;
  };

  const setSidebarCollapsed = (value: boolean) => {
    isSidebarCollapsed.value = value;
  };

  return {
    isSidebarCollapsed,
    getSectionsKeyActive,
    sections,
    showCategoryDrawer,
    productSupplierId,
    supplierOriginalId,
    isServiceTypeTrain,
    isServiceTypeMultiDays,
    isServiceTypeGeneral,
    toggleSection,
    handleOpenCategoryDrawer,
    setActiveSectionItem,
    toggleSidebar,
    setSidebarCollapsed,

    componentDrawer,
  };
};
