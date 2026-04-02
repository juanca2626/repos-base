import type { ServiceType } from '../../types';
import { drawerComponentMap } from '../componentMap';

export type DrawerAction = 'CREATE' | 'EDIT' | 'ADD';

export const resolveDrawerComponent = (serviceType: ServiceType, action: DrawerAction) => {
  const serviceMap = drawerComponentMap[serviceType];

  if (!serviceMap) {
    throw new Error(`Drawer map not found for ${serviceType}`);
  }

  const component = serviceMap[action];

  if (!component) {
    throw new Error(`Drawer component not found for ${serviceType} → ${action}`);
  }

  return component;
};
