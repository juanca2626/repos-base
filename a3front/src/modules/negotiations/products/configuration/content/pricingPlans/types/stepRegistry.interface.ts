import type { Component } from 'vue';
import type { StepKey } from './pricingPlan.types';
import type { ServiceType } from '@/modules/negotiations/products/configuration/types/service.type';

export interface Step {
  key: StepKey;
  label: string;
  component: Component;
  validator: (
    state: any,
    serviceType: ServiceType
  ) => {
    valid: boolean;
    errors: Record<string, string>;
  };
  action: (state: any) => Promise<boolean>;
  isComplete: (state: any) => boolean;
}
