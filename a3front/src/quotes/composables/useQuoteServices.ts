import { storeToRefs } from 'pinia';
import useLoader from '@/quotes/composables/useLoader';
import quotesApi from '@/quotes/api/quotesApi';
import quotesA3Api from '@/quotes/api/quotesA3Api';
import { useQuoteServicesStore } from '@/quotes/store/quote-services.store';
import { useQuoteStore } from '@/quotes/store/quote.store';
import type {
  DestinyCity,
  DestinyCountry,
  DestinyState,
  ServiceDestinations,
  ServiceDestiny,
  ServiceDestinyResponse,
  ServiceDetailResponse,
  ServiceExperience,
  ServiceExperiencesResponse,
  ServiceExtensionsRequest,
  ServiceExtensionsResponse,
  ServicesAvailableRequest,
  ServicesAvailableResponse,
  ServicesCategoriesResponse,
  ServicesSubType,
  ServicesSubTypesResponse,
  ServicesType,
  ServicesTypesResponse,
  ServicesZonesResponse,
  ServiceZone,
} from '@/quotes/interfaces/services';
// import { getLang } from "@/quotes/helpers/get-lang";
import useQuoteTranslations from '@/quotes/composables/useQuoteTranslations';

const getServicesCategories = async (lang: string): Promise<ServicesType[]> => {
  const { data } = await quotesApi.get<ServicesTypesResponse>(
    `/api/service_types/selectBox?lang=${lang}`
  );

  return data.data.map((item) => {
    return {
      ...item,
      label: item.translations[0].value,
      value: item.id,
    };
  });
};

const getServicesExperiences = async (lang: string): Promise<ServiceExperience[]> => {
  const { data } = await quotesApi.get<ServiceExperiencesResponse>(
    `/services/experiences?lang=${lang}`
  );

  return data.data.map((item) => {
    return {
      ...item,
      label: item.name,
    };
  });
};

const getServicesDurations = async (lang: string): Promise<ServicesSubType[]> => {
  const { data } = await quotesApi.get<ServicesSubTypesResponse>(
    `/api/service_categories/9/${lang}/subcategory`
  );

  return data.data.map((item) => {
    return {
      ...item,
      label: item.translations[0].value,
    };
  });
};

const getServiceTypeMeals = async (lang: string): Promise<ServicesSubType[]> => {
  const { data } = await quotesApi.get<ServicesSubTypesResponse>(
    `/api/service_categories/10/${lang}/subcategory`
  );

  return data.data.map((item) => {
    return {
      ...item,
      label: item.translations[0].value,
    };
  });
};

const getServiceZones = async (stateCode: string, lang: string): Promise<ServiceZone[]> => {
  const { data } = await quotesApi.get<ServicesZonesResponse>(
    `/api/zone/states/${stateCode}/${lang}`
  );

  return data.data.map((item) => {
    return {
      ...item,
      label: item.translations[0].value,
    };
  });
};

const getServicesDestinations = async (lang: string): Promise<ServiceDestinations> => {
  const { data } = await quotesApi.get<ServiceDestinyResponse>(
    `/api/services/ubigeo/selectbox/originFormat/${lang}`
  );

  const destinationsCountries = <DestinyCountry[]>[];
  const destinationsStates = <DestinyState[]>[];
  const destinationsCities = <DestinyCity[]>[];

  data.data.forEach((d: ServiceDestiny) => {
    d.description = d.description.replace(', ', ',');

    const [countryCode, stateCode, cityCode, zoneCode] = d.id.split(',');
    const [countryLabel, stateLabel, cityLabel, zoneLabel] = d.description.split(',');

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

      if (!destinationsCities.some((e) => e === zone)) {
        destinationsCities.push(zone);
      }
    }
  });

  return {
    destinationsCountries: destinationsCountries,
    destinationsStates: destinationsStates,
    destinationsCities: destinationsCities,
  };
};

const getServicesAvailability = async (
  request: ServicesAvailableRequest
): Promise<ServicesAvailableResponse> => {
  const { data } = await quotesApi.post<ServicesAvailableResponse>(`/api/services/search`, request);

  return data;
};

