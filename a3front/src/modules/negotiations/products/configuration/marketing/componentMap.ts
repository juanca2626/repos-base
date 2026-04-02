import { defineAsyncComponent } from 'vue';
import type { ServiceType } from '../types/index';

type MarketingComponentMap = {
  [K in ServiceType]: Record<string, ReturnType<typeof defineAsyncComponent>>;
};

export const marketingComponentMap: MarketingComponentMap = {
  GENERIC: {
    content: defineAsyncComponent(
      () => import('./generic/content/components/GenericContentMarketingComponent.vue')
    ),
    translations: defineAsyncComponent(
      () => import('./generic/translations/components/GenericTranslationsMarketingComponent.vue')
    ),
    images: defineAsyncComponent(
      () => import('./generic/images/components/GenericImageMarketingComponent.vue')
    ),
  },
  TRAIN: {
    content: defineAsyncComponent(
      () => import('./train/content/components/TrainContentMarketingComponent.vue')
    ),
    translations: defineAsyncComponent(
      () => import('./train/translations/components/TrainTranslationsMarketingComponent.vue')
    ),
    images: defineAsyncComponent(
      () => import('./train/images/components/TrainImageMarketingComponent.vue')
    ),
  },
  PACKAGE: {
    content: defineAsyncComponent(
      () => import('./package/content/components/PackageContentMarketingComponent.vue')
    ),
    translations: defineAsyncComponent(
      () => import('./package/translations/components/PackageTranslationsMarketingComponent.vue')
    ),
    images: defineAsyncComponent(
      () => import('./package/images/components/PackageImageMarketingComponent.vue')
    ),
  },
};
