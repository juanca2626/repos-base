import { describe, it, expect, vi, beforeEach } from 'vitest';

import { loadExternalHolidaysUseCase } from '../holidays/loadExternalHolidays.useCase';

import type { HolidayService } from '@/modules/negotiations/products/configuration/domain/holidays/services/holiday.service.interface';
import type { HolidaysResponse } from '@/modules/negotiations/products/configuration/domain/holidays/models/holidaysResponse.model';

describe('loadExternalHolidaysUseCase', () => {
  let holidayService: HolidayService;

  beforeEach(() => {
    holidayService = {
      loadExternalHolidays: vi.fn(),
    };
  });

  it('should call holidayService with params', async () => {
    const params = {
      country: 'PE',
      city: 'LIM',
      dateFrom: '2026-01-01',
      dateTo: '2030-12-31',
    };

    const mockResponse: HolidaysResponse = {
      country: {
        id: 89,
        name: 'Perú',
        code: 'PE',
      },
      holidays: [],
    };

    (holidayService.loadExternalHolidays as any).mockResolvedValue(mockResponse);

    const result = await loadExternalHolidaysUseCase(params, holidayService);

    expect(holidayService.loadExternalHolidays).toHaveBeenCalledWith(params);

    expect(result).toEqual(mockResponse);
  });

  it('should work without city parameter', async () => {
    const params = {
      country: 'PE',
      dateFrom: '2026-01-01',
      dateTo: '2030-12-31',
    };

    const mockResponse: HolidaysResponse = {
      country: {
        id: 89,
        name: 'Perú',
        code: 'PE',
      },
      holidays: [],
    };

    (holidayService.loadExternalHolidays as any).mockResolvedValue(mockResponse);

    const result = await loadExternalHolidaysUseCase(params, holidayService);

    expect(holidayService.loadExternalHolidays).toHaveBeenCalledWith(params);
    expect(result).toEqual(mockResponse);
  });
});
