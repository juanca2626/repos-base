<script setup>
  import { onMounted, computed, defineProps, reactive, watch } from 'vue';
  import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
  import ToursService from './services/ToursService.vue';
  import HotelsService from './services/HotelsService.vue';
  import MealsService from './services/MealsService.vue';
  import TransfersService from './services/TransfersService.vue';
  import ExtensionsService from './services/ExtensionsService.vue';
  import Miscellaneous from './services/Miscellaneous.vue';
  import { useSiderBarStore } from '../store/sidebar';
  import IconTours from '@/quotes/components/icons/IconTours.vue';
  import IconHotels from '@/quotes/components/icons/IconHotels.vue';
  import IconFoods from '@/quotes/components/icons/IconFoods.vue';
  import IconTranfers from '@/quotes/components/icons/IconTranfers.vue';
  import IconExtensions from '@/quotes/components/icons/IconExtensions.vue';
  import IconMiscellaneous from '@/quotes/components/icons/IconMiscellaneous.vue';
  import { useFilesStore } from '@store/files';
  // import type {
  //   QuoteService,
  // } from '@/quotes/interfaces/quote.response';
  import { useQuote } from '@/quotes/composables/useQuote';
  import { useQuoteServices } from '@/quotes/composables/useQuoteServices';
  import { useQuoteHotels } from '@/quotes/composables/useQuoteHotels';
  import { useI18n } from 'vue-i18n';

  const emit = defineEmits(['updateFlagSearched']);

  const { t } = useI18n();
  const filesStore = useFilesStore();

  const storeSidebar = useSiderBarStore();

  const props = defineProps({
    defaultOpen: Boolean,
    defaultCategory: String,
    showHeaderIcon: Boolean,
    showCheckbox: Boolean,
    showHotels: { type: Boolean, default: true },
    showExtensions: { type: Boolean, default: true },
    isFile: { type: Boolean, default: false },
    inModal: { type: Boolean, default: false },
  });

  const { serviceSearch: serviceSearchQuote, deleteServiceSelected } = useQuote();
  const { services, extensions } = useQuoteServices();
  const { hotels } = useQuoteHotels();

  const state = reactive({
    isClosed: !props.defaultOpen, // Cambiar a false si se proporciona defaultOpen: true
    isClosedBody: false,
    selectedService: props.defaultCategory || 'tours', // Establecer la categoría predeterminada si se proporciona defaultCategory
    showHeaderIcon: props.showHeaderIcon || true,
    serviceSearchQuote: [],
    showResults: false,
  });

  const itemsTabs = [
    { id: 9, name: 'tours' },
    { id: 2, name: 'tours' },
    { id: 4, name: 'tours' },
    { id: 11, name: 'misc' },
    { id: 1, name: 'transfers' },
    { id: 10, name: 'meals' },
  ];

  // const serviceSearch = ref<QuoteService>();

  const classServiceSearchQuote = computed(() => {
    state.selectedService = '';
    state.serviceSearchQuote = [];
    if (Object.keys(serviceSearchQuote.value).length === 0) return;
    const { extensions, service } = serviceSearchQuote.value;
    storeSidebar.setStatus(false, '', '');
    if (extensions && extensions.length > 0) {
      timeSelect('extensions');
    } else if (service) {
      const idTab = service.service_sub_category?.service_category_id;
      const item = itemsTabs.find((item) => item.id === idTab);
      if (item) {
        timeSelect(item.name);
      }
    } else {
      timeSelect('hotels');
    }
    state.serviceSearchQuote = serviceSearchQuote.value;
    // unsetSearchEdit()
  });

  // Watch for results to show them when in modal mode
  watch(
    [services, hotels, extensions],
    () => {
      if (!props.inModal) return;

      if (
        (state.selectedService === 'hotels' && hotels.value.length > 0) ||
        (state.selectedService === 'extensions' && extensions.value.length > 0) ||
        services.value.length > 0
      ) {
        state.showResults = true;
      }
    },
    { deep: true }
  );

  const timeSelect = (name) => {
    setTimeout(() => {
      state.selectedService = name;
      state.isClosedBody = true;
      state.isClosed = false;
    }, 10);
  };

  const toggleService = () => {
    state.isClosedBody = !state.isClosedBody;
    state.isClosed = !state.isClosed;
    //state.selectedService = ''
    if (state.selectedService === '') {
      state.selectedService = 'tours';
    }
  };

  const toggleCategory = (tab) => {
    state.isClosedBody = true;
    state.isClosed = false;
    state.showResults = false;

    if (state.selectedService !== tab) {
      emit('updateFlagSearched', false);
      state.selectedService = tab;
      services.value = [];
      extensions.value = [];
      hotels.value = [];
    }

    if (!props.isFile) {
      deleteServiceSelected();
    }
  };

  const topIcon = computed(
    () =>
      //state.isClosed ? "chevron-up" : "chevron-down"
      (state.isClosed = 'chevron-down')
  );

  onMounted(() => {
    if (props.defaultOpen) {
      if (!props.isFile || typeof filesStore.getFileItinerary.service_category_id === 'undefined') {
        toggleCategory(props.defaultCategory);
      } else {
        let category = Number(filesStore.getFileItinerary.service_category_id);

        switch (category) {
          case 9:
            toggleCategory('tours');
            break;
          case 10:
            toggleCategory('meals');
            break;
          case 1:
            toggleCategory('transfers');
            break;
          case 14:
            toggleCategory('misc');
            break;
        }
      }
    }
  });
