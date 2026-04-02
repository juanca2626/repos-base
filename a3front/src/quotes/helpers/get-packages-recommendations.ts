import quotesApi from '@/quotes/api/quotesApi';
import type { PackageRequest, PackageResponse } from '@/quotes/interfaces';

export const getPackagesRecommendations = async (
  request: PackageRequest
): Promise<PackageResponse> => {
  const { data } = await quotesApi.post<PackageResponse>(`/services/client/packages`, request);

  return data.data;
};
