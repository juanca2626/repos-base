<script lang="ts" setup>
  import DetailBoxExtensionComponent from '@/quotes/components/DetailBoxExtensionComponent.vue';
  import { computed, reactive, toRef, watchEffect } from 'vue';
  import moment from 'moment';
  import type { GroupedServices, QuoteService } from '@/quotes/interfaces';
  import ModalComponent from './global/ModalComponent.vue';
  import { VueDraggableNext as Draggable } from 'vue-draggable-next';
  import { useQuote } from '@/quotes/composables/useQuote';
  import IconAlert from '@/quotes/components/icons/IconAlert.vue';
  import IconError from '@/quotes/components/icons/IconError.vue';
  import IconBurger from '@/quotes/components/icons/IconBurger.vue';
  import IconEditDark from '@/quotes/components/icons/IconEditDark.vue';
  import IconCalendarLigth from '@/quotes/components/icons/IconCalendarLight.vue';
  import CheckBoxComponent from '@/quotes/components/global/CheckBoxComponent.vue';
  import IconAngleDown from '@/quotes/components/icons/IconAngleDown.vue';
  import IconBook from '@/quotes/components/icons/IconBook.vue';
  import IconTrashDark from '@/quotes/components/icons/IconTrashDark.vue';
  import IconPlusCleck from '@/quotes/components/icons/IconPlusCleck.vue';
  import ContentExtension from '@/quotes/components/modals/ContentExtension.vue';
  import type { ServiceExtensionsResponse } from '@/quotes/interfaces/services';
  import dayjs from 'dayjs';
  import { useI18n } from 'vue-i18n';
  import { useSiderBarStore } from '@/quotes/store/sidebar';
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

  const storeSidebar = useSiderBarStore();

  const { t } = useI18n();

  /*const props = defineProps({
  extension: []
});*/

  interface Props {
    items: Array;
    services: QuoteService[] | GroupedServices[];
    extension: ServiceExtensionsResponse;
    groupedExtension: GroupedServices;
  }

  interface GroupedServices {
    type: 'group_header' | 'hotel' | 'service';
    service: QuoteService;
    group: QuoteService[];
    groupedExtension: GroupedServices;
    selected: false;
  }

  const props = defineProps<Props>();
  const items = toRef(props, 'items');
  const services = toRef(props, 'services');
  const groupedExtension = toRef(props, 'groupedExtension');

  const day = computed(() => items.value[0].day);
  const type = computed(() => items.value[0].type);

  const state = reactive({
    opened: false,
    modalDetail: {
      isOpen: false,
    },
    modalDelete: {
      isOpen: false,
    },
    openOptional: false,
    openDelete: false,
  });

  const {
    orderServices,
    updateServicesOrder,
    updateServiceOptionalState,
    removeQuoteServices,
    operation,
    openItemService,
    setServiceEdit,
    setSearchEdit,
    serviceSelected: serviceSelectedQuote,
  } = useQuote();

  // Passengers

  const getWeekDay = (dateValue: string = '') => {
    if (!dateValue) return '';

    return dayjs(dateValue).format('dddd');
    // const WEEKDAYS = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado']
    // const indexDay = Number(dayjs(dateValue).format('d'))

    // return WEEKDAYS[indexDay]
  };

  const getDateInFormat = () => {
    //console.log(items.value[0].service.date_in_format)
    return items.value[0].service.date_in_format;
  };

  const getDateInstance = (date: string) => {
    return moment(date, 'DD/MM/YYYY');
  };

  const getDateIn = () => {
    return items.value[0].service.date_in;
  };
  const getDateOut = () => {
    //console.log(items.value);
    let cant = items.value.length - 1;
    return items.value[cant].service.date_in;
  };

  const dateIn = computed(() => {
    return getDateInstance(getDateIn()).format('MMM D, YYYY');
    // return props.service.date_in
  });

  const dateDay = computed(() => {
    return getWeekDay(getDateInFormat().toString());
    // return getDateInstance(getDateIn()).format("dddd");
  });

  const dateOut = computed(() => {
    return getDateInstance(getDateOut()).format('MMM D, YYYY');
  });

  const geFlightDestiny = () => {
    /*let flight: string = ""
    

    if(service.value.origin != '' && service.value.origin != null){
      flight = service.value.flight_orign.translations[0].value;
    }

    if(service.value.destiny != '' && service.value.destiny != null){ 
      flight = service.value.flight_destination.translations[0].value;
    }

    return flight; */
  };

  const getDestinationName = () => {
    if (type.value === 'service') {
      return items.value[0].service.service?.service_origin[0].state.translations[0].value;
    } else {
      return items.value[0].hotel?.state.translations[0].value;
    }
  };

  const getHotelTypeClass = () => {
    if (type.value === 'group_header') {
      return items.value[0].hotel?.class ?? '';
    } else {
      return items.value[0].service.service?.service_type.translations[0].value ?? 'NA';
    }

    return null;
  };

  const openDetail = () => {
    state.opened = !state.opened;
  };

  const getHotelTypeClassColor = () => {
    if (type.value === 'group_header') {
      return items.value[0].hotel?.color_class ?? '';
    }
  };

  const getServiceName = () => {
    return items.value[0].service.new_extension?.translations[0].tradename ?? 'NA';
    //return (service.value.translations && service.value.translations.length>0 ) ? service.value.translations[0].tradename : ''
  };

  const getServiceNights = () => {
    return (
      items.value[0].service.new_extension.nights +
      1 +
      'D / ' +
      items.value[0].service.new_extension.nights +
      'N'
    );
  };

  const toggleContentExtension = () => {
    state.modalDetail.isOpen = !state.modalDetail.isOpen;
  };

  const toggleDeleteExtension = () => {
    state.modalDelete.isOpen = !state.modalDelete.isOpen;
  };

  const toggleModalOptional = () => {
    state.openOptional = !state.openOptional;
  };

  const updateOptionalState = async () => {
    items.value.forEach((row) => {
      let quoteServiceId: number[];

      if (row?.type === 'service') {
        quoteServiceId = [row.service.id];
      } else if (row?.type === 'flight') {
        quoteServiceId = [row.service.id];
      } else {
        quoteServiceId = row.group.map((s) => s.id);
      }

      /*let optional = 1;
    if(items.value[0].service.optional === 1){
      optional = 0;
    }*/

      updateServiceOptionalState({
        optional: row.service.optional,
        quote_service_id: quoteServiceId,
      });
    });

    toggleModalOptional();
  };

  const updateDeleteState = async () => {
    items.value.forEach((row) => {
      let dataToRemove: QuoteService[];

      if (row?.type === 'group_header') {
        dataToRemove = row.group;
      } else if (row?.type === 'group_type_room') {
        dataToRemove = getRoomsByType(row.group, row.service.type_room_id!);
      } else {
        dataToRemove = [row.service!];
      }

      removeQuoteServices(dataToRemove);
      //serviceSelected.value = undefined;
    });
  };

  const selectItem = (e: boolean) => {
    services.value.forEach((row) => {
      if (row.service.new_extension_id === items.value[0].service.new_extension_id) {
        row.selected = e;
      }
    });
    //groupedService.value.selected = e
  };

  const getRoomsByType = (row, roomTypeId: number): QuoteService[] => {
    return row.filter((r) => r.service_rooms[0].rate_plan_room.room.room_type_id === roomTypeId);
  };

  const priceTotal = computed(() => {
    let price = 0;
    items.value.forEach((row) => {
      if (row.type === 'group_header') {
        row.group.forEach((itemGroup) => {
          price =
            Number(itemGroup.import_amount ? itemGroup.import_amount.total : 0) + Number(price);
        });
      } else if (row.type === 'flight') {
        //price = Number(row.service.import.total_amount) + Number(price)
      } else {
        //console.log(row.service.import.total_amount);
        price = Number(row.service.import.total_amount) + Number(price);
      }
    });
    //price = price > 0 ? '$' + price : '';

    //price = '$120';

    return price;
  });

  const errorValidations = computed(() => {
    let error = false;
    items.value.forEach((row) => {
      if (row.service.validations.length > 0) {
        error = true;
      }
    });

    return error;
  });

  const hotelTypeClass = computed(() => getHotelTypeClass());
  const hotelTypeClassColor = computed(() => getHotelTypeClassColor());
  const serviceName = computed(() => getServiceName());
  const serviceNights = computed(() => getServiceNights());
  const destinationName = computed(() => getDestinationName());
  const adultNumber = computed(() => {
    return items.value[0].service.adult;
  });
  const childNumber = computed(() => {
    return items.value[0].service.child;
  });

  watchEffect(() => {
    if (openItemService.value == true) {
      state.opened = true;
      openItemService.value = false;
    }
  });

  const editFormSearch = () => {
    setSearchEdit(groupedExtension);
  };

  const edit = () => {
    setServiceEdit(groupedExtension.value);
    storeSidebar.setStatus(true, 'extension_edit', 'edit');

    setTimeout(function () {
      let positionWindow = window.pageYOffset + 1;
      window.scrollTo({ top: positionWindow, behavior: 'smooth' });
    }, 50);
  };

  const classServiceSelectedQuote = computed(() => {
    // console.log("type: ",serviceSelectedQuote.value);
    if (Object.keys(serviceSelectedQuote.value).length > 0) {
      // console.log(groupedExtension.value, serviceSelectedQuote.value);

      if (serviceSelectedQuote.value) {
        if (
          groupedExtension.value.quote_service_id == serviceSelectedQuote.value.quote_service_id
        ) {
          return true;
        }
      } else {
        return false;
      }
    }
    return false;
  });
