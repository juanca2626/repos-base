export interface MenuItem {
  id: string;
  label: string;
  completed: boolean;
}

export interface MenuSection {
  title: string;
  key: string;
  expanded: boolean;
  items: MenuItem[];
  currentKey?: string;
}
