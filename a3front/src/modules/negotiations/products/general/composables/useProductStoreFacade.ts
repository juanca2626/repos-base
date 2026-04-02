import { storeToRefs } from 'pinia';
import { useProductStore } from '@/modules/negotiations/products/general/store/useProductStore';

export function useProductStoreFacade() {
  const productStore = useProductStore();

  const { setProductFormViewType } = productStore;

  const { productFormViewType } = storeToRefs(productStore);

  return {
    productFormViewType,
    setProductFormViewType,
  };
}
