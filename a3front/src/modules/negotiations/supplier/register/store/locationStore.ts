import { defineStore } from 'pinia';
import { supportApi } from '@/modules/negotiations/api/negotiationsApi';
import type { CountryLocation, LocationOption } from '@/modules/negotiations/supplier/interfaces';

export const useLocationStore = defineStore('locationStore', {
  state: () => ({
    countryLocations: [] as CountryLocation[], // Ubicaciones api
    locationOptions: [] as LocationOption[], // Opciones de ubicaciones
    isLoading: false,
    error: null as string | null,
    locationsByStateCity: [] as LocationOption[],
  }),
  actions: {
    async fetchLocations(countryId: number, excludeZone: boolean = false) {
      this.isLoading = true;
      this.error = null;

      try {
        const response = await supportApi.get('support/resources', {
          params: {
            'keys[]': 'country_locations',
            country_id: countryId,
            exclude_zone: excludeZone ? 1 : 0,
          },
        });

        this.countryLocations = response.data.data.country_locations;

        this.generateOptionLocations();
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Error al cargar ubicaciones';
      } finally {
        this.isLoading = false;
      }
    },
    generateOptionLocations() {
      // Mapear datos devueltos al formato esperado
      this.locationsByStateCity = this.mapLocationToOptions(
        this.filterLocationsByZoneAvailability(false)
      );
      this.locationOptions = this.mapLocationToOptions(this.countryLocations);
    },
    filterLocationsByZoneAvailability(includeZone: boolean): CountryLocation[] {
      return this.countryLocations.filter((row) =>
        includeZone ? row.zone_id !== null : row.zone_id === null
      );
    },
    getLocationsByZone(countryId: number, stateId: number, cityId: number): LocationOption[] {
      return this.countryLocations
        .filter((row) => {
          return (
            row.country_id === countryId &&
            row.state_id === stateId &&
            row.city_id === cityId &&
            row.zone_id !== null
          );
        })
        .map((location: CountryLocation) => ({
          label: `${location.location_name
            .split(',')
            .map((item: string) => item.trim())
            .pop()}`,
          value: location.zone_id?.toString() ?? '',
        }));
    },
    mapLocationToOptions(countryLocations: CountryLocation[]): LocationOption[] {
      return countryLocations.map((location: any) => ({
        label: `${location.country_name}, ${location.location_name}`,
        value:
          `${location.country_id}-${location.state_id}-${location.city_id || ''}-${location.zone_id || ''}`
            .replace(/--/g, '-')
            .replace(/-$/, ''), // Valor único
      }));
    },
  },
});
