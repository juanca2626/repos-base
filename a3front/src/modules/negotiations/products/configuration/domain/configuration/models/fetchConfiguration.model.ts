import type { ServiceType } from '../../../types/index';

export interface ConfigurationItem {
  id: string;
  key: string;
  name: string;
  type: ServiceType;
  raw: unknown;
}

export interface FetchConfigurationModel {
  items: ConfigurationItem[];
}
