import type { Rule } from 'ant-design-vue/es/form';
import { notification } from 'ant-design-vue';
import { storeToRefs } from 'pinia';
import { computed, onMounted, ref, watch } from 'vue';

import {
  handleCompleteResponse,
  handleErrorResponse,
} from '@/modules/negotiations/api/responseApi';
import { FormComponentEnum } from '@/modules/negotiations/supplier-new/enums/form-component.enum';
import { useSupplierGlobalComposable } from '@/modules/negotiations/supplier-new/composables/form/supplier-global.composable';
import { useSupplierService } from '@/modules/negotiations/supplier-new/service/supplier.service';
import { useSupplierModulesComposable } from '@/modules/negotiations/supplier-new/composables/supplier-modules.composable';
import { useInformationCommercialStore } from '@/modules/negotiations/supplier-new/store/negotiations/supplier-registration/information-commercial.store';
import { mapItemsToOptions } from '@/modules/negotiations/supplier/register/helpers/supplierFormHelper';
import { useSupplierResourceService } from '@/modules/negotiations/supplier-new/service/supplier-resources.service';
import { useSupplierClassificationStoreFacade } from '@/modules/negotiations/supplier-new/composables/form/negotiations/supplier-registration/supplier-classification-store-facade.composable';
import { isFilled } from '@/modules/negotiations/suppliers/helpers/field-validation-helper';
import { useSupplierCompleteData } from '@/modules/negotiations/supplier-new/composables/form/negotiations/use-supplier-complete-data.composable';

// type CommercialInfoKey =
//   | 'type_food_id'
//   | 'classification'
//   | 'spaces'
//   | 'schedule'
//   | 'additional_information';

// type SummaryField = {
//   key: CommercialInfoKey;
//   label: string;
//   format?: (value: any) => string;
// };

type ParsedCommercial = {
  type_food_id: any;
  classification: any;
  amenities: any[];
  spaces: any[];
  additional_information: any;
  scheduleType: number;
  scheduleGeneral: { open: string | null; close: string | null }[];
  schedule: any[]; // 👈 lo agregamos
};

