export type RowType = 'provider' | 'service' | 'plan';

export interface PricingPlanRow {
  key: string;
  type: RowType;

  provider?: string;
  product?: string;
  service?: string;

  city?: string;
  category?: string;
  cost?: number;
  currency?: string;

  tariffType?: string;
  validity?: string;

  children?: PricingPlanRow[];
}
