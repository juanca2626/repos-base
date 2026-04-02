import type { ActionType } from '@/modules/operations/shared/types/indicator';
import { useFormStore as useFormServiceManagementStore } from '@operations/modules/service-management/store/form.store';

export const useIndicatorActions = () => {
  const store = useFormServiceManagementStore();

  const handleIndicatorClick = (action: ActionType, type?: any) => {
    if (action === 'upcoming') {
      store.handleClick(action);
    } else {
      store.handleClick(action, type);
    }
  };

  return {
    handleIndicatorClick,
  };
};
