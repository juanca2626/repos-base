import { supportApi } from '@/modules/negotiations/api/negotiationsApi';

const baseResourceUrl = 'support/resources';

async function fetchCountryLocations(
  countryId: number,
  excludeZone: boolean = false
): Promise<any> {
  const response = await supportApi.get(baseResourceUrl, {
    params: {
      'keys[]': 'country_locations',
      country_id: countryId,
      exclude_zone: excludeZone ? 1 : 0,
    },
  });

  return response.data;
}

async function fetchServiceTypeList() {
  const response = await supportApi.get('service-types/list');

  return response.data;
}

export const useSupportResourceService = {
  fetchCountryLocations,
  fetchServiceTypeList,
};
