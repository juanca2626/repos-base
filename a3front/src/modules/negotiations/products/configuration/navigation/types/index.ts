export interface ApiItem {
  id: string;
  label: string;
  editing: boolean;
  completed: boolean;
  active: boolean;
  icon: string | null;
  disabled: boolean;
}

export interface ApiNavigationItem {
  key: string;
  code: string;
  title: string;
  icon: string | null;
  active: boolean;
  editing: boolean;
  completed: boolean;
  disabled: boolean;
  items?: ApiItem[];
  source: 'section' | 'item';
}
