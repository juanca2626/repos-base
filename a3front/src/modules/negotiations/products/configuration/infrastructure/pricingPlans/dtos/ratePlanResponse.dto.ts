export interface PricingPeriod {
  periodId: string;
  periodType: string;
  periodName?: string;
  ranges: {
    dateFrom: string;
    dateTo: string;
  }[];
}

export interface Holiday {
  id?: string;
  externalId: string;
  currentName: string;
  date: string;
}

export interface holidayHistory {
  event: string;
  timestamp: string;
  author: string;
  diff: {
    before: any;
    after: any;
  };
}

export interface dateHoliday {
  id?: string;
  uuid?: string;
  externalId: string;
  name: string;
  apiType: string;
  apiDate: string;
  isActive: boolean;
  isModified: boolean;
  moveInfo: {
    originGroupKey: string;
    currentGroupKey: string;
    movedAt: string;
  };
  isNewFromApi: boolean;
  expandedDates: String[];
  history: holidayHistory[];
}

export interface groupHoliday {
  id?: string;
  uuid?: string;
  icon?: string;
  color?: string;
  priority: number;
  key: string;
  label: string;
  isActive: boolean;
  dates: dateHoliday[];
}

export interface Segmentation {
  id?: string;
  name: string;
  code: string;
}

export interface Market {
  id?: string;
  name: string;
  code: string;
}

export interface Season {
  id?: string;
  name: string;
  code: string;
}

export interface Client {
  id?: string;
  name: string;
  code: string;
}

export interface RatePlanResponseDto {
  id: string;
  productSupplierId: string;
  serviceDetailId: string;
  name: string;
  rateType: string;
  status: string;
  version: number;

  bookingCode?: {
    required: boolean;
    code: string;
  };

  validity?: {
    from: string;
    to: string;
  };

  bookingWindow?: {
    isCustom: boolean;
    from: string | null;
    to: string | null;
  };

  currencyConfig?: {
    purchase: string;
    sales: string;
  };

  rulesInput?: {
    seasons: Season[];
    specificDates: string[];
  };

  segmentation?: {
    markets: Market[];
    clients: Client[];
    series: string | null;
    parties: string[];
    seasons: Segmentation[];
  };

  holidayConfig?: {
    includesHolidays: boolean;
    selectedHolidays: Holiday[];
    groups: groupHoliday[];
  };

  dayConfig?: {
    hasDifferentialPricing: boolean;
    selectedDifferentialDays: String[];
    standardDays: String[];
  };

  promotion?: {
    name: string;
  };

  rateVariations?: any[];
}
