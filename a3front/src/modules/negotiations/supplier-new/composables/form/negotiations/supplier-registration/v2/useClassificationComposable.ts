import { computed, reactive, ref, watch, nextTick } from 'vue';
import type { Rule, FormInstance } from 'ant-design-vue/es/form';
import { storeToRefs } from 'pinia';
import { useQuery } from '@tanstack/vue-query';
import {
  useSupplierClassificationCatalogQuery,
  getSupplierEditQueryKey,
  SUPPLIER_EDIT_KEYS,
} from '@/modules/negotiations/supplier-new/composables/form/negotiations/optimized-queries/supplier-complete.query';
import { useSupplierService } from '@/modules/negotiations/supplier-new/service/supplier.service';
import { useSupplierClassificationStore } from '@/modules/negotiations/supplier-new/store/negotiations/supplier-registration/supplier-classification.store';
import { useSupplierGlobalComposable } from '@/modules/negotiations/supplier-new/composables/form/supplier-global.composable';
import { useSupplierModulesComposable } from '@/modules/negotiations/supplier-new/composables/supplier-modules.composable';
import { handleCompleteResponse, handleError } from '@/modules/negotiations/api/responseApi';

export enum ScenarioType {
  /** Categoría → Type sin Subtypes (Trenes, Lanchas) — solo muestra "Tipo de proveedor" */
  ONE_LEVEL = 'ONE_LEVEL',
  /** Categoría → Types → Subtypes (Staff, Restaurante) — muestra "Tipo" y "Subtipo" */
  TWO_LEVELS = 'TWO_LEVELS',
  /** Categoría → 1 Type → Subtypes → Subtypes (Transporte) — remapea Subtypes como Types visualmente */
  THREE_LEVELS_REMAPPED = 'THREE_LEVELS_REMAPPED',
}

export interface ClassificationFormState {
  /** typeCode del tipo seleccionado (ej: 'TRP', 'STA') — estable entre entornos */
  supplier_classification_typeCode: string | undefined;
  /** subtypeCode del subtipo seleccionado (ej: 'AER', 'ACU') */
  supplier_sub_classification_subtypeCode: string | undefined;
  /** subtypeCode del sub-subtipo (ej: 'DOM', 'INT', 'RUT') — solo cuando el subtipo tiene hijos */
  supplier_sub_sub_classification_subtypeCode: string | undefined;
}

export interface ClassificationReadData {
  supplierClassification: { typeCode: string; name: string } | null;
  supplierSubClassification: { subtypeCode: string; name: string } | null;
}

