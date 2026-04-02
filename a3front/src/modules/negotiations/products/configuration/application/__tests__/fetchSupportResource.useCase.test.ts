import { it, expect, vi } from 'vitest';
import { fetchSupportResourceUseCase } from '../supportResource/fetchSupportResource.useCase';
import * as supportResourceModule from '../../infrastructure/supportResource/supportResource.service';
import { SUPPORT_RESOURCE_KEYS } from '../../domain/supportResource/supportResourceKeys';

it('should use default keys for GENERIC service', async () => {
  const spy = vi
    .spyOn(supportResourceModule, 'supportResourceService')
    .mockResolvedValue({ data: {} });

  await fetchSupportResourceUseCase({
    serviceType: 'GENERIC',
  });

  expect(spy).toHaveBeenCalledWith(SUPPORT_RESOURCE_KEYS.GENERIC);
});

it('should use provided keys instead of defaults', async () => {
  const overrideKeys = ['supplierCategories'];

  const spy = vi
    .spyOn(supportResourceModule, 'supportResourceService')
    .mockResolvedValue({ data: {} });

  await fetchSupportResourceUseCase({
    serviceType: 'GENERIC',
    keys: overrideKeys,
  });

  expect(spy).toHaveBeenCalledWith(overrideKeys);
});

it('should return empty object when response is undefined', async () => {
  vi.spyOn(supportResourceModule, 'supportResourceService').mockResolvedValue(undefined);

  const result = await fetchSupportResourceUseCase({
    serviceType: 'GENERIC',
  });

  expect(result).toEqual({});
});
