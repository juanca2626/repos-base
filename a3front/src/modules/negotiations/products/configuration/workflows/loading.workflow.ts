import type { Workflow } from './types';

export const loadingWorkflow: Workflow = [
  {
    id: 'service-details',
  },
  {
    id: 'capacity-configuration',
    dependsOn: ['service-details'],
    mode: 'ALL',
  },
  {
    id: 'content',
    dependsOn: ['capacity-configuration'],
    mode: 'ALL',
  },
  {
    id: 'pricing-plans',
    dependsOn: ['service-details', 'capacity-configuration'],
    mode: 'ALL',
  },
  {
    id: 'images',
  },
];
