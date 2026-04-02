<script setup lang="ts">
  import { computed, ref, toRef } from 'vue';

  import IconClose from '@/quotes/components/icons/IconClose.vue';
  import ModalComponent from '@/quotes/components/global/ModalComponent.vue';
  import { useQuoteHotels } from '@/quotes/composables/useQuoteHotels';
  import { useQuote } from '@/quotes/composables/useQuote';
  import { useSiderBarStore } from '@/quotes/store/sidebar';
  import type { QuoteServiceAddRequest, GroupedServices, Hotel } from '@/quotes/interfaces';
  import useNotification from '@/quotes/composables/useNotification';
  import ContentPromotion from '@/quotes/components/modals/ContentPromotion.vue';
  import IconHotelsDark from '@/quotes/components/icons/IconHotelsDark.vue';
  import ModalRoomTypesAndQuantity from '@/quotes/components/modals/ModalRoomTypesAndQuantity.vue';
  import HotelPoliciesPopver from '@/quotes/components/modals/HotelPoliciesPopver.vue';
  import IconMagnifyingDollar from '@/quotes/components/icons/IconMagnifyingDollar.vue';

  import { useI18n } from 'vue-i18n';
  const { t } = useI18n();

  const storeSidebar = useSiderBarStore();
  // Composable
  const {
    quoteCategories,
    selectedCategory,
    serviceSelected: groupedService,
    quote,
    addServices,
    replaceService,
  } = useQuote();

  const { searchParameters, unsetHotelSelectedPromotions } = useQuoteHotels();

  const { showErrorNotification } = useNotification();

  // Props
  interface Props {
    hotels: Hotel[];
  }

  const props = defineProps<Props>();

  // Emits
  interface Emits {
    (e: 'close'): void;
  }

  const emits = defineEmits<Emits>();

  const closeModal = () => {
    unsetHotelSelectedPromotions();
    emits('close');
  };

  // Hotel selected
  const hotelService = computed(() => (groupedService.value as GroupedServices)?.service ?? null);

  // Accommodation
  const accommodation = ref({
    single: {
      checked: !!quote.value.accommodation.single,
      quantity: quote.value.accommodation.single,
    },
    double: {
      checked: !!quote.value.accommodation.double,
      quantity: quote.value.accommodation.double,
    },
    triple: {
      checked: !!quote.value.accommodation.triple,
      quantity: quote.value.accommodation.triple,
    },
  });

  // Hotels with better price available
  const hotels = toRef(props, 'hotels');

  const activeHotelPanels = ref([`hotel-panel-${hotels.value[0].id}`]);

  // Rate plans
  const ratePlansSelected = ref<{ [key: string | number]: boolean }>({});
  const checkRatePlanHandler = (isChecked: boolean, hotelRateIndex: string) => {
    ratePlansSelected.value[hotelRateIndex] = isChecked;
  };

  // Add hotels to the quotation
  const addHotel = async () => {
    const categoriesId: number[] = [];
    quoteCategories.value.forEach((c) => {
      if (selectedCategory.value === c.type_class_id) {
        categoriesId.push(c.id);
      }
    });

    if (categoriesId.length === 0) {
      showErrorNotification(t('quote.validations.rq_category'));
      return;
    }

    let single = 0;
    let double = 0;
    let triple = 0;
    if (accommodation.value.single.checked) {
      single = accommodation.value.single.quantity;
    }

    if (accommodation.value.double.checked) {
      double = accommodation.value.double.quantity;
    }

    if (accommodation.value.triple.checked) {
      triple = accommodation.value.triple.quantity;
    }

    const ratesToAdd: QuoteServiceAddRequest[] = [];

    for (const [key, checked] of Object.entries(ratePlansSelected.value)) {
      if (checked) {
        const [hotelId, rateId] = key.split('-');

        const hotel = hotels.value.find((h) => h.id === Number(hotelId));

        if (hotel) {
          if (!ratesToAdd.find((h) => h.object_id === hotel.id)) {
            ratesToAdd.push({
              quote_id: quote.value.id,
              type: 'hotel',
              categories: categoriesId,
              object_id: hotel.id,
              service_code: hotel.code,
              date_in: searchParameters.value!.date_from,
              date_out: searchParameters.value!.date_to,
              service_rate_ids: [],
              on_request: 0,
              adult: quote.value.people[0].adults,
              child: quote.value.people[0].child,
              single: single,
              double: double,
              triple: triple,
              extension_parent_id: null,
            });
          }

          const room = hotel.rooms.find((r) => r.rates.find((rr) => rr.rateId === Number(rateId)));

          if (room) {
            const rate = room.rates.find((rr) => rr.rateId === Number(rateId));

            if (rate) {
              const hotelToAdd = ratesToAdd.find((h) => h.object_id === hotel.id);

              hotelToAdd!.service_rate_ids.push(Number(rateId));
              hotelToAdd!.on_request = rate.onRequest;
            }
          }
        }
      }
    }

    if (ratesToAdd.length) {
      if (hotelService.value) {
        await replaceService(ratesToAdd);
      } else {
        await addServices(ratesToAdd);
      }

      storeSidebar.setStatus(false, 'hotel', 'search');
    }
  };

  const visibilityRoom = (occupation: number) => {
    if (occupation == 1 && accommodation.value.single.checked === true) {
      return true;
    }

    if (occupation == 2 && accommodation.value.double.checked === true) {
      return true;
    }

    if (occupation == 3 && accommodation.value.triple.checked === true) {
      return true;
    }

    if (
      accommodation.value.single.checked === false &&
      accommodation.value.double.checked === false &&
      accommodation.value.triple.checked === false
    ) {
      return true;
    }

    return false;
  };
