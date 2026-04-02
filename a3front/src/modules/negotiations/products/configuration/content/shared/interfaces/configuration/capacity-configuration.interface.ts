export interface CapacityConfigurationRequest {
  serviceDetailId: string;
  unitOfMeasureCode: string;
  capacity: {
    min: number;
    max: number;
  };
  inclusions: {
    children: boolean;
    infants: boolean;
  };
}

export interface CapacityConfigurationResponse {
  id: string;
  serviceDetailId: string;
  groupingKeys: {
    programDurationCode?: string;
    operatingLocationKey: string;
    supplierCategoryCode?: string;
    trainTypeCode?: string;
    operationalSeasonCode?: string;
  };
  unitOfMeasureCode: string;
  capacity: {
    min: number;
    max: number;
  };
  inclusions: {
    children: boolean;
    infants: boolean;
  };
}
