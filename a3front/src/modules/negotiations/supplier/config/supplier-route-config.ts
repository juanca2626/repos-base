import { SupplierSubClassificationsEnum } from '@/utils/supplierSubClassifications.enum';
import type { SupplierRouteActionMap } from '@/modules/negotiations/supplier/interfaces';
import { ResourceActionTypeEnum } from '@/modules/negotiations/supplier/enums/resource-action-type.enum';

export const supplierRouteActionsMap: SupplierRouteActionMap = {
  [SupplierSubClassificationsEnum.TOURIST_TRANSPORT]: {
    create: 'supplierTouristTransportRegister',
    edit: 'supplierTouristTransportEdit',
    list: 'supplierTouristTransportList',
  },
  [SupplierSubClassificationsEnum.MUSEUMS]: {
    createClassification: 'supplierTicketRegisterClassification',
    editClassification: 'supplierTicketEditClassification',
    create: 'supplierTicketRegister',
    edit: 'supplierTicketEdit',
    list: 'suppliersTicketsList',
  },
};

export function getSupplierRouteName(
  supplierSubClassification: SupplierSubClassificationsEnum,
  action: ResourceActionTypeEnum
): string {
  const supplierRoute = supplierRouteActionsMap[supplierSubClassification];
  if (!supplierRoute) {
    throw new Error(
      `SupplierRoute with supplierSubClassification ${supplierSubClassification} not found`
    );
  }
  return supplierRoute[action] || '';
}
