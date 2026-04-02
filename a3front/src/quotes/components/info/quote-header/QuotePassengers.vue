<script setup lang="ts">
  import { ref, computed, watchEffect, inject } from 'vue';
  import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

  import IconTrashDark from '@/quotes/components/icons/IconTrashDark.vue';
  import BoxComponent from '@/quotes/components/info/BoxComponent.vue';
  import QuotePassengersForm from '@/quotes/components/info/quote-header/QuotePassengersForm.vue';
  import ModalComponent from '@/quotes/components/global/ModalComponent.vue';
  import QuoteOccupationAssignator from '@/quotes/components/info/quote-header/QuoteOccupationAssignatorNew.vue';
  import { useQuote } from '@/quotes/composables/useQuote';
  import { usePopup } from '@/quotes/composables/usePopup';
  import { useI18n } from 'vue-i18n';
  const { t } = useI18n();
  const { operation, people, saveQuoteRanges, quote, processing } = useQuote();
  const { showForm, toggleForm } = usePopup();

  const adults = ref<number>(0);
  const child = ref<number>(0);
  const childAges = ref<[]>([]);

  // props
  const showOcupationModal = ref<boolean>(false);
  const showRangesModal = ref<boolean>(false);
  const modal = ref<string>('passenger');
  const show_range = inject('show-range');

  const ranges = computed(() => quote.value.ranges);

  watchEffect(() => {
    if (quote.value.ranges.length == 0) {
      quote.value.ranges.push({ from: 1, to: 2 });
    }
  });

  const toggleModalPassenger = () => {
    showRangesModal.value = !showRangesModal.value;
  };
  const setRanges = async () => {
    await saveQuoteRanges(ranges.value);
    toggleModalPassenger();
  };

  const deleteRange = (index: number) => {
    ranges.value.splice(index, 1);
  };

  const addRange = () => {
    let maxRange = Math.max(...ranges.value.map((o) => o.to));
    ranges.value.push({ from: maxRange + 1, to: maxRange + 2 });
  };

  const setShowPopup = (
    value: boolean,
    adultsSelected: number,
    childSelected: number,
    agesSelected: []
  ) => {
    console.log('Entra');
    showForm.value = value;
    modal.value = 'occupation';
    adults.value = adultsSelected;
    child.value = childSelected;
    childAges.value = agesSelected;
    showOcupationModal.value = true;
  };

  const incializar = () => {
    modal.value = 'passenger';
  };

  const deleteRangs = () => {
    showRangesModal.value = false;
    show_range.value = true;
  };
</script>

