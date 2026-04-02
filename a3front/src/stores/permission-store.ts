import { defineStore } from 'pinia';
import { useStorage } from '@vueuse/core';
import type { Ref } from 'vue';
import type { AbilityPermission, Permission } from '@/interfaces/permission.interface';
import type { AppRoute } from '@/interfaces/app-menu-route.interface';

export const usePermissionStore = defineStore('permissions', () => {
  const routes: Ref<AppRoute[]> = useStorage('routes', []);
  const permissions: Ref<AbilityPermission[]> = useStorage('permissions', []);
  const token: Ref<string | null> = useStorage('__token', null);

  const setRoutes = (routesInput: AppRoute[]) => {
    routes.value = routesInput;
  };

  const permissionsToAbilityRules = (permissions: Permission[]): AbilityPermission[] => {
    return permissions.map(({ actions, subject }) => {
      return {
        subject,
        action: actions,
      };
    });
  };

  const setPermissions = (permissionsInput: Permission[]) => {
    permissions.value = permissionsToAbilityRules(permissionsInput);
  };

  const getPermissions = (): AbilityPermission[] => {
    return permissions.value;
  };

  const setToken = () => (token.value = 'tokendeprueba');

  return {
    routes,
    setRoutes,
    setPermissions,
    getPermissions,
    setToken,
  };
});
