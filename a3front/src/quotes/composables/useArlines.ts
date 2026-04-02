import { storeToRefs } from 'pinia';

import quotesApi from '@/quotes/api/quotesApi';
import type { AirlineResponse, Airline } from '@/quotes/interfaces';
import { useAirlinesStore } from '@/quotes/store/airlines.store';

const getAirlines = async (query: string): Promise<Airline[]> => {
  const { data } = await quotesApi.get<AirlineResponse>(`/api/flights/airlines?query=${query}`);

  return data.data.map((item) => {
    return {
      ...item,
      label: item.codigo + '-' + item.razon,
      code: item.codigo,
    };
  });
};

const useArlines = () => {
  const store = useAirlinesStore();
  const { airlines } = storeToRefs(store);
  return {
    // Properties
    airlines,
    // Methods
    getAirlines: async (query: string) => {
      try {
        const airlines = await getAirlines(query);
        store.setAirlines(airlines);
      } catch (e) {
        console.log(e);
      }
    },
    // Getters
  };
};

export default useArlines;
