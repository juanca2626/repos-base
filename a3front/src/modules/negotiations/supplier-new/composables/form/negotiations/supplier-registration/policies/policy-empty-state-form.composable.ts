import { useSupplierGlobalComposable } from '@/modules/negotiations/supplier-new/composables/form/supplier-global.composable';

export function usePolicyEmptyStateFormComposable(emit: any) {
  const { handleShowFormSpecific } = useSupplierGlobalComposable();

  const handleOpenForm = (formSpecific: any) => {
    handleShowFormSpecific(formSpecific, true);
    emit('onAddInformation');
  };

  return {
    handleOpenForm,
  };
}