export function useInformationCommercialComposable() {
  const isLoading = ref(false);
  const isLoadingButton = ref(false);
  const saving = ref(false);
  const resourcesLoaded = ref(false);
  const isLoadingResources = ref(false); // ✅ NUEVO: Flag para prevenir llamadas concurrentes
  const persistedSnapshot = ref<any | null>(null);
  const isRestoring = ref(false);

  const spinTip = computed(() => (isLoadingButton.value ? 'Guardando...' : 'Cargando...'));
  const spinning = computed(() => isLoading.value || isLoadingButton.value);

  const { supplierClassificationId } = useSupplierClassificationStoreFacade();

  const {
    supplierId,
    isEditMode,
    getShowFormComponent,
    getIsEditFormComponent,
    getDisabledComponent,
    getLoadingFormComponent,
    getLoadingButtonComponent,
    handleShowFormSpecific,
    handleIsEditFormSpecific,
    handleSavedFormSpecific,
    openNextSection,
    markItemComplete,
    handleSetActiveItem,
  } = useSupplierGlobalComposable();

  const { loadSupplierModules } = useSupplierModulesComposable();
  const { updateOrCreateSupplierInformationCommercial } = useSupplierService;
  const { fetchInformationCommercialResource } = useSupplierResourceService;

  const { completeData, refetch: refetchCompleteData } = useSupplierCompleteData();

  const informationCommercialStore = useInformationCommercialStore();
  const {
    formState,
    initialFormData,
    formRef,
    showAdditionalInfo,
    typeFoods,
    amenities,
    timeOptions,
  } = storeToRefs(informationCommercialStore);

  const buildEmptySchedule = () => [
    { day: 'MONDAY', label: 'Lun', schedules: [{ open: null, close: null }], available_day: true },
    { day: 'TUESDAY', label: 'Mar', schedules: [{ open: null, close: null }], available_day: true },
    {
      day: 'WEDNESDAY',
      label: 'Mié',
      schedules: [{ open: null, close: null }],
      available_day: true,
    },
    {
      day: 'THURSDAY',
      label: 'Jue',
      schedules: [{ open: null, close: null }],
      available_day: true,
    },
    { day: 'FRIDAY', label: 'Vie', schedules: [{ open: null, close: null }], available_day: true },
    {
      day: 'SATURDAY',
      label: 'Sáb',
      schedules: [{ open: null, close: null }],
      available_day: true,
    },
    { day: 'SUNDAY', label: 'Dom', schedules: [{ open: null, close: null }], available_day: true },
  ];

  const dayMap: Record<string, number> = {
    MONDAY: 1,
    TUESDAY: 2,
    WEDNESDAY: 3,
    THURSDAY: 4,
    FRIDAY: 5,
    SATURDAY: 6,
    SUNDAY: 7,
  };

  const convertTimeToMinutes = (timeString: string): number => {
    if (!timeString) return -1;
    const [hours, minutes] = timeString.split(':').map(Number);
    return hours * 60 + minutes;
  };

  const validateTimeRange = (openTime: string | null, closeTime: string | null): boolean => {
    if (!openTime || !closeTime) return true;

    const openMinutes = convertTimeToMinutes(openTime);
    const closeMinutes = convertTimeToMinutes(closeTime);

    return closeMinutes > openMinutes;
  };

  const showTimeValidationError = () => {
    notification.error({
      message: 'Error de validación',
      description: 'La hora de fin debe ser mayor que la hora de inicio',
      placement: 'topRight',
    });
  };

  const hasEmptyTimeFields = computed(() => {
    if (formState.value.scheduleType === 1) {
      return formState.value.scheduleGeneral.some((schedule: any) => {
        const hasOpen = schedule.open && schedule.open.trim() !== '';
        const hasClose = schedule.close && schedule.close.trim() !== '';
        return (hasOpen && !hasClose) || (!hasOpen && hasClose);
      });
    } else if (formState.value.scheduleType === 2) {
      return formState.value.schedule.some((day: any) => {
        if (!day.available_day) return false;

        return day.schedules.some((schedule: any) => {
          const hasOpen = schedule.open && schedule.open.trim() !== '';
          const hasClose = schedule.close && schedule.close.trim() !== '';
          return (hasOpen && !hasClose) || (!hasOpen && hasClose);
        });
      });
    }
    return false;
  });

  const hasInvalidTimeRanges = computed(() => {
    if (formState.value.scheduleType === 1) {
      return formState.value.scheduleGeneral.some((schedule: any) => {
        const open = schedule.open;
        const close = schedule.close;
        if (open && close) {
          return !validateTimeRange(open, close);
        }
        return false;
      });
    } else if (formState.value.scheduleType === 2) {
      return formState.value.schedule.some((day: any) => {
        if (!day.available_day) return false;

        return day.schedules.some((schedule: any) => {
          const open = schedule.open;
          const close = schedule.close;
          if (open && close) {
            return !validateTimeRange(open, close);
          }
          return false;
        });
      });
    }
    return false;
  });

  const formRules: Record<string, Rule[]> = {
    type_food_id: [
      {
        required: true,
        message: 'Por favor, seleccione al menos un tipo de comida.',
        trigger: 'change',
      },
    ],
  };

  // const getAdditionalInformationItem = (): SummaryField[] => {
  //   if (!formState.value.additional_information) return [];

  //   return [
  //     {
  //       key: 'additional_information',
  //       label: 'Información adicional:',
  //     },
  //   ];
  // };

  const getIsFormValid = computed(() => {
    const hasTypeFoods =
      Array.isArray(formState.value.type_food_id) && formState.value.type_food_id.length > 0;

    const noEmptyTimeFields = !hasEmptyTimeFields.value;

    const noInvalidTimeRanges = !hasInvalidTimeRanges.value;

    return hasTypeFoods && noEmptyTimeFields && noInvalidTimeRanges;
  });

  const getRequestData = () => {
    let schedule: any[] = [];
    let scheduleGeneral = null;

    if (formState.value.scheduleType === 1) {
      const validSchedules = formState.value.scheduleGeneral.filter((s: any) => s.open && s.close);
      if (validSchedules.length > 0) {
        scheduleGeneral = validSchedules;
      }
      schedule = [];
    } else if (formState.value.scheduleType === 2) {
      schedule = (formState.value.schedule || []).map((d: any) => ({
        day_of_week: dayMap[d.day],
        available_day: d.available_day,
        schedules: d.available_day
          ? d.schedules
              .filter((s: any) => s.open && s.close)
              .map((s: any) => ({
                open: s.open,
                close: s.close,
              }))
          : [],
      }));
      scheduleGeneral = null;
    }

    return {
      type_food_id: Array.isArray(formState.value.type_food_id) ? formState.value.type_food_id : [],
      classification: formState.value.classification,
      amenities: formState.value.amenities,
      spaces: formState.value.spaces,
      schedule,
      additional_information: formState.value.additional_information,
      schedule_type: formState.value.scheduleType,
      schedule_general: scheduleGeneral,
    };
  };

  const clone = <T>(v: T): T => JSON.parse(JSON.stringify(v));

  const mapResponseToForm = (raw: any) => {
    const ic = raw?.data ?? raw ?? {};

    let actualScheduleType = ic.schedule_type ?? 1;

    if (
      (ic.schedule_general?.open && ic.schedule_general?.close) ||
      (Array.isArray(ic.schedule_general) &&
        ic.schedule_general.some((s: any) => s.open && s.close))
    ) {
      actualScheduleType = 1;
    } else if (
      ic.schedule &&
      typeof ic.schedule === 'object' &&
      !Array.isArray(ic.schedule) &&
      Object.keys(ic.schedule).length > 0
    ) {
      actualScheduleType = 2;
    } else if (Array.isArray(ic.schedule) && ic.schedule.length > 0) {
      actualScheduleType = 2;
    }

    const parsed: ParsedCommercial = {
      type_food_id: Array.isArray(ic.type_food_id)
        ? ic.type_food_id
        : ic.type_food_id
          ? [ic.type_food_id]
          : [],
      classification: ic.classification ?? 0,
      amenities: Array.isArray(ic.amenities) ? ic.amenities : [],
      spaces:
        Array.isArray(ic.spaces) && ic.spaces.length > 0
          ? ic.spaces
          : [{ spaces: null, capacity: null }],
      additional_information: ic.additional_information ?? null,
      scheduleType: actualScheduleType,
      scheduleGeneral: [{ open: null, close: null }],
      schedule: buildEmptySchedule(),
    };

    if (actualScheduleType === 1 && ic.schedule_general) {
      if (ic.schedule_general.open && ic.schedule_general.close) {
        parsed.scheduleGeneral = [
          { open: ic.schedule_general.open, close: ic.schedule_general.close },
        ];
      } else if (Array.isArray(ic.schedule_general)) {
        parsed.scheduleGeneral = ic.schedule_general.filter((s: any) => s.open && s.close);
        if (parsed.scheduleGeneral.length === 0) {
          parsed.scheduleGeneral = [{ open: null, close: null }];
        }
      }
    }

    if (actualScheduleType === 2 && ic.schedule) {
      parsed.schedule = buildEmptySchedule().map((d) => {
        let saved = null;

        if (typeof ic.schedule === 'object' && !Array.isArray(ic.schedule)) {
          saved = ic.schedule[d.day];
        } else if (Array.isArray(ic.schedule)) {
          saved = ic.schedule.find(
            (item: any) => item.day === d.day || dayMap[d.day] === item.day_of_week
          );
        }

        const schedules = [];
        let availableDay = true;

        if (saved) {
          if (saved.hasOwnProperty('available_day')) {
            availableDay = saved.available_day;
            if (availableDay && saved.schedules && Array.isArray(saved.schedules)) {
              schedules.push(
                ...saved.schedules.map((s: any) => ({ open: s.open, close: s.close }))
              );
            }
          } else if (Array.isArray(saved)) {
            schedules.push(...saved.map((s: any) => ({ open: s.open, close: s.close })));
            availableDay = true;
          } else if (saved.open && saved.close) {
            schedules.push({ open: saved.open, close: saved.close });
            availableDay = true;
          }
        }

        if (schedules.length === 0) {
          schedules.push({ open: null, close: null });
        }

        return {
          ...d,
          schedules,
          available_day: availableDay,
        };
      });
    }

    isRestoring.value = true;
    formState.value = { ...initialFormData.value, ...parsed };

    if (parsed.additional_information) {
      showAdditionalInfo.value = true;
    }

    isRestoring.value = false;

    const hasData =
      (Array.isArray(parsed.type_food_id) && parsed.type_food_id.length > 0) ||
      parsed.classification > 0 ||
      (parsed.amenities?.length ?? 0) > 0 ||
      !!parsed.additional_information ||
      (Array.isArray(parsed.scheduleGeneral) &&
        parsed.scheduleGeneral.some((s: any) => s.open && s.close)) ||
      parsed.schedule.some((d: any) => d.schedules?.some((s: any) => s.open && s.close));

    if (hasData) {
      handleSavedFormSpecific(FormComponentEnum.COMMERCIAL_INFORMATION, true);
      handleIsEditFormSpecific(FormComponentEnum.COMMERCIAL_INFORMATION, true);
      handleShowFormSpecific(FormComponentEnum.COMMERCIAL_INFORMATION, true);
      markItemComplete('commercial_information'); // Backend usa snake_case
      persistedSnapshot.value = clone(formState.value);
    } else {
      persistedSnapshot.value = null;
      handleSavedFormSpecific(FormComponentEnum.COMMERCIAL_INFORMATION, false);
      handleIsEditFormSpecific(FormComponentEnum.COMMERCIAL_INFORMATION, false);
      handleShowFormSpecific(FormComponentEnum.COMMERCIAL_INFORMATION, false);
    }
  };

  const setResources = async () => {
    // ✅ PREVENIR LLAMADAS CONCURRENTES: Si ya está cargando o ya cargó, salir
    if (isLoadingResources.value || resourcesLoaded.value) {
      return;
    }

    try {
      isLoadingResources.value = true;

      if (completeData.value?.data?.data?.resources?.commercial_resources) {
        const resources = completeData.value.data.data.resources.commercial_resources;

        typeFoods.value = mapItemsToOptions(resources.typeFoods) || [];
        amenities.value = mapItemsToOptions(resources.amenities) || [];
        resourcesLoaded.value = true;
      } else {
        const subClassificationId =
          completeData.value?.data?.data?.supplier_info?.supplier_sub_classification_id;
        const { data } = await fetchInformationCommercialResource(subClassificationId);
        typeFoods.value = mapItemsToOptions(data.typeFoods) || [];
        amenities.value = mapItemsToOptions(data.amenities) || [];
        resourcesLoaded.value = true;
      }
    } catch (error) {
      console.error('❌ [CommercialInformation setResources] Error:', error);
      typeFoods.value = [];
      amenities.value = [];
      resourcesLoaded.value = true;
    } finally {
      isLoadingResources.value = false;
    }
  };

  const loadCommercialInformation = async () => {
    if (!supplierId.value) {
      formState.value = { ...initialFormData.value };
      persistedSnapshot.value = null;
      return;
    }
    try {
      if (completeData.value?.data?.data?.information_commercial) {
        const commercialData = completeData.value.data.data.information_commercial;

        mapResponseToForm(commercialData);
      } else {
        formState.value = { ...initialFormData.value };
        persistedSnapshot.value = null;
      }
    } catch (error) {
      console.error('❌ [CommercialInformation loadCommercialInformation] Error:', error);
      formState.value = { ...initialFormData.value };
      persistedSnapshot.value = null;
    }
  };

  const handleToggleAdditionalInfo = () => {
    showAdditionalInfo.value = !showAdditionalInfo.value;

    if (!showAdditionalInfo.value) {
      formState.value.additional_information = null;
    }
  };

  const handleClose = () => {
    if (persistedSnapshot.value) {
      isRestoring.value = true;
      formState.value = clone(persistedSnapshot.value);
      isRestoring.value = false;
      handleIsEditFormSpecific(FormComponentEnum.COMMERCIAL_INFORMATION, true);
      return;
    }
    formState.value = { ...initialFormData.value };
    handleShowFormSpecific(FormComponentEnum.COMMERCIAL_INFORMATION, false);
    handleIsEditFormSpecific(FormComponentEnum.COMMERCIAL_INFORMATION, false);
  };

  const handleSaveForm = async () => {
    if (saving.value || !supplierId.value) return;
    saving.value = true;
    isLoadingButton.value = true;

    try {
      const response = await updateOrCreateSupplierInformationCommercial(
        supplierId.value,
        getRequestData()
      );
      if (response?.success) {
        handleCompleteResponse(response);
        await loadSupplierModules();

        await refetchCompleteData();

        await loadCommercialInformation();
        handleIsEditFormSpecific(FormComponentEnum.COMMERCIAL_INFORMATION, true);
        handleSavedFormSpecific(FormComponentEnum.COMMERCIAL_INFORMATION, true);
        markItemComplete('commercial_information'); // Backend usa snake_case
        openNextSection(FormComponentEnum.MODULE_SERVICES);
      }
    } catch (error) {
      console.error('❌ [CommercialInformation handleSaveForm] Error:', error);
      handleErrorResponse();
      console.log('⛔ Error al guardar:', error);
    } finally {
      isLoadingButton.value = false;
      saving.value = false;
    }
  };

  const handleSave = async () => {
    if (saving.value || isLoadingButton.value) return;
    try {
      await formRef.value?.validate();
      await handleSaveForm();
    } catch (error) {
      console.log('⛔ Error de validación en Información Comercial:', error);
    }
  };

  const handleShowForm = () => {
    if (isLoading.value || saving.value) {
      return;
    }

    // Actualizar el ítem activo del sidebar
    handleSetActiveItem('supplier', 'supplier-negotiations', 'commercial_information');

    if (!persistedSnapshot.value) {
      handleShowFormSpecific(FormComponentEnum.COMMERCIAL_INFORMATION, true);
      handleIsEditFormSpecific(FormComponentEnum.COMMERCIAL_INFORMATION, false);
      handleSavedFormSpecific(FormComponentEnum.COMMERCIAL_INFORMATION, false);
    } else {
      persistedSnapshot.value = clone(formState.value);
      handleIsEditFormSpecific(FormComponentEnum.COMMERCIAL_INFORMATION, false);
    }
  };

  const handleAddSpace = () => {
    formState.value.spaces.push({ spaces: undefined, capacity: undefined });
  };

  const handleRemoveSpace = (index: number) => {
    if (Array.isArray(formState.value.spaces)) {
      formState.value.spaces.splice(index, 1);
    }
  };

  const handleAddGeneralSchedule = () => {
    formState.value.scheduleGeneral.push({ open: null, close: null });
  };

  const handleRemoveGeneralSchedule = (index: number) => {
    if (
      Array.isArray(formState.value.scheduleGeneral) &&
      formState.value.scheduleGeneral.length > 1
    ) {
      formState.value.scheduleGeneral.splice(index, 1);
    }
  };

  const handleAddSchedule = (dayIndex: number) => {
    if (formState.value.schedule[dayIndex]) {
      formState.value.schedule[dayIndex].schedules.push({ open: null, close: null });
    }
  };

  const handleRemoveSchedule = (dayIndex: number, scheduleIndex: number) => {
    if (formState.value.schedule[dayIndex]?.schedules?.length > 1) {
      formState.value.schedule[dayIndex].schedules.splice(scheduleIndex, 1);
    }
  };

  const handleToggleAvailableDay = (dayIndex: number) => {
    if (formState.value.schedule[dayIndex]) {
      const day = formState.value.schedule[dayIndex];
      day.available_day = !day.available_day;

      if (!day.available_day) {
        day.schedules = [{ open: null, close: null }];
      } else if (day.schedules.length === 0) {
        day.schedules = [{ open: null, close: null }];
      }
    }
  };

  const handleGeneralScheduleChange = (scheduleIndex: number = 0) => {
    const schedule = formState.value.scheduleGeneral[scheduleIndex];
    if (!schedule) return;

    const { open, close } = schedule;

    const isOpenComplete = !open || /^([01]?[0-9]|2[0-3]):[0-5][0-9]$/.test(open);
    const isCloseComplete = !close || /^([01]?[0-9]|2[0-3]):[0-5][0-9]$/.test(close);

    if (!isOpenComplete || !isCloseComplete) {
      return;
    }

    const hasOpen = open && open.trim() !== '';
    const hasClose = close && close.trim() !== '';

    if ((hasOpen && !hasClose) || (!hasOpen && hasClose)) {
      return;
    }

    if (hasOpen && hasClose && !validateTimeRange(open, close)) {
      showTimeValidationError();
      schedule.close = null;
    }
  };

  const handleCustomScheduleChange = (
    dayIndex: number,
    scheduleIndex: number,
    type: 'open' | 'close'
  ) => {
    const schedule = formState.value.schedule[dayIndex]?.schedules[scheduleIndex];
    if (!schedule) return;

    const { open, close } = schedule;

    const isOpenComplete = !open || /^([01]?[0-9]|2[0-3]):[0-5][0-9]$/.test(open);
    const isCloseComplete = !close || /^([01]?[0-9]|2[0-3]):[0-5][0-9]$/.test(close);

    if (!isOpenComplete || !isCloseComplete) {
      return;
    }

    const hasOpen = open && open.trim() !== '';
    const hasClose = close && close.trim() !== '';

    if ((hasOpen && !hasClose) || (!hasOpen && hasClose)) {
      return;
    }

    if (hasOpen && hasClose && !validateTimeRange(open, close)) {
      showTimeValidationError();
      schedule[type] = null;
    }
  };

  watch(
    () => supplierClassificationId.value,
    async (newVal, oldVal) => {
      if (newVal === oldVal) return;
      try {
        // ✅ MEJORADO: Resetear flags para recargar recursos cuando cambia la clasificación
        resourcesLoaded.value = false;
        isLoadingResources.value = false;
        await setResources();
        formRef.value?.clearValidate?.();
      } catch (err) {
        console.error('[CommercialInformation] Error en setResources:', err);
      }
    }
  );

  watch(
    supplierId,
    async (newId, oldId) => {
      // Detectar cambio de edición a nuevo registro
      if (oldId !== undefined && newId === undefined) {
        // Resetear formulario a estado inicial usando el método del store
        informationCommercialStore.resetForm();
        showAdditionalInfo.value = false;
        persistedSnapshot.value = null;
        isRestoring.value = false;
        isLoading.value = false;
        isLoadingButton.value = false;
        formRef.value?.clearValidate?.();
        return;
      }

      // Si no hay ID, asegurarse de tener formulario limpio
      if (!newId) {
        informationCommercialStore.resetForm();
        showAdditionalInfo.value = false;
        return;
      }

      if (newId === oldId) return;

      try {
        await loadCommercialInformation();
      } catch (err) {
        console.error('[CommercialInformation] Error en carga:', err);
      }
    },
    { immediate: true }
  );

  watch(
    () => completeData.value,
    async (newData) => {
      // No cargar datos si no hay supplierId (modo nuevo registro)
      if (!supplierId.value) {
        return;
      }

      if (!newData) {
        return;
      }

      // ✅ MEJORADO: Cargar recursos solo una vez, ya sea desde completeData o con fallback
      if (!resourcesLoaded.value) {
        await setResources();
      }

      if (newData.data?.data?.information_commercial) {
        await loadCommercialInformation();
      }
    },
    { immediate: true, deep: true }
  );

  watch(
    () => formState.value.scheduleType,
    (newType, oldType) => {
      if (newType === oldType || isRestoring.value) return;

      if (!persistedSnapshot.value) {
        if (newType === 1) {
          formState.value.schedule = buildEmptySchedule();
          formState.value.scheduleGeneral = [{ open: null, close: null }];
        } else if (newType === 2) {
          formState.value.scheduleGeneral = [{ open: null, close: null }];
          formState.value.schedule = buildEmptySchedule();
        }
      }
    }
  );

  onMounted(async () => {
    try {
      // ✅ Si los recursos no están cargados, cargar directamente en onMounted.
      // Cubre el caso de registro donde supplierId ya está seteado pero isEditMode es false,
      // por lo que el watcher de completeData podría no dispararse a tiempo.
      if (!resourcesLoaded.value && supplierClassificationId.value) {
        await setResources();
      }

      if (!timeOptions.value?.length) {
        for (let hour = 0; hour < 24; hour++) {
          const hh = hour.toString().padStart(2, '0');
          timeOptions.value.push(`${hh}:00`, `${hh}:30`);
        }
      }
    } catch (err) {
      console.error('[CommercialInformation] Error en onMounted:', err);
    } finally {
    }
  });

  const getTypeFoodLabel = () => {
    if (!Array.isArray(formState.value.type_food_id) || formState.value.type_food_id.length === 0) {
      return 'No definido';
    }

    const selectedFoods = formState.value.type_food_id
      .map((id: any) => {
        const food = typeFoods.value.find((item: any) => item.value === id);
        return food?.label;
      })
      .filter(Boolean);

    return selectedFoods.length > 0 ? selectedFoods.join(', ') : 'No definido';
  };

  const getSpacesLabel = () => {
    return (formState.value.spaces || [])
      .filter((r: any) => isFilled(r?.spaces) && isFilled(r?.capacity))
      .map((r: any) => `Sala ${r.spaces} - ${r.capacity}`)
      .join(' / ');
  };

  const getAmenitiesLabel = () => {
    if (!Array.isArray(formState.value.amenities) || formState.value.amenities.length === 0) {
      return 'No definido';
    }

    const selectedAmenities = formState.value.amenities
      .map((id: any) => {
        const amenity = amenities.value.find((item: any) => item.value === id);
        return amenity?.label;
      })
      .filter(Boolean);

    return selectedAmenities.length > 0 ? selectedAmenities.join(', ') : 'No definido';
  };

  let currentInputValue = '';
  let lastValidTime = '';

  const formatTimeInput = (input: string): string | null => {
    if (!input) return null;

    if (/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/.test(input)) {
      return input;
    }

    const cleaned = input.replace(/\D/g, '');

    if (cleaned.length === 0) return null;

    let hours: number;
    let minutes: number;

    if (cleaned.length === 1) {
      hours = parseInt(cleaned);
      minutes = 0;
    } else if (cleaned.length === 2) {
      hours = parseInt(cleaned);
      minutes = 0;
    } else if (cleaned.length === 3) {
      hours = parseInt(cleaned.substring(0, 1));
      minutes = parseInt(cleaned.substring(1, 3));
    } else if (cleaned.length >= 4) {
      hours = parseInt(cleaned.substring(0, 2));
      minutes = parseInt(cleaned.substring(2, 4));
    } else {
      return null;
    }

    if (hours < 0 || hours > 23 || minutes < 0 || minutes > 59) {
      return null;
    }

    return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`;
  };

  const formatTimeInputOnBlur = (input: string): string | null => {
    if (!input) return null;

    if (/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/.test(input)) {
      return input;
    }

    const colonMatch = input.match(/^(\d{1,2}):(\d{1})$/);
    if (colonMatch) {
      const hours = parseInt(colonMatch[1]);
      const minutes = parseInt(colonMatch[2] + '0');

      if (hours >= 0 && hours <= 23 && minutes >= 0 && minutes <= 59) {
        return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`;
      }
    }

    return formatTimeInput(input);
  };

  const formatTimeWhileTyping = (input: string): string => {
    if (!input) return '';

    const numbers = input.replace(/\D/g, '');

    if (numbers.length <= 2) {
      return numbers;
    } else if (numbers.length <= 4) {
      return `${numbers.substring(0, 2)}:${numbers.substring(2)}`;
    } else {
      return `${numbers.substring(0, 2)}:${numbers.substring(2, 4)}`;
    }
  };

  const handleTimeInput = (
    value: string,
    section: string,
    field: string,
    scheduleIndex: number = 0
  ) => {
    const formattedInput = formatTimeWhileTyping(value);
    currentInputValue = formattedInput;

    if (section === 'scheduleGeneral') {
      if (formattedInput.length >= 2 && formState.value.scheduleGeneral[scheduleIndex]) {
        formState.value.scheduleGeneral[scheduleIndex][field as 'open' | 'close'] = formattedInput;
      }
    }
  };

  const handleTimeBlur = (section: string, field: string, scheduleIndex: number = 0) => {
    if (currentInputValue) {
      const formattedTime = formatTimeInput(currentInputValue);
      if (formattedTime) {
        if (section === 'scheduleGeneral' && formState.value.scheduleGeneral[scheduleIndex]) {
          formState.value.scheduleGeneral[scheduleIndex][field as 'open' | 'close'] = formattedTime;
          lastValidTime = formattedTime;
        }
        handleGeneralScheduleChange(scheduleIndex);
      } else {
        if (
          section === 'scheduleGeneral' &&
          lastValidTime &&
          formState.value.scheduleGeneral[scheduleIndex]
        ) {
          formState.value.scheduleGeneral[scheduleIndex][field as 'open' | 'close'] = lastValidTime;
        }
      }
      currentInputValue = '';
    }
  };

  const handleCustomTimeInput = (
    value: string,
    dayIndex: number,
    scheduleIndex: number,
    field: string
  ) => {
    const formattedInput = formatTimeWhileTyping(value);
    currentInputValue = formattedInput;

    if (
      formattedInput.length >= 2 &&
      formState.value.schedule[dayIndex]?.schedules[scheduleIndex]
    ) {
      formState.value.schedule[dayIndex].schedules[scheduleIndex][field as 'open' | 'close'] =
        formattedInput;
    }
  };

  const handleCustomTimeBlur = (dayIndex: number, scheduleIndex: number, field: string) => {
    if (currentInputValue) {
      const formattedTime = formatTimeInput(currentInputValue);
      if (formattedTime && formState.value.schedule[dayIndex]?.schedules[scheduleIndex]) {
        formState.value.schedule[dayIndex].schedules[scheduleIndex][field as 'open' | 'close'] =
          formattedTime;
        handleCustomScheduleChange(dayIndex, scheduleIndex, field as 'open' | 'close');
      }
      currentInputValue = '';
    }
  };

  const formatTimeWhileTypingImproved = (input: string): string => {
    if (!input) return '';

    const numbers = input.replace(/\D/g, '');

    if (numbers.length === 0) {
      return '';
    } else if (numbers.length === 1) {
      return numbers;
    } else if (numbers.length === 2) {
      return numbers;
    } else if (numbers.length === 3) {
      return `${numbers.substring(0, 2)}:${numbers.substring(2, 3)}`;
    } else if (numbers.length >= 4) {
      return `${numbers.substring(0, 2)}:${numbers.substring(2, 4)}`;
    }

    return numbers;
  };

  const handleTimeInputChange = (
    event: Event,
    section: string,
    field: string,
    dayIndex?: number,
    scheduleIndex?: number,
    generalScheduleIndex?: number
  ) => {
    const target = event.target as HTMLInputElement;
    const value = target.value;

    const formattedValue = formatTimeWhileTypingImproved(value);

    target.value = formattedValue;

    if (section === 'scheduleGeneral' && generalScheduleIndex !== undefined) {
      if (formState.value.scheduleGeneral[generalScheduleIndex]) {
        formState.value.scheduleGeneral[generalScheduleIndex][field as 'open' | 'close'] =
          formattedValue;

        handleGeneralScheduleChange(generalScheduleIndex);
      }
    } else if (dayIndex !== undefined && scheduleIndex !== undefined) {
      if (formState.value.schedule[dayIndex]?.schedules[scheduleIndex]) {
        formState.value.schedule[dayIndex].schedules[scheduleIndex][field as 'open' | 'close'] =
          formattedValue;

        handleCustomScheduleChange(dayIndex, scheduleIndex, field as 'open' | 'close');

        if (dayIndex === 0 && scheduleIndex === 0) {
          formState.value.schedule.forEach((day, i) => {
            if (i > 0 && day.schedules[0]) {
              day.schedules[0][field as 'open' | 'close'] = formattedValue;
            }
          });
        }
      }
    }
  };

  const handleTimeInputBlur = (
    event: Event,
    section: string,
    field: string,
    dayIndex?: number,
    scheduleIndex?: number,
    generalScheduleIndex?: number
  ) => {
    const target = event.target as HTMLInputElement;
    const value = target.value;

    if (!value.trim()) {
      if (section === 'scheduleGeneral' && generalScheduleIndex !== undefined) {
        if (formState.value.scheduleGeneral[generalScheduleIndex]) {
          formState.value.scheduleGeneral[generalScheduleIndex][field as 'open' | 'close'] = null;
        }
      } else if (dayIndex !== undefined && scheduleIndex !== undefined) {
        if (formState.value.schedule[dayIndex]?.schedules[scheduleIndex]) {
          formState.value.schedule[dayIndex].schedules[scheduleIndex][field as 'open' | 'close'] =
            null;
        }
      }
      return;
    }

    const formattedTime = formatTimeInputOnBlur(value);

    if (formattedTime) {
      target.value = formattedTime;

      if (section === 'scheduleGeneral' && generalScheduleIndex !== undefined) {
        if (formState.value.scheduleGeneral[generalScheduleIndex]) {
          formState.value.scheduleGeneral[generalScheduleIndex][field as 'open' | 'close'] =
            formattedTime;
        }
        handleGeneralScheduleChange(generalScheduleIndex);
      } else if (dayIndex !== undefined && scheduleIndex !== undefined) {
        if (formState.value.schedule[dayIndex]?.schedules[scheduleIndex]) {
          formState.value.schedule[dayIndex].schedules[scheduleIndex][field as 'open' | 'close'] =
            formattedTime;
          handleCustomScheduleChange(dayIndex, scheduleIndex, field as 'open' | 'close');

          if (dayIndex === 0 && scheduleIndex === 0) {
            formState.value.schedule.forEach((day, i) => {
              if (i > 0 && day.schedules[0]) {
                day.schedules[0][field as 'open' | 'close'] = formattedTime;
              }
            });
          }
        }
      }
    } else {
      target.value = '';
      if (section === 'scheduleGeneral' && generalScheduleIndex !== undefined) {
        if (formState.value.scheduleGeneral[generalScheduleIndex]) {
          formState.value.scheduleGeneral[generalScheduleIndex][field as 'open' | 'close'] = null;
        }
      } else if (dayIndex !== undefined && scheduleIndex !== undefined) {
        if (formState.value.schedule[dayIndex]?.schedules[scheduleIndex]) {
          formState.value.schedule[dayIndex].schedules[scheduleIndex][field as 'open' | 'close'] =
            null;
        }
      }
    }
  };

  const handleTimeInputFocus = (
    event: Event,
    _section: string,
    _field: string,
    _dayIndex?: number,
    _scheduleIndex?: number,
    _generalScheduleIndex?: number
  ) => {
    const target = event.target as HTMLInputElement;
    const currentValue = target.value;

    if (currentValue && /^([01]?[0-9]|2[0-3]):[0-5][0-9]$/.test(currentValue)) {
      requestAnimationFrame(() => {
        setTimeout(() => {
          if (document.activeElement === target) {
            target.setSelectionRange(0, currentValue.length);
            target.select();
          }
        }, 1);
      });
    }
  };

  const handleTimeInputClick = (
    event: Event,
    _section: string,
    _field: string,
    _dayIndex?: number,
    _scheduleIndex?: number,
    _generalScheduleIndex?: number
  ) => {
    const target = event.target as HTMLInputElement;
    const currentValue = target.value;

    if (currentValue && /^([01]?[0-9]|2[0-3]):[0-5][0-9]$/.test(currentValue)) {
      event.preventDefault();

      setTimeout(() => {
        target.setSelectionRange(0, currentValue.length);
        target.select();
      }, 0);
    }
  };

  const handleTimeKeyDown = (event: KeyboardEvent) => {
    const target = event.target as HTMLInputElement;

    if (event.key === 'Enter' || event.key === 'Tab') {
      target.blur();
      return;
    }

    if (['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) {
      return;
    }

    if (/^\d$/.test(event.key)) {
      const selectionStart = target.selectionStart || 0;
      const selectionEnd = target.selectionEnd || 0;
      const selectedText = target.value.substring(selectionStart, selectionEnd);
      const totalLength = target.value.length;

      const isFullySelected =
        (selectionStart === 0 && selectionEnd === totalLength) ||
        (selectedText === target.value && selectedText.length > 0) ||
        (selectionEnd - selectionStart === totalLength && totalLength > 0);

      if (isFullySelected && /^([01]?[0-9]|2[0-3]):[0-5][0-9]$/.test(target.value)) {
        target.value = '';
        event.preventDefault();
        target.value = event.key;
        target.dispatchEvent(new Event('input', { bubbles: true }));
        return;
      }

      const currentValue = target.value.replace(/\D/g, '');
      if (currentValue.length >= 4) {
        event.preventDefault();
      }
      return;
    }
    if (!/^\d$/.test(event.key)) {
      event.preventDefault();
      return;
    }
  };

  return {
    formState,
    formRef,
    typeFoods,
    amenities,
    timeOptions,
    showAdditionalInfo,
    isLoading,
    isLoadingButton,
    saving,
    spinning,
    isEditMode,
    spinTip,
    formRules,
    getIsFormValid,
    getShowFormComponent,
    getIsEditFormComponent,
    getDisabledComponent,
    getLoadingFormComponent,
    getLoadingButtonComponent,
    handleClose,
    handleSave,
    handleShowForm,
    handleToggleAdditionalInfo,
    handleAddSpace,
    handleRemoveSpace,
    handleAddGeneralSchedule,
    handleRemoveGeneralSchedule,
    handleAddSchedule,
    handleRemoveSchedule,
    handleToggleAvailableDay,
    handleGeneralScheduleChange,
    handleCustomScheduleChange,
    getTypeFoodLabel,
    getSpacesLabel,
    getAmenitiesLabel,
    handleTimeInput,
    handleTimeBlur,
    handleCustomTimeInput,
    handleCustomTimeBlur,
    handleTimeInputChange,
    handleTimeInputBlur,
    handleTimeInputFocus,
    handleTimeInputClick,
    handleTimeKeyDown,
  };
}
