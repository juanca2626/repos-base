export type Day = 'Lunes' | 'Martes' | 'Miércoles' | 'Jueves' | 'Viernes' | 'Sábado' | 'Domingo';

export interface Activity {
  day: string;
  activityId: string | null;
  duration: number | null;
  calculatedSchedule: any | null;
}

export interface RawSchedule {
  id: string;
  day: Day;
  start: string;
  end: string;
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
  end: string;
  ids: string[];
  applyAllDay: boolean;
  singleTime: boolean;
  selected: boolean;
  activities: any[];
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

  return [first.start, first.end, first.day, last.day, slice.map((s) => s.id).join(',')].join('|');
}

function groupActivities(schedules: RawSchedule[]): Activity[] {
  // console.log("schedules", schedules[0].activities);
  const map = new Map<string, Activity>();
  let firstNullActivity: Activity | null = null;

  for (let i = 0; i < schedules.length; i++) {
    const schedule = schedules[i];
    const activities: Activity[] = schedule.activities;

    for (let j = 0; j < activities.length; j++) {
      const act = activities[j];
      // Caso NULL
      if (act.activityId === null) {
        if (!firstNullActivity) {
          firstNullActivity = act;
        }
        continue;
      }

      // Caso con ID válido
      if (!map.has(act.activityId)) {
        //   console.log("act", act);
        map.set(act.activityId, act);
      }
    }
  }

  if (map.size > 0) {
    return Array.from(map.values());
  }

  return firstNullActivity ? [firstNullActivity] : [];
}

export function groupSchedules(schedules: RawSchedule[]): GroupedSchedule[] {
  const timeGroups = new Map<string, RawSchedule[]>();

  // O(n) – agrupación por rango horario
  for (let i = 0; i < schedules.length; i++) {
    const item = schedules[i];
    const key = item.start + '-' + item.end;

    let bucket = timeGroups.get(key);
    if (!bucket) {
      bucket = [];
      timeGroups.set(key, bucket);
    }
    bucket.push(item);
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
        const activities = groupActivities(slice);

        result.push({
          id: hashString(signature),
          label:
            slice.length === 1
              ? DAY_SHORT[first.day]
              : `${DAY_SHORT[first.day]}-${DAY_SHORT[last.day]}`,
          start: first.start,
          end: first.end,
          ids: slice.map((s) => s.id),

          applyAllDay: slice.every((s) => s.applyAllDay),
          singleTime: slice.every((s) => s.singleTime),
          selected: slice[0].selected,
          activities,
        });

        startIdx = i;
      }
    }
  });

  return result;
}
