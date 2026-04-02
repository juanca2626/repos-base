import { computed, ref } from 'vue';
import { storeToRefs } from 'pinia';
import { useI18n } from 'vue-i18n';

import quotesApi from '@/quotes/api/quotesApi';

import quotesA3Api from '@/quotes/api/quotesA3Api';
import { useQuoteStore } from '@/quotes/store/quote.store';
import { useOccupationStore } from '@/quotes/store/occupation.store';
import { useSocketsStore } from '@/stores/global';
import { getUserCode, getUserName, getUserClientId } from '@/utils/auth';

import dayjs from 'dayjs';
import customParseFormat from 'dayjs/plugin/customParseFormat';
dayjs.extend(customParseFormat);
import { DateTime } from 'luxon';

import { getHotelsAvailability } from '@/quotes/helpers/get-hotels-availability';

// import { getLang } from "@/quotes/helpers/get-lang";
import useLoader from '@/quotes/composables/useLoader';
import useQuoteTranslations from '@/quotes/composables/useQuoteTranslations';
import useNotification from '@/quotes/composables/useNotification';

import type { QuoteServiceReplaceRequest } from '@/quotes/interfaces/quote-service-replace.request';
import type { QuoteServiceUpdateRequest } from '@/quotes/interfaces/quote-service-update.request';
import type { QuoteExtensionUpdateRequest } from '@/quotes/interfaces/quote-extension-update.request';
import type { QuoteServiceHotelsGenerateOccupationRequest } from '@/quotes/interfaces/quote-service-hotels-generate-occupation.request';
import type {
  HotelsAddRoom,
  HotelsAddRooms,
  QuoteServiceHotelsRoomTypeQuantityUpdateResponse,
} from '@/quotes/interfaces/quote-service-hotels-room-type-quantity-update.response';
import type { QuoteServiceHotelAddRoomsRequest } from '@/quotes/interfaces/quote-service-hotel-add-rooms.request';
import type {
  GroupedServices,
  Hotel,
  OpenQuoteCategory,
  Passenger,
  PassengerAgeChild,
  PassengersRequest,
  PassengersResponse,
  Person,
  QuoteCategoryCopyRequest,
  QuoteCategoryCopyResponse,
  QuoteDistributionUpdate,
  QuoteHotelsSearchRequest,
  QuoteRange,
  QuoteReservastionRequest,
  QuoteReservationResponse,
  QuoteResponse,
  QuoteSaveRequest,
  QuoteService,
  QuoteServiceAddRequest,
  QuoteServiceHotelsGenerateOccupationResponse,
  QuoteServiceHotelsOccupation,
  RemindersRequest,
  RemindersResponse,
  ReservationsRequest,
  ReservationsResponse,
  StatementsRequest,
  StatementsResponse,
} from '@/quotes/interfaces';
import { getUrlAuroraBack, getUrlAuroraQuoteBack, getUserId, getUserType } from '@/utils/auth';

import useQuotePrice from '@/quotes/composables/useQuotePrice';

const isAddingService = ref<boolean | undefined>(undefined);
const isDeletingService = ref<boolean | undefined>(undefined);
const reservationErrors = ref<any>([]);

const getQuoteByStatus = async (
  quoteStatus: number,
  lang: string,
  clientId?: number
): Promise<QuoteResponse> => {
  let url = `/api/quote/byUserStatus/${quoteStatus}?lang=${lang}`;
  if (clientId) {
    url += `&client_id=${clientId}`;
  }
  const { data } = await quotesA3Api.get<{ data: QuoteResponse[] }>(url);
  return data.data[0];
};

// Update Totals = quoteMe A2
const updateTotals = async (request: {
  quote_id: number;
  client_id: number;
  category_id: number;
}): Promise<void> => {
  await quotesA3Api.put(`/api/quote/me`, request);
};

// Quote Service add
const quoteCategoryCopy = async (request: QuoteCategoryCopyRequest): Promise<void> => {
  await quotesA3Api.post<QuoteCategoryCopyResponse[]>(`/api/quote/categories/copy`, request);
};

// Quote Service add
const addService = async (quoteId: number, request: QuoteServiceAddRequest): Promise<void> => {
  await quotesA3Api.post<QuoteResponse[]>(`/api/quote/${quoteId}/categories/service`, request);
};

// Quote Service mutators
const updateServiceDateIn = async (form: QuoteServiceUpdateRequest): Promise<void> => {
  await quotesA3Api.put<QuoteResponse[]>(`/api/quote/update/date_in/services`, form);
};
const updateServiceNights = async (form: QuoteServiceUpdateRequest): Promise<void> => {
  await quotesA3Api.put<QuoteResponse[]>(`/api/quote/nights/service`, form);
};
const updateExtension = async (form: QuoteExtensionUpdateRequest): Promise<void> => {
  await quotesA3Api.put<QuoteResponse[]>(`/api/quote/extension-update`, form);
};

const updateOnRequestMultiple = async (form: {
  services_update: {
    quote_service_id: number;
    on_request: number;
  }[];
}): Promise<void> => {
  await quotesA3Api.post<QuoteResponse[]>(`/api/quote/categories/update/on_request_multiple`, form);
};

// Quote Service replace
const replaceService = async (
  serviceId: number,
  request: QuoteServiceReplaceRequest
): Promise<void> => {
  await quotesA3Api.post<QuoteResponse[]>(`/api/quote/service/${serviceId}/rooms/replace`, request);
};

// Quote mutators
const updateQuoteName = async (quote_id: number, name: string): Promise<void> => {
  await quotesA3Api.put(`/api/quote/update/name`, { quote_id, name });
};

// Quote mutators
const saveAsQuote = async (quote_id: number, name: string): Promise<void> => {
  await quotesA3Api.post(`/api/quote/save_as`, {
    quote_id,
    new_name_quote: name,
  });
};

// Quote mutators
const closeQuotation = async (quote_id: number): Promise<void> => {
  await quotesA3Api.delete(`/api/quote/${quote_id}/forcefullyDestroy`);
};

// Quote mutators
const deleteQuotation = async (quote_id: number): Promise<void> => {
  await quotesA3Api.delete(`/api/quote/${quote_id}/forcefullyDestroy`);
};

// Quote mutators
// const deleteQuotationReports = async (quote_id: number): Promise<void> => {
//   await quotesA3Api.delete(`/api/quotes/${quote_id}`);
// };

const updateQuoteDateIn = async (quote_id: number, date_in: string): Promise<void> => {
  await quotesA3Api.put(`/api/quote/update/date_in`, {
    quote_id,
    date_in,
    lang: 'en',
  });
};

const updatePeople = async (form: {
  quote_id: number;
  passengers: Passenger[];
  people: Person;
}): Promise<void> => {
  return await quotesA3Api.put(`/api/quote/people`, form);
};

const deleteServices = async (quoteId: number, services: QuoteService[]): Promise<void> => {
  await quotesA3Api.post<QuoteResponse[]>(`/api/quotes/${quoteId}/services`, {
    services: services,
  });
};

const deletePassenger = (passenger_id: number): void => {
  quotesA3Api.delete(`/api/quote/passengers/${passenger_id}`);
};

const saveQuoteRanges = async (request: {
  quote_id: number;
  ranges: QuoteRange[];
}): Promise<void> => {
  await quotesA3Api.post(`/api/quote/ranges/save`, request);
};

const updateQuoteAssignment = async (request: { quote_id: number }): Promise<void> => {
  await quotesA3Api.post(`/api/quote/update-quote-assignment`, request);
};

const updateChildAge = async (age: PassengerAgeChild): Promise<void> => {
  await quotesA3Api.put(`/api/quote/age_child`, { age_child: age });
};

const updateQuoteCategory = async (request: {
  category_id: number | string;
  quote_id: number;
  operation: 'new' | 'delete';
  source_category_id?: number | string; // Optional: ID of category to copy from, or 'programming' to use programming data
}): Promise<void> => {
  await quotesA3Api.post(`api/quote/create_or_delete/category`, request);
};

const addQuoteExtension = async (request: {
  quote_id: number;
  category_ids: number[];
  extension_id: number;
  service_type_id: number;
  type_class_id: number;
  extension_date: Date | string;
}): Promise<void> => {
  return await quotesA3Api.post(`api/quote_client/extension`, request);
};

// Occupation mutators
const generateHotelsOccupationDistribution = async (
  form: QuoteServiceHotelsGenerateOccupationRequest
): Promise<QuoteServiceHotelsOccupation[]> => {
  const { data } = await quotesA3Api.get<QuoteServiceHotelsGenerateOccupationResponse>(
    `/api/quote/service/occupation_paseengers_hotel`,
    {
      params: form,
    }
  );

  return data.quoteDistributions;
};

const updateOccupationDistribution = async (form: QuoteDistributionUpdate): Promise<void> => {
  await quotesA3Api.post(`/api/quote/service/occupation_paseengers_hotel`, form);
};

// Occupation mutators
const generateHotelsOccupationDistributionClient = async (
  form: QuoteServiceHotelsGenerateOccupationRequest
): Promise<QuoteServiceHotelsOccupation[]> => {
  const { data } = await quotesA3Api.get<QuoteServiceHotelsGenerateOccupationResponse>(
    `/api/quote/service/occupation_paseengers_hotel_client`,
    {
      params: form,
    }
  );

  return data;
};

// const updateOccupationDistributionClient = async (form: QuoteDistributionUpdate): Promise<void> => {
//   await quotesA3Api.post(`/api/quote/service/occupation_paseengers_hotel_client`, form);
// };

const updateServiceHotelsRoomTypeQuantity = async (occupationHotel: {
  simple: string;
  double: number;
  triple: string;
  double_child: number;
  triple_child: number;
  quote_id: number;
}): Promise<QuoteServiceHotelsRoomTypeQuantityUpdateResponse> => {
  const { data } = await quotesA3Api.put<QuoteServiceHotelsRoomTypeQuantityUpdateResponse>(
    `/api/quote/service/occupation_hotel/general`,
    occupationHotel
  );

  return data;
};

const addQuoteServiceHotelAddRooms = async (
  quote_service_id: number,
  request: QuoteServiceHotelAddRoomsRequest
) => {
  await quotesA3Api.post(`/api/quote/service/${quote_service_id}/rooms/addFromHeader`, request);
};

// Passengers room mutators
const updateRoomOccupation = async (request: {
  passengers: Passenger[];
  service_id: number;
  quote_id: number;
}) => {
  await quotesA3Api.post(`/api/quote/service/passenger`, request);
};

