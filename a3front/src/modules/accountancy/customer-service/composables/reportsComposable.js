import { ref } from 'vue';
import { notification } from 'ant-design-vue';
import { reportsApi, claimsApi } from '../../services/api';

/**
 * Composable for Reports API operations
 * Encapsulates all reports-related API calls and state management
 */
export function useReportsApi() {
  const loading = ref(false);
  const saving = ref(false);

  /**
   * Search reports with filters
   * @param {Object} params - Search parameters
   * @returns {Promise<Array>} List of reports
   */
  const searchReports = async (params) => {
    loading.value = true;
    try {
      const response = await reportsApi.get('/search', { params });
      const rawData = response.data.data || [];

      // Map API response fields to table structure
      return rawData.map((item) => ({
        key: item.nrocom,
        codref: item.nrocom,
        reportCode: item.nrocom || item.codref,
        fileNumber: item.nroref,
        user: item.codusu,
        client: item.razon,
        fileName: item.nombre_file,
        kam: item.razon_kam,
        ejeqr: item.operad,
        receiptDate: item.fecser,
        resolutionDate: item.fecfin,
        mgmtDays: item.dias || 0,
        subject: item.resume,
        status: getStatusLabel(item.estado_reclamo),
        isClosed: item.estado_reclamo === 'CE',

        // Additional fields for edit mode
        codusu: item.codusu,
        codope: item.codope,
        nroord: item.nroord,
        codcli: item.codcli,
        area: item.area,

        // Text fields for modal
        comment: item.comment || '',
        respue: item.respue || '',

        // Date fields
        fecser: item.fecser,
        fecfin: item.fecfin,
      }));
    } catch (error) {
      console.error('Error fetching reports:', error);
      notification.error({
        message: 'Error',
        description: 'Error al cargar listado de reportes',
      });
      return [];
    } finally {
      loading.value = false;
    }
  };

  /**
   * Get status label from code
   * @param {string} code - Status code (PE, CE, etc)
   * @returns {string} Status label
   */
  const getStatusLabel = (code) => {
    const statusMap = {
      PE: 'Pendiente',
      CE: 'Cerrado',
      P: 'Pendiente',
      C: 'Cerrado',
    };
    return statusMap[code] || code || 'Pendiente';
  };

  /**
   * Get report detail by file number
   * Uses claims API endpoint as reports share the same searchDetail endpoint
   * @param {string} nroref - File number
   * @returns {Promise<Object|null>} Report detail
   */
  const getReportDetail = async (nroref) => {
    if (!nroref) return null;

    try {
      const response = await claimsApi.get('/searchDetail', {
        params: { nroref },
      });
      return response.data.data;
    } catch (error) {
      console.error('Error searching detail:', error);
      notification.error({
        message: 'Error',
        description: error.response?.data?.text || 'File no encontrado',
      });
      return null;
    }
  };

  /**
   * Get report text (comment and response)
   * @param {string} nrocom - Report number (padded format)
   * @returns {Promise<Object|null>} Report text data
   */
  const getReportText = async (nrocom) => {
    try {
      const response = await reportsApi.get('/text', {
        params: { nrocom },
      });
      return response.data.data;
    } catch (error) {
      console.error('Error fetching report text:', error);
      notification.error({
        message: 'Error',
        description: 'Error al cargar texto del reporte',
      });
      return null;
    }
  };

  /**
   * Save a new report
   * @param {Object} reportData - Report data
   * @returns {Promise<Object|null>} Created report info
   */
  const saveReport = async (reportData) => {
    saving.value = true;
    try {
      const response = await reportsApi.post('/save', reportData);

      // Check if API returned success or error
      if (response.data?.type === 'error' || response.data?.text?.toLowerCase().includes('error')) {
        notification.error({
          message: 'Error',
          description: response.data?.text || 'Error al guardar el reporte',
        });
        return null;
      }

      notification.success({
        message: 'Éxito',
        description: 'Reporte creado exitosamente',
      });
      return response.data.data;
    } catch (error) {
      console.error('Error saving report:', error);
      notification.error({
        message: 'Error',
        description: error.response?.data?.text || 'Error al guardar',
      });
      return null;
    } finally {
      saving.value = false;
    }
  };

  /**
   * Update an existing report
   * @param {Object} reportData - Report data
   * @returns {Promise<boolean>} Success status
   */
  const updateReport = async (reportData) => {
    saving.value = true;
    try {
      const response = await reportsApi.put('/update', reportData);

      // Check if API returned success or error
      if (response.data?.type === 'error' || response.data?.text?.toLowerCase().includes('error')) {
        notification.error({
          message: 'Error',
          description: response.data?.text || 'Error al actualizar el reporte',
        });
        return false;
      }

      notification.success({
        message: 'Éxito',
        description: 'Reporte actualizado',
      });
      return true;
    } catch (error) {
      console.error('Error updating report:', error);
      notification.error({
        message: 'Error',
        description: error.response?.data?.text || 'Error al actualizar',
      });
      return false;
    } finally {
      saving.value = false;
    }
  };

  /**
   * Delete a report
   * @param {number} codref - Report reference code
   * @param {string} usuario - User performing the deletion
   * @returns {Promise<boolean>} Success status
   */
  const deleteReport = async (codref, usuario = 'SYSTEM') => {
    try {
      await reportsApi.delete(`/delete/${codref}`, {
        params: { usuario },
      });
      notification.success({
        message: 'Éxito',
        description: 'Reporte eliminado',
      });
      return true;
    } catch (error) {
      console.error('Error deleting report:', error);
      notification.error({
        message: 'Error',
        description: error.response?.data?.text || 'Error al eliminar',
      });
      return false;
    }
  };

  return {
    loading,
    saving,
    searchReports,
    getReportDetail,
    getReportText,
    saveReport,
    updateReport,
    deleteReport,
  };
}
