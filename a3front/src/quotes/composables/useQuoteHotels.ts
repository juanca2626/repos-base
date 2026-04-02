import { storeToRefs } from 'pinia';
import type { Hotel, QuoteHotelsSearchRequest, SearchParameters } from '@/quotes/interfaces';
import { useQuoteHotelsStore } from '@/quotes/store/quote-hotels.store';
import { getHotelsAvailability } from '@/quotes/helpers/get-hotels-availability';
import useLoader from '@/quotes/composables/useLoader';

interface AvailabilityAndPromotions {
  success: boolean;
  type: AvailabilityAndPromotionsEnum;
  search_parameters: SearchParameters;
  hotels: Hotel[];
}

enum AvailabilityAndPromotionsEnum {
  availability = 'availability',
  promotions = 'promotions',
}

export const useQuoteHotels = () => {
  const store = useQuoteHotelsStore();
  const { showIsLoading, closeIsLoading } = useLoader();

  const { hotels, promotions, hotelSelected, searchParameters, hotelToEdit } = storeToRefs(store);

  return {
    // Props
    hotels,
    promotions,
    hotelSelected,
    hotelToEdit,
    searchParameters,

    // Methods
    getHotels: async (form: QuoteHotelsSearchRequest, silent: boolean = false) => {
      if (!silent) showIsLoading();

      try {
        store.unsetHotels();

        // Función auxiliar interna para evitar repetir la lógica de transformación y seguridad
        const fetchWrapper = async (
          payload: QuoteHotelsSearchRequest,
          type: AvailabilityAndPromotionsEnum
        ): Promise<AvailabilityAndPromotions> => {
          try {
            const { success, data } = await getHotelsAvailability(payload);

            // Verificación segura de la estructura: success true Y data tiene al menos un elemento
            const cityData = success && data?.length > 0 ? data[0].city : null;

            return {
              success: !!cityData,
              type: type,
              search_parameters: cityData?.search_parameters ?? {},
              hotels: cityData?.hotels ?? [],
            };
          } catch (e) {
            console.log('Error: ', e);
            return {
              success: false,
              type,
              search_parameters: {},
              hotels: [],
            };
          }
        };

        // Lanzamos ambas peticiones en paralelo de forma limpia
        const responses = await Promise.all([
          fetchWrapper(form, AvailabilityAndPromotionsEnum.availability),
          fetchWrapper(
            {
              ...form,
              type_classes: [1],
              typeclass_id: 'all',
              promotional_rate: 1,
            },
            AvailabilityAndPromotionsEnum.promotions
          ),
        ]);

        // Procesamos resultados
        responses.forEach(({ type, hotels, search_parameters, success }) => {
          if (!success) return; // Si esta parte falló, no actualizamos el store con datos vacíos

          if (type === AvailabilityAndPromotionsEnum.availability) {
            store.setHotels(hotels);
            store.setSearchParameters(search_parameters);
          } else if (type === AvailabilityAndPromotionsEnum.promotions) {
            store.setPromotions(hotels);
          }
        });
      } catch (e) {
        console.error('Error crítico en getHotels:', e);
      } finally {
        if (!silent) closeIsLoading(); // Maca deja de bailar aquí
      }
    },
    getHotelsNoPromotions: async (form: QuoteHotelsSearchRequest, silent: boolean = false) => {
      if (!silent) showIsLoading();
      try {
        store.unsetHotels();

        const promises: Promise<AvailabilityAndPromotions>[] = [];

        promises.push(
          new Promise<AvailabilityAndPromotions>((resolve) => {
            getHotelsAvailability(form).then(({ data: [{ city }] }) => {
              resolve({
                type: AvailabilityAndPromotionsEnum.availability,
                search_parameters: city.search_parameters,
                hotels: city.hotels,
              });
            });
          })
        );

        const responses = await Promise.all(promises);
        responses.forEach(({ type, hotels, search_parameters }) => {
          if (type === AvailabilityAndPromotionsEnum.availability) {
            store.setHotels(hotels);
            store.setSearchParameters(search_parameters);
          }
        });

        if (!silent) closeIsLoading();
      } catch (e) {
        console.log(e);
      } finally {
        if (!silent) closeIsLoading();
      }
    },
    setHotelSelected: async (hotelIdSelected: number, silent: boolean = false) => {
      if (!silent) showIsLoading();
      store.setHotelSelected(hotelIdSelected);
      if (!silent) closeIsLoading();
    },
    unsetHotelSelected: () => store.unsetHotelSelected(),
    unsetHotelSelectedPromotions: () => store.unsetHotelSelectedPromotions(),
  };
};
