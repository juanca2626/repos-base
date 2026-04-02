import dayjs from '@/modules/negotiations/products/configuration/utils/dayjs';
import { TariffType } from '@/modules/negotiations/products/configuration/enums/tariffType.enum';

type TravelRange = {
  dateFrom: dayjs.Dayjs;
  dateTo: dayjs.Dayjs;
} | null;

export function resolvePricingTravelRange(formState: any): TravelRange {
  // TARIFA PLANA
  if (formState.tariffType !== TariffType.PERIODS) {
    if (!formState.travelFrom || !formState.travelTo) return null;

    return {
      dateFrom: formState.travelFrom,
      dateTo: formState.travelTo,
    };
  }

  // TARIFA PERIODOS
  if (!formState.periods?.length) return null;

  const ranges = formState.periods
    .flatMap((group: any) => group.ranges || [])
    .filter((r: any) => r?.dateFrom && r?.dateTo);

  if (!ranges.length) return null;

  let min = dayjs(ranges[0].dateFrom);
  let max = dayjs(ranges[0].dateTo);

  for (const range of ranges) {
    const from = dayjs(range.dateFrom);
    const to = dayjs(range.dateTo);

    if (from.isBefore(min)) min = from;
    if (to.isAfter(max)) max = to;
  }

  return {
    dateFrom: min,
    dateTo: max,
  };
}
