<script setup lang="ts">
  import { ref, inject, watchEffect } from 'vue';

  import ModalComponent from '@/quotes/components/global/ModalComponent.vue';
  import { useQuote } from '@/quotes/composables/useQuote';
  // import { usePopup } from '@/quotes/composables/usePopup';
  import { useI18n } from 'vue-i18n';
  const { t } = useI18n();

  const {
    quote,
    ranges,
    saveAs,
    saveQuote,
    cancelQuoteRanges,
    getQuote,
    updateName,
    setUpdatePeople,
    updatePassengersAccommodations,
  } = useQuote();
  // const { toggleForm } = usePopup();

  // props
  const showRangesModal = ref<boolean>(false);
  const nameQuote = ref<string>('');

  const check = ref<number>(1);

  const show_range = inject('show-range');

  const toggleModalPassenger = () => {
    showRangesModal.value = !showRangesModal.value;
  };
  const process = async () => {
    if (check.value == 1) {
      await saveQuote();
      await saveAs(nameQuote.value);

      if (quote.value.data_type_passenger != null) {
        let passengerNews = [];
        quote.value.data_type_passenger.quote_passengers.forEach((value) => {
          value.id = null;
          passengerNews.push(value);
        });

        let adults = quote.value.data_type_passenger.quote_people.adults;
        let child = quote.value.data_type_passenger.quote_people.child;
        let childAges = quote.value.data_type_passenger.quote_age_child;
        let quoteAccommodations = quote.value.data_type_passenger.quote_accommodations;

        await updatePassengersAccommodations(
          passengerNews,
          adults,
          child,
          childAges,
          quoteAccommodations.single,
          quoteAccommodations.double,
          quoteAccommodations.triple
        );
        await getQuote();
        quote.value.name = nameQuote.value;
        await updateName();
        await getQuote();
      } else {
        await cancelQuoteRanges();
        await saveQuote();
        quote.value.name = nameQuote.value;
        await updateName();
        await getQuote(null, false);
        await setUpdatePeople();
        await getQuote();
      }
    } else {
      if (quote.value.data_type_passenger != null) {
        let passengerNews = [];
        quote.value.data_type_passenger.quote_passengers.forEach((value) => {
          value.id = null;
          passengerNews.push(value);
        });

        let adults = quote.value.data_type_passenger.quote_people.adults;
        let child = quote.value.data_type_passenger.quote_people.child;
        let childAges = quote.value.data_type_passenger.quote_age_child;
        let quoteAccommodations = quote.value.data_type_passenger.quote_accommodations;

        await updatePassengersAccommodations(
          passengerNews,
          adults,
          child,
          childAges,
          quoteAccommodations.single,
          quoteAccommodations.double,
          quoteAccommodations.triple
        );
        await getQuote();
      } else {
        await cancelQuoteRanges();
        await getQuote(null, false);
        await setUpdatePeople();
        await getQuote();
      }
    }
  };

  watchEffect(async () => {
    if (show_range.value == true) {
      showRangesModal.value = true;
      show_range.value = false;
    }
  });
</script>

<template>
  <div class="width100">
    <span class="txt-ele">{{ t('quote.label.chosen_ranks') }}:</span>

    <a-tag v-for="range of ranges" :key="`range-from-${range}`"
      >{{ range.from }} - {{ range.to }}</a-tag
    >
    <!-- <a-tag closable @close="deleteRange()">Tag 2</a-tag> -->

    <span class="absRigth" @click="toggleModalPassenger()">
      <svg
        width="24"
        height="25"
        viewBox="0 0 24 25"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
      >
        <path
          d="M18 6.0332L6 18.0332"
          stroke="#3D3D3D"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
        />
        <path
          d="M6 6.0332L18 18.0332"
          stroke="#3D3D3D"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
        />
      </svg>
    </span>

    <ModalComponent
      :modalActive="showRangesModal"
      class="modal-passengers"
      @close="showRangesModal = false"
    >
      <template #body>
        <h3 class="title">{{ t('quote.label.delete_ranks') }}</h3>
        <div class="description">
          {{ t('quote.label.quote_original') }}
        </div>

        <a-radio-group v-model:value="check">
          <a-radio :value="1">{{ t('quote.label.quote_copy') }}</a-radio>
          <a-radio :value="2">{{ t('quote.label.quote_change') }}</a-radio>
        </a-radio-group>
        <a-input
          v-if="check == 1"
          v-model:value="nameQuote"
          :placeholder="t('quote.label.new_name_of_quote')"
        />
      </template>
      <template #footer>
        <div class="footer">
          <button :disabled="false" class="cancel" @click="toggleModalPassenger">
            {{ t('quote.label.cancel') }}
          </button>
          <button :disabled="false" class="ok" @click="process">
            {{ t('quote.label.yes_continue') }}
          </button>
        </div>
      </template>
    </ModalComponent>
  </div>
