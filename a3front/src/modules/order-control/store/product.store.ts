import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import { fetchProducts } from '@ordercontrol/api';

interface ProductFromApi {
  _id: string;
  code: string;
  name: string;
  [key: string]: any; // Para el resto de propiedades
}

interface ProductOption {
  value: string;
  label: string;
}

export const useProductStore = defineStore('productStore', () => {
  const isLoading = ref(false);
  const products = ref<ProductOption[]>([]);
  const error = ref<string | null>(null);
  const getProducts = computed(() => products.value);

  /**
   * Fetches products from the API and maps them for select components.
   * @param {any} params - Optional parameters for the request.
   */
  const fetchAll = async (params: any = {}) => {
    isLoading.value = true;
    error.value = null;
    try {
      const response = await fetchProducts(params);
      console.log('Products - fetchAll ~ response:', response);
      if (response && response.data) {
        products.value = response.data
          .filter((product: ProductFromApi) => product.visible === true)
          .map((product: ProductFromApi) => ({
            value: product.code,
            label: product.name,
          }));
      } else {
        products.value = [];
      }
    } catch (e: any) {
      error.value = e.message || 'An unknown error occurred while fetching products.';
      products.value = [];
    } finally {
      isLoading.value = false;
    }
  };

  return {
    isLoading,
    getProducts,
    error,
    fetchAll,
  };
});
