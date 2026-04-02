import { useAbility } from '@casl/vue';
import { computed } from 'vue';
import { PermissionActionEnum } from '@/enums/permission-action.enum';
import { ModulePermissionEnum } from '@/enums/module-permission.enum';

export function useTypeUnitConfiguratorPermission() {
  const ability = useAbility();

  const can = (action: PermissionActionEnum) => {
    return computed(() => ability.can(action, ModulePermissionEnum.TRANSPORT_CONFIGURATOR));
  };

  const canRead = can(PermissionActionEnum.READ);

  const canCreate = can(PermissionActionEnum.CREATE);

  const canUpdate = can(PermissionActionEnum.UPDATE);

  const canDelete = can(PermissionActionEnum.DELETE);

  const canCreateOrUpdate = computed(() => {
    return (
      ability.can(PermissionActionEnum.CREATE, ModulePermissionEnum.TRANSPORT_CONFIGURATOR) ||
      ability.can(PermissionActionEnum.UPDATE, ModulePermissionEnum.TRANSPORT_CONFIGURATOR)
    );
  });

  return {
    canRead,
    canCreate,
    canUpdate,
    canDelete,
    canCreateOrUpdate,
  };
}
