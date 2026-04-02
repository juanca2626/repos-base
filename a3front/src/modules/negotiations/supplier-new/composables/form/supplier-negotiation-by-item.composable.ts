import { computed } from 'vue';
import { useSupplierClassificationStoreFacade } from '@/modules/negotiations/supplier-new/composables/form/negotiations/supplier-registration/supplier-classification-store-facade.composable';

export function useSupplierNegotiationByItem() {
  const { supplierClassificationId } = useSupplierClassificationStoreFacade();

  const hiddenMapClassifications: SupplierClassificationEnum[] = ['ACU', 'STA'];

  const showMapSection = computed(() => {
    const classificationId = supplierClassificationId.value;

    if (!classificationId) return true;

    return !hiddenMapClassifications.includes(classificationId);
  });

  return {
    showMapSection,
  };
}