export function useClassificationComposable() {
  const {
    supplierId,
    markItemComplete,
    isEditMode: isGlobalEditMode,
    handleSetActiveItem,
  } = useSupplierGlobalComposable();
  const { loadSupplierModules } = useSupplierModulesComposable();
  const { updateOrCreateSupplierClassification } = useSupplierService;

  const supplierClassificationStore = useSupplierClassificationStore();
  const {
    supplierClassificationId: storeClassificationId,
    supplierSubClassificationId: storeSubClassificationId,
    supplierClassificationName: storeClassificationName,
    supplierSubClassificationName: storeSubClassificationName,
    supplierSubClassificationHasSubSubs: storeHasSubSubs,
    isEditMode,
    justSaved,
  } = storeToRefs(supplierClassificationStore);

  const componentKey = ref(0);
  const hasInitializedFromSupplier = ref(false);
  const isSaving = ref(false);
  const formRef = ref<FormInstance>();

  if (!isEditMode.value && !isGlobalEditMode.value) {
    if (!storeClassificationId.value) {
      isEditMode.value = true;
    }
  }

  const formState = reactive<ClassificationFormState>({
    supplier_classification_typeCode: undefined,
    supplier_sub_classification_subtypeCode: undefined,
    supplier_sub_sub_classification_subtypeCode: undefined,
  });

  // ─── Queries ──────────────────────────────────────────────────────────────

  // Catálogo de clasificaciones — fuente única para todos los modos
  const { data: catalogData, isLoading: isLoadingCatalog } =
    useSupplierClassificationCatalogQuery();

  // Datos del supplier en modo edición (para leer la clasificación guardada)
  const { data: supplierData, isLoading: isLoadingSupplierData } = useQuery({
    queryKey: computed(() => {
      const key = supplierId.value
        ? getSupplierEditQueryKey(supplierId.value)
        : (['supplier', 'edit-complete', '__disabled__'] as const);

      // console.log('key para traer los datos del supplier', key);

      return key;
    }),
    queryFn: async () => {
      const response = await useSupplierService.showSupplierCompleteData(supplierId.value!, {
        keys: [...SUPPLIER_EDIT_KEYS],
      });

      return response;
    },
    enabled: computed(() => !!supplierId.value && isGlobalEditMode.value),
    staleTime: 0,
    refetchOnMount: true,
    refetchOnWindowFocus: false,
  });

  // ─── Datos normalizados ───────────────────────────────────────────────────

  const classificationsData = computed(() => {
    if (!catalogData.value)
      return { classifications: [], subClassifications: [], subSubClassifications: [] };
    return catalogData.value;
  });

  // ─── Inicialización en modo edición ──────────────────────────────────────

  watch(
    [() => supplierData.value, () => classificationsData.value],
    async ([data, classData]) => {
      if (justSaved.value) return;
      if (hasInitializedFromSupplier.value) return;

      if (data && isGlobalEditMode.value && classData.classifications.length > 0) {
        const supplierInfo = data?.data?.data?.supplier_info;

        if (supplierInfo?.supplier_sub_classification) {
          hasInitializedFromSupplier.value = true;
          await nextTick();

          const subClassification = supplierInfo.supplier_sub_classification;
          const classification = subClassification.supplier_classification;

          // Usar typeCode/subtypeCode si el backend los devuelve Y son válidos en el catálogo.
          // Fallback: buscar por nombre (case-insensitive) si el valor del backend no existe
          // en el catálogo (evita que un código incorrecto del backend se use directamente).
          const classificationNameLower = classification.name?.toLowerCase();
          const subClassificationNameLower = subClassification.name?.toLowerCase();

          const typeCodeFromBackend = classification.typeCode;
          const typeCodeByName = classData.classifications.find(
            (c) => c.name.toLowerCase() === classificationNameLower
          )?.typeCode;
          let typeCode =
            typeCodeFromBackend &&
            classData.classifications.some((c) => c.typeCode === typeCodeFromBackend)
              ? typeCodeFromBackend
              : typeCodeByName;

          const subtypeCodeFromBackend = subClassification.subtypeCode;

          // Primero buscar en subClassifications (segundo nivel)
          let subtypeCode =
            subtypeCodeFromBackend &&
            classData.subClassifications.some((s) => s.subtypeCode === subtypeCodeFromBackend)
              ? subtypeCodeFromBackend
              : undefined;

          // Si no se encuentra, buscar en subSubClassifications (tercer nivel)
          if (!subtypeCode && subtypeCodeFromBackend) {
            subtypeCode = classData.subSubClassifications?.some(
              (s) => s.subtypeCode === subtypeCodeFromBackend
            )
              ? subtypeCodeFromBackend
              : undefined;
          }

          // Si aún no se encuentra, buscar por nombre
          if (!subtypeCode) {
            subtypeCode = classData.subClassifications.find(
              (s) => s.name.toLowerCase() === subClassificationNameLower
            )?.subtypeCode;
          }

          // Si aún no se encuentra, buscar por nombre en subSubClassifications
          if (!subtypeCode) {
            subtypeCode = classData.subSubClassifications?.find(
              (s) => s.name.toLowerCase() === subClassificationNameLower
            )?.subtypeCode;
          }

          // Si aún no tenemos typeCode, derivarlo del parentTypeCode del subtipo en el catálogo
          if (!typeCode && subtypeCode) {
            typeCode = classData.subClassifications.find(
              (s) => s.subtypeCode === subtypeCode
            )?.parentTypeCode;
          }

          formState.supplier_classification_typeCode = typeCode;

          // Si subtypeCode es un tercer nivel (está en subSubClassifications),
          // necesitamos encontrar su padre (segundo nivel) y asignarlo correctamente
          const isThirdLevelCode = (classData.subSubClassifications ?? []).find(
            (s) => s.subtypeCode === subtypeCode
          );

          if (isThirdLevelCode) {
            // Es tercer nivel: asignar el padre al segundo nivel y el código al tercer nivel
            formState.supplier_sub_classification_subtypeCode = isThirdLevelCode.parentSubtypeCode;
            formState.supplier_sub_sub_classification_subtypeCode = subtypeCode;
          } else {
            // Es segundo nivel: asignar directamente
            formState.supplier_sub_classification_subtypeCode = subtypeCode;
            formState.supplier_sub_sub_classification_subtypeCode = undefined;
          }

          // Para 3 niveles (isThirdLevelCode), el store debe guardar el 2do nivel (AER),
          // NO el top-level (TRP), para ser consistente con lo que guarda handleSaveForm.
          // Si se guarda TRP, el watch(isEditMode) lo busca en subClassifications y encuentra
          // "Transporte terrestre" (que tiene subtypeCode='TRP'), corrompiendo el formulario.
          const classificationIdToStore = isThirdLevelCode
            ? isThirdLevelCode.parentSubtypeCode // 'AER'
            : (typeCode ?? null); // 'TRP', 'STA', etc.
          supplierClassificationStore.setSupplierClassificationId(classificationIdToStore);
          supplierClassificationStore.setSupplierSubClassificationId(subtypeCode ?? null);

          // Determinar si tiene sub-subtipos:
          // - Si subtypeCode está en subSubClassifications (tercer nivel), entonces SÍ tiene estructura de 3 niveles
          // - Si subtypeCode está en subClassifications (segundo nivel), verificar si tiene hijos en subSubClassifications
          const isThirdLevel = (classData.subSubClassifications ?? []).some(
            (s) => s.subtypeCode === subtypeCode
          );

          const hasSubSubs = isThirdLevel
            ? true // Es tercer nivel, mostrar como "Tipo: Aerolíneas, Subtipo: Doméstico"
            : (classData.subSubClassifications ?? []).some(
                (s) => s.parentSubtypeCode === subtypeCode
              ); // Es segundo nivel, verificar si tiene hijos

          // Guardar nombres según la estructura:
          // - Si es 3 niveles: guardar 2do nivel como "classification" y 3er nivel como "subClassification"
          // - Si es 2 niveles: guardar 1er nivel como "classification" y 2do nivel como "subClassification"
          let classificationNameToStore = '';
          let subClassificationNameToStore = '';

          if (isThirdLevelCode) {
            // Es 3 niveles: guardar nombres del 2do y 3er nivel
            const secondLevelName = classData.subClassifications.find(
              (s) => s.subtypeCode === isThirdLevelCode.parentSubtypeCode
            )?.name;
            classificationNameToStore = secondLevelName || classification.name;
            subClassificationNameToStore = subClassification.name;
          } else {
            // Es 2 niveles: guardar nombres del 1er y 2do nivel (del backend)
            classificationNameToStore = classification.name;
            subClassificationNameToStore = subClassification.name;
          }

          supplierClassificationStore.setSupplierClassificationName(classificationNameToStore);
          supplierClassificationStore.setSupplierSubClassificationName(
            subClassificationNameToStore
          );
          supplierClassificationStore.setSupplierSubClassificationHasSubSubs(hasSubSubs);

          isEditMode.value = false;
        }
      }
    },
    { immediate: true }
  );

  // Sincronizar con el store cuando venga preseleccionado (desde listado → nuevo supplier).
  // Observa AMBOS: el valor del store Y las clasificaciones cargadas.
  // Así funciona aunque el mock/API resuelva después del montaje del componente.
  watch(
    [storeClassificationId, () => classificationsData.value],
    ([newValue, catalog]) => {
      if (!isEditMode.value || !newValue || formState.supplier_classification_typeCode) return;

      const { classifications, subClassifications } = catalog;

      // Caso 1: el valor es un subtypeCode (AER, ACU, TRN, TRP, LOD, CRC...).
      // Se verifica primero porque 'TRP' existe tanto como clasificación padre
      // como subtipo (Transporte terrestre), y en ese caso queremos el subtipo.
      const matchedSub = subClassifications.find((s) => s.subtypeCode === newValue);
      if (matchedSub) {
        formState.supplier_classification_typeCode = matchedSub.parentTypeCode;
        // nextTick para que el watch que limpia el subtipo al cambiar el tipo no lo borre
        nextTick(() => {
          formState.supplier_sub_classification_subtypeCode = newValue;
        });
        return;
      }

      // Caso 2: el valor es directamente un typeCode de clasificación (ATT, RES, STA...).
      // Fallback cuando no hay match como subtipo.
      const isValidTypeCode = classifications.some((c) => c.typeCode === newValue);
      if (isValidTypeCode) {
        formState.supplier_classification_typeCode = newValue;
      }
    },
    { immediate: true }
  );

  watch(isEditMode, async (newValue) => {
    if (newValue === true) {
      const classifications = classificationsData.value.classifications;
      const subClassifications = classificationsData.value.subClassifications;
      const subSubClassifications = classificationsData.value.subSubClassifications ?? [];

      // Intentar restaurar desde storeClassificationId (typeCode validado contra catálogo)
      const candidateTypeCode = storeClassificationId.value;
      // Leer candidateSubtypeCode antes de cualquier nextTick para evitar cambios de estado
      const candidateSubtypeCode = supplierClassificationStore.supplierSubClassificationId;

      // Si storeHasSubSubs es true, el candidateTypeCode es en realidad un subtypeCode (2do nivel)
      // y debemos buscar su parentTypeCode para asignarlo al formState
      if (storeHasSubSubs.value && candidateTypeCode) {
        const secondLevel = subClassifications.find((s) => s.subtypeCode === candidateTypeCode);
        if (secondLevel) {
          formState.supplier_classification_typeCode = secondLevel.parentTypeCode;
          // Esperar a que el watch de tipo limpie el subtipo antes de asignarlo
          await nextTick();
          formState.supplier_sub_classification_subtypeCode = candidateTypeCode;
          // Esperar a que el watch de subtipo limpie el sub-subtipo antes de asignarlo
          await nextTick();
          if (candidateSubtypeCode) {
            const thirdLevel = subSubClassifications.find(
              (s) => s.subtypeCode === candidateSubtypeCode
            );
            if (thirdLevel) {
              formState.supplier_sub_sub_classification_subtypeCode = candidateSubtypeCode;
            }
          }
        }
      } else {
        // Caso de 2 niveles: candidateTypeCode es un typeCode de clasificación
        const isValidTypeCode =
          !!candidateTypeCode && classifications.some((c) => c.typeCode === candidateTypeCode);

        if (isValidTypeCode) {
          formState.supplier_classification_typeCode = candidateTypeCode!;
        } else if (storeClassificationName.value) {
          // Fallback: buscar typeCode por nombre si el valor del store no es válido
          const nameLower = storeClassificationName.value.toLowerCase();
          const found = classifications.find((c) => c.name.toLowerCase() === nameLower);
          if (found) {
            formState.supplier_classification_typeCode = found.typeCode;
            supplierClassificationStore.setSupplierClassificationId(found.typeCode);
          }
        }

        // Esperar a que el watch de tipo limpie el subtipo antes de asignarlo
        await nextTick();

        // Restaurar subtypeCode validado
        const isValidSubtypeCode =
          !!candidateSubtypeCode &&
          subClassifications.some((s) => s.subtypeCode === candidateSubtypeCode);

        if (isValidSubtypeCode) {
          formState.supplier_sub_classification_subtypeCode = candidateSubtypeCode!;
        } else if (storeSubClassificationName.value) {
          // Fallback: buscar subtypeCode por nombre
          const subNameLower = storeSubClassificationName.value.toLowerCase();
          const foundSub = subClassifications.find((s) => s.name.toLowerCase() === subNameLower);
          if (foundSub) {
            formState.supplier_sub_classification_subtypeCode = foundSub.subtypeCode;
            supplierClassificationStore.setSupplierSubClassificationId(foundSub.subtypeCode);
          }
        }
      }
    }
  });

  // ─── Computed ─────────────────────────────────────────────────────────────

  const isClassificationDisabled = computed(
    () =>
      isLoadingCatalog.value ||
      isLoadingSupplierData.value ||
      (isEditMode.value && !!storeClassificationId.value)
  );

  // Deshabilitar el subtipo cuando fue pre-seleccionado por navegación desde el listado.
  // Esto ocurre cuando storeClassificationId contiene un subtypeCode (AER, ACU, TRP, TRN...)
  // en lugar de un typeCode de clasificación padre. En ese caso el usuario no debe cambiarlo.
  // También deshabilitar en modo edición cuando hay 3 niveles (el 2do nivel no debe cambiar).
  const isSubClassificationDisabled = computed(() => {
    if (isLoadingCatalog.value || isLoadingSupplierData.value) return true;
    if (!formState.supplier_classification_typeCode) return true;

    // En modo edición global con 3 niveles, deshabilitar el 2do nivel
    if (isGlobalEditMode.value && requiresSubSubClassification.value) return true;

    if (!storeClassificationId.value) return false;
    // Está pre-seleccionado si el valor del store es un subtypeCode en el catálogo
    return classificationsData.value.subClassifications.some(
      (s) => s.subtypeCode === storeClassificationId.value
    );
  });

  const supplierClassifications = computed(() => {
    return classificationsData.value.classifications;
  });

  const isLoading = computed(
    () => isSaving.value || isLoadingCatalog.value || isLoadingSupplierData.value
  );

  const isDataReady = computed(() => {
    if (isGlobalEditMode.value) {
      // En modo edición, esperar a que:
      // 1. El catálogo esté cargado
      // 2. Los datos del supplier estén cargados
      // 3. Las clasificaciones estén disponibles
      // 4. Se haya inicializado desde el supplier
      // 5. Los valores del formulario estén asignados
      return (
        !isLoadingCatalog.value &&
        !isLoadingSupplierData.value &&
        classificationsData.value.classifications.length > 0 &&
        hasInitializedFromSupplier.value &&
        !!formState.supplier_classification_typeCode &&
        (!!formState.supplier_sub_classification_subtypeCode ||
          !!formState.supplier_sub_sub_classification_subtypeCode)
      );
    }

    // En modo creación, solo esperar el catálogo
    return !isLoadingCatalog.value && classificationsData.value.classifications.length > 0;
  });

  /** Subtipos filtrados por parentTypeCode del tipo seleccionado */
  const supplierSubClassifications = computed(() => {
    if (!formState.supplier_classification_typeCode) return [];
    return classificationsData.value.subClassifications.filter(
      (s) => s.parentTypeCode === formState.supplier_classification_typeCode
    );
  });

  /** Sub-subtipos filtrados por parentSubtypeCode del subtipo seleccionado */
  const supplierSubSubClassifications = computed(() => {
    if (!formState.supplier_sub_classification_subtypeCode) return [];
    return (classificationsData.value.subSubClassifications ?? []).filter(
      (s) => s.parentSubtypeCode === formState.supplier_sub_classification_subtypeCode
    );
  });

  /** True cuando el subtipo seleccionado requiere elegir un sub-subtipo */
  const requiresSubSubClassification = computed(
    () => supplierSubSubClassifications.value.length > 0
  );

  /** True cuando el store tiene un subtypeCode pre-seleccionado (navegación desde listado) */
  const isPreSelectedSubtype = computed(() => {
    // Solo aplica cuando NO estamos editando un proveedor existente
    if (isGlobalEditMode.value) return false;
    if (!storeClassificationId.value) return false;
    return classificationsData.value.subClassifications.some(
      (s) => s.subtypeCode === storeClassificationId.value
    );
  });

  /** Nombre del subtipo pre-seleccionado para mostrarlo como "Tipo de proveedor" en el layout especial */
  const preSelectedSubtypeName = computed(() => {
    if (!isPreSelectedSubtype.value) return '';
    return (
      classificationsData.value.subClassifications.find(
        (s) => s.subtypeCode === storeClassificationId.value
      )?.name ?? ''
    );
  });

  /** Layout especial: viene del listado con subtipo pre-seleccionado que tiene sub-subtipos */
  const isPreSelectedWithSubSubTypes = computed(
    () => isPreSelectedSubtype.value && requiresSubSubClassification.value
  );

  /**
   * Escenario activo según la estructura de datos del tipo/subtipo seleccionado.
   * Centraliza la lógica de detección — sustituye los múltiples flags booleanos en el template.
   */
  const scenario = computed<ScenarioType>(() => {
    // THREE_LEVELS_REMAPPED: el subtipo seleccionado tiene sub-subtipos (AER → DOM/INT)
    // o viene pre-seleccionado desde el listado con sub-subtipos disponibles
    if (requiresSubSubClassification.value || isPreSelectedWithSubSubTypes.value) {
      return ScenarioType.THREE_LEVELS_REMAPPED;
    }

    // ONE_LEVEL: el subtipo seleccionado es un "direct leaf" sin sub-subtipos (TRN, LNC)
    const selectedSub = formState.supplier_sub_classification_subtypeCode;
    if (selectedSub) {
      const subInCatalog = classificationsData.value.subClassifications.find(
        (s) => s.subtypeCode === selectedSub
      );
      if (subInCatalog?.isDirectSub) return ScenarioType.ONE_LEVEL;
    }

    // ONE_LEVEL pre-seleccionado desde el listado (antes de que el watch sincronice formState)
    if (isPreSelectedSubtype.value) return ScenarioType.ONE_LEVEL;

    // TWO_LEVELS: caso por defecto (STA, RES, ATT, etc.)
    return ScenarioType.TWO_LEVELS;
  });

  /**
   * Modo lectura de una sola línea: el subtipo guardado es hoja directa (TRN, ACU)
   * sin sub-subtipos en el catálogo → mostrar solo "Tipo de proveedor: Trenes"
   */
  /**
   * Modo lectura de una sola línea: el subtipo guardado es un "direct sub" (Strategy B)
   * sin sub-subtipos disponibles → mostrar solo "Tipo de proveedor: Trenes / Lanchas".
   * Excluye los subtipos de Strategy A (Museo, Restaurante, etc.) que siempre muestran 2 líneas.
   */
  const isSingleLineReadMode = computed(() => {
    // Usar el flag del store (seteado al guardar o al cargar desde el backend)
    if (storeHasSubSubs.value !== null) {
      return !storeHasSubSubs.value;
    }
    // Fallback: calcular desde el catálogo si el flag aún no está seteado
    const subs = classificationsData.value.subClassifications;
    if (!subs.length) return false;
    const subId = storeSubClassificationId.value;
    const subName = storeSubClassificationName.value?.toLowerCase();
    const subInCatalog =
      (subId && subs.find((s) => s.subtypeCode === subId)) ||
      (subName && subs.find((s) => s.name.toLowerCase() === subName)) ||
      undefined;
    if (!subInCatalog) return false;
    const hasSubSubs = (classificationsData.value.subSubClassifications ?? []).some(
      (s) => s.parentSubtypeCode === subInCatalog.subtypeCode
    );
    return !hasSubSubs;
  });

  const readData = computed<ClassificationReadData>(() => {
    // Cuando hay valores guardados en el store, usarlos directamente
    // porque ya están en el formato correcto (2do y 3er nivel para casos de 3 niveles)
    if (storeClassificationName.value && storeSubClassificationName.value) {
      return {
        supplierClassification: {
          typeCode:
            formState.supplier_sub_classification_subtypeCode ||
            formState.supplier_classification_typeCode!,
          name: storeClassificationName.value,
        },
        supplierSubClassification: {
          subtypeCode:
            formState.supplier_sub_sub_classification_subtypeCode ||
            formState.supplier_sub_classification_subtypeCode!,
          name: storeSubClassificationName.value,
        },
      };
    }

    // Fallback: buscar en el catálogo cuando no hay valores en el store
    const classification = supplierClassifications.value.find(
      (c) => c.typeCode === formState.supplier_classification_typeCode
    );
    const subClassification = classificationsData.value.subClassifications.find(
      (s) => s.subtypeCode === formState.supplier_sub_classification_subtypeCode
    );

    return {
      supplierClassification: classification ?? null,
      supplierSubClassification: subClassification ?? null,
    };
  });

  // Limpiar subtipo y sub-subtipo al cambiar el tipo
  watch(
    () => formState.supplier_classification_typeCode,
    (newValue, oldValue) => {
      // console.log('aqui el supplier_classification_type_code', {
      //   newValue,
      //   oldValue,
      // });
      if (newValue !== oldValue) {
        formState.supplier_sub_classification_subtypeCode = undefined;
        formState.supplier_sub_sub_classification_subtypeCode = undefined;
      }
    }
  );

  // Limpiar sub-subtipo al cambiar el subtipo
  watch(
    () => formState.supplier_sub_classification_subtypeCode,
    (newValue, oldValue) => {
      if (newValue !== oldValue) {
        formState.supplier_sub_sub_classification_subtypeCode = undefined;
      }
    }
  );

  // ─── Validación ───────────────────────────────────────────────────────────

  const rules: Record<string, Rule[]> = {
    supplier_classification_typeCode: [
      {
        required: true,
        message: 'Por favor seleccione un tipo de proveedor',
        trigger: 'change',
      },
    ],
    supplier_sub_classification_subtypeCode: [
      {
        required: true,
        message: 'Por favor seleccione un subtipo de proveedor',
        trigger: 'change',
      },
    ],
  };

  const isFormValid = computed(() => {
    if (!formState.supplier_classification_typeCode) return false;
    if (!formState.supplier_sub_classification_subtypeCode) return false;
    if (
      requiresSubSubClassification.value &&
      !formState.supplier_sub_sub_classification_subtypeCode
    )
      return false;
    return true;
  });

  // ─── Handlers ─────────────────────────────────────────────────────────────

  const handleEditMode = () => {
    isEditMode.value = true;
    handleSetActiveItem('supplier', 'supplier-negotiations', 'classification');
  };

  const handleCancel = () => {
    isEditMode.value = false;
  };

  const handleSaveForm = async () => {
    try {
      if (
        !formState.supplier_classification_typeCode ||
        !formState.supplier_sub_classification_subtypeCode
      ) {
        console.error('Por favor complete todos los campos');
        return false;
      }

      isSaving.value = true;

      // Enviar el código más profundo disponible
      const codeToSend =
        formState.supplier_sub_sub_classification_subtypeCode ??
        formState.supplier_sub_classification_subtypeCode;

      const response = await updateOrCreateSupplierClassification({
        supplier_id: supplierId.value,
        supplier_sub_classification_code: codeToSend,
      });

      if (response && response.success) {
        handleCompleteResponse(response);
        const { data } = response;

        if (data.id) {
          supplierId.value = data.id;
        }

        const subClass = data.supplier_sub_classification;
        // Usar typeCode/subtypeCode del backend si están disponibles; si no, usar el formState
        const typeCodeFromBackend =
          subClass?.supplier_classification?.typeCode ?? formState.supplier_classification_typeCode;
        const subtypeCodeFromBackend =
          subClass?.subtypeCode ?? formState.supplier_sub_classification_subtypeCode;

        // Cuando hay 3 niveles, guardar códigos del 2do y 3er nivel en el store
        // Cuando hay 2 niveles, guardar códigos del 1er y 2do nivel
        let typeCodeToStore = typeCodeFromBackend;
        let subtypeCodeToStore = subtypeCodeFromBackend;
        let classificationName = '';
        let subClassificationName = '';

        if (requiresSubSubClassification.value) {
          // Caso de 3 niveles: guardar 2do nivel como "classificationId" y 3er nivel como "subClassificationId"
          typeCodeToStore = formState.supplier_sub_classification_subtypeCode!;
          subtypeCodeToStore = formState.supplier_sub_sub_classification_subtypeCode!;

          const secondLevel = classificationsData.value.subClassifications.find(
            (s) => s.subtypeCode === formState.supplier_sub_classification_subtypeCode
          );
          const thirdLevel = classificationsData.value.subSubClassifications?.find(
            (s) => s.subtypeCode === formState.supplier_sub_sub_classification_subtypeCode
          );

          classificationName = secondLevel?.name ?? '';
          subClassificationName = thirdLevel?.name ?? '';
        } else {
          // Caso de 2 niveles: usar 1er nivel como "classification" y 2do nivel como "subClassification"
          classificationName =
            subClass?.supplier_classification?.name ??
            classificationsData.value.classifications.find(
              (c) => c.typeCode === typeCodeFromBackend
            )?.name ??
            '';
          subClassificationName =
            subClass?.name ??
            classificationsData.value.subClassifications.find(
              (s) => s.subtypeCode === subtypeCodeFromBackend
            )?.name ??
            '';
        }

        supplierClassificationStore.setSupplierClassificationId(typeCodeToStore ?? null);
        supplierClassificationStore.setSupplierSubClassificationId(subtypeCodeToStore ?? null);
        supplierClassificationStore.setSupplierClassificationName(classificationName);
        supplierClassificationStore.setSupplierSubClassificationName(subClassificationName);
        supplierClassificationStore.setSupplierSubClassificationHasSubSubs(
          requiresSubSubClassification.value
        );

        justSaved.value = true;

        // 1ro: pasar a read mode → clasificación queda en lectura
        await nextTick();
        isEditMode.value = false;

        // 2do: marcar completo → la siguiente sección aparece DESPUÉS de que
        //      clasificación ya esté en read mode (flujo progresivo en registro)
        await nextTick();
        markItemComplete('classification');

        // Sincronizar módulos con el backend en background (no bloqueante)
        loadSupplierModules();

        setTimeout(() => {
          justSaved.value = false;
        }, 500);

        return true;
      } else {
        console.error(response?.message || 'Error al guardar la clasificación del proveedor');
        return false;
      }
    } catch (error) {
      handleError(error as Error);
      return false;
    } finally {
      isSaving.value = false;
    }
  };

  const handleSave = async () => {
    try {
      await formRef.value?.validate();
      await handleSaveForm();
    } catch (error) {
      console.error('Validation failed:', error);
    }
  };

  return {
    formState,
    formRef,
    isEditMode,
    isLoading,
    isSaving,
    isDataReady,
    isClassificationDisabled,
    componentKey,
    justSaved,

    // Escenario activo — fuente única de verdad para el template
    ScenarioType,
    scenario,

    supplierClassifications,
    supplierSubClassifications,
    supplierSubSubClassifications,
    preSelectedSubtypeName,
    isSingleLineReadMode,
    readData,
    isFormValid,
    rules,
    isSubClassificationDisabled,

    handleEditMode,
    handleCancel,
    handleSave,
  };
}
