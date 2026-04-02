import { useAbility } from '@casl/vue';
import { computed } from 'vue';
import { PermissionActionEnum } from '@/enums/permission-action.enum';
import { ModulePermissionEnum } from '@/enums/module-permission.enum';

export const useModulePermission = () => {
  const ability = useAbility();

  const can = (action: PermissionActionEnum, modulePermission: ModulePermissionEnum) => {
    return computed(() => ability.can(action, modulePermission));
  };

  const canCreate = (modulePermission: ModulePermissionEnum) => {
    return can(PermissionActionEnum.CREATE, modulePermission);
  };

  const canRead = (modulePermission: ModulePermissionEnum) => {
    return can(PermissionActionEnum.READ, modulePermission);
  };

  const canUpdate = (modulePermission: ModulePermissionEnum) => {
    return can(PermissionActionEnum.UPDATE, modulePermission);
  };

  const canDelete = (modulePermission: ModulePermissionEnum) => {
    return can(PermissionActionEnum.DELETE, modulePermission);
  };

  const canCreateOrUpdate = (modulePermission: ModulePermissionEnum) => {
    return computed(() => {
      return (
        ability.can(PermissionActionEnum.CREATE, modulePermission) ||
        ability.can(PermissionActionEnum.UPDATE, modulePermission)
      );
    });
  };

  const canRaw = (action: string, subject: string): boolean => {
    return ability.can(action, subject);
  };

  return {
    can,
    canCreate,
    canRead,
    canUpdate,
    canDelete,
    canCreateOrUpdate,
    canRaw,
  };
};
