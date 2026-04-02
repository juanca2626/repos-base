import type { Status } from '../../types';

export interface BackendSubSection {
  code: string;
  name: string;
  status: Status;
}

export interface BackendSection {
  code: string;
  name: string;
  status: Status;
  subSections: BackendSubSection[];
}

export interface BackendSidebarResponse {
  totalProgress: number;
  sections: BackendSection[];
}
