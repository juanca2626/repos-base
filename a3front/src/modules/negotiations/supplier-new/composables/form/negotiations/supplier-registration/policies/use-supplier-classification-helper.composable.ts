import { computed } from 'vue';
import { storeToRefs } from 'pinia';
import { useSupplierClassificationStore } from '@/modules/negotiations/supplier-new/store/negotiations/supplier-registration/supplier-classification.store';

/**
 * Composable helper para determinar características del proveedor basado en su clasificación
 *
 * @returns {Object} Objeto con computed properties para identificar tipo de proveedor
 */
export function useSupplierClassificationHelper() {
  const supplierClassificationStore = useSupplierClassificationStore();
  const { supplierClassificationId } = storeToRefs(supplierClassificationStore);

  /**
   * Determina si el proveedor es de tipo Alojamiento (LODGES o CRUISE)
   * Hoteles, Lodges y Cruceros comparten la misma lógica en el frontend
   */
  const isAccommodationSupplier = computed(() => {
    const accommodationTypes: string[] = [
      'LOD',
      'CRC',
      // TODO: Agregar HOTEL cuando se agregue al enum
    ];

    return (
      supplierClassificationId.value !== null &&
      accommodationTypes.includes(supplierClassificationId.value)
    );
  });

  /**
   * Determina si el proveedor es de tipo Servicio (cualquier otro que no sea Alojamiento)
   */
  const isServiceSupplier = computed(() => {
    return supplierClassificationId.value !== null && !isAccommodationSupplier.value;
  });

  return {
    supplierClassificationId,
    isAccommodationSupplier,
    isServiceSupplier,
  };
}
