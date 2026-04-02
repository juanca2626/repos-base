<script lang="ts" setup>
  import type { QuoteServiceHotelsOccupationPassenger } from '@/quotes/interfaces/quote-service-hotels-generate-occupation.response';
  import { computed, toRef, watch } from 'vue';
  import { useI18n } from 'vue-i18n';

  const { t } = useI18n();

  // const { quote } = useQuote();

  interface Props {
    options: QuoteServiceHotelsOccupationPassenger[];
    passengers: QuoteServiceHotelsOccupationPassenger[];
    occupation: number;
    roomTypeId?: String; // Add room type ID prop
    roomCapacities?: Record<string, number>; // Add room capacities prop
  }

  interface Emits {
    (e: 'update:passengers', p: QuoteServiceHotelsOccupationPassenger[]): void;
    (e: 'update:warning', isInvalid: boolean): void;
  }

  const props = defineProps<Props>();
  const emits = defineEmits<Emits>();

  const onChange = (
    val: {
      disabled: boolean | undefined;
      key: number;
      label: string;
      option: QuoteServiceHotelsOccupationPassenger;
      originLabel: string;
      value: number | string;
    }[]
  ) => {
    emits(
      'update:passengers',
      val.map((p) => ({ label: p.label, code: p.key }))
    );
  };

  const passengers = computed(() => props.passengers.map((p) => ({ label: p.label, key: p.code })));
  // const options = computed(() => props.options.filter(o => !props.passengers.includes(o)))
  const options = computed(() =>
    props.options.filter((o) => !props.passengers.some((p) => p.code === o.code))
  );
  const occupation = toRef(props, 'occupation');

  const roomRules: Record<string, { adults: number; children: number[] }[]> = {
    single: [
      { adults: 1, children: [0] }, // 1 adulto solo
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

  const adults = computed(
    () => props.passengers.filter((p) => (p.label ?? '').toLowerCase().includes('adult')).length
  );
  const children = computed(() => props.passengers.length - adults.value);

  const isInvalidCombo = computed(() => {
    const rules = roomRules[props.roomTypeId ?? ''] || [];
    return !rules.some(
      (rule) => rule.adults === adults.value && rule.children.includes(children.value)
    );
  });

  // Validación de capacidad máxima (existente)
  const isOverCapacity = computed(() => {
    if (!props.roomTypeId || !props.roomCapacities) return false;
    const max = props.roomCapacities[props.roomTypeId];
    return max !== undefined && props.passengers.length > max;
  });

  // Validación de capacidad mínima (nueva)
  const isUnderMinimum = computed(() => {
    return props.passengers.length < occupation.value;
  });

  // Validación combinada (manteniendo tu lógica original)
  const showWarning = computed(() => {
    return isUnderMinimum.value || isOverCapacity.value || isInvalidCombo.value;
  });

  watch(
    showWarning,
    (newValue) => {
      emits('update:warning', newValue);
    },
    { immediate: true }
  );
</script>

<template>
  <a-select
    :value="passengers"
    label-in-value
    :field-names="{ label: 'label', value: 'code' }"
    :options="options"
    mode="multiple"
    :placeholder="t('quote.label.please_select')"
    @change="onChange"
    style="width: 100%"
    :class="showWarning ? 'class_red' : ''"
  />
  <span v-if="showWarning" class="class_red_text">
    {{ t('quote.label.check_accommodation') }}
  </span>
</template>

<style lang="scss">
  .class_red .ant-select-selector {
    border: 1px solid red !important;
  }

  .class_red_text {
    color: red !important;
  }

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

    .acomodacion-modal .modal-inner {
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
</style>