const getExtensionsAvailability = async (
  request: ServiceExtensionsRequest
): Promise<ServiceExtensionsResponse[]> => {
  const { data } = await quotesA3Api.post<ServiceExtensionsResponse[]>(
    `/api/quote/extensions`,
    request
  );

  return data;
};

const getServiceDetails = async (
  serviceId: number,
  serviceDate: string,
  adult: number,
  child: number,
  lang: string
): Promise<ServiceDetailResponse> => {
  const { data } = await quotesApi.get<{ data: ServiceDetailResponse }>(
    `/api/service/${serviceId}/moreDetails`,
    {
      params: {
        lang: lang,
        date_out: serviceDate,
        total_pax: Number(adult) + Number(child),
      },
    }
  );

  return data;
};

const getServicesTypeCategories = async (lang: string): Promise<ServicesCategory[]> => {
  const { data } = await quotesApi.get<ServicesCategoriesResponse>(
    `/api/service_categories/selectBox?lang=${lang}`
  );

  return data.data.map((item) => {
    return {
      ...item,
      label: item.translations[0].value,
      value: item.id,
    };
  });
};

const getServicesSubCategories = async (
  category_id: number,
  lang: string
): Promise<ServicesSubType[]> => {
  const { data } = await quotesApi.get<ServicesSubTypesResponse>(
    `/api/service_categories/${category_id}/${lang}/subcategory`
  );

  return data.data.map((item) => {
    return {
      ...item,
      label: item.translations[0].value,
    };
  });
};

