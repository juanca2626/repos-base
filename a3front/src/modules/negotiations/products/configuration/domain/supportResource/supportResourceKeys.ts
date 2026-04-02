import type { ServiceType, SupportResourceKey } from '../../types/index';

export const SUPPORT_RESOURCE_KEYS: Record<ServiceType, SupportResourceKey[]> = {
  GENERIC: [
    'supplierCategories',
    'profiles',
    'pointTypes',
    'activities',
    'requirements',
    'inclusions',
    'textTypes',
    'operationalSeasons',
  ],

  TRAIN: ['trainTypes', 'requirements', 'inclusions', 'operationalSeasons'],

  PACKAGE: [
    'profiles',
    'pointTypes',
    'programDurations',
    'operationalSeasons',
    'textTypes',
    'activities',
  ],
};
