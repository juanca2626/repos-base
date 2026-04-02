// data.store.ts
import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import {
  downloadExcel,
  fetchOrders,
  fetchByCodeAndOrder,
  updateOrder,
  sendFollowUp,
  sendFollowUpBulk,
  reassignOrder,
  updateOrderStatus,
  updateStatusBulk,
  updateOrderStatusCancellationReason,
  fetchUsedTemplates,
} from '@ordercontrol/api';

const DEFAULT_PER_PAGE = 9;
const DEFAULT_PAGE = 1;

export const useOrderStore = defineStore('orderStore', () => {
  const total = ref(0);
  const currentPage = ref(DEFAULT_PAGE);
  const defaultPerPage = ref(DEFAULT_PER_PAGE);
  const perPage = ref(DEFAULT_PER_PAGE);
  const isLoading = ref(false);
  const loading = ref(false);
  const isDownloading = ref(false);
  const orders = ref<any[]>([]);

  const filterBy = ref(null); // Default filter by name
  const filterByType = ref(null); // Default filter type

  const error = ref<string | null>(null);
  const loadingModal = ref(false);
  const errorModal = ref<string | null>(null);

  const getTotal = computed(() => total.value);
  const getCurrentPage = computed(() => currentPage.value);
  const getDefaultPerPage = computed(() => defaultPerPage.value);
  const getPerPage = computed(() => perPage.value);
  const getOrders = computed(() => orders.value);

  const services = ref<any[]>([]);

  //TODO: Asignación de proveedores
  const providers = ref<any[]>([]);
  const sendServiceOrder = ref(false);

  const additionals = ref<any[]>([]);

  // const data = ref<any[]>([]);
  const kpi = ref<any>(null);

  const isLoadingTemplates = ref(false);

  const usedTemplatesOptions = ref<{ value: string; label: string }[]>([]);
  const getUsedTemplatesOptions = computed(() => usedTemplatesOptions.value);

  const allUsedTemplatesOptions = ref<{ value: string; label: string }[]>([]);
  const getAllUsedTemplatesOptions = computed(() => allUsedTemplatesOptions.value); // Nuevo getter

  const inited = () => {
    console.log('OrderControlStore inited');
    // isLoading.value = true;
  };

  const fetchUsedTemplatesAction = async () => {
    if (allUsedTemplatesOptions.value.length > 0) return;
    isLoadingTemplates.value = true;
    try {
      const response = await fetchUsedTemplates();
      if (response && response.data) {
        allUsedTemplatesOptions.value = response.data;
      } else {
        // Fallback si la respuesta viene directa sin .data
        allUsedTemplatesOptions.value = Array.isArray(response) ? response : [];
      }
    } catch (e) {
      console.error('Error fetching used templates filters:', e);
      allUsedTemplatesOptions.value = [];
    } finally {
      isLoadingTemplates.value = false; // Desactivar carga
    }
  };

  const fetchAll = async ({
    currentPage: page = DEFAULT_PAGE,
    perPage: perPageSize = DEFAULT_PER_PAGE,
    filter = null,
    market = null,
    status = null,
    executive = null,
    customer = null,
    travel_date = null,
    last_communication = null,
    templates = null,
    filterBy_,
    filterByType_,
  }: any = {}) => {
    isLoading.value = true;

    try {
      const formatted_travel_date =
        travel_date && Array.isArray(travel_date)
          ? travel_date.map((date) => date.format('YYYY-MM-DD'))
          : null;

      const formatted_last_communication =
        last_communication && Array.isArray(last_communication)
          ? last_communication.map((date) => date.format('YYYY-MM-DD'))
          : null;

      const payload_ = {
        page,
        per_page: perPageSize,
        filter,
        market,
        status,
        executive,
        customer,
        templates,
        travel_date: formatted_travel_date,
        last_communication: formatted_last_communication,
        filterBy: filterBy_,
        filterByType: filterByType_,
      };

      console.log('Previus payload: ', payload_);

      const response = await fetchOrders(payload_);

      if (response && response.data) {
        const { orders: fetchedOrders, pagination, usedTemplates } = response.data;
        orders.value = fetchedOrders;
        currentPage.value = pagination.page;
        perPage.value = pagination.per_page;
        total.value = pagination.total || 0;
        usedTemplatesOptions.value = usedTemplates || [];
      } else {
        orders.value = [];
        total.value = 0;
        usedTemplatesOptions.value = [];
      }
    } catch (e: any) {
      error.value = e.message || 'An unknown error occurred while fetching orders.';
      orders.value = [];
      total.value = 0;
      usedTemplatesOptions.value = [];
    } finally {
      isLoading.value = false;
    }
  };

  const fetchByCodeAndOrderAction = async (code: string | number, order: number) => {
    loading.value = true;
    try {
      const response = await fetchByCodeAndOrder(code, order);
      return response?.data || response; // Ajustar según estructura de tu respuesta
    } catch (e: any) {
      error.value = e.message || 'Error al obtener detalles del pedido.';
      throw e;
    } finally {
      loading.value = false;
    }
  };

  const changePage = async (payload: any) => {
    await fetchAll({
      currentPage: payload.currentPage,
      perPage: payload.perPage,
      filterBy_: filterBy.value,
      filterByType_: filterByType.value,
    });
  };

  const sortBy = async (payload: any) => {
    await fetchAll({
      currentPage: currentPage.value,
      perPage: perPage.value,
      filterBy_: payload.filterBy,
      filterByType_: payload.filterByType,
    });
  };

  // const update = async (id: string, payload: any) => {};

  // Actualiza la observación de una orden
  const update = async (id: string, payload: any) => {
    loading.value = true;
    try {
      const { success } = await updateOrder(id, payload);
      // if (response && response.data) {
      //   console.log('Observation updated successfully:', response.data);
      //   errorModal.value = null;
      //   loading.value = false;
      // } else {
      //   console.error('Failed to update observation:', response);
      //   errorModal.value = 'Failed to update observation';
      //   loading.value = false;
      // }
      return success;
    } catch (e: any) {
      error.value = e.message || 'An unknown error occurred while updating the template.';
      return false;
    }
  };

  const downloadOrdersExcel = async (params: any = {}) => {
    isDownloading.value = true;
    error.value = null;
    try {
      const response = await downloadExcel(params);

      // Si la respuesta es un blob de tipo application/json, es un error de la API.
      if (response instanceof Blob && response.type.includes('application/json')) {
        const errorJson = JSON.parse(await response.text());
        throw new Error(errorJson.message || 'Ocurrió un error al generar el reporte.');
      }

      // Validar que la respuesta sea un Blob. Si no lo es, es probable que sea un error de la API.
      if (!(response instanceof Blob)) {
        throw new Error(
          'La respuesta del servidor no es un archivo válido. Es posible que no haya datos para exportar o haya ocurrido un error.'
        );
      }

      const blob = response;

      // Crear un elemento de enlace, establecer la URL del blob y simular un clic para descargar
      const link = document.createElement('a');
      link.href = URL.createObjectURL(blob);

      const filename = `Reporte-de-Ordenes-${new Date().toISOString().slice(0, 10)}.xlsx`;
      link.setAttribute('download', filename);
      document.body.appendChild(link);
      link.click();

      // Limpiar eliminando el enlace y revocando la URL del objeto
      document.body.removeChild(link);
      URL.revokeObjectURL(link.href);
    } catch (e: any) {
      error.value = e.message || 'Ocurrió un error desconocido al descargar el archivo Excel.';
    } finally {
      isDownloading.value = false;
    }
  };

  const sendFollowUpEmail = async (
    code: string,
    order: number,
    payload: { templateId: string }
  ) => {
    loading.value = true;
    try {
      await sendFollowUp(code, order, payload);
      return true;
    } catch (e: any) {
      error.value = e.message || 'Error al enviar el seguimiento.';
      return false;
    } finally {
      loading.value = false;
    }
  };

  // NUEVO: Acción para envío masivo (Global o Lista)
  const sendFollowUpEmailBulk = async (payload: any) => {
    loading.value = true;
    error.value = null;
    try {
      // payload puede contener { isGlobal: true, filter: ..., excludedIds: ... }
      // o simplemente una lista si decides implementarlo diferente en back,
      // pero para el enfoque actual, el payload completo va al backend.
      await sendFollowUpBulk(payload);
      return true;
    } catch (e: any) {
      error.value = e.message || 'Error al enviar el seguimiento masivo.';
      throw e; // Relanzamos para que el componente maneje la notificación de error
    } finally {
      loading.value = false;
    }
  };

  const reassign = async (code: string, order: number, payload: any) => {
    loading.value = true;
    error.value = null;
    try {
      return await reassignOrder(code, order, payload);
    } catch (e: any) {
      error.value = e.message || 'Ocurrió un error al reasignar la orden.';
      return false;
    } finally {
      loading.value = false;
    }
  };

  const updateStatus = async (orderId: string, payload: { statusId: string }) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await updateOrderStatus(orderId, payload);
      if (response && response.success) {
        return true;
      }
      error.value = response?.message || 'No se pudo actualizar el estado del pedido.';
      return false;
    } catch (e: any) {
      error.value = e.message || 'Ocurrió un error al actualizar el estado del pedido.';
      return false;
    } finally {
      loading.value = false;
    }
  };
  // NUEVO: Acción para actualización de estado masiva (Global o Lista)
  const updateStatusBulkAction = async (payload: any) => {
    loading.value = true;
    error.value = null;
    try {
      await updateStatusBulk(payload);
      return true;
    } catch (e: any) {
      error.value = e.message || 'Error al actualizar estados masivamente.';
      throw e;
    } finally {
      loading.value = false;
    }
  };

  const updateCancellationReason = async (
    orderId: string,
    payload: { cancellationReasonId: string }
  ) => {
    loading.value = true;
    error.value = null;
    try {
      const response = await updateOrderStatusCancellationReason(orderId, payload);
      if (response && response.success) {
        return true;
      }
      error.value = response?.message || 'No se pudo actualizar el motivo de cancelación.';
      return false;
    } catch (e: any) {
      error.value = e.message || 'Ocurrió un error al actualizar el motivo de cancelación.';
      return false;
    } finally {
      loading.value = false;
    }
  };

  return {
    getTotal,
    getCurrentPage,
    getDefaultPerPage,
    getPerPage,
    isLoading,
    getOrders,
    isDownloading,
    orders,
    loading,
    error,
    loadingModal,
    errorModal,
    services,
    kpi,
    providers,
    sendServiceOrder,
    additionals,
    inited,
    fetchAll,
    fetchByCodeAndOrder: fetchByCodeAndOrderAction,
    sortBy,
    changePage,
    update,
    downloadOrdersExcel,
    sendFollowUpEmail,
    sendFollowUpEmailBulk,
    reassign,
    updateStatus,
    updateStatusBulk: updateStatusBulkAction,
    updateCancellationReason,
    isLoadingTemplates,
    usedTemplatesOptions,
    getUsedTemplatesOptions,
    allUsedTemplatesOptions,
    getAllUsedTemplatesOptions,
    fetchUsedTemplates: fetchUsedTemplatesAction,
  };
});
