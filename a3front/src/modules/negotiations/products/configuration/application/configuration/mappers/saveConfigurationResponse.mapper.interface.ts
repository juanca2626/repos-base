import type { FetchConfigurationModel } from '../../../domain/configuration/models/fetchConfiguration.model';
import type { BackendConfigurationResponse } from '../../../domain/configuration/types/configuration.backend.types';

export interface SaveConfigurationResponseMapper {
  map(response: BackendConfigurationResponse): FetchConfigurationModel;
}
