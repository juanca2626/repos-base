import type { Workflow } from './types';

export const marketingWorkflow: Workflow = [
  {
    id: 'content',
  },
  {
    id: 'translations',
    dependsOn: ['content'],
    mode: 'ALL',
  },
];
