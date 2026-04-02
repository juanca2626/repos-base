import type {
  Schedule as ScheduleFromUseSchedule,
  ScheduleDay,
} from '@/modules/negotiations/products/configuration/interfaces/schedule.interface';

export interface ScheduleData {
  scheduleType: number;
  scheduleGeneral: ScheduleFromUseSchedule[];
  schedule: ScheduleDay[];
}

export const validateSingleSchedule = (
  schedule: ScheduleFromUseSchedule,
  twenty_four_hours: boolean,
  single_time: boolean
): boolean => {
  if (twenty_four_hours === true) {
    return true;
  }

  if (single_time === true) {
    return !!(schedule.open && schedule.open.trim() !== '');
  }

  // Si no es single_time, debe tener open y close
  return !!(
    schedule.open &&
    schedule.open.trim() !== '' &&
    schedule.close &&
    schedule.close.trim() !== ''
  );
};

export const validateSchedules = (scheduleData: ScheduleData | null): boolean => {
  if (!scheduleData) {
    return false;
  }

  const { scheduleType, scheduleGeneral, schedule } = scheduleData;

  if (scheduleType === 1) {
    if (!scheduleGeneral || scheduleGeneral.length === 0) {
      return false;
    }

    return scheduleGeneral.every((s) =>
      validateSingleSchedule(s, s.twenty_four_hours || false, s.single_time || false)
    );
  }

  if (scheduleType === 2) {
    if (!schedule || schedule.length === 0) {
      return false;
    }

    const availableDays = schedule.filter((day) => day.available_day === true);

    if (availableDays.length === 0) {
      return false;
    }

    return availableDays.every((day) => {
      if (!day.schedules || day.schedules.length === 0) {
        return false;
      }

      return day.schedules.every((s) =>
        validateSingleSchedule(s, day.twenty_four_hours, day.single_time)
      );
    });
  }

  return true;
};
