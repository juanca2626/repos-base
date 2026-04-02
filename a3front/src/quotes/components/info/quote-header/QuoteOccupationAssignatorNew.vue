<script lang="ts" setup>
  import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
  import AmountComponent from '@/quotes/components/global/AmountComponent.vue';
  import IconAlert from '@/quotes/components/icons/IconAlert.vue';
  import { computed, inject, onMounted, ref, toRef, watchEffect, watch } from 'vue';
  import { useQuote } from '@/quotes/composables/useQuote';
  import type {
    QuoteServiceHotelsOccupation,
    QuoteServiceHotelsOccupationPassenger,
  } from '@/quotes/interfaces';
  import QuoteOccupationSelect from '@/quotes/components/info/quote-header/QuoteOccupationSelect.vue';
  import { useI18n } from 'vue-i18n';

  const show_occupations = inject('show-occupations');

  const { t } = useI18n();
  const showModal = ref(false);
  const showAlert = ref<boolean>(false);

  interface Props {
    adults: number;
    child: number;
    childAges: [];
  }

  const props = defineProps<Props>();

  const adults = toRef(props, 'adults');
  const child = toRef(props, 'child');
  const childAges = toRef(props, 'childAges');

  interface Emits {
    (e: 'close'): void;
  }

  const emits = defineEmits<Emits>();
  const closeForm = () => {
    emits('close');
  };
  // const openModal = () => {
  //   showModal.value = true;
  // };
  const closeModal = () => {
    showModal.value = false;
  };

  const {
    quote,
    accommodation,
    operation,
    getQuoteAccommodationClient,
    assignQuoteOccupation,
    getQuote,
    updatePassengersAccommodations,
  } = useQuote();

  // const passengers = computed<Person>(() => quote.value.people[0]);
  // const passengerOptins = ref<[]>([])
  let passengerOptins = [...quote.value.passengers];

  const single = ref<number>(accommodation.value.single);
  const double = ref<number>(accommodation.value.double);
  const triple = ref<number>(accommodation.value.triple);

  const generatedDistribution = ref<QuoteServiceHotelsOccupation[]>([]);

  const setAccommodation = async () => {
    const hotelsOccupationsDistributions = await getQuoteAccommodationClient(
      single.value,
      double.value,
      triple.value,
      adults.value,
      child.value
    );

    generatedDistribution.value = hotelsOccupationsDistributions.quoteDistributions;
    passengerOptins = hotelsOccupationsDistributions.passengers;
    // openModal()
  };

  // Reglas explícitas de combinaciones válidas
  const roomRules: Record<string, { adults: number; children: number[] }[]> = {
    single: [
      { adults: 1, children: [0] }, // 1 adulto
      { adults: 1, children: [1] }, // 1 adulto + 1 niño
    ],
    double: [
      { adults: 2, children: [0, 1] }, // 2 adultos (solos o con 1 niño)
      { adults: 1, children: [2] }, // 1 adulto + 2 niños
    ],
    triple: [
      { adults: 3, children: [0, 1] }, // 3 adultos (solos o con 1 niño)
      { adults: 2, children: [1, 2] }, // 2 adultos + 1 o 2 niños
    ],
  };

  const updateAccommodation = async (): Promise<boolean | void> => {
    let validate = true;

    generatedDistribution.value.forEach((room) => {
      const totalPassengers = room.passengers.length;

      const adults = room.passengers.filter((p) =>
        (p.label ?? '').toLowerCase().includes('adult')
      ).length;

      const children = totalPassengers - adults;

      // 1. Validar que total de personas cumpla mínimo de occupation
      if (totalPassengers < room.occupation) {
        validate = false;
        return;
      }

      // 2. Validar combinaciones específicas de adultos/niños
      const rules = roomRules[room.type_room] || [];
      const validRoom = rules.some(
        (rule) => rule.adults === adults && rule.children.includes(children)
      );

      if (!validRoom) {
        validate = false;
      }
    });

    if (!validate) {
      return false;
    }

    if (!isFormValid.value) {
      return;
    }

    let passengerNews = [];
    passengerOptins.forEach((value) => {
      if (isNumber(value.id) === false) {
        value.id = null;
      }
      passengerNews.push(value);
    });

    if (quote.value.passengers.length > passengerNews.length) {
      passengerNews = quote.value.passengers;
    }

    await updatePassengersAccommodations(
      passengerNews,
      adults.value,
      child.value,
      childAges.value,
      single.value,
      double.value,
      triple.value
    );
    await getQuote();
    closeForm();
  };

  const isNumber = (value) => {
    return typeof value === 'number' && !Number.isNaN(value);
  };

  const assignOccupation = async () => {
    await assignQuoteOccupation(single.value, double.value, triple.value);
    closeModal();
    closeForm();
  };

  const options = computed<QuoteServiceHotelsOccupationPassenger[]>(() => [
    ...(passengerOptins
      .filter((p) => p.type === 'ADL')
      .map((p, i) => ({
        code: p.id,
        label: `Adult ${i + 1}`,
      })) as QuoteServiceHotelsOccupationPassenger[]),
    ...(passengerOptins
      .filter((p) => p.type === 'CHD')
      .map((p, i) => ({
        code: p.id,
        label: `Child ${i + 1}`,
      })) as QuoteServiceHotelsOccupationPassenger[]),
  ]);

  const setQuotePassenger = async (type: string, value: number) => {
    switch (type) {
      case 'single':
        single.value = value;
        break;
      case 'double':
        double.value = value;
        break;
      case 'triple':
        triple.value = value;
        break;
    }

    if (operation.value == 'passengers') {
      await setAccommodation();
    } else {
      await assignOccupation();
    }
  };

  watchEffect(async () => {
    if (show_occupations.value == true) {
      if (operation.value == 'passengers') {
        await setAccommodation();
        show_occupations.value = false;
      }
    }

    generatedDistribution.value.forEach((pax) => {
      showAlert.value = false;
      if (pax.occupation < pax.passengers.length || pax.occupation > pax.passengers.length) {
        showAlert.value = true;
      }
    });
  });

  onMounted(async () => {
    await setAccommodation();
  });

  const services = computed(() => quote.value.categories?.[0]?.services ?? []);

  const roomCapacities = computed<Record<string, number>>(() => {
    const capacities: Record<string, number> = {};

    services.value.forEach((service) => {
      const serviceRooms = (service as any)?.service?.service_rooms;
      if (Array.isArray(serviceRooms)) {
        serviceRooms.forEach((room) => {
          const roomTypeId = room.rate_plan_room?.room?.room_type?.id;
          const maxCapacity = room.rate_plan_room?.room?.max_capacity;

          if (roomTypeId && maxCapacity) {
            if (!capacities[roomTypeId] || maxCapacity > capacities[roomTypeId]) {
              capacities[roomTypeId] = maxCapacity;
            }
          }
        });
      }
    });

    return capacities;
  });

  // Almacena el estado de cada habitación
  const roomWarnings = ref<boolean[]>([]);

  // Maneja los cambios de cada select
  const handleWarningUpdate = (isInvalid: boolean, index: number) => {
    roomWarnings.value[index] = isInvalid;
  };

  // Computed que verifica si TODOS los selects son válidos
  const isFormValid = computed(() => {
    return !roomWarnings.value.some((warning) => warning === true);
  });

  watch(
    () => generatedDistribution.value.length,
    (newLength) => {
      roomWarnings.value = new Array(newLength).fill(false);
    },
    { immediate: true }
  );
