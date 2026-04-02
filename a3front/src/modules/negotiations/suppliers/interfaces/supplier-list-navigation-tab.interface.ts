import type { SupplierListRouteNameEnum } from '@/modules/negotiations/suppliers/enums/supplier-list-route-name.enum';

export interface SupplierNavigationTab {
  key: SupplierListRouteNameEnum;
  label: string;
}

export interface SupplierNavigationTabGroup {
  ['TRANSPORT']: SupplierNavigationTab[];
  ['TOUR_OPERATOR']: SupplierNavigationTab[];
}