</script>

<template>
  <div v-if="items" class="quotes-extensiones">
    <div class="quotes-itineraries">
      <div class="quotes-itineraries-internal">
        <div class="quotes-itineraries-content">
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
              <div class="date">
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
                  :style="{ 'background-color': hotelTypeClassColor }"
                  class="tag-button superior"
                >
                  {{ hotelTypeClass }}
                </div>
              </div>
              <div class="status">
                <div class="icon" @click="toggleContentExtension()">
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
            :class="{ activeserviceselected: classServiceSelectedQuote }"
          >
            <div class="title-box">
              <div class="left">
                <CheckBoxComponent @checked="selectItem" />
                <div class="icon">
                  <svg
                    width="27"
                    height="12"
                    viewBox="0 0 27 12"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                  >
                    <path
                      d="M19.6533 1.65771V3.98165H7.34805V1.65771C7.34805 0.578872 6.04372 0.0385942 5.28083 0.801432L0.938674 5.14359C0.465754 5.61651 0.465754 6.38318 0.938674 6.8561L5.28083 11.1983C6.04367 11.9611 7.34805 11.4208 7.34805 10.342V8.01809H19.6533V10.342C19.6533 11.4209 20.9576 11.9611 21.7205 11.1983L26.0626 6.85615C26.5355 6.38323 26.5355 5.61656 26.0626 5.14364L21.7205 0.801482C20.9576 0.0385938 19.6533 0.578872 19.6533 1.65771Z"
                      fill="#3D3D3D"
                    />
                  </svg>
                </div>
                <div class="title">
                  {{ serviceName }} <span>{{ serviceNights }}</span>
                  <icon-error v-if="errorValidations" color="#FF3B3B" />
                </div>
              </div>
              <div class="right">
                <div class="cost">
                  <div class="actions-box">
                    <div :class="{ opened: state.opened }" class="down" @click="openDetail">
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
                      <div class="action">
                        <div class="icon" @click="edit">
                          <a-tooltip placement="bottom">
                            <template #title>
                              <span> {{ t('quote.label.edit') }}</span>
                            </template>
                            <icon-edit-dark />
                          </a-tooltip>
                        </div>
                      </div>

                      <div class="action">
                        <div class="icon" @click="editFormSearch">
                          <a-tooltip placement="bottom">
                            <template #title>
                              <span> {{ t('quote.label.plusCheck') }}</span>
                            </template>
                            <icon-plus-cleck />
                          </a-tooltip>
                        </div>
                      </div>

                      <div class="action">
                        <div class="icon" @click="toggleDeleteExtension">
                          <a-tooltip placement="bottom">
                            <template #title>
                              <span> {{ t('quote.label.delete_btn') }}</span>
                            </template>
                            <icon-trash-dark />
                          </a-tooltip>
                        </div>
                      </div>
                      <div class="action">
                        <div class="icon" @click="toggleModalOptional()">
                          <a-tooltip placement="bottom">
                            <template #title>
                              <span> {{ t('quote.label.text_optional') }}</span>
                            </template>
                            <icon-book />
                          </a-tooltip>
                        </div>
                      </div>
                    </div>
                    <div class="text" v-if="operation == 'passengers'">
                      $ {{ displayPrice(priceTotal) }} <span>ADL</span>
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
            <div v-if="state.opened" class="detail">
              <!--            <TransportComponent v-if="serviceType === 'tours'"/>-->

              <draggable
                class="dragArea"
                ghost-class="ghost"
                @change="orderServices"
                @end="updateServicesOrder"
                :list="items"
                handle=".handle"
              >
                <DetailBoxExtensionComponent
                  v-for="(service, indexService) in items"
                  :totalService="totalService"
                  :item="indexService"
                  :grouped-service="service"
                />
              </draggable>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!--<modal-extension-detail
        v-if="state.modalDetail.isOpen"
        :hotel="hotelSelected"
        :title="serviceName"
        :description="serviceContent"
        :show="state.modalDetail.isOpen"
        @close="claseHotelDetailModal"
    />-->

    <ModalComponent
      :modalActive="state.modalDelete.isOpen"
      class="modal-eliminarservicio"
      @close="toggleDeleteExtension"
    >
      <template #body>
        <h3 class="title">{{ t('quote.label.detele_service') }}</h3>
        <div class="description">
          {{ t('quote.label.removing_extension') }}
          <b
            >{{ serviceName }} <span>{{ serviceNights }}</span></b
          >. {{ t('quote.label.youre_sure') }}
        </div>
      </template>
      <template #footer>
        <div class="footer">
          <button :disabled="false" class="cancel" @click="toggleDeleteExtension">
            {{ t('quote.label.return') }}
          </button>
          <button :disabled="false" class="ok" @click="updateDeleteState">
            {{ t('quote.label.yes_continue') }}
          </button>
        </div>
      </template>
    </ModalComponent>

    <ModalComponent
      :modalActive="state.modalDetail.isOpen"
      class="modal-contenExtension"
      @close="toggleContentExtension"
    >
      <template #body>
        <div class="container-modal">
          <!-- <div class="titlePopup"><h4>{{ serviceName }}  <span>{{ serviceNights }}sssss</span></h4></div>
          <div class="container-flex"><p>{{ serviceContent }}</p></div> -->

          <ContentExtension :extension="extension" />
        </div>
      </template>
    </ModalComponent>

    <ModalComponent
      :modalActive="state.openOptional"
      class="modal-eliminarservicio"
      @close="toggleModalOptional"
    >
      <template #body>
        <h3 class="title">{{ t('quote.label.optional_service') }}</h3>
        <div class="description">
          {{ t('quote.label.extension_marked_optional') }}
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
  .quotes-extensiones {
    & > .quotes-itineraries {
      width: 100%;
      border: 0;
      margin: 0;
    }

    .modal-inner {
      max-width: 920px !important;
      overflow: auto;
      max-height: 90%;
    }

    .modal-eliminarservicio .modal-inner {
      max-width: 435px !important;
    }
  }

  .container-modal {
    display: flex;
    flex-direction: column;
    padding: 0 20px 30px;
    gap: 35px;

    .titlePopup {
      display: flex;
      flex-direction: column;
      padding: 31px 0 0 0;

      h4 {
        font-size: 36px;
        font-style: normal;
        font-weight: 400;
        line-height: 43px; /* 119.444% */
        letter-spacing: -0.36px;
        color: #212529;
        margin: 0;
      }
    }

    .container-flex {
      p {
        font-size: 18px;
        font-style: normal;
        font-weight: 400;
        line-height: 25px;
      }

      a {
        color: #eb5757;
        font-size: 16px;
        font-style: normal;
        font-weight: 500;
        line-height: 23px; /* 143.75% */
        letter-spacing: -0.24px;

        svg {
          vertical-align: middle;
          margin-right: 5px;
        }

        span {
          position: relative;

          &:before {
            content: '';
            position: absolute;
            left: 0;
            right: 0;
            bottom: -3px;
            height: 1.5px;
            background: #eb5757;
            border-radius: 2px;
          }
        }
      }
    }
  }
</style>
