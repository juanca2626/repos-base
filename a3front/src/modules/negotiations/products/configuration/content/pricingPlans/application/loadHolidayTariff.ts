import { useConfigurationStore } from '@/modules/negotiations/products/configuration/stores/useConfigurationStore';
import { useConfigurationLocations } from '@/modules/negotiations/products/configuration/composables/useConfigurationLocations';
import { resolvePricingTravelRange } from '../domain/travelRange/resolvePricingTravelRange';
import { getYearsFromTravelRange } from '@/modules/negotiations/products/configuration/utils/getYearsFromTravelRange.util';
import { storeToRefs } from 'pinia';

type LoadHolidayTariffResult = {
  holidays: any[];
  years: number[];
  loadingHoliday: boolean;
  selectedYear: number | null;
  selectedCategoryId: string | number | null;
};

export async function loadHolidayTariff(
  formState: any,
  ratePlanId: string | null
): Promise<LoadHolidayTariffResult | null> {
  if (!formState.includeHolidayTariffs) return null;

  const configurationStore = useConfigurationStore();

  const { loadingHoliday } = storeToRefs(configurationStore);

  const { location } = useConfigurationLocations();

  const range = resolvePricingTravelRange(formState);

  if (!range) return null;

  const dateFrom = range.dateFrom.format('YYYY-MM-DD');
  const dateTo = range.dateTo.format('YYYY-MM-DD');

  const response = await configurationStore.loadExternalHolidays({
    country: location.value.countryCode,
    city: location.value.stateCode,
    dateFrom,
    dateTo,
    ratePlanId,
  });

  const holidays = response?.groups || [];

  const years = getYearsFromTravelRange(dateFrom, dateTo);
  const selectedYear = years[0] ?? null;

  const selectedCategoryId = holidays.length ? (holidays[0].id ?? holidays[0].uuid) : null;

  return {
    holidays,
    loadingHoliday: loadingHoliday.value,
    years,
    selectedYear,
    selectedCategoryId,
  };
}
