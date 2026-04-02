interface BaseMenu {
  id: number;
  name: string;
  target_site: string;
  icon: string;
  actions: string[];
}

export interface SubMenu extends BaseMenu {
  slug: string;
  path: string;
}

export interface Menu extends BaseMenu {
  slug: string | null;
  path: string | null;
  app_id: number;
  children: SubMenu[];
}

export interface AppRoute {
  id: number;
  lang: string;
  name: string;
  icon: string | null;
  children: Menu[];
}
