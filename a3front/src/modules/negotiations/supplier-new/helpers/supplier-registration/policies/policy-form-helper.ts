import { SegmentationEnum } from '@/modules/negotiations/supplier-new/enums/supplier-registration/policies/segmentation.enum';
import { BusinessGroupEnum } from '@/modules/negotiations/supplier-new/enums/supplier-registration/policies/business-groups.enum';
import type { SelectOption } from '@/modules/negotiations/suppliers/interfaces';
import type { PolicySegmentationSpecification } from '@/modules/negotiations/supplier-new/interfaces/supplier-registration';

export const isSeriesSegmentation = (segmentationId: number) => {
  return segmentationId === SegmentationEnum.SERIES;
};

export const isClientSegmentation = (segmentationId: number) => {
  return segmentationId === SegmentationEnum.CLIENTS;
};

export const hasEventSegmentation = (policySegmentationIds: number[]) => {
  return policySegmentationIds.includes(SegmentationEnum.EVENTS);
};

export const listFormatter = new (Intl as any).ListFormat('es', {
  style: 'long',
  type: 'conjunction',
});

export const mapClientsToOptions = (clients: any[]) => {
  return clients.map((row: any) => {
    return {
      value: row.id,
      label: `(${row.code}) ${row.name}`,
    };
  });
};

export const isFitsBusinessGroup = (businessGroupId: number | null) => {
  return businessGroupId === BusinessGroupEnum.FITS;
};

export const isGroupsBusinessGroup = (businessGroupId: number | null) => {
  return businessGroupId === BusinessGroupEnum.GROUPS;
};

export const getLabelFromOptions = (items: SelectOption[], value: number): string => {
  const record = items.find((item) => item.value === value);

  return record ? record.label.toLowerCase() : '';
};

export const sortSpecifications = (specifications: PolicySegmentationSpecification[]) => {
  specifications.sort((a, b) => a.segmentationId - b.segmentationId);
};
