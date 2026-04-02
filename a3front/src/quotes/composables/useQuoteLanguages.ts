import { storeToRefs } from 'pinia';

import quotesApi from '@/quotes/api/quotesApi';
import type { Language, LanguagesResponse } from '@/quotes/interfaces';
import { useLanguagesStore } from '@/stores/global';

const getLanguages = async (): Promise<Language[]> => {
  const { data } = await quotesApi.get<LanguagesResponse>(`/api/languages`);

  return data.data.map((item) => {
    return {
      ...item,
      label: item.name,
      value: item.id,
    };
  });
};

const useQuoteLanguages = () => {
  const store = useLanguagesStore();
  const { languages } = storeToRefs(store);
  return {
    // Properties
    languages,
    // Methods
    getLanguages: async () => {
      try {
        const languages = await getLanguages();
        store.setLanguages(languages);
      } catch (e) {
        console.log(e);
      }
    },
    // Getters
  };
};

export default useQuoteLanguages;
