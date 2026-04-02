import { defineAsyncComponent } from 'vue';
import type { ServiceType } from '../types/index';

type DrawerComponentMap = {
  [K in ServiceType]: Record<string, ReturnType<typeof defineAsyncComponent>>;
};

export const drawerComponentMap: DrawerComponentMap = {
  GENERIC: {
    CREATE: defineAsyncComponent(() => import('./generic/components/GenericCreateDrawer.vue')),
    EDIT: defineAsyncComponent(() => import('./generic/components/GenericEditDrawer.vue')),
    ADD: defineAsyncComponent(() => import('./generic/components/GenericEditCategoryDrawer.vue')),
  },
  TRAIN: {
    CREATE: defineAsyncComponent(() => import('./train/components/TrainCreateDrawer.vue')),
    EDIT: defineAsyncComponent(() => import('./train/components/TrainEditDrawer.vue')),
    ADD: defineAsyncComponent(() => import('./train/components/TrainEditTypeDrawer.vue')),
  },
  PACKAGE: {
    CREATE: defineAsyncComponent(() => import('./package/components/PackageCreateDrawer.vue')),
    EDIT: defineAsyncComponent(() => import('./package/components/PackageEditDrawer.vue')),
    ADD: defineAsyncComponent(() => import('./package/components/PackageEditSeasonDrawer.vue')),
  },
};
