import type { CompoundSidebarStatus } from './sidebar.types';

export interface BackendCompoundSubSection {
  code: string;
  name: string;
  status: CompoundSidebarStatus;
}

export interface BackendCompoundSection {
  code: string;
  name: string;
  status: CompoundSidebarStatus;
  subSections: BackendCompoundSubSection[];
}

export interface BackendCompoundSidebarResponse {
  totalProgress: number;
  sections: BackendCompoundSection[];
}
