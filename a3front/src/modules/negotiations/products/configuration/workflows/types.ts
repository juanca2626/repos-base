export type DependencyMode = 'ALL' | 'ANY';

export interface WorkflowStepRule {
  id: string;
  dependsOn?: string[];
  mode?: DependencyMode;
}

export type Workflow = WorkflowStepRule[];
