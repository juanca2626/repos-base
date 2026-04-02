import { TariffType } from '@/modules/negotiations/products/configuration/enums/tariffType.enum';
import type { BasicSection } from '../state/createInitialBasicState';

export function getBasicStepIssues(state: BasicSection) {
  const issues: string[] = [];

  if (!state.tariffType) issues.push('tariffType');

  if (state.requiresBookingCode && !state.bookingCode) {
    issues.push('bookingCode');
  }

  if (
    [TariffType.FLAT, TariffType.PROMOTIONAL, TariffType.SPECIFIC].includes(
      state.tariffType as TariffType
    )
  ) {
    if (!state.travelFrom) issues.push('travelFrom');
    if (!state.travelTo) issues.push('travelTo');
  }

  if (state.tariffType === TariffType.PERIODS) {
    if (!state.periods?.length) {
      issues.push('periods');
    } else {
      state.periods.forEach((group: any, i: number) => {
        if (!group.periodType) issues.push(`periodType_${i}`);

        group.ranges?.forEach((range: any, r: number) => {
          if (!range.dateFrom || !range.dateTo) {
            issues.push(`range_${i}_${r}`);
          }
        });
      });
    }
  }

  if (state.tariffType === TariffType.PROMOTIONAL) {
    if (!state.promotionName?.length) issues.push('promotionName');
  }

  if (state.tariffType === TariffType.SPECIFIC) {
    if (!state.tariffSegmentation?.length) issues.push('tariffSegmentation');
    if (!state.specificMarkets?.length) issues.push('specificMarkets');
    if (!state.specificClients?.length) issues.push('specificClients');
    if (!state.specificSeries?.length) issues.push('specificSeries');
  }

  if (state.modifyBookingPeriod) {
    if (!state.bookingFrom) issues.push('bookingFrom');
    if (!state.bookingTo) issues.push('bookingTo');
  }

  if (state.differentiatedTariff && !state.selectedDays?.length) {
    issues.push('selectedDays');
  }

  if (state.includeHolidayTariffs && !state.selectedHolidays?.length) {
    issues.push('selectedHolidays');
  }

  if (!state.currencyBuy) issues.push('currencyBuy');
  if (!state.currencySell) issues.push('currencySell');

  return issues;
}
