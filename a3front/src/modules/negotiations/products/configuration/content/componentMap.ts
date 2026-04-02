import { defineAsyncComponent } from 'vue';
import type { ServiceType } from '../types/index';

type LoadingComponentMap = {
  [K in ServiceType]: Record<string, ReturnType<typeof defineAsyncComponent>>;
};

export const loadingComponentMap: LoadingComponentMap = {
  GENERIC: {
    'service-details': defineAsyncComponent(
      () => import('./generic/serviceDetails/components/ServiceDetailsForm.vue')
    ),
    'capacity-configuration': defineAsyncComponent(
      () => import('./generic/configuration/components/ServiceConfigurationForm.vue')
    ),
    content: defineAsyncComponent(
      () => import('./generic/content/components/ServiceContentForm.vue')
    ),
    'pricing-plans': defineAsyncComponent(
      () => import('./generic/pricingPlans/components/ServicePricingPlansForm.vue')
    ),
    images: defineAsyncComponent(
      () => import('./generic/images/components/GenericImageComponent.vue')
    ),
  },

  TRAIN: {
    'service-details': defineAsyncComponent(
      () => import('./train/serviceDetails/components/TrainServiceDetails.vue')
    ),
    'capacity-configuration': defineAsyncComponent(
      () => import('./train/configuration/components/TrainServiceConfigurationForm.vue')
    ),
    content: defineAsyncComponent(() => import('./train/content/components/TrainContentForm.vue')),
    'pricing-plans': defineAsyncComponent(
      () => import('./train/pricingPlans/components/TrainPricingPlansForm.vue')
    ),
  },

  PACKAGE: {
    'service-details': defineAsyncComponent(
      () => import('./package/serviceDetails/components/MultiDayServiceDetails.vue')
    ),
    'capacity-configuration': defineAsyncComponent(
      () => import('./package/configuration/components/MultiDayConfigurationForm.vue')
    ),
    content: defineAsyncComponent(
      () => import('./package/content/components/MultiDayContentForm.vue')
    ),
    'pricing-plans': defineAsyncComponent(
      () => import('./package/pricingPlans/components/MultiDayPricingPlansForm.vue')
    ),
  },
};
