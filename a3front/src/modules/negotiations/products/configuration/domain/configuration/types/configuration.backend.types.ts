export interface BackendCountry {
  code: string;
  name: string;
}

export interface BackendState {
  code: string;
  name: string;
}

export interface BackendOperatingLocation {
  key: string;
  country: BackendCountry;
  state: BackendState;
}

export interface BackendBehaviorSetting {
  supplierCategoryCode: string;
  mode: 'COMPONENT' | 'SIMPLE';
}

export interface BackendConfigurationItem {
  id: string;
  operatingLocation: BackendOperatingLocation;
  applyGeneralBehavior: boolean;
  behaviorSettings: BackendBehaviorSetting[];
}

// GENERIC
export interface BackendGenericConfigurationItem {
  id: string;
  operatingLocation: {
    key: string;
    country: { code: string; name: string };
    state: { code: string; name: string };
  };
  applyGeneralBehavior: boolean;
  behaviorSettings: {
    supplierCategoryCode: string;
    mode: 'COMPONENT' | 'SIMPLE';
  }[];
}

// TRAIN
export interface BackendTrainConfigurationItem {
  id: string;
  operatingLocation: {
    key: string;
    country: { code: string; name: string };
    state: { code: string; name: string };
    city?: { code: string | null; name: string };
  };
  trainTypeCodes: string[];
}

// PACKAGE
export interface BackendPackageConfigurationItem {
  id: string;
  programDurationCode: string;
  operationalSeasonCodes: string[];
}

export type BackendConfigurationResponse =
  | BackendGenericConfigurationItem[]
  | BackendTrainConfigurationItem[]
  | BackendPackageConfigurationItem[];
