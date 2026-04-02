import { ref, reactive, computed, toRef, watch } from 'vue';
import { storeToRefs } from 'pinia';
import { useRoute } from 'vue-router';
import type {
  Inclusion,
  Requirement,
  SelectOption,
  SelectOptionTextType,
} from '@/modules/negotiations/products/configuration/content/shared/interfaces/content';
import { useGenericConfigurationStore } from '@/modules/negotiations/products/configuration/stores/useGenericConfigurationStore';
import { useServiceContentFormAction } from './useServiceContentFormAction';
import { useServiceContentDataLoader } from './useServiceContentDataLoader';
import { useServiceContentSchedules } from './useServiceContentSchedules';
import { useNavigationStore } from '@/modules/negotiations/products/configuration/stores/useNavegationStore';
import { useSupportResourcesStore } from '@/modules/negotiations/products/configuration/stores/useSupportResourcesStore';
import { sendTextForReview } from '../services/serviceContent.service';

export interface UseServiceContentFormProps {
  currentKey?: string;
  currentCode?: string;
}

export const useServiceContentForm = (props?: UseServiceContentFormProps) => {
  const route = useRoute();
  const navigationStore = useNavigationStore();
  const { getSectionsItemActive } = storeToRefs(navigationStore);
  const { setCompletedItem } = navigationStore;

  const genericStore = useGenericConfigurationStore();

  const supportResourcesStore = useSupportResourcesStore();
  const {
    activities: supplierActivities,
    textTypes: supplierTextTypes,
    inclusions: supplierInclusions,
    requirements: supplierRequirements,
  } = storeToRefs(supportResourcesStore);

  const currentKey = computed(() => props?.currentKey ?? '');
  const currentCode = computed(() => props?.currentCode ?? '');

  const serviceDetailsSchedules = toRef(genericStore, 'serviceDetailsSchedules');

  const { getServiceDetailOperability } = genericStore;

  const MARKETING_ALERT_KEY = 'negotiations_content_marketing_alert_dismissed';
  const showMarketingAlert = ref<boolean>(!sessionStorage.getItem(MARKETING_ALERT_KEY));

  watch(showMarketingAlert, (val) => {
    if (!val) sessionStorage.setItem(MARKETING_ALERT_KEY, '1');
  });

  const totalFields = 5;

  const formState = reactive<any>({
    textTypeId: [],
    days: [],
    schedules: [],
    textTypes: [],
  });

  const {
    ITEMS_PER_PAGE,
    quickDurationOptions,
    selectedSchedule,
    schedulesWithActivities,
    operabilitySummaryText,
    isFirstSchedule,
    visibleSchedules,
    canGoPrevious,
    canGoNext,
    groupedSchedules,
    goToPreviousPage,
    goToNextPage,
    selectSchedule,
    handleDurationChange,
    totalDuration,
    copyScheduleOptions,
    copyActivitiesFromSchedule,
    handleAddDay,
    handleDeleteDay,
    handleAddSchedule,
    handleDeleteSchedule,
    loadSchedulesFromOperability,
    loadSchedulesGrouped,
    recalculateSchedules,
  } = useServiceContentSchedules({
    formState,
    currentKey: currentKey.value,
    currentCode: currentCode.value,
    getServiceDetailOperability,
  });

  const isFormValid = computed(() => {
    return completedFields.value === totalFields;
  });

  const textTypes = computed<SelectOptionTextType[]>(() => {
    const textTypesList = supplierTextTypes.value;
    if (!textTypesList || textTypesList.length === 0) {
      return [];
    }

    return textTypesList.map((textType) => ({
      label: textType.name,
      value: textType.code,
      contentLength: textType.contentLength,
    }));
  });

  const inclusions = ref<Inclusion[]>([
    {
      id: null,
      description: null,
      incluye: false,
      visibleCliente: false,
      editMode: false,
    },
  ]);

  const requirements = ref<Requirement[]>([
    {
      id: null,
      description: null,
      visibleCliente: false,
      editMode: false,
    },
  ]);

  const activities = computed<SelectOption[]>(() => {
    const activitiesList = supplierActivities.value;
    if (!activitiesList || activitiesList.length === 0) {
      return [];
    }
    return activitiesList.map((activity) => ({
      label: activity.name,
      value: activity.code,
    }));
  });

  const inclusionOptions = computed<SelectOption[]>(() => {
    const inclusionsList = supplierInclusions.value;
    if (!inclusionsList || inclusionsList.length === 0) {
      return [];
    }
    return inclusionsList.map((inclusion) => ({
      label: inclusion.name,
      value: inclusion.code,
    }));
  });

  const requirementOptions = computed<SelectOption[]>(() => {
    const requirementsList = supplierRequirements.value;
    if (!requirementsList || requirementsList.length === 0) {
      return [];
    }
    return requirementsList.map((requirement) => ({
      label: requirement.name,
      value: requirement.code,
    }));
  });

  const filterOption = (input: string, option: any) => {
    return option.label.toLowerCase().indexOf(input.toLowerCase()) >= 0;
  };

  const getlabelFromTextTypes = (value: number) => {
    return textTypes.value.find((item) => item.value === value)?.label;
  };

  const getTextTypeHtml = (textTypeId: number): string => {
    const textType = formState.textTypes.find((tt: any) => tt.textTypeId === textTypeId);
    return textType?.html || '';
  };

  const getTextTypeExcerpt = (textTypeId: number, maxLength = 120): string => {
    const html = getTextTypeHtml(textTypeId);
    if (!html) return '';
    const withoutNbsp = html.replace(/&nbsp;/gi, ' ').replace(/&#160;/g, ' ');
    const plain = withoutNbsp
      .replace(/<[^>]*>/g, ' ')
      .replace(/\s+/g, ' ')
      .trim();
    return plain.length <= maxLength ? plain : `${plain.slice(0, maxLength)}...`;
  };

  const getTextTypeStatus = (textTypeId: number): string | undefined => {
    const textType = formState.textTypes.find((tt: any) => tt.textTypeId === textTypeId);
    return textType?.status;
  };

  const getInclusionLabel = (code: string | null): string => {
    if (code == null) return '';
    return inclusionOptions.value.find((o) => o.value === code)?.label ?? '';
  };

  const getRequirementLabel = (code: string | null): string => {
    if (code == null) return '';
    return requirementOptions.value.find((o) => o.value === code)?.label ?? '';
  };

  const inclusionReadItems = computed(() =>
    inclusions.value
      .filter((i: { description: string | null }) => i.description)
      .map((i: Inclusion) => ({
        label: getInclusionLabel(i.description),
        visibleCliente: i.visibleCliente,
        incluye: i.incluye,
      }))
  );

  const requirementReadItems = computed(() =>
    requirements.value
      .filter((r: { description: string | null }) => r.description)
      .map((r: Requirement) => ({
        label: getRequirementLabel(r.description),
        visibleCliente: r.visibleCliente,
      }))
  );

  const updateTextTypeHtml = (textTypeId: number, html: string) => {
    const textType = formState.textTypes.find((tt: any) => tt.textTypeId === textTypeId);
    if (textType) {
      textType.html = html;
    } else {
      // Si no existe, agregarlo
      formState.textTypes.push({
        textTypeId: textTypeId,
        html: html,
        status: 'PENDING',
      });
    }
  };

  const handleChangeTextType = () => {
    const currentTextTypeIds = formState.textTypeId;
    const existingTextTypes = formState.textTypes;

    const textTypesMap = new Map(existingTextTypes.map((tt: any) => [tt.textTypeId, tt]));

    const newTextTypes = currentTextTypeIds.map((textTypeId: number) => {
      const existing = textTypesMap.get(textTypeId);
      if (existing) {
        return existing;
      }
      return {
        textTypeId: textTypeId,
        html: null,
        status: 'PENDING',
      };
    });

    formState.textTypes = newTextTypes;
  };

  const isSelectedTextType = (value: number): boolean => {
    return formState.textTypeId.includes(value);
  };

  const getActivityLabel = (activityId: number | null): string => {
    if (activityId == null) return '';
    const option = activities.value.find((a) => a.value === activityId);
    return option?.label ?? '';
  };

  const initData = () => {
    const key = currentKey.value;
    const code = currentCode.value;

    loadSchedulesFromOperability();
    loadContentData(key, code);
    loadSchedulesGrouped();
    // recalculateSchedules
  };

  const completedFields = computed(() => {
    let count = 0;
    if (groupedSchedules.value.some((schedule: any) => schedule.selected)) {
      count++;
    }

    if (
      groupedSchedules.value.some((schedule: any) =>
        schedule.activities.some(
          (activity: any) => activity.activityId != null && activity.duration != null
        )
      )
    ) {
      count++;
    }
    if (formState.textTypeId.some((item: any) => item)) {
      if (
        formState.textTypes.some((textType: any) => textType.html != null && textType.html != '')
      ) {
        count++;
      }
    }
    // inclusiones y requerimientos
    if (
      inclusions.value.some(
        (inclusion: any) => inclusion.description != null && inclusion.description != ''
      )
    ) {
      count++;
    }

    if (
      requirements.value.some(
        (requirement: any) => requirement.description != null && requirement.description != ''
      )
    ) {
      count++;
    }

    return count;
  });

  const isEditingContent = computed(() => {
    return getSectionsItemActive.value?.editing ?? false;
  });

  const { isLoadingButton, handleSaveAndAdvance } = useServiceContentFormAction(
    formState,
    groupedSchedules,
    inclusions,
    requirements,
    {
      get currentKey() {
        return currentKey.value;
      },
      get currentCode() {
        return currentCode.value;
      },
      get currentItemId() {
        return getSectionsItemActive.value?.id ?? '';
      },
    },
    setCompletedItem
  );

  const addRequirement = () => {
    requirements.value.push({
      id: null,
      description: null,
      visibleCliente: false,
      editMode: false,
    });
  };

  const removeRequirement = (index: number) => {
    if (requirements.value.length > 1) {
      requirements.value.splice(index, 1);
    }
  };

  const addInclusion = () => {
    inclusions.value.push({
      id: null,
      description: null,
      incluye: false,
      visibleCliente: false,
      editMode: false,
    });
  };

  const getTimeSchedule = (schedule: any): string => {
    if (schedule.applyAllDay) {
      return '24 hrs';
    }
    if (schedule.singleTime) {
      return schedule.start || '';
    }

    if (!schedule.start && !schedule.end) {
      return '--:--';
    }

    return `${schedule.start || ''} - ${schedule.end || ''}`;
  };

  const inclusionsWithoutId = computed(() => {
    return inclusions.value.filter((inclusion) => inclusion.id === null);
  });

  const removeInclusion = (index: number) => {
    if (inclusions.value.length > 1) {
      inclusions.value.splice(index, 1);
    }
  };

  const { loadContentData } = useServiceContentDataLoader(formState, inclusions, requirements);

  const loadContentOnEditMode = () => {
    const key = currentKey.value;
    const code = currentCode.value;
    loadSchedulesFromOperability();
    loadContentData(key, code);
    loadSchedulesGrouped();
    recalculateSchedules();
  };

  const sendingReviewCodes = ref<string[]>([]);

  const isSendingReview = (textTypeCode: string) => sendingReviewCodes.value.includes(textTypeCode);

  const handleSendForReview = async (textTypeCode: string) => {
    const html = getTextTypeHtml(textTypeCode);
    if (!html) return;

    const productSupplierId = route.params.id as string;
    const serviceDetailsId = genericStore.getServiceDetailId(currentKey.value, currentCode.value);
    if (!productSupplierId || !serviceDetailsId) return;

    sendingReviewCodes.value = [...sendingReviewCodes.value, textTypeCode];
    try {
      await sendTextForReview(productSupplierId, serviceDetailsId, { textTypeCode, html });
      const textType = formState.textTypes.find((tt: any) => tt.textTypeId === textTypeCode);
      if (textType) {
        textType.status = 'SENT_FOR_REVIEW';
      }
    } finally {
      sendingReviewCodes.value = sendingReviewCodes.value.filter((c) => c !== textTypeCode);
    }
  };

  const showEditModal = ref(false);

  const handleEditMode = () => {
    showEditModal.value = true;
  };

  const handleConfirmEdit = () => {
    if (getSectionsItemActive.value) {
      getSectionsItemActive.value.editing = true;
    }
    showEditModal.value = false;
    loadContentOnEditMode();
  };

  const handleCancelEdit = () => {
    showEditModal.value = false;
  };

  watch(
    serviceDetailsSchedules,
    () => {
      initData();
    },
    { deep: true }
  );

  watch(
    () => [currentKey.value, currentCode.value],
    () => {
      initData();
    },
    { immediate: true }
  );

  return {
    totalFields,
    isFormValid,
    completedFields,
    activities,
    textTypes,
    formState,
    isLoadingButton,
    inclusions,
    inclusionsWithoutId,
    requirements,
    inclusionOptions,
    requirementOptions,
    selectedSchedule,
    schedulesWithActivities,

    addInclusion,
    removeInclusion,
    addRequirement,
    removeRequirement,
    filterOption,
    selectSchedule,
    handleAddSchedule,
    handleDeleteSchedule,
    handleAddDay,
    handleDeleteDay,
    handleChangeTextType,
    // filterOption,
    isSelectedTextType,
    getlabelFromTextTypes,
    getTextTypeHtml,
    getTextTypeExcerpt,
    getTextTypeStatus,
    getInclusionLabel,
    getRequirementLabel,
    inclusionReadItems,
    requirementReadItems,
    updateTextTypeHtml,
    handleSaveAndAdvance,
    loadContentOnEditMode,
    handleSendForReview,
    isSendingReview,

    // Modo edición
    isEditingContent,
    showEditModal,
    handleEditMode,
    handleConfirmEdit,
    handleCancelEdit,

    // Paginación horizontal de schedules
    ITEMS_PER_PAGE,
    visibleSchedules,
    canGoPrevious,
    canGoNext,
    goToPreviousPage,
    goToNextPage,
    // Cálculo de horarios
    handleDurationChange,
    totalDuration,
    quickDurationOptions,
    operabilitySummaryText,
    getActivityLabel,
    // Copiar actividades
    copyScheduleOptions,
    copyActivitiesFromSchedule,
    isFirstSchedule,
    // Alerta de marketing
    showMarketingAlert,

    getTimeSchedule,
  };
};