</script>

<template>
  <ModalComponent :modal-active="true" class="modal-itinerario-detail" @close="closeModal">
    <template #body>
      <div class="icon-close-modal" @click="closeModal">
        <icon-close />
      </div>
      <div class="container">
        <div class="title-popup">
          <h4>
            <icon-magnifying-dollar />
            {{ t('quote.label.promotions') }}
          </h4>

          <div class="clases">
            <div class="category promo">
              {{ t('quote.label.all_categories') }}
            </div>
          </div>
        </div>

        <modal-room-types-and-quantity :accommodation="accommodation" />

        <div class="content">
          <a-collapse v-model:activeKey="activeHotelPanels" expand-icon-position="left" accordion>
            <a-collapse-panel v-for="hotel of hotels" :key="`hotel-panel-${hotel.id}`">
              <template #extra>
                <div class="hotel-header">
                  <div class="flex">
                    <icon-hotels-dark />
                    {{ hotel.name }}
                    <hotel-policies-popver :hotel="hotel" />
                  </div>
                </div>
              </template>
              <div v-for="room of hotel.rooms" :key="`promo-room-${hotel.id}-${room.room_id}`">
                <div v-if="visibilityRoom(room.occupation)">
                  <ContentPromotion
                    v-for="rate of room.rates"
                    :key="`promo-room-${hotel.id}-${room.room_id}-${rate.rateId}`"
                    :hotel="hotel"
                    :room-name="room.name"
                    :room-description="room.description"
                    :rate-plan="rate"
                    :is-checked="ratePlansSelected[`${hotel.id}-${rate.rateId}`] ?? false"
                    @checked="
                      (isChecked) => checkRatePlanHandler(isChecked, `${hotel.id}-${rate.rateId}`)
                    "
                  />
                </div>
              </div>
            </a-collapse-panel>
          </a-collapse>
        </div>
      </div>
    </template>

    <template #footer>
      <div class="footer">
        <button :disabled="false" class="cancel" @click="closeModal">
          {{ t('quote.label.cancel') }}
        </button>
        <button :disabled="false" class="ok" @click="addHotel">
          {{ t('quote.label.save') }}
        </button>
      </div>
    </template>
  </ModalComponent>
</template>

<style scoped lang="scss">
  .flex {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 18px;
    font-weight: 700;
    color: #3d3d3d;
  }

  .container {
    display: flex;
    flex-direction: column;
    padding: 0 20px;

    .title-popup {
      display: flex;
      flex-direction: column;

      h4 {
        color: #3d3d3d;
        font-size: 36px;
        font-style: normal;
        font-weight: 600;
        line-height: 43px; /* 119.444% */
        letter-spacing: -0.36px;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
      }

      svg {
        outline: none !important;
      }
    }

    .content {
      height: 40vh;
      overflow: auto;
      margin-bottom: 10px;

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

      .hotel-header {
        display: block;
        .hotel-policies {
          font-family: Montserrat;
          font-size: 0.75rem;
          font-style: normal;
          font-weight: 400;
          line-height: 1.188rem; /* 158.333% */
          letter-spacing: 0.011rem;
          max-width: 240px;

          h4 {
            font-size: 0.75rem;
            font-weight: 700;
            margin-bottom: 0;
          }

          &-general,
          &-cancelation {
            padding: 10px;
          }

          &-cancelation {
            background-color: #fff2f2;
          }
        }
      }
    }

    .clases {
      display: flex;
      align-items: center;
      padding: 5px 0 0 0;
      gap: 10px;

      .category {
        display: flex;
        height: 27px;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        background: #4c90e0;
        border-radius: 6px;
        color: #fff;
        font-weight: 400;
        font-size: 14px;
        padding: 10px 20px;
        min-width: 120px;

        &.promo {
          background: rgba(224, 69, 61, 1);
        }
      }
    }
  }

  :deep(.ant-collapse) {
    border: 0;

    .ant-collapse-item,
    .ant-collapse-content {
      border: 0;
    }
    .ant-collapse-item > .ant-collapse-header .ant-collapse-extra {
      margin: 0;
    }

    .ant-collapse-item > .ant-collapse-header {
      flex-direction: column-reverse;
    }

    .ant-collapse-item > .ant-collapse-header .ant-collapse-expand-icon {
      position: absolute;
      right: 0;
      top: 50%;
      margin-top: -12px;
    }
  }

  :deep(.ant-collapse) {
    .ant-collapse-content-box {
      background: unset;
      padding: 0 !important;
    }
    .ant-collapse-arrow svg {
      color: #eb5757;
      font-size: 16px;
    }
  }
</style>
