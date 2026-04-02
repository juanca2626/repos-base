import { ref } from 'vue';
import type { Activity } from '@/modules/negotiations/products/configuration/interfaces/shared-service.interface';

const getInitialActivity = (): Activity => ({
  duration: '00:00',
  activity: '',
  timeRange: '9:00 / 10:00',
});

export const useServiceDetailsActivities = () => {
  const activities = ref<Activity[]>([getInitialActivity()]);

  const addActivity = () => {
    activities.value.push(getInitialActivity());
  };

  const removeActivity = (index: number) => {
    if (activities.value.length > 1) {
      activities.value.splice(index, 1);
    }
  };

  const updateActivity = (index: number, field: keyof Activity, value: string) => {
    if (activities.value[index]) {
      activities.value[index][field] = value;
    }
  };

  const resetActivities = () => {
    activities.value = [getInitialActivity()];
  };

  return {
    activities,
    addActivity,
    removeActivity,
    updateActivity,
    resetActivities,
  };
};
