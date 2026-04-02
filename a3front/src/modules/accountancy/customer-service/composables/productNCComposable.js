import { ref } from 'vue';
import { message } from 'ant-design-vue';
import { nonConformingProductsApi } from '../../services/api';

export function useProductNC() {
  const loading = ref(false);
  const saving = ref(false);

  /**
   * Search file detail by file number
   */
  const searchFileDetail = async (nrofile) => {
    try {
      loading.value = true;
      const response = await nonConformingProductsApi.get(`/search-detail/${nrofile}`);
      return response.data;
    } catch (error) {
      console.error('Error searching file detail:', error);
      message.error('Error al buscar el detalle del file');
      return null;
    } finally {
      loading.value = false;
    }
  };

  /**
   * Get all service types
   */
  const getServiceTypes = async () => {
    try {
      const response = await nonConformingProductsApi.get('/search-type-services');
      return response.data.data || [];
    } catch (error) {
      console.error('Error getting service types:', error);
      return [];
    }
  };

  /**
   * Get services by type code
   */
  const getServices = async (nroref, typeCode) => {
    try {
      const response = await nonConformingProductsApi.get('/search-services', {
        params: {
          nroref,
          tipo_servicio: typeCode,
          module: '',
        },
      });
      return response.data.data || [];
    } catch (error) {
      console.error('Error getting services:', error);
      return [];
    }
  };

  /**
   * Get all categories
   */
  const getCategories = async (typeService) => {
    const user_ = localStorage.getItem('user') || 'SYSTEM';
    try {
      const response = await nonConformingProductsApi.get('/search-types', {
        params: {
          tipo_servicio: typeService,
          usuario: user_,
        },
      });
      return response.data.data || [];
    } catch (error) {
      console.error('Error getting categories:', error);
      return [];
    }
  };

  /**
   * Get subcategories by category code
   */
  const getSubcategories = async (service, categoryCode) => {
    try {
      const response = await nonConformingProductsApi.get('/search-sub-categories', {
        params: {
          service,
          category: categoryCode,
        },
      });
      return response.data.data || [];
    } catch (error) {
      console.error('Error getting subcategories:', error);
      return [];
    }
  };

  /**
   * Get passengers for a file
   */
  const getPassengers = async (nroref) => {
    try {
      const response = await nonConformingProductsApi.get('/search-passengers', {
        params: {
          nroref,
        },
      });
      return response.data.data || [];
    } catch (error) {
      console.error('Error getting passengers:', error);
      return [];
    }
  };

  /**
   * Get available dates for service
   */
  const getAvailableDates = async (nroref, service) => {
    try {
      const response = await nonConformingProductsApi.get('/search-fechas-disponibles', {
        params: {
          nrofile: nroref,
          prefac: service,
        },
      });
      return response.data.data || [];
    } catch (error) {
      console.error('Error getting available dates:', error);
      return [];
    }
  };

  /**
   * Format date for API (DD-MM-YYYY)
   */
  const formatDateForAPI = (dateValue) => {
    if (!dateValue) return '';

    // Si es un string en formato DD-MM-YYYY, dejarlo igual
    if (typeof dateValue === 'string' && dateValue.match(/^\d{2}-\d{2}-\d{4}$/)) {
      return dateValue;
    }

    // Si es un objeto DayJS (del datepicker)
    if (dateValue && dateValue.format) {
      return dateValue.format('DD-MM-YYYY');
    }

    // Si es un Date object o ISO string
    if (dateValue instanceof Date || (typeof dateValue === 'string' && dateValue.includes('T'))) {
      const date = new Date(dateValue);
      const day = date.getDate().toString().padStart(2, '0');
      const month = (date.getMonth() + 1).toString().padStart(2, '0');
      const year = date.getFullYear();
      return `${day}-${month}-${year}`;
    }

    return '';
  };

  /**
   * Save new Product NC
   */
  const saveProductNC = async (data) => {
    try {
      saving.value = true;

      // Validaciones
      if (data.comment && data.comment.length >= 30000) {
        message.error(
          'No está permitido la cantidad de texto ingresado en el comentario. Por favor, trate de acortar el contenido para continuar.'
        );
        return { success: false };
      }

      if (!data.date) {
        message.error('Ingrese una fecha de incidente');
        return { success: false };
      }

      const response = await nonConformingProductsApi.post('/save', data);

      if (response.data.data.type === 'success') {
        message.success('Producto guardado correctamente');
        return { success: true, data: response.data.data };
      } else {
        message.error('Error al guardar el producto');
        return { success: false };
      }
    } catch (error) {
      if (error.response?.data?.error) {
        message.error(error.response.data.error);
      } else if (error.response?.data) {
        message.error(JSON.stringify(error.response.data));
      } else if (error.message) {
        message.error('Error: ' + error.message);
      } else {
        message.error('Error desconocido');
      }
      return { success: false };
    } finally {
      saving.value = false;
    }
  };

  /**
   * Update existing Product NC
   */
  const updateProductNC = async (codref, data) => {
    try {
      saving.value = true;

      const response = await nonConformingProductsApi.put('/update', {
        ...data,
        codref,
      });

      if (response.data.data.type === 'success') {
        message.success('Producto actualizado correctamente');
        return { success: true, data: response.data.data };
      } else {
        message.error('Error al actualizar el producto');
        return { success: false };
      }
    } catch (error) {
      if (error.response?.data?.error) {
        message.error(error.response.data.error);
      } else {
        message.error('Error al actualizar el producto');
      }
      return { success: false };
    } finally {
      saving.value = false;
    }
  };

  return {
    loading,
    saving,
    searchFileDetail,
    getServiceTypes,
    getServices,
    getCategories,
    getSubcategories,
    getPassengers,
    getAvailableDates,
    formatDateForAPI,
    saveProductNC,
    updateProductNC,
  };
}
