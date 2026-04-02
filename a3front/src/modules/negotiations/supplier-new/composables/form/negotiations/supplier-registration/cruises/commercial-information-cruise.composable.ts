import { computed, reactive, ref, watch, nextTick } from 'vue';
import {
  handleCompleteResponse,
  handleError,
  type HttpError,
} from '@/modules/negotiations/api/responseApi';
import { FormComponentEnum } from '@/modules/negotiations/supplier-new/enums/form-component.enum';
import { useSupplierGlobalComposable } from '@/modules/negotiations/supplier-new/composables/form/supplier-global.composable';
import { useSupplierModulesComposable } from '@/modules/negotiations/supplier-new/composables/supplier-modules.composable';
import type {
  ListItemGlobal,
  SupplierInformationResponse,
  CommercialInformationCruiseForm,
} from '@/modules/negotiations/supplier-new/interfaces/supplier-registration';
import { useSupplierInformationService } from '@/modules/negotiations/supplier-new/service/supplier-information.service';

export function useCommercialInformationCruiseComposable() {
  // =========================
  // 1) Estado local
  // =========================
  const isLoading = ref(false);
  const isLoadingButton = ref(false);
  const saving = ref(false); // evita doble submit

  const spinTip = computed(() => (isLoadingButton.value ? 'Guardando...' : 'Cargando...'));
  const spinning = computed(() => isLoading.value || isLoadingButton.value);

  // =========================
  // 2) Global / servicios
  // =========================
  const {
    supplierId,
    isEditMode,
    getShowFormComponent,
    getIsEditFormComponent,
    handleShowFormSpecific,
    handleIsEditFormSpecific,
    handleSavedFormSpecific,
    openNextSection,
    markItemComplete,
  } = useSupplierGlobalComposable();

  const { loadSupplierModules } = useSupplierModulesComposable();

  const { saveSupplierInformation, showSupplierInformation } = useSupplierInformationService;

  // =========================
  // 3) Form state + snapshot
  // =========================
  const initFormState: CommercialInformationCruiseForm = {
    additionalInformation: null,
    requirements: null,
    restrictions: null,
  };

  // usamos nuevas referencias para beneficiar reactividad
  const formState = reactive<CommercialInformationCruiseForm>({ ...initFormState });
  const originalFormState = reactive<CommercialInformationCruiseForm>({ ...initFormState });

  // =========================
  // 4) Validación / resumen
  // =========================
  // El campo es opcional, siempre es válido
  const getIsFormValid = computed(() => true);

  // Tip seguro para summary (evita error de `format`)
  type CruiseSummaryField = {
    key: keyof CommercialInformationCruiseForm;
    label: string;
    format?: (v: any) => string;
  };

  const commercialInformationSummary = computed<ListItemGlobal[]>(() => {
    const fields: CruiseSummaryField[] = [
      { key: 'requirements', label: 'Requisitos:' },
      { key: 'restrictions', label: 'Restricciones:' },
      { key: 'additionalInformation', label: 'Data adicional:' },
    ];

    return fields.map(({ key, label, format }) => {
      const raw = formState[key];
      return { title: label, value: format ? format(raw) : (raw as any) };
    });
  });

  // =========================
  // 5) Mappers
  // =========================
  const toRequest = () => ({
    additional_information: formState.additionalInformation,
    requirements: formState.requirements,
    restrictions: formState.restrictions,
  });

  const hydrateFromResponse = async (data: SupplierInformationResponse) => {
    const loaded: CommercialInformationCruiseForm = {
      additionalInformation: data.additional_information ?? null,
      requirements: data.requirements ?? null,
      restrictions: data.restrictions ?? null,
    };

    Object.assign(formState, loaded);
    Object.assign(originalFormState, loaded);

    const hasData = !!(loaded.additionalInformation || loaded.requirements || loaded.restrictions);
    if (hasData) {
      await nextTick();
      handleSavedFormSpecific(FormComponentEnum.COMMERCIAL_INFORMATION, true);
      handleIsEditFormSpecific(FormComponentEnum.COMMERCIAL_INFORMATION, true);
      handleShowFormSpecific(FormComponentEnum.COMMERCIAL_INFORMATION, true);
      markItemComplete('commercial_information'); // Backend usa snake_case
    }
  };

  const resetFormState = () => {
    Object.assign(formState, { ...initFormState });
  };

  // =========================
  // 6) Carga / persistencia
  // =========================
  const loadData = async () => {
    if (!supplierId.value) return;
    try {
      isLoading.value = true;
      const resp = await showSupplierInformation(supplierId.value);
      if (resp?.success) {
        await hydrateFromResponse(resp.data);
      } else {
        resetFormState();
      }
    } finally {
      isLoading.value = false;
    }
  };

  const handleSaveForm = async () => {
    if (!supplierId.value || saving.value) return;
    try {
      saving.value = true;
      isLoadingButton.value = true;

      const response = await saveSupplierInformation(supplierId.value, toRequest());
      if (response?.success) {
        handleCompleteResponse(response);
        // recarga para refrescar el modo resumen con lo último
        await loadData();
        await loadSupplierModules();

        handleSavedFormSpecific(FormComponentEnum.COMMERCIAL_INFORMATION, true);
        handleIsEditFormSpecific(FormComponentEnum.COMMERCIAL_INFORMATION, true);
        markItemComplete('commercial_information'); // Backend usa snake_case
        openNextSection(FormComponentEnum.MODULE_SERVICES);
      }
    } catch (error) {
      handleError(error as HttpError);
      console.error('⛔ Error save:', error);
    } finally {
      isLoadingButton.value = false;
      saving.value = false;
    }
  };

  // =========================
  // 7) Acciones públicas
  // =========================
  const handleSave = async () => {
    try {
      await handleSaveForm();
    } catch (error) {
      console.error('⛔ Validation error:', error);
    }
  };

  const handleClose = () => {
    try {
      // Primero reseteamos los estados de loading
      isLoading.value = false;
      isLoadingButton.value = false;
      saving.value = false;

      if (isEditMode.value) {
        // En modo edición restauramos al estado original
        Object.assign(formState, JSON.parse(JSON.stringify(originalFormState)));
        handleIsEditFormSpecific(FormComponentEnum.COMMERCIAL_INFORMATION, true);

        // Si no hay datos, ocultamos el formulario completamente
        if (!originalFormState.additionalInformation) {
          handleShowFormSpecific(FormComponentEnum.COMMERCIAL_INFORMATION, false);
        }
      } else {
        // En modo creación
        resetFormState();
        handleShowFormSpecific(FormComponentEnum.COMMERCIAL_INFORMATION, false);
        handleIsEditFormSpecific(FormComponentEnum.COMMERCIAL_INFORMATION, true);
      }
    } catch (error) {
      console.error('Error al cerrar formulario:', error);
      // Aseguramos reseteo de estados en caso de error
      isLoading.value = false;
      isLoadingButton.value = false;
      saving.value = false;
    }
  };

  const handleShowForm = () => {
    handleIsEditFormSpecific(FormComponentEnum.COMMERCIAL_INFORMATION, false);
  };

  // =========================
  // 8) Watchers / lifecycle
  // =========================
  // Rehidrata cuando cambia de registro en la misma vista
  watch(
    supplierId,
    async (newId, oldId) => {
      if (!newId || newId === oldId) return;
      await loadData();
    },
    { immediate: true } // hidrata también al entrar en edición
  );

  // onMounted(loadData); // comentando para evitar doble carga

  // =========================
  // 9) Expuestos (ordenado)
  // =========================
  return {
    // estado / refs
    formState,
    isLoading,
    isLoadingButton,
    spinning,
    spinTip,
    isEditMode,

    // getters / summary
    getShowFormComponent,
    getIsEditFormComponent,
    getIsFormValid,
    commercialInformationSummary,

    // acciones
    handleClose,
    handleSave,
    handleShowForm,
  };
}
