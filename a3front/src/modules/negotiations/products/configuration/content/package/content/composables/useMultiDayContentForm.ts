import { reactive, ref, onMounted, watch, computed } from 'vue';
import { storeToRefs } from 'pinia';
import { useRoute } from 'vue-router';
import { usePackageConfigurationStore } from '@/modules/negotiations/products/configuration/stores/usePackageConfigurationStore';
import {
  sendPackageTextForReview,
  type PackageSendTextForReviewRequest,
} from '../services/serviceContent.service';
import type { UseMultiDayContentFormOptions, FormState } from '../interfaces';
import { useMultiDayContentSchedules } from './useMultiDayContentSchedules';
import { useMultiDayContentTexts } from './useMultiDayContentTexts';
import { useMultiDayContentCatalogs } from './useMultiDayContentCatalogs';
import { useMultiDayContentDataLoader } from './useMultiDayContentDataLoader';
import { useMultiDayContentMeta } from './useMultiDayContentMeta';
import { useNavigationStore } from '@/modules/negotiations/products/configuration/stores/useNavegationStore';
import { useSupportResourcesStore } from '@/modules/negotiations/products/configuration/stores/useSupportResourcesStore';

export const useMultiDayContentForm = (opts?: UseMultiDayContentFormOptions) => {
  const route = useRoute();
  const navigationStore = useNavigationStore();
  const { getSectionsItemActive } = storeToRefs(navigationStore);
  const { setCompletedItem } = navigationStore;

  const supportResourcesStore = useSupportResourcesStore();
  const {
    programDurations,
    activities: supplierActivities,
    textTypes: supplierTextTypes,
  } = storeToRefs(supportResourcesStore);

  const packageStore = usePackageConfigurationStore();
  const { serviceDetailsSchedules } = storeToRefs(packageStore);

  const currentKey = computed(() => opts?.currentKey ?? '');
  const currentCode = computed(() => opts?.currentCode ?? '');
  const programDurationAmountDays = computed(() => {
    return (
      programDurations.value.find((programDuration) => programDuration.code === currentKey.value)
        ?.duration?.days ?? 0
    );
  });

  const quickDurationOptions = ['00:30', '00:45', '01:00', '01:30', '01:45', '02:00'];

  const formState = reactive<FormState>({
    textTypeId: [],
    days: [],
    schedules: [],
    textTypes: [],
  });

  const schedulesComposable = useMultiDayContentSchedules({
    formState,
    programDurationAmountDays: programDurationAmountDays.value,
    currentKey: currentKey.value,
    currentCode: currentCode.value,
  });

  const {
    ITEMS_PER_PAGE,
    selectedSchedule,
    schedulesWithActivities,
    operabilitySummaryText,
    isFirstSchedule,
    visibleSchedules,
    canGoPrevious,
    canGoNext,
    activitiesGroupedByDay,
    totalDuration,
    groupedSchedules,
    goToPreviousPage,
    goToNextPage,
    getCopyScheduleOptions,
    copyActivitiesFromSchedule,
    getGlobalActivityIndex,
    selectSchedule,
    handleDurationChange,
    handleAddDay,
    handleDeleteDay,
    handleAddSchedule,
    handleDeleteSchedule,
    loadSchedulesFromOperability,
    loadSchedulesGrouped,
    recalculateSchedules,
  } = schedulesComposable;

  const textsComposable = useMultiDayContentTexts({
    formState,
    supplierTextTypes: supplierTextTypes.value,
    programDurationAmountDays: programDurationAmountDays.value,
  });

  const {
    textTypes,
    getLabelFromTextTypes,
    getTextTypeHtml,
    getTextTypeExcerpt,
    getTextTypeStatus,
    getTextTypeDays,
    isSelectedTextType,
    updateTextTypeHtml,
    handleChangeTextType,
    loadDays,
  } = textsComposable;

  const catalogsComposable = useMultiDayContentCatalogs({
    supplierActivities: supplierActivities.value,
  });

  const { activities, filterOption } = catalogsComposable;

  const getActivityLabel = (activityId: number | string | null): string => {
    if (activityId == null) return '';
    const option = activities.value.find(
      (a) => a.value === activityId || a.value === String(activityId)
    );
    return option?.label ?? '';
  };

  const expandedReadCardKey = ref<string | null>(null);

  const toggleReadCard = (textTypeCode: string) => {
    expandedReadCardKey.value = expandedReadCardKey.value === textTypeCode ? null : textTypeCode;
  };

  const sendingReviewCodes = ref<string[]>([]);

  const isSendingReview = (textTypeCode: string) => sendingReviewCodes.value.includes(textTypeCode);

  const handleSendForReview = async (textTypeCode: string) => {
    const productSupplierId = route.params.id as string;
    const serviceDetailsId = packageStore.getServiceDetailId(currentKey.value, currentCode.value);
    if (!productSupplierId || !serviceDetailsId) return;

    let request: PackageSendTextForReviewRequest;

    if (textTypeCode === 'ITINERARY') {
      const days = getTextTypeDays(textTypeCode);
      if (!days.some((d) => !!d.html)) return;
      request = { textTypeCode, days };
    } else {
      const html = getTextTypeHtml(textTypeCode);
      if (!html) return;
      request = { textTypeCode, html };
    }

    sendingReviewCodes.value = [...sendingReviewCodes.value, textTypeCode];
    try {
      await sendPackageTextForReview(productSupplierId, serviceDetailsId, request);
      const textType = formState.textTypes.find((tt) => tt.textTypeCode === textTypeCode);
      if (textType) {
        textType.status = 'SENT_FOR_REVIEW';
      }
    } finally {
      sendingReviewCodes.value = sendingReviewCodes.value.filter((c) => c !== textTypeCode);
    }
  };

  const { loadContentData } = useMultiDayContentDataLoader(formState);

  const metaComposable = useMultiDayContentMeta({
    formState,
    groupedSchedules,
    currentKey: currentKey.value,
    currentCode: currentCode.value,
    programDurationAmountDays: programDurationAmountDays.value,
    getSectionsItemActive,
    setCompletedItem,
  });

  const {
    totalFieldsCompleted,
    isEditingContent,
    showMarketingAlert,
    completedFields,
    handleEditMode,
    isLoadingButton,
    handleSaveAndAdvance,
  } = metaComposable;

  const initData = () => {
    loadDays();
    loadSchedulesFromOperability();
    loadContentData(currentKey.value, currentCode.value);
    loadSchedulesGrouped();
    recalculateSchedules();
  };

  onMounted(() => {
    initData();
  });

  watch(
    serviceDetailsSchedules,
    () => {
      initData();
    },
    {
      deep: true,
    }
  );

  return {
    isEditingContent,
    totalFieldsCompleted,
    completedFields,
    activities,
    textTypes,
    formState,
    isLoadingButton,
    filterOption,
    selectSchedule,
    handleAddSchedule,
    handleDeleteSchedule,
    handleAddDay,
    handleDeleteDay,
    handleChangeTextType,
    isSelectedTextType,
    getLabelFromTextTypes,
    handleSaveAndAdvance,
    handleEditMode,
    // Paginación de schedules
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
    // Copiar actividades
    getCopyScheduleOptions,
    copyActivitiesFromSchedule,
    isFirstSchedule,
    selectedSchedule,
    schedulesWithActivities,
    operabilitySummaryText,
    getActivityLabel,
    activitiesGroupedByDay,
    getGlobalActivityIndex,
    // Text types
    getTextTypeHtml,
    getTextTypeExcerpt,
    getTextTypeStatus,
    getTextTypeDays,
    updateTextTypeHtml,
    // Alerta de marketing
    showMarketingAlert,
    // Tarjeta de lectura expandible
    expandedReadCardKey,
    toggleReadCard,
    // Enviar a revisión
    isSendingReview,
    handleSendForReview,
  };
};
