import { isBasicComplete } from './rules/isBasicComplete';
import { isStaffComplete } from './rules/isStaffComplete';
import { isPricesComplete } from './rules/isPricesComplete';
import { isQuotaComplete } from './rules/isQuotaComplete';

export function stepCompletionRules(state: any) {
  return {
    basic: isBasicComplete(state),
    staff: isStaffComplete(state),
    prices: isPricesComplete(state),
    quota: isQuotaComplete(state),
  };
}
