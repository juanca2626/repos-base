import { SegmentationEnum } from '@/modules/negotiations/supplier-new/enums/supplier-registration/policies/segmentation.enum';
import type { SelectOptionsLoading } from '@/modules/negotiations/supplier-new/interfaces/supplier-registration';

// Segmentaciones que soportan loading (tanto números como strings)
export const segmentationsSupportingLoading: (number | string)[] = [
  SegmentationEnum.MARKETS,
  SegmentationEnum.CLIENTS,
  SegmentationEnum.EVENTS,
  SegmentationEnum.SERVICE_TYPES,
  SegmentationEnum.SEASONS,
  'markets',
  'clients',
  'events',
  'serviceTypes',
  'service-types',
  'seasons',
];

// Mapeo de segmentationId a la key de loading
export const specificationLoadingMap: Record<number | string, keyof SelectOptionsLoading> = {
  // Keys numéricos
  [SegmentationEnum.CLIENTS]: 'clients',
  [SegmentationEnum.MARKETS]: 'markets',
  [SegmentationEnum.EVENTS]: 'holidayCalendars',
  [SegmentationEnum.SERVICE_TYPES]: 'serviceTypes',
  [SegmentationEnum.SEASONS]: 'seasons',
  // Keys string
  clients: 'clients',
  markets: 'markets',
  events: 'holidayCalendars',
  serviceTypes: 'serviceTypes',
  'service-types': 'serviceTypes',
  seasons: 'seasons',
};
