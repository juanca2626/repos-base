import type { SaveConfigurationStrategy } from '@/modules/negotiations/products/configuration/domain/configuration/strategies/saveConfiguration.strategy';
import type { Configuration } from '@/modules/negotiations/products/configuration/domain/configuration/models/Configuration.model';

import { savePackageConfigurationService } from '@/modules/negotiations/products/configuration/infrastructure/configuration/savePackageConfiguration.service';
import { mapToSaveConfigurationRequest } from '@/modules/negotiations/products/configuration/infrastructure/configuration/mappers/saveConfiguration.mapper';
import type { BackendConfigurationResponse } from '../types/configuration.backend.types';

export class SavePackageConfigurationStrategy implements SaveConfigurationStrategy {
  async execute(
    productSupplierId: string,
    configuration: Configuration
  ): Promise<BackendConfigurationResponse> {
    const payload = mapToSaveConfigurationRequest(configuration);

    const response = await savePackageConfigurationService(productSupplierId, payload);

    return response?.data ?? [];
  }
}
