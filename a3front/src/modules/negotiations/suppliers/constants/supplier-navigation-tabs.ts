import type { SupplierNavigationTabGroup } from '@/modules/negotiations/suppliers/interfaces';
import { SupplierListRouteNameEnum } from '@/modules/negotiations/suppliers/enums/supplier-list-route-name.enum';

export const navigationTabsByGroup: SupplierNavigationTabGroup = {
  TRANSPORT: [
    { key: SupplierListRouteNameEnum.AIRLINE, label: 'Aerolíneas' },
    { key: SupplierListRouteNameEnum.WATER_TRANSPORT, label: 'Lanchas' },
    { key: SupplierListRouteNameEnum.LAND_TRANSPORT, label: 'Transporte terrestre' },
    { key: SupplierListRouteNameEnum.TRAIN, label: 'Trenes' },
  ],
  TOUR_OPERATOR: [
    { key: SupplierListRouteNameEnum.CRUISE, label: 'Cruceros' },
    { key: SupplierListRouteNameEnum.LODGES, label: 'Lodges' },
    { key: SupplierListRouteNameEnum.LOCAL_OPERATOR, label: 'Operadores locales' },
  ],
};
