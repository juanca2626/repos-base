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

export interface SaveStaffPricingDto {
  taxAndStaffConfig: {
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
}
