import { ref, watch } from 'vue';
import debounce from 'lodash/debounce';

export function useDebouncedSearch(searchFn: (query: string) => void, delay = 500) {
  const searchQuery = ref('');

  // Definir el debounce dentro del setup, para que no se cree en cada render
  const debouncedSearch = debounce((query: string) => {
    if (query.length >= 2) {
      searchFn(query);
    }
  }, delay);

  watch(searchQuery, (newQuery) => {
    debouncedSearch(newQuery);
  });

  return { searchQuery };
}
