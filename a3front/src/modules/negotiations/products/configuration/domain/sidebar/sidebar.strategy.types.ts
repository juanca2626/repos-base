import type { BackendSidebarResponse } from './sidebar.backend.types';
import type { SidebarModel } from './sidebar.types';

export type SidebarStrategy = (data: BackendSidebarResponse, codeOrKey: string) => SidebarModel;
