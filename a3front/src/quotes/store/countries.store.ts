import { defineStore } from 'pinia';
import type { Country } from '@/quotes/interfaces';

interface CountriesState {
  countries: Country[];
}

export const useCountriesStore = defineStore({
  id: 'useCountriesStore',
  state: () =>
    ({
      countries: [] as Country[],
    }) as CountriesState,
  actions: {
    setCountries(countries: Country[]) {
      this.countries = countries;
    },
  },
});
