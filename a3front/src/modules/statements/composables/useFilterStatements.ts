import { reactive } from 'vue';

export const useFilterStatements = () => {
  const filters = reactive({
    startDate: null,
    endDate: null,
    total: null,
  });

  const applyFilters = () => {
    console.log('Filtros aplicados:', filters);
  };

  return {
    filters,
    applyFilters,
  };
};
