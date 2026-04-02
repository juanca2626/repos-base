import { validateBasicStep } from './basicValidation';
import { validatePricesStep } from './pricesValidation';
import { validateQuotaStep } from './quotaValidation';
import { validateStaffStep } from './staff/validateStaffByService';

export function validatePricingPlanStep(step: string, state: any) {
  const validators: Record<string, Function> = {
    basic: validateBasicStep,
    prices: validatePricesStep,
    quota: validateQuotaStep,
    staff: validateStaffStep,
  };

  const validator = validators[step];

  if (!validator) {
    console.warn(`No validator found for step: ${step}`);
    return { valid: true, errors: {} };
  }

  return validator(state);
}
