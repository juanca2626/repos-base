import { TypeTechnicalSheetEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/type-technical-sheet.enum';

export const extensionFormDataMap = {
  [TypeTechnicalSheetEnum.TRANSPORT_VEHICLE]: {
    resource: 'vehicle-extensions',
    event: 'reloadTransportVehicleList',
    parentKey: 'supplier_transport_vehicle_id',
    typeDocumentKey: 'type_vehicle_document_id',
  },
  [TypeTechnicalSheetEnum.VEHICLE_DRIVER]: {
    resource: 'driver-extensions',
    event: 'reloadTransportDriverList',
    parentKey: 'supplier_vehicle_driver_id',
    typeDocumentKey: 'type_vehicle_driver_document_id',
  },
};
