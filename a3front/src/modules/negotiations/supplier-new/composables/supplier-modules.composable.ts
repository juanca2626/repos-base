import { useSupplierGlobalStore } from '@/modules/negotiations/supplier-new/store/supplier-global.store';
import { storeToRefs } from 'pinia';
import {
  extractPanelModules,
  extractModuleOptions,
} from '@/modules/negotiations/supplier-new/composables/form/negotiations/optimized-queries/supplier-modules.query';
import { useSupplierService } from '@/modules/negotiations/supplier-new/service/supplier.service';

export function useSupplierModulesComposable() {
  const supplierGlobalStore = useSupplierGlobalStore();
  const { panelSideBar, optionsForm, supplierId, subClassificationSupplierId } =
    storeToRefs(supplierGlobalStore);

  // NO crear query aquí, hacer llamada directa al servicio
  // Esto evita crear múltiples instancias del query

  const loadSupplierModules = async () => {
    try {
      if (!supplierId.value) {
        return null;
      }

      // Hacer la llamada directa al servicio
      const response = await useSupplierService.showModules({
        supplier_id: supplierId.value,
        supplier_sub_classification_id: subClassificationSupplierId.value,
      });

      if (response) {
        panelSideBar.value = extractPanelModules(response, panelSideBar.value);
        optionsForm.value = extractModuleOptions(response);
        return response;
      }

      return null;
    } catch (error) {
      console.error('❌ [loadSupplierModules] Error al cargar los módulos del proveedor:', error);
      return null;
    }
  };

  return {
    loadSupplierModules,
  };
}
