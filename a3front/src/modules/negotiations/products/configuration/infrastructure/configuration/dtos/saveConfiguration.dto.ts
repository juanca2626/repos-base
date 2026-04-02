export interface SaveConfigurationRequest {
  configurations: (ConfigurationDto | ProgramDurationDto)[];
}

export interface ConfigurationDto {
  operatingLocation: {
    key: string;
    country: {
      code: string;
      name: string;
    };
    state?: {
      code?: string | null;
      name?: string | null;
    };
    city?: {
      code?: string | null;
      name?: string | null;
    };
  };
  applyGeneralBehavior?: boolean;
  behaviorSettings?: BehaviorSettingDto[];
  trainTypeCodes?: string[];
}

export interface BehaviorSettingDto {
  supplierCategoryCode: string;
  mode: 'COMPONENT' | 'SIMPLE';
}

export interface ProgramDurationDto {
  programDurationCode: string;
  operationalSeasonCodes: string[];
}
