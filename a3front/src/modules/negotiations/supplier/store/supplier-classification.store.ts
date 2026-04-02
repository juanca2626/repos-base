import { defineStore } from 'pinia';
import type { SelectOption } from '@/modules/negotiations/supplier/interfaces';
import { supplierApi } from '@/modules/negotiations/api/negotiationsApi';
import { mapItemsToOptions } from '@/modules/negotiations/supplier/register/helpers/supplierFormHelper';

export const useSupplierClassificationStore = defineStore('supplierClassification', {
  state: () => ({
    supplier_sub_classification_id: 0,
    supplierClassificationOptions: [] as SelectOption[],
    isLoading: false,
  }),
  actions: {
    setSupplierSubClassificationId(id: number) {
      this.supplier_sub_classification_id = id;
    },
    async fetchSupplierClassifications() {
      this.isLoading = true;

      try {
        const response = await supplierApi.get('supplier/resources', {
          params: { 'keys[]': 'supplierSubClassification' },
        });

        this.supplierClassificationOptions = mapItemsToOptions(
          response.data.data.supplierSubClassification
        );
      } catch (error: any) {
        console.error('Error al cargar las subclasificaciones del proveedor:', error);
      } finally {
        this.isLoading = false;
      }
    },
  },
});
