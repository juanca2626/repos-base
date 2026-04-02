export interface Market {
  name: string;
  code: string;
}

export interface Client {
  name: string;
  code: string;
}

export interface Party {
  name: string;
  code: string;
}

export interface Season {
  name: string;
  code: string;
}

export interface SaveBasicPricingDto {
  id: string | null;
  productSupplierId: string;
  serviceDetailId: string;
  rateType: string;
  name: string;

  promotion: {
    name?: string;
  };

  bookingCode: {
    required: boolean;
    code: string;
  };

  validity: {
    from: string;
    to: string;
  };

  bookingWindow: {
    isCustom: boolean;
    from: string | null;
    to: string | null;
  };

  currencyConfig: {
    purchase: string;
    sales: string;
  };

  rulesInput?: {
    seasons: any[];
    specificDates: any[];
  };

  segmentation?: {
    markets: Market[];
    clients: Client[];
    series: String | null;
    parties: Party[];
    seasons: Season[];
  };

  dayConfig: {
    hasDifferentialPricing: boolean;
    selectedDifferentialDays: String[];
    standardDays: String[];
  };

  holidayConfig: {
    includesHolidays: boolean;
    groups: any[];
  };
}
