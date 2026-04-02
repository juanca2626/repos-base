import dayjs from 'dayjs';
import type { RatePlan } from '@/modules/negotiations/products/configuration/domain/pricingPlans/types/ratePlan.types';
import type { BasicSection } from '@/modules/negotiations/products/configuration/content/pricingPlans/state/createInitialBasicState';
import { TariffType } from '@/modules/negotiations/products/configuration/enums/tariffType.enum';
import { getYearsFromTravelRange } from '@/modules/negotiations/products/configuration/utils/getYearsFromTravelRange.util';
import type { StaffState } from '@/modules/negotiations/products/configuration/content/pricingPlans/types/staff.types';

function mapRateTypeToTariffType(rateType: string): TariffType {
  const upper = rateType?.toUpperCase();
  if (Object.values(TariffType).includes(upper as TariffType)) return upper as TariffType;
  return TariffType.FLAT;
}

export function mapRatePlanToState(ratePlan: RatePlan, state: any): any {
  const entityId = ratePlan?.id ?? null;

  let years: number[] = [];

  if (ratePlan.validity?.from && ratePlan.validity?.to) {
    years = getYearsFromTravelRange(
      dayjs(ratePlan.validity.from).format('YYYY-MM-DD'),
      dayjs(ratePlan.validity.to).format('YYYY-MM-DD')
    );
  }

  const basicState = {
    name: ratePlan.name ?? 'Tarifa',
    requiresBookingCode: ratePlan.bookingCode?.required ?? false,
    bookingCode: ratePlan.bookingCode?.code ?? '',
    tariffType: mapRateTypeToTariffType(ratePlan.rateType ?? ''),
    promotionName: ratePlan.promotion?.name ?? '',
    tariffSegmentation: ratePlan.segmentation?.seasons ?? [],
    specificMarkets: ratePlan.segmentation?.markets ?? [],
    specificClients: ratePlan.segmentation?.clients ?? [],
    specificSeries: ratePlan.segmentation?.series ?? '',
    periods: [
      {
        periodId: '',
        periodType: '',
        periodName: '',
        ranges: [{ dateFrom: null, dateTo: null }],
      },
    ],
    travelFrom: ratePlan.validity?.from ? dayjs(ratePlan.validity.from) : dayjs(),
    travelTo: ratePlan.validity?.to ? dayjs(ratePlan.validity.to) : null,
    modifyBookingPeriod: ratePlan.bookingWindow?.isCustom ?? false,
    bookingFrom: ratePlan.bookingWindow?.from ? dayjs(ratePlan.bookingWindow.from) : null,
    bookingTo: ratePlan.bookingWindow?.to ? dayjs(ratePlan.bookingWindow.to) : null,
    differentiatedTariff: ratePlan.dayConfig?.hasDifferentialPricing ?? false,
    selectedDays: ratePlan.dayConfig?.selectedDifferentialDays ?? [],
    standardDays: ratePlan.dayConfig?.standardDays ?? state.standardDays,
    includeHolidayTariffs: ratePlan.holidayConfig?.includesHolidays ?? false,
    selectedHolidays: ratePlan.holidayConfig?.groups ?? [],
    currencyBuy: ratePlan.currencyConfig?.purchase ?? 'USD',
    currencySell: ratePlan.currencyConfig?.sales ?? 'USD',
    years: years,
    selectedCategoryId: ratePlan.holidayConfig?.groups?.[0]?.id ?? null,
    selectedYear: years[0] ?? null,
  };

  const staffState = {
    taxes: {
      labelTax: ratePlan.taxAndStaffConfig?.globalDefinitions?.mainTax?.label ?? 'IGV',
      affectedByIGV: ratePlan.taxAndStaffConfig?.globalDefinitions?.mainTax?.isAffected ?? false,
      igvPercent: ratePlan.taxAndStaffConfig?.globalDefinitions?.mainTax?.value ?? null,
      igvRecovery: ratePlan.taxAndStaffConfig?.globalDefinitions?.mainTax?.hasRecovery ?? false,
      igvRecoveryPercent: ratePlan.taxAndStaffConfig?.globalDefinitions?.mainTax?.value ?? null,
      servicePercentage: ratePlan.taxAndStaffConfig?.globalDefinitions?.serviceFee?.value ?? null,
      additionalPercentage:
        (ratePlan.taxAndStaffConfig?.globalDefinitions?.additionalFees?.length ?? 0) > 0,
      additionalPercentages:
        ratePlan.taxAndStaffConfig?.globalDefinitions?.additionalFees?.map((fee) => ({
          id: fee.id,
          name: fee.name,
          percentage: fee.value,
        })) ?? state.staff.taxes.additionalPercentages,
    },
    staff: {
      selectedStaff: ratePlan.taxAndStaffConfig?.staffRules?.map((rule) => rule.role) ?? [],
      staffTaxes: (ratePlan.taxAndStaffConfig?.staffRules ?? []).reduce(
        (acc, rule) => {
          acc[rule.role] = rule.taxSettings.reduce(
            (taxAcc, tax) => {
              const taxKey = tax.feeId;
              taxAcc[taxKey] = tax.isAffected;
              return taxAcc;
            },
            {} as Record<string, boolean>
          );
          return acc;
        },
        {} as Record<string, Record<string, boolean>>
      ),
    },
    commissions: {
      percentage: ratePlan.taxAndStaffConfig?.commissions?.percentage ?? null,
    },
  };

  const pricesState = state.prices; // de momento para no romper nada
  const quotaState = state.quota; // de momento para no romper nada

  return {
    entityId,
    basic: basicState as BasicSection,
    staff: staffState as StaffState,
    prices: pricesState,
    quota: quotaState,
  };
}
