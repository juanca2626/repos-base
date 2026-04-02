import type { SaveConfigurationResponseMapper } from './saveConfigurationResponse.mapper.interface';
import type { FetchConfigurationModel } from '../../../domain/configuration/models/fetchConfiguration.model';
import type { BackendConfigurationResponse } from '../../../domain/configuration/types/configuration.backend.types';

export class GenericSaveResponseMapper implements SaveConfigurationResponseMapper {
  map(response: BackendConfigurationResponse): FetchConfigurationModel {
    const configurations = response ?? [];

    return {
      items: configurations.map((config: any) => ({
        id: config.id,
        key: config.operatingLocation.key,
        name: config.operatingLocation.state.name,
        type: 'GENERIC',
        raw: config,
      })),
    };
  }
}
