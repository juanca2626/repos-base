import type { FetchConfigurationModel } from '../models/fetchConfiguration.model';

export interface FetchConfigurationStrategy {
  (data: any): FetchConfigurationModel;
}
