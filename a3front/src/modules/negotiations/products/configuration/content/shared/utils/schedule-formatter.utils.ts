import type {
  Schedule,
  ScheduleDay,
} from '@/modules/negotiations/products/configuration/interfaces/schedule.interface';

const DAY_NAME_MAP: Record<string, string> = {
  Lun: 'Lun',
  Mar: 'Mar',
  Mié: 'Mie',
  Jue: 'Jue',
  Vie: 'Vie',
  Sáb: 'Sab',
  Dom: 'Dom',
};

export const formatOperatingRanges = (
  scheduleType: number,
  scheduleGeneral: Schedule[],
  scheduleDays: ScheduleDay[]
): string => {
  if (scheduleType === 1) {
    // Modo "Todos los días" - usar scheduleGeneral
    const generalSchedule = scheduleGeneral[0];
    if (!generalSchedule) {
      return 'No disponible';
    }

    if (generalSchedule.twenty_four_hours) {
      return '24 horas';
    }

    if (generalSchedule.open && generalSchedule.close) {
      return `${generalSchedule.open} - ${generalSchedule.close}`;
    }

    return 'No disponible';
  } else {
    // Modo personalizado - obtener el rango más temprano y más tarde de los días activos
    const activeDays = scheduleDays.filter((day) => day.available_day);
    if (activeDays.length === 0) {
      return 'No disponible';
    }

    const allTimes: string[] = [];
    activeDays.forEach((day) => {
      day.schedules.forEach((schedule) => {
        if (schedule.open) allTimes.push(schedule.open);
        if (schedule.close) allTimes.push(schedule.close);
      });
    });

    if (allTimes.length === 0) {
      return 'No disponible';
    }

    const sortedTimes = allTimes.sort();
    const earliest = sortedTimes[0];
    const latest = sortedTimes[sortedTimes.length - 1];

    return `${earliest} - ${latest}`;
  }
};

export const formatAttentionDays = (
  scheduleType: number,
  scheduleGeneral: Schedule[],
  scheduleDays: ScheduleDay[]
): string => {
  if (scheduleType === 1) {
    // Modo "Todos los días"
    return 'Todos los días';
  } else {
    // Modo personalizado - obtener los días activos
    const activeDays = scheduleDays.filter((day) => day.available_day);
    if (activeDays.length === 0) {
      return 'Ninguno';
    }

    if (activeDays.length === 7) {
      return 'Todos los días';
    }

    // Mapear labels a nombres abreviados
    const dayNames = activeDays.map((day) => DAY_NAME_MAP[day.label] || day.label);

    // Si son días consecutivos, formatear como rango
    if (dayNames.length === 5 && dayNames[0] === 'Lun' && dayNames[4] === 'Vie') {
      return 'Lun - Vie';
    }
    if (dayNames.length === 6 && dayNames[0] === 'Lun' && dayNames[5] === 'Sab') {
      return 'Lun - Sab';
    }

    // Si no, listar los días
    return dayNames.join(', ');
  }
};
