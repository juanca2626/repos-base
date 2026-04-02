import type { SaveConfigurationStrategy } from '@/modules/negotiations/products/configuration/domain/configuration/strategies/saveConfiguration.strategy';
import type { Configuration } from '@/modules/negotiations/products/configuration/domain/configuration/models/Configuration.model';

import { saveGenericConfigurationService } from '@/modules/negotiations/products/configuration/infrastructure/configuration/saveGenericConfiguration.service';
import { mapToSaveConfigurationRequest } from '@/modules/negotiations/products/configuration/infrastructure/configuration/mappers/saveConfiguration.mapper';
import type { BackendConfigurationResponse } from '../types/configuration.backend.types';

export class SaveGenericConfigurationStrategy implements SaveConfigurationStrategy {
  async execute(
    productSupplierId: string,
    configuration: Configuration
  ): Promise<BackendConfigurationResponse> {
    const payload = mapToSaveConfigurationRequest(configuration);

    const response = await saveGenericConfigurationService(productSupplierId, payload);

    return response?.data ?? [];
  }
}
