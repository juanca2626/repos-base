import { defineStore } from 'pinia';
import { ref } from 'vue';
import { FormViewTypeEnum } from '@/modules/negotiations/products/general/enums/form-view-type.enum';

export const useProductStore = defineStore('productStore', () => {
  // controlar vistas en product form manager
  const productFormViewType = ref<FormViewTypeEnum>(FormViewTypeEnum.EDITABLE);

  const setProductFormViewType = (value: FormViewTypeEnum) => {
    productFormViewType.value = value;
  };

  return {
    productFormViewType,
    setProductFormViewType,
  };
});
