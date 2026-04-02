import type { MenuItemEnum } from '@/modules/negotiations/products/general/enums/menu-item.enum';

export interface MenuItem {
  menuKey: MenuItemEnum;
  isActive: boolean;
  isComplete: boolean;
  title: string;
}
