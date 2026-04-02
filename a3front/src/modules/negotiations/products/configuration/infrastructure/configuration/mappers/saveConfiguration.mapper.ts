import type { Configuration } from '@/modules/negotiations/products/configuration/domain/configuration/models/Configuration.model';
import type { SaveConfigurationRequest } from '../dtos/saveConfiguration.dto';

export const mapToSaveConfigurationRequest = (
  configuration: Configuration
): SaveConfigurationRequest => {
  let configurations: SaveConfigurationRequest = {
    configurations: [],
  };

  if (configuration.operatingLocations) {
    for (const location of configuration.operatingLocations ?? []) {
      const locationDto = {
        key: location.location.key,
        country: {
          code: location.location.country.code,
          name: location.location.country.name,
        },
        state: {
          code: location.location.state?.code ?? undefined,
          name: location.location.state?.name ?? undefined,
        },
        ...(location.location.city?.name != null && {
          city: {
            code: location.location.city.code ?? undefined,
            name: location.location.city.name,
          },
        }),
      };

      configurations.configurations.push({
        operatingLocation: locationDto,
        applyGeneralBehavior: location.applyGeneralBehavior ?? false,
        behaviorSettings: location.behaviorSettings?.map((behavior) => ({
          supplierCategoryCode: behavior.supplierCategoryCode,
          mode: behavior.mode,
        })),
        trainTypeCodes: location.trainTypes ?? [],
      });
    }
  }

  if (configuration.programDurations) {
    for (const programDuration of configuration.programDurations) {
      configurations.configurations.push({
        programDurationCode: programDuration.programDurationCode,
        operationalSeasonCodes: programDuration.operationalSeasonCodes,
      });
    }
  }

  return configurations;
};
