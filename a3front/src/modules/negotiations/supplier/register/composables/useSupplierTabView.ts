import { useSupplierTabViewStore } from '@/modules/negotiations/supplier/register/store/supplierTabViewStore';
import { computed, onMounted } from 'vue';
import TabsConfigServices from '@/modules/negotiations/supplier/register/components/ConfigurationModule/TabsConfigServices.vue';
import SupplierFormTreasuryComponent from '@/modules/negotiations/supplier/register/components/SupplierFormTreasuryComponent.vue';
import SupplierFormAccountingComponent from '@/modules/negotiations/supplier/register/components/ConfigurationAccounting/SupplierFormAccountingComponent.vue';
import { storeToRefs } from 'pinia';

export function useSupplierTabView() {
  const supplierTabViewStore = useSupplierTabViewStore();

  const { tabOptionCollaborator } = storeToRefs(supplierTabViewStore);
  const { setTabOptionCollaborator } = supplierTabViewStore;

  const handleSetTabOptionCollaborator = (item: any) => {
    setTabOptionCollaborator(item);
  };

  const renderTabOptionCollaborator = computed(() => {
    const component: Record<'negotiations' | 'treasury' | 'accounting', any> = {
      negotiations: TabsConfigServices,
      treasury: SupplierFormTreasuryComponent,
      accounting: SupplierFormAccountingComponent,
    };
    return component[tabOptionCollaborator.value as 'negotiations' | 'treasury' | 'accounting'];
  });

  onMounted(() => {
    setTabOptionCollaborator('negotiations');
  });

  return {
    tabOptionCollaborator,
    setTabOptionCollaborator,
    handleSetTabOptionCollaborator,
    renderTabOptionCollaborator,
  };
}
