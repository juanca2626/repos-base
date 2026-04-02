import type { ActionType } from '@/modules/operations/shared/types/indicator';
import { useFormStore } from '../store/form.store';
import { useNoReportStore } from '../store/noReports.store';

export const useIndicatorActions = () => {
  const formStore = useFormStore();
  const noReportStore = useNoReportStore();

  const validActions: ActionType[] = ['confirmed', 'unconfirmed', 'no_report'];

  const handleIndicatorClick = (action: ActionType) => {
    noReportStore.setNoReport(false);

    if (validActions.includes(action)) {
      formStore.handleClick(action);
    } else {
      console.warn(`Acción no reconocida: ${action}`);
    }
  };

  return {
    handleIndicatorClick,
  };
};
