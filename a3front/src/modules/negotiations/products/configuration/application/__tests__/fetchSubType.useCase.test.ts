import { it, expect, vi } from 'vitest';

import { fetchSubTypeUseCase } from '../supportResource/fetchSubType.useCase';
import * as subTypeModule from '../../infrastructure/supportResource/serviceSubType.service';

it('should call serviceSubTypeService with correct serviceTypeId', async () => {
  const spy = vi.spyOn(subTypeModule, 'serviceSubTypeService').mockResolvedValue({ data: [] });

  await fetchSubTypeUseCase({ serviceTypeId: '69611a777e0ce8d2f4acf421' });

  expect(spy).toHaveBeenCalledWith('69611a777e0ce8d2f4acf421');
});

it('should return subTypes when service returns data', async () => {
  const mockSubTypes = [{ id: '692f78b90ad9f4637d65cb65', code: 'breakfast', name: 'Desayuno' }];

  vi.spyOn(subTypeModule, 'serviceSubTypeService').mockResolvedValue({
    data: mockSubTypes,
  });

  const result = await fetchSubTypeUseCase({
    serviceTypeId: '69611a777e0ce8d2f4acf421',
  });

  expect(result).toEqual(mockSubTypes);
});

it('should return empty array when response is undefined', async () => {
  vi.spyOn(subTypeModule, 'serviceSubTypeService').mockResolvedValue(undefined);

  const result = await fetchSubTypeUseCase({
    serviceTypeId: '69611a777e0ce8d2f4acf421',
  });

  expect(result).toEqual([]);
});