<template>
  <BoxComponent
    :showEdit="false"
    class="extra"
    :title="t('quote.label.passengers')"
    :disabled="processing"
  >
    <template #text>
      <div
        class="passengers-item"
        @click="
          toggleForm();
          incializar();
        "
        v-if="operation == 'passengers'"
      >
        <div class="item">
          <font-awesome-icon :style="{ fontSize: '13px' }" icon="user" />
          <span class="text">{{ people?.adults }}</span>
        </div>
        <div class="item child">
          <font-awesome-icon :style="{ fontSize: '16px' }" icon="child" />
          <span class="text">{{ people?.child }}</span>
        </div>
        <!--        <div class="item">-->
        <!--          <font-awesome-icon :style="{ fontSize: '14px' }" icon="baby-carriage"/>-->
        <!--          <span class="text">{{ passengers.infant }}</span>-->
        <!--        </div>-->
      </div>

      <div v-if="operation == 'ranges'" class="tag-button per-range">
        {{ t('quote.label.per_ranges') }}
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
    <template #actions>
      <div class="button-container" v-if="operation == 'passengers'">
        <font-awesome-icon :style="{ color: '#EB5757' }" icon="plus-circle" />
        <span class="text" @click="showRangesModal = true">
          {{ t('quote.label.add_ranges') }}
        </span>
      </div>
      <div class="button-container" v-if="operation == 'ranges'">
        <font-awesome-icon
          :style="{ fontSize: '14px' }"
          icon="pen-to-square"
          @click="showRangesModal = true"
        />
      </div>
    </template>
  </BoxComponent>

  <ModalComponent
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
  </ModalComponent>

  <ModalComponent
    :modalActive="showRangesModal"
    class="modal-passengers"
    @close="showRangesModal = false"
  >
    <template #body>
      <div class="container">
        <div class="title">
          <h2>{{ t('quote.label.ranges_passengers') }}</h2>
        </div>
        <div class="body">
          <!-- div class="col1">
            <span v-for="range of ranges.length" :key="`range-number-${range}`">
              {{ range }}
            </span>
          </div -->
          <div class="col2">
            <div class="input" v-for="(range, r) of ranges" :key="`range-from-${range}`">
              <label v-if="r === 0">{{ t('quote.label.from') }}</label>
              <input name="from" type="number" v-model="range.from" />
            </div>
          </div>
          <div class="col2">
            <div class="input" v-for="(range, r) of ranges" :key="`range-to-${range}`">
              <label v-if="r === 0">{{ t('quote.label.to') }}</label>
              <input name="from" type="number" v-model="range.to" />
            </div>
          </div>
          <div class="col3">
            <div
              v-for="(range, index) of ranges.length"
              :key="`range-action-${range}`"
              :class="{ close: range === 1, add: range > 1 }"
            >
              <span @click="deleteRange(index)" class="cursor-pointer">
                <svg
                  v-if="index > 0"
                  xmlns="http://www.w3.org/2000/svg"
                  width="30"
                  height="30"
                  viewBox="0 0 30 30"
                  fill="none"
                >
                  <path
                    d="M15 27.5C21.9036 27.5 27.5 21.9036 27.5 15C27.5 8.09644 21.9036 2.5 15 2.5C8.09644 2.5 2.5 8.09644 2.5 15C2.5 21.9036 8.09644 27.5 15 27.5Z"
                    stroke="#737373"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                  <path
                    d="M18.75 11.25L11.25 18.75"
                    stroke="#737373"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                  <path
                    d="M11.25 11.25L18.75 18.75"
                    stroke="#737373"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                </svg>
              </span>
              <span @click="addRange()" class="cursor-pointer">
                <svg
                  v-if="index == 0"
                  xmlns="http://www.w3.org/2000/svg"
                  width="30"
                  height="30"
                  viewBox="0 0 30 30"
                  fill="none"
                >
                  <path
                    d="M15 27.5C21.9036 27.5 27.5 21.9036 27.5 15C27.5 8.09644 21.9036 2.5 15 2.5C8.09644 2.5 2.5 8.09644 2.5 15C2.5 21.9036 8.09644 27.5 15 27.5Z"
                    stroke="#C63838"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                  <path
                    d="M15 10V20"
                    stroke="#C63838"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                  <path
                    d="M10 15H20"
                    stroke="#C63838"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                </svg>
              </span>
            </div>
          </div>
        </div>

        <div class="delete-rangos" @click="deleteRangs" v-if="operation == 'ranges'">
          <icon-trash-dark color="#D80404" /><span>{{ t('quote.label.delete_ranks') }}</span>
        </div>
      </div>
    </template>
    <template #footer>
      <div class="footer">
        <button :disabled="false" class="cancel" @click="toggleModalPassenger">
          {{ t('quote.label.cancel') }}
        </button>
        <button :disabled="false" class="ok" @click="setRanges">
          {{ t('quote.label.cancel_save') }}
        </button>
      </div>
    </template>
  </ModalComponent>
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
          // gap: 70px;
          gap: 45px;
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
      font-size: 24px !important;
      font-style: normal;
      font-weight: 700;
      line-height: 31px;
      letter-spacing: -0.24px;
    }
  }
</style>
