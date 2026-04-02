import type { Tax } from '../../types/tax.types';
import type { PeriodPrice, PassengerPrices, RangePrice } from '../../types/price.types';

export class PriceEngine {
  private getTaxPercent(taxes: Tax[]): number {
    return taxes.reduce((acc, t) => acc + t.percentage, 0);
  }

  private getFactor(taxes: Tax[]) {
    return 1 + this.getTaxPercent(taxes) / 100;
  }

  private netFromTotal(total: number, taxes: Tax[]) {
    return total / this.getFactor(taxes);
  }

  private totalFromNet(net: number, taxes: Tax[]) {
    return net * this.getFactor(taxes);
  }

  private applyDiscount(base: number, enabled?: boolean, percent?: number) {
    if (!enabled || !percent) return base;
    return base * (1 - percent / 100);
  }

  calculatePassengers(passengers: PassengerPrices, taxes: Tax[], includeTaxes: boolean) {
    let adultNet = passengers.adult.netRate ?? 0;
    let adultTotal = passengers.adult.total ?? 0;

    if (includeTaxes) {
      adultNet = this.netFromTotal(adultTotal, taxes);
    } else {
      adultTotal = this.totalFromNet(adultNet, taxes);
    }

    const childNet = this.applyDiscount(
      adultNet,
      passengers.child.discountEnabled,
      passengers.child.discountPercent
    );

    const infantNet = this.applyDiscount(
      adultNet,
      passengers.infant.discountEnabled,
      passengers.infant.discountPercent
    );

    const childTotal = this.totalFromNet(childNet, taxes);
    const infantTotal = this.totalFromNet(infantNet, taxes);

    return {
      adult: { netRate: adultNet, total: adultTotal },
      child: { netRate: childNet, total: childTotal },
      infant: { netRate: infantNet, total: infantTotal },
    };
  }

  recalculateRanges(ranges: RangePrice[], taxes: Tax[], includeTaxes: boolean) {
    for (let i = 0; i < ranges.length; i++) {
      const range = ranges[i];

      if (i === 0) {
        range.rangeFrom = 1;
      } else {
        const prev = ranges[i - 1];
        range.rangeFrom = (prev.rangeTo ?? 0) + 1;
      }

      const result = this.calculatePassengers(range.passengers, taxes, includeTaxes);

      range.passengers.adult = { ...range.passengers.adult, ...result.adult };
      range.passengers.child = { ...range.passengers.child, ...result.child };
      range.passengers.infant = { ...range.passengers.infant, ...result.infant };
    }

    return ranges;
  }

  recalculateUnique(passengers: PassengerPrices, taxes: Tax[], includeTaxes: boolean) {
    return this.calculatePassengers(passengers, taxes, includeTaxes);
  }

  recalculatePeriods(periods: PeriodPrice[], taxes: Tax[]) {
    for (const period of periods) {
      if (period.tariffInputMode === 'UNIQUE') {
        const result = this.recalculateUnique(
          period.pricing.passengers,
          taxes,
          period.includeTaxes
        );

        period.pricing.passengers.adult = { ...period.pricing.passengers.adult, ...result.adult };
        period.pricing.passengers.child = { ...period.pricing.passengers.child, ...result.child };
        period.pricing.passengers.infant = {
          ...period.pricing.passengers.infant,
          ...result.infant,
        };
      }

      if (period.tariffInputMode === 'RANGE') {
        period.pricing.ranges = this.recalculateRanges(
          period.pricing.ranges,
          taxes,
          period.includeTaxes
        );
      }
    }

    return periods;
  }
}
