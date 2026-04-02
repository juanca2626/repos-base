import type { ContentActivity } from '../interfaces';
import {
  timeToMinutes,
  minutesToTime,
} from '@/modules/negotiations/products/configuration/utils/time.utils';

const ONE_MINUTE = 1;

const groupActivitiesByDay = (activities: ContentActivity[]): Map<number, ContentActivity[]> => {
  const byDay = new Map<number, ContentActivity[]>();

  for (const act of activities) {
    const day = act.numberDay ?? 0;
    if (!byDay.has(day)) byDay.set(day, []);
    byDay.get(day)!.push(act);
  }

  return byDay;
};

export const calculateSchedulesForActivities = (
  activities: ContentActivity[],
  startTime: string
): void => {
  const byDay = groupActivitiesByDay(activities);

  byDay.forEach((dayActivities) => {
    let currentTime = startTime;
    let hasNullDuration = false;
    let isFirstActivity = true;

    dayActivities.forEach((activity) => {
      if (hasNullDuration || !activity.duration) {
        activity.calculatedSchedule = null;
        if (!activity.duration) {
          hasNullDuration = true;
        }
      } else {
        const displayStart = isFirstActivity
          ? currentTime
          : minutesToTime(timeToMinutes(currentTime) + ONE_MINUTE);

        const endMinutes = timeToMinutes(currentTime) + timeToMinutes(activity.duration);
        const end = minutesToTime(endMinutes);

        activity.calculatedSchedule = `${displayStart} - ${end}`;
        currentTime = minutesToTime(endMinutes);
        isFirstActivity = false;
      }
    });
  });
};
