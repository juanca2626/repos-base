import { ref, computed, type Ref, type ComputedRef } from 'vue';
import { useMultiDayContentRequestBuilder } from './useMultiDayContentRequestBuilder';
import { useMultiDayContentFormActions } from './useMultiDayContentFormActions';
import type { MultiDayContentRequest, ScheduleForRequest, FormState } from '../interfaces';

export interface UseMultiDayContentMetaOptions {
  formState: FormState;
  groupedSchedules: Ref<any[]>;
  currentKey: string;
  currentCode: string;
  programDurationAmountDays: number;
  getSectionsItemActive: ComputedRef<any>;
  setCompletedItem: (currentKey: string, currentCode: string, currentItemId: string) => void;
}

const areSelectedTextTypesComplete = (
  selectedTextTypeCodes: (string | number)[],
  textTypes: FormState['textTypes'],
  amountDays: number
) => {
  if (selectedTextTypeCodes.length === 0) return true;

  return selectedTextTypeCodes.every((code) => {
    const textTypeCode = String(code);
    const textType = textTypes.find((tt) => tt.textTypeCode === textTypeCode);
    if (!textType) return false;
    const days = textType.days ?? [];

    if (textTypeCode === 'ITINERARY') {
      return (
        amountDays > 0 &&
        Array.from({ length: amountDays }, (_, i) => i + 1).every((dayNumber) => {
          const dayData = days.find((d) => d.dayNumber === dayNumber);
          return (dayData?.html ?? '').trim() !== '';
        })
      );
    }

    // REMARKS, MENU, etc.: no tienen días; basta con que tengan al menos un bloque con contenido
    return days.some((day) => (day?.html ?? '').trim() !== '');
  });
};

export const useMultiDayContentMeta = (opts: UseMultiDayContentMetaOptions) => {
  const {
    formState,
    groupedSchedules,
    programDurationAmountDays,
    currentKey,
    currentCode,
    getSectionsItemActive,
    setCompletedItem,
  } = opts;
  // const formProgressStore = useFormProgressStore(); // ELIMINADO

  const totalFieldsCompleted = 3;

  const showMarketingAlert = ref<boolean>(true);

  const isEditingContent = computed(() => {
    return getSectionsItemActive.value?.editing ?? false;
  });

  const completedFields = computed(() => {
    let completedCount = 0;
    const amountDays = Math.max(programDurationAmountDays ?? 0, formState.days?.length ?? 0);

    // Validar que hay un schedule seleccionado
    const hasSelectedSchedule = groupedSchedules.value.some((schedule) => schedule.selected);
    if (hasSelectedSchedule) completedCount += 1;

    // Validar que todas las actividades están completas por día
    const selectedSchedule = groupedSchedules.value.find((schedule) => schedule.selected);

    if (selectedSchedule?.activities && amountDays > 0) {
      const allDaysHaveCompleteActivities = Array.from(
        { length: amountDays },
        (_, i) => i + 1
      ).every((dayNumber) =>
        selectedSchedule.activities.some(
          (activity: any) =>
            (activity.numberDay ?? 0) === dayNumber &&
            activity.duration != null &&
            activity.activityId != null
        )
      );
      if (allDaysHaveCompleteActivities) completedCount += 1;
    }

    const selectedTextTypeCodes = (formState.textTypeId ?? []) as (string | number)[];

    const textTypesComplete = areSelectedTextTypesComplete(
      selectedTextTypeCodes,
      formState.textTypes,
      amountDays
    );

    if (textTypesComplete) completedCount += 1;

    return completedCount;
  });

  const handleEditMode = () => {
    getSectionsItemActive.value.editing = true;
  };

  const { buildRequest } = useMultiDayContentRequestBuilder();

  const buildContentRequest = (): MultiDayContentRequest =>
    buildRequest({
      schedules: formState.schedules as unknown as ScheduleForRequest[],
      groupedSchedules,
      textTypes: formState.textTypes,
    });

  const formActions = useMultiDayContentFormActions({
    buildContentRequest,
    props: {
      currentKey,
      currentCode,
      currentItemId: getSectionsItemActive.value.id,
    },
    setCompletedItem,
  });

  const { isLoadingButton, handleSaveAndAdvance } = formActions;

  return {
    // Constantes
    totalFieldsCompleted,
    // Estado
    isEditingContent,
    showMarketingAlert,
    // Computed
    completedFields,
    // Handlers
    handleEditMode,
    // Acciones
    isLoadingButton,
    handleSaveAndAdvance,
  };
};
