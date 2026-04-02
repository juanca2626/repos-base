import { computed, ref, watch } from 'vue';
import { supplierClassificationStore } from '@/modules/negotiations/accounting-management/tax-settings/suppliers/store/supplierClassification.store';
import { storeToRefs } from 'pinia';
import { useRoute } from 'vue-router';
import { useSupplierTaxAssignFilterStore } from '@/modules/negotiations/accounting-management/tax-settings/suppliers/store/supplierTaxAssignFilter.store';

export const useSupplierSubScrollableTab = () => {
  const supplierStore = supplierClassificationStore();
  const supplierTaxAssignFilter = useSupplierTaxAssignFilterStore();

  const { data, isLoading, activeKey, nameClassification } = storeToRefs(supplierStore);
  const activeTab = ref<number | string>('');
  const route = useRoute();
  const id = route.params.id as string;
  supplierTaxAssignFilter.setTaxesSupplierClassificationId(+id);

  const subClassification = computed(() => {
    const foundClassification = data.value.find(
      (classification) =>
        classification.id === activeKey.value ||
        classification.sub_classifications.some((sub) => sub.id === activeKey.value)
    );
    return foundClassification ? foundClassification.sub_classifications : [];
  });

  watch(
    subClassification,
    (newSubClassification) => {
      if (
        newSubClassification.length > 0 &&
        !newSubClassification.some((sub) => sub.id === activeTab.value)
      ) {
        activeTab.value = newSubClassification[0].id;
        supplierTaxAssignFilter.setSupplierSubClassificationId(newSubClassification[0].id);
      }
    },
    { immediate: true }
  );

  // watch(activeTab, (newActiveTab) => {
  //   console.log('ActiveTab changed watch:', newActiveTab);
  //   emit('updateFilters', {
  //     supplier_sub_classification_id: newActiveTab,
  //     taxes_supplier_classification_id: id,
  //     name: null,
  //   });
  // });

  return {
    nameClassification,
    subClassification,
    isLoading,
    activeTab,
  };
};
