import type { Activity } from '@/modules/negotiations/products/configuration/interfaces/shared-service.interface';

/**
 * Calcula la duración total desde actividades
 * @param activities Array de actividades con duraciones en formato HH:MM
 * @returns Duración total en formato HH:MM
 */
export const calculateDurationFromActivities = (activities: Activity[]): string => {
  const total = activities.reduce((acc, activity) => {
    const [hours, minutes] = activity.duration.split(':').map(Number);
    return acc + hours * 60 + minutes;
  }, 0);

  const hours = Math.floor(total / 60);
  const minutes = total % 60;
  return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}`;
};

/**
 * Calcula la duración total, priorizando la duración directa si existe y no es '00:00',
 * de lo contrario calcula desde las actividades
 * @param directDuration Duración directa en formato HH:MM
 * @param activities Array de actividades con duraciones en formato HH:MM
 * @returns Duración total en formato HH:MM
 */
export const calculateTotalDuration = (
  directDuration: string | undefined,
  activities: Activity[]
): string => {
  if (directDuration && directDuration !== '00:00') {
    return directDuration;
  }

  return calculateDurationFromActivities(activities);
};
