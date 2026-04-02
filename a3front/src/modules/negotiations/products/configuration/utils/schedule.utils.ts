import type {
  Operability,
  Schedule,
} from '@/modules/negotiations/products/configuration/interfaces/service-details.interface';

type Day = 'MONDAY' | 'TUESDAY' | 'WEDNESDAY' | 'THURSDAY' | 'FRIDAY' | 'SATURDAY' | 'SUNDAY';

const DAY_TRANSLATIONS: Record<Day, string> = {
  MONDAY: 'Lunes',
  TUESDAY: 'Martes',
  WEDNESDAY: 'Miércoles',
  THURSDAY: 'Jueves',
  FRIDAY: 'Viernes',
  SATURDAY: 'Sábado',
  SUNDAY: 'Domingo',
};

interface ScheduleWithSelected extends Schedule {
  selected: boolean;
  activities: Array<{
    numberDay?: number | null;
    day?: string | null;
    duration: string | null;
    activityId: number | null;
    calculatedSchedule: string | null;
  }>;
}

function translateDayToSpanish(day: string): string {
  return DAY_TRANSLATIONS[day as Day] ?? day;
}

export function resolveSchedulesByMode(config: Operability): ScheduleWithSelected[] {
  const { mode, schedules } = config;

  let result = schedules.filter((s) => s.active);

  if (mode === 'ALL_DAY') {
    result = result.map((schedule, index) => ({
      ...schedule,
      day: `Horario ${index + 1}`,
    }));
  }

  return result.map((schedule, index) => ({
    ...schedule,
    day: translateDayToSpanish(schedule.day),
    selected: index === 0 ? true : false,
    activities: [
      {
        day: schedule.day,
        duration: null,
        activityId: null,
        calculatedSchedule: null,
      },
    ],
  }));
}

export function resolveSchedulesByAmountDays(
  config: Operability,
  amountDays: number
): ScheduleWithSelected[] {
  const { mode, schedules } = config;

  const activeSchedules = schedules.filter((s) => s.active);
  const result: ScheduleWithSelected[] = [];

  if (mode === 'ALL_DAY') {
    const schedules = activeSchedules.map((schedule, index) => {
      const activities = [];
      for (let i = 0; i < amountDays; i++) {
        activities.push({
          numberDay: i + 1,
          day: null,
          duration: null,
          activityId: null,
          calculatedSchedule: null,
        });
      }

      return {
        ...schedule,
        day: `Horario ${index + 1}`,
        selected: index === 0 ? true : false,
        activities: activities,
      };
    });

    result.push(...schedules);
  } else {
    const schedules = activeSchedules.map((schedule, index) => {
      const activities = [];
      for (let i = 0; i < amountDays; i++) {
        activities.push({
          numberDay: i + 1,
          day: null,
          duration: null,
          activityId: null,
          calculatedSchedule: null,
        });
      }

      return {
        ...schedule,
        day: translateDayToSpanish(schedule.day),
        selected: index === 0 ? true : false,
        activities: activities,
      };
    });

    result.push(...schedules);
  }

  return result;
}
