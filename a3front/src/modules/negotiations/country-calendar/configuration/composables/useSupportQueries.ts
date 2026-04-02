import { useQuery } from '@tanstack/vue-query';
import { countryCalendarService } from '../services/countryCalendarService';

export const SUPPORT_KEYS = {
  cities: 'cities',
  countries: 'countries',
  zones: 'zones',
};

export const useSupportResources = () => {
  return useQuery({
    queryKey: ['support-resources', { keys: Object.values(SUPPORT_KEYS) }],
    queryFn: () => countryCalendarService.fetchResources(Object.values(SUPPORT_KEYS)),
    staleTime: 1000 * 60 * 60, // 1 hour - these don't change often
  });
};
