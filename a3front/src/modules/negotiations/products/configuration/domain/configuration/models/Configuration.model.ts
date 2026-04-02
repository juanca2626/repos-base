export interface Configuration {
  operatingLocations?: OperatingLocation[];
  programDurations?: ProgramDuration[];
}

export interface Location {
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
}

export interface OperatingLocation {
  location: Location;
  applyGeneralBehavior?: boolean;
  behaviorSettings?: BehaviorSetting[];
  trainTypes?: string[];
}

export interface BehaviorSetting {
  supplierCategoryCode: string;
  mode: 'COMPONENT' | 'SIMPLE';
}

export interface ProgramDuration {
  programDurationCode: string;
  operationalSeasonCodes: string[];
}
