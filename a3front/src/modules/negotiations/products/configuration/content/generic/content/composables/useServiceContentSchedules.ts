import { ref, computed, watch, nextTick } from 'vue';
import { resolveSchedulesByMode } from '@/modules/negotiations/products/configuration/utils/schedule.utils';
import {
  completeTimeValue,
  timeToMinutes,
  minutesToTime,
} from '@/modules/negotiations/products/configuration/utils/time.utils';
import { groupSchedules } from '@/modules/negotiations/products/configuration/utils/groupSchedule.utils';

interface UseServiceContentSchedulesOptions {
  formState: { schedules: any[]; days: any[] };
  currentKey: string;
  currentCode: string;
  getServiceDetailOperability: (key: string, code: string) => any;
}

export const useServiceContentSchedules = (opts: UseServiceContentSchedulesOptions) => {
  const { formState, currentKey, currentCode, getServiceDetailOperability } = opts;

  const ITEMS_PER_PAGE = 4;
  const quickDurationOptions = ['00:30', '00:45', '01:00', '01:30', '01:45', '02:00'];

  const currentPageIndex = ref(0);
  const groupedSchedules = ref<any[]>([]);

  const selectedSchedule = computed(() => {
    return groupedSchedules.value.find((schedule: any) => schedule.selected) || null;
  });

  const isFirstSchedule = computed(() => {
    if (!selectedSchedule.value || groupedSchedules.value.length === 0) return false;
    const firstSchedule = formState.schedules[0];
    return selectedSchedule.value.id === firstSchedule.id;
  });

  const visibleSchedules = computed(() => {
    const start = currentPageIndex.value * ITEMS_PER_PAGE;
    const end = start + ITEMS_PER_PAGE;
    return groupedSchedules.value.slice(start, end);
  });

  const schedulesWithActivities = computed(() =>
    groupedSchedules.value.filter((schedule: any) =>
      schedule.activities?.some(
        (activity: any) => activity.duration != null && activity.activityId != null
      )
    )
  );

  const canGoPrevious = computed(() => currentPageIndex.value > 0);

  const canGoNext = computed(() => {
    const totalSchedules = groupedSchedules.value.length;
    const maxIndex = Math.ceil(totalSchedules / ITEMS_PER_PAGE) - 1;
    return currentPageIndex.value < maxIndex;
  });

  const copyScheduleOptions = computed(() => {
    const currentSelectedId = selectedSchedule.value?.id;

    return groupedSchedules.value
      .filter((schedule: any) => {
        if (schedule.id === currentSelectedId) return false;
        if (!schedule.activities || schedule.activities.length === 0) return false;

        const hasValidActivity = schedule.activities.some((activity: any) => {
          return (
            activity.duration !== null ||
            activity.activityId !== null ||
            activity.calculatedSchedule !== null
          );
        });

        return hasValidActivity;
      })
      .map((schedule: any) => ({
        label: schedule.label,
        value: schedule.id,
      }));
  });

  const calculateSchedules = () => {
    if (!selectedSchedule.value) return;

    const startTime = selectedSchedule.value.start;
    if (!startTime) return;

    const activities = selectedSchedule.value.activities;
    let currentTime = startTime;
    let hasNullDuration = false;

    activities.forEach((activity: any) => {
      if (hasNullDuration || !activity.duration) {
        activity.calculatedSchedule = null;
        if (!activity.duration) {
          hasNullDuration = true;
        }
      } else {
        const displayStart = currentTime;
        const endMinutes = timeToMinutes(currentTime) + timeToMinutes(activity.duration);
        const end = minutesToTime(endMinutes);
        activity.calculatedSchedule = `${displayStart} - ${end}`;
        currentTime = minutesToTime(endMinutes);
      }
    });
  };

  const scheduleRecalculation = () => {
    nextTick(() => {
      calculateSchedules();
    });
  };

  const handleDurationChange = (value: string, activityIndex: number) => {
    if (!selectedSchedule.value) return;

    const completedValue = completeTimeValue(value);
    selectedSchedule.value.activities[activityIndex].duration = completedValue || null;

    calculateSchedules();
  };

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

  const operabilitySummaryText = computed(() => {
    const configuraciones = groupedSchedules.value.length;
    const horariosDeSalida = groupedSchedules.value.reduce(
      (acc: number, s: any) =>
        acc + (s.activities?.filter((a: any) => a.calculatedSchedule)?.length || 0),
      0
    );
    return `${configuraciones} configuraciones con ${horariosDeSalida} horarios de salida`;
  });

  const getScheduleInitData = () => {
    return {
      time: null,
      activityId: null,
      calculatedSchedule: '9:00 / 10:00',
    };
  };

  const getDayInitData = () => {
    return {
      id: null,
      schedules: [
        {
          ...getScheduleInitData(),
        },
      ],
    };
  };

  const handleAddDay = () => {
    formState.days.push(structuredClone(getDayInitData()));
  };

  const handleDeleteDay = (dayIndex: number) => {
    formState.days.splice(dayIndex, 1);
  };

  const handleAddSchedule = (index: number) => {
    if (!selectedSchedule.value) return;

    selectedSchedule.value.activities.splice(index + 1, 0, {
      day: selectedSchedule.value?.label || null,
      duration: null,
      activityId: null,
      calculatedSchedule: null,
    });

    scheduleRecalculation();
  };

  const handleDeleteSchedule = (index: number) => {
    if (!selectedSchedule.value) return;

    selectedSchedule.value.activities.splice(index, 1);

    scheduleRecalculation();
  };

  const copyActivitiesFromSchedule = (sourceScheduleId: number) => {
    if (!selectedSchedule.value) return;

    const sourceSchedule = groupedSchedules.value.find(
      (schedule: any) => schedule.id === sourceScheduleId
    );

    if (!sourceSchedule || !sourceSchedule.activities) return;

    selectedSchedule.value.activities = sourceSchedule.activities.map((activity: any) => ({
      day: activity.day,
      duration: activity.duration,
      activityId: activity.activityId,
      calculatedSchedule: null,
    }));

    scheduleRecalculation();
  };

  const selectSchedule = (id: string) => {
    const group = groupedSchedules.value.find((g: any) => g.id === id);

    groupedSchedules.value.forEach((schedule: any) => {
      schedule.selected = schedule.id === (group ? group.id : id);
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

  const loadSchedulesFromOperability = () => {
    const operability = getServiceDetailOperability(currentKey, currentCode);
    if (operability) {
      const schedules = resolveSchedulesByMode(operability);
      formState.schedules = schedules;
    }
  };

  const loadSchedulesGrouped = () => {
    groupedSchedules.value = groupSchedules(JSON.parse(JSON.stringify(formState.schedules)) as any);
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
    quickDurationOptions,
    // Computed
    selectedSchedule,
    schedulesWithActivities,
    operabilitySummaryText,
    isFirstSchedule,
    visibleSchedules,
    canGoPrevious,
    canGoNext,
    copyScheduleOptions,
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
    // Inicialización
    loadSchedulesFromOperability,
    loadSchedulesGrouped,
    recalculateSchedules: scheduleRecalculation,
  };
};
