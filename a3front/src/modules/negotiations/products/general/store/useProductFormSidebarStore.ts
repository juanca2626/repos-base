import { defineStore } from 'pinia';
import { ref } from 'vue';
import { MenuItemEnum } from '@/modules/negotiations/products/general/enums/menu-item.enum';
import type { MenuItem } from '@/modules/negotiations/products/general/interfaces/shared';

export const useProductFormSidebarStore = defineStore('productFormSidebarStore', () => {
  const activeMenuKey = ref<MenuItemEnum | null>(null);

  const initMenuItems: MenuItem[] = [
    {
      menuKey: MenuItemEnum.GENERAL_INFORMATION,
      isActive: true,
      isComplete: false,
      title: 'Información general',
    },
    {
      menuKey: MenuItemEnum.SUPPLIERS,
      isActive: false,
      isComplete: false,
      title: 'Proveedores',
    },
  ];

  const menuItems = ref<MenuItem[]>(structuredClone(initMenuItems));

  const resetMenuItems = () => {
    menuItems.value = structuredClone(initMenuItems);
  };

  const setActiveMenuKey = (value: MenuItemEnum) => {
    activeMenuKey.value = value;
  };

  const updateMenuItem = (menuKey: MenuItemEnum, inputs: Partial<MenuItem>) => {
    const item = menuItems.value.find((item) => item.menuKey === menuKey);
    if (item) {
      Object.assign(item, inputs);
    }
  };

  const markMenuComplete = (menuKey: MenuItemEnum) => {
    updateMenuItem(menuKey, { isComplete: true });
  };

  const markMenuActive = (menuKey: MenuItemEnum) => {
    menuItems.value.forEach((item) => {
      item.isActive = item.menuKey === menuKey;
    });
  };

  return {
    activeMenuKey,
    menuItems,
    setActiveMenuKey,
    markMenuComplete,
    markMenuActive,
    resetMenuItems,
  };
});
