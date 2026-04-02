<script setup lang="ts">
  import { ref } from 'vue';
  import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

  import BoxComponent from '@/quotes/components/info/BoxComponent.vue';
  import QuotePassengersForm from '@/quotes/components/info/quote-header/QuoteNewPassengersForm.vue';
  import { useQuote } from '@/quotes/composables/useQuote';
  import { usePopup } from '@/quotes/composables/usePopup';
  import { useI18n } from 'vue-i18n';
  const { t } = useI18n();
  const { quoteNew } = useQuote();
  const { showForm, toggleForm } = usePopup();

  // props
  const modal = ref<string>('passenger');

  const setShowPopup = (
    value: boolean,
    adultsSelected: number,
    childSelected: number,
    agesSelected: []
  ) => {
    showForm.value = value;
    modal.value = 'occupation';

    quoteNew.value.adults = adultsSelected;
    quoteNew.value.child = childSelected;
    quoteNew.value.quoteChildAges = agesSelected;

    // showOcupationModal.value = true;
  };

  const incializar = () => {
    modal.value = 'passenger';
  };
</script>

<template>
  <BoxComponent :showEdit="false" class="extra" :title="t('quote.label.passengers')">
    <template #text>
      <div
        class="passengers-item"
        @click="
          toggleForm();
          incializar();
        "
      >
        <div class="item">
          <font-awesome-icon :style="{ fontSize: '13px' }" icon="user" />
          <span class="text">{{ quoteNew?.adults }}</span>
        </div>
        <div class="item child">
          <font-awesome-icon :style="{ fontSize: '16px' }" icon="child" />
          <span class="text">{{ quoteNew?.child }}</span>
        </div>
      </div>
    </template>
    <template #form>
      <QuotePassengersForm
        :show="showForm"
        @change="
          (value: boolean, adultsSelected: number, childSelected: number, agesSelected: []) =>
            setShowPopup(value, adultsSelected, childSelected, agesSelected)
        "
        v-if="modal == 'passenger'"
      />
    </template>
  </BoxComponent>

  <!-- <ModalComponent
    :modalActive="showOcupationModal"
    class="modal-passengers modal-assignator"
    @close="showOcupationModal = false"
  >
    <template #body>
      <div class="container">
        <div class="title">{{ t('quote.label.assign_accommodation') }}</div>
        <div class="body">
          <quote-occupation-assignator
            v-if="modal == 'occupation'"
            :adults="adults"
            :child="child"
            :childAges="childAges"
            @close="showOcupationModal = false"
          />
        </div>
      </div>
    </template>
  </ModalComponent> -->
</template>

<style lang="scss" scoped>
  .delete-rangos {
    color: #eb5757;
    text-align: left;
    font-size: 16px;
    cursor: pointer;
    width: 97%;
    align-items: center;
    display: flex;
    gap: 7px;

    span {
      position: relative;
      &:before {
        content: '';
        position: absolute;
        left: 0;
        right: 0;
        height: 1px;
        background: #eb5757;
        bottom: 3px;
      }
    }
  }

  :deep(.rooms-form) {
    width: 330px !important;
    .box {
      .amountN {
        width: 90px;
      }
    }
  }

  .button-container {
    svg {
      width: 15px;
      height: 15px;
    }
    &:hover {
      svg {
        color: #eb5757;
      }
    }
  }

  .per-range {
    background-color: #979797;
    color: white;
    font-size: 12px;
    font-weight: bold;
    border-radius: 6px;
    height: 19px;
    line-height: 19px;
    padding: 0 8px;
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
      gap: 30px;

      .title {
        display: flex;
        padding-left: 0;
        justify-content: center;
        align-items: flex-start;
        align-self: stretch;
        margin-bottom: 20px;
      }

      .body {
        display: flex;
        justify-content: center;
        align-items: flex-start;
        gap: 24px;
        align-self: stretch;
        overflow: hidden;
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
