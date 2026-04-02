import { getBasicStepIssues } from '../../shared/basicRules';
import type { BasicSection } from '../../state/createInitialBasicState';

export function isBasicComplete(state: BasicSection) {
  return getBasicStepIssues(state).length === 0;
}
