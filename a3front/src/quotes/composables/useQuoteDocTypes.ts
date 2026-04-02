import { storeToRefs } from 'pinia';

import quotesApi from '@/quotes/api/quotesApi';
import type { Doctype, DoctypesResponse } from '@/quotes/interfaces';
import { useDocTypesStore } from '@/quotes/store/doc-types.store';
import useQuoteTranslations from '@/quotes/composables/useQuoteTranslations';

const getDoctypes = async (lang: string): Promise<Doctype[]> => {
  const { data } = await quotesApi.get<DoctypesResponse>(`/api/doctypes?lang=${lang}`);

  return data.data.map((item) => {
    return {
      ...item,
      label: item.translations[0].value,
      code: item.iso,
    };
  });
};

const useQuoteDocTypes = () => {
  const store = useDocTypesStore();
  const { getLang } = useQuoteTranslations();
  const { docTypes } = storeToRefs(store);
  return {
    // Properties
    docTypes,
    // Methods
    getDoctypes: async () => {
      try {
        const docTypes = await getDoctypes(getLang());
        store.setDocTypes(docTypes);
      } catch (e) {
        console.log(e);
      }
    },
    // Getters
  };
};

export default useQuoteDocTypes;
