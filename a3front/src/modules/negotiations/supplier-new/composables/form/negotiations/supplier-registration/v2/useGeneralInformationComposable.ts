import { handleCompleteResponse, handleError } from '@/modules/negotiations/api/responseApi';
import {
  extractGeneralInfo,
  getSupplierEditQueryKey,
  SUPPLIER_EDIT_KEYS,
} from '@/modules/negotiations/supplier-new/composables/form/negotiations/optimized-queries/supplier-complete.query';
import { useSupplierGlobalComposable } from '@/modules/negotiations/supplier-new/composables/form/supplier-global.composable';
import { useSupplierModulesComposable } from '@/modules/negotiations/supplier-new/composables/supplier-modules.composable';
import { useSupplierGeneralInformationService } from '@/modules/negotiations/supplier-new/service/supplier-general-information.service';
import { useSupplierResourceService } from '@/modules/negotiations/supplier-new/service/supplier-resources.service';
import { useSupplierService } from '@/modules/negotiations/supplier-new/service/supplier.service';
import { useSupplierClassificationStore } from '@/modules/negotiations/supplier-new/store/negotiations/supplier-registration/supplier-classification.store';
import { generalInformationSupplierStatus } from '@/modules/negotiations/suppliers/constants/supplier-status';
import { SupplierStatusEnum } from '@/modules/negotiations/suppliers/enums/supplier-status.enum';
import { useQuery } from '@tanstack/vue-query';
import type { FormInstance, Rule } from 'ant-design-vue/es/form';
import { storeToRefs } from 'pinia';
import { computed, reactive, ref, watch } from 'vue';

export interface GeneralInformationFormState {
  code: string;
  businessName: string;
  chainId: number | null | undefined;
  companyName: string;
  rucNumber: string;
  dniNumber: string;
  authorizedManagement: boolean | undefined;
  status: SupplierStatusEnum;
  reason_state: string;
}

export interface GeneralInformationReadData {
  code: string;
  businessName: string;
  chain: string | null;
  companyName: string;
  rucNumber: string;
  dniNumber: string;
  authorizedManagement: string;
  status: string;
  reason_state: string;
}

