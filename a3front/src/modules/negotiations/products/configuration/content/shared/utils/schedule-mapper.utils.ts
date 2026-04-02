import type {
  ScheduleDay,
  Schedule as ScheduleFromUseSchedule,
} from '@/modules/negotiations/products/configuration/interfaces/schedule.interface';
import type { Schedule as ApiSchedule } from '@/modules/negotiations/products/configuration/interfaces/service-details.interface';

export const mapDayLabelToApiDay = (label: string): string => {
  const dayMap: Record<string, string> = {
    Lun: 'MONDAY',
    Mar: 'TUESDAY',
    Mié: 'WEDNESDAY',
    Jue: 'THURSDAY',
    Vie: 'FRIDAY',
    Sáb: 'SATURDAY',
    Dom: 'SUNDAY',
  };
  return dayMap[label] || label.toUpperCase();
};

export const mapApiDayToLabel = (apiDay: string): string => {
  const dayMap: Record<string, string> = {
    MONDAY: 'Lun',
    TUESDAY: 'Mar',
    WEDNESDAY: 'Mié',
    THURSDAY: 'Jue',
    FRIDAY: 'Vie',
    SATURDAY: 'Sáb',
    SUNDAY: 'Dom',
  };
  return dayMap[apiDay] || apiDay;
};

export const mapSchedulesToApiFormat = (
  scheduleDays: ScheduleDay[],
  scheduleType: number,
  scheduleGeneral: ScheduleFromUseSchedule[],
  apiSchedulesInput: ApiSchedule[]
): ApiSchedule[] => {
  const apiSchedules: ApiSchedule[] = [];

  // Si es tipo 1 (Todos los días), usar scheduleGeneral
  if (scheduleType === 1) {
    const generalSchedule = scheduleGeneral || [
      {
        id: null,
        open: '',
        close: '',
        twenty_four_hours: false,
        single_time: false,
      },
    ];

    const days = ['MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY', 'SATURDAY', 'SUNDAY'];

    if (apiSchedulesInput.length > 0) {
      generalSchedule.forEach((item, index) => {
        days.forEach((day) => {
          const idsSchedules = apiSchedulesInput
            .filter((item) => item.day === day)
            .map((item) => item.id);
          apiSchedules.push({
            id: idsSchedules[index] || null,
            day: day,
            start: item.open || null,
            end: item.close || null,
            applyAllDay: Boolean(item.twenty_four_hours),
            singleTime: Boolean(item.single_time),
            active: true,
          });
        });
      });
    } else {
      days.forEach((day) => {
        generalSchedule.forEach((schedule) => {
          apiSchedules.push({
            id: schedule.id || null,
            day,
            start: schedule.open || null,
            end: schedule.close || null,
            applyAllDay: Boolean(schedule.twenty_four_hours),
            singleTime: Boolean(schedule.single_time),
            active: true,
          });
        });
      });
    }
  } else {
    // Si es tipo 2 (Personalizado), usar scheduleDays
    scheduleDays.forEach((day) => {
      const isApplyAllDay = Boolean(day.twenty_four_hours);
      const isSingleTime = Boolean(day.single_time);

      day.schedules.forEach((schedule) => {
        apiSchedules.push({
          id: schedule.id || null,
          day: mapDayLabelToApiDay(day.label),
          start: isApplyAllDay ? null : schedule.open || null,
          end: isApplyAllDay ? null : schedule.close || null,
          applyAllDay: isApplyAllDay,
          singleTime: isSingleTime,
          active: day.available_day || false,
        });
      });
    });
  }

  return apiSchedules;
};

export const mapApiSchedulesToScheduleData = (
  apiSchedules: ApiSchedule[],
  mode: string
): {
  scheduleType: number;
  scheduleGeneral: ScheduleFromUseSchedule[];
  schedule: ScheduleDay[];
} => {
  // Si mode es "ALL_DAYS", es tipo 1 (Todos los días)
  const scheduleType = mode === 'ALL_DAYS' ? 1 : 2;

  if (scheduleType === 1) {
    // Modo "Todos los días" - usar el primer schedule como general
    const schedulesByDay = apiSchedules.filter((s) => s.day === 'MONDAY');
    const scheduleGeneral: ScheduleFromUseSchedule[] = schedulesByDay.map((s) => ({
      id: s.id || null,
      open: s.start || '',
      close: s.end || '',
      twenty_four_hours: s.applyAllDay || false,
      single_time: s.singleTime || false,
    }));

    const scheduleDays: ScheduleDay[] = [
      {
        label: 'Lun',
        available_day: true,
        schedules: [
          {
            id: null,
            open: '',
            close: '',
            twenty_four_hours: false,
            single_time: false,
          },
        ],
        twenty_four_hours: false,
        single_time: false,
      },
      {
        label: 'Mar',
        available_day: true,
        schedules: [
          {
            id: null,
            open: '',
            close: '',
            twenty_four_hours: false,
            single_time: false,
          },
        ],
        twenty_four_hours: false,
        single_time: false,
      },
      {
        label: 'Mié',
        available_day: true,
        schedules: [
          {
            id: null,
            open: '',
            close: '',
            twenty_four_hours: false,
            single_time: false,
          },
        ],
        twenty_four_hours: false,
        single_time: false,
      },
      {
        label: 'Jue',
        available_day: true,
        schedules: [
          {
            id: null,
            open: '',
            close: '',
            twenty_four_hours: false,
            single_time: false,
          },
        ],
        twenty_four_hours: false,
        single_time: false,
      },
      {
        label: 'Vie',
        available_day: true,
        schedules: [
          {
            id: null,
            open: '',
            close: '',
            twenty_four_hours: false,
            single_time: false,
          },
        ],
        twenty_four_hours: false,
        single_time: false,
      },
      {
        label: 'Sáb',
        available_day: true,
        schedules: [
          {
            id: null,
            open: '',
            close: '',
            twenty_four_hours: false,
            single_time: false,
          },
        ],
        twenty_four_hours: false,
        single_time: false,
      },
      {
        label: 'Dom',
        available_day: true,
        schedules: [
          {
            id: null,
            open: '',
            close: '',
            twenty_four_hours: false,
            single_time: false,
          },
        ],
        twenty_four_hours: false,
        single_time: false,
      },
    ];

    return {
      scheduleType: 1,
      scheduleGeneral,
      schedule: scheduleDays,
    };
  } else {
    const scheduleGeneral: ScheduleFromUseSchedule[] = [
      {
        id: null,
        open: '',
        close: '',
        twenty_four_hours: false,
        single_time: false,
      },
    ];
    // Agrupar los horarios por día
    const scheduleDays: ScheduleDay[] = Object.values(
      apiSchedules.reduce((acc, apiSchedule) => {
        const key = apiSchedule.day;

        if (!(key in acc)) {
          (acc as Record<string, ScheduleDay>)[key] = {
            label: mapApiDayToLabel(key),
            available_day: apiSchedule.active,
            schedules: [],
            twenty_four_hours: apiSchedule.applyAllDay,
            single_time: apiSchedule.singleTime,
          };
        }
        (acc as Record<string, ScheduleDay>)[key].schedules.push({
          id: apiSchedule.id || null,
          open: apiSchedule.start || '',
          close: apiSchedule.end || '',
          twenty_four_hours: apiSchedule.applyAllDay,
          single_time: apiSchedule.singleTime,
        });

        return acc;
      }, {})
    );

    return {
      scheduleType: 2,
      scheduleGeneral: scheduleGeneral,
      schedule: scheduleDays,
    };
  }
};
