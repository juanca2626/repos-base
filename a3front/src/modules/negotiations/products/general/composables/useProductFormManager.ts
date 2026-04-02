import { computed } from 'vue';
import { useProductStoreFacade } from '@/modules/negotiations/products/general/composables/useProductStoreFacade';
import { FormViewTypeEnum } from '@/modules/negotiations/products/general/enums/form-view-type.enum';
import { useSupplierAssignmentStoreFacade } from '@/modules/negotiations/products/general/composables/form/useSupplierAssignmentStoreFacade';

export function useProductFormManager() {
  const { productFormViewType, setProductFormViewType } = useProductStoreFacade();

  const { isSupplierAssignmentForm } = useSupplierAssignmentStoreFacade();

  const showEditableForm = computed(() => productFormViewType.value === FormViewTypeEnum.EDITABLE);

  const showSummaryForm = computed(() => productFormViewType.value === FormViewTypeEnum.SUMMARY);

  const openSummaryMode = () => {
    setProductFormViewType(FormViewTypeEnum.SUMMARY);
  };

  const openEditableMode = () => {
    setProductFormViewType(FormViewTypeEnum.EDITABLE);
  };

  const closeEditableMode = () => {
    openSummaryMode();
  };

  const closeSummaryMode = () => {
    openEditableMode();
  };

  return {
    showEditableForm,
    showSummaryForm,
    isSupplierAssignmentForm,
    openSummaryMode,
    openEditableMode,
    closeEditableMode,
    closeSummaryMode,
  };
}
