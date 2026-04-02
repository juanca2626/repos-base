import type { Configuration } from '../models/Configuration.model';
import type { BackendConfigurationResponse } from '../types/configuration.backend.types';

export interface SaveConfigurationStrategy {
  execute(
    productSupplierId: string,
    configuration: Configuration
  ): Promise<BackendConfigurationResponse | null>;
}
