import { usePermissionStore } from '@/stores/permission-store';
import { ability } from '@/services/casl/ability';

export const useCaslAbility = () => {
  const { getPermissions } = usePermissionStore();

  const updatePermissions = () => {
    ability.update(getPermissions());
  };

  return {
    updatePermissions,
  };
};
