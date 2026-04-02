import { defineStore } from 'pinia';
import { fetchSuppliers, saveServiceMaskToFile } from '@/services/files/index.js';
import { handleError, handleSuccessResponse } from '@/modules/negotiations/api/responseApi';
import dayjs from 'dayjs';

// Tipos para el estado y las acciones
interface Supplier {
  label: string;
  value: string;
  supplier_name: string;
}

interface ServiceMask {
  service_id: string;
  code: string;
  name: string;
}

interface ServiceMaskRate {
  dateInit: string;
  priceSale: number | string;
  priceCost: number | string;
  details: object[];
  passengers: object[];
  description: string;
  typeCost: string;
}

interface File {
  file: {
    id: string;
    adults: number;
    children: number;
  };
  passengers: object[];
}

export const useServiceMaskStore = defineStore('serviceMaskStore', {
  state: () => ({
    loading: false,
    suppliers: [] as Supplier[],
    notFound: false,
    serviceMask: {} as ServiceMask,
    serviceMaskRate: {} as ServiceMaskRate,
    serviceMaskSupplier: {} as Supplier,
    file: {} as File,
    isCreatedServiceMask: false,
  }),

  actions: {
    // Establecer proveedor
    setServiceMaskSupplier(supplier: Supplier) {
      this.serviceMaskSupplier = supplier;
    },
    clearServiceMask() {
      this.serviceMask = {};
    },
    clearServiceMaskRate() {
      this.serviceMaskRate = {};
    },
    clearServiceMaskSupplier() {
      this.serviceMaskSupplier = {};
    },
    clearFileSelected() {
      this.file = {};
    },
    // Establecer tarifa de máscara de servicio
    setServiceMaskRate(supplierRate: ServiceMaskRate) {
      this.serviceMaskRate = supplierRate;
    },

    // Establecer archivo
    setFile(file: File) {
      this.file = file;
    },

    // Establecer máscara de servicio
    setServiceMask(data: ServiceMask) {
      this.serviceMask = data;
    },

    // Establecer estado de no encontrado
    setNotFoundSupplier(notFound: boolean) {
      this.notFound = notFound;
    },

    // Obtener proveedores con validación
    async fetchSuppliers({ filter, limit }: { filter: string; limit: number }) {
      this.loading = true;
      this.notFound = false;
      try {
        const { data } = await fetchSuppliers(filter, limit);
        if (data.status === 200 && !data.error) {
          this.suppliers = data.data.map((supplier: any) => ({
            label: `${supplier.ruc} - ${supplier.lintlx}`,
            value: supplier.codigo,
          }));
          this.notFound = this.suppliers.length === 0;
        } else {
          this.suppliers = [];
          this.notFound = true;
        }
      } catch (error) {
        console.error('Error al buscar datos:', error);
        this.suppliers = [];
        this.notFound = true;
      } finally {
        this.loading = false;
      }
    },

    // Guardar máscara de servicio
    async saveServiceMask() {
      if (!this.validateServiceMask()) return;

      this.loading = true;
      try {
        const data = this.formatServiceMaskData();
        console.log('Datos a enviar:', data);

        const response = await saveServiceMaskToFile(this.file.file.id, data);
        this.isCreatedServiceMask = true;
        handleSuccessResponse(response);
      } catch (error) {
        handleError(error);
        this.isCreatedServiceMask = false;
      } finally {
        this.loading = false;
      }
    },

    // Validar datos de máscara de servicio
    validateServiceMask(): boolean {
      if (!this.serviceMask.service_id || !this.serviceMaskRate.dateInit) {
        console.error('Datos de máscara de servicio incompletos');
        return false;
      }
      return true;
    },

    // Formatear datos para la API
    formatServiceMaskData() {
      return {
        entity: 'service-mask',
        service_id: this.serviceMask.service_id,
        service_code: this.serviceMask.code,
        name: this.serviceMask.name,
        description: this.serviceMaskRate.description,
        service_supplier_code: this.serviceMaskSupplier.value,
        service_supplier_name: this.serviceMaskSupplier.label,
        date: dayjs(this.serviceMaskRate.dateInit, 'DD/MM/YYYY').format('YYYY-MM-DD'),
        adult_num: this.file.file.adults,
        child_num: this.file.file.children,
        type_import: this.serviceMaskRate.typeCost,
        total_amount: parseFloat(this.serviceMaskRate.priceSale as string).toFixed(2),
        total_cost_amount: parseFloat(this.serviceMaskRate.priceCost as string).toFixed(2),
        details: this.serviceMaskRate.details,
        accommodations: this.serviceMaskRate.passengers,
      };
    },

    // Obtener la fecha actual formateada
    getCurrentDateTime() {
      const now = new Date();
      return {
        date: dayjs(now).format('DD/MM/YYYY'),
        time: dayjs(now).format('HH:mm:ss'),
      };
    },

    // Actualizar detalles de texto en varios idiomas
    updateTextServiceEdit(
      languages: { value: string; id: number }[] | undefined,
      textSkeleton: Record<string, string>,
      textItineraries: Record<string, string>
    ) {
      let details = [];
      languages.value?.forEach((language) => {
        const languageIso = language.value;
        const languageId = language.id;

        // Buscar los textos traducidos para el idioma actual
        const itineraryText = textItineraries[languageIso] || '';
        const skeletonText = textSkeleton[languageIso] || '';
        if (itineraryText || skeletonText) {
          // Crear el objeto de detalles para el idioma
          details.push({
            language_id: languageId,
            language_iso: languageIso,
            itinerary: itineraryText,
            skeleton: skeletonText,
          });
        }
      });

      this.serviceMaskRate.details = details;

      // this.serviceMaskRate.details = languages.map((language) => {
      //   const itineraryText = textItineraries[language.value] || '';
      //   const skeletonText = textSkeleton[language.value] || '';
      //   return {
      //     language_id: language.id,
      //     language_iso: language.value,
      //     itinerary: itineraryText,
      //     skeleton: skeletonText,
      //   };
      // });
    },
  },

  getters: {
    getServiceMask: (state) => state.serviceMask,
    getFile: (state) => state.file,
    getSuppliers: (state) => state.suppliers,
    isLoading: (state) => state.loading,
    notFoundSupplier: (state) => state.notFound,
    getServiceMaskSupplier: (state) => state.serviceMaskSupplier,
    getServiceMaskRate: (state) => state.serviceMaskRate,
    getIsCreatedServiceMask: (state) => state.isCreatedServiceMask,
  },

  persist: {
    pick: ['serviceMaskSupplier', 'serviceMaskRate', 'serviceMask', 'file'],
    storage: localStorage,
  },
});
