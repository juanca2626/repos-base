<script lang="ts" setup>
  import { computed, reactive, ref, toRef } from 'vue';
  import moment from 'moment';

  import ServiceComponent from '@/quotes/components/details/ServiceComponent.vue';
  import ModalComponent from './global/ModalComponent.vue';
  import ModalItinerarioDetail from '@/quotes/components/modals/ModalItinerarioDetail.vue';
  import HotelsComponent from '@/quotes/components/details/HotelsComponent.vue';
  import ScheduleComponent from '@/quotes/components/info/ScheduleComponent.vue';
  import ReplaceService from '@/quotes/components/ReplaceService.vue';
  import ReplaceHotel from '@/quotes/components/ReplaceHotel.vue';
  import { useSiderBarStore } from '@/quotes/store/sidebar';
  import CheckBoxComponent from '@/quotes/components/global/CheckBoxComponent.vue';
  import IconCalendarLigth from '@/quotes/components/icons/IconCalendarLight.vue';
  import HotelPromotions from '@/quotes/components/HotelPromotions.vue';
  import type {
    GroupedServices,
    QuoteService,
    QuoteServiceServiceImport,
  } from '@/quotes/interfaces/quote.response';
  import IconHotelsDark from '@/quotes/components/icons/IconHotelsDark.vue';
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
  import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
  import IconOnRequest from '@/quotes/components/icons/IconOnRequest.vue';
  import useLoader from '@/quotes/composables/useLoader';
  import type { Service } from '@/quotes/interfaces/services';
  import dayjs from 'dayjs';

  import { useI18n } from 'vue-i18n';
  import { useLanguagesStore } from '@/stores/global';
  import { getPriceWithCommission, hasCommission } from '@/utils/price';
  import { getUserType } from '@/utils/auth';
  import { useQuoteStore } from '@/quotes/store/quote.store';
  import { storeToRefs } from 'pinia';
  const quoteStore = useQuoteStore();
  const { marketList } = storeToRefs(quoteStore);
  const user_type_id = parseInt(getUserType(), 10);

  // ✅ usar directamente el cliente guardado en el store
  const client = computed(() => marketList?.value.data || null);

  // ✅ Función para mostrar el precio con comisión
  const displayPrice = (price: number) => getPriceWithCommission(price, client.value, user_type_id);
  const showCommissionBadge = computed(() => hasCommission(client.value, user_type_id));

  const { t } = useI18n();
  const languageStore = useLanguagesStore();

  const { showIsLoading, closeIsLoading } = useLoader();
  const storeSidebar = useSiderBarStore();

  // const { showForm, toggleForm } = usePopup();

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

  moment.locale('pt');

  const selectItem = (e: boolean) => {
    groupedService.value.selected = e;
  };

  const getServiceName = () => {
    if (type.value === 'service') {
      return service.value.service?.service_translations[0].name;
    } else if (type.value === 'flight') {
      let flight: string = '';
      if (service.value.code_flight == 'AEC' || service.value.code_flight == 'AECFLT') {
        flight = '[' + t('quote.label.flight') + ' - ' + t('quote.label.national') + ']';
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

  const serviceItinerary = computed(() => getServiceItinerary());
  const serviceSchedule = computed(() => getServiceSchedule());

  const serviceType = computed(() => getServiceType());
  const serviceTypeName = computed(() => getServiceTypeName());
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
    return moment(date, 'DD/MM/YYYY');
  };

  const dateIn = computed(() => {
    return getDateInstance(getDateIn()).format('MMM D, YYYY');
    // return props.service.date_in
  });
  const dateDay = computed(() => {
    return getWeekDay(getDateInFormat().toString());
    // return getDateInstance(getDateIn()).format("dddd");
  });

  const getWeekDay = (dateValue: string = '') => {
    if (!dateValue) return '';

    return dayjs(dateValue).format('dddd');
    // const WEEKDAYS = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado']
    // const indexDay = Number(dayjs(dateValue).format('d'))

    // return WEEKDAYS[indexDay]
  };

  const dateOut = computed(() => {
    return getDateInstance(getDateOut()).format('MMM D, YYYY');
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
      return (service.value.import as QuoteServiceServiceImport)?.total_amount ?? 0;
    } else {
      return group.value
        .map((s) => s.import_amount?.price_ADL ?? 0)
        .reduce((a, b) => Number(a) + Number(b), 0);
    }
  });

  // Hotels
  const hotelNightNumber = computed(() => {
    const dateIn = moment(getDateIn(), 'DD/MM/YYYY');
    const dateOut = moment(getDateOut(), 'DD/MM/YYYY');

    return dateOut.diff(dateIn, 'days');
  });

  const hotelRoomsNumber = computed(() => group.value.length ?? 0);

  const state = reactive({
    opened: false,
    modalHotelDetail: {
      isOpen: false,
    },
    openOptional: false,
    openDelete: false,
  });
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

  const toggleModalOptional = () => {
    state.openOptional = !state.openOptional;
  };

  const openDetail = () => {
    state.opened = !state.opened;
  };

  const {
    serviceSelected: serviceSelectedQuote,
    operation,
    setServiceEdit,
    removeQuoteServices,
    updateServiceOptionalState,
  } = useQuote();

  const serviceSelected = ref<QuoteService>();

  const removeServiceConfirm = async (service: QuoteService) => {
    serviceSelected.value = service;
  };
  const editServiceRoom = async (service: QuoteService) => {
    setServiceEdit(service);
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

  const updateOptionalState = () => {
    let quoteServiceId: number[];

    if (groupedService.value?.type === 'service') {
      quoteServiceId = [service.value.id];
    } else {
      quoteServiceId = group.value.map((s) => s.id);
    }

    updateServiceOptionalState({
      optional: service.value.optional,
      quote_service_id: quoteServiceId,
    });

    toggleModalOptional();
  };

  // Services

  const serviceServiceSelected = ref<Service>();
  const openServiceModalItinerary = (service: Service) => {
    showIsLoading();
    serviceServiceSelected.value = service;
    state.modalHotelDetail.isOpen = true;
    closeIsLoading();
  };

  // const changeServices = (args) => {
  //   console.log(args);
  //   showForm.value = false;
  // };

  const classServiceSelectedQuote = computed(() => {
    if (Object.keys(serviceSelectedQuote.value).length > 0) {
      if (service.value.id == serviceSelectedQuote.value.service.id) {
        return true;
      }
    }
    return false;
  });
</script>

<template>
  <div :id="item" :total="totalService" class="itemExtension">
    <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path
        d="M20 13.333L26.6667 19.9997L20 26.6663"
        stroke="#979797"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
      />
      <path
        d="M5.33398 5.33301V14.6663C5.33398 16.0808 5.89589 17.4374 6.89608 18.4376C7.89628 19.4378 9.25283 19.9997 10.6673 19.9997H26.6673"
        stroke="#979797"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
      />
    </svg>

    <div :class="{ 'service-optional': service.optional }" class="quotes-itineraries">
      <div class="quotes-itineraries-internal">
        <div class="quotes-itineraries-content">
          <a-space v-if="service.validations.length" direction="vertical" style="width: 100%">
            <a-alert type="error" show-icon>
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
          <div class="quotes-itineraries-header alert-improvements border-0" v-if="hotelTypeClass">
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
              <div class="date">
                <div class="icon-container">
                  <div class="icon">
                    <icon-calendar-ligth />
                  </div>
                  <div class="text">{{ t('quote.label.start') }}:</div>
                </div>
                <div class="date-text">{{ dateIn }}</div>
              </div>
              <div v-if="getHotelTypeClass() !== null" class="date">
                <div class="icon-container end">
                  <div class="icon">
                    <icon-calendar-ligth />
                  </div>
                  <div class="text">{{ t('quote.label.end') }}:</div>
                </div>
                <div class="date-text">{{ dateOut }}</div>
              </div>
              <div class="days">
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
                <div v-if="type === 'service' && serviceType !== 'NA'" class="tag-button">
                  {{ serviceTypeName }}
                </div>
              </div>
              <div class="status">
                <div
                  v-if="hotelTypeClass"
                  class="icon"
                  @click="openHotelDetailModal(service.hotel!)"
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
                  @click="openServiceModalItinerary(service.service!)"
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
              activeserviceselected: classServiceSelectedQuote,
              bgHotel: service.optional ? false : hotelTypeClass,
            }"
          >
            <div class="title-box">
              <div class="left">
                <CheckBoxComponent @checked="selectItem" />
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
                  <ScheduleComponent :schedules="serviceSchedule" :hours="serviceHourIn" />
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
                        >{{ t('global.label.with_commission') }}</span
                      >
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

    <ModalComponent
      :modalActive="state.openOptional"
      class="modal-eliminarservicio"
      @close="toggleModalOptional"
    >
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
  </div>
</template>

<style lang="scss">
  .on-request {
    display: flex;
    align-items: center;
    gap: 5px;
  }

  .activeserviceselected {
    background-color: #d9d9d9 !important;
  }

  .itemExtension {
    display: flex;
    padding: 0;
    gap: 5px;
    width: 100%;

    .quotes-itineraries {
      margin-top: 0;
      margin-bottom: 15px;
      width: 97%;
    }
  }

  .modal-eliminarservicio .modal-inner {
    max-width: 435px;
  }

  .action .icon {
    cursor: pointer;
    height: 20px;
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
            background: #fff;

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
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
    border-radius: 6px;
    border: 2px solid #e9e9e9;
    margin-bottom: 24px;

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
                display: flex;
                align-items: center;
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
                    line-height: 25px;
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

            & > div {
              width: 100%;
            }
          }
        }
      }
    }
  }
</style>
