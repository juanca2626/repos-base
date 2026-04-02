import type { ServiceDetailsResponse } from '@/modules/negotiations/products/configuration/interfaces/service-details.interface';

export interface FrequencyOption {
  label: string;
  value: string;
}

export const mapFrequenciesToOptions = (
  serviceDetail: ServiceDetailsResponse | null
): FrequencyOption[] => {
  if (!serviceDetail?.content?.frequencies) return [];

  return serviceDetail.content.frequencies.map((freq) => ({
    label: `${freq.code} | ${freq.fareType}`,
    value: freq.id,
  }));
};
