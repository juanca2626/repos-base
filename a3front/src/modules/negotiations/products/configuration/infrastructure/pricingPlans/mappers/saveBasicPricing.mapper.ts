import type { SaveBasicPricingRequest } from '@/modules/negotiations/products/configuration/domain/pricingPlans/types/saveBasicPricingRequest.types';
import type { SaveBasicPricingDto } from '../dtos/saveBasicPricing.dto';
import dayjs from 'dayjs';

export const mapToSaveBasicPricingRequest = (
  productSupplierId: string,
  serviceDetailId: string,
  ratePlanId: string | null,
  data: SaveBasicPricingRequest
): SaveBasicPricingDto => {
  return {
    id: ratePlanId ?? null,
    productSupplierId,
    serviceDetailId,

    rateType: data.tariffType,

    name: data.name,

    promotion: {
      name: data.promotionName ?? '',
    },

    bookingCode: {
      required: data.requiresBookingCode,
      code: data.bookingCode ?? '',
    },

    validity: {
      from: dayjs(data.travelFrom).format('YYYY-MM-DD'),
      to: dayjs(data.travelTo).format('YYYY-MM-DD'),
    },

    bookingWindow: {
      isCustom: data.modifyBookingPeriod,
      from: data.bookingFrom != null ? dayjs(data.bookingFrom).format('YYYY-MM-DD') : null,
      to: data.bookingTo != null ? dayjs(data.bookingTo).format('YYYY-MM-DD') : null,
    },

    currencyConfig: {
      purchase: data.currencyBuy,
      sales: data.currencySell,
    },

    rulesInput: {
      seasons:
        data.periods
          ?.filter((p: any) => p.periodId?.trim())
          ?.map((p: any) => ({
            seasonId: p.periodId,
            ranges: p.ranges.map((r: any) => ({
              from: dayjs(r.dateFrom).format('YYYY-MM-DD'),
              to: dayjs(r.dateTo).format('YYYY-MM-DD'),
            })),
          })) ?? [],
      specificDates: [],
    },

    segmentation: {
      markets: data.specificMarkets ?? [],
      clients: data.specificClients ?? [],
      series: data.specificSeries ?? '',
      parties: [],
      seasons: [],
    },

    dayConfig: {
      hasDifferentialPricing: data.differentiatedTariff,
      selectedDifferentialDays: data.selectedDays ?? [],
      standardDays: data.standardDays ?? [],
    },

    holidayConfig: {
      includesHolidays: data.includeHolidayTariffs,
      groups: data.selectedHolidays ?? [],
    },
  };
};
