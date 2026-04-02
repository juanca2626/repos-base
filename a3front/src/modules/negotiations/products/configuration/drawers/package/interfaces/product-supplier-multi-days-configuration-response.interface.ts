export interface operationalSeasonCodeRequest {
  operationalSeasonCodes: String[];
}

export interface ProductSupplierMultiDaysConfiguration {
  id?: string;
  programDurationCode: string;
  operationalSeasonCodes: String[];
}

export interface ProductSupplierMultiDaysConfigurationResponse {
  configurations: ProductSupplierMultiDaysConfiguration[];
}