// Passengers room mutators
const updateServiceServicePassengers = async (request: {
  service_id: number;
  adult: number;
  child: number;
  quote_id: number;
}) => {
  await quotesA3Api.post(`/api/quote/update/services/passengers`, request);
};

// Hours service
const updateServiceHours = async (
  id: number,
  request: {
    hour_in: string;
  }
) => {
  await quotesA3Api.put(`/api/quote/services/${id}/hour_in`, request);
};

// Save quotation
const saveQuotation = async (quoteId: number, request: QuoteSaveRequest) => {
  await quotesA3Api.put(`/api/quotes/${quoteId}`, request);
};

// Save quotation
const saveQuotationNew = async (request: {}) => {
  await quotesA3Api.post(`/api/quotes`, request);
};
const updateServiceOptionalState = async (request: {
  optional: boolean | number;
  quote_service_id: number;
}): Promise<void> => {
  await quotesA3Api.put(`/api/quote/optional`, request);
};

interface ResponsePromise {
  rooms_add: HotelsAddRoom[];
  availability: Hotel[];
}

const fetchHotelAvailability = (
  hotelsAddRooms: HotelsAddRooms,
  lang: string
): Promise<ResponsePromise>[] => {
  const promises: Promise<ResponsePromise>[] = [];

  for (const entry of Object.entries(hotelsAddRooms)) {
    const roomsAdd = entry[1];
    const hotel = entry[1][0];
    promises.push(
      new Promise<ResponsePromise>((resolve) => {
        getHotelsAvailability({
          // buscamos los hoteles disponibles para poder agregar la habitacion
          hotels_id: [hotel.hotel_id],
          date_from: hotel.date_in,
          date_to: hotel.date_out,
          quantity_rooms: 1,
          quantity_persons_rooms: [],
          typeclass_id: hotel.typeclass_id,
          destiny: {
            code: hotel.destiny_code,
            label: hotel.destiny_label,
          },
          lang: lang,
          set_markup: 0,
          zero_rates: true,
        }).then((response) => {
          resolve({
            rooms_add: roomsAdd,
            availability: response.data[0].city.hotels,
          });
        });
      })
    );
  }

  return promises;
};

const storeServiceHotel = (quote_id: number, result: ResponsePromise[], lang: string) => {
  const responsePromise: Promise<void>[] = [];
  for (const { rooms_add, availability } of result) {
    const roomsAvailable = availability.length > 0 ? availability[0].rooms : [];
    for (const room_new of rooms_add) {
      let shouldBreak = false;
      for (const room of roomsAvailable) {
        if ([1, 2, 3].includes(room.room_type_id) && room_new.occupation == room.occupation) {
          for (const rate of room.rates) {
            if (rate.rates_plans_type_id == 2) {
              responsePromise.push(
                addQuoteServiceHotelAddRooms(room_new.quote_service_id, {
                  quote_id: quote_id,
                  quote_service_id: room_new.quote_service_id,
                  rate_plan_room_ids: [],
                  lang: lang,
                  rate_plan_rooms_choose: [
                    {
                      rate_plan_room_id: rate.rateId,
                      choose: true,
                      occupation: room_new.occupation,
                      on_request: 1,
                    },
                  ],
                  cant: room_new.cant,
                  quote_service: room_new.quote_service ? room_new.quote_service : '',
                })
              );
              shouldBreak = true;

              break;
            }
          }
        }

        if (shouldBreak) {
          break;
        }
      }
    }
  }

  return responsePromise;
};

const processQuoteResponse = (data: QuoteResponse): QuoteResponse => {
  data.categories.forEach((category: OpenQuoteCategory) => {
    if (category.services.length) {
      const servicesGrouped: QuoteService[] = (category.services as QuoteService[]).filter(
        (service: QuoteService) => {
          return (
            service.type === 'group_header' ||
            service.type === 'service' ||
            service.type === 'flight'
          );
        }
      );

      const results = servicesGrouped
        .map((s: QuoteService) => {
          const group: QuoteService[] = (category.services as QuoteService[]).filter(
            (ss: QuoteService) =>
              ss.type === 'hotel' &&
              ss.grouped_code === s.grouped_code &&
              ss.total_accommodations > 0
          );

          if (s.type === 'group_header') {
            s.service_rooms = s.service_rooms.filter((sr) =>
              group.some(
                (ss: QuoteService) =>
                  sr.rate_plan_room.room.room_type_id ===
                  ss.service_rooms[0].rate_plan_room.room.room_type_id
              )
            );
          }

          let date_order = s.date_in_format;
          if (s.type == 'group_header') {
            date_order += ' 23:59:00';
          } else {
            if (s.hour_in) {
              date_order += ' ' + s.hour_in;
            } else {
              date_order += ' 00:00:00';
            }
          }
          return {
            selected: false,
            day: null,
            date: s.date_in_format,
            hours: s.hour_in,
            date_order: date_order,
            extensions: [],
            extension_id: s.new_extension_id,
            quote_service_id: s.id,
            type: s.type,
            service: s,
            group: group,
          };
        })
        .filter((s: GroupedServices) => {
          if (s.type === 'group_header') {
            return s.group.length;
          }

          return true;
        });

      results.sort(function (a, b) {
        var c = new Date(a.date_order);
        var d = new Date(b.date_order);
        return c - d;
      });

      let serviceExtensionAra = [];
      results.forEach((row) => {
        if (row.extension_id) {
          // && !serviceExtensionAra.includes(row.extension_id)
          let sw = false;
          serviceExtensionAra.forEach((s, index) => {
            let r = s.extensions.filter((e) => {
              return e.extension_id == row.extension_id;
            });

            if (r.length > 0) {
              sw = true;
              serviceExtensionAra[index].extensions.push(row);
            }
          });

          if (sw == false) {
            serviceExtensionAra.push({
              selected: false,
              day: null,
              date: row.date,
              extensions: [row],
              extension_id: row.extension_id,
              quote_service_id: row.quote_service_id,
              type: 'group_extension',
              service: row,
              group: null,
            });
          }
        } else {
          serviceExtensionAra.push(row);
        }
      });

      // console.log(serviceExtensionAra);

      category.services = serviceExtensionAra;
    }
  });

  return data;
};

const generatePassenger = async (passengers: Passenger[], people: Person): Promise<void> => {
  if (passengers.length === 0) {
    passengers = [];
  }

  const countPassengers = (type: 'ADL' | 'CHD'): number => {
    return passengers.filter((passenger) => passenger.type === type).length;
  };

  const addPassengers = (count: number, type: 'ADL' | 'CHD'): void => {
    const diff = count - countPassengers(type);
    for (let i = 0; i < diff; i++) {
      passengers.push({
        id: null,
        first_name: '',
        last_name: '',
        gender: '',
        birthday: null,
        doctype_iso: '',
        document_number: null,
        country_iso: '',
        email: null,
        phone: null,
        notes: null,
        type,
      });
    }
  };

  const removePassengers = (count: number, type: 'ADL' | 'CHD'): void => {
    let deletedCount = countPassengers(type) - count;
    for (let i = passengers.length; i < 0 && deletedCount > 0; i--) {
      if (passengers[i].type === type && passengers[i].id !== null) {
        deletePassenger(Number(passengers[i].id));
        passengers.splice(i, 1);
        deletedCount--;
      }
    }
  };

  addPassengers(people.adults, 'ADL');
  addPassengers(people.child, 'CHD');
  removePassengers(people.adults, 'ADL');
  removePassengers(people.child, 'CHD');
};

// Save passenger
const save_passengers = async (request: PassengersRequest): Promise<void> => {
  await quotesApi.post<PassengersResponse[]>(`/api/save_passenger`, request);
};

// Pre Reserver Quote
const reservation_quote = async (
  request: QuoteReservastionRequest
): Promise<QuoteReservationResponse> => {
  const { data } = await quotesApi.post<QuoteReservationResponse>(
    `/services/reservations/quote`,
    request
  );
  return data;
};

// Reserver Quote
const reservation = async (request: ReservationsRequest): Promise<ReservationsResponse> => {
  const { data } = await quotesApi.post<ReservationsResponse>(
    `/services/hotels/reservation/add`,
    request
  );
  return data;
};

// Statement Quote
const statement = async (
  quoteId: number,
  request: StatementsRequest
): Promise<StatementsResponse> => {
  const { data } = await quotesA3Api.post<StatementsResponse>(
    `/api/quote/${quoteId}/statements`,
    request
  );
  return data;
};

// Save reminders
const reminders = async (
  reservationId: number,
  request: RemindersRequest
): Promise<RemindersResponse> => {
  const { data } = await quotesApi.post<RemindersResponse>(
    `/api/reservations/${reservationId}/reminders`,
    request
  );
  return data;
};

// Delete reminders
const delete_reminders = async (reservationId: number): Promise<RemindersResponse> => {
  const { data } = await quotesApi.delete<RemindersResponse>(
    `/api/reservations/${reservationId}/reminders`
  );
  return data;
};

