import { useQuote } from '@/quotes/composables/useQuote';
import dayjs from 'dayjs';

/**
 * Checks if a new date conflicts with existing services in the current category.
 * A conflict occurs if there's at least one service on the exact same `date_in`
 * that is not the current service being moved/edited.
 */
export function useDateConflictCheck() {
  const { quoteCategories, selectedCategory } = useQuote();

  const checkDateConflict = (
    newDateStr: string,
    currentServiceId: number | string | null = null
  ) => {
    // Attempt to parse the new date string robustly, it could be DD/MM/YYYY or YYYY-MM-DD
    let parsedNewDate = dayjs(newDateStr, 'YYYY-MM-DD');
    if (!parsedNewDate.isValid()) {
      parsedNewDate = dayjs(newDateStr, 'DD/MM/YYYY');
    }

    if (!parsedNewDate.isValid() || !quoteCategories.value) {
      return { hasConflict: false, conflictingServices: [], newDate: newDateStr };
    }

    const newDateFormatted = parsedNewDate.format('YYYY-MM-DD');
    const conflictingServices: Array<{ id: number; name: string; type: string; dateIn: string }> =
      [];

    // Find the currently selected category
    const cat = quoteCategories.value.find((c: any) => c.type_class_id === selectedCategory.value);

    if (cat && cat.services) {
      // Flatten services to handle GroupedServices and nested extensions
      const allServices: any[] = [];
      cat.services.forEach((s: any) => {
        console.log('Service: ', s.service);
        if (s.type === 'group_extension' && s.extensions) {
          s.extensions.forEach((ext: any) => {
            allServices.push(ext.service || ext);
          });
        } else if (s.type === 'group_header' && s.group) {
          s.group.forEach((g: any) => {
            allServices.push(g.service || g);
          });
        } else {
          allServices.push(s.service || s);
        }
      });

      console.log(
        `Checking conflict for ${newDateFormatted}. Total services in category: ${allServices.length}`
      );

      console.log('SERVICES: ', allServices);

      allServices.forEach((actualService: any) => {
        // Skip if it is the current service we are checking against (using loose comparison for safety)
        if (currentServiceId && String(actualService.id) === String(currentServiceId)) {
          return;
        }

        if (actualService.date_in) {
          // Robust parsing of the service date
          let parsedServiceDate = dayjs(actualService.date_in, 'YYYY-MM-DD', true);
          if (!parsedServiceDate.isValid()) {
            parsedServiceDate = dayjs(actualService.date_in, 'DD/MM/YYYY', true);
          }

          if (parsedServiceDate.isValid()) {
            const serviceDateFormatted = parsedServiceDate.format('YYYY-MM-DD');

            if (serviceDateFormatted === newDateFormatted) {
              // Get a display name for the conflict
              let name = 'Service';
              if (actualService.hotel) name = actualService.hotel.name;
              else if (actualService.service)
                name = actualService.service.name || actualService.service.aurora_code || '';
              else name = actualService.name || actualService.aurora_code || actualService.id;

              conflictingServices.push({
                id: actualService.id,
                name: name,
                type: actualService.type,
                dateIn: actualService.date_in,
              });
            }
          }
        }
      });
    }

    return {
      hasConflict: conflictingServices.length > 0,
      conflictingServices,
      newDate: newDateStr, // Keep original format for display if needed
    };
  };

  return {
    checkDateConflict,
  };
}
