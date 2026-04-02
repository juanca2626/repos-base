import { ref } from 'vue';
import { storeToRefs } from 'pinia';

import quotesApi from '@/quotes/api/quotesApi';
import type {
  Destination,
  Destinations,
  DestinationsCountry,
  DestinationsResponse,
  DestinationsState,
  DestinationsZone,
} from '@/quotes/interfaces';
import { useDestinationsStore } from '@/quotes/store/destinations.store';
import useQuoteTranslations from '@/quotes/composables/useQuoteTranslations';

const isLoading = ref<boolean | undefined>(undefined);

const getDestinations = async (lang: string = 'en'): Promise<Destinations> => {
  const { data } = await quotesApi.get<DestinationsResponse>(
    `/services/hotels/quotes/destinations?lang=${lang}`
  );

  const destinationsCountries = <DestinationsCountry[]>[];
  const destinationsStates = <DestinationsState[]>[];
  const destinationsZones = <DestinationsZone[]>[];

  data.data.forEach((d: Destination) => {
    d.label = d.label.replace(', ', ',');

    const [countryCode, stateCode, cityCode, zoneCode] = d.code.split(',');
    const [countryLabel, stateLabel, cityLabel, zoneLabel] = d.label.split(',');

    const country = {
      code: countryCode,
      label: countryLabel.trim(),
    };

    if (!destinationsCountries.some((e) => e.code === countryCode)) {
      destinationsCountries.push(country);
    }

    const state = {
      code: stateCode,
      label: stateLabel ? stateLabel.trim() : '',
      country_code: countryCode,
    };

    if (!destinationsStates.some((e) => e.code === state.code)) {
      destinationsStates.push(state);
    }

    if (cityCode) {
      const zone = {
        code: cityCode + (zoneCode ? ',' + zoneCode : ''),
        label: cityLabel + (zoneLabel ? ',' + zoneLabel : ''),
        state_code: stateCode,
      };

      if (!destinationsZones.some((e) => e === zone)) {
        destinationsZones.push(zone);
      }
    }
  });

  return {
    destinationsCountries: destinationsCountries,
    destinationsStates: destinationsStates,
    destinationsZones: destinationsZones,
  };
};

const useQuoteDestinations = () => {
  const store = useDestinationsStore();
  const { destinations } = storeToRefs(store);
  const { getLang } = useQuoteTranslations();
  return {
    // Properties
    destinations,

    // Methods
    getDestinations: async () => {
      isLoading.value = true;
      destinations.value = await getDestinations(getLang());
      isLoading.value = false;
    },
    getStatesByCountryCode(countryCode: string = 'PE') {
      return destinations.value.destinationsStates.filter(
        (d_u) => d_u.country_code === countryCode
      );
    },
    getZonesByStateCode(stateCode: string) {
      return destinations.value.destinationsZones.filter((d_u) => d_u.state_code === stateCode);
    },

    // Getters
  };
};

export default useQuoteDestinations;
