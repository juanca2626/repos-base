export type Day = 'Lunes' | 'Martes' | 'Miércoles' | 'Jueves' | 'Viernes' | 'Sábado' | 'Domingo';

export interface Activity {
  numberDay: number;
  day: string | null;
  duration: number | null;
  activityId: string | null;
  calculatedSchedule: unknown | null;
}

export interface RawSchedule {
  id: string;
  day: Day;
  start: string;
  end: string | null;
  applyAllDay: boolean;
  singleTime: boolean;
  active: boolean;
  selected: boolean;
  activities: Activity[];
}

export interface GroupedSchedule {
  id: string;
  label: string;
  start: string;
  end: string | null;
  ids: string[];
  selected: boolean;
  applyAllDay: boolean;
  singleTime: boolean;
  activities: Activity[];
}

const DAY_INDEX: Record<Day, number> = {
  Lunes: 0,
  Martes: 1,
  Miércoles: 2,
  Jueves: 3,
  Viernes: 4,
  Sábado: 5,
  Domingo: 6,
};

const DAY_SHORT: Record<Day, string> = {
  Lunes: 'Lun',
  Martes: 'Mar',
  Miércoles: 'Mié',
  Jueves: 'Jue',
  Viernes: 'Vie',
  Sábado: 'Sáb',
  Domingo: 'Dom',
};

function hashString(input: string): string {
  let hash = 0;
  for (let i = 0; i < input.length; i++) {
    hash = (hash << 5) - hash + input.charCodeAt(i);
    hash |= 0;
  }
  return Math.abs(hash).toString(36);
}

function buildGroupSignature(slice: RawSchedule[]): string {
  const first = slice[0];
  const last = slice[slice.length - 1];

  return [
    first.start,
    first.end ?? '',
    first.applyAllDay,
    first.singleTime,
    first.day,
    last.day,
    slice.map((s) => s.id).join(','),
  ].join('|');
}

function mergeActivitiesByNumberDay(schedules: RawSchedule[]): Activity[] {
  const map = new Map<string, Activity>();

  for (let i = 0; i < schedules.length; i++) {
    const activities = schedules[i].activities;

    for (let j = 0; j < activities.length; j++) {
      const act = activities[j];

      let code = act.numberDay + '-' + act.activityId;

      if (act.activityId == null) {
        code = act.numberDay + '-null';
        if (!map.has(code)) {
          map.set(code, act);
        }
      } else {
        if (!map.has(code)) {
          map.set(code, act);
        }
      }
    }
  }

  return Array.from(map.values()).sort((a, b) => a.numberDay - b.numberDay);
}

export function groupScheduleForActivityDay(schedules: RawSchedule[]): GroupedSchedule[] {
  if (!schedules || schedules.length === 0) return [];

  const timeGroups = new Map<string, RawSchedule[]>();

  for (let i = 0; i < schedules.length; i++) {
    const schedule = schedules[i];

    const key = [
      schedule.start,
      schedule.end ?? '',
      schedule.applyAllDay,
      schedule.singleTime,
    ].join('#');

    let bucket = timeGroups.get(key);

    if (!bucket) {
      bucket = [];
      timeGroups.set(key, bucket);
    }

    bucket.push(schedule);
  }

  const result: GroupedSchedule[] = [];

  timeGroups.forEach((items) => {
    items.sort((a, b) => DAY_INDEX[a.day] - DAY_INDEX[b.day]);

    let startIdx = 0;

    for (let i = 1; i <= items.length; i++) {
      const prev = items[i - 1];
      const curr = items[i];

      if (!curr || DAY_INDEX[curr.day] !== DAY_INDEX[prev.day] + 1) {
        const slice = items.slice(startIdx, i);

        const first = slice[0];
        const last = slice[slice.length - 1];

        const signature = buildGroupSignature(slice);

        result.push({
          id: hashString(signature),
          label:
            slice.length === 1
              ? DAY_SHORT[first.day]
              : `${DAY_SHORT[first.day]}-${DAY_SHORT[last.day]}`,
          start: first.start,
          end: first.end,
          ids: slice.map((s) => s.id),
          selected: slice[0].selected,
          applyAllDay: slice.every((s) => s.applyAllDay),
          singleTime: slice.every((s) => s.singleTime),
          activities: mergeActivitiesByNumberDay(slice),
        });

        startIdx = i;
      }
    }
  });

  return result;
}
