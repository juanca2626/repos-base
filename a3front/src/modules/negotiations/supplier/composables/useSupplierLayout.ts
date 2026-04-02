import { storeToRefs } from 'pinia';
import { computed } from 'vue';
import { useSupplierLayoutStore } from '@/modules/negotiations/supplier/store/supplier-layout.store';
import { useSupplierFormStoreFacade } from '@/modules/negotiations/supplier/register/composables/useSupplierFormStoreFacade';
import { SupplierSubClassificationsEnum } from '@/utils/supplierSubClassifications.enum';

export function useSupplierLayout() {
  const { isFormEditMode, formStateNegotiation, isLoadingForm } = useSupplierFormStoreFacade();

  const supplierLayoutStore = useSupplierLayoutStore();

  const { supplierSubClassification } = storeToRefs(supplierLayoutStore);
  const { setSupplierSubClassification } = supplierLayoutStore;

  const headerTitle = computed(() => {
    if (!isFormEditMode.value) return '';

    const codes = [formStateNegotiation.cityCode, formStateNegotiation.supplierCode]
      .filter(Boolean)
      .join('');

    return codes || formStateNegotiation.businessName
      ? `${codes} - ${formStateNegotiation.businessName || ''}`
      : '';
  });

  const isTicketSubClassification = () => {
    return supplierSubClassification.value === SupplierSubClassificationsEnum.MUSEUMS;
  };

  return {
    isFormEditMode,
    supplierSubClassification,
    setSupplierSubClassification,
    headerTitle,
    isTicketSubClassification,
    isLoadingForm,
  };
}
