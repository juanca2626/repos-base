import type { Rule } from 'ant-design-vue/es/form';
import { storeToRefs } from 'pinia';
import { computed, nextTick, ref, watch } from 'vue';
import { useRoute } from 'vue-router';

import { useGeneralInformationStore } from '@/modules/negotiations/supplier-new/store/negotiations/supplier-registration/general-information.store';
import { useSupplierGlobalComposable } from '@/modules/negotiations/supplier-new/composables/form/supplier-global.composable';
import { handleCompleteResponse, handleError } from '@/modules/negotiations/api/responseApi';
import { FormComponentEnum } from '@/modules/negotiations/supplier-new/enums/form-component.enum';
import {
  filterOption,
  mapItemsToOptions,
} from '@/modules/negotiations/supplier/register/helpers/supplierFormHelper';

import type {
  GeneralInformationRequest,
  GeneralInformationResponse,
  GeneralInformationSummary,
  ListItemGlobal,
} from '@/modules/negotiations/supplier-new/interfaces/supplier-registration';

type ChainSelectOption = {
  label: string;
  value: number | null;
};
import type { SupplierStatusEnum } from '@/modules/negotiations/suppliers/enums/supplier-status.enum';
import { SupplierStatusEnum as SupplierStatus } from '@/modules/negotiations/suppliers/enums/supplier-status.enum';

import { useSupplierGeneralInformationService } from '@/modules/negotiations/supplier-new/service/supplier-general-information.service';
import { useSupplierModulesComposable } from '@/modules/negotiations/supplier-new/composables/supplier-modules.composable';
import { useSupplierClassificationStoreFacade } from '@/modules/negotiations/supplier-new/composables/form/negotiations/supplier-registration/supplier-classification-store-facade.composable';
import { useSupplierCompleteData } from '@/modules/negotiations/supplier-new/composables/form/negotiations/use-supplier-complete-data.composable';

import { generalInformationSupplierStatus } from '@/modules/negotiations/suppliers/constants/supplier-status';