export const useQuote = () => {
  const store = useQuoteStore();
  const storeOccupation = useOccupationStore();
  const socketsStore = useSocketsStore();
  const {
    quote,
    quoteNew,
    quoteServiceTypeId,
    quoteLanguageId,
    quoteResponse,
    view,
    page,
    serviceSelected,
    serviceSearch,
    selectedCategory,
    currentReportQuote,
    downloadItinerary,
    downloadSkeletonUse,
    quotePricePassenger,
    quotePriceRanger,
    quoteRowExtentions,
    itemsExtensiones,
    selectedHotelDetails,
    openItemService,
    alertCategory,
    processing,
    showDesign,
  } = storeToRefs(store);

  const { t } = useI18n();

  const { showIsLoading, closeIsLoading, isLoading } = useLoader();
  const { getLang } = useQuoteTranslations();
  const { showErrorNotification } = useNotification();
  const { getQuotePricePassenger, getQuotePriceRanger } = useQuotePrice();

  const deleteServiceSelected = () => {
    store.unsetServiceSelected();
  };

  const getQuote = async (
    activeGroupedCode?: string | null,
    loadRates: boolean = true,
    silent: boolean = false
  ) => {
    // Obtener la URL base actual
    const urlBase = window.location.origin;
    // Obtener la URL completa actual
    const currentUrl = window.location.href;
    // Crear una expresión regular para reconocer el patrón {url_base}/files/*
    const regex = new RegExp(`^${urlBase}/files/.*`);
    // Verificar si la URL actual coincide con el patrón
    if (regex.test(currentUrl)) {
      closeIsLoading();
      return;
    }

    // Only show global loading if not in silent mode
    if (!silent) {
      showIsLoading();
    }

    quoteNew.value.name = '';
    quoteNew.value.date_in = DateTime.now().plus({ days: 1 }).toFormat('yyyy-MM-dd');
    quoteNew.value.estimated_travel_date = DateTime.now().plus({ days: 2 }).toFormat('yyyy-MM-dd');
    quoteNew.value.estimated_travel_date;
    quoteNew.value.adults = 2;
    quoteNew.value.child = 0;
    quoteNew.value.infant = 0;
    quoteNew.value.quoteChildAges = [];
    quoteNew.value.quoteServiceTypeId = '';
    quoteNew.value.quoteCategoriesSelected = [];

    try {
      const activeCategory = selectedCategory.value;

      let response = await getQuoteByStatus(2, getLang(), getUserClientId());

      if (response && response.logs) {
        if (response.logs[0].type === 'editing_package') {
          await saveQuotation(response.id, {
            categories: response.categories.map((c) => c.type_class_id),
            client_id: getUserClientId(),
            date: response.date_in,
            date_estimated: response.estimated_travel_date,
            name: response.name,
            notes: [],
            operation: response.operation,
            passengers: response.passengers,
            people: response.people[0],
            service_type_id: response.service_type_id,
            languageId: null,
          });

          response = await getQuoteByStatus(2, getLang(), getUserClientId());
        }
        response.reference_code = '';
        response.flights = [];
        response.reservation = [];
        response.statement = [];

        store.setQuoteResponse(response);

        const resp = processQuoteResponse(response);

        if (resp.accommodation) {
          if (
            resp.accommodation.single == 0 &&
            resp.accommodation.double == 0 &&
            resp.accommodation.triple == 0
          ) {
            storeOccupation.showWindowOccupation();
          }
        } else {
          storeOccupation.showWindowOccupation();
        }

        if (activeGroupedCode != null) {
          const show = true;
          resp.categories.forEach((c) => {
            (c.services as GroupedServices[]).forEach((s) => {
              if (activeGroupedCode === s.service?.grouped_code) {
                s.service.grouped_show = show;
              }
            });
          });
        }

        store.setQuote(resp);
        store.setSelectedCategory(
          activeCategory ? activeCategory : resp.categories[0].type_class_id
        );
        quoteServiceTypeId.value = resp.service_type_id;
        quoteLanguageId.value = resp.language_id;
        quotePricePassenger.value = {};
        quotePriceRanger.value = {};

        //agrupa por fecha y le coloca que dia pertenece del itinerario
        resp.categories.forEach((c) => {
          const dayDinamic: string | Date[] = [];

          (c.services as GroupedServices[]).forEach((s) => {
            // if(s.type == 'service'){
            if (!dayDinamic[s.service.date_in_format]) {
              dayDinamic[s.service.date_in_format] = Array();
            }
            dayDinamic[s.service.date_in_format].push(s.service.id);

            if (s.extension_id) {
              (s.extensions as GroupedServices[]).forEach((e) => {
                if (!dayDinamic[e.service.date_in_format]) {
                  dayDinamic[e.service.date_in_format] = Array();
                }
                dayDinamic[e.service.date_in_format].push(e.service.id + '_' + s.extension_id);
              });
            }

            // }
          });

          const positions: string | Date[] = [];
          Object.entries(dayDinamic).forEach(function (x) {
            positions.push(x[1]);
          });
          (c.services as GroupedServices[]).forEach((s) => {
            positions.forEach((e, index) => {
              if (e.includes(s.quote_service_id)) {
                s.day = index + 1;
              }
            });

            if (s.extension_id) {
              (s.extensions as GroupedServices[]).forEach((ex) => {
                positions.forEach((e, index) => {
                  if (e.includes(ex.quote_service_id + '_' + s.extension_id)) {
                    ex.day = index;
                  }
                });
              });
            }
          });
        });

        if (loadRates == true) {
          // if(resp.categories[0].services.length >0){
          if (response.operation == 'passengers') {
            await getQuotePricePassenger(silent);
          } else {
            await getQuotePriceRanger(silent);
          }
          // }
        }

        // Only close global loading if not in silent mode
        if (!silent) {
          closeIsLoading();
        }
      } else {
        if (!silent) {
          closeIsLoading();
        }
      }
    } catch (e: any) {
      console.log('ERROR: ', e.data.message);
      if (e.data && e.data.message) {
        showErrorNotification(e.data.message ?? 'Error al obtener la cotización', true);
      }

      const { closeIsLoading } = useLoader();

      if (!silent) {
        closeIsLoading();
      }
    }
  };

  const getQuoteAccommodation = async (
    single: number,
    double: number,
    triple: number,
    adults: number,
    child: number
  ): Promise<QuoteServiceHotelsOccupation[]> => {
    try {
      showIsLoading();
      const hotelsOccupationsDistributions = await generateHotelsOccupationDistribution({
        single: single,
        double: double,
        triple: triple,
        adults: adults, //quote.value.people[0].adults,
        child: child, //quote.value.people[0].child,
        quote_id: quote.value.id,
      });
      closeIsLoading();
      return hotelsOccupationsDistributions;
    } catch (e) {
      console.log(e);
      closeIsLoading();
    }

    return [];
  };

  const updateQuoteAccommodation = async (
    distributionPassengers: QuoteServiceHotelsOccupation[],
    single: number,
    double: number,
    triple: number,
    updatePeopleHow: number = 1,
    silent: boolean = false
  ) => {
    if (!silent) showIsLoading();
    try {
      await updateOccupationDistribution({
        distribution_passengers: distributionPassengers,
        quote_id: quote.value.id,
      });

      const response = await updateServiceHotelsRoomTypeQuantity({
        simple: single.toString(),
        double: double,
        triple: triple.toString(),
        double_child: quote.value.accommodation.double_child,
        triple_child: quote.value.accommodation.triple_child,
        quote_id: quote.value.id,
      });

      const promisesAvailability = fetchHotelAvailability(response.hotels_add_rooms, getLang());

      if (promisesAvailability.length > 0) {
        const promisesResult = await Promise.all(promisesAvailability);

        const promisesServiceHotel = storeServiceHotel(quote.value.id, promisesResult, getLang());

        if (promisesServiceHotel.length > 0) {
          await Promise.all(promisesServiceHotel);
        }
      }

      if (quote.value.operation == 'passengers' && updatePeopleHow == 1) {
        await updatePeople({
          quote_id: quote.value.id,
          passengers: quote.value.passengers,
          people: {
            adults: quote.value.people[0].adults,
            child: quote.value.people[0].child,
            ages_child: quote.value.age_child,
          },
        });
      }
      // getQuote()
    } catch (e) {
      console.log(e);
      if (!silent) closeIsLoading();
    }
  };

  const assignQuoteOccupation = async (
    single: number,
    double: number,
    triple: number,
    silent: boolean = false
  ) => {
    if (!silent) showIsLoading();
    try {
      const response = await updateServiceHotelsRoomTypeQuantity({
        simple: single.toString(),
        double: double,
        triple: triple.toString(),
        double_child: quote.value.accommodation.double_child,
        triple_child: quote.value.accommodation.triple_child,
        quote_id: quote.value.id,
      });

      const promisesAvailability = fetchHotelAvailability(response.hotels_add_rooms, getLang());

      if (promisesAvailability.length > 0) {
        const promisesResult = await Promise.all(promisesAvailability);

        const promisesServiceHotel = storeServiceHotel(quote.value.id, promisesResult, getLang());

        if (promisesServiceHotel.length > 0) {
          await Promise.all(promisesServiceHotel);
        }
      }
      getQuote(null, true, silent);
    } catch (e) {
      console.log(e);
      if (!silent) closeIsLoading();
    }
  };

  const getQuoteAccommodationClient = async (
    single: number,
    double: number,
    triple: number,
    adults: number,
    child: number
  ): Promise<QuoteServiceHotelsOccupation[]> => {
    try {
      showIsLoading();
      const hotelsOccupationsDistributions = await generateHotelsOccupationDistributionClient({
        single: single,
        double: double,
        triple: triple,
        adults: adults, //quote.value.people[0].adults,
        child: child, //quote.value.people[0].child,
        quote_id: quote.value.id,
      });
      closeIsLoading();
      return hotelsOccupationsDistributions;
    } catch (e) {
      console.log(e);
      closeIsLoading();
    }

    return [];
  };

  const verify_itinerary_errors = () => {
    let have_errors = 0;
    quote.value.categories.forEach((c) => {
      if (c.type_class_id == selectedCategory.value) {
        c.services.forEach((s) => {
          if (s.type == 'group_extension') {
            s.extensions.forEach((g) => {
              have_errors = have_errors + count_error_valdations(g);
            });
          } else {
            have_errors = have_errors + count_error_valdations(s);
          }
        });
      }
    });
    // console.log(have_errors)
    if (have_errors > 0 && quote.value.operation === 'passengers') {
      return true;
    }

    if (quote.value.operation === 'ranges') {
      return false;
    }

    return have_errors > 0 ? true : false;
  };

  const registerEventAlertGTM = (message: string) => {
    if (getUserType() == '4') {
      window.dataLayer.push({
        event: 'alert',
        message: message,
      });
    }
  };

  const registerEventDownloadGTM = (file_category: string) => {
    if (getUserType() == '4') {
      window.dataLayer.push({
        event: 'download',
        file_category: file_category,
      });
    }
  };

  const count_error_valdations = (s) => {
    let have_errors = 0;
    const errorsMessages: string[] = [];

    if (s.type == 'service') {
      if (
        s.service.validations.length > 0 &&
        !s.service.locked &&
        s.service.total_accommodations > 0
      ) {
        let have_validation_true = 0;
        s.service.validations.forEach((v) => {
          if (v.validation) {
            errorsMessages.push(v.error_gtm);
            have_validation_true++;
          }
        });
        if (have_validation_true > 0) {
          have_errors++;
        }
      }
    }

    if (s.type == 'group_header') {
      s.group.forEach((g) => {
        if (g.validations.length > 0 && !g.locked && g.total_accommodations > 0) {
          let have_validation_true = 0;
          g.validations.forEach((v) => {
            if (v.validation) {
              errorsMessages.push(v.error_gtm);
              have_validation_true++;
            }
          });
          if (have_validation_true > 0) {
            have_errors++;
          }
        }

        if (g.type === 'hotel' && !g.locked && g.total_accommodations > 0) {
          if (verify_type_rooms(g) === true) {
            have_errors++;
          }
        }
      });
    }

    if (have_errors > 0) {
      const uniqueMessages: string[] = [...new Set(errorsMessages)];
      uniqueMessages.forEach((message) => {
        registerEventAlertGTM(message);
      });
      console.log(uniqueMessages);
    }

    return have_errors;
  };

  const verify_type_rooms = (hotel) => {
    if (quote.value.operation === 'ranges') {
      return false;
    }

    if (hotel.single > 0) {
      let validate_sgl = false;
      hotel.service_rooms.forEach((s_r) => {
        if (s_r.rate_plan_room != null && s_r.rate_plan_room.room.room_type.occupation === 1) {
          validate_sgl = true;
        }
      });
      if (!validate_sgl) {
        return true;
      }
    }
    if (hotel.double > 0) {
      let validate_dbl = false;
      hotel.service_rooms.forEach((s_r) => {
        if (s_r.rate_plan_room != null && s_r.rate_plan_room.room.room_type.occupation === 2) {
          validate_dbl = true;
        }
      });
      if (!validate_dbl) {
        return true;
      }
    }
    if (hotel.triple > 0) {
      let validate_tpl = false;
      hotel.service_rooms.forEach((s_r) => {
        if (s_r.rate_plan_room != null && s_r.rate_plan_room.room.room_type.occupation === 3) {
          validate_tpl = true;
        }
      });
      if (!validate_tpl) {
        return true;
      }
    }
    // console.log(hotel);
    return false;
  };

  const validate_reservation = async ({ loading = true }) => {
    try {
      if (loading) {
        showIsLoading();
      }

      reservationErrors.value = [];
      const passengers = quote.value.passengers.map((passenger) => {
        // let phone = '';
        // if(passenger.phone_code ){
        //     phone = '+' + passenger.phone_code
        // }

        // if(passenger.phone){
        //     phone = phone + '' + passenger.phone
        // }else{
        //     phone = '';
        // }

        return {
          nombres: passenger.first_name,
          apellidos: passenger.last_name,
          sexo: passenger.gender,
          fecnac:
            passenger.birthday && passenger.birthday != '0000-00-00'
              ? passenger.birthday_selected.format('DD/MM/YYYY')
              : '',
          nrodoc: passenger.document_number,
          tipdoc: passenger.doctype_iso,
          nacion: passenger.country_iso,
          correo: passenger.email,
          celula: passenger.phone,
          phone_code: passenger.phone_code,
          observ: passenger.notes,
          resali: passenger.dietary_restrictions,
          resmed: passenger.medical_restrictions,
          tipo: passenger.type,
          address: passenger.address,
          city_ifx_iso: passenger.city_ifx_iso,
          is_direct_client: passenger.is_direct_client,
          document_url: passenger.document_url,
          id: passenger.id,
          quote_id: passenger.quote_id,
        };
      });

      await save_passengers({
        passengers: passengers,
        repeat: 0,
        modePassenger: 2,
        file: quote.value.id,
        type: 'quote',
        paxs: quote.value.passengers.length,
      });

      await saveQuotation(quote.value.id, {
        categories: quote.value.categories.map((c) => c.type_class_id),
        client_id: getUserClientId(),
        date: quote.value.date_in,
        date_estimated: quote.value.estimated_travel_date,
        name: quote.value.name,
        notes: [],
        operation: quote.value.operation,
        passengers: quote.value.passengers,
        people: quote.value.people[0],
        service_type_id: quoteServiceTypeId.value,
        languageId: quoteLanguageId.value,
        reference_code: quote.value.reference_code,
      });

      const quote_category = quote.value.categories.find((e) => {
        return e.type_class_id == selectedCategory.value;
      });

      if (quote_category == null) {
        closeIsLoading();
        return false;
      }

      const reservationQuote = await reservation_quote({
        client_id: getUserClientId(),
        lang: getLang(),
        quote_id: quote.value.id,
        quote_id_original: quote_id_original(),
        reference: '', //this.file.file_reference,
        file_code: '', //this.file.file_code,
        quote_category_id: quote_category.id,
        services_optionals: [],
      });

      if (!reservationQuote.success) {
        reservationErrors.value = reservationQuote.errors ?? [];
        closeIsLoading();
        return false;
      }

      closeIsLoading();
    } catch (e: unknown) {
      if (e instanceof Error) {
        console.log(e);
      }
      closeIsLoading();
      return false;
    }
  };

  const save_reservation = async () => {
    try {
      showIsLoading();
      const passengers = quote.value.passengers.map((passenger) => {
        // let phone = '';
        // if(passenger.phone_code ){
        //     phone = '+' + passenger.phone_code
        // }

        // if(passenger.phone){
        //     phone = phone + '' + passenger.phone
        // }else{
        //     phone = '';
        // }

        return {
          nombres: passenger.first_name,
          apellidos: passenger.last_name,
          sexo: passenger.gender,
          fecnac:
            passenger.birthday && passenger.birthday != '0000-00-00'
              ? passenger.birthday_selected.format('DD/MM/YYYY')
              : '',
          nrodoc: passenger.document_number,
          tipdoc: passenger.doctype_iso,
          nacion: passenger.country_iso,
          correo: passenger.email,
          celula: passenger.phone,
          phone_code: passenger.phone_code,
          observ: passenger.notes,
          resali: passenger.dietary_restrictions,
          resmed: passenger.medical_restrictions,
          tipo: passenger.type,
          address: passenger.address,
          city_ifx_iso: passenger.city_ifx_iso,
          is_direct_client: passenger.is_direct_client,
          document_url: passenger.document_url,
          id: passenger.id,
          quote_id: passenger.quote_id,
        };
      });

      await save_passengers({
        passengers: passengers,
        repeat: 0,
        modePassenger: 2,
        file: quote.value.id,
        type: 'quote',
        paxs: quote.value.passengers.length,
      });

      await saveQuotation(quote.value.id, {
        categories: quote.value.categories.map((c) => c.type_class_id),
        client_id: getUserClientId(),
        date: quote.value.date_in,
        date_estimated: quote.value.estimated_travel_date,
        name: quote.value.name,
        notes: [],
        operation: quote.value.operation,
        passengers: quote.value.passengers,
        people: quote.value.people[0],
        service_type_id: quoteServiceTypeId.value,
        languageId: quoteLanguageId.value,
        reference_code: quote.value.reference_code,
      });

      const quote_category = quote.value.categories.find((e) => {
        return e.type_class_id == selectedCategory.value;
      });

      if (quote_category == null) {
        closeIsLoading();
        return false;
      }

      let reservations_flights: Array = [];

      quote.value.flights.forEach((element) => {
        let adult = 0;
        let child = 0;
        let ages_child: Array = [];
        element.passengers.forEach((elem) => {
          const passenger = quote.value.passengers.find((c) => {
            return c.id === elem;
          });
          // console.log(passenger);
          if (passenger.type == 'ADL') {
            adult = adult + 1;
          }
          if (passenger.type == 'CHD') {
            child = child + 1;
            ages_child.push({
              child: 1,
              age: passenger.age_child,
            });
          }
        });

        reservations_flights.push({
          service_id: 0,
          client_id: getUserClientId(),
          origin: element.origin ? element.origin.value : '',
          destiny: element.destiny ? element.destiny.value : '',
          code_flight: element.code_flight,
          lang: getLang(),
          date: element.date.format('YYYY-MM-DD'),
          services_id: [0],
          quantity_persons: {
            adults: adult,
            child: child,
            ages_child: ages_child,
          },
        });
      });

      const reservationQuote = await reservation_quote({
        client_id: getUserClientId(),
        lang: getLang(),
        quote_id: quote.value.id,
        quote_id_original: quote_id_original(),
        reference: '', //this.file.file_reference,
        file_code: '', //this.file.file_code,
        quote_category_id: quote_category.id,
        services_optionals: [],
      });

      if (!reservationQuote.success) {
        closeIsLoading();
        return false;
      }

      if (reservations_flights.length > 0) {
        reservationQuote.response.reservations_flights = reservations_flights;
      }

      reservationQuote.response.entity = 'Quote';
      reservationQuote.response.object_id = quote_id_original();
      reservationQuote.response.type_class_id = selectedCategory.value;
      reservationQuote.response.reference = ''; //this.file.file_reference

      const reservation_response = await reservation(reservationQuote.response);

      if (!reservation_response.success) {
        closeIsLoading();
        return false;
      }

      quote.value.reservation = reservation_response.data;

      const statement_response = await statement(quote.value.id, {
        type_class_id: selectedCategory.value,
        client_id: getUserClientId(),
      });

      quote.value.statement = statement_response;
      closeIsLoading();
    } catch (e: unknown) {
      if (e instanceof Error) {
        console.log(e);
      }
      closeIsLoading();
      return false;
    }
  };

  const save_reservation_reminders = async (days: number, email_alt = '', date) => {
    showIsLoading();
    try {
      const reminders_response = await reminders(quote.value.reservation.id, {
        days_before: days,
        email: quote.value.statement.client.email,
        email_alt: email_alt,
        date: date,
      });
      console.log(reminders_response);

      closeIsLoading();
    } catch (e: unknown) {
      if (e instanceof Error) {
        console.log(e);
      }
      closeIsLoading();
      return false;
    }
  };

  const delete_reservation_reminders = async () => {
    showIsLoading();
    try {
      const reminders_response = await delete_reminders(quote.value.reservation.id);
      console.log(reminders_response);
      closeIsLoading();
    } catch (e: unknown) {
      if (e instanceof Error) {
        console.log(e);
      }
      closeIsLoading();
      return false;
    }
  };

  const quote_id_original = () => {
    let id: number = 0;
    if (quote.value.logs) {
      quote.value.logs.forEach((log) => {
        if (log.type == 'editing_quote') {
          id = log.object_id;
        }
      });
    }

    return id;
  };

  const setQuoteHotelRoomAccommodation = async (service: QuoteService, passengers: Passenger[]) => {
    showIsLoading();
    try {
      await updateRoomOccupation({
        passengers: passengers,
        service_id: service.id,
        quote_id: quote.value.id,
      });
      getQuote(service.grouped_code);
    } catch (e) {
      console.log(e);
      closeIsLoading();
    }
  };

  return {
    // Props
    isAddingService,
    isDeletingService,
    isLoading,
    quote,
    quoteNew,
    quoteServiceTypeId,
    quoteLanguageId,
    selectedCategory,
    selectedHotelDetails,
    openItemService,
    alertCategory,
    currentReportQuote,
    downloadItinerary,
    downloadSkeletonUse,
    quoteRowExtentions,
    serviceSearch,
    serviceSelected,
    itemsExtensiones,
    view,
    page,
    // Methods
    getQuote,
    getQuoteAccommodation,
    updateQuoteAccommodation,
    assignQuoteOccupation,
    getQuoteAccommodationClient,
    quotePricePassenger,
    quotePriceRanger,
    verify_itinerary_errors,
    validate_reservation,
    save_reservation,
    save_reservation_reminders,
    delete_reservation_reminders,
    deleteServiceSelected,
    setQuoteHotelRoomAccommodation,
    reservationErrors,
    orderServices: async (event: { oldIndex: number; newIndex: number }) => {
      const cat = quote.value.categories.find((c) => {
        return c.type_class_id === selectedCategory.value;
      });

      if (cat !== undefined) {
        const array_move = function (arr: QuoteService[], old_index: number, new_index: number) {
          if (
            old_index >= 0 &&
            old_index < arr.length &&
            new_index >= 0 &&
            new_index < arr.length &&
            old_index !== new_index // Asegurarse de que el índice actual y el nuevo índice sean diferentes
          ) {
            const elementToMove = arr.splice(old_index, 1)[0];
            arr.splice(new_index, 0, elementToMove);
          }
          return arr;
        };

        cat.services = array_move(cat.services as QuoteService[], event.oldIndex, event.newIndex);
      }
    },
    updateServicesOrder: async () => {
      // showIsLoading();
      try {
        const cat = quote.value.categories.find((c) => {
          return c.type_class_id === selectedCategory.value;
        });

        if (cat !== undefined) {
          /*
          const services: GroupedServices[] = cat.services as GroupedServices[];
          const servicesList: QuoteService[] = [];

          services.forEach((s) => {
            if (s.type === 'group_extension') {
              s.extensions.forEach((ex) => {
                servicesList.push(ex.service);

                if (ex.type === 'group_header') {
                  (ex.group! as QuoteService[]).forEach((gsex) => {
                    servicesList.push(gsex);
                  });
                }
              });
            } else {
              servicesList.push(s.service);
              if (s.type === 'group_header') {
                (s.group! as QuoteService[]).forEach((gs) => {
                  servicesList.push(gs);
                });
              }
            }
          });
          */

          const services: GroupedServices[] = cat.services as GroupedServices[];
          const servicesList: any[] = []; // Usamos any para manejar la estructura con el nuevo order

          // 1. Aplanamos la lista de servicios
          services.forEach((s) => {
            if (s.type === 'group_extension') {
              s.extensions.forEach((ex) => {
                servicesList.push(ex.service);
                if (ex.type === 'group_header') {
                  (ex.group! as QuoteService[]).forEach((gsex) => servicesList.push(gsex));
                }
              });
            } else {
              servicesList.push(s.service);
              if (s.type === 'group_header') {
                (s.group! as QuoteService[]).forEach((gs) => servicesList.push(gs));
              }
            }
          });

          // 2. CRITICO: Asignar el nuevo orden basado en la posición actual del array
          const payload = servicesList.map((service, index) => {
            return {
              ...service,
              order: index + 1,
            };
          });

          await quotesA3Api.post(`/api/quote/update_order_and_date/services`, {
            services: payload,
            quote_id: quote.value.id,
          });

          // await getQuote(null, false, false);
          broadcastQuoteUpdate('quote_updated', 'quote.notification.service_order_updated');
        } else {
          // closeIsLoading();
        }
      } catch (e) {
        console.log(e);
        // closeIsLoading();
      }
    },
    exportar: async (data) => {
      try {
        let quoteExport;

        if (data) {
          quoteExport = data;
        } else {
          quoteExport = quote.value;
        }

        if (quoteExport.operation === 'ranges') {
          registerEventDownloadGTM('quote_excel_ranges');
          const link =
            getUrlAuroraQuoteBack() +
            'quote/' +
            quoteExport.id +
            '/export/ranges?lang=' +
            getLang() +
            '&client_id=' +
            getUserClientId() +
            '&user_id=' +
            getUserId() +
            '&user_type_id=' +
            getUserType();
          const a = document.createElement('a');
          a.target = '_blank';
          a.href = link;
          a.click();
        }
        if (quoteExport.operation === 'passengers') {
          registerEventDownloadGTM('quote_excel_passengers');
          showIsLoading();
          await updateTotals({
            quote_id: quoteExport.id,
            client_id: getUserClientId(),
            category_id: selectedCategory.value,
          });
          closeIsLoading();
          // console.log('Entra pasajero descarga');
          const link =
            getUrlAuroraQuoteBack() +
            'quote/' +
            quoteExport.id +
            '/export/passengers?lang=' +
            getLang() +
            '&client_id=' +
            getUserClientId() +
            '&user_id=' +
            getUserId() +
            '&user_type_id=' +
            getUserType();
          const a = document.createElement('a');
          a.target = '_blank';
          a.href = link;
          a.click();
        }
      } catch (e) {
        console.log(e);
        closeIsLoading();
      }
    },
    downloadQuoteSkeleton: async (data) => {
      try {
        let quoteExport;

        if (data) {
          quoteExport = data;
        } else {
          quoteExport = quote.value;
        }

        const cat = quoteExport.categories.find((c) => {
          if (selectedCategory.value) {
            return c.type_class_id === selectedCategory.value;
          } else {
            return c.type_class_id;
          }
        });

        showIsLoading();

        let result = await quotesA3Api.get(
          'api/quote/' +
            quoteExport.id +
            '/category/' +
            cat.id +
            '/skeleton?lang=' +
            downloadSkeletonUse.value.selectedIdLanguage +
            '&client_id=' +
            getUserClientId() +
            '&use_header=' +
            downloadSkeletonUse.value.addPorta +
            '&refPax=' +
            getUserName(),
          { responseType: 'blob' }
        );

        registerEventDownloadGTM('quote_skeleton');

        closeIsLoading();

        return result;
      } catch (e) {
        console.log(e);
        closeIsLoading();
      }
    },
    downloadQuoteItinerary: async (data) => {
      try {
        let quoteExport;

        if (data) {
          quoteExport = data;
        } else {
          quoteExport = quote.value;
        }

        console.log(quoteExport);
        console.log(selectedCategory.value);

        const cat = quoteExport.categories.find((c) => {
          if (selectedCategory.value) {
            return c.type_class_id === selectedCategory.value;
          } else {
            return c.type_class_id;
          }
        });

        showIsLoading();

        let txtCliente = '';

        if (downloadItinerary.value.addPorta) {
          txtCliente =
            '&cover_client_logo=' +
            'cliente-' +
            downloadItinerary.value.destinosPortada +
            '&urlPortadaLogo=' +
            downloadItinerary.value.urlPortada;
        }

        let typePortada = downloadItinerary.value.addPorta
          ? downloadItinerary.value.typePortada
          : 4;

        let result = await quotesA3Api.get(
          'api/quote/' +
            quoteExport.id +
            '/category/' +
            cat.id +
            '/itinerary?lang=' +
            downloadItinerary.value.selectedIdLanguage +
            '&client_id=' +
            getUserClientId() +
            '&use_header=' +
            downloadItinerary.value.addPorta +
            '&cover=' +
            downloadItinerary.value.destinosPortada +
            '&refPax=' +
            getUserName() +
            '&client_logo=' +
            typePortada +
            txtCliente,
          { responseType: 'blob' }
        );

        registerEventDownloadGTM('quote_itinerary');
        closeIsLoading();

        return result;
      } catch (e) {
        console.log(e);
        closeIsLoading();
      }
    },

    setComboPortada: async (portada, selectLang, logoWidth, nameServicio) => {
      showIsLoading();
      try {
        const clientLogo = ['1', '2', '3'];

        const idClientLogo = clientLogo.includes(logoWidth);

        if (idClientLogo) {
          let result = await quotesA3Api.get('api/quote/imageCreate', {
            params: {
              clienteId: getUserClientId(),
              portada: portada,
              portadaName: nameServicio,
              estado: logoWidth,
              refPax: getUserName(),
              lang: selectLang,
              nameCliente: getUserName(),
            },
          });

          closeIsLoading();
          result.baseUrl = getUrlAuroraQuoteBack();
          return result;
        }
      } catch (e) {
        console.log(e);
        closeIsLoading();
      }
    },

    downloadPassengerExcel: async () => {
      registerEventDownloadGTM('quote_excel');
      const a = document.createElement('a');
      a.target = '_blank';
      a.href =
        getUrlAuroraBack() +
        'passengers-export?total=' +
        quote.value.passengers.length +
        '&lang=' +
        getLang();
      a.click();
    },
    uploadPassengerExcel: async (file) => {
      showIsLoading();
      try {
        const formData = new FormData();
        formData.append('file', file);

        const { data } = await quotesA3Api.post(`/api/passengers-import`, formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
          },
        });

        closeIsLoading();
        return data;
      } catch (e) {
        console.log(e);
        closeIsLoading();
      }
    },
    updatePrince: async () => {
      showIsLoading();
      try {
        const process_quote_service: number[] = [];
        const promises = [];

        const services: QuoteService[] =
          quoteResponse.value.categories
            .find((c) => c.type_class_id === selectedCategory.value)
            ?.services.filter((service) => service.type === 'group_header')
            ?.flatMap((header) => header.group) || [];

        for (let i = 0; i < services.length; i++) {
          if (!services[i].locked) {
            const rate_plan_room_id =
              services[i].service_rooms && services[i].service_rooms.length > 0
                ? services[i].service_rooms[0].rate_plan_room_id
                : null;
            if (rate_plan_room_id === null) {
              continue;
            }
            const data: QuoteHotelsSearchRequest = {
              hotels_id: [services[i].object_id],
              rate_plan_room_search: [rate_plan_room_id],
              date_from: dayjs(services[i].date_in, 'DD/MM/YYYY').format('YYYY-MM-DD'),
              date_to: dayjs(services[i].date_out, 'DD/MM/YYYY').format('YYYY-MM-DD'),
              quantity_rooms: 1,
              quantity_persons_rooms: [
                {
                  adults: quoteResponse.value.operation == 'passengers' ? services[i].adult : 1,
                  child: 0,
                  ages_child: [],
                },
              ],
              typeclass_id: services[i].hotel!.typeclass_id,
              destiny: {
                code: services[i].hotel!.country.iso + ',' + services[i].hotel!.state.iso,
                label:
                  services[i].hotel!.country.translations[0].value +
                  ',' +
                  services[i].hotel!.state.translations[0].value,
              },
              set_markup: 0,
              zero_rates: true,
            };

            promises.push(getHotelsAvailability(data));

            process_quote_service.push(services[i].id);
          }
        }

        if (promises.length > 0) {
          const result_promises = await Promise.all(promises);
          const updateServices: {
            quote_service_id: number;
            on_request: number;
          }[] = [];
          result_promises.forEach(({ data, success }, index) => {
            if (success) {
              if (data[0].city.hotels.length == 0) {
                updateServices.push({
                  quote_service_id: process_quote_service[index],
                  on_request: 1,
                });
              } else {
                let on_request_count = 0;
                for (let k = 0; k < data[0].city.hotels[0].rooms.length; k++) {
                  if (data[0].city.hotels[0].rooms[k].rates[0].onRequest == 0) {
                    on_request_count++;
                  }
                }
                updateServices.push({
                  quote_service_id: process_quote_service[index],
                  on_request: on_request_count > 0 ? 1 : 0,
                });
              }
            }
          });

          await updateOnRequestMultiple({
            services_update: updateServices,
          });
        }

        await updateTotals({
          quote_id: quote.value.id,
          client_id: getUserClientId(),
          category_id: selectedCategory.value,
        });
        // await getQuote(null, true, true);
        broadcastServiceUpdate(process_quote_service, false);
        closeIsLoading();
      } catch (e) {
        console.log(e);
        closeIsLoading();
      }
    },
    updateName: async () => {
      showIsLoading();
      try {
        await updateQuoteName(quote.value.id, quote.value.name);
        //  await getQuote(null, true, true);
        broadcastQuoteUpdate('quote_updated', 'quote.notification.service_updated');
        closeIsLoading();
      } catch (e) {
        console.log(e);
        closeIsLoading();
      }
    },
    updateDateIn: async (dateIn: string) => {
      showIsLoading();
      try {
        await updateQuoteDateIn(quote.value.id, dateIn);
        // await getQuote(null, true, true);
        broadcastQuoteUpdate('date_updated', 'quote.notification.date_updated');
        closeIsLoading();
      } catch (e) {
        console.log(e);
        closeIsLoading();
      }
    },
    saveQuoteNew: async () => {
      showIsLoading();
      try {
        let passengers = [];
        for (let i = 0; i < quoteNew.value.adults; i++) {
          passengers.push({
            id: null,
            first_name: '',
            last_name: '',
            gender: '',
            birthday: null,
            doctype_iso: '',
            document_number: null,
            country_iso: '',
            email: null,
            phone: null,
            notes: null,
            type: 'ADL',
          });
        }

        for (let i = 0; i < quoteNew.value.child; i++) {
          passengers.push({
            id: null,
            first_name: '',
            last_name: '',
            gender: '',
            birthday: null,
            doctype_iso: '',
            document_number: null,
            country_iso: '',
            email: null,
            phone: null,
            notes: null,
            type: 'CHD',
          });
        }

        let params = {
          categories: quoteNew.value.quoteCategoriesSelected,
          client_id: getUserClientId(),
          date: quoteNew.value.date_in,
          date_estimated: quoteNew.value.estimated_travel_date,
          name: quoteNew.value.name,
          notes: [],
          operation: 'passengers',
          passengers: passengers,
          people: {
            adults: quoteNew.value.adults,
            child: quoteNew.value.child,
            ages_child: quoteNew.value.quoteChildAges,
          },
          service_type_id: quoteNew.value.quoteServiceTypeId,
        };

        await saveQuotationNew(params);
        await getQuote();
        return true;
      } catch (e) {
        console.log(e);
        closeIsLoading();
        return false;
      }
    },
    saveQuote: async () => {
      showIsLoading();
      try {
        await saveQuotation(quote.value.id, {
          categories: quote.value.categories.map((c) => c.type_class_id),
          client_id: getUserClientId(),
          date: quote.value.date_in,
          date_estimated: quote.value.estimated_travel_date,
          name: quote.value.name,
          notes: [],
          operation: quote.value.operation,
          passengers: quote.value.passengers,
          people: quote.value.people[0],
          service_type_id: quoteServiceTypeId.value,
          languageId: quoteLanguageId.value,
        });
        // await getQuote()
        return true;
      } catch (e) {
        console.log(e);
        closeIsLoading();
        return false;
      }
    },
    closeQuote: async (goToPackage = true) => {
      showIsLoading();
      try {
        await closeQuotation(quote.value.id);
        store.unsetQuote();
        location.reload();
        if (goToPackage == true) {
          //   document.location.href = getUrlAuroraFront() + 'packages';
        }
      } catch (e) {
        console.log(e);
        closeIsLoading();
      }
    },
    deleteQuote: async (goToPackage = true) => {
      showIsLoading();
      try {
        await deleteQuotation(quote.value.id);
        store.unsetQuote();
        location.reload();
        if (goToPackage == true) {
          //   document.location.href = getUrlAuroraFront() + 'packages';
        }
      } catch (e) {
        console.log(e);
        socketsStore.send({
          type: 'update_quote',
          processing: false,
          success: false,
          message: 'Error deleting quote',
          description: e.message || 'Unknown error',
          date: dayjs().format('YYYY-MM-DD'),
          time: dayjs().format('HH:mm:ss'),
          userCode: getUserCode(),
          quote_id: quote.value.id,
          stream_log: 'deleteQuote',
        });
        closeIsLoading();
      }
    },
    deleteQuoteReport: async (data) => {
      showIsLoading();
      try {
        await deleteQuotation(data.id);
      } catch (e) {
        console.log(e);
        closeIsLoading();
      }
    },
    saveAs: async (name: string) => {
      showIsLoading();
      try {
        await saveAsQuote(quote.value.id, name);

        // await getQuote()
      } catch (e) {
        console.log(e);
        socketsStore.send({
          message: 'Error saving quote copy',
          description: e.message || 'Unknown error',
          success: false,
          date: dayjs().format('YYYY-MM-DD'),
          time: dayjs().format('HH:mm:ss'),
          stream_log: 'saveAs',
        });
        closeIsLoading();
      }
    },
    setQuotePassengers: async (type: string, value: number) => {
      showIsLoading();
      try {
        if (quote.value.people) {
          switch (type) {
            case 'adults':
              quote.value.people[0].adults = value;
              break;
            case 'child':
              quote.value.people[0].child = value;
              break;
            case 'infant':
              quote.value.people[0].infant = value;
              break;
          }
        }

        await generatePassenger(quote.value.passengers, quote.value.people[0]);
        await updatePeople({
          quote_id: quote.value.id,
          passengers: quote.value.passengers,
          people: quote.value.people[0],
        });

        await getQuote();
      } catch (e) {
        console.log(e);
        socketsStore.send({
          message: 'Error setting passengers',
          description: e.message || 'Unknown error',
          success: false,
          date: dayjs().format('YYYY-MM-DD'),
          time: dayjs().format('HH:mm:ss'),
          stream_log: 'setQuotePassengers',
        });
        closeIsLoading();
      }
    },
    saveQuoteRanges: async (ranges: QuoteRange[]) => {
      showIsLoading();
      try {
        await updateQuoteAssignment({
          quote_id: quote.value.id,
        });
        await saveQuoteRanges({
          quote_id: quote.value.id,
          ranges: ranges,
        });
        await updatePeople({
          quote_id: quote.value.id,
          passengers: [],
          people: {
            adults: 0,
            child: 0,
            ages_child: [],
          },
        });
        const response = await updateServiceHotelsRoomTypeQuantity({
          simple: '1',
          double: 1,
          triple: '1',
          double_child: 0,
          triple_child: 0,
          quote_id: quote.value.id,
        });

        const promisesAvailability = fetchHotelAvailability(response.hotels_add_rooms, getLang());

        if (promisesAvailability.length > 0) {
          const promisesResult = await Promise.all(promisesAvailability);

          const promisesServiceHotel = storeServiceHotel(quote.value.id, promisesResult, getLang());

          if (promisesServiceHotel.length > 0) {
            await Promise.all(promisesServiceHotel);
          }
        }

        getQuote();
      } catch (e) {
        console.log(e);
        socketsStore.send({
          message: 'Error saving quote ranges',
          description: e.message || 'Unknown error',
          success: false,
          date: dayjs().format('YYYY-MM-DD'),
          time: dayjs().format('HH:mm:ss'),
          stream_log: 'saveQuoteRanges',
        });
        closeIsLoading();
      }
    },
    cancelQuoteRanges: async (
      adults: number = 2,
      child: number = 0,
      simple: number = 0,
      double: number = 1,
      triple: number = 0
    ) => {
      showIsLoading();
      try {
        await updatePeople({
          quote_id: quote.value.id,
          passengers: [],
          people: {
            adults: adults,
            child: child,
            ages_child: [],
          },
        });

        const generatedDistribution = await getQuoteAccommodation(
          simple,
          double,
          triple,
          adults,
          child
        );
        await updateQuoteAccommodation(generatedDistribution, simple, double, triple);

        const response = await updateServiceHotelsRoomTypeQuantity({
          simple: simple,
          double: double,
          triple: triple,
          double_child: 0,
          triple_child: 0,
          quote_id: quote.value.id,
        });

        const promisesAvailability = fetchHotelAvailability(response.hotels_add_rooms, getLang());

        if (promisesAvailability.length > 0) {
          const promisesResult = await Promise.all(promisesAvailability);

          const promisesServiceHotel = storeServiceHotel(quote.value.id, promisesResult, getLang());

          if (promisesServiceHotel.length > 0) {
            await Promise.all(promisesServiceHotel);
          }
        }
        // getQuote()
      } catch (e) {
        console.log(e);
        socketsStore.send({
          message: 'Error canceling quote ranges',
          description: e.message || 'Unknown error',
          success: false,
          date: dayjs().format('YYYY-MM-DD'),
          time: dayjs().format('HH:mm:ss'),
          stream_log: 'cancelQuoteRanges',
        });
        closeIsLoading();
      }
    },
    setUpdatePeople: async (adults: number = 2, child: number = 0) => {
      await updatePeople({
        quote_id: quote.value.id,
        passengers: quote.value.passengers,
        people: {
          adults: adults,
          child: child,
          ages_child: [],
        },
      });
    },
    setQuoteChildAge: async (ageIndex: number, value: number) => {
      showIsLoading();
      try {
        quote.value.age_child[ageIndex].age = value;

        await updateChildAge(quote.value.age_child[ageIndex]);

        getQuote();
      } catch (e) {
        console.log(e);
        socketsStore.send({
          message: 'Error setting child age',
          description: e.message || 'Unknown error',
          success: false,
          date: dayjs().format('YYYY-MM-DD'),
          time: dayjs().format('HH:mm:ss'),
          stream_log: 'setQuoteChildAgeChangeQuote',
        });
        closeIsLoading();
      }
    },
    setQuoteChildAgeChangeQuote: async (ageIndex: number, value: number) => {
      showIsLoading();
      try {
        quote.value.age_child[ageIndex].age = value;

        await updateChildAge(quote.value.age_child[ageIndex]);
      } catch (e) {
        console.log(e);
        closeIsLoading();
      }
    },
    //copi de cancelQuoteRanges
    updatePassengersAccommodations: async (
      passengers: array,
      adults: number = 2,
      child: number = 0,
      ages_child: array,
      simple: number = 0,
      double: number = 1,
      triple: number = 0
    ) => {
      showIsLoading();
      try {
        const passengerUpdates = await updatePeople({
          quote_id: quote.value.id,
          passengers: passengers,
          people: {
            adults: adults,
            child: child,
            ages_child: ages_child,
          },
        });

        const generatedDistribution = await getQuoteAccommodation(
          simple,
          double,
          triple,
          adults,
          child
        );
        await updateQuoteAccommodation(generatedDistribution, simple, double, triple, 0);

        await updatePeople({
          quote_id: quote.value.id,
          passengers: passengerUpdates.data.data,
          people: {
            adults: adults,
            child: child,
            ages_child: ages_child,
          },
        });

        // await getQuote(null, true, true);
        broadcastQuoteUpdate('update', 'files.label.accommodations_success');
        closeIsLoading();
      } catch (e) {
        console.log(e);
        socketsStore.send({
          type: 'update_quote',
          success: false,
          processing: false,
          message: 'Error updating passenger accommodations',
          description: e.message || 'Unknown error',
          date: dayjs().format('YYYY-MM-DD'),
          time: dayjs().format('HH:mm:ss'),
          userCode: getUserCode(),
          quote_id: quote.value.id,
          stream_log: 'updatePassengersAccommodations',
        });
        closeIsLoading();
      }
    },
    updateQuoteCategory: async (
      categoryId: number | string,
      operation: 'new' | 'delete',
      sourceCategoryId?: number | string,
      key?: number,
      total?: number
    ) => {
      try {
        // Start processing for first item or single operation
        if (!key || key === 1) {
          socketsStore.send({
            type: 'update_quote',
            processing: true,
            quote_id: quote.value.id,
            user_code: getUserCode(),
            success: true,
          });
        }

        await updateQuoteCategory({
          quote_id: quote.value.id,
          category_id: categoryId,
          operation: operation,
          source_category_id: sourceCategoryId,
        });

        if (!total || key === total) {
          // await getQuote(null, true, true);

          if (operation === 'new') {
            broadcastQuoteUpdate('new_category', 'quote.notification.category_added');
          } else {
            broadcastQuoteUpdate('delete_category', 'quote.notification.category_deleted');
          }
        }
      } catch (e) {
        console.log(e);
        socketsStore.send({
          type: 'update_quote',
          action: 'update_category',
          success: false,
          processing: false,
          message: t('quote.label.error'),
          description: t('quote.label.error_updating_quote_category'),
          date: dayjs().format('YYYY-MM-DD'),
          time: dayjs().format('HH:mm:ss'),
          userCode: getUserCode(),
          quote_id: quote.value.id,
        });
      }
    },
    addExtension: async (
      extension_id: number,
      service_type_id: number,
      type_class_id: number,
      extension_date: Date | string,
      single: number,
      double: number,
      triple: number
    ) => {
      showIsLoading();

      const quote_category = quote.value!.categories.find(
        (c) => c.type_class_id === selectedCategory.value
      );

      try {
        const { data } = await addQuoteExtension({
          quote_id: quote.value.id,
          category_ids: [quote_category!.id],
          extension_id: extension_id,
          service_type_id: service_type_id,
          type_class_id: type_class_id,
          extension_date: extension_date,
        });
        // getQuote()

        if (data.success == true) {
          // Reacomodamos toda la cotizacion
          const generatedDistribution = await getQuoteAccommodation(
            single,
            double,
            triple,
            quote.value.people[0].adults,
            quote.value.people[0].child
          );

          if (quote.value.operation == 'passengers') {
            await updateQuoteAccommodation(generatedDistribution, single, double, triple);
          } else {
            await assignQuoteOccupation(single, double, triple);
          }

          // await getQuote(null, true, true);
          broadcastQuoteUpdate('new_extension', 'quote.notification.extension_added');
        } else {
          console.log(data);
          closeIsLoading();
        }
      } catch (e) {
        console.log(e.message);
        socketsStore.send({
          message: 'Error adding extension',
          description: e.message || 'Unknown error',
          success: false,
          date: dayjs().format('YYYY-MM-DD'),
          time: dayjs().format('HH:mm:ss'),
          stream_log: 'addExtension',
        });
        closeIsLoading();
      }
    },

    addServices: async (forms: QuoteServiceAddRequest[]) => {
      showIsLoading();
      try {
        const promises = [];
        for (const form of forms) {
          promises.push(addService(quote.value.id, form));
        }
        await Promise.all(promises);

        // await getQuote(null, true, true);
        broadcastQuoteUpdate('new_service', 'quote.notification.service_added');
        closeIsLoading();
      } catch (e) {
        console.log(e);
        socketsStore.send({
          message: 'Error adding services',
          description: e.message || 'Unknown error',
          success: false,
          date: dayjs().format('YYYY-MM-DD'),
          time: dayjs().format('HH:mm:ss'),
          stream_log: 'addServices',
        });
        closeIsLoading();
      }
    },
    quoteCategoryCopy: async (
      category_id_from: number,
      category_id_to: number,
      key: number,
      total: number
    ) => {
      try {
        if (!key || key === 1) {
          socketsStore.send({
            type: 'update_quote',
            processing: true,
            quote_id: quote.value.id,
            user_code: getUserCode(),
            success: true,
          });
        }

        await quoteCategoryCopy({
          quote_category_id_from: category_id_from,
          quote_category_id_to: category_id_to,
        });

        if (!key || key === total) {
          //  await getQuote(null, true, true);
          broadcastQuoteUpdate('category_updated', 'quote.notification.category_updated');
        }
      } catch (e) {
        console.log(e);
        socketsStore.send({
          message: 'Error copying quote category',
          description: e.message || 'Unknown error',
          success: false,
          processing: false,
          date: dayjs().format('YYYY-MM-DD'),
          time: dayjs().format('HH:mm:ss'),
          stream_log: 'quoteCategoryCopy',
        });
        closeIsLoading();
      }
    },
    replaceService: async (forms: QuoteServiceAddRequest[]) => {
      showIsLoading();
      try {
        const promises = [];
        for (const form of forms) {
          promises.push(addService(quote.value.id, form));
        }
        if (serviceSelected.value.type === 'service') {
          promises.push(
            deleteServices(quote.value.id, [(serviceSelected.value as GroupedServices).service!])
          );
        } else {
          promises.push(
            deleteServices(quote.value.id, (serviceSelected.value as GroupedServices).group!)
          );
        }

        await Promise.all(promises);
        await getQuote();
        store.unsetServiceSelected();
      } catch (e) {
        console.log(e);
        socketsStore.send({
          message: 'Error replacing service',
          description: e.message || 'Unknown error',
          success: false,
          date: dayjs().format('YYYY-MM-DD'),
          time: dayjs().format('HH:mm:ss'),
          stream_log: 'replaceService',
        });
        closeIsLoading();
      }
    },
    updateServiceDate: async (
      dateIn: string,
      nights: number | null,
      adult: number | null = null,
      child: number | null = null,
      hours: string | null = null,
      passengers: [],
      localized = false,
      propagate: boolean = false,
      flagSearchQuote: boolean = true
    ) => {
      const serviceId =
        (serviceSelected.value as GroupedServices).service?.id ||
        (serviceSelected.value as QuoteService).id;

      if (!serviceId) {
        return false;
      }

      // Show global loading if NOT using localized mode
      if (!localized) {
        showIsLoading();
      }

      // Declare data outside try block so it's accessible in catch
      let data: QuoteServiceUpdateRequest | null = null;
      let idsToLoad: number[] = [];

      let success = false;
      try {
        const oldDate =
          (serviceSelected.value as GroupedServices).service?.date_in ||
          (serviceSelected.value as QuoteService).date_in;

        // Parse both dates with support for multiple formats
        const newDateObj = dayjs(dateIn, ['YYYY-MM-DD', 'DD/MM/YYYY'], true);
        const oldDateObj = dayjs(oldDate, ['YYYY-MM-DD', 'DD/MM/YYYY'], true);
        const daysDiff = newDateObj.diff(oldDateObj, 'day');

        // Convert date to YYYY-MM-DD format for API
        const formattedDate = newDateObj.format('YYYY-MM-DD');

        // Find the category and calculate index_service
        const cat = quoteResponse.value.categories.find(
          (c) => c.type_class_id === selectedCategory.value
        );

        let indexService = 0;
        if (cat) {
          indexService = (cat.services as GroupedServices[]).findIndex(
            (s) => s.service?.id === serviceId
          );
          // If not found in grouped services, try direct services
          if (indexService === -1) {
            indexService = (cat.services as QuoteService[]).findIndex((s) => s.id === serviceId);
          }
          // Ensure we have a valid index
          if (indexService === -1) {
            indexService = 0;
          }
        }

        data = {
          index_service: indexService,
          quote_service_ids: [],
          date_in: formattedDate,
          quote_id: quote.value.id,
          move_services: propagate ? 1 : 0,
          days: daysDiff,
          client_id: getUserClientId(),
        };

        if (serviceSelected.value.type == 'group_header') {
          data.quote_service_ids = [
            ...(serviceSelected.value as GroupedServices).group.map((g) => g.id),
          ];
        } else if (serviceSelected.value.type == 'service') {
          const groupedService = serviceSelected.value as GroupedServices;
          if (groupedService.service?.id) {
            data.quote_service_ids = [groupedService.service.id];
          } else {
            data.quote_service_ids = [serviceId];
          }
        } else {
          data.quote_service_ids = [serviceId];
        }

        // Notify other users that these services are loading
        if (data.quote_service_ids.length > 0) {
          idsToLoad = [...data.quote_service_ids];
          if (
            serviceSelected.value.type === 'group_header' &&
            !idsToLoad.includes((serviceSelected.value as GroupedServices).service.id)
          ) {
            idsToLoad.push((serviceSelected.value as GroupedServices).service.id);
          }
          broadcastServiceUpdate(idsToLoad, true);
        }

        await updateServiceDateIn(data);

        // Only update nights if explicitly provided
        if (nights !== null && nights !== undefined) {
          await updateServiceNights({
            index_service: data.index_service,
            quote_service_ids: data.quote_service_ids,
            quote_id: data.quote_id,
            nights: nights,
          });
        }

        // Only update passengers if explicitly provided
        if (serviceSelected.value.type == 'service' && (adult !== null || child !== null)) {
          await updateServiceServicePassengers({
            service_id: data.quote_service_ids[0],
            adult: adult ?? 0,
            child: child ?? 0,
            quote_id: data.quote_id,
          });
        }

        // Only update hours if explicitly provided
        if (serviceSelected.value.type == 'service' && hours !== null && hours !== undefined) {
          await updateServiceHours(data.quote_service_ids, {
            hour_in: hours,
          });
        }

        // Only update room occupation if passengers array is provided and not empty
        if (
          serviceSelected.value.type == 'service' &&
          passengers &&
          Array.isArray(passengers) &&
          passengers.length > 0
        ) {
          const passengers_front = serviceSelected.value.service.passengers_front;

          passengers_front.forEach((s, index) => {
            passengers_front[index].checked = false;
          });

          passengers.forEach((passenger) => {
            passengers_front.forEach((s, index) => {
              if (passenger == s.id) {
                passengers_front[index].checked = true;
              }
            });
          });

          await updateRoomOccupation({
            passengers: passengers_front,
            service_id: serviceSelected.value.service.id,
            quote_id: quote.value.id,
          });
        }

        if (flagSearchQuote) {
          await getQuote(null, true, localized);
        }

        success = true;
      } catch (e) {
        console.log(e.message);

        socketsStore.send({
          type: 'update_quote',
          message: 'Error updating service date',
          description: e.message || 'Unknown error',
          success: false,
          userCode: getUserCode(),
          quote_id: quote.value.id,
        });
      } finally {
        // Clear loading state on error or success
        if (idsToLoad.length > 0) {
          broadcastServiceUpdate(
            idsToLoad,
            false,
            'update',
            success ? 'quote.notification.service_modified_title' : 'quote.label.error',
            success
              ? 'quote.notification.service_modified_success'
              : 'quote.notification.service_modified_error'
          );
        }

        if (!localized) {
          closeIsLoading();
        }
      }
    },
    updateExtension: async (dateIn: string, extension_id: number) => {
      showIsLoading();

      try {
        const categories = quote.value.categories.find((c) => {
          return c.type_class_id === selectedCategory.value;
        });

        const data: QuoteExtensionUpdateRequest = {
          extension_id: extension_id,
          date: dateIn,
          quote_id: quote.value.id,
          category_id: categories.id,
        };

        await updateExtension(data).then(async () => {
          // closeIsLoading()
          // await getQuote();
          broadcastQuoteUpdate('extension_updated', 'quote.notification.extension_updated');
        });
      } catch (e) {
        console.log(e.message);
        socketsStore.send({
          message: 'Error updating extension',
          description: e.message || 'Unknown error',
          success: false,
          date: dayjs().format('YYYY-MM-DD'),
          time: dayjs().format('HH:mm:ss'),
          stream_log: 'updateExtension',
        });
        closeIsLoading();
      }
    },
    removeQuoteServices: async (services: QuoteService[]) => {
      showIsLoading();
      try {
        await deleteServices(quote.value.id, services);
        //  await getQuote(null, true, true);
        broadcastQuoteUpdate('delete_service', 'quote.notification.service_deleted');
        closeIsLoading();
      } catch (e) {
        console.log(e);
        socketsStore.send({
          message: 'Error removing quote services',
          description: e.message || 'Unknown error',
          success: false,
          date: dayjs().format('YYYY-MM-DD'),
          time: dayjs().format('HH:mm:ss'),
          stream_log: 'removeQuoteServices',
        });
        closeIsLoading();
      }
    },
    replaceServiceHotelRoom: async (occupation: number, rateId: number, onRequest: number) => {
      showIsLoading();
      try {
        await replaceService((serviceSelected.value as QuoteService).id, {
          quote_id: quote.value.id,
          quote_service_id: (serviceSelected.value as QuoteService).id,
          rate_plan_room_ids: [],
          lang: getLang(),
          rate_plan_rooms_choose: [
            {
              choose: true,
              occupation: occupation,
              on_request: onRequest,
              rate_plan_room_id: rateId,
            },
          ],
        });

        getQuote();
      } catch (e) {
        console.log(e);
        socketsStore.send({
          message: 'Error replacing service hotel room',
          description: e.message || 'Unknown error',
          success: false,
          date: dayjs().format('YYYY-MM-DD'),
          time: dayjs().format('HH:mm:ss'),
          stream_log: 'replaceServiceHotelRoom',
        });
        closeIsLoading();
      }
    },

    setView: (view: string) => store.setView(view),

    setServiceEdit: async (service: QuoteService | GroupedServices, showDesign: boolean = true) => {
      store.setServiceSelected(service, showDesign);
    },
    unsetServiceEdit: async () => {
      store.unsetServiceSelected();
    },
    setSearchEdit: async (service: QuoteService | GroupedServices) => {
      store.setSearch(service);
    },
    unsetSearchEdit: async () => {
      store.unsetSearch();
    },
    updateServiceOptionalState: async (request: {
      optional: boolean | number;
      quote_service_id: number;
    }) => {
      const serviceId = request.quote_service_id;
      const allAffectedIds = [serviceId];

      // If it's a grouped service, ensure we track the header too for loading states
      if (
        serviceSelected.value?.type === 'group_header' &&
        !allAffectedIds.includes(serviceSelected.value.service.id)
      ) {
        allAffectedIds.push(serviceSelected.value.service.id);
      }

      // Unified loading start
      broadcastServiceUpdate(allAffectedIds, true);

      let success = false;
      try {
        await updateServiceOptionalState(request);
        success = true;
      } catch (e) {
        console.log(e);
      } finally {
        // Clear loading state on error or success
        broadcastServiceUpdate(
          allAffectedIds,
          false,
          'update',
          success ? 'quote.notification.service_modified_title' : 'quote.label.error',
          success
            ? 'quote.notification.service_modified_success'
            : 'quote.notification.service_modified_error'
        );
      }
    },
    addQuoteServiceHotelRoom: async (services: []) => {
      showIsLoading();
      try {
        const promises: Promise<void>[] = [];
        services.forEach((s) => {
          promises.push(addQuoteServiceHotelAddRooms(s.quote_service_id, s));
        });

        await Promise.all(promises);

        getQuote();
      } catch (e) {
        console.log(e);
        socketsStore.putNotification({
          message: 'Error adding quote service hotel room',
          description: e.message || 'Unknown error',
          success: false,
          date: dayjs().format('YYYY-MM-DD'),
          time: dayjs().format('HH:mm:ss'),
          stream_log: 'addQuoteServiceHotelRoom',
        });

        socketsStore.send({
          type: 'notification',
          data: {
            message: 'Error adding quote service hotel room',
            description: e.message || 'Unknown error',
            success: false,
            date: dayjs().format('YYYY-MM-DD'),
            time: dayjs().format('HH:mm:ss'),
            stream_log: 'addQuoteServiceHotelRoom',
            userCode: getUserCode(),
          },
        });
        closeIsLoading();
      }
    },

    // Getters
    operation: computed(() => quote.value.operation ?? ''),
    quoteCategories: computed(() => quote.value?.categories ?? []),
    processing,
    showDesign,
    quoteCategoriesSelected: computed<string[] | number[]>(() => {
      const selected = [] as string[];
      if (quote.value?.categories) {
        quote.value.categories.forEach((category) => {
          // _c.tabActive = (_k == 0) ? 'active' : ''

          category.checkAddService = false;
          category.checkAddHotel = false;
          category.checkAddExtension = false;

          if (category.tabActive == 'active') {
            category.checkAddService = true;
            category.checkAddHotel = true;
            category.checkAddExtension = true;
          }

          selected.push(category.type_class_id.toString());
        });
      }

      return selected;
    }),
    services: computed(() => {
      const cat = quote.value.categories.find((c) => {
        return c.type_class_id === selectedCategory.value;
      });

      const servicesList: GroupedServices[] = [];
      if (cat !== undefined) {
        (cat.services as GroupedServices[]).forEach((s) => {
          servicesList.push(s);
        });
      }

      return servicesList;
    }),
    people: computed(() => (quote.value.people ? quote.value.people[0] : undefined)),
    quotePassengers: computed(() => quote.value.passengers),
    quoteChildAges: computed(() => {
      if (quote.value.people && quote.value.people[0].child) {
        if (quote.value.people[0].child > quote.value.age_child.length) {
          for (let i = quote.value.people[0].child - quote.value.age_child.length; i > 0; i--) {
            quote.value.age_child.push({
              age: 0,
              quote_id: quote.value.id,
            });
          }
        } else if (quote.value.people[0].child < quote.value.age_child.length) {
          for (let i = quote.value.age_child.length - quote.value.people[0].child; i > 0; i--) {
            quote.value.age_child.pop();
          }
        }
      }

      return quote.value.age_child;
    }),
    accommodation: computed(
      () =>
        quote.value.accommodation ?? {
          single: 0,
          double: 0,
          double_child: 0,
          triple: 0,
          triple_child: 0,
        }
    ),
    ranges: computed<QuoteRange[]>(() => {
      return quote.value.ranges;
    }),
    quoteId: computed(() => {
      return quote_id_original();
    }),
    updateTotals: async (id: number, categoryId: number) => {
      await updateTotals({
        quote_id: id,
        client_id: getUserClientId(),
        category_id: categoryId,
      });
    },
  };
};

