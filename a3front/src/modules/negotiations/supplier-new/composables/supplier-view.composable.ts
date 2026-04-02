import { computed } from 'vue';
import { useSupplierGlobalStoreFacade } from '@/modules/negotiations/supplier-new/composables/supplier-global-store-facade.composable';
import { SupplierViewTypeEnum } from '@/modules/negotiations/supplier-new/enums/supplier-view-type.enum';

export function useSupplierViewComposable() {
  const { supplierViewType, setSupplierViewType } = useSupplierGlobalStoreFacade();

  const showSupplierForm = computed(
    () => supplierViewType.value === SupplierViewTypeEnum.SUPPLIER_FORM
  );
  const showPolicyManager = computed(
    () => supplierViewType.value === SupplierViewTypeEnum.POLICY_MANAGER
  );

  const openPolicyManager = () => {
    setSupplierViewType(SupplierViewTypeEnum.POLICY_MANAGER);
  };

  const closePolicyManager = () => {
    openSupplierForm();
  };

  const openSupplierForm = () => {
    setSupplierViewType(SupplierViewTypeEnum.SUPPLIER_FORM);
  };

  return {
    showSupplierForm,
    showPolicyManager,
    openPolicyManager,
    closePolicyManager,
    openSupplierForm,
  };
}
