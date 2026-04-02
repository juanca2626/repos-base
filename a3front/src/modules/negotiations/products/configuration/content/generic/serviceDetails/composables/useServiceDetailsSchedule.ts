import { computed, ref, type ComputedRef } from 'vue';
import type { Schedule } from '@/modules/negotiations/products/configuration/interfaces/shared-service.interface';
import type {
  ScheduleDay,
  Schedule as ScheduleFromUseSchedule,
} from '@/modules/negotiations/products/configuration/interfaces/schedule.interface';
import type { Schedule as ApiSchedule } from '@/modules/negotiations/products/configuration/interfaces/service-details.interface';
import {
  formatOperatingRanges,
  formatAttentionDays,
} from '@/modules/negotiations/products/configuration/content/shared/utils/schedule-formatter.utils';
import { mapSchedulesToApiFormat } from '@/modules/negotiations/products/configuration/content/shared/utils/schedule-mapper.utils';
interface ScheduleData {
  scheduleType: number;
  scheduleGeneral: ScheduleFromUseSchedule[];
  schedule: ScheduleDay[];
}

export const useServiceDetailsSchedule = (
  scheduleData: ComputedRef<ScheduleData | null>,
  serviceDetail: ComputedRef<any | null>
) => {
  const scheduleType = computed(() => scheduleData.value?.scheduleType ?? 1);
  const scheduleGeneral = computed(() => scheduleData.value?.scheduleGeneral ?? []);
  const scheduleDays = computed(() => scheduleData.value?.schedule ?? []);
  const apiSchedules = computed(() => serviceDetail.value?.content?.operability?.schedules ?? []);

  const schedules = ref<Schedule[]>([
    { id: 1, time: '9:00', selected: true },
    { id: 2, time: '14:00', selected: false },
  ]);

  const formattedOperatingRanges = computed(() => {
    if (!scheduleData.value) {
      return 'No disponible';
    }
    return formatOperatingRanges(scheduleType.value, scheduleGeneral.value, scheduleDays.value);
  });

  const formattedAttentionDays = computed(() => {
    if (!scheduleData.value) {
      return 'No disponible';
    }
    return formatAttentionDays(scheduleType.value, scheduleGeneral.value, scheduleDays.value);
  });

  const selectSchedule = (id: number) => {
    schedules.value.forEach((schedule) => {
      schedule.selected = schedule.id === id;
    });
  };

  const getApiSchedules = (): ApiSchedule[] => {
    return mapSchedulesToApiFormat(
      scheduleDays.value,
      scheduleType.value,
      scheduleGeneral.value,
      apiSchedules.value
    );
  };

  const getOperabilityFlags = (): { applyAllDay: boolean; singleTime: boolean } => {
    let operabilityApplyAllDay = false;
    let operabilitySingleTime = false;

    if (scheduleType.value === 1) {
      // Para "Todos los días", usar el valor de scheduleGeneral[0]
      const generalSchedule = scheduleGeneral.value[0];
      if (generalSchedule) {
        operabilityApplyAllDay = Boolean(generalSchedule.twenty_four_hours);
        operabilitySingleTime = Boolean(generalSchedule.single_time);
      }
    } else {
      // Para "Personalizado", verificar si todos los días activos tienen la misma configuración
      const apiSchedules = getApiSchedules();
      const activeSchedules = apiSchedules.filter((s) => s.active);
      if (activeSchedules.length > 0) {
        operabilityApplyAllDay = activeSchedules.every((s) => s.applyAllDay);
        operabilitySingleTime = activeSchedules.every((s) => s.singleTime);
      }
    }

    return {
      applyAllDay: operabilityApplyAllDay,
      singleTime: operabilitySingleTime,
    };
  };

  return {
    scheduleType,
    scheduleGeneral,
    scheduleDays,
    schedules,
    formattedOperatingRanges,
    formattedAttentionDays,
    selectSchedule,
    getApiSchedules,
    getOperabilityFlags,
  };
};
