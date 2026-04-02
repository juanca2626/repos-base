import { useMutation, useQueryClient } from '@tanstack/vue-query';
import {
  countryCalendarService,
  type CreateCalendarPayload,
} from '../services/countryCalendarService';

export const useCalendarMutations = () => {
  const queryClient = useQueryClient();

  const createCalendar = useMutation({
    mutationFn: (payload: CreateCalendarPayload) => countryCalendarService.createCalendar(payload),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['calendars'] });
    },
  });

  // Future mutations can be added here (update, delete, etc.)

  return {
    createCalendar,
  };
};
