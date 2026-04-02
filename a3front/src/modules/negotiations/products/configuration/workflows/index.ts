import type { Workflow } from './types';

import { marketingWorkflow } from './marketing.workflow';
import { loadingWorkflow } from './loading.workflow';

export type Role = 'MARKETING' | 'LOADING';

export const workflowByRole: Record<Role, Workflow> = {
  MARKETING: marketingWorkflow,
  LOADING: loadingWorkflow,
};
