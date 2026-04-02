import { defineStore } from 'pinia';
import { ref, reactive, computed } from 'vue';
import type { ProductForm } from '@/modules/negotiations/products/general/interfaces/form';

export const useProductFormStore = defineStore('productFormStore', () => {
  const initialFormData: ProductForm = {
    id: null,
    serviceTypeId: null,
    serviceTypeName: null,
    code: null,
    name: null,
  };

  const isEditMode = ref<boolean>(false);

  const pendingRequests = ref<number>(0);

  const startLoading = () => {
    return pendingRequests.value++;
  };
  const stopLoading = () => {
    return (pendingRequests.value = Math.max(pendingRequests.value - 1, 0));
  };

  const isLoading = computed(() => pendingRequests.value > 0);

  const formState = reactive<ProductForm>({
    ...initialFormData,
  });

  const setIsEditMode = (value: boolean) => {
    isEditMode.value = value;
  };

  const setFormState = (data: ProductForm) => {
    Object.assign(formState, structuredClone(data));
  };

  const resetFormState = () => {
    setFormState(initialFormData);
  };

  const productId = computed(() => formState.id);

  const productCode = computed(() => formState.code);

  const setProductId = (id: string | null) => {
    formState.id = id;
  };

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
});
