import { ref } from 'vue';
import { notification } from 'ant-design-vue';
import { claimsApi } from '../../services/api';

/**
 * Composable for Claims API operations
 * Encapsulates all claims-related API calls and state management
 */
export function useClaimsApi() {
  const loading = ref(false);
  const saving = ref(false);

  /**
   * Search claims with filters
   * @param {Object} params - Search parameters
   * @returns {Promise<Array>} List of claims
   */
  const searchClaims = async (params) => {
    loading.value = true;
    try {
      const response = await claimsApi.get('/search', { params });
      return response.data.data || [];
    } catch (error) {
      console.error('Error fetching claims:', error);
      notification.error({
        message: 'Error',
        description: 'Error al cargar listado de reclamos',
      });
      return [];
    } finally {
      loading.value = false;
    }
  };

  /**
   * Get claim detail by file number
   * @param {string} nroref - File number
   * @returns {Promise<Object|null>} Claim detail
   */
  const getClaimDetail = async (nroref) => {
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
   * Save a new claim
   * @param {Object} claimData - Claim data
   * @returns {Promise<Object|null>} Created claim info
   */
  const saveClaim = async (claimData) => {
    saving.value = true;
    try {
      const response = await claimsApi.post('/save', claimData);

      // Check if API returned success or error
      if (response.data?.type === 'error' || response.data?.text?.toLowerCase().includes('error')) {
        notification.error({
          message: 'Error',
          description: response.data?.text || 'Error al guardar el reclamo',
        });
        return null;
      }

      notification.success({
        message: 'Éxito',
        description: 'Reclamo creado exitosamente',
      });
      return response.data.data;
    } catch (error) {
      console.error('Error saving claim:', error);
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
   * Update an existing claim
   * @param {Object} claimData - Claim data
   * @returns {Promise<boolean>} Success status
   */
  const updateClaim = async (claimData) => {
    saving.value = true;
    try {
      const response = await claimsApi.put('/update', claimData);

      // Check if API returned success or error
      if (response.data?.type === 'error' || response.data?.text?.toLowerCase().includes('error')) {
        notification.error({
          message: 'Error',
          description: response.data?.text || 'Error al actualizar el reclamo',
        });
        return false;
      }

      notification.success({
        message: 'Éxito',
        description: 'Reclamo actualizado',
      });
      return true;
    } catch (error) {
      console.error('Error updating claim:', error);
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
   * Delete a claim
   * @param {number} codref - Claim reference code
   * @param {string} usuario - User performing the deletion
   * @returns {Promise<boolean>} Success status
   */
  const deleteClaim = async (codref, usuario = 'SYSTEM') => {
    try {
      await claimsApi.delete(`/delete/${codref}`, {
        params: { usuario },
      });
      notification.success({
        message: 'Éxito',
        description: 'Reclamo eliminado',
      });
      return true;
    } catch (error) {
      console.error('Error deleting claim:', error);
      notification.error({
        message: 'Error',
        description: error.response?.data?.text || 'Error al eliminar',
      });
      return false;
    }
  };

  /**
   * Get all replies for a claim
   * @param {string} nrocom - Claim number (padded format)
   * @returns {Promise<Array>} List of replies
   */
  const getReplies = async (nrocom) => {
    loading.value = true;
    try {
      const response = await claimsApi.get('/replies', {
        params: { nrocom },
      });
      return response.data.data || [];
    } catch (error) {
      console.error('Error fetching replies:', error);
      notification.error({
        message: 'Error',
        description: 'Error al cargar réplicas',
      });
      return [];
    } finally {
      loading.value = false;
    }
  };

  /**
   * Add a reply to a claim
   * @param {Object} replyData - Reply data {codref, comment, nrolin}
   * @returns {Promise<boolean>} Success status
   */
  const addReply = async (replyData) => {
    saving.value = true;
    try {
      await claimsApi.post('/replies/add', replyData);
      notification.success({
        message: 'Éxito',
        description: 'Réplica agregada',
      });
      return true;
    } catch (error) {
      console.error('Error adding reply:', error);
      notification.error({
        message: 'Error',
        description: error.response?.data?.text || 'Error al agregar réplica',
      });
      return false;
    } finally {
      saving.value = false;
    }
  };

  /**
   * Remove a reply from a claim
   * @param {string} nrocom - Claim number (padded format)
   * @param {number} nrolin - Line number of the reply
   * @returns {Promise<boolean>} Success status
   */
  const removeReply = async (nrocom, nrolin) => {
    try {
      await claimsApi.delete('/replies/remove', {
        params: { nrocom, nrolin },
      });
      notification.success({
        message: 'Éxito',
        description: 'Réplica eliminada',
      });
      return true;
    } catch (error) {
      console.error('Error removing reply:', error);
      notification.error({
        message: 'Error',
        description: error.response?.data?.text || 'Error al eliminar réplica',
      });
      return false;
    }
  };

  /**
   * Get service codes for a claim
   * @param {string} nrocom - Claim number (padded format)
   * @returns {Promise<Array>} List of service codes
   */
  const getServiceCodes = async (nrocom) => {
    loading.value = true;
    try {
      const response = await claimsApi.get('/services', {
        params: { nrocom },
      });
      return response.data.data || [];
    } catch (error) {
      console.error('Error fetching service codes:', error);
      notification.error({
        message: 'Error',
        description: 'Error al cargar códigos de servicio',
      });
      return [];
    } finally {
      loading.value = false;
    }
  };

  /**
   * Add a service code to a claim
   * @param {Object} serviceData - Service code data {codref, codsvs, comment, nrolin}
   * @returns {Promise<boolean>} Success status
   */
  const addServiceCode = async (serviceData) => {
    saving.value = true;
    try {
      await claimsApi.post('/services/add', serviceData);
      notification.success({
        message: 'Éxito',
        description: 'Código de servicio agregado',
      });
      return true;
    } catch (error) {
      console.error('Error adding service code:', error);
      notification.error({
        message: 'Error',
        description: error.response?.data?.text || 'Error al agregar código de servicio',
      });
      return false;
    } finally {
      saving.value = false;
    }
  };

  /**
   * Remove a service code from a claim
   * @param {number} codref - Claim reference code
   * @param {number} nrolin - Line number of the service code
   * @returns {Promise<boolean>} Success status
   */
  const removeServiceCode = async (nrocom, nrolin) => {
    try {
      await claimsApi.delete('/services/remove', {
        params: { nrocom, nrolin },
      });
      notification.success({
        message: 'Éxito',
        description: 'Código de servicio eliminado',
      });
      return true;
    } catch (error) {
      console.error('Error removing service code:', error);
      notification.error({
        message: 'Error',
        description: error.response?.data?.text || 'Error al eliminar código de servicio',
      });
      return false;
    }
  };

  return {
    loading,
    saving,
    searchClaims,
    getClaimDetail,
    saveClaim,
    updateClaim,
    deleteClaim,
    getReplies,
    addReply,
    removeReply,
    getServiceCodes,
    addServiceCode,
    removeServiceCode,
    transformClaimData,
  };
}

/**
 * Transform API claim data to table format
 * @param {Object} claim - Raw claim data from API
 * @returns {Object} Transformed claim data for table
 */
export const transformClaimData = (claim) => ({
  key: claim.codref,
  codref: claim.codref,
  nrocom: claim.nrocom,
  claimCode: claim.nrocom,
  fileNumber: claim.nroref,
  user: claim.codusu,
  client: claim.razon,
  fileName: claim.nombre_file,
  kam: claim.razon_kam,
  ejeqr: claim.operad,
  receiptDate: claim.fecser,
  resolutionDate: claim.fecfin,
  mgmtDays: claim.dias || 0,
  comment: claim.comment,
  respue: claim.respue,
  type:
    claim.tipore === 'FK' && claim.estado_reclamo === 'CE'
      ? 'INFUNDADO'
      : claim.tipore === 'OK' && claim.estado_reclamo === 'CE'
        ? 'OBJETIVO'
        : '',
  status:
    claim.estado_reclamo && claim.estado_reclamo !== ''
      ? claim.estado_reclamo === 'PE'
        ? 'EN PROCESO'
        : 'CERRADO'
      : '',
  // Additional fields for editing
  nroord: claim.nroord,
  tipo_monto: claim.tipo_monto,
  compensacion_reembolso: claim.comree,
  total_compensacion: claim.montot,
  monto_compensacion: claim.moncom,
  monto_reembolso: claim.monree,
  observaciones_compensacion: claim.obscom || claim.OBSCOM,
  observaciones_reembolso: claim.obsree || claim.OBSREE,
  area: claim.area,
  specialist: claim.codope,
  fecser: claim.fecser,
  fecfin: claim.fecfin,
});