</script>

<template>
  <div id="quotes-add-service" :class="{ 'mb-0 in-modal': inModal }">
    <div :class="{ close: state.isClosed }" class="top" v-if="!props.inModal">
      <span class="title">{{ t('quote.label.add_service') }}</span>
      <span class="icon" @click="toggleService()" v-if="!showHeaderIcon">
        <font-awesome-icon
          :icon="topIcon"
          :style="{
            color: '#FEFEFE',
            fontSize: '20px',
            cursor: 'pointer',
          }"
        />
      </span>
    </div>
    <div class="categories" :class="classServiceSearchQuote">
      <div class="services-header" :class="{ active: !state.isClosed }">
        <div
          :class="{
            active: (state.selectedService === 'tours') & !state.isClosed,
          }"
          class="categories-item"
          @click="toggleCategory('tours')"
        >
          <!--<div class="top-line"></div>-->
          <span class="icon">
            <icon-tours />
          </span>
          <span class="text">{{ t('quote.label.tours') }}</span>
        </div>
        <div
          v-if="showHotels"
          :class="{ active: (state.selectedService === 'hotels') & !state.isClosed }"
          class="categories-item"
          @click="toggleCategory('hotels')"
        >
          <!--<div class="top-line"></div>-->
          <span class="icon">
            <icon-hotels />
          </span>
          <span class="text">{{ t('quote.label.hotels') }}</span>
        </div>
        <div
          :class="{
            active: (state.selectedService === 'meals') & !state.isClosed,
          }"
          class="categories-item"
          @click="toggleCategory('meals')"
        >
          <!--<div class="top-line"></div>-->
          <span class="icon">
            <icon-foods />
          </span>
          <span class="text">{{ t('quote.label.foods') }}</span>
        </div>
        <div
          :class="{
            active: (state.selectedService === 'transfers') & !state.isClosed,
          }"
          class="categories-item"
          @click="toggleCategory('transfers')"
        >
          <!--<div class="top-line"></div>-->
          <span class="icon">
            <icon-tranfers />
          </span>
          <span class="text">{{ t('quote.label.transfers') }}</span>
        </div>
        <div
          v-if="showExtensions"
          :class="{
            active: (state.selectedService === 'extensions') & !state.isClosed,
          }"
          class="categories-item"
          @click="toggleCategory('extensions')"
        >
          <!--<div class="top-line"></div>-->
          <span class="icon">
            <icon-extensions />
          </span>
          <span class="text">{{ t('quote.label.extensions') }}</span>
        </div>
        <div
          :class="{
            active: (state.selectedService === 'misc') & !state.isClosed,
          }"
          class="categories-item"
          @click="toggleCategory('misc')"
        >
          <!--<div class="top-line"></div>-->
          <span class="icon">
            <icon-miscellaneous />
          </span>
          <span class="text">{{ t('quote.label.miscellaneous') }}</span>
        </div>
      </div>
      <div v-if="state.isClosedBody" class="services-body" :class="state.selectedService">
        <ToursService
          v-if="state.selectedService === 'tours'"
          :itinerary="filesStore.getFileItinerary"
          :showCheckbox="showCheckbox"
          :isFile="isFile"
          :items="state.serviceSearchQuote"
          :modalMode="props.inModal"
        />
        <HotelsService
          v-if="state.selectedService === 'hotels'"
          :isFile="isFile"
          :itinerary="filesStore.getFileItinerary"
          :items="state.serviceSearchQuote"
          :modalMode="props.inModal"
        />
        <MealsService
          v-if="state.selectedService === 'meals'"
          :itinerary="filesStore.getFileItinerary"
          :isFile="isFile"
          :items="state.serviceSearchQuote"
          :modalMode="props.inModal"
        />
        <TransfersService
          v-if="state.selectedService === 'transfers'"
          :itinerary="filesStore.getFileItinerary"
          :isFile="isFile"
          :items="state.serviceSearchQuote"
          :modalMode="props.inModal"
        />
        <ExtensionsService
          v-if="state.selectedService === 'extensions'"
          :modalMode="props.inModal"
        />
        <Miscellaneous
          v-if="state.selectedService === 'misc'"
          :itinerary="filesStore.getFileItinerary"
          :isFile="isFile"
          :items="state.serviceSearchQuote"
          :modalMode="props.inModal"
        />
      </div>
    </div>
  </div>
