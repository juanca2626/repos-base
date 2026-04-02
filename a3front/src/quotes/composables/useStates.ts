import { storeToRefs } from 'pinia';

import quotesApi from '@/quotes/api/quotesApi';
import type { StatesResponse, State } from '@/quotes/interfaces';
import { useStatesStore } from '@/quotes/store/states.store';

const getStates = async (country: string): Promise<State[]> => {
  const { data } = await quotesApi.get<StatesResponse>(`/api/country/${country}/cities_ifx`);

  return data.data.map((item) => {
    return {
      ...item,
      label: item.name,
      code: item.iso,
    };
  });
};

const useStates = () => {
  const store = useStatesStore();
  const { states } = storeToRefs(store);
  return {
    // Properties
    states,
    // Methods
    getStates: async (country: string) => {
      try {
        const states = await getStates(country);
        store.setStates(states);
      } catch (e) {
        console.log(e);
      }
    },
    // Getters
  };
};

export default useStates;
