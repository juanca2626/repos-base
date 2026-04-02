// supplier-clone.store.ts
import { defineStore } from 'pinia';
import { ref } from 'vue';
import type {
  SupplierRegistration,
  ModuleConfiguration,
} from '@/modules/negotiations/supplier/interfaces/supplier-clone-response.interface';
import { supplierApi } from '@/modules/negotiations/api/negotiationsApi';

interface SupplierCloneApiResponse {
  success: boolean;
  data: {
    supplier_registration: SupplierRegistration[];
    module_configuration: ModuleConfiguration[];
  };
}

// interface TransportSupplierApiResponse {
//   success: boolean;
//   data: {
//     supplier_id: number;
//     supplier_name: string;
//   }[];
//   code: number;
// }

export const useCloneSupplierStore = defineStore('useCloneSupplier', () => {
  const supplierRegistration = ref<SupplierRegistration[]>([]);
  const moduleConfiguration = ref<ModuleConfiguration[]>([]);
  const transportSuppliers = ref<{ supplier_id: number; supplier_name: string }[]>([]); // Agregado para almacenar los proveedores de transporte
  const isLoading = ref(false);
  const error = ref<string | null>(null);

  const fetchSupplierData = async () => {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await supplierApi.get<SupplierCloneApiResponse>('supplier/all-modules/1');
      // console.log('API Response:', response.data); // Verifica que la respuesta contiene los datos correctos

      if (response.data.success) {
        supplierRegistration.value = response.data.data.supplier_registration;
        moduleConfiguration.value = response.data.data.module_configuration;
      } else {
        error.value = 'Error al cargar los datos del proveedor.';
      }
    } catch (err) {
      error.value = 'Error de conexión a la API.';
      console.error(err);
    } finally {
      isLoading.value = false;
    }
  };

  // Nueva función para obtener proveedores de transporte con POST
  // const fetchTransportSuppliers = async () => {

  //   console.log("llega ")
  //   isLoading.value = true;
  //   error.value = null;

  // try {
  //   const response = await supplierApi.post<TransportSupplierApiResponse>('suppliers-transport');
  //   if (response.data.success) {
  //     transportSuppliers.value = response.data.data; // Guarda los proveedores de transporte en el estado
  //   } else {
  //     error.value = 'Error al cargar los proveedores de transporte.';
  //   }
  // } catch (err) {
  //   error.value = 'Error de conexión a la API.';
  //   console.error(err);
  // } finally {
  //   isLoading.value = false;
  // }
  // };

  return {
    supplierRegistration,
    moduleConfiguration,
    transportSuppliers,
    isLoading,
    error,
    fetchSupplierData,
    // fetchTransportSuppliers,
  };
});