</script>

<template>
  <div class="rooms-form headerSearch new">
    <div class="input">
      <!--<h3 class="titleSection">{{ t('quote.label.assign_accommodation') }}</h3>-->

      <icon-alert class="alert-headerSearch" :height="25" :width="25" v-if="showAlert" />
      <a-alert
        :message="t('quote.label.alertaccommodation')"
        type="info"
        show-icon
        v-if="showAlert"
      />

      <div class="details-acomodations">
        <span>{{ t('quote.label.to_distribute') }}:</span>

        <div class="item">
          <font-awesome-icon icon="user" />
          <span>{{ adults }} {{ t('quote.label.adult') }}(s)</span>
        </div>

        <div class="item">
          <font-awesome-icon icon="child" />
          <span>{{ child }} {{ t('quote.label.child') }}(s)</span>
        </div>
      </div>

      <label>{{ t('quote.label.rooms') }}</label>
      <div class="boxes new">
        <div class="box">
          <span>SGL</span>
          <AmountComponent
            v-model:amount="single"
            :min="0"
            :max="60"
            @change="(value) => setQuotePassenger('single', value)"
          />
          <input name="box-1" type="text" class="hide" />
        </div>

        <div class="box">
          <span>DBL</span>
          <AmountComponent
            v-model:amount="double"
            :min="0"
            :max="60"
            @change="(value) => setQuotePassenger('double', value)"
          />
          <input name="box-1" type="text" class="hide" />
        </div>

        <div class="box">
          <span>TRL</span>
          <AmountComponent
            v-model:amount="triple"
            :min="0"
            :max="60"
            @change="(value) => setQuotePassenger('triple', value)"
          />
          <input name="box-1" type="text" class="hide" />
        </div>
      </div>

      <div class="body acomoda">
        <div class="bottom">
          <div v-for="(pax, index) of generatedDistribution" :key="index" class="content">
            <span>{{ t('quote.label.room') }} {{ pax.type_room_name }}:</span>
            <quote-occupation-select
              v-model:passengers="pax.passengers"
              v-model:options="options"
              @update:warning="(isInvalid) => handleWarningUpdate(isInvalid, index)"
              :placeholder="t('quote.label.please_select')"
              :occupation="pax.occupation"
              :room-type-id="pax.type_room"
              :room-capacities="roomCapacities"
            />
          </div>
        </div>
      </div>

      <div class="footer">
        <button :disabled="false" class="cancel" @click="closeForm">
          {{ t('quote.label.cancel') }}
        </button>
        <button class="ok" @click="updateAccommodation">
          {{ t('quote.label.save') }}
        </button>
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
  .rooms-form {
    display: flex;
    width: 329px;
    padding: 12px 16px 16px 16px;
    flex-direction: column;
    align-items: flex-start;
    gap: 16px;
    border-radius: 0 0 6px 6px;
    background: #fff;
    box-shadow: 0 4px 8px 0 rgba(16, 24, 40, 0.16);
    position: absolute;
    top: 35px;
    z-index: 1;

    &.headerSearch.new {
      padding: 12px 16px 16px 16px;
      width: 588px !important;
      box-shadow: 0 4px 8px 0 rgba(16, 24, 40, 0.16);
      top: 35px;

      .ant-alert {
        width: 100%;
        margin-bottom: 20px;
        color: #2e2b9e;
        background: #ededff;
        border: 1px solid #2e2b9e;
        padding: 10px;

        .ant-alert-message {
          color: #2e2b9e;
          padding-left: 7px;
        }

        .ant-alert-icon {
          visibility: hidden;
        }

        .alert-headerSearch {
          position: absolute;
          left: 12px;
          top: 14px;
          z-index: 2;
        }
      }

      &.rooms-form .box .amountN {
        width: 25px;
      }

      .acomoda {
        width: 100%;

        .bottom {
          flex-direction: column;
        }

        .content {
          display: flex;
          margin-bottom: 0;
          height: auto !important;
          width: 100%;

          &:last-child {
            margin-bottom: 20px;
          }

          & > span {
            color: #575757;
            font-size: 14px;
            font-style: normal;
            text-align: left;
            line-height: 45px;
            width: 25%;
            position: relative;
            font-weight: 500;

            &:before {
              content: '';
              position: absolute;
              left: 0;
              right: 0;
              height: 4px;
              background: #fff;
              top: 61%;
            }
          }

          .ant-select {
            width: 75%;

            .ant-select-selector {
              height: 45px;
              border: 1px solid #c4c4c4;

              .ant-select-selection-item {
                background: #ededff;
              }

              .anticon svg,
              .anticon svg path {
                color: #5c5ab4;
              }

              .ant-select-selection-placeholder {
                text-align: left;
                font-size: 12px;
              }

              &:after {
                overflow: hidden;
              }

              /*:after{
                  overflow: hidden;
                }*/

              .ant-select-selection-item-content {
                font-size: 12px;
                color: #2e2b9e;
                font-weight: bold;
                position: relative;

                &:before {
                  display: none;
                }
              }
            }
          }
        }
      }
    }

    .input {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 6px;
      align-self: stretch;

      label {
        display: flex;
        flex-direction: column;
        gap: 6px;
        align-self: stretch;
        font-weight: 500;
      }

      .boxes {
        display: flex;
        height: 45px;
        padding: 4px 10px;
        justify-content: space-between;
        align-items: center;
        align-self: stretch;
        border-radius: 4px;
        border: 1px solid #c4c4c4;
        background: #fff;

        &.new {
          .box {
            input.hide {
              width: 0px;
            }
          }
        }

        .box {
          display: flex;
          align-items: center;
          gap: 8px;

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
            display: block;
          }
        }
      }

      .bottom {
        color: #eb5757;
        text-align: right;
        font-size: 16px;
        font-style: normal;
        font-weight: 600;
        line-height: 17px;
        letter-spacing: 0.24px;
        text-decoration-line: underline;
        display: flex;
        margin-top: 12px;
        gap: 10px;
        width: 100%;

        p {
          margin-bottom: 0;
          cursor: pointer;
        }
      }
    }

    .details-acomodations {
      margin-bottom: 5px;
      display: flex;
      width: 100%;
      gap: 15px;
      align-items: center;
      font-size: 14px;

      .item {
        display: flex;
        gap: 4px;
        align-items: center;

        span {
          font-weight: bold;
        }
      }
    }

    .acomodacion-modal {
      .modal-inner {
        width: 590px;

        .body {
          display: flex;
          flex-direction: column;
          align-items: flex-start;
          gap: 16px;
          align-self: stretch;
          padding: 0 10px;
          margin-bottom: 40px;

          .top {
            display: flex;
            align-items: flex-start;
            gap: 16px;

            span {
              color: #4f4b4b;
              font-size: 14px;
              font-style: normal;
              font-weight: 500;
              line-height: 21px;
              letter-spacing: 0.21px;
            }

            .item {
              display: flex;
              align-items: center;
              gap: 5px;

              span {
                color: #575757;
                font-size: 14px;
                font-style: normal;
                font-weight: 700;
                line-height: 21px;
                letter-spacing: 0.21px;
              }
            }
          }

          .bottom {
            display: flex;
            padding: 1px 0;
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
            align-self: stretch;

            .content {
              display: flex;
              align-items: center;
              gap: 15px;
              align-self: stretch;

              span {
                color: #4f4b4b;
                font-size: 14px;
                font-style: normal;
                font-weight: 500;
                line-height: 21px;
                letter-spacing: 0.21px;
              }

              .ant-select {
                flex: 1 1 0;

                .ant-select-selector {
                  border-radius: 4px;
                  border: 1px solid #ededff;
                  background: #ffffff;
                  padding: 6px;
                  font-size: 14px;

                  .ant-select-selection-item {
                    border-radius: 6px;
                    background: #ededff;

                    .ant-select-selection-item-content {
                      color: #2e2b9e;
                    }
                  }
                }
              }
            }
          }
        }
      }
    }
  }

  .footer {
    display: flex;
    justify-content: center;
    align-items: flex-end;
    gap: 25px;
    background-color: #ffffff;
    padding: 0 0 10px;
    margin: 0;

    button {
      height: 62px !important;
      line-height: inherit !important;
      font-size: 15px !important;
      width: 167px;
      justify-content: center;
      align-items: center;
      border-radius: 6px;
      letter-spacing: -0.255px;
      border: none;
      cursor: pointer;

      &.cancel {
        background: #fafafa;
        color: #eb5757;
        border: 1px solid #eb5757;
      }

      &.ok {
        background: #eb5757;
        color: #ffffff;
      }
    }
  }
</style>
