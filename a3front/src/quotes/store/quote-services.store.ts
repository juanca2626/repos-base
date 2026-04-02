import { defineStore } from 'pinia';
import type {
  Service,
  ServiceDestinations,
  ServiceDetailResponse,
  ServiceExperience,
  ServiceExtensionsResponse,
  ServicesAvailableRequest,
  ServicesCategory,
  ServicesSubType,
  ServicesType,
  ServiceZone,
} from '@/quotes/interfaces/services';

interface QuoteServicesState {
  services: Service[];
  searchParameters: ServicesAvailableRequest | null;
  servicesTypes: ServicesType[];
  serviceExperiences: ServiceExperience[];
  serviceDurations: ServicesSubType[];
  serviceTypeMeals: ServicesSubType[];
  servicesDestinations: ServiceDestinations;
  servicesZones: ServiceZone[];
  extensions: ServiceExtensionsResponse[];
  serviceCategories: ServicesCategory[];
  serviceSubCategories: ServicesSubType[];
  count: number;
  serviceDetailsCache: Record<string, ServiceDetailResponse>;
}

export const useQuoteServicesStore = defineStore({
  id: 'quoteServicesStore',
  state: () =>
    ({
      services: [],
      searchParameters: null,
      servicesTypes: [],
      serviceExperiences: [],
      serviceDurations: [],
      serviceTypeMeals: [],
      servicesDestinations: {
        destinationsCountries: [],
        destinationsStates: [],
        destinationsCities: [],
        destinationsZones: [],
      },
      servicesZones: [],
      extensions: [],
      serviceCategories: [],
      serviceSubCategories: [],
      count: 0,
      serviceDetailsCache: {},
    }) as QuoteServicesState,
  actions: {
    setServices(services: Service[]) {
      this.services = services;
    },
    setExtensions(extensions: ServiceExtensionsResponse[]) {
      this.extensions = extensions;
      this.setCount(extensions.length);
    },
    setCount(count: number) {
      this.count = count;
    },
    setServicesCategories(categories: ServicesType[]) {
      this.servicesTypes = categories;
    },
    setServicesExperiences(experience: ServiceExperience[]) {
      this.serviceExperiences = experience;
    },
    setServicesDurations(durations: ServicesSubType[]) {
      this.serviceDurations = durations;
    },
    setServicesTypeMeals(typeMeals: ServicesSubType[]) {
      this.serviceTypeMeals = typeMeals;
    },
    setServicesDestinations(destinations: ServiceDestinations) {
      this.servicesDestinations = destinations;
    },
    setServicesZones(zones: ServiceZone[] = []) {
      this.servicesZones = zones;
    },
    setSearchParameters(searchParameters: ServicesAvailableRequest) {
      this.searchParameters = searchParameters;
    },
    setServicesTypeCategories(categories: ServicesCategory[]) {
      this.serviceCategories = categories;
    },
    setServicesSubCategories(subCategories: ServicesSubType[]) {
      this.serviceSubCategories = subCategories;
    },
    unsetServices() {
      this.services = [];
    },
    unsetCount() {
      this.count = 0;
    },
    unsetServicesCategories() {
      this.servicesTypes = [];
    },
    unsetServicesExperiences() {
      this.serviceExperiences = [];
    },
    unsetServicesDurations() {
      this.serviceDurations = [];
    },
    unsetServicesTypeMeals() {
      this.serviceTypeMeals = [];
    },
    unsetServicesDestinations() {
      this.servicesDestinations = {
        destinationsCountries: [],
        destinationsStates: [],
        destinationsCities: [],
      };
    },
    unsetServicesZones() {
      this.servicesZones = [];
    },
    setServiceDetailCache(key: string, detail: ServiceDetailResponse) {
      this.serviceDetailsCache[key] = detail;
    },
  },
});