export const useQuoteServices = () => {
  const store = useQuoteServicesStore();
  const quoteStore = useQuoteStore();
  const { showIsLoading, closeIsLoading } = useLoader();
  const { getLang } = useQuoteTranslations();
  const {
    services,
    servicesTypes,
    serviceExperiences,
    serviceDurations,
    serviceTypeMeals,
    servicesDestinations,
    servicesZones,
    searchParameters,
    extensions,
    serviceCategories,
    serviceSubCategories,
    count,
  } = storeToRefs(store);

  return {
    // Props
    services,
    servicesTypes,
    serviceExperiences,
    serviceDurations,
    serviceTypeMeals,
    servicesDestinations,
    servicesZones,
    searchParameters,
    extensions,
    serviceCategories,
    serviceSubCategories,
    count,
    // Methods
    getToursAvailable: async (form: ServicesAvailableRequest, silent: boolean = false) => {
      if (!silent) showIsLoading();
      if (silent) quoteStore.setIsLoading(true);
      try {
        // service_category: 9 = tours
        const { data: services, count } = await getServicesAvailability({
          ...form,
          service_category: [9, 2],
        });
        store.setServices(services);
        store.setCount(count);
        store.setSearchParameters({ ...form, service_category: [9, 2] });
      } catch (e) {
        console.log(e);
      } finally {
        if (!silent) closeIsLoading();
        if (silent) quoteStore.setIsLoading(false);
      }
    },
    getMealsAvailable: async (form: ServicesAvailableRequest, silent: boolean = false) => {
      if (!silent) showIsLoading();
      if (silent) quoteStore.setIsLoading(true);
      try {
        // service_category: 10 = meals
        const { data: services, count } = await getServicesAvailability({
          ...form,
          service_category: [10],
        });
        store.setServices(services);
        store.setCount(count);
        store.setSearchParameters({ ...form, service_category: [10] });
      } catch (e) {
        console.log(e);
      } finally {
        if (!silent) closeIsLoading();
        if (silent) quoteStore.setIsLoading(false);
      }
    },
    getTransferAvailable: async (form: ServicesAvailableRequest, silent: boolean = false) => {
      if (!silent) showIsLoading();
      if (silent) quoteStore.setIsLoading(true);
      try {
        // service_category: 1 = transfer
        const { data: services, count } = await getServicesAvailability({
          ...form,
          service_category: [1],
        });
        store.setServices(services);
        store.setCount(count);
        store.setSearchParameters({ ...form, service_category: [1] });
      } catch (e) {
        console.log(e);
      } finally {
        if (!silent) closeIsLoading();
        if (silent) quoteStore.setIsLoading(false);
      }
    },
    getMiselaniosAvailable: async (form: ServicesAvailableRequest, silent: boolean = false) => {
      if (!silent) showIsLoading();
      if (silent) quoteStore.setIsLoading(true);
      try {
        // service_category: 11 = miselanios
        const { data: services, count } = await getServicesAvailability({
          ...form,
          service_category: [11],
        });
        store.setServices(services);
        store.setCount(count);
        store.setSearchParameters({ ...form, service_category: [11] });
      } catch (e) {
        console.log(e);
      } finally {
        if (!silent) closeIsLoading();
        if (silent) quoteStore.setIsLoading(false);
      }
    },
    getServiceByCategory: async (form: ServicesAvailableRequest, silent: boolean = false) => {
      if (!silent) showIsLoading();
      if (silent) quoteStore.setIsLoading(true);
      try {
        const { data: services, count } = await getServicesAvailability(form);
        store.setServices(services);
        store.setCount(count);
        store.setSearchParameters(form);
      } catch (e) {
        console.log(e);
      } finally {
        if (!silent) closeIsLoading();
        if (silent) quoteStore.setIsLoading(false);
      }
    },
    getExtensionsAvailable: async (form: ServiceExtensionsRequest, silent: boolean = false) => {
      if (!silent) showIsLoading();
      if (silent) quoteStore.setIsLoading(true);
      try {
        const { data: extensions } = await getExtensionsAvailability(form);
        store.setExtensions(extensions);
        store.setCount(extensions.length);
      } catch (e) {
        console.log(e);
      } finally {
        if (!silent) closeIsLoading();
        if (silent) quoteStore.setIsLoading(false);
      }
    },
    getServiceDetails: async (
      serviceId: number,
      serviceDate: string,
      adult: number,
      child: number
    ) => {
      const key = `${serviceId}-${serviceDate}-${adult}-${child}-${getLang()}`;
      if (store.serviceDetailsCache[key]) {
        return store.serviceDetailsCache[key];
      }

      showIsLoading();
      try {
        const result = await getServiceDetails(
          serviceId,
          serviceDate,
          adult,
          child,
          getLang()
        ).finally(() => closeIsLoading());
        store.setServiceDetailCache(key, result);
        return result;
      } catch (e) {
        console.log(e);
        closeIsLoading();
      }
    },
    getServicesCategories: async () => {
      const categories = await getServicesCategories(getLang());
      store.setServicesCategories(categories);
    },
    getServicesExperiences: async () => {
      const experiences = await getServicesExperiences(getLang());
      store.setServicesExperiences(experiences);
    },
    getServicesDurations: async () => {
      const durations = await getServicesDurations(getLang());
      store.setServicesDurations(durations);
    },
    getServiceTypeMeals: async () => {
      const typeMeals = await getServiceTypeMeals(getLang());
      store.setServicesTypeMeals(typeMeals);
    },
    getServicesDestinations: async () => {
      const destinations = await getServicesDestinations(getLang());
      store.setServicesDestinations(destinations);
    },
    getCountryStates(countryCode: string = '89') {
      if (!store.servicesDestinations) return [];
      return store.servicesDestinations.destinationsStates.filter(
        (d_u) => d_u.country_code === countryCode
      );
    },
    getStateCities(stateCode: string) {
      if (!store.servicesDestinations) return [];
      return store.servicesDestinations.destinationsCities.filter(
        (d_u) => d_u.state_code === stateCode
      );
    },
    getServiceZones: async (stateCode: string) => {
      const zones = await getServiceZones(stateCode, getLang());
      store.setServicesZones(zones);
    },
    getServicesTypeCategories: async () => {
      const categories = await getServicesTypeCategories(getLang());
      store.setServicesTypeCategories(categories);
    },
    getServicesSubCategories: async (category_id: number) => {
      const subCategories = await getServicesSubCategories(category_id, getLang());
      store.setServicesSubCategories(subCategories);
    },

    changePage: async (page: number) => {
      if (!store.searchParameters) return;
      const newParams = { ...store.searchParameters, page };
      if (!newParams.limit) newParams.limit = 10;

      showIsLoading();
      try {
        const { data: services, count } = await getServicesAvailability(newParams);
        store.setServices(services);
        store.setCount(count);
        store.setSearchParameters(newParams);
      } catch (e) {
        console.log(e);
      } finally {
        closeIsLoading();
      }
    },
    iniServiceZones() {
      store.setServicesZones();
    },
  };
};
