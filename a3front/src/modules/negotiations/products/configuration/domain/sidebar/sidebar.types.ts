import type { Status } from '../../types/index';

export interface SidebarItem {
  id: string;
  label: string;
  editing: boolean;
  completed: boolean;
  active: boolean;
  icon: string | null;
  disabled: boolean;
  source: 'item';
  status: Status;
}

export interface SidebarNode {
  key: string;
  code: string;
  title: string;
  icon: string | null;
  active: boolean;
  editing: boolean;
  completed: boolean;
  disabled: boolean;
  source: 'section' | 'item';
  status: Status;
  items?: SidebarItem[];
}

export interface SidebarModel {
  totalProgress: number;
  sections: SidebarNode[];
}
