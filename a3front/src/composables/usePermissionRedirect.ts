import { PermissionActionEnum } from '@/enums/permission-action.enum';
import { usePermissionStore } from '@/stores/permission-store';
import type { RoutePermissionConfig } from '@/interfaces/route-permission-config.interface';
import { useRouter } from 'vue-router';

export function usePermissionRedirect() {
  const { getPermissions } = usePermissionStore();
  const userPermissions = getPermissions();

  const router = useRouter();

  const redirectOnPermissionCheck = (routes: RoutePermissionConfig[]) => {
    for (const { routeName, permission, action = PermissionActionEnum.READ } of routes) {
      const grantedPermission = userPermissions.find(
        (item) => item.subject === permission && item.action.includes(action)
      );

      if (grantedPermission) {
        router.replace({ name: routeName });
        return;
      }
    }
  };

  return {
    redirectOnPermissionCheck,
  };
}