export function useGeneralInformationComposable() {
  // Composables y servicios (deben llamarse en el nivel superior)
  const {
    supplierId,
    markItemComplete,
    isEditMode: isGlobalEditMode,
    handleSetActiveItem,
  } = useSupplierGlobalComposable();
  const { loadSupplierModules } = useSupplierModulesComposable();
  const { updateGeneralInformation } = useSupplierGeneralInformationService;

  // Store de clasificación (para saber si es STAFF)
  const supplierClassificationStore = useSupplierClassificationStore();
  const {
    supplierClassificationId,
    supplierSubClassificationId,
    supplierSubClassificationHasSubSubs,
  } = storeToRefs(supplierClassificationStore);

  // Verificar si es clasificación STAFF
  const isStaffClassification = computed(() => {
    return supplierClassificationId.value === 'STA';
  });

  // Estado del modo edición local
  // En modo registro: true (mostrar formulario)
  // En modo edición: false inicialmente (mostrar lectura), cambia a true al hacer clic en "Editar"
  const isEditMode = ref(!isGlobalEditMode.value);

  // Loading para guardar/actualizar
  const isSaving = ref(false);

  // Form ref
  const formRef = ref<FormInstance>();

  // Estado del formulario
  const formState = reactive<GeneralInformationFormState>({
    code: '',
    businessName: '',
    chainId: undefined,
    companyName: '',
    rucNumber: '',
    dniNumber: '',
    authorizedManagement: false,
    status: SupplierStatusEnum.ACTIVE,
    reason_state: '',
  });

  // Query para cargar cadenas usando el nuevo endpoint con classification_code
  // Se ejecuta tanto en modo registro como en modo edición
  // Si no tiene subniveles (ej: TRN, ACU), usa supplierSubClassificationId
  // Si tiene subniveles o por defecto (ej: STA, TRP, AER), usa supplierClassificationId
  const codeForChains = computed(() => {
    return supplierSubClassificationHasSubSubs.value === false
      ? supplierSubClassificationId.value
      : supplierClassificationId.value;
  });

  const { data: chainsQueryData, isLoading: isLoadingChains } = useQuery({
    queryKey: computed(() => ['chains', codeForChains.value] as const),
    queryFn: async () => {
      const code = codeForChains.value;
      console.log('🔗 [Chains Query] Loading chains for code:', code);
      if (!code) return { chains: [] };

      try {
        const chains = await useSupplierResourceService.fetchChainsByCode(code);
        console.log('✅ [Chains Query] Loaded chains:', chains);
        return { chains };
      } catch (error) {
        console.error('❌ [Chains Query] Error loading chains:', error);
        return { chains: [] };
      }
    },
    enabled: computed(() => !!codeForChains.value),
    staleTime: 5 * 60 * 1000,
    refetchOnMount: false,
    refetchOnWindowFocus: false,
  });

  // Modo EDICIÓN: useQuery con el MISMO queryKey compartido. Sin HTTP extra, con reactividad.
  const { data: supplierData, isLoading: isLoadingSupplierData } = useQuery({
    queryKey: computed(() =>
      supplierId.value
        ? getSupplierEditQueryKey(supplierId.value)
        : (['supplier', 'edit-complete', '__disabled__'] as const)
    ),
    queryFn: async () =>
      useSupplierService.showSupplierCompleteData(supplierId.value!, {
        keys: [...SUPPLIER_EDIT_KEYS.filter((key) => key !== 'chains')], // Excluir chains del query principal
      }),
    enabled: computed(() => !!supplierId.value && isGlobalEditMode.value),
    staleTime: 5 * 60 * 1000,
    refetchOnMount: false,
    refetchOnWindowFocus: false,
  });

  // Extraer datos según el modo
  const chainsData = computed(() => {
    let rawChains: any[] = [];

    // Siempre usar el nuevo query de cadenas (tanto en modo edición como registro)
    if (chainsQueryData.value) {
      rawChains = chainsQueryData.value.chains || [];
    }

    return { chains: rawChains };
  });

  // Cargar datos del supplier al formulario cuando están disponibles (solo en modo edición)
  watch(
    () => supplierData.value,
    (data) => {
      if (data && isGlobalEditMode.value) {
        const generalInfo = extractGeneralInfo(data);

        if (generalInfo.generalInfo) {
          formState.code = generalInfo.generalInfo.code || '';
          formState.businessName = generalInfo.generalInfo.business_name || '';
          formState.companyName = generalInfo.generalInfo.company_name || '';
          formState.rucNumber = generalInfo.generalInfo.ruc_number || '';
          formState.dniNumber = generalInfo.generalInfo.dni_number || '';
          formState.authorizedManagement = generalInfo.generalInfo.authorized_management ?? false;
          formState.status =
            (generalInfo.generalInfo.status as SupplierStatusEnum) || SupplierStatusEnum.ACTIVE;
          formState.reason_state = (generalInfo.generalInfo as any).reason_state || '';

          // IMPORTANTE: Manejar correctamente null para "Ninguno"
          // El backend puede devolver chain_id directamente o dentro de supplier_chain
          let chainId: number | null | undefined;

          if ('chain_id' in generalInfo.generalInfo) {
            // Si chain_id viene directamente en general_info
            chainId = (generalInfo.generalInfo as any).chain_id;
          } else if (generalInfo.generalInfo.supplier_chain) {
            // Si viene dentro de supplier_chain
            chainId = generalInfo.generalInfo.supplier_chain.chain_id;
          } else {
            // Si no existe ninguna referencia, usar null (Ninguno)
            chainId = null;
          }

          // Asignar: preservar null, convertir undefined a null
          formState.chainId = chainId ?? null;

          // Si no hay datos reales, mantener en modo edición para que el usuario pueda ingresar info
          const hasData = !!(generalInfo.generalInfo.business_name || generalInfo.generalInfo.code);

          // Solo salir del modo edición si hay datos Y las cadenas ya se cargaron (o no hay chainId)
          if (hasData) {
            // Si no tiene chainId o las cadenas ya se cargaron, salir del modo edición
            if (!formState.chainId || !isLoadingChains.value) {
              isEditMode.value = false;
            }
            // Si tiene chainId pero las cadenas aún están cargando, esperar
            // (el watch de isLoadingChains lo manejará)
          } else {
            isEditMode.value = true;
          }
        }
      }
    },
    { immediate: true }
  );

  // Watch para salir del modo edición cuando las cadenas terminen de cargar
  watch(
    () => isLoadingChains.value,
    (loading, wasLoading) => {
      // Cuando termina de cargar (de true a false) y estamos en modo edición global con datos
      if (wasLoading && !loading && isGlobalEditMode.value && formState.chainId) {
        const hasData = !!(formState.businessName || formState.code);
        if (hasData) {
          isEditMode.value = false;
        }
      }
    }
  );

  // Lista de cadenas (agregar "Ninguno" como primera opción, igual que el original línea 412)
  const chains = computed(() => {
    // Siempre usar el nuevo query de cadenas (tanto en modo edición como registro)
    let rawChains: any[] = [];

    if (chainsQueryData.value) {
      rawChains = chainsQueryData.value.chains || [];
    }

    const mappedChains = rawChains.map((chain: any) => ({
      label: chain.name,
      value: chain.id,
    }));

    return [{ label: 'Ninguno', value: null }, ...mappedChains];
  });

  // true cuando el endpoint no devuelve cadenas → solo existe la opción "Ninguno"
  const hasNoChains = computed(() => {
    if (!chainsQueryData.value) return false;
    return (chainsQueryData.value.chains || []).length === 0;
  });

  // Auto-seleccionar null (Ninguno) y bloquear el campo cuando no hay cadenas
  watch(
    hasNoChains,
    (noChains) => {
      if (noChains) {
        formState.chainId = null;
      }
    },
    { immediate: true }
  );

  // Opciones de estado del proveedor (usar las mismas que el original)
  const supplierStatusOptions = computed(() =>
    Object.entries(generalInformationSupplierStatus).map(([value, label]) => ({
      value: value as SupplierStatusEnum,
      label,
    }))
  );

  // Determinar si se debe mostrar el campo "Motivo"
  const shouldShowReasonField = computed(() => {
    return (
      formState.status === SupplierStatusEnum.INACTIVE ||
      formState.status === SupplierStatusEnum.SUSPENDED
    );
  });

  // Loading para mostrar overlay (durante carga inicial Y al guardar)
  const isLoading = computed(() => {
    return isSaving.value || isLoadingSupplierData.value || isLoadingChains.value;
  });

  // Loading para deshabilitar campos durante carga inicial
  const isLoadingFields = computed(() => {
    return isLoadingSupplierData.value || isLoadingChains.value;
  });

  // Datos para el modo lectura
  const readData = computed<GeneralInformationReadData>(() => {
    let chainName = '-';

    if (formState.chainId) {
      // Si las cadenas aún están cargando, mostrar loading
      if (isLoadingChains.value) {
        chainName = 'Cargando...';
      } else {
        // Buscar en el array de chains disponibles del nuevo query
        const chain = chainsData.value.chains.find((c: any) => c.id === formState.chainId);

        if (chain) {
          chainName = chain.name;
        } else {
          // Si no se encuentra después de cargar, mostrar "Ninguno" o el ID
          chainName = formState.chainId ? `Cadena ${formState.chainId}` : 'Ninguno';
        }
      }
    } else {
      chainName = 'Ninguno';
    }

    const statusLabel =
      supplierStatusOptions.value.find((s) => s.value === formState.status)?.label ||
      formState.status;
    const authorizedManagementLabel = formState.authorizedManagement ? 'Sí' : 'No';

    return {
      code: formState.code || '-',
      businessName: formState.businessName || '-',
      chain: chainName,
      companyName: formState.companyName || '-',
      rucNumber: formState.rucNumber || '-',
      dniNumber: formState.dniNumber || '-',
      authorizedManagement: authorizedManagementLabel,
      status: statusLabel,
      reason_state: formState.reason_state || '-',
    };
  });

  // Watch para limpiar el campo reason_state cuando cambia el estado
  watch(
    () => formState.status,
    (newValue) => {
      if (newValue !== SupplierStatusEnum.INACTIVE && newValue !== SupplierStatusEnum.SUSPENDED) {
        formState.reason_state = '';
      }
    }
  );

  // 🔹 NUEVO: Watch para detectar cambios en supplierId
  watch(
    () => supplierId.value,
    (newId, oldId) => {
      console.log('🆔 [GeneralInformationComposable] SupplierId changed:', { oldId, newId });
      if (oldId !== undefined && newId !== oldId && newId !== undefined) {
        console.log('🔄 [GeneralInformationComposable] Resetting form state for new supplier');
        // Resetear el formulario cuando cambia el ID
        formState.code = '';
        formState.businessName = '';
        formState.chainId = undefined;
        formState.companyName = '';
        formState.rucNumber = '';
        formState.dniNumber = '';
        formState.authorizedManagement = false;
        formState.status = SupplierStatusEnum.ACTIVE;
        formState.reason_state = '';
        isEditMode.value = false;
      }
    }
  );

  const maxLength = computed(() => {
    return 6;
  });

  // Reglas de validación
  const rules: Record<string, Rule[]> = {
    code: [
      {
        required: true,
        message: 'Por favor ingrese el código',
        trigger: 'blur',
      },
      {
        min: maxLength.value,
        max: maxLength.value,
        message: `El código debe tener exactamente ${maxLength.value} caracteres`,
        trigger: 'blur',
      },
      {
        pattern: /^[A-Za-z0-9]*$/,
        message:
          'El código solo debe contener letras y números (sin tildes ni caracteres especiales)',
        trigger: ['blur', 'change'],
      },
    ],
    businessName: [
      {
        required: true,
        message: 'Por favor ingrese el nombre comercial',
        trigger: 'blur',
      },
    ],
    chainId: [
      {
        validator: (_rule: Rule, value: number | null | undefined) => {
          // Si es STAFF, no es requerido
          if (isStaffClassification.value) {
            return Promise.resolve();
          }
          // Para no-STAFF, permitir null (Ninguno) o un número válido
          if (value === null || (typeof value === 'number' && value > 0)) {
            return Promise.resolve();
          }
          // Si es undefined, rechazar
          return Promise.reject('Por favor seleccione la cadena');
        },
        trigger: 'change',
      },
    ],
    companyName: [
      {
        required: true,
        message: 'Por favor ingrese la razón social',
        trigger: 'blur',
      },
    ],
    rucNumber: [
      {
        required: true,
        message: 'Por favor ingrese el número de RUC',
        trigger: 'blur',
      },
      {
        len: 11,
        message: 'El número de RUC debe tener 11 dígitos',
        trigger: 'blur',
      },
    ],
    dniNumber: [
      {
        required: isStaffClassification.value,
        message: 'Por favor ingrese el número de DNI',
        trigger: 'blur',
      },
      {
        len: 8,
        message: 'El número de DNI debe tener 8 dígitos',
        trigger: 'blur',
      },
    ],
    authorizedManagement: [
      {
        required: true,
        message: 'Por favor seleccione una opción',
        trigger: 'change',
      },
    ],
    status: [
      {
        required: true,
        message: 'Por favor seleccione el estado',
        trigger: 'change',
      },
    ],
    reason_state: [
      {
        required: shouldShowReasonField.value,
        message: 'Por favor ingrese el motivo',
        trigger: 'blur',
      },
    ],
  };

  // Manejar modo edición
  const handleEditMode = () => {
    isEditMode.value = true;
    // Actualizar el ítem activo del sidebar
    handleSetActiveItem('supplier', 'supplier-negotiations', 'general_information');
  };

  // Filtrar caracteres no permitidos del código en tiempo real
  const handleCodeInput = () => {
    // Eliminar cualquier caracter que no sea letra (A-Z, a-z) o número (0-9)
    formState.code = formState.code.replace(/[^A-Za-z0-9]/g, '').toUpperCase();
  };

  // Convertir razón social a mayúsculas en tiempo real
  const handleCompanyNameInput = () => {
    formState.companyName = formState.companyName.toUpperCase();
  };

  // Cancelar edición
  const handleCancel = () => {
    isEditMode.value = false;
  };

  // Guardar formulario (sin validación)
  const handleSaveForm = async () => {
    if (!supplierId.value) {
      console.error('No se encontró el ID del proveedor');
      return false;
    }

    try {
      // Activar loading
      isSaving.value = true;

      // Preparar request con la estructura esperada por el backend
      // Enviar ambos formatos para compatibilidad:
      // - ruc_number/dni_number (formato actual del frontend)
      // - type_document_id/document_number (formato esperado por el cálculo de progress del backend)
      const request = {
        code: formState.code,
        business_name: formState.businessName,
        company_name: formState.companyName,
        ruc_number: formState.rucNumber,
        dni_number: formState.dniNumber,
        // Campos adicionales para el cálculo de progress del backend
        type_document_id: isStaffClassification.value ? 2 : 1, // 1=RUC, 2=DNI
        document_number: isStaffClassification.value ? formState.dniNumber : formState.rucNumber,
        authorized_management: formState.authorizedManagement,
        chain_id: formState.chainId ?? null,
        status: formState.status,
        reason_state: formState.reason_state,
      };

      // Llamar al servicio de backend
      const response = await updateGeneralInformation(supplierId.value, request);

      if (response?.success) {
        handleCompleteResponse(response);

        // Marcar item como completo en el sidebar ANTES de recargar
        markItemComplete('general_information');

        // Recargar módulos del supplier (preservará el estado de isComplete gracias al previousState)
        await loadSupplierModules();

        // Cambiar a modo lectura
        isEditMode.value = false;
        return true;
      } else {
        console.error('Error al guardar la información general');
        return false;
      }
    } catch (error) {
      handleError(error as Error);
      return false;
    } finally {
      isSaving.value = false;
    }
  };

  // Guardar datos (con validación)
  const handleSave = async () => {
    try {
      await formRef.value?.validate();
      await handleSaveForm();
    } catch (error) {
      // Validation error
      console.error('Validation failed:', error);
    }
  };

  // Computed: validar si el formulario es válido (todos los campos requeridos completos)
  const isFormValid = computed(() => {
    // Campos siempre requeridos
    const hasCode = !!formState.code && formState.code.length === maxLength.value;
    const hasBusinessName = !!formState.businessName?.trim();
    const hasCompanyName = !!formState.companyName?.trim();
    const hasRucNumber = !!formState.rucNumber && formState.rucNumber.length === 11;
    const hasAuthorizedManagement = formState.authorizedManagement !== undefined;
    const hasStatus = !!formState.status;

    // Chain solo es requerido si NO es STAFF (pero null/"Ninguno" es un valor válido)
    const hasChain = isStaffClassification.value ? true : formState.chainId !== undefined; // undefined es inválido, pero null es válido

    // DNI solo es requerido si es STAFF
    const hasDni = isStaffClassification.value
      ? !!formState.dniNumber && formState.dniNumber.length === 8
      : true;

    // Motivo solo es requerido si el estado es INACTIVE o SUSPENDED
    const hasReason = shouldShowReasonField.value ? !!formState.reason_state?.trim() : true;

    return (
      hasCode &&
      hasBusinessName &&
      hasChain &&
      hasCompanyName &&
      hasRucNumber &&
      hasDni &&
      hasAuthorizedManagement &&
      hasStatus &&
      hasReason
    );
  });

  return {
    // Estado
    formState,
    formRef,
    isEditMode,
    isLoading,
    isLoadingFields, // Para deshabilitar campos durante carga inicial
    isStaffClassification,

    // Datos computados
    chains,
    hasNoChains,
    shouldShowReasonField,
    supplierStatusOptions,
    readData,
    isFormValid,

    // Reglas de validación
    rules,

    // Métodos
    handleEditMode,
    handleCancel,
    handleCodeInput,
    handleCompanyNameInput,
    handleSave,
  };
}
