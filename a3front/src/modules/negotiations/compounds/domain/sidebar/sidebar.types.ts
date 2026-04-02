export type CompoundSidebarStatus = 'PENDING' | 'IN_PROGRESS' | 'COMPLETED';

export interface CompoundSidebarItem {
  id: string;
  label: string;
  editing: boolean;
  completed: boolean;
  active: boolean;
  icon: string | null;
  disabled: boolean;
  source: 'item';
  status: CompoundSidebarStatus;
}

export interface CompoundSidebarNode {
  code: string;
  title: string;
  icon: string | null;
  active: boolean;
  editing: boolean;
  completed: boolean;
  disabled: boolean;
  source: 'section' | 'item';
  status: CompoundSidebarStatus;
  items?: CompoundSidebarItem[];
}

export interface CompoundSidebarModel {
  totalProgress: number;
  sections: CompoundSidebarNode[];
}
