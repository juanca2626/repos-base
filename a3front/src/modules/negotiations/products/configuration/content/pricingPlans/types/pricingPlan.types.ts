export type PricingPlanStep = 'basic' | 'staff' | 'prices' | 'quota';

export type StepKey = 'basic' | 'prices' | 'quota' | 'staff';

export type StepErrors = Record<string, string>;

export type ErrorsState = {
  basic: StepErrors;
  staff: StepErrors;
  prices: StepErrors;
  quota: StepErrors;
};
