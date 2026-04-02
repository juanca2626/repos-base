import { useQuery } from '@tanstack/vue-query';
import { type Ref } from 'vue';
import { countryCalendarService } from '../services/countryCalendarService';

interface UseCalendarsOptions {
  page: Ref<number>;
  pageSize: Ref<number>;
  search: Ref<string>;
}

export const useCalendarQueries = () => {
  const useCalendars = (options: UseCalendarsOptions) => {
    const { page, pageSize, search } = options;

    return useQuery({
      queryKey: ['calendars', { page, pageSize, search }],
      queryFn: () =>
        countryCalendarService.fetchCalendars({
          page: page.value,
          pageSize: pageSize.value,
          search: search.value,
        }),
      placeholderData: (previousData: any) => previousData,
    });
  };

  return {
    useCalendars,
  };
};
