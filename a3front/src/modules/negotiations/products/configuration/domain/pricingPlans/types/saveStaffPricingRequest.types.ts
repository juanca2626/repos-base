export type StaffTax = {
  igv: boolean;
  services: boolean;
  other: boolean;
};

export type StaffId = string;
export type StaffTaxKey = string;

export type AdditionalPercentage = {
  id: string;
  name: string;
  percentage: number | null;
};

export interface StaffTaxesByType {
  [taxKey: StaffTaxKey]: boolean;
}

export interface StaffTaxesMap {
  [staffId: StaffId]: StaffTaxesByType;
}

export interface StaffTaxDefinition {
  key: StaffTaxKey;
  label: string;
  kind: 'igv' | 'service' | 'additional';
  additionalId?: string;
}

export interface SaveStaffPricingRequest {
  taxes: {
    labelTax: string | null;
    affectedByIGV: boolean;
    igvPercent: number | null;
    igvRecovery: boolean;
    igvRecoveryPercent: number | null;
    servicePercentage: number | null;
    additionalPercentage: boolean;
    additionalPercentages: AdditionalPercentage[];
  };

  staff?: {
    selectedStaff: string[];
    staffTaxes: StaffTaxesMap;
  };

  commissions: null | {
    percentage: number | null;
  };
}

export interface StaffOption {
  value: string;
  label: string;
}
