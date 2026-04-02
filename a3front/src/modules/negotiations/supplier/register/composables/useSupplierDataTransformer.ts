import { useSupplierFormStoreFacade } from '@/modules/negotiations/supplier/register/composables/useSupplierFormStoreFacade';
import { useLocationStore } from '@/modules/negotiations/supplier/register/store/locationStore';
import {
  parseKeyOperationLocation,
  joinKeyOperationLocation,
} from '@/modules/negotiations/supplier/register/helpers/operationLocationHelper';

import type {
  FormOperationLocation,
  SupplierResponse,
  RequestOperationLocation,
  RequestClassification,
  ClassificationResponse,
  OperationResponse,
} from '@/modules/negotiations/supplier/interfaces';

export function useSupplierDataTransformer() {
  const { formStateNegotiation, supplierLocationsUpdate } = useSupplierFormStoreFacade();

  const { getLocationsByZone } = useLocationStore();

  // crear request para guardar/actualizar proveedor
  const buildRequestPayload = (supplierId?: number) => {
    const isRequestEdit = !!supplierId;

    const { countryId, stateId, cityId, zoneId } = parseKeyOperationLocation(
      formStateNegotiation.comercialLocation,
      '-'
    );

    return {
      authorized_management: formStateNegotiation.authorizedManagement,
      code: `${formStateNegotiation.cityCode}${formStateNegotiation.supplierCode}`,
      name: formStateNegotiation.name,
      business_name: formStateNegotiation.businessName,
      belongs_company: formStateNegotiation.belongsCompany,
      document: formStateNegotiation.rucNumber,
      is_business: true,
      fiscal_address: formStateNegotiation.fiscalAddress,
      service_charges: formStateNegotiation.applyServicePercentage
        ? formStateNegotiation.serviceCharges
        : 0,
      commission_charges: formStateNegotiation.applyCommissionPercentage
        ? formStateNegotiation.commissionCharges
        : 0,
      financial_charges: formStateNegotiation.applyFinancialExpenses,
      commercial_address: formStateNegotiation.commercialAddress,
      country_id: countryId,
      state_id: stateId,
      city_id: cityId,
      zone_id: zoneId,
      observations: formStateNegotiation.observations || null,
      phone: formStateNegotiation.phone,
      email: formStateNegotiation.email,
      belongs_state: false,
      classifications: buildRequestClassifications(isRequestEdit),
    };
  };

  const buildRequestClassifications = (isRequestEdit: boolean): RequestClassification[] => {
    return formStateNegotiation.supplierClassifications.map((classification) => {
      const supplierSubClassificationId = Number(classification);
      return {
        supplier_sub_classification_id: supplierSubClassificationId,
        operations: isRequestEdit
          ? buildEditRequestOperations(supplierSubClassificationId)
          : buildNewRequestOperations(),
      };
    });
  };

  const buildEditRequestOperations = (
    supplierSubClassificationId: number
  ): RequestOperationLocation[] => {
    // Verificar si existe la subclasificación
    const existsClassification = supplierLocationsUpdate.value.some(
      (row) => row.supplier_sub_classification_id === supplierSubClassificationId
    );

    // Si la clasificación es nueva, retornar operaciones nuevas
    if (!existsClassification) {
      return buildNewRequestOperations();
    }

    // Lugares de operación para clasificaciones existentes y edición
    const editOperationLocations = supplierLocationsUpdate.value
      .filter((row) => row.supplier_sub_classification_id === supplierSubClassificationId)
      .map((row) => ({ ...row }));

    // Lugares de operación nuevos para clasificaciones existentes
    const newOperationLocations = formStateNegotiation.operationLocations
      .filter(
        (operationLocation) =>
          // Condicion para validar que se trata de un registro nuevo
          operationLocation.supplierBranchOfficeIds.length === 0
      )
      .map(mapFormOperationLocation);

    return [...editOperationLocations, ...newOperationLocations];
  };

  const buildNewRequestOperations = (): RequestOperationLocation[] => {
    return formStateNegotiation.operationLocations.map(mapFormOperationLocation);
  };

  // Función para mapear un operationLocation a RequestOperationLocation
  const mapFormOperationLocation = (
    operationLocation: FormOperationLocation
  ): RequestOperationLocation => {
    const { countryId, stateId, cityId } = parseKeyOperationLocation(
      operationLocation.primaryLocationKey ?? '',
      '-'
    );
    const zoneId = operationLocation.zoneKey ? Number(operationLocation.zoneKey) : null;

    return {
      country_id: countryId,
      state_id: stateId,
      city_id: cityId,
      zone_id: zoneId,
    };
  };

  // Asignar datos - edicion
  const setSupplierToForm = (supplier: SupplierResponse): void => {
    formStateNegotiation.classifications = supplier.classifications;
    formStateNegotiation.cityCode = supplier.code.substring(0, 3);
    formStateNegotiation.supplierCode = supplier.code.substring(3);
    formStateNegotiation.observations = supplier.observations ?? '';
    formStateNegotiation.authorizedManagement = Boolean(supplier.authorized_management);
    formStateNegotiation.businessName = supplier.business_name;
    formStateNegotiation.name = supplier.name;
    formStateNegotiation.supplierClassifications = prepareSupplierClassifications(
      supplier.classifications
    );
    formStateNegotiation.rucNumber = supplier.document;
    formStateNegotiation.belongsCompany = Boolean(supplier.belongs_company);
    formStateNegotiation.fiscalAddress = supplier.fiscal_address;
    formStateNegotiation.applyServicePercentage = (supplier.service_charges ?? 0) > 0;
    formStateNegotiation.serviceCharges = supplier.service_charges;
    formStateNegotiation.applyCommissionPercentage = (supplier.commission_charges ?? 0) > 0;
    formStateNegotiation.commissionCharges = supplier.commission_charges;
    formStateNegotiation.applyFinancialExpenses = supplier.financial_charges;
    formStateNegotiation.phone = supplier.phone;
    formStateNegotiation.email = supplier.email;
    formStateNegotiation.commercialAddress = supplier.commercial_address;
    formStateNegotiation.comercialLocation = prepareComercialLocation(supplier);
    formStateNegotiation.operationLocations = prepareOperationLocations(supplier.classifications);

    prepareSupplierLocationsUpdate(supplier.classifications);
  };

  const prepareSupplierLocationsUpdate = (classifications: ClassificationResponse[]) => {
    supplierLocationsUpdate.value = classifications
      .map((row) => {
        return row.operations.map((operation) => {
          return {
            supplier_sub_classification_id: row.supplier_sub_classification_id,
            supplier_branch_office_id: operation.supplier_branch_office_id,
            country_id: operation.country.id,
            state_id: operation.state.id,
            city_id: operation.city?.id ?? null,
            zone_id: operation.zone?.id ?? null,
          };
        });
      })
      .flat();
  };

  const prepareOperationLocations = (classifications: ClassificationResponse[]) => {
    const operationLocations: FormOperationLocation[] = [];

    classifications.forEach((classification) => {
      classification.operations.forEach((operation) => {
        processOperationLocation(
          classification.supplier_sub_classification_id,
          operation,
          operationLocations
        );
      });
    });

    return operationLocations;
  };

  const processOperationLocation = (
    supplierSubClassificationId: number,
    operation: OperationResponse,
    operationLocations: FormOperationLocation[]
  ) => {
    const countryId = operation.country.id;
    const stateId = operation.state.id;
    const cityId = operation?.city?.id;

    const primaryLocationKey = joinKeyOperationLocation('-', countryId, stateId, cityId);

    const zoneKey = operation?.zone?.id ? operation.zone.id.toString() : null;

    const existsOperationLocation = operationLocations.find((item) => {
      return item.primaryLocationKey === primaryLocationKey && item.zoneKey === zoneKey;
    });

    if (existsOperationLocation) {
      existsOperationLocation.supplierBranchOfficeIds.push(operation.supplier_branch_office_id);
    } else {
      operationLocations.push({
        primaryLocationKey,
        zoneKey,
        locationOptionsByZone: prepareLocationOptionsByZone(countryId, stateId, cityId),
        supplierBranchOfficeIds: [operation.supplier_branch_office_id],
      });
    }
  };

  const prepareLocationOptionsByZone = (countryId: number, stateId: number, cityId?: number) => {
    if (countryId && stateId && cityId) {
      return getLocationsByZone(countryId, stateId, cityId);
    }

    return [];
  };

  const prepareSupplierClassifications = (classifications: ClassificationResponse[]): string[] => {
    return classifications.map((row: any) => {
      return row.supplier_sub_classification_id;
    });
  };

  const prepareComercialLocation = (supplier: SupplierResponse): string => {
    const { country, state, city, zone } = supplier;

    return joinKeyOperationLocation('-', country.id, state.id, city?.id, zone?.id);
  };

  return {
    setSupplierToForm,
    buildRequestPayload,
  };
}
