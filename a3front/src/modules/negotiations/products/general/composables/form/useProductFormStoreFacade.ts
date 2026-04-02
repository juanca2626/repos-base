import { storeToRefs } from 'pinia';
import { useProductFormStore } from '@/modules/negotiations/products/general/store/useProductFormStore';

export function useProductFormStoreFacade() {
  const productFormStore = useProductFormStore();

  const {
    formState,
    setIsEditMode,
    setFormState,
    resetFormState,
    startLoading,
    stopLoading,
    setProductId,
  } = productFormStore;

  const { isEditMode, isLoading, productId, productCode } = storeToRefs(productFormStore);

  return {
    isEditMode,
    formState,
    isLoading,
    productId,
    productCode,
    setIsEditMode,
    setFormState,
    resetFormState,
    startLoading,
    stopLoading,
    setProductId,
  };
}
