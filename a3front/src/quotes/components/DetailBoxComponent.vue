<script lang="ts" setup>
  import { computed, reactive, ref, toRef, watch, watchEffect } from 'vue';
  import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

  import ServiceComponent from '@/quotes/components/details/ServiceComponent.vue';
  import ModalComponent from './global/ModalComponent.vue';
  import ModalItinerarioDetail from '@/quotes/components/modals/ModalItinerarioDetail.vue';
  import HotelsComponent from '@/quotes/components/details/HotelsComponent.vue';
  import ScheduleComponent from '@/quotes/components/info/ScheduleComponent.vue';
  import { useSiderBarStore } from '@/quotes/store/sidebar';
  import CheckBoxComponent from '@/quotes/components/global/CheckBoxComponent.vue';
  import ReplaceService from '@/quotes/components/ReplaceService.vue';
  import ReplaceHotel from '@/quotes/components/ReplaceHotel.vue';
  import HotelPromotions from '@/quotes/components/HotelPromotions.vue';
  import IconCalendarLigth from '@/quotes/components/icons/IconCalendarLight.vue';
  import IconPlusSquare from '@/quotes/components/icons/IconPlusSquare.vue';
  import AddServiceComponent from '@/quotes/components/AddServiceComponent.vue';
  import type {
    GroupedServices,
    QuoteService,
    QuoteServiceServiceImport,
  } from '@/quotes/interfaces/quote.response';
  import IconHotelsDark from '@/quotes/components/icons/IconHotelsDark.vue';
  import IconPlusCleck from '@/quotes/components/icons/IconPlusCleck.vue';
  import IconConfirmed from '@/quotes/components/icons/IconConfirmed.vue';
  import IconMiscellaneousItem from '@/quotes/components/icons/IconMiscellaneousItem.vue';
  import IconEditDark from '@/quotes/components/icons/IconEditDark.vue';
  import IconTrashDark from '@/quotes/components/icons/IconTrashDark.vue';
  import IconBook from '@/quotes/components/icons/IconBook.vue';
  import IconBurger from '@/quotes/components/icons/IconBurger.vue';
  import { useQuote } from '@/quotes/composables/useQuote';
  import IconAngleDown from '@/quotes/components/icons/IconAngleDown.vue';
  import IconAlert from '@/quotes/components/icons/IconAlert.vue';
  import IconError from '@/quotes/components/icons/IconError.vue';
  import ServiceRamoveConfirm from '@/quotes/components/modals/ServiceDeleteConfirmation.vue';
  import IconLunch from '@/quotes/components/icons/IconLunch.vue';
  import IconToursDark from '@/quotes/components/icons/IconToursDark.vue';
  import IconTransfersDark from '@/quotes/components/icons/IconTransfersDark.vue';
  import { getHotelById } from '@/quotes/helpers/get-hotel-by-id';
  import type { Hotel } from '@/quotes/interfaces';
  import IconOnRequest from '@/quotes/components/icons/IconOnRequest.vue';
  import useLoader from '@/quotes/composables/useLoader';
  import { useDateConflictCheck } from '@/quotes/composables/useDateConflictCheck';
  import QuoteShiftServiceModal from '@/quotes/components/modals/QuoteShiftServiceModal.vue';
  import LoadingSkeleton from '@/components/global/LoadingSkeleton.vue';
  import type { Service } from '@/quotes/interfaces/services';
  import dayjs from 'dayjs';
  import { useI18n } from 'vue-i18n';
  import { useLanguagesStore, useSocketsStore } from '@/stores/global';
  import { useQuoteStore } from '@/quotes/store/quote.store';
  import { useQuoteServices } from '@/quotes/composables/useQuoteServices';
  import { useQuoteHotels } from '@/quotes/composables/useQuoteHotels';
  import ServiceItemModal from '@/quotes/components/ServiceItemModal.vue';
  import 'dayjs/locale/es-mx';
  import 'dayjs/locale/pt-br';
  import 'dayjs/locale/en-au';
  import { getPriceWithCommission, hasCommission } from '@/utils/price';
  import { getUserType } from '@/utils/auth';
  import { storeToRefs } from 'pinia';
  import ErrorView from '@/views/ErrorView.vue';
  const { t } = useI18n();
  const quoteStore = useQuoteStore();
  const languageStore = useLanguagesStore();
  const { marketList } = storeToRefs(quoteStore);
  const user_type_id = parseInt(getUserType(), 10);

  // ✅ usar directamente el cliente guardado en el store
  const client = computed(() => marketList.value?.data || null);

  // ✅ Función para mostrar el precio con comisión
  const displayPrice = (price: number) => getPriceWithCommission(price, client.value, user_type_id);

  // ✅ Mostrar badge solo si aplica
  const showCommissionBadge = computed(() => hasCommission(client.value, user_type_id));

  const { showIsLoading, closeIsLoading } = useLoader(); /**/
  const storeSidebar = useSiderBarStore();
  const socketsStore = useSocketsStore();

  const { checkDateConflict } = useDateConflictCheck();

  const { services, extensions, count, changePage } = useQuoteServices();
  const { hotels } = useQuoteHotels();

  const {
    updateServiceDate,
    setServiceEdit,
    serviceSelected: serviceSelectedQuote,
    operation,
    setSearchEdit,
    removeQuoteServices,
    updateServiceOptionalState,
    showDesign,
  } = useQuote();

  const isAddServiceModalOpen = ref(false);
  const selectedAddServiceTab = ref('tours');

  const updateSearched = (is_searched: boolean) => {
    isSearched.value = is_searched;
  };

  const toggleAddServiceModal = () => {
    storeSidebar.setStatus(false, '', '');

    isSearched.value = false;
    count.value = 0;
    services.value = [];
    extensions.value = [];
    hotels.value = [];

    isAddServiceModalOpen.value = !isAddServiceModalOpen.value;
  };

  const currentResults = computed(() => {
    if (selectedAddServiceTab.value === 'hotels') return hotels.value;
    if (selectedAddServiceTab.value === 'extensions') return extensions.value;
    return services.value;
  });

  const showResults = computed(() => {
    if (selectedAddServiceTab.value === 'hotels' && hotels.value.length > 0) return true;
    if (selectedAddServiceTab.value === 'extensions' && extensions.value.length > 0) return true;
    if (services.value.length > 0) return true;
    return false;
  });

  const pagination = ref({
    current: 1,
    pageSize: 10,
    total: 0,
  });

  const isServerSidePagination = computed(() => {
    return !['extensions', 'hotels'].includes(selectedAddServiceTab.value);
  });

  const paginatedResults = computed(() => {
    const results = currentResults.value || [];

    if (isServerSidePagination.value) {
      pagination.value.total = count.value;
      return results;
    } else {
      pagination.value.total = results.length;
      const start = (pagination.value.current - 1) * pagination.value.pageSize;
      const end = start + pagination.value.pageSize;
      return results.slice(start, end);
    }
  });

  const handlePageChange = (page: number, pageSize: number) => {
    pagination.value.current = page;
    pagination.value.pageSize = pageSize;

    if (isServerSidePagination.value) {
      changePage(page);
    }
  };

  watch(selectedAddServiceTab, () => {
    pagination.value.current = 1;
    if (isServerSidePagination.value) {
    }
  });

  const isProcessingSearch = computed(() => quoteStore.isLoading);
  const isSearched = ref(false);

  watch(isProcessingSearch, (newValue, oldValue) => {
    isSearched.value = false;
    if (!newValue && oldValue) {
      isSearched.value = true;
    }
  });

  watch(isAddServiceModalOpen, (isOpen) => {
    if (isOpen) {
      document.body.style.overflow = 'hidden';
    } else {
      document.body.style.overflow = '';
    }
  });

  const isDatePickerOpen = ref(false);
  const openDatePicker = () => {
    isDatePickerOpen.value = true;
  };
  const getDatePickerContainer = () => {
    return document?.body || document?.documentElement;
  };

  const state = reactive({
    opened: false,
    modalHotelDetail: {
      isOpen: false,
    },
    modalDateChange: {
      isOpen: false,
      date: null as string | null,
      nightsCount: null as number | null,
    },
    modalDateConflict: {
      isOpen: false,
      date: null as string | null,
      nightsCount: null as number | null,
      conflictingServices: [] as Array<any>,
    },
    openOptional: false,
    openDelete: false,
  });

  const confirmDateChange = async (propagate: boolean) => {
    const { date, nightsCount } = state.modalDateChange;
    if (!date) return;

    closeDateChangeModal(); // Close first
    await updateServiceDate(date, nightsCount, null, null, null, [], true, propagate);
  };

  const closeDateChangeModal = () => {
    state.modalDateChange.isOpen = false;
    state.modalDateChange.date = null;
    state.modalDateChange.nightsCount = null;
  };

  const handleDateChange = async (date: any) => {
    if (!date) return;

    const dateStr = date.format('DD/MM/YYYY');
    isDatePickerOpen.value = false;

    try {
      setServiceEdit(groupedService.value, false);
      const nightsCount = null;

      const conflictResult = checkDateConflict(dateStr, service.value.id);

      if (conflictResult.hasConflict) {
        state.modalDateConflict.date = dateStr;
        state.modalDateConflict.nightsCount = nightsCount;
        state.modalDateConflict.conflictingServices = conflictResult.conflictingServices;
        state.modalDateConflict.isOpen = true;
        return; // Wait for user confirmation in modal
      }

      proceedWithDateChangeOrPropagate(dateStr, nightsCount);
    } catch (e) {
      console.error(e);
    }
  };

  const proceedWithDateChangeOrPropagate = async (dateStr: string, nightsCount: number | null) => {
    if (item.value + 1 !== totalService.value) {
      state.modalDateChange.date = dateStr;
      state.modalDateChange.nightsCount = nightsCount;
      state.modalDateChange.isOpen = true;
    } else {
      await updateServiceDate(dateStr, nightsCount, null, null, null, [], true, true);
    }
  };

  const confirmDateConflict = async (propagate: boolean) => {
    // User agreed to ignore conflict and chose whether to propagate
    const { date, nightsCount } = state.modalDateConflict;
    if (!date) return;

    state.modalDateConflict.isOpen = false;
    await updateServiceDate(date, nightsCount, null, null, null, [], true, propagate);
  };

  const closeDateConflictModal = () => {
    state.modalDateConflict.isOpen = false;
    state.modalDateConflict.date = null;
    state.modalDateConflict.nightsCount = null;
    state.modalDateConflict.conflictingServices = [];
  };

  interface Props {
    totalService: number;
    item: number;
    groupedService: GroupedServices;
    iniDiv: boolean;
    finDiv: boolean;
  }

  const props = defineProps<Props>();

  const groupedService = toRef(props, 'groupedService');

  const totalService = toRef(props, 'totalService');
  const item = toRef(props, 'item');
  const hotelSelected = ref<Hotel>();

  const type = computed(() => groupedService.value.type);
  const day = computed(() => groupedService.value.day);
  const service = computed(() => groupedService.value.service);
  const group = computed(() => groupedService.value.group);
  // const selected = computed(() => groupedService.value.selected);

  // moment.locale("pt")

  const selectItem = (e: boolean) => {
    groupedService.value.selected = e;
  };

  const getServiceName = () => {
    if (type.value === 'service') {
      return service.value.service?.service_translations[0].name;
    } else if (type.value === 'flight') {
      let flight: string = '';
      if (service.value.code_flight == 'AEC' || service.value.code_flight == 'AECFLT') {
        flight = '[' + t('quote.label.flight') + ' - ' + t('flights.label.national') + ']';
      }
      if (service.value.code_flight == 'AEI' || service.value.code_flight == 'AEIFLT') {
        flight = '[' + t('quote.label.flight') + ' - ' + t('quote.label.international') + ']';
      }

      if (service.value.origin != '' && service.value.origin != null) {
        flight += t('quote.label.origin') + ': ' + service.value.origin;
      }

      if (
        service.value.origin != '' &&
        service.value.origin != null &&
        service.value.destiny != '' &&
        service.value.destiny != null
      ) {
        flight += ' / ';
      }

      if (service.value.destiny != '' && service.value.destiny != null) {
        flight += ' ' + t('quote.label.destiny') + ': ' + service.value.destiny;
      }

      return flight;
    } else {
      return service.value.hotel?.name;
    }
  };

  const geFlightDestiny = () => {
    let flight: string = '';

    if (service.value.origin != '' && service.value.origin != null) {
      flight = service.value?.flight_origin?.translations?.[0]?.value ?? '';
    }

    if (service.value.destiny != '' && service.value.destiny != null) {
      flight = service.value?.flight_destination?.translations?.[0]?.value || '';
    }

    return flight;
  };

  const getServiceHourIn = () => {
    if (type.value === 'service') {
      return service.value?.hour_in;
    } else {
      ('');
    }
  };

  const getServiceDurationId = () => {
    if (type.value === 'service') {
      return service.value?.service?.unit_duration_id;
    } else {
      ('');
    }
  };

  const getServiceDuration = () => {
    if (type.value === 'service') {
      return service.value?.service?.duration;
    } else {
      ('');
    }
  };

  const getServiceItinerary = () => {
    if (type.value === 'service') {
      return service.value.service?.service_translations[0].itinerary;
    } else {
      return '';
    }
  };

  const getServiceSchedule = () => {
    if (type.value === 'service') {
      return service.value.service?.schedules;
    } else {
      return [];
    }
  };
  const getServiceType = () => {
    if (type.value === 'service') {
      return service.value.service?.service_type.code ?? 'NA';
    }
  };
  const getServiceTypeName = () => {
    if (type.value === 'service') {
      return service.value.service?.service_type.translations[0].value ?? 'NA';
    }
  };

  const getServiceTypeId = () => {
    if (type.value === 'service') {
      return service.value.service?.service_type.id ?? 'NA';
    }
  };

  const getServiceCategory = () => {
    if (type.value === 'service') {
      return service.value.service?.service_sub_category.service_categories.id.toString() ?? '';
    }
  };

  const getServiceCategorName = () => {
    if (type.value === 'service') {
      return (
        service.value.service?.service_sub_category.service_categories.translations[0].value.toString() ??
        ''
      );
    }
  };

  const getHotelTypeClass = () => {
    if (type.value === 'group_header') {
      return service.value.hotel?.class ?? '';
    }

    return null;
  };

  const getHotelTypeClassColor = () => {
    if (type.value === 'group_header') {
      return service.value.hotel?.color_class ?? '';
    }
  };

  const serviceName = computed(() => getServiceName());
  const serviceHourIn = computed(() => getServiceHourIn());
  const serviceDurationId = computed(() => getServiceDurationId());
  const serviceDuration = computed(() => getServiceDuration());

  const serviceItinerary = computed(() => getServiceItinerary());
  const serviceSchedule = computed(() => getServiceSchedule());

  const serviceType = computed(() => getServiceType());
  const serviceTypeName = computed(() => getServiceTypeName());
  const serviceTypeId = computed(() => getServiceTypeId());
  const serviceCategory = computed(() => getServiceCategory());
  const serviceCategoryName = computed(() => getServiceCategorName());

  const hotelTypeClass = computed(() => getHotelTypeClass());
  const hotelTypeClassColor = computed(() => getHotelTypeClassColor());
  // Dates

  const getDateInFormat = () => {
    return service.value.date_in_format;
  };

  const getDateIn = () => {
    return service.value.date_in;
  };
  const getDateOut = () => {
    return service.value.date_out;
  };
  const getDateInstance = (date: string) => {
    return dayjs(date, ['YYYY-MM-DD', 'DD/MM/YYYY']);
  };

  const dateIn = computed(() => {
    return getDateInstance(getDateIn()).format('MMM D, YYYY');
  });
  const dateDay = computed(() => {
    return getWeekDay(getDateInFormat().toString());
  });

  const getWeekDay = (dateValue: string = '') => {
    if (!dateValue) return '';
    return dayjs(dateValue).format('dddd');
  };

  const dateOut = computed(() => {
    return getDateInstance(getDateOut()).format('MMM D, YYYY');
  });

  // Computed for the date picker value
  const serviceDateValue = computed(() => {
    const dateStr = getDateIn();
    if (!dateStr) return undefined;
    // Handle both formats for the picker
    return dayjs(dateStr, ['YYYY-MM-DD', 'DD/MM/YYYY']);
  });

  // destinations
  const getDestinationName = () => {
    if (type.value === 'service') {
      return service.value.service?.service_origin[0].state.translations[0].value;
    } else {
      return service.value.hotel?.state.translations[0].value;
    }
  };
  const destinationName = computed(() => getDestinationName());

  // Passengers
  const adultNumber = computed(() => {
    if (type.value === 'service' || type.value === 'flight') {
      return service.value.adult;
    } else {
      return group.value.map((s) => s.adult).reduce((a, b) => a + b, 0);
    }
  });
  const childNumber = computed(() => {
    if (type.value === 'service' || type.value === 'flight') {
      return service.value.child;
    } else {
      return group.value.map((s) => s.child).reduce((a, b) => a + b, 0);
    }
  });

  // prices
  const priceTotal = computed(() => {
    if (type.value === 'service') {
      return (service.value.import as QuoteServiceServiceImport)?.price_ADL ?? 0;
    } else {
      return group.value
        .map((s) => s.import_amount?.price_ADL ?? 0)
        .reduce((a, b) => Number(a) + Number(b), 0);
    }
  });

  const hotelNightNumber = computed(() => {
    const dateIn = getDateInstance(getDateIn());
    const dateOut = getDateInstance(getDateOut());

    return dateOut.diff(dateIn, 'days');
  });

  const hotelRoomsNumber = computed(() => group.value.length ?? 0);

  // Hotels
  const openHotelDetailModal = async (hotel: Hotel) => {
    showIsLoading();
    hotelSelected.value = await getHotelById(hotel.id, languageStore.currentLanguage);
    state.modalHotelDetail.isOpen = true;
    closeIsLoading();
  };

  const claseHotelDetailModal = () => {
    state.modalHotelDetail.isOpen = false;
  };

  const toggleModalOptional = (serviceArg?: QuoteService) => {
    if (serviceArg && !state.openOptional) {
      setServiceEdit(groupedService.value, false);
    }
    state.openOptional = !state.openOptional;
  };

  const openDetail = () => {
    state.opened = !state.opened;
  };

  const serviceSelected = ref<QuoteService>();

  const classServiceSelectedQuote = computed(() => {
    if (Object.keys(serviceSelectedQuote.value).length > 0) {
      if (serviceSelectedQuote.value.service) {
        if (service.value.id == serviceSelectedQuote.value.service.id) {
          return true;
        }
      } else {
        return false;
      }
    }

    return false;

    // if(type == 'group_header'){
    //   if(service.value.id = serviceSelectedQuote.value.service.id){
    //     return true;
    //   }
    // }else{
    //   serviceSelected
    // }
  });

  const removeServiceConfirm = async (service: QuoteService) => {
    serviceSelected.value = service;
  };
  const editServiceRoom = async (service: QuoteService) => {
    // console.log("mauoooooooooooooooooooo");
    setServiceEdit(service);
  };

  const editFormSearch = async () => {
    setSearchEdit(service.value);
  };

  const removeService = async () => {
    let dataToRemove: QuoteService[];

    if (serviceSelected.value?.type === 'group_header') {
      dataToRemove = group.value;
    } else if (serviceSelected.value?.type === 'group_type_room') {
      dataToRemove = getRoomsByType(serviceSelected.value.type_room_id!);
    } else {
      dataToRemove = [serviceSelected.value!];
    }

    removeQuoteServices(dataToRemove);
    serviceSelected.value = undefined;
  };

  const getRoomsByType = (roomTypeId: number): QuoteService[] => {
    return group.value.filter(
      (r) => r.service_rooms[0].rate_plan_room.room.room_type_id === roomTypeId
    );
  };

  const editHotel = () => {
    console.log(groupedService.value);
    setServiceEdit(groupedService.value);
    // if (type.value === "hotel") {
    if (type.value === 'group_header') {
      storeSidebar.setStatus(true, 'hotel_edit', 'edit');
    }
    if (type.value === 'service') {
      storeSidebar.setStatus(true, 'service_edit', 'edit');
    }

    setTimeout(function () {
      let positionWindow = window.pageYOffset + 1;
      window.scrollTo({ top: positionWindow, behavior: 'smooth' });
    }, 50);
  };

  const updateOptionalState = async () => {
    let targetIds: number[] = [];

    if (groupedService.value?.type === 'service') {
      targetIds = [service.value.id];
    } else {
      targetIds = group.value.map((s) => s.id);
    }

    toggleModalOptional();
    try {
      const newOptionalState = service.value.optional ? 0 : 1;
      // Process updates sequentially to ensure backend receives expected "single integer" format
      await Promise.all(
        targetIds.map((id) =>
          updateServiceOptionalState({
            optional: newOptionalState,
            quote_service_id: id,
          })
        )
      );
    } catch (error) {
      console.error(error);
    }
  };

  // Services

  const serviceServiceSelected = ref<Service>();
  const openServiceModalItinerary = (service: Service) => {
    showIsLoading();
    serviceServiceSelected.value = service;
    state.modalHotelDetail.isOpen = true;
    closeIsLoading();
  };

  const truncateString = (str: string, num: number) => {
    if (str.length <= num) {
      return str;
    }
    return str.slice(0, num) + '...';
  };

  const formatDate = (date: string) => {
    return dayjs(date).format('DD/MM/YYYY');
  };

  watchEffect(() => {
    if (languageStore.currentLanguage == 'es') {
      dayjs.locale('es-mx');
    }

    if (languageStore.currentLanguage == 'en') {
      dayjs.locale('en-br');
    }

    if (languageStore.currentLanguage == 'pt') {
      dayjs.locale('pt-br');
    }
  });
