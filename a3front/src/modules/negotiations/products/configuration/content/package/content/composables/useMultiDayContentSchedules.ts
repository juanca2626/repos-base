import { ref, computed, watch, nextTick } from 'vue';
import { usePackageConfigurationStore } from '@/modules/negotiations/products/configuration/stores/usePackageConfigurationStore';
import { resolveSchedulesByAmountDays } from '@/modules/negotiations/products/configuration/utils/schedule.utils';
import {
  completeTimeValue,
  timeToMinutes,
  minutesToTime,
} from '@/modules/negotiations/products/configuration/utils/time.utils';
import type { Operability } from '@/modules/negotiations/products/configuration/interfaces/service-details.interface';
import type { ContentActivity, ContentSchedule, FormState } from '../interfaces';
import { calculateSchedulesForActivities } from '../utils/multiDayContentForm.utils';
import { groupScheduleForActivityDay } from '@/modules/negotiations/products/configuration/utils/groupScheduleForActivityDay.util';
export interface UseMultiDayContentSchedulesOptions {
  formState: FormState;
  programDurationAmountDays: number;
  currentKey: string;
  currentCode: string;
}

export const useMultiDayContentSchedules = (opts: UseMultiDayContentSchedulesOptions) => {
  const { formState, programDurationAmountDays, currentKey, currentCode } = opts;
  const packageStore = usePackageConfigurationStore();

  const ITEMS_PER_PAGE = 4;
  const currentPageIndex = ref(0);

  const groupedSchedules = ref<any[]>([]);

  const selectedSchedule = computed<ContentSchedule | null>(() => {
    return groupedSchedules.value.find((schedule) => schedule.selected) || null;
  });

  const isFirstSchedule = computed(() => {
    if (!selectedSchedule.value || groupedSchedules.value.length === 0) return false;
    const firstSchedule = groupedSchedules.value[0];
    return selectedSchedule.value.id === firstSchedule.id;
  });

  const visibleSchedules = computed(() => {
    const start = currentPageIndex.value * ITEMS_PER_PAGE;
    const end = start + ITEMS_PER_PAGE;
    return groupedSchedules.value.slice(start, end);
  });

  const canGoPrevious = computed(() => {
    return currentPageIndex.value > 0;
  });

  const canGoNext = computed(() => {
    const totalSchedules = groupedSchedules.value.length;
    const maxIndex = Math.ceil(totalSchedules / ITEMS_PER_PAGE) - 1;
    return currentPageIndex.value < maxIndex;
  });

  const activitiesGroupedByDay = computed(() => {
    const activities = selectedSchedule.value?.activities;
    if (!activities?.length) return [] as { numberDay: number; activities: ContentActivity[] }[];

    const byDay = new Map<number, ContentActivity[]>();
    for (const activity of activities) {
      const day = activity.numberDay ?? 0;
      if (!byDay.has(day)) byDay.set(day, []);
      byDay.get(day)!.push(activity);
    }
    return Array.from(byDay.entries())
      .sort(([dayA], [dayB]) => dayA - dayB)
      .map(([numberDay, activities]) => ({ numberDay, activities }));
  });

  const totalDuration = computed(() => {
    if (!selectedSchedule.value || !selectedSchedule.value.activities) {
      return '00:00';
    }

    let totalMinutes = 0;
    let hasNullDuration = false;

    for (const activity of selectedSchedule.value.activities) {
      if (!activity.duration) {
        hasNullDuration = true;
        break;
      }
      totalMinutes += timeToMinutes(activity.duration);
    }

    if (hasNullDuration) {
      return null;
    }

    return minutesToTime(totalMinutes);
  });

  const schedulesWithActivities = computed(() =>
    groupedSchedules.value.filter((schedule) =>
      schedule.activities?.some(
        (activity: any) => activity.duration !== null && activity.activityId !== null
      )
    )
  );

  const operabilitySummaryText = computed(() => {
    const configuraciones = groupedSchedules.value.filter((schedule) =>
      schedule.activities?.some(
        (activity: any) => activity.duration !== null && activity.activityId !== null
      )
    ).length;
    const horariosDeSalida = groupedSchedules.value.reduce(
      (acc, s) =>
        acc +
        (s.activities?.filter((a: any) => a.duration !== null && a.activityId !== null)?.length ||
          0),
      0
    );
    return `${configuraciones} configuraciones con ${horariosDeSalida} horarios de salida`;
  });

  const getGlobalActivityIndex = (groupIndex: number, activityIndexInDay: number): number => {
    const grouped = activitiesGroupedByDay.value;
    let globalIndex = 0;
    for (let i = 0; i < groupIndex; i++) {
      globalIndex += grouped[i].activities.length;
    }
    return globalIndex + activityIndexInDay;
  };

  const getCopyScheduleOptions = (targetDay: number) => {
    const currentSelectedId = selectedSchedule.value?.id;
    const options: { label: string; value: string }[] = [];

    for (const schedule of groupedSchedules.value) {
      if (schedule.id === currentSelectedId || !schedule.activities?.length) continue;

      const dayActivities = schedule.activities.filter(
        (activity: any) => (activity.numberDay ?? 0) === targetDay
      );
      if (dayActivities.length === 0) continue;

      const hasValidData = dayActivities.some(
        (activity: any) => activity.duration !== null || activity.activityId !== null
      );
      if (hasValidData) {
        options.push({
          label: `${schedule.label} - Día ${targetDay}`,
          value: `${schedule.id}_${targetDay}`,
        });
      }
    }

    return options.sort((optionA, optionB) => optionA.label.localeCompare(optionB.label));
  };

  const getServiceDetailOperability = (
    serviceKey: string,
    serviceCode: string
  ): Operability | null => {
    const serviceDetail = packageStore.getServiceDetail(serviceKey, serviceCode);
    return (serviceDetail?.content?.operability as Operability) || null;
  };

  const calculateSchedules = () => {
    if (!selectedSchedule.value) return;

    const startTime = selectedSchedule.value.start || '00:00';
    if (!startTime) return;

    const activities = selectedSchedule.value.activities;
    calculateSchedulesForActivities(activities, startTime);
  };

  const scheduleRecalculation = () => {
    nextTick(() => {
      calculateSchedules();
    });
  };

  const createEmptyActivity = (numberDay: number, day: string | null = null): ContentActivity => {
    return {
      numberDay,
      day,
      duration: null,
      activityId: null,
      calculatedSchedule: null,
    };
  };

  const selectSchedule = (id: number | string) => {
    groupedSchedules.value.forEach((schedule: any) => {
      schedule.selected = schedule.id === id;
    });

    scheduleRecalculation();
  };

  const goToPreviousPage = () => {
    if (canGoPrevious.value) {
      currentPageIndex.value -= 1;
    }
  };

  const goToNextPage = () => {
    if (canGoNext.value) {
      currentPageIndex.value += 1;
    }
  };

  const handleDurationChange = (value: string, activityIndex: number) => {
    if (!selectedSchedule.value?.activities) return;

    const completedValue = completeTimeValue(value);
    selectedSchedule.value.activities[activityIndex].duration = completedValue || null;

    calculateSchedules();
  };

  const handleAddDay = (indexDay: number) => {
    if (!selectedSchedule.value?.activities) return;

    const newDayNumber = indexDay + 1;
    selectedSchedule.value.activities.push(createEmptyActivity(newDayNumber));

    scheduleRecalculation();
  };

  const handleDeleteDay = (dayNumber: number) => {
    if (!selectedSchedule.value?.activities) return;

    const dayIndex = selectedSchedule.value.activities.findIndex(
      (activity) => activity.numberDay === dayNumber
    );
    if (dayIndex < 0) return;

    selectedSchedule.value.activities.splice(dayIndex, 1);
    scheduleRecalculation();
  };

  const handleAddSchedule = (index: number, numberDay: number) => {
    if (!selectedSchedule.value?.activities) return;

    const newActivity = createEmptyActivity(numberDay, selectedSchedule.value.day);
    selectedSchedule.value.activities.splice(index + 1, 0, newActivity);

    scheduleRecalculation();
  };

  const handleDeleteSchedule = (activityGlobalIndex: number) => {
    if (!selectedSchedule.value?.activities) return;
    selectedSchedule.value.activities.splice(activityGlobalIndex, 1);
    scheduleRecalculation();
  };

  const copyActivitiesFromSchedule = (payload: string) => {
    if (!selectedSchedule.value?.activities) return;

    const [sourceScheduleId, dayNumberStr] = String(payload).split('_');
    const sourceNumberDay = Number(dayNumberStr);
    if (!sourceScheduleId || Number.isNaN(sourceNumberDay)) return;

    const sourceSchedule = formState.schedules.find(
      (schedule) => String(schedule.id) === sourceScheduleId
    );
    if (!sourceSchedule?.activities) return;

    // Extraer actividades del día específico del schedule origen
    const dayActivitiesToCopy = sourceSchedule.activities
      .filter((activity) => (activity.numberDay ?? 0) === sourceNumberDay)
      .map(
        (activity): ContentActivity => ({
          numberDay: sourceNumberDay,
          day: activity.day,
          duration: activity.duration,
          activityId: activity.activityId,
          calculatedSchedule: null,
        })
      );

    const targetActivities = selectedSchedule.value.activities;
    const existingDayIndex = targetActivities.findIndex(
      (activity) => (activity.numberDay ?? 0) === sourceNumberDay
    );

    if (existingDayIndex === -1) {
      // Insertar en la posición correcta manteniendo el orden por número de día
      const insertIndex = targetActivities.findIndex(
        (activity) => (activity.numberDay ?? 0) > sourceNumberDay
      );
      const insertPosition = insertIndex === -1 ? targetActivities.length : insertIndex;
      targetActivities.splice(insertPosition, 0, ...dayActivitiesToCopy);
    } else {
      // Reemplazar actividades existentes del mismo día
      const nextDayIndex = targetActivities.findIndex(
        (activity, index) =>
          index > existingDayIndex && (activity.numberDay ?? 0) !== sourceNumberDay
      );
      const removeCount =
        nextDayIndex === -1
          ? targetActivities.length - existingDayIndex
          : nextDayIndex - existingDayIndex;
      targetActivities.splice(existingDayIndex, removeCount, ...dayActivitiesToCopy);
    }

    scheduleRecalculation();
  };

  const loadSchedulesFromOperability = () => {
    const operability = getServiceDetailOperability(currentKey || '', currentCode || '');
    if (operability && programDurationAmountDays) {
      const schedules = resolveSchedulesByAmountDays(operability, programDurationAmountDays);
      formState.schedules = schedules as unknown as ContentSchedule[];
    }
  };

  const loadSchedulesGrouped = () => {
    groupedSchedules.value = groupScheduleForActivityDay(
      JSON.parse(JSON.stringify(formState.schedules)) as any
    );
  };

  watch(
    () => formState.schedules.length,
    () => {
      const totalSchedules = formState.schedules.length;
      const maxIndex = Math.ceil(totalSchedules / ITEMS_PER_PAGE) - 1;
      if (currentPageIndex.value > maxIndex) {
        currentPageIndex.value = Math.max(0, maxIndex);
      }
    }
  );

  return {
    // Constantes
    ITEMS_PER_PAGE,
    // Computed
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
    // Handlers
    selectSchedule,
    goToPreviousPage,
    goToNextPage,
    handleDurationChange,
    handleAddDay,
    handleDeleteDay,
    handleAddSchedule,
    handleDeleteSchedule,
    copyActivitiesFromSchedule,
    // Helpers
    getGlobalActivityIndex,
    getCopyScheduleOptions,
    // Inicialización
    loadSchedulesFromOperability,
    loadSchedulesGrouped,
    recalculateSchedules: scheduleRecalculation,
  };
};
