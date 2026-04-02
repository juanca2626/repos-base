import type { BackendCompoundSidebarResponse } from './sidebar.backend.types';
import type { CompoundSidebarModel } from './sidebar.types';

export type CompoundSidebarStrategy = (
  data: BackendCompoundSidebarResponse
) => CompoundSidebarModel;
