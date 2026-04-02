import { defineStore } from 'pinia';
import { supportApi } from '@/modules/negotiations/api/negotiationsApi';

interface FilterData {
  name: string;
  supplierSubClassificationId: number | null;
  taxesSupplierClassificationId: number | null;
  page: number;
  pageSize: number;
}

export const useSupplierTaxAssignFilterStore = defineStore('supplierTaxAssignFilter', {
  state: (): FilterData => ({
    name: '',
    supplierSubClassificationId: null,
    taxesSupplierClassificationId: null,
    page: 1,
    pageSize: 10,
  }),
  actions: {
    setName(name: string) {
      this.name = name;
    },
    setSupplierSubClassificationId(id: number | null) {
      this.supplierSubClassificationId = id;
    },
    setTaxesSupplierClassificationId(id: number | null) {
      this.taxesSupplierClassificationId = id;
    },
    setPage(page: number) {
      this.page = page;
    },
    setPageSize(pageSize: number) {
      this.pageSize = pageSize;
    },
    areRequiredFieldsPresent(): boolean {
      return (
        this.supplierSubClassificationId !== null && this.taxesSupplierClassificationId !== null
      );
    },
    async getData() {
      if (!this.areRequiredFieldsPresent()) {
        console.warn('Required fields are missing. getData will not be executed.');
        return null;
      }

      const filterParams = {
        name: this.name,
        supplier_sub_classification_id: this.supplierSubClassificationId,
        taxes_supplier_classification_id: this.taxesSupplierClassificationId,
        page: this.page,
        pageSize: this.pageSize,
      };
      try {
        const response = await supportApi.get('assigned-supplier-taxes', {
          params: filterParams,
        });
        return response.data; // Asumiendo que la respuesta de la API está en response.data
      } catch (error) {
        console.error('Error en getData:', error);
        throw error;
      }
    },
  },
});
