<script lang="ts" setup>
  import type { UnwrapRef } from 'vue';
  import { computed, onMounted, reactive, watchEffect } from 'vue';
  import dayjs, { Dayjs } from 'dayjs';
  import { notification } from 'ant-design-vue';

  import AmountComponent from '@/quotes/components/global/AmountComponent.vue';
  import { useQuote } from '@/quotes/composables/useQuote';
  import IconMagnifyingGlass from '@/quotes/components/icons/IconMagnifyingGlass.vue';
  import type { GroupedServices } from '@/quotes/interfaces';
  import { useSiderBarStore } from '@/quotes/store/sidebar';
  import useQuoteTranslations from '@/quotes/composables/useQuoteTranslations';
  import { useQuoteServices } from '@/quotes/composables/useQuoteServices';
  import { useDateConflictCheck } from '@/quotes/composables/useDateConflictCheck';
  import QuoteShiftServiceModal from '@/quotes/components/modals/QuoteShiftServiceModal.vue';

  import { useI18n } from 'vue-i18n';

  const { t } = useI18n();
  const { getLang } = useQuoteTranslations();

  const {
    serviceSelected: groupedService,
    updateServiceDate,
    unsetServiceEdit,
    quote,
    operation,
  } = useQuote();
  // const { getHotels } = useQuoteHotels();
  const {
    servicesDestinations,
    getToursAvailable,
    getMealsAvailable,
    getTransferAvailable,
    getMiselaniosAvailable,
    getServiceByCategory,
  } = useQuoteServices();

  const type = computed(() => groupedService.value.type);
  const service = computed(() => (groupedService.value as GroupedServices).service);
  // const cityName = computed(() => service.value.destiny);
  const price = computed(() => service.value.import.price_ADL);

  const { checkDateConflict } = useDateConflictCheck();

  const maxAdults = quote.value.people[0].adults;
  const maxChild = quote.value.people[0].child;

  interface SearchHotelsForm {
    checkIn: Dayjs | undefined;
    adult: number;
    child: number;
    hours: string;
    passengers: Array[];
    passenger_selected: Array[];
  }

  const getDestinationName = () => {
    if (type.value === 'service') {
      return service.value.service?.service_origin[0].state.translations[0].value;
    } else {
      return service.value.hotel?.state.translations[0].value;
    }
  };

  const destinationName = computed(() => getDestinationName());

  const passenger_front = computed(() => {
    if (type.value === 'service') {
      return service.value.passengers_front.map((row, index) => ({
        label:
          'Pasajero ' +
          (index + 1) +
          ' ' +
          row.type +
          (row.type == 'CHD' ? ' (' + row.age_child.age + 'y)' : ''),
        value: row.id,
        type: row.type,
        age_child: row.type == 'CHD' ? row.age_child.age : 0,
      }));
    }
    return [];
  });

  const getServiceTypeName = () => {
    // console.log(service.value)
    if (type.value === 'service') {
      return service.value.service?.service_type.translations[0].value ?? 'NA';
    }
  };

  const getServiceTypeId = () => {
    if (type.value === 'service') {
      return service.value.service?.service_type.id ?? 'NA';
    }
  };

  /*const getServicePrice = () => {
  if (type.value === "service") {
    return service.value.service?.service_type.id ?? "NA";
          service.value.service_rate[0].service_rate_plans[0].price_adult
  }
};*/

  const modalDateConflict = reactive({
    isOpen: false,
    date: null as string | null,
    conflictingServices: [] as any[],
  });

  const hotelsFormState: UnwrapRef<SearchHotelsForm> = reactive({
    checkIn: dayjs(),
    adult: 0,
    child: 0,
    hours: '00:00',
    passengers: [],
    passenger_selected: [],
  });

  // console.log('service: ', service)

  onMounted(() => {
    hotelsFormState.checkIn = dayjs(service.value.date_in, 'DD/MM/YYYY');
    hotelsFormState.adult = service.value.adult;
    hotelsFormState.child = service.value.child;
    if (type.value === 'service') {
      hotelsFormState.hours = service.value.hour_in
        ? service.value.hour_in?.substring(0, 5)
        : '00:00';
    }

    if (type.value === 'service') {
      service.value.passengers_front.forEach((element) => {
        if (element.checked == true) {
          hotelsFormState.passenger_selected.push(element.id);
        }
      });
    }
  });

  watchEffect(() => {
    if (service.value) {
      hotelsFormState.checkIn = dayjs(service.value.date_in, 'DD/MM/YYYY');
      hotelsFormState.adult = service.value.adult;
      (hotelsFormState.child = service.value.child),
        (hotelsFormState.hours = service.value.hour_in
          ? service.value.hour_in?.substring(0, 5)
          : '00:00');
    }
  });

  const storeSidebar = useSiderBarStore();
  const updateSelectedService = async () => {
    const newDateFormatted = dayjs(hotelsFormState.checkIn).format('DD/MM/YYYY');
    const dateService = service.value.date_in;

    console.log(newDateFormatted, dateService);

    if (newDateFormatted !== dateService) {
      // Check if there is a conflict
      const conflictResult = checkDateConflict(newDateFormatted, service.value.id);

      if (conflictResult.hasConflict) {
        modalDateConflict.date = newDateFormatted;
        modalDateConflict.conflictingServices = conflictResult.conflictingServices.filter(
          (cs: any) => cs.id !== service.value.id
        );
        modalDateConflict.isOpen = true;
        return;
      }
    }

    await proceedWithUpdate();
  };

  const confirmDateConflict = async (propagate: boolean) => {
    modalDateConflict.isOpen = false;
    await proceedWithUpdate(propagate);
  };

  const closeDateConflictModal = () => {
    modalDateConflict.isOpen = false;
    modalDateConflict.date = null;
    modalDateConflict.conflictingServices = [];
  };

  const proceedWithUpdate = async (propagate: boolean = false) => {
    let numADL = null;
    let numCHL = null;
    let hours = null;

    if (
      hotelsFormState.adult !== service.value.adult ||
      hotelsFormState.child !== service.value.child
    ) {
      numADL = hotelsFormState.adult;
      numCHL = hotelsFormState.child;
    }

    if (hotelsFormState.hours !== service.value.hour_in?.substring(0, 5)) {
      hours = hotelsFormState.hours;
    }

    await updateServiceDate(
      dayjs(hotelsFormState.checkIn).format('YYYY-MM-DD'),
      null,
      numADL,
      numCHL,
      hours,
      hotelsFormState.passenger_selected,
      true,
      propagate
    )
      .then(() => {
        unsetServiceEdit();
        storeSidebar.setStatus(false, 'search', '');
      })
      .catch((e) => {
        openNotificationWithIcon(e.message);
      });
  };
  const openNotificationWithIcon = (message: string) => {
    notification['error']({
      message: 'Error',
      description: message,
    });
  };
  const searchServicesToReplace = async () => {
    // Destin
    const destinyCountry = servicesDestinations.value.destinationsCountries.find((d) => {
      return d.code == service.value.service!.service_destination[0].country_id.toString();
    });
    const destinyState = servicesDestinations.value.destinationsStates.find((d) => {
      return (
        d.country_code == service.value.service!.service_destination[0].country_id.toString() &&
        d.code == service.value.service!.service_destination[0].state_id.toString()
      );
    });
    let destinyCity = null;
    if (service.value.service!.service_destination[0].city_id) {
      destinyCity = servicesDestinations.value.destinationsCities.find((d) => {
        return (
          d.state_code == service.value.service!.service_destination[0].state_id.toString() &&
          d.code == service.value.service!.service_destination[0].city_id.toString()
        );
      });
    }

    // Origin
    const originCountry = servicesDestinations.value.destinationsCountries.find((d) => {
      return d.code == service.value.service!.service_origin[0].country_id.toString();
    });
    const originState = servicesDestinations.value.destinationsStates.find((d) => {
      return (
        d.country_code == service.value.service!.service_origin[0].country_id.toString() &&
        d.code == service.value.service!.service_origin[0].state_id.toString()
      );
    });
    let originCity = null;
    if (service.value.service!.service_origin[0].city_id) {
      originCity = servicesDestinations.value.destinationsCities.find((d) => {
        return (
          d.state_code == service.value.service!.service_origin[0].state_id.toString() &&
          d.code == service.value.service!.service_origin[0].city_id.toString()
        );
      });
    }

    const destiny = {
      code:
        destinyCountry!.code + ',' + (destinyState?.code ?? '') + ',' + (destinyCity?.code ?? ''),
      label:
        destinyCountry!.label +
        ',' +
        (destinyState?.label ?? '') +
        ',' +
        (destinyCity?.label ?? ''),
    };
    const origin = {
      code: originCountry!.code + ',' + (originState?.code ?? '') + ',' + (originCity?.code ?? ''),
      label:
        originCountry!.label + ',' + (originState?.label ?? '') + ',' + (originCity?.label ?? ''),
    };

    switch (service.value.service!.service_sub_category!.service_category_id) {
      case 9:
        await getToursAvailable({
          adults: quote.value.people[0].adults > 0 ? quote.value.people[0].adults : 1,
          allWords: 1, // true
          children: quote.value.people[0].child,
          date_from: dayjs(service.value.date_in, 'DD/MM/YYYY').format('YYYY-MM-DD'),
          destiny: '',
          lang: getLang(),
          origin: origin,
          service_name: '',
          service_type: '',
          experience_type: '',
          service_sub_category: '',
        });
        break;

      case 10:
        await getMealsAvailable({
          adults: quote.value.people[0].adults > 0 ? quote.value.people[0].adults : 1,
          allWords: 1, // true
          children: quote.value.people[0].child,
          date_from: dayjs(service.value.date_in, 'DD/MM/YYYY').format('YYYY-MM-DD'),
          destiny: '',
          lang: getLang(),
          origin: origin,
          service_name: '',
          service_type: '',
          service_sub_category: '',
          // price_range:
        });
        break;

      case 1:
        await getTransferAvailable({
          adults: quote.value.people[0].adults > 0 ? quote.value.people[0].adults : 1,
          allWords: 1, // true
          children: quote.value.people[0].child,
          date_from: dayjs(service.value.date_in, 'DD/MM/YYYY').format('YYYY-MM-DD'),
          destiny: destiny,
          lang: getLang(),
          origin: origin,
          service_name: '',
          service_type: '',
          service_premium: '',
          include_transfer_driver: '',
        });
        break;

      case 14:
        await getMiselaniosAvailable({
          adults: quote.value.people[0].adults > 0 ? quote.value.people[0].adults : 1,
          allWords: 1, // true
          children: quote.value.people[0].child,
          date_from: dayjs(service.value.date_in, 'DD/MM/YYYY').format('YYYY-MM-DD'),
          destiny: '',
          lang: getLang(),
          origin: '',
          service_name: '',
        });
        break;

      default:
        await getServiceByCategory({
          service_category: service.value.service!.service_sub_category!.service_category_id,
          adults: quote.value.people[0].adults > 0 ? quote.value.people[0].adults : 1,
          allWords: 1, // true
          children: quote.value.people[0].child,
          date_from: dayjs(service.value.date_in, 'DD/MM/YYYY').format('YYYY-MM-DD'),
          destiny: destiny,
          lang: getLang(),
          origin: origin,
          service_name: '',
        });
        break;
    }

    storeSidebar.setStatus(true, 'service', 'search');
  };

  const serviceTypeName = computed(() => getServiceTypeName());
  const serviceTypeId = computed(() => getServiceTypeId());
  /*console.log(serviceTypeName)
console.log(service.value)*/
</script>

