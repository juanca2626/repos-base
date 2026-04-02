import { it, expect, vi } from 'vitest';

import { saveConfigurationUseCase } from '../configuration/saveConfiguration.useCase';

import * as genericModule from '@/modules/negotiations/products/configuration/infrastructure/configuration/saveGenericConfiguration.service';
import * as trainModule from '@/modules/negotiations/products/configuration/infrastructure/configuration/saveTrainConfiguration.service';
import * as packageModule from '@/modules/negotiations/products/configuration/infrastructure/configuration/savePackageConfiguration.service';

it('should call saveGenericConfigurationService for GENERIC service', async () => {
  const spy = vi
    .spyOn(genericModule, 'saveGenericConfigurationService')
    .mockResolvedValue(undefined);

  await saveConfigurationUseCase({
    serviceType: 'GENERIC',
    productSupplierId: '69611a777e0ce8d2f4acf421',
    configuration: {},
  });

  expect(spy).toHaveBeenCalledTimes(1);
});

it('should call saveTrainConfigurationService for TRAIN service', async () => {
  const spy = vi.spyOn(trainModule, 'saveTrainConfigurationService').mockResolvedValue(undefined);

  await saveConfigurationUseCase({
    serviceType: 'TRAIN',
    productSupplierId: '695c2eefa73430a45f88a54f',
    configuration: {},
  });

  expect(spy).toHaveBeenCalledTimes(1);
});

it('should call savePackageConfigurationService for PACKAGE service', async () => {
  const spy = vi
    .spyOn(packageModule, 'savePackageConfigurationService')
    .mockResolvedValue(undefined);

  await saveConfigurationUseCase({
    serviceType: 'PACKAGE',
    productSupplierId: '69610e9881204419d8fd3a64',
    configuration: {},
  });

  expect(spy).toHaveBeenCalledTimes(1);
});

it('should send mapped payload to service for GENERIC service', async () => {
  const spy = vi
    .spyOn(genericModule, 'saveGenericConfigurationService')
    .mockResolvedValue(undefined);

  const configuration = {
    operatingLocations: [
      {
        location: {
          key: '89-1606-0',
          country: {
            code: 'PE',
            name: 'PERÚ',
          },
          state: {
            code: 'ICA',
            name: 'ICA',
          },
        },
        applyGeneralBehavior: false,
        behaviorSettings: [
          {
            supplierCategoryCode: 'shared',
            mode: 'COMPONENT',
          },
        ],
      },
    ],
  };

  await saveConfigurationUseCase({
    serviceType: 'GENERIC',
    productSupplierId: '69611a777e0ce8d2f4acf421',
    configuration,
  });

  expect(spy).toHaveBeenCalledWith(
    '69611a777e0ce8d2f4acf421',
    expect.objectContaining({
      configurations: expect.any(Array),
    })
  );
});

it('should send mapped payload to service for TRAIN service', async () => {
  const spy = vi.spyOn(trainModule, 'saveTrainConfigurationService').mockResolvedValue(undefined);

  const configuration = {
    operatingLocations: [
      {
        location: {
          key: '89-1606-0',
          country: {
            code: 'PE',
            name: 'PERÚ',
          },
          state: {
            code: 'ICA',
            name: 'ICA',
          },
        },
        trainTypeCodes: ['VISTADOME'],
      },
    ],
  };

  await saveConfigurationUseCase({
    serviceType: 'TRAIN',
    productSupplierId: '695c2eefa73430a45f88a54f',
    configuration,
  });

  expect(spy).toHaveBeenCalledWith(
    '695c2eefa73430a45f88a54f',
    expect.objectContaining({
      configurations: expect.any(Array),
    })
  );
});

it('should send mapped payload to service for PACKAGE service', async () => {
  const spy = vi
    .spyOn(packageModule, 'savePackageConfigurationService')
    .mockResolvedValue(undefined);

  const configuration = {
    programDurations: [
      {
        programDurationCode: '2D1N',
        operationalSeasonCodes: ['MEDIUM', 'HIGH'],
      },
    ],
  };

  await saveConfigurationUseCase({
    serviceType: 'PACKAGE',
    productSupplierId: '69610e9881204419d8fd3a64',
    configuration,
  });

  expect(spy).toHaveBeenCalledWith(
    '69610e9881204419d8fd3a64',
    expect.objectContaining({
      configurations: expect.any(Array),
    })
  );
});