</script>

<template>
  <div
    :id="item"
    :total="totalService"
    :count="service.new_extension_id"
    v-if="!service.new_extension_id"
    class="service-item-container"
  >
    <a-badge-ribbon
      :color="service.isNew || service.isUpdated ? '#EB5757' : '#737373'"
      class="cursor-pointer"
      style="position: absolute; z-index: 999; top: -7px; padding: 3px 5px"
      v-if="
        service.isNew ||
        service.isUpdated ||
        (service.isError &&
          socketsStore.getNotifications.filter(
            (item) => item.itinerary_id === service.id && item.flag_show
          ).length > 0)
      "
    >
      <template #text>
        <span>
          <a-popover
            placement="topRight"
            v-if="
              socketsStore.getNotifications.filter(
                (item) =>
                  (item.itinerary_id === service.id || item.quote_service_id === service.id) &&
                  item.flag_show
              ).length > 0
            "
          >
            <template #title>
              <small class="text-uppercase"
                ><font-awesome-icon :icon="['fas', 'arrows-rotate']" spin />
                {{ t('global.message.itinerary_updated') }}
                <a-tag color="red" class="ms-1">{{
                  service.code || service.object_code || service.service?.aurora_code || 'CODE'
                }}</a-tag></small
              >
            </template>
            <template #content>
              <div class="pe-2" style="max-height: 220px; overflow-y: auto">
                <template
                  v-for="item in socketsStore.getNotifications.filter(
                    (item) =>
                      (item.itinerary_id === service.id || item.quote_service_id === service.id) &&
                      item.flag_show
                  )"
                >
                  <p class="mb-0">
                    <font-awesome-icon
                      :icon="['far', item.success ? 'thumbs-up' : 'thumbs-down']"
                      :class="item.success ? 'text-success' : 'text-danger'"
                    />
                    {{ truncateString(item.description ? t(item.description) : '', 90) }}
                    <small v-if="!item.success" class="text-600 text-uppercase"
                      >({{ item.message ? t(item.message) : '' }})</small
                    >
                  </p>
                  <p class="mb-0 text-dark-gray" style="font-size: 0.9rem">
                    <a-row type="flex" justify="start" align="middle" style="gap: 5px">
                      <a-col>
                        <small v-if="item.user_code">
                          <font-awesome-icon :icon="['far', 'circle-user']" class="me-1" />
                          <b>{{ item.user_code }}</b>
                        </small>
                        <small v-else>
                          <font-awesome-icon :icon="['fas', 'robot']" class="me-1" />
                          <b>Aurora BOT</b>
                        </small>
                      </a-col>
                      <a-col>
                        <small v-if="item?.date && item?.time">
                          <font-awesome-icon :icon="['far', 'clock']" class="me-1" />
                          <b>{{ formatDate(item.date) }} {{ item.time }}</b>
                        </small>
                      </a-col>
                    </a-row>
                  </p>
                </template>
              </div>
            </template>
            <font-awesome-icon :icon="['fas', 'circle-question']" fade />
          </a-popover>
          <template v-else>
            <span v-if="service.isNew">
              <font-awesome-icon :icon="['fas', 'bolt']" fade />
            </span>
            <span v-else>
              <a-popover placement="topRight">
                <template #title>
                  <small class="text-uppercase"
                    ><font-awesome-icon :icon="['fas', 'arrows-rotate']" spin />
                    {{ t('global.message.itinerary_updated') }}
                    <a-tag color="red" class="ms-1">{{
                      service.code || service.object_code || service.service?.aurora_code || 'CODE'
                    }}</a-tag></small
                  >
                </template>
                <template #content>
                  <div class="pe-2" style="max-height: 220px; overflow-y: auto">
                    <p class="mb-0">
                      <font-awesome-icon :icon="['far', 'thumbs-up']" :class="'text-success'" />
                      {{ t('files.notification.itinerary_update_remote') }}.
                    </p>
                    <p class="mb-0 text-dark-gray" style="font-size: 0.9rem">
                      <a-row type="flex" justify="start" align="middle" style="gap: 5px">
                        <a-col>
                          <small>
                            <font-awesome-icon :icon="['fas', 'robot']" class="me-1" />
                            <b>Aurora BOT</b>
                          </small>
                        </a-col>
                        <a-col> </a-col>
                      </a-row>
                    </p>
                  </div>
                </template>
                <font-awesome-icon :icon="['fas', 'bullseye']" fade />
              </a-popover>
            </span>
          </template>
        </span>
      </template>
    </a-badge-ribbon>

    <div
      v-if="service.isLoading"
      class="quotes-itineraries"
      style="
        width: 100%;
        min-height: 150px;
        display: flex;
        align-items: center;
        justify-content: center;
      "
    >
      <div class="quotes-itineraries-internal px-4 pt-4 pb-0">
        <LoadingSkeleton :rows="3" />
      </div>
    </div>
    <div
      v-else
      :class="{
        'service-optional': service.optional,
        ignore:
          !classServiceSelectedQuote && Object.keys(serviceSelectedQuote).length > 0 && showDesign,
        isEditing: classServiceSelectedQuote && showDesign,
      }"
      class="quotes-itineraries"
      style="width: 100%"
    >
      <div class="quotes-itineraries-internal">
        <div class="quotes-itineraries-content">
          <a-space v-if="service.validations.length" direction="vertical" style="width: 100%">
            <a-alert type="error" class="mb-0 border-0 border-radius-0" show-icon>
              <template #message>
                <span class="text-danger">{{ service.validations[0].error }}</span>
              </template>
              <template #icon>
                <a-tooltip placement="top">
                  <template #title>
                    <span> {{ t('quote.label.information') }}</span>
                  </template>
                  <icon-error color="#FF3B3B" />
                </a-tooltip>
              </template>
            </a-alert>
          </a-space>
          <div
            class="quotes-itineraries-header alert-improvements border-0 border-radius-0"
            v-if="hotelTypeClass"
          >
            <div class="action">
              <div class="icon">
                <a-tooltip placement="bottom">
                  <template #title>
                    <span> {{ t('quote.label.promotions') }}</span>
                  </template>

                  <hotel-promotions
                    :hotel="service"
                    @edit-hotel="editHotel"
                    v-if="hotelTypeClass"
                  />
                </a-tooltip>
              </div>
            </div>
          </div>
          <div class="quotes-itineraries-header">
            <div class="left">
              <div class="move-icon handle">
                <icon-burger />
              </div>
              <div class="date date-editable" @click="openDatePicker">
                <a-date-picker
                  :value="serviceDateValue"
                  format="DD/MM/YYYY"
                  :open="isDatePickerOpen"
                  @change="handleDateChange"
                  @openChange="(open: any) => (isDatePickerOpen = open)"
                  :getPopupContainer="getDatePickerContainer"
                  :inputReadOnly="true"
                  style="
                    position: absolute;
                    width: 1px;
                    height: 1px;
                    opacity: 0;
                    pointer-events: none;
                  "
                />
                <div class="icon-container">
                  <div class="icon">
                    <icon-calendar-ligth />
                  </div>
                  <div class="text">{{ t('quote.label.start') }}:</div>
                </div>
                <div class="date-text text-uppercase">{{ dateIn }}</div>
              </div>
              <div v-if="getHotelTypeClass() !== null" class="date">
                <div class="icon-container end">
                  <div class="icon">
                    <icon-calendar-ligth />
                  </div>
                  <div class="text">{{ t('quote.label.end') }}:</div>
                </div>
                <div class="date-text text-uppercase">{{ dateOut }}</div>
              </div>
              <div class="days text-uppercase">
                {{ t('quote.label.day_upper') }} {{ day }} <span class="pipe">|</span> {{ dateDay }}
                <span class="pipe">|</span>
                {{ destinationName || geFlightDestiny() }}
              </div>
            </div>
            <div class="right">
              <div class="people" v-if="operation == 'passengers'">
                <font-awesome-icon :style="{ fontSize: '13px' }" icon="user" />
                {{ adultNumber }} ADL
                <font-awesome-icon :style="{ fontSize: '16px' }" class="icon-right" icon="child" />
                {{ childNumber }} CHD
              </div>
              <div class="tag">
                <div
                  v-if="hotelTypeClass"
                  :style="{ 'background-color': hotelTypeClassColor }"
                  class="tag-button superior"
                >
                  {{ hotelTypeClass }}
                </div>
                <div
                  v-if="type === 'service' && serviceType !== 'NA'"
                  class="tag-button"
                  v-bind:class="{
                    'bg-private': serviceTypeId == 2,
                    'bg-shared': serviceTypeId == 1,
                    'bg-none': serviceTypeId == 3,
                  }"
                >
                  {{ serviceTypeName }}
                </div>
              </div>
              <div class="status">
                <div v-if="hasNotifications" class="icon" style="display: none">
                  <!-- Old location removed/hidden -->
                </div>
                <div
                  v-if="hotelTypeClass"
                  class="icon"
                  @click="openHotelDetailModal(service.hotel)"
                >
                  <a-tooltip placement="top">
                    <template #title>
                      <span> {{ t('quote.label.information') }}</span>
                    </template>
                    <icon-alert :height="25" :width="25" />
                  </a-tooltip>
                </div>
                <div
                  v-if="type === 'service'"
                  class="icon"
                  @click="openServiceModalItinerary(service.service)"
                >
                  <a-tooltip placement="top">
                    <template #title>
                      <span> {{ t('quote.label.information') }}</span>
                    </template>
                    <icon-alert :height="25" :width="25" />
                  </a-tooltip>
                </div>
              </div>
            </div>
          </div>
          <div
            class="quotes-itineraries-body"
            :class="{
              bgHotel: hotelTypeClass,
            }"
          >
            <div class="title-box">
              <div class="left">
                <CheckBoxComponent :modelValue="groupedService.selected" @checked="selectItem" />
                <div class="icon">
                  <icon-hotels-dark v-if="hotelTypeClass" />
                  <template v-else-if="['1', '10', '9', '14'].includes(serviceCategory)">
                    <icon-lunch v-if="serviceCategory === '10'" />
                    <icon-tours-dark v-if="serviceCategory === '9'" />
                    <icon-transfers-dark v-if="serviceCategory === '1'" />
                    <icon-miscellaneous-item v-if="serviceCategory === '14'" />
                  </template>
                  <icon-miscellaneous-item v-else />
                </div>
                <div class="title">
                  {{ serviceName }}

                  <div v-if="hotelTypeClass" class="extra-hotel">
                    <span>|</span>
                    {{ hotelNightNumber }} {{ t('quote.label.nights') }}
                    <span>|</span>
                    {{ hotelRoomsNumber }} {{ t('quote.label.rooms') }}

                    <icon-confirmed v-if="!service.on_request" />
                    <template v-else>
                      <div class="on-request">
                        <icon-on-request />
                        RQ
                      </div>
                    </template>
                  </div>
                </div>
              </div>
              <div class="right">
                <template v-if="type === 'service'">
                  <ScheduleComponent
                    :schedules="serviceSchedule"
                    :hours="serviceHourIn"
                    :duration_id="serviceDurationId"
                    :duration="serviceDuration"
                  />
                </template>
                <div class="cost">
                  <div class="actions-box">
                    <div
                      :class="{ opened: state.opened }"
                      v-if="type != 'flight'"
                      class="down"
                      @click="openDetail"
                    >
                      <div class="icon">
                        <a-tooltip placement="bottom">
                          <template #title>
                            <span> {{ t('quote.label.view_detail') }}</span>
                          </template>
                          <icon-angle-down />
                        </a-tooltip>
                      </div>
                    </div>
                    <div class="actions">
                      <div class="action" v-if="type != 'flight'">
                        <div class="icon" @click="editHotel">
                          <a-tooltip placement="bottom">
                            <template #title>
                              <span> {{ t('quote.label.edit') }}</span>
                            </template>
                            <icon-edit-dark />
                          </a-tooltip>
                        </div>
                      </div>

                      <div class="action" v-if="type != 'flight'">
                        <div class="icon" @click="toggleAddServiceModal">
                          <a-tooltip placement="bottom">
                            <template #title>
                              <span> {{ t('quote.label.add_service') }}</span>
                            </template>
                            <icon-plus-square :style="{ fontSize: '18px' }" />
                          </a-tooltip>
                        </div>
                      </div>

                      <div class="action" v-if="type != 'flight'" style="display: none">
                        <div class="icon" @click="editFormSearch">
                          <a-tooltip placement="bottom">
                            <template #title>
                              <span> {{ t('quote.label.plusCheck') }}</span>
                            </template>
                            <icon-plus-cleck />
                          </a-tooltip>
                        </div>
                      </div>

                      <div class="action" v-if="type != 'flight'">
                        <div class="icon">
                          <a-tooltip placement="bottom">
                            <template #title>
                              <span> {{ t('quote.label.replace') }}</span>
                            </template>
                            <replace-service @edit-hotel="editHotel" v-if="type === 'service'" />
                            <replace-hotel @edit-hotel="editHotel" v-if="hotelTypeClass" />
                          </a-tooltip>
                        </div>
                      </div>
                      <div class="action">
                        <div class="icon" @click="removeServiceConfirm(service)">
                          <a-tooltip placement="bottom">
                            <template #title>
                              <span> {{ t('quote.label.delete_btn') }}</span>
                            </template>
                            <icon-trash-dark />
                          </a-tooltip>
                        </div>
                      </div>
                      <div class="action" v-if="type != 'flight'">
                        <div class="icon" @click="toggleModalOptional(service)">
                          <a-tooltip placement="bottom">
                            <template #title>
                              <span> {{ t('quote.label.text_optional') }}</span>
                            </template>
                            <icon-book />
                          </a-tooltip>
                        </div>
                      </div>
                    </div>
                    <div class="text" v-if="operation == 'passengers' && type != 'flight'">
                      ${{ displayPrice(priceTotal) }} <span>ADL</span>
                      <span
                        v-if="showCommissionBadge"
                        class="badge-warning ml-2"
                        style="font-size: 10px; padding: 1px 2px"
                      >
                        {{ t('global.label.with_commission') }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <hr v-if="state.opened" />
            <div v-if="state.opened" class="detail">
              <!--            <TransportComponent v-if="serviceType === 'tours'"/>-->
              <hotels-component
                v-if="hotelTypeClass"
                :service="service"
                :service-group="group"
                @remove-service="removeServiceConfirm"
                @edit-room-search="editServiceRoom"
              />
              <service-component :itinerary="serviceItinerary" v-else />
            </div>
          </div>
        </div>
      </div>
    </div>

    <service-ramove-confirm
      v-if="!!serviceSelected"
      :service="serviceSelected"
      :show-modal="!!serviceSelected"
      @cancel="serviceSelected = undefined"
      @close="serviceSelected = undefined"
      @ok="removeService"
    />

    <modal-itinerario-detail
      v-if="state.modalHotelDetail.isOpen"
      :hotel="hotelSelected"
      :service="serviceServiceSelected"
      :serviceDateOut="dayjs(service.date_out, 'DD/MM/YYYY').format('YYYY-MM-DD')"
      :title="serviceName"
      :type="type"
      :category="serviceCategory"
      :categoryName="serviceCategoryName"
      :show="state.modalHotelDetail.isOpen"
      @close="claseHotelDetailModal"
    />

    <ModalComponent :modalActive="state.openOptional" @close="toggleModalOptional">
      <template #body>
        <h3 class="title">{{ t('quote.label.optional_service') }}</h3>
        <div v-if="service.optional" class="description">
          {{ t('quote.label.service_marked_optional') }}
        </div>
        <div v-else class="description">
          {{ t('quote.label.service_marked_no_optional') }}
        </div>
      </template>
      <template #footer>
        <div class="footer">
          <button :disabled="false" class="cancel" @click="toggleModalOptional">
            {{ t('quote.label.cancel') }}
          </button>
          <button :disabled="false" class="ok" @click="updateOptionalState">
            {{ t('quote.label.yes_continue') }}
          </button>
        </div>
      </template>
    </ModalComponent>

    <ModalComponent :modalActive="state.modalDateChange.isOpen" @close="closeDateChangeModal">
      <template #body>
        <h3 class="title">{{ t('quote.label.confirm_date_change') }}</h3>
        <div class="description">
          <p>{{ t('quote.question.propagate_date_change') }}</p>
          <div class="selected-date-info" style="margin-top: 10px; font-weight: bold">
            <span>{{ t('quote.label.new_date') }}: </span>
            <span>{{ state.modalDateChange.date }}</span>
          </div>
        </div>
      </template>
      <template #footer>
        <div class="footer">
          <button :disabled="false" class="cancel" @click="confirmDateChange(false)">
            {{ t('quote.label.no_only_this') }}
          </button>
          <button :disabled="false" class="ok" @click="confirmDateChange(true)">
            {{ t('quote.label.yes_continue') }}
          </button>
        </div>
      </template>
    </ModalComponent>

    <ModalComponent
      :modalActive="isAddServiceModalOpen"
      class="modal-add-service-itinerary"
      @close="toggleAddServiceModal"
      width="90%"
    >
      <template #body>
        <div class="modal-add-service-container">
          <div class="header-title">
            {{ t('quote.label.add_service') }}
          </div>

          <AddServiceComponent
            :inModal="true"
            :defaultOpen="true"
            defaultCategory="tours"
            @updateFlagSearched="updateSearched"
            @update:selectedService="(tab: any) => (selectedAddServiceTab = tab)"
          />

          <!-- Results area moved from AddServiceComponent -->
          <div class="modal-results-area" v-if="showResults || isProcessingSearch">
            <div class="results-header" v-if="!isProcessingSearch && user_type_id !== 3">
              <h3 class="results-title">
                {{ t('quote.label.results') }} ({{ currentResults.length }})
              </h3>
            </div>

            <div class="loading-container" v-if="isProcessingSearch">
              <LoadingSkeleton :rows="4" />
            </div>

            <div class="results-list" v-else>
              <!-- Table layout for user_type_id === 3 -->
              <ServiceItemModal
                v-for="item in paginatedResults"
                :key="item.id"
                :service="item"
                :type="selectedAddServiceTab === 'hotels' ? 'hotel' : 'service'"
                :user-type="user_type_id"
              />
              <div v-if="paginatedResults.length === 0" class="no-results">
                {{ t('quotes.label.no_results_found') }}
              </div>

              <div class="results-pagination">
                <a-pagination
                  v-model:current="pagination.current"
                  v-model:pageSize="pagination.pageSize"
                  :total="pagination.total"
                  :show-size-changer="true"
                  :page-size-options="['10', '20', '50', '100']"
                  @change="handlePageChange"
                  :show-total="
                    (total: number, range: any) =>
                      `${range[0]}-${range[1]} ${t('quote.label.of')} ${total} ${t('quote.label.results').toLowerCase()}`
                  "
                />
              </div>
            </div>
          </div>
          <template v-if="currentResults.length === 0 && isSearched">
            <ErrorView
              status="404"
              :title="t('quote.error.no_results.title')"
              :subtitle="t('quote.error.no_results.subtitle')"
            />
          </template>
        </div>
      </template>
    </ModalComponent>

    <QuoteShiftServiceModal
      v-if="state.modalDateConflict.isOpen"
      :is-open="state.modalDateConflict.isOpen"
      mode="date_conflict"
      :insertion-date="state.modalDateConflict.date || ''"
      :previous-date="service.date_in"
      :conflicting-services="state.modalDateConflict.conflictingServices"
      @confirm="confirmDateConflict"
      @close="closeDateConflictModal"
    />
  </div>
</template>

<style lang="scss">
  .date-editable {
    position: relative;
    border-bottom: 2px dashed #eb5757;
    cursor: pointer;
    padding: 3px 0px;
    transition: all 0.3s;
    &:hover {
      background-color: rgba(235, 87, 87, 0.05);
    }
  }

  // Global style for date picker popup to ensure it appears above all elements
  .ant-picker-dropdown {
    z-index: 99999 !important;
  }

  // Date change modal styles
  :deep(.date-change-modal-content) {
    .modal-question {
      margin-bottom: 16px;
      font-size: 14px;
      color: rgba(0, 0, 0, 0.65);
      line-height: 1.6;
    }

    .selected-date-info {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 12px 16px;
      background: linear-gradient(135deg, #c00d0e 0%, #8b0a0b 100%);
      border-radius: 8px;
      margin-top: 12px;
      box-shadow: 0 2px 8px rgba(192, 13, 14, 0.2);

      .date-label {
        font-size: 13px;
        font-weight: 500;
        color: rgba(255, 255, 255, 0.9);
      }

      .date-value {
        font-size: 15px;
        font-weight: 600;
        color: #ffffff;
        letter-spacing: 0.5px;
      }
    }
  }

  :deep(.btn-propagate) {
    background: linear-gradient(135deg, #c00d0e 0%, #8b0a0b 100%);
    border: none;
    font-weight: 500;
    box-shadow: 0 2px 8px rgba(192, 13, 14, 0.3);
    transition: all 0.3s ease;

    &:hover {
      background: linear-gradient(135deg, #a00b0c 0%, #6f0809 100%);
      box-shadow: 0 4px 12px rgba(192, 13, 14, 0.4);
      transform: translateY(-1px);
    }
  }

  :deep(.btn-no-propagate) {
    border-color: #d9d9d9;
    color: rgba(0, 0, 0, 0.65);
    font-weight: 500;
    transition: all 0.3s ease;

    &:hover {
      border-color: #c00d0e;
      color: #c00d0e;
    }
  }

  .on-request {
    display: flex;
    align-items: center;
    gap: 5px;
  }

  .activeserviceselected {
    background-color: #d9d9d9 !important;
  }

  /*
  .modal-eliminarservicio .modal-inner {
    max-width: 435px;
  }
  */

  .modal-add-service-itinerary > .modal-inner {
    max-width: 1400px;
    width: 1024px;
    max-height: 80vh;
    overflow: hidden;
    padding: 0;
    display: flex;
    flex-direction: column;
    border-radius: 12px;
    border: none;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);

    > .modal-close {
      display: none !important;
    }

    > .modal-body {
      flex-grow: 1;
      display: flex;
      flex-direction: column;
      overflow: hidden;
    }
  }

  .modal-add-service-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 24px;
    background: #ffffff;
    color: #262626;
    border-bottom: 1px solid #f0f0f0;

    .header-left {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .header-icon {
      color: #c00d0e;
      font-size: 22px;
    }

    h2 {
      margin: 0;
      font-size: 20px;
      font-weight: 700;
      color: #262626;
      letter-spacing: -0.3px;
      text-transform: none;
    }
  }

  .modal-add-service-container {
    flex-grow: 1;
    overflow: auto;
    display: flex;
    flex-direction: column;

    .header-title {
      padding: 20px;
      display: block;
      position: relative;
      font-size: 16px;
      text-align: center;
      background: #919191;
      color: #fff;
      font-weight: bold;
      text-transform: uppercase;
    }
  }

  .modal-results-area {
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    padding: 30px; // Increased padding
    overflow: visible;
    background: #f8f9fa;
    gap: 20px; // Add gap between header and list

    &.placeholder {
      justify-content: center;
      align-items: center;
      background: #f0f2f5;
      min-height: 300px; // Increased min-height

      .no-results {
        padding: 0;
        p {
          font-size: 16px;
          color: #8c8c8c;
        }
      }
    }

    .results-header {
      margin-bottom: 0;
      .results-title {
        font-size: 24px !important;
        margin: 0;
        font-weight: 700;
        color: #262626;
      }
    }

    .loading-container {
      padding: 20px;
      background: #fff;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    .results-list {
      flex-grow: 1;
      overflow: visible;

      // Ant Design Table Styles
      .services-table {
        background: #fff;
        border-radius: 8px;
        overflow: hidden;

        .ant-table {
          font-size: 14px;
        }

        .ant-table-thead > tr > th {
          background: #bfbfbf;
          color: #fff;
          font-weight: 700;
          text-transform: uppercase;
          font-size: 14px;
          border-bottom: none;
          padding: 12px 16px;

          &::before {
            display: none; // Remove default Ant Design column separator
          }
        }

        .ant-table-tbody > tr {
          border-bottom: 1px solid #f0f0f0;
          transition: background-color 0.3s;

          &:hover {
            background-color: #fafafa;
          }

          > td {
            padding: 16px 10px;
            vertical-align: middle;
            border-bottom: 1px solid #f0f0f0;
          }

          &:last-child > td {
            border-bottom: none;
          }
        }

        // Description column styles
        .desc-column {
          display: flex;
          flex-direction: column;
          gap: 6px;

          .desc-title {
            font-size: 14px;
            color: #595959;
            font-weight: 500;
          }

          .desc-actions {
            display: flex;
            gap: 15px;
            font-size: 12px;
            flex-wrap: wrap;

            .action-link {
              cursor: pointer;
              display: flex;
              align-items: center;
              gap: 4px;
              text-decoration: underline;
              transition: color 0.2s;

              &:hover {
                color: #c00d0e;
              }
            }
          }
        }

        // Rate column styles
        .rate-column {
          display: flex;
          flex-direction: column;
          align-items: flex-start;
          gap: 4px;

          .rate-option {
            display: flex;
            align-items: center;
            gap: 6px;

            .radio-circle {
              width: 14px;
              height: 14px;
              border-radius: 50%;
              border: 1px solid #1890ff;
              display: flex;
              align-items: center;
              justify-content: center;

              &.selected .inner-circle {
                width: 8px;
                height: 8px;
                border-radius: 50%;
                background-color: #1890ff;
              }
            }

            .rate-label {
              font-size: 12px;
              color: #595959;
              font-weight: 600;
            }
          }

          .rate-price {
            font-size: 16px;
            font-weight: 700;
            color: #595959;
            margin-left: 20px;
            display: flex;
            align-items: center;
            gap: 5px;

            .badge-rq {
              background-color: #c00d0e;
              color: #fff;
              font-size: 10px;
              padding: 1px 4px;
              border-radius: 3px;
              font-weight: bold;
            }
          }
        }

        // Add button styles
        .btn-add-table {
          background-color: #eb5757;
          border: none;
          color: white;
          width: 45px;
          height: 35px;
          border-radius: 6px;
          font-size: 18px;
          cursor: pointer;
          display: flex;
          align-items: center;
          justify-content: center;
          transition: background-color 0.3s;

          &:hover {
            background-color: #c00d0e;
          }

          &:active {
            transform: scale(0.95);
          }
        }

        // Empty state
        .ant-empty-description {
          color: #8c8c8c;
          font-style: italic;
        }
      }

      padding-right: 0;
      display: flex; // enable flex for gap
      flex-direction: column;
      gap: 16px; // Add gap between items

      .no-results {
        text-align: center;
        padding: 60px;
        color: #8c8c8c;
        font-style: italic;
        font-size: 15px;
      }
    }

    // Pagination styles
    .results-pagination {
      display: flex;
      justify-content: center;
      padding: 20px 0;
      margin-top: 20px;
      border-top: 1px solid #f0f0f0;

      :deep(.ant-pagination) {
        display: flex;
        align-items: center;
        gap: 8px;

        .ant-pagination-item {
          border-radius: 6px;
          border-color: #d9d9d9;
          transition: all 0.3s ease;

          &:hover {
            border-color: #c00d0e;

            a {
              color: #c00d0e;
            }
          }

          &.ant-pagination-item-active {
            border-color: #c00d0e;
            background: #c00d0e;

            a {
              color: #fff;
            }
          }
        }

        .ant-pagination-prev,
        .ant-pagination-next {
          border-radius: 6px;

          &:hover button {
            color: #c00d0e;
            border-color: #c00d0e;
          }
        }

        .ant-pagination-options {
          .ant-select-selector {
            border-radius: 6px;
            transition: all 0.3s ease;

            &:hover {
              border-color: #c00d0e;
            }
          }

          .ant-select-focused .ant-select-selector {
            border-color: #c00d0e;
            box-shadow: 0 0 0 2px rgba(192, 13, 14, 0.1);
          }
        }

        .ant-pagination-total-text {
          color: #595959;
          font-size: 14px;
        }
      }
    }
  }

  .action .icon {
    cursor: pointer;
    height: 20px;
  }

  // Targeted Notifications Styles
  .has-notifications-active {
    // border: 2px solid #eb5757 !important; // Removed as requested
    // box-shadow: 0 0 10px rgba(235, 87, 87, 0.2); // Removed as requested
    position: relative;
    transition: all 0.3s ease;
  }

  .service-notification-wrapper {
    position: absolute;
    top: -12px;
    right: -12px;
    z-index: 10;
  }

  .notification-badge-trigger {
    background: #eb5757;
    color: #ffffff;
    width: 24px;
    height: 24px;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    position: relative;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    transition: transform 0.2s ease;

    &:hover {
      transform: scale(1.1);
    }

    .question-icon {
      font-size: 14px;
      font-weight: bold;
    }

    .badge-count {
      position: absolute;
      top: -8px;
      right: -8px;
      background: #ffffff;
      color: #eb5757;
      border: 1px solid #eb5757;
      border-radius: 50%;
      min-width: 16px;
      height: 16px;
      font-size: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: bold;
      padding: 0 2px;
    }
  }

  .service-notifications-list {
    max-width: 280px;
    max-height: 400px;
    overflow-y: auto;

    .service-notification-item {
      padding: 8px 0;

      .notif-header {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 4px;

        .notif-title {
          font-weight: 700;
          font-size: 11px;
          text-transform: uppercase;
          color: #333;
        }

        .user-code {
          background: #ffeaea;
          color: #eb5757;
          font-size: 10px;
          padding: 1px 6px;
          border-radius: 4px;
          margin-left: auto;
          font-weight: 700;
        }
      }

      .notif-desc {
        font-size: 13px;
        color: #555;
        line-height: 1.4;
        margin-bottom: 6px;
      }

      .notif-footer {
        display: flex;
        justify-content: space-between;
        font-size: 11px;
        color: #888;

        .user-name {
          font-weight: 600;
        }
      }

      hr {
        margin: 10px 0;
        border: 0;
        border-top: 1px solid #eee;
      }
    }
  }

  .openSideBar {
    .quotes-itineraries {
      .quotes-itineraries-internal {
        .quotes-itineraries-content {
          .quotes-itineraries-body {
            .title-box {
              .left {
                flex-basis: 48% !important;

                .title {
                  gap: 15px;
                }

                .extra-hotel {
                  flex-grow: 0;
                  flex-basis: 100%;
                  justify-content: flex-end;
                  gap: 10px;
                }
              }
            }
          }

          .quotes-itineraries-header {
            .right {
              gap: 12px;
            }
          }

          .quotes-itineraries-body .title-box .right {
            .cost {
              padding-left: 14px;

              .actions-box {
                gap: 4px;

                .text {
                  padding-left: 10px;
                }

                .actions {
                  gap: 10px;
                }
              }
            }
          }
        }
      }
    }
  }

  .quotes-itineraries {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
    border-radius: 6px;
    border: 2px solid #e9e9e9;
    margin-bottom: 24px;

    // Add border for optional service
    &.service-optional {
      border: 2px dashed #eb5757 !important;
    }

    &.ignore {
      opacity: 0.5;
      filter: blur(1.5px);
      pointer-events: none; /* Evita clics en los que no se editan */
      user-select: none;
    }

    &.isEditing {
      opacity: 1;
      filter: blur(0);
      pointer-events: auto;
      box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }

    .icon-right {
      margin-left: 10px;
    }

    .quotes-itineraries-internal {
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      gap: 10px;
      align-self: stretch;

      .quotes-itineraries-content {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
        align-self: stretch;

        .quotes-itineraries-header {
          display: flex;
          padding: 5px 31px 0 6px;
          align-items: center;
          align-self: stretch;

          &.alert-improvements {
            border-radius: 6px 6px 0 0;
            padding: 8px 12px 12px 12px;
          }

          .left {
            display: flex;
            padding: 10px 10px 10px 21px;
            align-items: center;
            gap: 16px;
            align-self: stretch;

            .move-icon {
              width: 20px;
              height: 20px;
              cursor: move;
            }

            .date {
              display: flex;
              /*width: 200px;*/
              align-items: center;
              gap: 8px;
              align-self: stretch;
              position: relative;

              .icon-container {
                display: flex;
                width: auto;
                align-items: center;
                gap: 3px;
                flex-shrink: 0;
                align-self: stretch;

                &.end {
                  width: 56px;
                }

                .icon {
                  width: 23px;
                  height: 23px;
                  flex-shrink: 0;
                  cursor: pointer;
                }

                .text {
                  color: #eb5757;
                  font-size: 14px;
                  font-style: normal;
                  font-weight: 700;
                  line-height: 21px;
                  letter-spacing: 0.21px;
                  font-family: 'Montserrat', sans-serif;
                }
              }

              .date-text {
                color: #3d3d3d;
                font-size: 14px;
                font-style: normal;
                font-weight: 700;
                line-height: 21px;
                letter-spacing: 0.21px;
              }
            }

            .days {
              color: #3d3d3d;
              text-align: right;
              font-size: 14px;
              font-style: normal;
              font-weight: 700;
              line-height: 21px; /* 150% */
              letter-spacing: 0.21px;

              .pipe {
                color: #eb5757;
                font-size: 14px;
                font-style: normal;
                font-weight: 700;
                line-height: 21px;
                letter-spacing: 0.21px;
              }
            }
          }

          .right {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 53px;
            flex: 1 0 0;
            align-self: stretch;

            .people {
              display: flex;
              align-items: center;
              gap: 5px;
              color: #3d3d3d;
              text-align: right;
              font-size: 12px;
              font-style: normal;
              font-weight: 500;
              line-height: 19px; /* 158.333% */
              letter-spacing: 0.18px;
            }

            .tag {
              display: flex;
              flex-direction: column;
              justify-content: center;
              align-items: center;
              gap: 10px;

              .tag-button {
                display: flex;
                padding: 2px 16px;
                justify-content: center;
                align-items: center;
                gap: 10px;
                border-radius: 6px;
                background: #ff9494;
                color: #ffffff;
                text-align: center;
                font-size: 12px;
                font-style: normal;
                font-weight: 700;
                line-height: 19px;
                letter-spacing: 0.18px;

                &.private {
                  background-color: #ff9494;
                }

                &.shared {
                  background-color: #bdbdbd;
                }
              }
            }

            .status {
              display: flex;
              width: 25px;
              height: 25px;
              justify-content: center;
              align-items: center;

              .icon {
                width: 25px;
                height: 25px;
                flex-shrink: 0;
                cursor: pointer;
              }
            }
          }
        }

        .quotes-itineraries-body {
          display: flex;
          padding: 25px 31px;
          flex-direction: column;
          align-items: flex-start;
          gap: 10px;
          flex: 1 0 0;
          align-self: stretch;
          border-radius: 6px;
          background: #fafafa;

          .title-box {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex: 1 0 0;
            align-self: stretch;

            .left {
              display: flex;
              align-items: center;
              gap: 15px;
              align-self: stretch;
              flex-basis: 64%;

              .icon {
                width: 31px;
                height: 31px;
                flex-shrink: 0;
              }

              .title {
                color: #3d3d3d;
                font-size: 16px;
                font-style: normal;
                font-weight: 400;
                line-height: 23px;
                letter-spacing: -0.24px;
                width: 100%;
                padding-left: 0;
                padding-right: 0;
                display: flex;
                gap: 26px;
                align-items: center;
              }

              .extra-hotel {
                display: flex;
                gap: 26px;

                span {
                  color: #eb5757;
                  font-size: 14px;
                  font-style: normal;
                  font-weight: 700;
                  line-height: 21px; /* 150% */
                  letter-spacing: 0.21px;
                }
              }
            }

            .right {
              height: 31px;
              display: flex;
              flex-basis: 30%;
              justify-content: flex-end;

              .cost {
                display: inline-flex;
                justify-content: center;
                align-items: center;
                gap: 22px;
                padding-left: 40px;

                .actions-box {
                  display: flex;
                  padding-left: 0;
                  align-items: center;
                  gap: 22px;

                  .down {
                    display: flex;
                    flex-direction: column;
                    align-items: flex-start;
                    gap: 10px;
                    cursor: pointer;
                    margin-right: 10px;

                    .icon {
                      display: flex;
                      width: 16px;
                      height: 10px;
                      justify-content: center;
                      align-items: center;

                      svg {
                        width: 24px;
                        height: 24px;
                        flex-shrink: 0;
                      }
                    }

                    &.opened {
                      transform: rotate(180deg);
                      transition: all 0.3s ease-in;
                    }
                  }

                  .actions {
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    gap: 18px;

                    .action {
                      display: flex;
                      width: 17px;
                      height: 17px;
                      justify-content: center;
                      align-items: center;

                      svg {
                        width: 17px;
                        height: 17px;
                        flex-shrink: 0;
                      }
                    }
                  }

                  .text {
                    color: #eb5757;
                    text-align: right;
                    font-size: 18px;
                    font-style: normal;
                    font-weight: 700;
                    line-height: 18px;
                    letter-spacing: -0.18px;

                    span {
                      font-size: 12px;
                      display: block;
                      align-items: center;
                    }
                  }
                }
              }

              .time-picker {
                display: inline-flex;
                padding: 4px 12px;
                align-items: center;
                border-radius: 6px;
                border: 0;
                background: inherit;

                .input-dp {
                  display: flex;
                  width: 47.5px;
                  padding: 1px 0;
                  align-items: center;
                  gap: 4px;
                }

                .arrow {
                  display: flex;
                  width: 32px;
                  padding: 1px 8px;
                  justify-content: center;
                  align-items: center;
                  gap: 10px;
                  align-self: stretch;
                  opacity: 0.8700000047683716;
                }

                .clock {
                  display: flex;
                  width: 31px;
                  padding: 0 8px;
                  justify-content: center;
                  align-items: center;
                  gap: 10px;
                  align-self: stretch;
                  opacity: 0.8700000047683716;
                }
              }
            }
          }

          hr {
            height: 1px;
            align-self: stretch;
            stroke-width: 1px;
            margin: 1em 0 0;
            stroke: #c4c4c4;
          }

          .detail {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 5px;
            align-self: stretch;
          }
        }
      }
    }
  }
  .service-item-container {
    position: relative;
  }
</style>
