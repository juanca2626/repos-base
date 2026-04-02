import type { ModulePermissionEnum } from '@/enums/module-permission.enum';
import type { PermissionActionEnum } from '@/enums/permission-action.enum';

export interface RoutePermissionConfig {
  routeName: string;
  permission: ModulePermissionEnum;
  action?: PermissionActionEnum;
}
