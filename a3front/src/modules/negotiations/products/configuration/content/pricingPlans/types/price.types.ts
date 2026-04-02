import type { StatusTariff } from '@/modules/negotiations/products/configuration/enums/statusTariff.enum';
import type { RateVariation } from '../domain/models/RateVariation';

export interface Tax {
  name: string;
  percentage: number;
}

export interface PassengerPrice {
  netRate: number | null;
  total: number | null;
  discountEnabled?: boolean;
  discountPercent?: number;
}

export interface PassengerPrices {
  adult: PassengerPrice;
  child: PassengerPrice;
  infant: PassengerPrice;
}

export interface RangePrice {
  rangeFrom: number | null;
  rangeTo: number | null;
  passengers: PassengerPrices;
}

export interface PeriodPrice {
  periodId: string;
  ratePlanId: string;
  type: 'STANDARD' | 'WEEKEND' | 'HOLIDAY';
  priority: number;
  label: string;
  dateDisplay: string;
  refGroupId: string | null;
  pricing: {
    passengers: PassengerPrices;
    ranges: RangePrice[];
  };
  frequencies: String[] | null;
  pricingStatus: StatusTariff;
  status: 'NOT_STARTED' | 'IN_PROGRESS' | 'COMPLETED' | 'FAILED';
  // PARA USO EN EL UI
  tariffInputMode: 'UNIQUE' | 'RANGE';
  includeTaxes: boolean;
  policies: string[];
}

export interface Catalogs {
  frequencies: any[];
}

export interface PriceState {
  isLoadingRateVariations: boolean;
  rateVariations: RateVariation[];
  selectedRateVariationId: string | null;
  catalogs: Catalogs;
}
