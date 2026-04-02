import { useRoute, useRouter } from 'vue-router';
import { computed } from 'vue';
import { supplierInfo } from '@/modules/negotiations/suppliers/constants/supplier-info';
import { useSelectedSupplierClassificationComposable } from '@/modules/negotiations/supplier-new/composables/form/negotiations/supplier-registration/selected-supplier-classification.composable';
import { useSupplierGlobalComposable } from '../../supplier-new/composables/form/supplier-global.composable';

export function useSupplier() {
  const { selectedClassificationId, setSelectedClassificationId } =
    useSelectedSupplierClassificationComposable();

  const { resetAllComponents } = useSupplierGlobalComposable();

  const route = useRoute();
  const router = useRouter();

  const initSupplierSubClassification = () => {
    setSelectedClassificationId((route.meta.supplierClassificationId as string) ?? null);
  };

  const handleNewSupplier = async () => {
    resetAllComponents();
    setSelectedClassificationId(route.meta.supplierClassificationId as string);
    router.push({ name: 'supplier-register-form' });
  };

  const listTitle = computed(() => {
    if (selectedClassificationId.value) {
      return supplierInfo[selectedClassificationId.value]?.listTitle;
    }

    return '';
  });

  return {
    selectedClassificationId,
    supplierInfo,
    listTitle,
    initSupplierSubClassification,
    handleNewSupplier,
  };
}