</template>

<style lang="scss">
  @import '@/scss/variables';

  .ant-picker-panel-container,
  .ant-picker-dropdown {
    font-size: 14px;
  }

  #quotes-add-service {
    display: flex;
    flex-direction: column;
    align-items: center;
    border-radius: 6px;
    background: #fafafa;
    margin-bottom: 24px;

    .top {
      display: flex;
      padding: 8px 24px;
      justify-content: center;
      align-items: center;
      gap: 16px;
      align-self: stretch;
      border-radius: 6px 6px 0 0;
      background: #c4c4c4;

      .icon {
        svg {
          transform: rotate(180deg);
        }
      }

      &.top.close {
        background-color: #eb5757 !important;
        .icon {
          svg {
            transform: rotate(0);
          }
        }
      }

      .title {
        display: flex;
        height: 41px;
        flex-direction: column;
        justify-content: center;
        flex: 1 0 0;
        color: #fefefe;
        font-size: 18px;
        font-style: normal;
        font-weight: 700;
        line-height: 30px;
        letter-spacing: -0.27px;
      }
    }

    .categories {
      display: flex;
      width: 100%;
      flex-direction: column;

      .services-header {
        display: flex;
        align-items: center;
        align-self: stretch;
        border-right: 2px solid #e9e9e9;
        border-left: 2px solid #e9e9e9;
        border-radius: 0 0 6px 6px;
        width: 100%;

        &.active {
          padding-top: 7px;
          border-radius: 0;
          flex-wrap: wrap;
        }

        .categories-item {
          display: flex;
          padding: 12px 0;
          justify-content: center;
          align-items: center;
          gap: 7px;
          flex: 1;
          cursor: pointer;
          color: #909090;
          border-right: 1px solid #c4c4c4;
          border-bottom: 2px solid #e9e9e9;

          &:first-child {
            border-left: 0;
            &.active {
              &:before {
                left: -2px;
              }
            }
          }

          &:last-child {
            border-right: 0;
            &.active {
              &:before {
                right: -2px;
              }
            }
          }

          &.active {
            border-top-color: #eb5757 !important;
            color: #eb5757 !important;
            position: relative;

            &:before {
              height: 7px;
              position: absolute;
              background: #eb5757;
              left: -1px;
              top: -7px;
              content: '';
              right: -1px;
            }

            svg {
              path {
                fill: #eb5757 !important;
              }
            }
          }

          &:hover {
            border-top-color: #eb5757;
            color: #eb5757;

            svg {
              path {
                fill: #eb5757;
              }
            }
          }

          .icon {
            width: 31px;
            height: 31px;
          }

          .text {
            text-align: center;
            font-size: 18px;
            font-style: normal;
            font-weight: 700;
            line-height: 30px; /* 166.667% */
            letter-spacing: -0.27px;
          }
        }
      }

      .services-body {
        display: flex;
        padding: 24px;
        flex-direction: column;
        align-items: flex-start;
        gap: 24px;
        align-self: stretch;
        border-radius: 0 0 6px 6px;
        background: #fefefe;
        border: 2px solid #e9e9e9;
        border-top: 0;

        .container {
          width: 100%;
          display: flex;
          flex-direction: column;
          align-items: flex-start;
          align-self: stretch;
          gap: 0 !important;

          .row-box {
            display: flex;
            align-items: center;
            gap: 16px;
            align-self: stretch;

            .input-box {
              display: flex;
              padding: 0 1px;
              flex-direction: column;
              gap: 5px;
              flex: 1 0 0;

              &.miscellaneous-input {
                flex-grow: 0;
                flex-basis: 21%;
              }
              &.miscellaneous-cantidad {
                flex-grow: 0;
                flex-basis: 10%;
              }

              &.search {
                justify-content: center;
                align-items: flex-start;
                gap: 6px;
                flex: 1 0 0;

                .ant-row.ant-form-item {
                  width: 36.5%;
                }

                &.meals {
                  input {
                    width: 100%;
                    font-size: 14px;
                  }
                }

                &.miscellaneous {
                  flex-grow: 0;
                  flex-basis: 36%;
                  input {
                    width: 100%;
                    font-size: 14px;
                  }
                }

                input {
                  width: 100%;
                  font-size: 14px;
                }
              }

              label {
                color: #575757;
                /*font-size: 14px;*/
                font-style: normal;
                font-weight: 500;
                line-height: 21px;
                letter-spacing: 0.21px;
              }

              .ant-picker {
                border-radius: 4px;
                border: 1px solid #c4c4c4;
                background: #ffffff;
                height: 45px;
                width: 100%;
                /*font-size: 12px;*/

                &:hover {
                  border-color: #eb5757;

                  .ant-picker-suffix {
                    color: #eb5757;
                  }
                }

                input {
                  /*font-size: 14px;*/
                  width: 100%;
                }
              }

              .ant-select:not(.ant-select-disabled):hover .ant-select-selector {
                border-color: #eb5757;
              }

              .ant-select-selector {
                height: 45px;
                /*font-size: 14px;*/
              }

              .ant-input {
                font-size: 14px;
                height: 45px;
              }
            }

            .actions_buttons {
              display: flex;
              justify-content: flex-end;
              align-items: center;
              gap: 16px;

              .text {
                display: flex;
                padding: 4px 10px;
                justify-content: flex-end;
                align-items: center;
                gap: 10px;
                border-radius: 12px;
                cursor: pointer;

                svg {
                  width: 18px;
                  height: 18px;
                }

                span {
                  color: #eb5757;
                  font-family: 'Montserrat', sans-serif;
                  font-size: 16px;
                  font-style: normal;
                  font-weight: 400;
                  line-height: 24px; /* 150% */
                }
              }

              .search_button_container {
                display: flex;
                width: 193px;
                height: 59px;
                padding-top: 2px;
                justify-content: center;
                align-items: center;

                .search-button {
                  display: flex;
                  padding: 16px 39px;
                  align-items: center;
                  border-radius: 6px;
                  background: #eb5757;
                  cursor: pointer;

                  .content {
                    display: flex;
                    align-items: center;
                    gap: 12px;

                    .icon {
                      width: 20px;
                      height: 20px;

                      svg {
                        path {
                          stroke: #fff;
                        }
                      }
                    }

                    .text {
                      color: #ffffff;
                      font-size: 18px;
                      font-style: normal;
                      font-weight: 600;
                      line-height: 25px;
                    }
                  }
                }

                &.disabled {
                  opacity: 0.5;
                  cursor: not-allowed;
                  pointer-events: none;
                }
              }

              .text.disabled {
                opacity: 0.5;
                cursor: not-allowed;
                pointer-events: none;
              }
            }
          }
        }
      }
    }
  }

  // Styles for modal mode
  #quotes-add-service.in-modal {
    margin-bottom: 0;
    background: #ffffff;
    border-radius: 12px;
    height: auto; // Let it grow
    overflow: visible; // No internal scroll

    .categories {
      height: auto;
      min-height: 100%;
      display: flex;
      flex-direction: column;

      .services-header {
        background: #ffffff;
        border-right: none;
        border-left: none;
        border-bottom: 1px solid #f0f0f0;
        padding: 0 10px;

        .categories-item {
          flex-direction: column;
          padding: 12px 20px;
          min-width: 100px;
          gap: 4px;
          border-right: none;
          border-bottom: 3px solid transparent;

          &:before {
            display: none;
          }

          &.active {
            color: #c00d0e !important;
            border-bottom-color: #c00d0e !important;
            background: #fffafa;

            svg path {
              fill: #c00d0e !important;
            }
          }

          &:hover {
            color: #c00d0e;
            background: #fffafa;
            border-bottom-color: #f0f0f0;
          }

          .icon {
            width: 24px;
            height: 24px;
          }

          .text {
            font-size: 13px;
          }
        }
      }
    }
  }
</style>
