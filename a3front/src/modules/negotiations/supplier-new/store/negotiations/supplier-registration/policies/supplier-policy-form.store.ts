import { defineStore } from 'pinia';
import { reactive, ref } from 'vue';
import type {
  SupplierPolicyCloneResponse,
  SupplierPolicyForm,
} from '@/modules/negotiations/supplier-new/interfaces/supplier-registration';
import type { FormModeEnum } from '@/modules/negotiations/supplier-new/enums/supplier-registration/policies/form-mode.enum';

// Interfaz para la data completa de una política clonada
export interface ClonedPolicyData {
  // Información básica
  name: string;
  businessGroupId: string | null;
  dateFrom: string | null;
  dateTo: string | null;
  paxMin: number | null;
  paxMax: number | null;
  measurementUnit: string | null;
  isHotel: boolean;
  policySegmentationIds: number[];
  segmentationSpecifications: any[];
  // Reglas
  rules: any;
  // Configuración original
  configuration: any;
}

export const useSupplierPolicyFormStore = defineStore('supplierPolicyFormStore', () => {
  const formMode = ref<FormModeEnum | null>(null);
  const policyId = ref<string | null>(null); // Registro actual (editar) - MongoDB ObjectId
  const clonePolicyId = ref<string | null>(null); // Política a clonar (MongoDB ObjectId)

  const reloadPolicyData = ref<boolean>(false);
  const reloadHolidayCalendars = ref<boolean>(false);

  // Data de política clonada (para pre-llenar formularios sin crear en backend)
  const clonedPolicyData = ref<ClonedPolicyData | null>(null);

  const initialFormData: SupplierPolicyForm = {
    supplierId: null,
    businessGroupId: null,
    businessGroup: null,
    name: null,
    dateFrom: null,
    dateTo: null,
    paxMin: null,
    paxMax: null,
    measurementUnit: null,
    isHotel: false, // Para mostrar campos de hotel como Alcance de Cancelación
    policySegmentationIds: [], // for UI
    segmentationSpecifications: [], // for UI
    segmentationNamesSummary: null, // from API
  };

  const formState = reactive<SupplierPolicyForm>({ ...initialFormData });

  const policyCloneResponse = ref<SupplierPolicyCloneResponse | null>(null);

  const setFormMode = (value: FormModeEnum | null) => {
    formMode.value = value;
  };

  const setPolicyId = (value: string | null) => {
    policyId.value = value;
  };

  const setClonePolicyId = (value: string | null) => {
    clonePolicyId.value = value;
  };

  const setReloadPolicyData = (value: boolean) => {
    reloadPolicyData.value = value;
  };

  const setReloadHolidayCalendars = (value: boolean) => {
    reloadHolidayCalendars.value = value;
  };

  // Nota: structuredClone fallaba (DataCloneError) al recibir objetos reactivos
  // provenientes del spread de formState (proxy) y recursos externos.
  // Se reemplaza por una asignación superficial que preserva reactividad.
  const setFormState = (data: SupplierPolicyForm) => {
    // Shallow copy: arrays/objetos internos mantienen referencias (suficiente para este caso).
    // Si en el futuro se requiere aislamiento total, usar cloneDeep de lodash solo sobre campos necesarios.
    Object.assign(formState, { ...data });
  };

  const resetFormState = () => {
    setFormState(initialFormData);
  };

  const setPolicyCloneResponse = (value: SupplierPolicyCloneResponse | null) => {
    policyCloneResponse.value = value;
  };

  // Acciones para manejar la data clonada
  const setClonedPolicyData = (data: ClonedPolicyData | null) => {
    clonedPolicyData.value = data;
  };

  const clearClonedPolicyData = () => {
    clonedPolicyData.value = null;
  };

  return {
    formMode,
    policyId,
    clonePolicyId,
    reloadPolicyData,
    reloadHolidayCalendars,
    formState,
    policyCloneResponse,
    clonedPolicyData,
    setFormMode,
    setPolicyId,
    setClonePolicyId,
    setReloadPolicyData,
    setReloadHolidayCalendars,
    setFormState,
    resetFormState,
    setPolicyCloneResponse,
    setClonedPolicyData,
    clearClonedPolicyData,
  };
});
