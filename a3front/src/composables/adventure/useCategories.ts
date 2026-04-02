import { useCategoriesStore } from '@/stores/adventure';
import { storeToRefs } from 'pinia';

export function useCategories() {
  const categoriesStore = useCategoriesStore();
  const { isLoading, error, getCategories, category } = storeToRefs(categoriesStore);
  const { fetchAll, save, update } = categoriesStore;

  return {
    isLoading,
    error,
    category,
    categories: getCategories,
    fetchCategories: fetchAll,
    saveCategory: save,
    updateCategory: update,
  };
}