const broadcastQuoteUpdate = (action: string, description: string) => {
  const store = useQuoteStore();
  const socketsStore = useSocketsStore();

  socketsStore.send({
    success: true,
    type: 'update_quote',
    processing: false,
    action: action,
    message: 'quote.label.success',
    description: description,
    code: getUserCode(),
    name: getUserName(),
    quote_id: store.quote?.id,
  });
};

const broadcastServiceUpdate = (
  serviceIds: number[],
  processing: boolean = false,
  action: 'update' | 'new' | 'delete' = 'update',
  title?: string,
  description?: string
) => {
  const store = useQuoteStore();
  const socketsStore = useSocketsStore();

  // Local update for immediate feedback
  if (processing) {
    serviceIds.forEach((id) => store.setLoadingService(id, true));
  } else {
    store.clearLoadingServices(serviceIds);
  }

  socketsStore.send({
    type: 'update_quote_service',
    action: action,
    processing: processing,
    success: true,
    message: title || (processing ? 'quote.label.processing_quote' : 'quote.label.success'),
    description:
      description ||
      (processing
        ? 'quote.label.content_processing_quote'
        : 'quote.notification.service_modified_success'),
    quote_id: store.quote?.id,
    service_ids: serviceIds,
    user_code: getUserCode(),
    user_id: getUserId(),
    // code: getUserCode(),
    // name: getUserName(),
    date: dayjs().format('YYYY-MM-DD'),
    time: dayjs().format('HH:mm:ss'),
  });
};
