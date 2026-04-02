export interface MultiDayConfigurationItem {
  programDurationCode: string;
  operationalSeasonCodes: string[];
}

export interface MultiDayConfigurationRequest {
  configurations: MultiDayConfigurationItem[];
}
