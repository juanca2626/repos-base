import type { FetchConfigurationModel } from '../../../domain/configuration/models/fetchConfiguration.model';

export interface SaveConfigurationResponseMapper {
  map(response: unknown): FetchConfigurationModel;
}