</template>

<style lang="scss" scoped>
  .modal-passengers {
    :deep(.modal-inner) {
      max-width: 500px;

      .title {
        margin-bottom: 20px;
      }

      .description {
        margin-bottom: 30px;
      }
    }
  }

  :deep(.ant-input) {
    height: 47px;
    margin-bottom: 30px;
  }

  :deep(.ant-radio-group) {
    display: flex;
    margin-bottom: 15px;
  }

  .width100 {
    position: relative;
    width: 100%;
  }

  .absRigth {
    position: absolute;
    right: 0;
    top: 0;
    cursor: pointer;
  }

  .ant-tag {
    background: #ededff;
    color: #2e2b9e;
    font-size: 12px;
    font-weight: bold;
    padding: 0 10px;
    margin-right: 15px;
    &:last-child() {
      margin-right: 0;
    }
  }

  .txt-ele {
    color: #979797;
    font-size: 14px;
    font-weight: bold;
    margin-right: 15px;
  }

  .body-container {
    display: flex;
    gap: 35px;

    .list {
      width: 100%;
    }

    &.openSideBar {
      .list {
        min-width: 70%;
        max-width: 70%;
      }
    }

    .sidebar-container {
      width: 30%;
    }
  }

  .modal-passengers {
    .container {
      display: flex;
      height: unset !important;
      padding: 15px 10px;
      flex-direction: column;
      align-items: center;
      gap: 40px;

      .title {
        display: flex;
        padding-left: 0;
        justify-content: center;
        align-items: flex-start;
        align-self: stretch;
      }

      .body {
        display: flex;
        justify-content: center;
        align-items: flex-start;
        gap: 24px;
        align-self: stretch;

        .col1 {
          display: flex;
          padding-top: 35px;
          flex-direction: column;
          align-items: flex-start;
          gap: 70px;
          align-self: stretch;

          span {
            width: 19px;
            height: 30px;
            color: #000;
            font-size: 24px;
            font-style: normal;
            font-weight: 600;
            line-height: normal;
            letter-spacing: -0.24px;
          }
        }

        .col2 {
          display: flex;
          flex-direction: column;
          align-items: center;
          gap: 24px;
          flex: 1 0 0;

          .input {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            align-self: stretch;

            label {
              color: #575757;
              font-size: 14px;
              font-style: normal;
              font-weight: 500;
              line-height: 21px;
              letter-spacing: 0.21px;
              align-self: stretch;
            }

            input {
              display: flex;
              height: 45px;
              padding: 4px 10px;
              align-items: center;
              gap: 5px;
              align-self: stretch;
              border-radius: 4px;
              border: 1px solid #c4c4c4;
              background: #ffffff;
            }
          }
        }

        .col3 {
          display: flex;
          padding-top: 35px;
          flex-direction: column;
          align-items: flex-start;
          gap: 70px;
          align-self: stretch;

          .close {
            display: flex;
            width: 26px;
            height: 26px;
            justify-content: center;
            align-items: center;

            svg {
              width: 30px;
              height: 30px;
              flex-shrink: 0;
            }
          }

          .add {
            display: flex;
            width: 26px;
            height: 26px;
            justify-content: center;
            align-items: center;

            svg {
              width: 30px;
              height: 30px;
              flex-shrink: 0;
            }
          }
        }
      }
    }

    h2 {
      color: #4f4b4b;
      text-align: center;
      font-size: 24px;
      font-style: normal;
      font-weight: 700;
      line-height: 31px;
      letter-spacing: -0.24px;
    }

    .footer {
      display: flex;
      justify-content: center;
      align-items: flex-end;
      gap: 25px;
      background-color: #ffffff;
      padding-bottom: 1.5em;

      .cancel {
        width: 170px;
        height: 62px;
        justify-content: center;
        align-items: center;
        padding: 16px 48px;
        border-radius: 6px;
        background: #fafafa;
        color: #575757;
        text-align: center;
        font-size: 17px;
        font-style: normal;
        font-weight: 500;
        line-height: 30px;
        letter-spacing: -0.255px;
        border: none;
        cursor: pointer;
      }

      .ok {
        width: 250px;
        height: 62px;
        justify-content: center;
        align-items: center;
        border-radius: 6px;
        background: #eb5757;
        color: #ffffff;
        text-align: center;
        font-size: 17px;
        font-style: normal;
        font-weight: 500;
        line-height: 30px;
        letter-spacing: -0.255px;
        border: none;
        cursor: pointer;
      }
    }
  }
</style>