export function useGeneralInformationComposable() {
  const route = useRoute();
  const {
    supplierId,
    isEditMode,
    getShowFormComponent,
    getIsEditFormComponent,
    handleSavedFormSpecific,
    handleShowFormSpecific,
    handleIsEditFormSpecific,
    openNextSection,
    markItemComplete,
  } = useSupplierGlobalComposable();

  const { updateGeneralInformation } = useSupplierGeneralInformationService;
  const { loadSupplierModules } = useSupplierModulesComposable();
  const { supplierClassificationId } = useSupplierClassificationStoreFacade();

  const { completeData, refetch: refetchCompleteData } = useSupplierCompleteData();

  const giStore = useGeneralInformationStore();
  const { formRef } = storeToRefs(giStore);
  const { formState, resetFormState } = giStore;

  const isLoading = ref(false);
  const isLoadingButton = ref(false);
  const saving = ref(false);

  const persistedSnapshot = ref<GeneralInformationRequest | null>(null);
  const originalFormState = ref<typeof formState | null>(null);

  const isStaffClassification = computed(
    () =>
      supplierClassificationId.value !== undefined &&
      supplierClassificationId.value !== null &&
      Number(supplierClassificationId.value) === Number('STA')
  );

  const dniRules = computed<Rule[]>(() =>
    isStaffClassification.value
      ? [
          { required: true, message: 'El número de DNI es obligatorio.', trigger: 'change' },
          { pattern: /^\d{8}$/, message: 'El DNI debe tener 8 dígitos', trigger: 'change' },
        ]
      : []
  );

  const formRules = computed<Record<string, Rule[]>>(() => ({
    code: [{ required: true, message: 'El código es obligatorio.', trigger: 'change' }],
    businessName: [
      { required: true, message: 'El nombre comercial es obligatorio.', trigger: 'change' },
    ],
    rucNumber: [
      { required: true, message: 'El RUC es obligatorio.', trigger: 'change' },
      { pattern: /^\d{11}$/, message: 'El RUC debe tener 11 dígitos', trigger: 'change' },
    ],
    dniNumber: dniRules.value,
    companyName: [
      { required: true, message: 'La razón social es obligatoria.', trigger: 'change' },
    ],
    authorizedManagement: [
      { required: true, message: 'El campo es obligatorio.', trigger: 'change' },
    ],
    chainId: [
      {
        validator: (rule: any, value: any) => {
          if (isStaffClassification.value) {
            return Promise.resolve();
          }
          if (value === undefined) {
            return Promise.reject('Debe seleccionar una cadena.');
          }
          return Promise.resolve();
        },
        trigger: 'change',
      },
    ],
    status: [{ required: true, message: 'El estado es obligatorio.', trigger: 'change' }],
    reason_state: [
      {
        validator: (rule: any, value: any) => {
          if (shouldShowReasonField.value) {
            if (
              formState.status === SupplierStatus.SUSPENDED ||
              formState.status === SupplierStatus.INACTIVE
            ) {
              if (!value || !value.trim()) {
                return Promise.reject('El motivo es obligatorio para este estado.');
              }
            }
          }
          return Promise.resolve();
        },
        trigger: 'change',
      },
    ],
  }));

  const chains = ref<ChainSelectOption[]>([]);

  const getIsFormValid = computed(() => {
    const {
      code,
      businessName,
      companyName,
      rucNumber,
      authorizedManagement,
      status,
      chainId,
      dniNumber,
      reason_state,
    } = formState;

    const basicFields = !!(
      code &&
      businessName &&
      companyName &&
      rucNumber &&
      authorizedManagement !== null &&
      status
    );

    if (!basicFields) return false;

    if (shouldShowReasonField.value) {
      if (status === SupplierStatus.SUSPENDED || status === SupplierStatus.INACTIVE) {
        if (!reason_state || !reason_state.trim()) {
          return false;
        }
      }
    }

    if (isStaffClassification.value) {
      return !!dniNumber;
    } else {
      return chainId !== undefined;
    }
  });

  const getConfirmOptions = () => [
    { value: true, label: 'Sí' },
    { value: false, label: 'No' },
  ];

  const supplierStatusOptions = computed(() =>
    Object.entries(generalInformationSupplierStatus).map(([value, label]) => ({
      value: value as SupplierStatusEnum,
      label,
    }))
  );

  const findChainName = (chainId?: number | null) =>
    chainId ? (chains.value.find((row) => row.value === chainId)?.label ?? '') : 'Ninguno';

  const findStatusName = (status?: SupplierStatusEnum) =>
    status ? (supplierStatusOptions.value.find((row) => row.value === status)?.label ?? '') : '';

  const generalInformationSummary = computed<ListItemGlobal[]>(() => {
    const summaryFields: GeneralInformationSummary[] = [
      { key: 'code', label: 'Código:' },
      { key: 'businessName', label: 'Nombre comercial:' },
      ...(!isStaffClassification.value
        ? ([
            {
              key: 'chainId',
              label: 'Cadena:',
              format: (chainId: number | null) => findChainName(chainId),
            },
          ] as GeneralInformationSummary[])
        : []),
      { key: 'companyName', label: 'Razón social:' },
      { key: 'rucNumber', label: 'Número de RUC:' },
      ...(isStaffClassification.value
        ? ([{ key: 'dniNumber', label: 'Número de DNI:' }] as GeneralInformationSummary[])
        : []),
      {
        key: 'authorizedManagement',
        label: 'Proveedor autorizado por gerencia:',
        format: (value: boolean) => (value ? 'Sí' : 'No'),
      },
      {
        key: 'status',
        label: 'Estado:',
        format: (status: any) => findStatusName(status),
      },
      ...(shouldShowReasonField.value && formState.reason_state
        ? ([{ key: 'reason_state', label: 'Motivo:' }] as GeneralInformationSummary[])
        : []),
    ];

    const result = summaryFields.map(({ key, label, format }) => ({
      title: label,
      value: format ? format((formState as any)[key]) : (formState as any)[key],
    }));

    return result;
  });

  const hasSignificantData = computed(() => {
    return !!(
      formState.code ||
      formState.businessName ||
      formState.companyName ||
      formState.rucNumber ||
      formState.dniNumber ||
      formState.chainId !== undefined ||
      formState.status
    );
  });

  const hasPersistedData = computed(() => {
    if (originalFormState.value) {
      return !!(
        (originalFormState.value.code && originalFormState.value.code.trim()) ||
        (originalFormState.value.businessName && originalFormState.value.businessName.trim()) ||
        (originalFormState.value.companyName && originalFormState.value.companyName.trim()) ||
        (originalFormState.value.rucNumber && originalFormState.value.rucNumber.trim()) ||
        (originalFormState.value.dniNumber && originalFormState.value.dniNumber.trim()) ||
        originalFormState.value.chainId !== undefined ||
        originalFormState.value.status ||
        originalFormState.value.authorizedManagement !== null
      );
    }
    return !!persistedSnapshot.value;
  });

  const shouldShowReasonField = computed(() => {
    const currentStatus = formState.status;
    const originalStatus = originalFormState.value?.status;

    if (currentStatus === SupplierStatus.SUSPENDED || currentStatus === SupplierStatus.INACTIVE) {
      return true;
    }

    if (
      isEditMode.value &&
      currentStatus === SupplierStatus.ACTIVE &&
      (originalStatus === SupplierStatus.SUSPENDED || originalStatus === SupplierStatus.INACTIVE)
    ) {
      return true;
    }

    return false;
  });

  watch(shouldShowReasonField, (newValue) => {
    if (!newValue) {
      formState.reason_state = null;
    }
  });

  const toRequest = (): GeneralInformationRequest => ({
    code: formState.code,
    business_name: formState.businessName,
    company_name: formState.companyName,
    ruc_number: formState.rucNumber,
    dni_number: formState.dniNumber,
    authorized_management: formState.authorizedManagement,
    chain_id: formState.chainId ?? null,
    status: formState.status!,
    reason_state: formState.reason_state,
  });

  const markGeneralInformationComplete = () => {
    handleSavedFormSpecific(FormComponentEnum.GENERAL_INFORMATION, true);
    handleShowFormSpecific(FormComponentEnum.GENERAL_INFORMATION, true);
    handleIsEditFormSpecific(FormComponentEnum.GENERAL_INFORMATION, true);
    markItemComplete('general_information');
  };

  const cleanFields = () => {
    if (isStaffClassification.value) {
      formState.chainId = null;
      formState.chainName = '';
    } else {
      formState.dniNumber = null;
    }
  };

  const hydrateForm = async (data: GeneralInformationResponse) => {
    const hasDataFromServer = !!(
      (data.code && data.code.trim()) ||
      (data.business_name && data.business_name.trim()) ||
      (data.company_name && data.company_name.trim()) ||
      (data.ruc_number && data.ruc_number.trim()) ||
      (data.dni_number && data.dni_number.trim()) ||
      data.supplier_chain?.chain_id ||
      (data.authorized_management !== null && data.authorized_management !== false)
    );

    if (!hasDataFromServer) {
      resetFormState();
      originalFormState.value = null;
      persistedSnapshot.value = null;
      handleShowFormSpecific(FormComponentEnum.GENERAL_INFORMATION, false);
      handleIsEditFormSpecific(FormComponentEnum.GENERAL_INFORMATION, false);
      handleSavedFormSpecific(FormComponentEnum.GENERAL_INFORMATION, false);
      return;
    }

    const snapshot: GeneralInformationRequest = {
      code: data.code,
      business_name: data.business_name,
      company_name: data.company_name,
      ruc_number: data.ruc_number,
      dni_number: data.dni_number,
      authorized_management: data.authorized_management,
      chain_id: data.supplier_chain?.chain_id ?? null,
      status: data.status!,
      reason_state: data.reason_state,
    };
    persistedSnapshot.value = { ...snapshot };

    const formStateSnapshot = {
      code: data.code,
      businessName: data.business_name,
      companyName: data.company_name,
      rucNumber: data.ruc_number,
      dniNumber: data.dni_number,
      authorizedManagement: data.authorized_management,
      chainId: data.supplier_chain?.chain_id ?? null,
      chainName: '',
      status: data.status,
      statusName: '',
      reason_state: data.reason_state,
    };

    originalFormState.value = { ...formStateSnapshot };

    Object.assign(formState, formStateSnapshot);

    cleanFields();

    await nextTick();
    markGeneralInformationComplete();
  };

  const loadData = async () => {
    if (!supplierId.value) {
      resetFormState();
      originalFormState.value = null;
      persistedSnapshot.value = null;
      handleShowFormSpecific(FormComponentEnum.GENERAL_INFORMATION, false);
      handleIsEditFormSpecific(FormComponentEnum.GENERAL_INFORMATION, false);
      handleSavedFormSpecific(FormComponentEnum.GENERAL_INFORMATION, false);
      return;
    }

    try {
      if (completeData.value?.data?.data?.general_info) {
        await hydrateForm(completeData.value.data.data.general_info);
      } else {
        resetFormState();
        originalFormState.value = null;
        persistedSnapshot.value = null;
        handleShowFormSpecific(FormComponentEnum.GENERAL_INFORMATION, false);
        handleIsEditFormSpecific(FormComponentEnum.GENERAL_INFORMATION, false);
        handleSavedFormSpecific(FormComponentEnum.GENERAL_INFORMATION, false);
      }
    } catch (err) {
      handleError(err as Error);
      resetFormState();
      originalFormState.value = null;
      persistedSnapshot.value = null;
      handleShowFormSpecific(FormComponentEnum.GENERAL_INFORMATION, false);
      handleIsEditFormSpecific(FormComponentEnum.GENERAL_INFORMATION, false);
      handleSavedFormSpecific(FormComponentEnum.GENERAL_INFORMATION, false);
    }
  };

  const loadChains = async () => {
    try {
      if (completeData.value?.data?.data?.resources?.chains?.chains) {
        const chainsData = completeData.value.data.data.resources.chains.chains;
        const mappedChains = mapItemsToOptions(chainsData) as ChainSelectOption[];
        chains.value = [{ label: 'Ninguno', value: null }, ...mappedChains];
      } else {
        chains.value = [{ label: 'Ninguno', value: null }];
      }
    } catch {
      chains.value = [{ label: 'Ninguno', value: null }];
    }
  };

  const handleClose = () => {
    try {
      isLoading.value = false;
      isLoadingButton.value = false;
      saving.value = false;

      if (originalFormState.value) {
        Object.assign(formState, JSON.parse(JSON.stringify(originalFormState.value)));
        formRef.value?.clearValidate?.();
        handleIsEditFormSpecific(FormComponentEnum.GENERAL_INFORMATION, true);
      } else {
        resetFormState();
        handleShowFormSpecific(FormComponentEnum.GENERAL_INFORMATION, false);
        handleIsEditFormSpecific(FormComponentEnum.GENERAL_INFORMATION, false);
      }
    } catch {
      isLoading.value = false;
      isLoadingButton.value = false;
      saving.value = false;
    }
  };

  const handleSaveForm = async () => {
    if (!supplierId.value || saving.value) return;
    try {
      saving.value = true;
      isLoadingButton.value = true;

      const response = await updateGeneralInformation(supplierId.value, toRequest());
      if (response?.success) {
        handleCompleteResponse(response);

        await loadSupplierModules();

        // ✅ IMPORTANTE: Refrescar la query unificada para obtener los datos actualizados
        await refetchCompleteData();

        // Esperar a que el watch de completeData rehidrate el formulario
        await loadData();

        markGeneralInformationComplete();
        openNextSection(FormComponentEnum.COMMERCIAL_LOCATION);
      }
    } catch (error) {
      handleError(error as Error);
    } finally {
      isLoadingButton.value = false;
      saving.value = false;
    }
  };

  const handleSave = async () => {
    try {
      await formRef.value?.validate();
      await handleSaveForm();
    } catch {
      // Validation error
    }
  };

  const handleShowForm = () => {
    if (!originalFormState.value) {
      resetFormState();
      handleShowFormSpecific(FormComponentEnum.GENERAL_INFORMATION, true);
      handleIsEditFormSpecific(FormComponentEnum.GENERAL_INFORMATION, false);
      handleSavedFormSpecific(FormComponentEnum.GENERAL_INFORMATION, false);
    } else {
      handleIsEditFormSpecific(FormComponentEnum.GENERAL_INFORMATION, false);
    }
  };

  const spinTip = computed(() => (isLoadingButton.value ? 'Guardando...' : 'Cargando...'));
  const spinning = computed(() => isLoading.value || isLoadingButton.value);

  watch(
    () => supplierClassificationId.value,
    async (newValue, oldValue) => {
      if (newValue === oldValue || newValue == null) return;
      await loadChains();
      cleanFields();
    },
    { immediate: true }
  );

  watch(
    supplierId,
    async (newId, oldId) => {
      resetFormState();
      persistedSnapshot.value = null;
      originalFormState.value = null;

      handleShowFormSpecific(FormComponentEnum.GENERAL_INFORMATION, false);
      handleIsEditFormSpecific(FormComponentEnum.GENERAL_INFORMATION, false);
      handleSavedFormSpecific(FormComponentEnum.GENERAL_INFORMATION, false);

      if (!newId) {
        return;
      }

      if (newId === oldId) return;

      await loadData();
    },
    { immediate: true }
  );

  watch(
    () => route.params.id,
    async (newParam, oldParam) => {
      const rid = newParam ? Number(newParam) : null;
      if (!rid || String(newParam) === String(oldParam)) return;
      if (supplierId.value === rid) return;

      resetFormState();
      persistedSnapshot.value = null;
      originalFormState.value = null;
      await loadData();
    }
  );

  watch(
    () => completeData.value,
    async (newData) => {
      if (!supplierId.value || !newData) {
        return;
      }

      if (newData.data?.data?.resources?.chains?.chains) {
        await loadChains();
      }

      if (newData.data?.data?.general_info) {
        await hydrateForm(newData.data.data.general_info);
      }

      isLoading.value = false;
    },
    { immediate: true, deep: true }
  );

  return {
    formState,
    formRef,
    isLoading,
    isLoadingButton,
    spinning,
    spinTip,
    chains,

    formRules,
    getIsFormValid,
    generalInformationSummary,
    supplierStatusOptions,
    isStaffClassification,
    filterOption,
    getShowFormComponent,
    getIsEditFormComponent,
    isEditMode,
    getConfirmOptions,
    hasSignificantData,
    hasPersistedData,
    shouldShowReasonField,

    handleClose,
    handleSave,
    handleShowForm,
  };
}
