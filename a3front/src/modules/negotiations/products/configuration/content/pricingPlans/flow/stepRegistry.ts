import BasicStep from '../ui/steps/basic/BasicStep.vue';
import StaffStep from '../ui/steps/staff/StaffStep.vue';
import PricesStep from '../ui/steps/prices/PricesStep.vue';
import QuotaStep from '../ui/steps/quota/QuotaStep.vue';

import { validateBasicStep } from '../validation/basicValidation';
import { validatePricesStep } from '../validation/pricesValidation';
import { validateQuotaStep } from '../validation/quotaValidation';
import { validateStaffStep } from '../validation/staff/validateStaffByService';

import { saveBasicStep } from '../actions/saveBasicStep';
import { saveStaffStep } from '../actions/saveStaffStep';
import { savePriceStep } from '../actions/savePriceStep';
import { saveQuotaStep } from '../actions/saveQuotaStep';

import { isBasicComplete } from '../engine/rules/isBasicComplete';
import { isStaffComplete } from '../engine/rules/isStaffComplete';
import { isPricesComplete } from '../engine/rules/isPricesComplete';
import { isQuotaComplete } from '../engine/rules/isQuotaComplete';

import { BasicIcon, StaffIcon, PriceIcon, QuotaIcon } from '../icons';
import type { Step } from '../types/stepRegistry.interface';
import type { PricingPlanStep } from '../types/pricingPlan.types';
import type { ServiceType } from '@/modules/negotiations/products/configuration/types/service.type';

export function getStepsByType(_type: ServiceType): Step[] {
  const sharedSteps = [
    {
      key: 'basic' as PricingPlanStep,
      label: 'Datos básicos',
      component: BasicStep,
      icon: BasicIcon,
      validator: (state: any, serviceType: ServiceType) => validateBasicStep(state, serviceType),
      action: (state: any) => saveBasicStep(state),
      isComplete: (state: any) => isBasicComplete(state),
    },
    {
      key: 'staff' as PricingPlanStep,
      label: 'Staff e impuestos',
      component: StaffStep,
      icon: StaffIcon,
      validator: (state: any, serviceType: ServiceType) => validateStaffStep(state, serviceType),
      action: (state: any) => saveStaffStep(state),
      isComplete: (state: any) => isStaffComplete(state),
    },
    {
      key: 'prices' as PricingPlanStep,
      label: 'Montos',
      component: PricesStep,
      icon: PriceIcon,
      validator: (state: any, serviceType: ServiceType) => validatePricesStep(state, serviceType),
      action: (state: any) => savePriceStep(state),
      isComplete: (state: any) => isPricesComplete(state),
    },
    {
      key: 'quota' as PricingPlanStep,
      label: 'Cupos del servicio',
      component: QuotaStep,
      icon: QuotaIcon,
      validator: (state: any, serviceType: ServiceType) => validateQuotaStep(state, serviceType),
      action: (state: any) => saveQuotaStep(state),
      isComplete: (state: any) => isQuotaComplete(state),
    },
  ];

  //  esto se debe cambiar cuando se agreguen los steps para los otros servicios
  // if (type === 'GENERIC') {
  //   return sharedSteps
  // };

  // if (type === 'PACKAGE') {
  //   return sharedSteps;
  // }

  // if (type === 'TRAIN') {
  //   return sharedSteps;
  // }

  return sharedSteps;
}
