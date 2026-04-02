import { storeToRefs } from 'pinia';
import { useRouter, type RouteParamsRawGeneric } from 'vue-router';
import { useSupplierLayoutStore } from '@/modules/negotiations/supplier/store/supplier-layout.store';
import { ResourceActionTypeEnum } from '@/modules/negotiations/supplier/enums/resource-action-type.enum';
import { supplierRouteActionsMap } from '@/modules/negotiations/supplier/config/supplier-route-config';

export function useSupplierRouteAction() {
  const router = useRouter();

  const supplierLayoutStore = useSupplierLayoutStore();

  const { supplierSubClassification } = storeToRefs(supplierLayoutStore);

  const handleCreateSupplier = () => {
    router.push({ name: 'supplier-register-form' });
  };

  const handleEditSupplier = (id: number) => {
    redirectRoute(findRouteName(ResourceActionTypeEnum.EDIT), {
      id,
    });
  };

  const handleBackToList = () => {
    redirectRoute(findRouteName(ResourceActionTypeEnum.LIST));
  };

  const redirectRoute = async (name: string, params?: RouteParamsRawGeneric) => {
    try {
      await router.push({
        name,
        params,
      });
    } catch (error) {
      console.error(`Failed to redirect to route ${name}:`, error);
    }
  };

  const findRouteName = (action: ResourceActionTypeEnum) => {
    if (!supplierSubClassification.value) {
      throw new Error('SupplierSubClassification is empty');
    }

    const supplierRoute = supplierRouteActionsMap[supplierSubClassification.value];

    if (!supplierRoute) {
      throw new Error(
        `SupplierRoute with supplierSubClassification ${supplierSubClassification.value} not found`
      );
    }

    const routeName = supplierRoute[action];

    if (!routeName) {
      throw new Error(`Route not found for action: ${action}`);
    }

    return routeName;
  };

  return {
    supplierSubClassification,
    handleCreateSupplier,
    handleEditSupplier,
    handleBackToList,
  };
}
