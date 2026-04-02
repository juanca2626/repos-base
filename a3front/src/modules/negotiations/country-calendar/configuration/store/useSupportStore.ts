import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { countryCalendarService, type SupportResource } from '../services/countryCalendarService';

export const useSupportStore = defineStore('countryCalendarSupport', () => {
  // State
  const cities = ref<SupportResource[]>([]);
  const countries = ref<SupportResource[]>([]);
  const zones = ref<SupportResource[]>([]);
  const loading = ref(false);
  const loaded = ref(false);

  // Getters
  const cityMapById = computed(() => {
    const map: Record<number, string> = {};
    cities.value.forEach((c) => {
      map[c.id] = c.name;
    });
    return map;
  });

  const zoneMapById = computed(() => {
    const map: Record<number, string> = {};
    zones.value.forEach((z) => {
      map[z.id] = z.name;
    });
    return map;
  });

  const countryMapById = computed(() => {
    const map: Record<number, string> = {};
    countries.value.forEach((c) => {
      map[c.id] = c.name;
    });
    return map;
  });

  // Actions
  const fetchResources = async () => {
    if (loaded.value || loading.value) return;

    try {
      loading.value = true;
      const response = await countryCalendarService.fetchResources(['countries']);
      countries.value = response.countries || [];
      loaded.value = true;
    } catch (error) {
      console.error('Error fetching support resources:', error);
    } finally {
      loading.value = false;
    }
  };

  const fetchCitiesAndZones = async (countryId: number): Promise<void> => {
    try {
      loading.value = true;
      const response = await countryCalendarService.getCitiesAndZones(countryId);
      cities.value = response.cities;
      zones.value = response.zones;
    } catch (error) {
      console.error('Error fetching cities and zones:', error);
      cities.value = [];
      zones.value = [];
    } finally {
      loading.value = false;
    }
  };

  return {
    cities,
    countries,
    zones,
    loading,
    loaded,
    cityMapById,
    zoneMapById,
    countryMapById,
    fetchResources,
    fetchCitiesAndZones,
  };
});
