interface ViewDataConfig {
  configureAs: string;
  subClassificationDescription: string;
}

export interface ViewDataMap {
  [key: number]: ViewDataConfig;
}
