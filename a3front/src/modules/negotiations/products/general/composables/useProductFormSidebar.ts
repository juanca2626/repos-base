import { storeToRefs } from 'pinia';
import { MenuItemEnum } from '@/modules/negotiations/products/general/enums/menu-item.enum';
import { useProductFormSidebarStore } from '@/modules/negotiations/products/general/store/useProductFormSidebarStore';

export function useProductFormSidebar() {
  const productFormSidebarStore = useProductFormSidebarStore();

  const { setActiveMenuKey, markMenuActive } = productFormSidebarStore;

  const { menuItems } = storeToRefs(productFormSidebarStore);

  const handleSelectMenuItem = (menuKey: MenuItemEnum) => {
    setActiveMenuKey(menuKey);
    markMenuActive(menuKey);
  };

  return {
    menuItems,
    handleSelectMenuItem,
  };
}
