<script setup lang="ts">
  import AmountComponent from '@/quotes/components/global/AmountComponent.vue';
  import IconCheck from '@/quotes/components/icons/IconCheck.vue';
  // import { useQuote } from '@/quotes/composables/useQuote';
  import { toRef } from 'vue';

  import { useI18n } from 'vue-i18n';

  const { t } = useI18n();
  // const { quote } = useQuote();

  // Props
  interface Props {
    accommodation: PropAccommodation;
  }

  interface PropAccommodation {
    single: AccommodationContent;
    double: AccommodationContent;
    triple: AccommodationContent;
  }

  interface AccommodationContent {
    checked: boolean;
    quantity: number;
  }

  // const props = withDefaults(defineProps<Props>(), {
  //   accommodation: {
  //     single: {
  //       checked: false,
  //       quantity: 0,
  //     },
  //     double: {
  //       checked: false,
  //       quantity: 0,
  //     },
  //     triple: {
  //       checked: false,
  //       quantity: 0,
  //     },
  //   }
  // })
  const props = defineProps<Props>();

  const accommodation = toRef(props, 'accommodation');

  // console.log(accommodation);

  // // Emits
  // interfaces Emits {
  //   (e: 'update:accommodation', value:PropAccommodation): void
  // }

  // const emits = defineEmits<Emits>()

  // // Accommodation update handler
  // watch(accommodation.value, (value:PropAccommodation)=>{
  //   emits('update:accommodation', value)
  // });
</script>

<template>
  <div class="infoSelect">
    <div class="box-passengers p-0">
      <div class="top">
        <div class="block">
          <label>{{ t('quote.label.simple') }}</label>
          <div class="input">
            <div
              class="box check"
              :class="{ checked: accommodation.single.checked }"
              @click="accommodation.single.checked = !accommodation.single.checked"
            >
              <icon-check v-if="accommodation.single.checked" />
            </div>
          </div>
          <div class="input" v-if="accommodation.single.checked">
            <div class="box">
              <AmountComponent
                :amount="accommodation.single.quantity"
                :min="0"
                :max="80"
                @change="(value) => (accommodation.single.quantity = value)"
              />
            </div>
          </div>
        </div>
        <div class="block">
          <label>{{ t('quote.label.doble') }}</label>
          <div class="input">
            <div
              class="box check"
              :class="{ checked: accommodation.double.checked }"
              @click="accommodation.double.checked = !accommodation.double.checked"
            >
              <icon-check v-if="accommodation.double.checked" />
            </div>
          </div>
          <div class="input" v-if="accommodation.double.checked">
            <div class="box">
              <AmountComponent
                :amount="accommodation.double.quantity"
                :min="0"
                :max="80"
                @change="(value) => (accommodation.double.quantity = value)"
              />
            </div>
          </div>
        </div>
        <div class="block">
          <label>{{ t('quote.label.triple') }}</label>
          <div class="input">
            <div
              class="box check"
              :class="{ checked: accommodation.triple.checked }"
              @click="accommodation.triple.checked = !accommodation.triple.checked"
            >
              <icon-check v-if="accommodation.triple.checked" />
            </div>
          </div>
          <div class="input" v-if="accommodation.triple.checked">
            <div class="box">
              <AmountComponent
                :amount="accommodation.triple.quantity"
                :min="0"
                :max="80"
                @change="(value) => (accommodation.triple.quantity = value)"
              />
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped lang="scss">
  .infoSelect {
    display: flex;
    padding: 20px 12px;
    align-items: center;
    gap: 20px;
    align-self: stretch;

    .box-passengers {
      display: inline-flex;
      padding: 12px 16px;
      flex-direction: column;
      align-items: flex-end;
      gap: 10px;
      border-radius: 0 0 6px 6px;
      background: #ffffff;
      width: 100%;

      .box {
        display: flex;
        align-items: center;
        border: 1px solid #c4c4c4;
        border-radius: 4px;
        gap: 8px;
        width: 60px;
        justify-content: center;
        padding: 0 4px;
        align-items: center;
        //height: 32px;

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
          padding: 0;
        }

        &.check {
          width: 22px;
          height: 22px;
          border: 1px solid #c4c4c4;
          cursor: pointer;

          &.checked {
            border: 1px solid #eb5757;
            background: #eb5757;
          }
        }
      }

      .top {
        display: flex;
        align-items: center;
        gap: 15px;
      }

      .bottom {
        display: flex;
        align-items: center;
        gap: 0;

        span {
          color: #4f4b4b;
          font-size: 14px;
          font-style: normal;
          font-weight: 500;
          line-height: 21px;
          letter-spacing: 0.21px;
          text-align: right;
          width: 100px;
        }

        .block {
          display: flex;
          height: 45px;
          padding: 5px 10px;
          align-items: center;
          gap: 16px;
          border-radius: 4px;
          background: #ffffff;
          width: 75px;
          padding-right: 0;
        }
      }

      .block {
        display: flex;
        align-items: center;
        gap: 5px;
        width: auto;
        font-size: 16px;

        .input {
          display: flex;
          padding: 0 1px;
          flex-direction: column;
          align-items: flex-start;
          gap: 6px;
          width: auto;

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
            color: red;

            :deep(.ant-input-number) {
              width: 100%;

              input {
                padding: 0 !important;
                text-align: center;
              }
            }

            :deep(.buttons) {
              gap: 0;
            }
          }

          input {
            visibility: hidden;
            height: 0;
            width: 0;
            padding: 0 !important;
            text-align: center;
          }
        }
      }
    }
  }
</style>
