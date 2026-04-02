import { ref } from 'vue';

export const useFilterOptions = () => {
  const filterOptions = ref([
    { value: 'a', label: 'filter a' },
    { value: 'b', label: 'filter b' },
    { value: 'c', label: 'filter c' },
  ]);

  return {
    filterOptions,
  };
};
