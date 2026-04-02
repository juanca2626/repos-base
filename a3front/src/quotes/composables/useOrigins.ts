import { storeToRefs } from 'pinia';

import quotesApi from '@/quotes/api/quotesApi';
import type { OriginResponse, Origin } from '@/quotes/interfaces';
import { useOriginsStore } from '@/quotes/store/origins.store';

const getOrigins = async (query: string): Promise<Origin[]> => {
  const { data } = await quotesApi.get<OriginResponse>(`/api/flights/origins/1?query=${query}`);

  return data.data.map((item) => {
    return {
      ...item,
      label: item.ciudad + '-' + item.pais,
      code: item.codciu,
      iso: item.codpais,
    };
  });
};
const getOriginsDomestic = async (query: string): Promise<Origin[]> => {
  const { data } = await quotesApi.get<OriginResponse>(`/api/flights/origins/0?query=${query}`);

  return data.data.map((item) => {
    return {
      ...item,
      label: item.ciudad + '-' + item.pais,
      code: item.codciu,
    };
  });
};

const useOrigins = () => {
  const store = useOriginsStore();
  const { origins } = storeToRefs(store);
  return {
    // Properties
    origins,
    // Methods
    getOrigins: async (query: string) => {
      try {
        const origins = await getOrigins(query);
        store.setOrigins(origins);
      } catch (e) {
        console.log(e);
      }
    },
    getOriginsDomestic: async (query: string) => {
      try {
        const origins = await getOriginsDomestic(query);
        store.setOrigins(origins);
      } catch (e) {
        console.log(e);
      }
    },
    // Getters
  };
};

export default useOrigins;
