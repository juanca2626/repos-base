import { storeToRefs } from 'pinia';

import quotesApi from '@/quotes/api/quotesApi';
import type { CountriesResponse, Country } from '@/quotes/interfaces';
import { useCountriesStore } from '@/quotes/store/countries.store';
import useQuoteTranslations from '@/quotes/composables/useQuoteTranslations';

const getCountries = async (lang: string): Promise<Country[]> => {
  const { data } = await quotesApi.get<CountriesResponse>(`/api/passengers-countries?lang=${lang}`);

  return data.data.map((item) => {
    return {
      ...item,
      label: item.translations[0].value,
      code: item.iso,
    };
  });
};

const useCountries = () => {
  const store = useCountriesStore();
  const { countries } = storeToRefs(store);
  const { getLang } = useQuoteTranslations();
  return {
    // Properties
    countries,
    // Methods
    getCountries: async () => {
      try {
        const countries = await getCountries(getLang());
        store.setCountries(countries);
      } catch (e) {
        console.log(e);
      }
    },
    getPhoneCode: () => {
      return countries.value.map((item) => {
        return {
          label: item.iso + ' (+' + item.phone_code + ')',
          code: item.phone_code,
        };
      });
    },
    // Getters
  };
};

export default useCountries;
