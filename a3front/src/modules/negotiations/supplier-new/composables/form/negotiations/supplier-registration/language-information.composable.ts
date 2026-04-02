import { computed, reactive, ref, watch } from 'vue';
import { FormComponentEnum } from '@/modules/negotiations/supplier-new/enums/form-component.enum';
import { useSupplierService } from '@/modules/negotiations/supplier-new/service/supplier.service';
import { useSupplierGlobalComposable } from '../../supplier-global.composable';
import { handleCompleteResponse, handleError } from '@/modules/negotiations/api/responseApi';
import { useSupplierModulesComposable } from '@/modules/negotiations/supplier-new/composables/supplier-modules.composable';
import { useSupplierClassificationStoreFacade } from './supplier-classification-store-facade.composable';

export type LanguageForm = {
  id?: number | null;
  language: string | null;
  level: string | null;
};

export function useLanguageInformationComposable() {
  const {
    supplierId,
    isEditMode,
    getShowFormComponent,
    getIsEditFormComponent,
    handleShowFormSpecific,
    handleIsEditFormSpecific,
    handleSavedFormSpecific,
    markItemComplete,
  } = useSupplierGlobalComposable();

  const { supplierClassificationId } = useSupplierClassificationStoreFacade();

  const { showLanguages, showSupplierLanguages, updateOrCreateSupplierLanguage } =
    useSupplierService;

  const { loadSupplierModules } = useSupplierModulesComposable();

  // Solo cargar idiomas cuando la clasificación es Staff (Guiado y asistencia)
  const shouldLoadLanguages = computed(() => {
    return supplierClassificationId.value === 'STA';
  });

  const isLoading = ref(false);
  const isLoadingButton = ref(false);
  const resourcesLoaded = ref(false);
  const showMultipleLanguages = ref(false);

  const initFormState: LanguageForm = {
    id: null,
    language: null,
    level: null,
  };

  const formState = reactive<LanguageForm>({ ...initFormState });
  const originalFormState = reactive<LanguageForm>({ ...initFormState }); // snapshot para cancelar

  const multipleLanguages = ref<LanguageForm[]>([]);
  const originalMultipleLanguages = ref<LanguageForm[]>([]);

  const languages = ref<{ id: string; name: string }[]>([]);
  const levelOptions = [
    { value: 'BASIC', label: 'Básico' },
    { value: 'INTERMEDIATE', label: 'Intermedio' },
    { value: 'ADVANCED', label: 'Avanzado' },
  ];

  const spinTip = computed(() => (isLoadingButton.value ? 'Guardando...' : 'Cargando...'));
  const spinning = computed(() => isLoading.value || isLoadingButton.value);

  // ✅ Validación mejorada que incluye idiomas únicos
  const getIsFormValid = computed(() => {
    if (showMultipleLanguages.value) {
      // Validar que todos los campos estén completos
      const allFieldsFilled = multipleLanguages.value.every((lang) =>
        Boolean(lang.language && lang.level)
      );

      // Validar que no haya idiomas repetidos
      const languageIds = multipleLanguages.value
        .map((lang) => lang.language)
        .filter((id) => id !== null);
      const uniqueLanguageIds = new Set(languageIds);
      const noRepeatedLanguages = languageIds.length === uniqueLanguageIds.size;

      return allFieldsFilled && noRepeatedLanguages;
    }
    return Boolean(formState.language && formState.level);
  });

  // ✅ Helper para detectar idiomas repetidos
  const getRepeatedLanguages = computed(() => {
    if (!showMultipleLanguages.value) return new Set();

    const languageCounts = new Map();
    multipleLanguages.value.forEach((lang, index) => {
      if (lang.language) {
        if (!languageCounts.has(lang.language)) {
          languageCounts.set(lang.language, []);
        }
        languageCounts.get(lang.language).push(index);
      }
    });

    const repeated = new Set();
    languageCounts.forEach((indices) => {
      if (indices.length > 1) {
        indices.forEach((index: number) => repeated.add(index));
      }
    });

    return repeated;
  });

  const currentLanguage = computed({
    get: () =>
      showMultipleLanguages.value && multipleLanguages.value.length === 1
        ? multipleLanguages.value[0]?.language
        : formState.language,
    set: (value) => {
      if (showMultipleLanguages.value && multipleLanguages.value.length === 1) {
        multipleLanguages.value[0].language = value;
      } else {
        formState.language = value;
      }
    },
  });

  const currentLevel = computed({
    get: () =>
      showMultipleLanguages.value && multipleLanguages.value.length === 1
        ? multipleLanguages.value[0]?.level
        : formState.level,
    set: (value) => {
      if (showMultipleLanguages.value && multipleLanguages.value.length === 1) {
        multipleLanguages.value[0].level = value;
      } else {
        formState.level = value;
      }
    },
  });

  const getLanguageName = (id: string | null) =>
    languages.value.find((l) => l.id === id)?.name || 'Sin idioma';

  const getLevelLabel = (value: string | null) =>
    value ? levelOptions.find((opt) => opt.value === value)?.label || value : 'Sin nivel';

  // ✅ Helper para validar si un idioma ya está seleccionado
  const isLanguageSelected = (languageId: string, currentIndex?: number) => {
    if (showMultipleLanguages.value) {
      return multipleLanguages.value.some(
        (lang, index) => lang.language === languageId && index !== currentIndex
      );
    }
    return false;
  };

  // ✅ True cuando aún hay idiomas disponibles para agregar
  const hasAvailableLanguages = computed(() => {
    const usedCount = showMultipleLanguages.value
      ? multipleLanguages.value.length
      : formState.language
        ? 1
        : 0;
    return usedCount < languages.value.length;
  });

  const resetFormState = () => Object.assign(formState, { ...initFormState });

  const resetMultipleLanguages = () => {
    multipleLanguages.value = [];
    showMultipleLanguages.value = false;
  };

  const createEmptyLanguage = (): LanguageForm => ({
    id: null,
    language: null,
    level: null,
  });

  const hydrateFromFirstLanguage = (rows: any[]) => {
    if (!Array.isArray(rows) || rows.length === 0) {
      resetFormState();
      resetMultipleLanguages();
      // Si no hay data, NO mostrar el formulario (aparecerá EmptyStateFormGlobalComponent)
      handleShowFormSpecific(FormComponentEnum.COMMERCIAL_LANGUAGE, false);
      return;
    }

    if (rows.length === 1) {
      // Un solo idioma - usar formulario simple
      const first = rows[0];
      formState.id = first.id ?? null;
      // El backend puede enviar language_id o language.id
      formState.language = first.language_id ?? first.language?.id ?? null;
      formState.level = first.level ?? null;

      Object.assign(originalFormState, { ...formState });
      resetMultipleLanguages();
      // ✅ Importante: limpiar el snapshot múltiple cuando hay solo 1
      originalMultipleLanguages.value = [];
    } else {
      // Múltiples idiomas - usar array
      multipleLanguages.value = rows.map((row) => ({
        id: row.id ?? null,
        // El backend puede enviar language_id o language.id
        language: row.language_id ?? row.language?.id ?? null,
        level: row.level ?? null,
      }));

      originalMultipleLanguages.value = JSON.parse(JSON.stringify(multipleLanguages.value));
      showMultipleLanguages.value = true;
      resetFormState();
      // ✅ Importante: limpiar el snapshot simple cuando hay múltiples
      Object.assign(originalFormState, { ...initFormState });
    }

    // Si hay data, mostrar el formulario en modo lectura
    handleShowFormSpecific(FormComponentEnum.COMMERCIAL_LANGUAGE, true);
    handleIsEditFormSpecific(FormComponentEnum.COMMERCIAL_LANGUAGE, true);
    handleSavedFormSpecific(FormComponentEnum.COMMERCIAL_LANGUAGE, true);
    markItemComplete('commercial_information'); // Backend usa snake_case, mismo item que otros componentes comerciales
  };

  const loadLanguagesCatalog = async () => {
    if (resourcesLoaded.value) return;
    try {
      const resp = await showLanguages();
      languages.value = resp?.data || [];
    } catch (e) {
      languages.value = [];
      handleError(e as Error);
    } finally {
      resourcesLoaded.value = true;
    }
  };

  const loadSupplierLanguages = async () => {
    if (!supplierId.value) return;
    try {
      isLoading.value = true;
      const resp = await showSupplierLanguages(supplierId.value);
      hydrateFromFirstLanguage(resp?.data || []);
    } catch (e) {
      resetFormState();
      resetMultipleLanguages();
      handleShowFormSpecific(FormComponentEnum.COMMERCIAL_LANGUAGE, false);
      handleError(e as Error);
    } finally {
      isLoading.value = false;
    }
  };

  const addNewLanguage = () => {
    multipleLanguages.value.push(createEmptyLanguage());
  };

  const removeLanguage = (index: number) => {
    if (multipleLanguages.value.length > 1) {
      multipleLanguages.value.splice(index, 1);

      // Si queda solo un idioma, volver al modo simple
      if (multipleLanguages.value.length === 1) {
        const lastLanguage = multipleLanguages.value[0];
        Object.assign(formState, lastLanguage);
        resetMultipleLanguages();
      }
    }
  };

  const handleClose = () => {
    try {
      // Primero reseteamos los estados de loading
      isLoading.value = false;
      isLoadingButton.value = false;

      // Determinar si originalmente había múltiples idiomas
      const hadMultipleLanguages = originalMultipleLanguages.value.length > 1;
      const hadSingleLanguage =
        originalFormState.id || originalFormState.language || originalFormState.level;

      if (hadMultipleLanguages) {
        // Restaurar múltiples idiomas completamente
        multipleLanguages.value = JSON.parse(JSON.stringify(originalMultipleLanguages.value));
        showMultipleLanguages.value = true; // ✅ Este era el problema!
        resetFormState(); // Limpiar formState porque estamos en modo múltiple
        handleIsEditFormSpecific(FormComponentEnum.COMMERCIAL_LANGUAGE, true);
      } else if (hadSingleLanguage) {
        // Restaurar un solo idioma
        Object.assign(formState, JSON.parse(JSON.stringify(originalFormState)));
        resetMultipleLanguages(); // Asegurar que no esté en modo múltiple
        handleIsEditFormSpecific(FormComponentEnum.COMMERCIAL_LANGUAGE, true);
      } else {
        // No había datos guardados - volver al estado vacío
        resetFormState();
        resetMultipleLanguages();
        handleShowFormSpecific(FormComponentEnum.COMMERCIAL_LANGUAGE, false);
      }
    } catch (error) {
      console.error('Error al cerrar formulario:', error);
      isLoading.value = false;
      isLoadingButton.value = false;
    }
  };

  const handleSave = async () => {
    if (!supplierId.value || !getIsFormValid.value) return;

    try {
      isLoadingButton.value = true;

      // Preparar array de idiomas - siempre enviar como array
      let languagesToSave: LanguageForm[] = [];

      if (showMultipleLanguages.value) {
        // Caso múltiples idiomas
        languagesToSave = [...multipleLanguages.value];
      } else {
        // Caso un solo idioma - convertir a array
        languagesToSave = [{ ...formState }];
      }

      // Preparar payload como array de objetos
      const payload = languagesToSave.map((lang) => ({
        supplier_id: supplierId.value,
        language_id: lang.language,
        level: lang.level,
        ...(lang.id && { id: lang.id }), // Solo incluir id si existe
      }));

      // Enviar el array al backend
      const response = await updateOrCreateSupplierLanguage(supplierId.value, payload);

      if (response.success) {
        await loadSupplierModules();
      }

      // feedback
      handleCompleteResponse(response);

      // ✅ REFRESCAR DATOS DESDE EL BACKEND para asegurar consistencia
      await loadSupplierLanguages();

      // marcar como guardado y volver a lectura
      handleShowFormSpecific(FormComponentEnum.COMMERCIAL_LANGUAGE, true);
      handleIsEditFormSpecific(FormComponentEnum.COMMERCIAL_LANGUAGE, true);
      handleSavedFormSpecific(FormComponentEnum.COMMERCIAL_LANGUAGE, true);
      markItemComplete('commercial_information'); // Backend usa snake_case, mismo item que otros componentes comerciales
    } catch (e) {
      handleError(e as Error);
    } finally {
      isLoadingButton.value = false;
    }
  };

  const handleShowForm = () => {
    handleIsEditFormSpecific(FormComponentEnum.COMMERCIAL_LANGUAGE, false);
  };

  const handleAddLanguage = () => {
    if (!showMultipleLanguages.value) {
      // Primera vez que se agrega - convertir el formulario actual en array
      if (formState.language && formState.level) {
        // Si hay datos en el formulario actual, conservarlos y agregar uno nuevo
        multipleLanguages.value = [{ ...formState }, createEmptyLanguage()];
      } else {
        // Si no hay datos, crear directamente dos elementos vacíos
        // Esto resuelve el problema del doble click en registros nuevos
        multipleLanguages.value = [createEmptyLanguage(), createEmptyLanguage()];
      }
      showMultipleLanguages.value = true;
      resetFormState();
    } else {
      // Ya está en modo múltiple - agregar uno nuevo
      addNewLanguage();
    }
  };

  // Watch para cargar catálogo cuando cambia la clasificación (independiente del supplierId)
  watch(
    supplierClassificationId,
    async (newClassificationId) => {
      // Solo cargar catálogo si la clasificación es Staff (Guiado y asistencia)
      if (newClassificationId === 'STA' && !resourcesLoaded.value) {
        await loadLanguagesCatalog();
      }
    },
    { immediate: true }
  );

  // Watch para cargar datos del supplier cuando hay supplierId
  watch(
    supplierId,
    async (newId, oldId) => {
      // Solo proceder si hay supplierId y clasificación es Staff
      if (!newId || newId === oldId) return;
      if (!shouldLoadLanguages.value) return;
      // En modo registro no cargar datos existentes — el formulario debe iniciar vacío
      if (!isEditMode.value) return;

      try {
        isLoading.value = true;
        resetFormState();
        resetMultipleLanguages();
        handleShowFormSpecific(FormComponentEnum.COMMERCIAL_LANGUAGE, false);

        await loadSupplierLanguages();
      } finally {
        isLoading.value = false;
      }
    },
    { immediate: true }
  );

  return {
    formState,
    multipleLanguages,
    showMultipleLanguages,
    isLoading,
    isLoadingButton,
    spinning,
    isEditMode,
    spinTip,

    currentLanguage,
    currentLevel,

    languages,
    levelOptions,
    getLanguageName,
    getLevelLabel,

    getShowFormComponent,
    getIsEditFormComponent,
    getIsFormValid,

    getRepeatedLanguages,
    isLanguageSelected,
    hasAvailableLanguages,

    handleClose,
    handleSave,
    handleShowForm,
    handleAddLanguage,
    addNewLanguage,
    removeLanguage,
  };
}
