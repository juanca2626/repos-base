import type { TariffType } from '@/modules/negotiations/products/configuration/enums/tariffType.enum';
import type { PricingPeriod, Holiday, Segmentation, Market, Client } from './pricingCommon.types';

export interface SaveBasicPricingRequest {
  name: string;
  tariffType: TariffType;

  requiresBookingCode: boolean;
  bookingCode: string | null;

  travelFrom: string;
  travelTo: string;

  modifyBookingPeriod: boolean;
  bookingFrom: string | null;
  bookingTo: string | null;

  currencyBuy: string;
  currencySell: string;

  periods: PricingPeriod[];

  differentiatedTariff: boolean;
  selectedDays: String[];
  standardDays: String[];

  includeHolidayTariffs: boolean;
  selectedHolidays: String[] | Holiday[];

  promotionName?: string;

  tariffSegmentation: Segmentation[];
  specificMarkets: Market[];
  specificClients: Client[];
  specificSeries: String | null;
}

export interface SaveBasicPricingRequestBeforeMapping {
  name: string;
  tariffType: TariffType;

  requiresBookingCode: boolean;
  bookingCode: string | null;

  travelFrom: string;
  travelTo: string;

  modifyBookingPeriod: boolean;
  bookingFrom: string | null;
  bookingTo: string | null;

  currencyBuy: string;
  currencySell: string;

  periods: PricingPeriod[];

  differentiatedTariff: boolean;
  selectedDays: String[];
  standardDays: String[];

  includeHolidayTariffs: boolean;
  selectedHolidays: String[] | Holiday[];

  promotionName?: string;

  tariffSegmentation: String[];
  specificMarkets: String[];
  specificClients: String[];
  specificSeries: String | null;
}
