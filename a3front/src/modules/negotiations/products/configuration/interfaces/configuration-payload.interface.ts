export interface OperatingLocationCountry {
  code: string;
  name: string;
}

export interface OperatingLocationState {
  code: string;
  name: string;
}

export interface OperatingLocationCity {
  code: string | null;
  name: string;
}

export interface OperatingLocation {
  key: string;
  country: OperatingLocationCountry;
  state: OperatingLocationState;
  city?: OperatingLocationCity;
}

export type BehaviorMode = 'SIMPLE' | 'COMPONENT';

export interface BehaviorMatrixItem {
  operatingLocationKey: string;
  supplierCategoryCode: string;
  mode: BehaviorMode;
}

export interface BehaviorSetting {
  applyGeneralBehavior: boolean;
  matrix: BehaviorMatrixItem[];
}

export interface ConfigurationPayload {
  operatingLocations: OperatingLocation[];
  supplierCategoryCodes: string[];
  behaviorSetting: BehaviorSetting;
}
