import { ref } from 'vue';
import { useRestaurantFilterStore } from '@/modules/negotiations/suppliers/restaurants/store/restaurant-filter.store';

export function useRestaurantFilter() {
  const { filterState } = useRestaurantFilterStore();

  const showDrawerForm = ref<boolean>(false);

  const handleAddFilter = () => {
    showDrawerForm.value = true;
  };

  return {
    showDrawerForm,
    filterState,
    handleAddFilter,
  };
}
