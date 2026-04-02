import type { SaveConfigurationResponseMapper } from './saveConfigurationResponse.mapper.interface';
import type { FetchConfigurationModel } from '../../../domain/configuration/models/fetchConfiguration.model';
import type { BackendConfigurationResponse } from '../../../domain/configuration/types/configuration.backend.types';

export class TrainSaveResponseMapper implements SaveConfigurationResponseMapper {
  map(response: BackendConfigurationResponse): FetchConfigurationModel {
    const configurations = response ?? [];

    return {
      items: configurations.map((config: any) => ({
        id: config.id,
        key: config.stationCode,
        name: config.stationName,
        type: 'TRAIN',
        raw: config,
      })),
    };
  }
}