<template>
  <div>
    <div class="editComponent">
      <div class="place">
        <div>
          <icon-magnifying-glass />
          {{ destinationName }}
        </div>
        <div></div>
      </div>

      <div class="description">
        <div class="input-box">
          <a-date-picker
            v-model:value="hotelsFormState.checkIn"
            id="start-date"
            :format="'DD/MM/YYYY'"
          />
        </div>

        <div class="block" v-if="operation == 'passengers'">
          <div class="input">
            <label>{{ t('quote.label.adults') }}</label>
            <div class="box">
              <AmountComponent v-model:amount="hotelsFormState.adult" :min="0" :max="maxAdults" />
            </div>
          </div>
          <div class="input">
            <label>{{ t('quote.label.child') }}</label>
            <div class="box">
              <AmountComponent v-model:amount="hotelsFormState.child" :min="0" :max="maxChild" />
            </div>
          </div>
        </div>

        <template v-if="type === 'service'">
          <div class="block full">
            <div class="input">
              <label class="input-label">{{ t('quote.label.assign_list') }}</label>
              <a-select
                v-model:value="hotelsFormState.passenger_selected"
                mode="multiple"
                style="width: 100%"
                :max-tag-count="2"
                :options="passenger_front"
              >
              </a-select>
              <!-- label-in-value -->
            </div>
          </div>

          <div class="block full">
            <div class="input">
              <label class="input-label">{{ t('quote.label.type') }}</label>
              <div class="tag-button input-text">{{ serviceTypeName }}</div>
            </div>
          </div>

          <div class="block full">
            <div class="input">
              <label v-if="serviceTypeId == 2">{{ t('quote.label.hour') }}</label>
              <label class="input-label" v-if="serviceTypeId == 1">{{
                t('quote.label.hour')
              }}</label>
              <input
                type="time"
                class="hours"
                v-model="hotelsFormState.hours"
                v-if="serviceTypeId == 2"
              />
              <div class="tag-button input-text" v-if="serviceTypeId == 1">
                {{ hotelsFormState.hours }}
              </div>
            </div>
          </div>
        </template>
      </div>
    </div>

    <div class="row-flex">
      <div class="quotes-actions-btn save" @click="updateSelectedService">
        <div class="content">
          <div class="text">{{ t('quote.label.save') }}</div>
        </div>
      </div>

      <div
        class="quotes-actions-btn"
        v-if="storeSidebar.modePage === 'search'"
        @click="searchServicesToReplace"
      >
        <div class="content">
          <div class="text">
            <svg
              width="24"
              height="24"
              viewBox="0 0 24 24"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M17 2.29419L21 5.8236L17 9.35301"
                stroke="#EB5757"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
              <path
                d="M3 11.1178V9.35314C3 8.41708 3.42143 7.51936 4.17157 6.85747C4.92172 6.19558 5.93913 5.82373 7 5.82373H21"
                stroke="#EB5757"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
              <path
                d="M7 21.706L3 18.1766L7 14.6472"
                stroke="#EB5757"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
              <path
                d="M21 12.8826V14.6473C21 15.5833 20.5786 16.4811 19.8284 17.1429C19.0783 17.8048 18.0609 18.1767 17 18.1767H3"
                stroke="#EB5757"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
            </svg>
            {{ t('quote.label.replace') }}
          </div>
        </div>
      </div>

      <div class="price">
        ${{ price }} <span>{{ t('quote.label.per_room') }}</span>
      </div>
    </div>

    <QuoteShiftServiceModal
      v-if="modalDateConflict.isOpen"
      :is-open="modalDateConflict.isOpen"
      mode="date_conflict"
      :insertion-date="modalDateConflict.date || ''"
      :previous-date="service.date_in"
      :conflicting-services="modalDateConflict.conflictingServices"
      @confirm="confirmDateConflict"
      @close="closeDateConflictModal"
    />
  </div>
