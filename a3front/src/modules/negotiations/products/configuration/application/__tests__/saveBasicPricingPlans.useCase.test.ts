import { describe, it, expect, vi } from 'vitest';
import { saveBasicPricingUseCase } from '../pricingPlans/saveBasicPricing.useCase';

import * as strategyResolver from '../pricingPlans/resolvers/saveBasicPricingResolver';
import * as mapperResolver from '../pricingPlans/resolvers/saveBasicPricingResponseMapperResponse';

describe('saveBasicPricingUseCase', () => {
  it('should call save service for GENERIC service', async () => {
    const mockExecute = vi.fn().mockResolvedValue({ status: 200 });

    const data = {
      name: 'Tarifa Test',
      tariffType: 'FLAT',
      requiresBookingCode: false,
      bookingCode: '',
      travelFrom: '2026-01-01',
      travelTo: '2026-12-31',
      modifyBookingPeriod: false,
      bookingFrom: null,
      bookingTo: null,
      currencyBuy: 'USD',
      currencySell: 'USD',
      periods: [],
      differentiatedTariff: false,
      includeHolidayTariffs: false,
      selectedHolidays: [],
      selectedDays: [],
      promotionName: [],
      tariffSegmentation: [],
      specificMarkets: [],
      specificClients: [],
      specificSeries: [],
    };

    vi.spyOn(strategyResolver, 'resolveSaveBasicPricingStrategy').mockReturnValue({
      execute: mockExecute,
    } as any);

    vi.spyOn(mapperResolver, 'resolveSaveBasicPricingResponseMapper').mockReturnValue({
      map: vi.fn().mockReturnValue({ success: true }),
    } as any);

    const result = await saveBasicPricingUseCase(
      'GENERIC',
      '69610e9881204419d8fd3a64',
      '69a99a6901ee5f90ded44c06',
      data
    );

    expect(mockExecute).toHaveBeenCalled();

    expect(result).toEqual({ success: true });
  });

  it.each(['GENERIC', 'TRAIN', 'PACKAGE'])(
    'should execute strategy for %s service type',
    async (serviceType) => {
      const mockExecute = vi.fn().mockResolvedValue({ status: 200 });

      vi.spyOn(strategyResolver, 'resolveSaveBasicPricingStrategy').mockReturnValue({
        execute: mockExecute,
      } as any);

      vi.spyOn(mapperResolver, 'resolveSaveBasicPricingResponseMapper').mockReturnValue({
        map: vi.fn().mockReturnValue({ success: true }),
      } as any);

      const result = await saveBasicPricingUseCase(
        serviceType as any,
        '69610e9881204419d8fd3a64',
        '69a99a6901ee5f90ded44c06',
        {}
      );

      expect(mockExecute).toHaveBeenCalled();

      expect(result).toEqual({ success: true });
    }
  );

  it('should handle empty response', async () => {
    const mockExecute = vi.fn().mockResolvedValue(undefined);

    vi.spyOn(strategyResolver, 'resolveSaveBasicPricingStrategy').mockReturnValue({
      execute: mockExecute,
    } as any);

    vi.spyOn(mapperResolver, 'resolveSaveBasicPricingResponseMapper').mockReturnValue({
      map: vi.fn().mockReturnValue({ success: false }),
    } as any);

    const result = await saveBasicPricingUseCase('GENERIC', 'supplier123', 'service456', {});

    expect(result).toEqual({ success: false });
  });
});
