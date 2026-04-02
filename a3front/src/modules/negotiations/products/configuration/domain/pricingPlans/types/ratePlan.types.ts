import type {
  Market,
  Client,
  Season,
  Segmentation,
  Holiday,
  groupHoliday,
} from './pricingCommon.types';

export interface AdditionalFee {
  id?: string;
  name: string;
  value: number | null;
}

export interface StaffTax {
  feeId: string;
  isAffected: boolean;
}

export interface StaffRule {
  role: string;
  label: string;
  taxSettings: StaffTax[];
}

export interface RatePlan {
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

  taxAndStaffConfig?: {
    globalDefinitions: {
      mainTax: {
        isAffected: boolean;
        label: string;
        value: number | null;
        hasRecovery: boolean;
      };
      serviceFee: {
        isAffected: boolean;
        value: number | null;
      };
      additionalFees: AdditionalFee[];
    };

    staffRules: StaffRule[];

    commissions: {
      percentage: number | null;
    };
  };

  rateVariations?: any[];
}
