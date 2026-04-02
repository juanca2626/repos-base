import type { Step } from '../types/stepRegistry.interface';
import type { StepKey } from '../types/pricingPlan.types';

export function getNextStepKey(steps: Step[], currentStepKey: StepKey): StepKey | null {
  const index = steps.findIndex((step) => step.key === currentStepKey);

  if (index === -1 || index === steps.length - 1) return null;

  return steps[index + 1].key;
}

export function getStepIndexByKey(steps: Step[], stepKey: StepKey): number {
  return steps.findIndex((step) => step.key === stepKey);
}
