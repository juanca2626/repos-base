<script lang="ts" setup>
  import IconLeftArrowTab from '@/quotes/components/icons/IconLeftArrowTab.vue';
  import ModalItinerarioDetail from '@/quotes/components/modals/ModalItinerarioDetail.vue';
  import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
  import { computed, onMounted, reactive, ref, watch, watchEffect } from 'vue';
  import moment from 'moment';
  import { useLanguagesStore } from '@/stores/global';
  import { useQuote } from '@/quotes/composables/useQuote';
  import IconClock from '@/quotes/components/icons/IconClock.vue';
  import { useQuoteServices } from '@/quotes/composables/useQuoteServices';
  import type { GroupedServices } from '@/quotes/interfaces/quote.response';
  import IconAlert from '@/quotes/components/icons/IconAlert.vue';
  import { getHotelById } from '@/quotes/helpers/get-hotel-by-id';
  import useLoader from '@/quotes/composables/useLoader';
  import type { Hotel } from '@/quotes/interfaces';
  import { DateTime } from 'luxon';

  import type { Service } from '@/quotes/interfaces/services';
  import dayjs from 'dayjs';
  import { storeToRefs } from 'pinia';
  import { useI18n } from 'vue-i18n';

  // const { getLang } = useQuoteTranslations();

  const { t } = useI18n();

  const { quote, quoteCategories, selectedCategory } = useQuote();
  const { showIsLoading, closeIsLoading } = useLoader();
  // const { showForm, toggleForm } = usePopup();

  const languageStore = useLanguagesStore();

  interface Props {
    groupedService: GroupedServices;
    selected?: boolean;
  }

  defineProps<Props>();

  // Composable
  const { getServiceDetails } = useQuoteServices();
  const serviceDetail = ref<ServiceDetailResponse>();

  // const groupedService = toRef(props, 'groupedService');
  const hotelSelected = ref<Hotel>();

  /*const type = computed(() => groupedService.value.type);
const service = computed(() => groupedService.value.service);
const group = computed(() => groupedService.value.group);*/

  type Nullable<T> = T | null;

  interface State {
    startDate: Nullable<DateTime>;
  }

  interface Event {
    start: number;
    end: number;
    startmin: number;
    endmin: number;
    title: string;
    description?: string;
    type: string;
    date_in: number;
    id: number;
    optional: number;
    service: [];
  }

  interface Events {
    [key: string]: Event[];
  }

  // Services

  const serviceServiceSelected = ref<Service>();
  const openServiceModalItinerary = (service: Service) => {
    showIsLoading();
    state.modalHotelDetail.date_out = service.date_out;
    serviceServiceSelected.value = service.service;
    if (service.type === 'service') {
      state.getServiceCategory =
        service.service?.service_sub_category.service_categories.id.toString() ?? '';
      state.serviceCategoryName =
        service.service?.service_sub_category.service_categories.translations[0].value.toString() ??
        '';
    }
    state.type = service.type;
    state.serviceName = getServiceName(service);
    state.modalHotelDetail.isOpen = true;

    closeIsLoading();
  };

  const getServiceName = (itemService) => {
    if (itemService.type === 'service') {
      return itemService.service?.service_translations[0].name;
    } else if (itemService.value === 'flight') {
      let flight: string = '';
      if (itemService.code_flight == 'AEC' || itemService.code_flight == 'AECFLT') {
        flight = '[' + t('quote.label.flight') + ' - ' + t('quote.label.national') + ']';
      }
      if (itemService.code_flight == 'AEI' || itemService.code_flight == 'AEIFLT') {
        flight = '[' + t('quote.label.flight') + ' - ' + t('quote.label.international') + ']';
      }

      if (itemService.origin != '' && itemService.origin != null) {
        flight += t('quote.label.origin') + ': ' + itemService.origin;
      }

      if (
        itemService.origin != '' &&
        itemService.origin != null &&
        itemService.destiny != '' &&
        itemService.destiny != null
      ) {
        flight += ' / ';
      }

      if (itemService.destiny != '' && itemService.destiny != null) {
        flight += ' ' + t('quote.label.destiny') + ': ' + itemService.destiny;
      }

      return flight;
    } else {
      return itemService.hotel?.name;
    }
  };

  // Hotel
  const openHotelDetailModal = async (hotel: Hotel) => {
    showIsLoading();
    //console.log(hotel);
    hotelSelected.value = await getHotelById(hotel.hotel.id, languageStore.currentLanguage);
    state.type = hotel.type;
    state.serviceName = getServiceName(hotel);
    state.getServiceCategory = '';
    state.serviceCategoryName = '';
    //console.log(state);
    state.modalHotelDetail.isOpen = true;
    closeIsLoading();
  };

  const claseHotelDetailModal = () => {
    state.modalHotelDetail.isOpen = false;
  };

  const changeTab = (tab: number) => {
    selectedCategory.value = tab;
    style.splice(0, style.length);
    dataMap();
  };

  const state: State = reactive({
    startDate: null,
    modalHotelDetail: {
      isOpen: false,
      date_out: '',
    },
    getServiceCategory: '',
    serviceCategoryName: '',
    monthLong: '',
    weekYear: '',
    type: '',
    serviceName: '',
  });

  const calendar = computed(() => {
    if (state.startDate === null) return { calendar: {} };
    let currentDay = state.startDate;
    let headerDays = [];
    let hours = [];

    for (let i = 0; i < 7; i++) {
      headerDays.push({
        name: currentDay.plus({ days: i }).toFormat('EEEE'),
        number: currentDay.plus({ days: i }).toFormat('dd'),
        month: currentDay.plus({ days: i }).toFormat('MM'),
      });
    }

    for (let i = 5; i <= 20; i++) {
      hours.push(('0' + i).slice(-2) + ':00');
    }

    return { headerDays, hours };
  });

  const current = () => {
    if (state.startDate === null) return { calendar: {} };
    let currentDay = state.startDate;
    state.startDate = currentDay.minus({ days: 0 });
  };

  const before = () => {
    if (state.startDate === null) return { calendar: {} };
    let currentDay = state.startDate;
    state.startDate = currentDay.minus({ days: 7 });
  };

  const after = () => {
    if (state.startDate === null) return { calendar: {} };
    let currentDay = state.startDate;
    state.startDate = currentDay.plus({ days: 7 });
  };

  let listEvents = [];

  const addDaysToDate = (date, days) => {
    //let res = new Date(date)
    date.setDate(date.getDate() + days);
    return date.toLocaleDateString();
  };

  const addEvents = (
    start,
    end,
    startmin,
    endmin,
    title,
    description,
    type,
    date_in,
    idService,
    optional,
    service
  ) => {
    const val = {
      start: start,
      end: end,
      startmin: startmin,
      endmin: endmin,
      title: title,
      description: description,
      type: type,
      date_in: date_in,
      id: idService,
      optional: optional,
      service: service,
    };

    listEvents[date_in] = listEvents[date_in] ? [...listEvents[date_in], val] : [val];

    //return listEvents;
  };

  const currentService = (
    day,
    currentTurns,
    date_in,
    title,
    description,
    type,
    idService,
    optional,
    service
  ) => {
    let totalTurns, start_time, end_time;
    totalTurns = currentTurns.detail.length - 1;
    start_time = currentTurns.detail[0].start_time.split(':');
    end_time = currentTurns.detail[totalTurns].end_time.split(':');

    let tmpDate = addDaysToDate(
      new Date(date_in[2] + '-' + date_in[1] + '-' + date_in[0]),
      day
    ).split('/');

    let tmpdate_in = tmpDate[1] + tmpDate[0];

    addEvents(
      start_time[0],
      end_time[0],
      start_time[1],
      end_time[1],
      title,
      description,
      type,
      tmpdate_in,
      idService,
      optional,
      service
    );
    current();
  };

  const dataMap = async () => {
    //console.log(quoteCategories.value);

    listEvents.splice(0, listEvents.length);
    showIsLoading();
    quoteCategories.value.forEach((elem) => {
      if (selectedCategory.value === elem.type_class_id) {
        //let totalServices  = elem.services.find((element) => element.service.service.id > 0)

        elem.services.forEach(async (c) => {
          if (c.extension_id) {
            c.extensions.forEach(async (ex) => {
              currentMapService(ex);
            });
          } else {
            currentMapService(c);
          }
        });
      }
    });
  };

  const currentMapService = async (c) => {
    let date_in = c.service.date_in.split('/');
    let optional = c.service.optional;

    let start,
      end,
      startmin,
      endmin,
      title,
      description,
      type,
      start_time,
      end_time,
      currentTurns,
      idService,
      endServiceDetail;
    description = ' ';

    if (c.service.service) {
      title = c.service.service.service_translations[0].name;
      type = c.type;
      idService = c.service.service.id;

      serviceDetail.value = await getServiceDetails(
        idService,
        c.service.date_in_format,
        c.service.adult ?? 1,
        c.service.child ?? 0
      );

      currentTurns = serviceDetail.value.operations.turns[0].find((element) => element.day > 1);

      if (currentTurns) {
        currentService(
          currentTurns.day,
          currentTurns,
          date_in,
          title,
          description,
          type,
          idService,
          optional,
          c.service
        );

        start_time = '';
        end_time = '';
      }

      if (serviceDetail.value.operations.turns[0][0].detail.length > 0) {
        endServiceDetail = serviceDetail.value.operations.turns[0][0].detail.length - 1;
        start_time = serviceDetail.value.operations.turns[0][0].detail[0].start_time.split(':');
        end_time =
          serviceDetail.value.operations.turns[0][0].detail[endServiceDetail].end_time.split(':');
      } else {
        start_time = serviceDetail.value.operations.turns[0][0].departure_time.split(':');
        end_time = serviceDetail.value.operations.turns[0][0].departure_time.split(':');
        end_time[0] = Number(end_time[0]) + Number(1);
      }

      if (start_time[0] == 0) {
        start_time.splice(0, start_time.length);
        start_time = c.service.service.schedules[0].services_schedule_detail[0].monday.split(':');
      }

      start = start_time[0];
      startmin = start_time[1];
      end = end_time[0];
      endmin = end_time[1];
    } else if (c.service.type === 'flight') {
      let flight: string = '';
      if (c.service.code_flight == 'AEC' || c.service.code_flight == 'AECFLT') {
        flight = '[' + t('quote.label.flight') + ' - ' + t('quote.label.national') + ']';
      }
      if (c.service.code_flight == 'AEI' || c.service.code_flight == 'AEIFLT') {
        flight = '[' + t('quote.label.flight') + ' - ' + t('quote.label.international') + ']';
      }

      if (c.service.origin != '' && c.service.origin != null) {
        flight += t('quote.label.origin') + ': ' + c.service.origin;
      }

      if (
        c.service.origin != '' &&
        c.service.origin != null &&
        c.service.destiny != '' &&
        c.service.destiny != null
      ) {
        flight += ' / ';
      }

      if (c.service.destiny != '' && c.service.destiny != null) {
        flight += ' ' + t('quote.label.destiny') + ': ' + c.service.destiny;
      }

      title = flight;
      type = c.service.type;
      idService = c.service.id;
      start = 6;
      end = 6;
      startmin = '';
      endmin = '';
      // let date_in_ = date_in[1] + date_in[0];
    } else {
      title = c.service.hotel.name;
      type = 'hotel';
      start = 5;
      end = 5;
      startmin = '';
      endmin = '';
      idService = c.service.hotel.id;
    }
    date_in = date_in[1] + date_in[0];

    addEvents(
      start,
      end,
      startmin,
      endmin,
      title,
      description,
      type,
      date_in,
      idService,
      optional,
      c.service
    );
    current();
    //closeIsLoading();

    //})
  };

  const hasEvents = (day: string) => {
    return Object.keys(events).includes(day);
  };

  dataMap();

  let events: Events = listEvents;

  let style = [];

  const eventStyle = (event: Event) => {
    let leftSpacing, z_index, heightService;

    if (event.type != 'hotel' || event.type != 'flight') {
      leftSpacing = '0px';
      let styleFind = style.find(
        (element) =>
          element.date_in === event.date_in &&
          element.id != event.id &&
          element.start < event.start &&
          element.end > event.start
      );

      if (styleFind) {
        leftSpacing = '10px';
      }
      z_index = event.start;

      let styleID = style.find(
        (element) => element.id === event.id && element.date_in === event.date_in
      );
      if (!styleID) {
        style.push(event);
      }

      heightService = (event.end - event.start) * 60;
      if (heightService === 0) {
        heightService = '60';
      }
    } else {
      leftSpacing = '0px';
    }

    let margenSobra = 0;
    let margenTop = parseInt(60 + (event.start - 5) * 60) + parseInt(event.startmin);
    if (event.start != event.end) {
      margenSobra = parseInt(event.endmin) - parseInt(event.startmin);
    }
    let margenHeight = parseInt(heightService) + parseInt(margenSobra);

    if (event.type === 'flight') {
      margenTop = 60;
      margenHeight = 60;
    }

    return {
      top: event.type === 'hotel' ? 0 : margenTop + 'px',
      height: event.type === 'hotel' ? 60 : margenHeight + 'px',
      left: leftSpacing,
      'z-index': z_index,
    };
  };

  const eventTitle = (event: Event) => {
    let heightService, lengthTitle, title;
    heightService = (event.end - event.start) * 60;
    lengthTitle = event.title.length;
    if (heightService < 61 && lengthTitle > 40) {
      title = event.title.substring(0, 40) + '...';
    } else {
      title = event.title;
    }
    return title;
  };

  const eventStart = () => {
    /*console.log(state.startDate)
  console.log(quote.value)*/
    let [y, m, d] = quote.value.date_in.split('-');
    let date_pre = DateTime.fromObject(
      { year: y, month: m, day: d, hour: 0, minute: 0 },
      { locale: languageStore.currentLanguage }
    );
    //console.log(date_pre)

    if (date_pre.weekday == 7) {
      state.startDate = date_pre;
      //console.log(1)
    } else {
      let date_ = moment(y + '-' + m + '-' + d, 'YYYY-MM-DD')
        .subtract(0, 'days')
        .format('YYYY-MM-DD');

      let [yy, mm, dd] = date_.split('-');
      /*console.log(2)
    console.log(date_)*/

      state.startDate = DateTime.fromObject(
        { year: yy, month: mm, day: dd, hour: 0, minute: 0 },
        { locale: languageStore.currentLanguage }
      );
    }

    state.monthLong = state.startDate.monthLong;
    state.weekYear = state.startDate.weekYear;

    // console.log(state.startDate)
  };

  onMounted(() => {
    // state.startDate = DateTime.now().setLocale("es");
    eventStart();
  });

  const { currentLanguage } = storeToRefs(languageStore);
  watch(currentLanguage, () => {
    eventStart();
  });

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
  <div class="quotes-tabs">
    <div class="left">
      <icon-left-arrow-tab />
    </div>
    <div class="center">
      <div
        v-for="quoteCategory of quoteCategories"
        :key="`category-tab-${quoteCategory.type_class_id}`"
        :class="{ active: selectedCategory === quoteCategory.type_class_id }"
        class="tab"
        @click="changeTab(quoteCategory.type_class_id)"
      >
        {{ quoteCategory.type_class.translations[0].value }}
      </div>
    </div>
  </div>

  <div class="quotes-calendar">
    <div class="arrows">
      <h4>{{ state.monthLong }} {{ state.weekYear }}</h4>

      <svg
        @click="before"
        xmlns="http://www.w3.org/2000/svg"
        width="36"
        height="36"
        viewBox="0 0 36 36"
        fill="none"
      >
        <path
          d="M27 18L9 18"
          stroke="#EB5757"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
        />
        <path
          d="M18 27L9 18L18 9"
          stroke="#EB5757"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
        />
      </svg>
      <svg
        @click="after"
        xmlns="http://www.w3.org/2000/svg"
        width="36"
        height="36"
        viewBox="0 0 36 36"
        fill="none"
      >
        <path
          d="M9 18H27"
          stroke="#EB5757"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
        />
        <path
          d="M18 9L27 18L18 27"
          stroke="#EB5757"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
        />
      </svg>
      <!--<font-awesome-icon icon="arrow-left" @click="before" />
      <font-awesome-icon icon="arrow-right" @click="after" />-->
    </div>
    <div class="calendar">
      <div class="hours">
        <div class="hour empty">&nbsp;</div>
        <div v-for="(h, index) in calendar.hours" :key="index" class="hour">
          {{ h }}
        </div>
      </div>
      <div class="container">
        <div class="header">
          <div v-for="(day, index) in calendar.headerDays" :key="index" class="day">
            {{ day.name }} {{ day.number }}
          </div>
        </div>
        <div class="body">
          <div v-for="(day, index) in calendar.headerDays" :key="index" class="day-container">
            <div class="day hotel"></div>
            <div v-for="h in calendar.hours" :key="h" class="day">&nbsp;</div>
            <div v-if="hasEvents(day.month + '' + day.number)" class="events-container">
              <div
                v-for="(e, index) in events[day.month + '' + day.number]"
                :key="index"
                :class="{
                  hotel: e.type === 'hotel',
                  transport: e.type === 'transport',
                  tour: e.type === 'tour',
                  serviceoptional: e.optional,
                }"
                :style="eventStyle(e)"
                :title="e.title"
                class="event"
                :start="e.start + ':' + e.startmin"
                :end="e.end + ':' + e.endmin"
              >
                <div v-if="e.type === 'hotel'" class="titleHotel">
                  <font-awesome-icon icon="hotel" />
                  {{ e.title }}
                  <span class="icon" @click="openHotelDetailModal(e.service!)">
                    <a-tooltip placement="top">
                      <template #title>
                        <span> {{ t('quote.label.information') }}</span>
                      </template>
                      <icon-alert :height="25" :width="25" />
                    </a-tooltip>
                  </span>
                </div>
                <div v-else-if="e.type === 'flight'" class="title">
                  {{ e.title }}
                </div>
                <div v-else>
                  <p class="title">
                    {{ eventTitle(e) }}

                    <span class="icon" @click="openServiceModalItinerary(e.service!)">
                      <a-tooltip placement="top">
                        <template #title>
                          <span> {{ t('quote.label.information') }}</span>
                        </template>
                        <icon-alert :height="25" :width="25" />
                      </a-tooltip>
                    </span>
                  </p>
                  <p class="description">
                    <icon-clock />
                    {{ e.start }}:{{ e.startmin }} {{ e.start < 12 ? 'AM' : 'PM' }} - {{ e.end }}:{{
                      e.endmin
                    }}
                    {{ e.end >= 12 ? 'PM' : 'AM' }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <modal-itinerario-detail
    probando="state.modalHotelDetail.date_out"
    v-if="state.modalHotelDetail.isOpen"
    :hotel="hotelSelected"
    :service="serviceServiceSelected"
    :serviceDateOut="dayjs(state.modalHotelDetail.date_out, 'DD/MM/YYYY').format('YYYY-MM-DD')"
    :title="state.serviceName"
    :type="state.type"
    :category="state.getServiceCategory"
    :categoryName="state.serviceCategoryName"
    :show="state.modalHotelDetail.isOpen"
    @close="claseHotelDetailModal"
  />
</template>

<style lang="scss" scoped>
  .quotes-tabs {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 50px 0 0 50px;
    align-self: stretch;

    .left {
      display: flex;
      padding: 4px;
      align-items: flex-start;
      gap: 10px;
      border-radius: 6px;
      background: #fafafa;

      svg {
        width: 20px;
        height: 20px;
      }
    }

    .center {
      display: flex;
      gap: 10px;
      justify-content: space-between;
      flex-direction: row;
      align-items: flex-end;

      .tab {
        display: flex;
        width: 180px;
        padding: 4px 16px;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        border-radius: 6px 6px 0 0;
        background: #e9e9e9;
        color: #979797;
        text-align: center;
        font-size: 14px;
        font-style: normal;
        font-weight: 600;
        line-height: 21px;
        letter-spacing: 0.21px;
        cursor: pointer;

        &.active {
          background: #737373;
          color: #fefefe;
          padding: 6px 16px;
        }
      }
    }
  }

  .quotes-calendar {
    position: relative;
    padding-bottom: 100px;

    .arrows {
      display: inline-flex;
      height: 36px;
      justify-content: center;
      align-items: flex-start;
      gap: 40px;
      flex-shrink: 0;
      font-size: 36px;
      color: #eb5757;
      cursor: pointer;
      position: absolute;
      margin-top: -85px;
      margin-left: 0;
      top: -34px;

      h4 {
        font-size: 24px;
        font-weight: bold;
        letter-spacing: -1.5px;
        text-transform: capitalize;
      }
    }

    .calendar {
      background-color: #ffffff;
      width: 100%;
      flex-shrink: 0;
      display: flex;

      .hours {
        &.empty {
          height: 60px;
        }

        .hour {
          height: 60px;
          text-align: center;
          padding-right: 10px;
          color: #b5b5b5;
          font-size: 14px;
          font-style: normal;
          font-weight: 400;
          line-height: 22px;
          letter-spacing: -0.21px;
          display: flex;
          justify-content: center;
          align-items: flex-end;
        }
      }

      .container {
        border-radius: 6px;
        border: 2px solid #b5b5b5;
        flex-grow: 1;

        .header {
          display: flex;
          text-align: center;
          align-items: center;

          .day {
            flex: 1 1 0;
            padding: 12px 0;
            border-right: 1px solid #b5b5b5;
            border-bottom: 1px solid #b5b5b5;

            &:nth-last-of-type(1) {
              border-right: none;
            }
          }
        }

        .body {
          display: flex;
          flex-direction: row;

          .day-container {
            display: flex;
            flex-direction: column;
            flex: 1 1 0;
            border-right: 1px solid #b5b5b5;
            position: relative;

            &:last-child {
              border-right: 0;
            }

            .day {
              height: 60px;
              border-bottom: 1px solid #b5b5b5;
            }

            .event {
              position: absolute;
              padding: 0;
              left: 0;
              right: 0;
              background: #fff;
              box-shadow: 1px 0px 7px -1px rgba(0, 0, 0, 0.3);
              padding: 0 0 0 10px;
              overflow: hidden;

              &:hover {
                background: #e9e9e9;

                &:before {
                  background: #979797;
                }
              }

              &:before {
                content: '';
                position: absolute;
                width: 9px;
                left: 0px;
                border-radius: 6px 0 0 6px;
                top: 0;
                bottom: 0;
                background: #b5b5b5;
              }

              &.serviceoptional {
                background: #fff2f2;

                &:before {
                  background: #eb5757;
                }

                &:hover {
                  background: #ffe1e1;

                  &:before {
                    background: #c63838;
                  }

                  .description {
                    color: #c63838;
                  }
                }

                .description {
                  color: #eb5757;
                }

                .title {
                  color: #3d3d3d;
                }
              }

              .titleHotel {
                padding: 4px;
                color: #2e2e2e;
                font-size: 14px;

                span {
                  position: absolute;
                  right: 2px;
                  top: 4px;
                }
              }

              .icon {
                cursor: pointer;

                svg {
                  width: 18px;
                  height: 18px;
                }
              }

              .title {
                color: #2e2e2e;
                font-size: 12px;
                font-style: normal;
                font-weight: 500;
                line-height: 15px;
                letter-spacing: -0.27px;
                padding: 4px 34px 0 4px;
                position: relative;

                span {
                  position: absolute;
                  right: 2px;
                  top: 4px;
                }
              }

              .description {
                color: #979797;
                font-size: 12px;
                font-style: normal;
                font-weight: 400;
                line-height: 22px;
                letter-spacing: -0.21px;
                display: flex;
                align-items: center;
                padding: 0 0 0 10px;
                margin-bottom: 0;
                gap: 4px;

                svg {
                  color: #979797;
                  width: 12px;
                  fill: #979797;
                }

                :deep(path) {
                  color: #979797;
                  width: 13px;
                  fill: #979797;
                }
              }

              &.transport {
                background: #e9e9e9;
              }

              &.tour {
                background: #fcecec;
              }

              &.hotel {
                background: #fafafa;
                left: 0;
                background: #fafafa;
                display: flex;
                justify-content: center;
                align-items: center;
                text-align: center;
                height: 59px !important;
                gap: 5px;
                box-shadow: none;

                &:before {
                  display: none;
                }
              }
            }
          }
        }
      }
    }
  }
</style>