</template>

<style lang="scss" scoped>
  .quotes-actions-btn.save .content .text {
    color: #fff !important;
  }

  .editComponent {
    overflow-y: auto;
    padding-bottom: 1rem;
    /* width */
    &::-webkit-scrollbar {
      width: 5px;
      margin-right: 4px;
      padding-right: 2px;
    }

    /* Track */
    &::-webkit-scrollbar-track {
      border-radius: 10px;
    }

    /* Handle */
    &::-webkit-scrollbar-thumb {
      border-radius: 4px;
      background: #c4c4c4;
      margin-right: 4px;
    }

    /* Handle on hover */
    &::-webkit-scrollbar-thumb:hover {
      background: #eb5757;
    }
  }

  .input-text,
  .hours {
    width: 100%;
    font-size: 16px;
    padding: 4px 11px 4px;
    border: 1px solid #d9d9d9;
    border-radius: 4px;
    background: #fff;
    outline: none;
  }

  input[type='time']::-webkit-calendar-picker-indicator {
    filter: invert(70%);
  }

  input[type='time']::selection,
  input[type='time']::-webkit-datetime-edit-hour-field:focus,
  input[type='time']::-webkit-datetime-edit-minute-field:focus,
  input[type='time']::-webkit-datetime-edit-second-field:focus,
  input[type='time']::-webkit-datetime-edit-ampm-field:focus {
    background-color: #eb5757;
  }

  .input-text,
  .input-label {
    color: #979797 !important;
  }

  .row-flex {
    display: flex;
    flex: 1;
    padding: 21px 18px;
    border-top: 1px solid #c4c4c4;
    gap: 10px;

    > div {
      flex: auto;
    }
  }

  .description {
    min-height: 350px;
    padding: 0 16px 0 16px;
  }

  .block {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 65px;
    margin-top: 24px;

    &.full {
      width: 100%;
    }

    .input {
      display: flex;
      padding: 0 1px;
      flex-direction: column;
      align-items: flex-start;
      gap: 6px;
      width: 100%;

      label {
        color: #575757;
        font-size: 14px;
        font-style: normal;
        font-weight: 500;
        line-height: 21px;
        letter-spacing: 0.21px;
        align-self: stretch;
      }

      .box {
        display: flex;
        align-items: center;
        border: 1px solid #c4c4c4;
        border-radius: 4px;
        gap: 8px;
        width: 100%;
        justify-content: center;
        align-items: center;

        span {
          color: #575757;
          font-size: 14px;
          font-style: normal;
          font-weight: 400;
          line-height: 21px;
          letter-spacing: 0.21px;
        }

        input {
          border: none;
          width: 100%;
        }
      }
    }
  }

  :deep(.ant-picker) {
    width: 100%;
    font-size: 12px;

    .ant-picker-suffix {
      color: #eb5757;
    }
  }
</style>
