<script setup lang="ts">
  import { computed, ref, toRef } from 'vue';

  import IconClose from '@/quotes/components/icons/IconClose.vue';
  import ModalRoomDetail from '@/quotes/components/modals/ModalRoomDetail.vue';
  import ModalComponent from '@/quotes/components/global/ModalComponent.vue';
  import { useQuote } from '@/quotes/composables/useQuote';
  import { useSiderBarStore } from '@/quotes/store/sidebar';
  import type { GroupedServices, Hotel, QuoteServiceAddRequest } from '@/quotes/interfaces';

  import { useQuoteHotels } from '@/quotes/composables/useQuoteHotels';
  import useNotification from '@/quotes/composables/useNotification';

  import { useI18n } from 'vue-i18n';

  const { t } = useI18n();

  const storeSidebar = useSiderBarStore();

  const {
    quoteCategories,
    selectedCategory,
    serviceSelected: groupedService,
    quote,
    addServices,
    replaceService,
    openItemService,
  } = useQuote();

  const hotelService = computed(() => (groupedService.value as GroupedServices)?.service ?? null);

  const { searchParameters, promotions } = useQuoteHotels();

  const { showErrorNotification } = useNotification();

  // Props
  interface Props {
    hotel: Hotel;
  }

  const props = defineProps<Props>();

  // Emits
  interface Emits {
    (e: 'close'): void;
  }

  const emits = defineEmits<Emits>();

  const closeModal = () => {
    emits('close');
  };

  // Hotel selected data
  const hotel = toRef(props, 'hotel');
  // console.log(hotelService.value.new_extension_id);
  // Add hotel to quotation
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

    if (activeTab.value === 'hotel') {
      // Validando que elija como máximo uno de cada tipo de habitación
      let error = 0;
      let total_sgl = 0;
      let total_dbl = 0;
      let total_tpl = 0;
      hotel.value!.rooms.forEach((r) => {
        r.rates.forEach((rp) => {
          if (hotelRatesSelected.value[rp.rateId]) {
            if (r.occupation == 1) {
              total_sgl++;
            }
            if (r.occupation == 2) {
              total_dbl++;
            }
            if (r.occupation == 3) {
              total_tpl++;
            }
            if (total_sgl > 1 || total_dbl > 1 || total_tpl > 1) {
              error++;
            }
          }
        });
      });

      if (error > 0) {
        showErrorNotification(t('quote.label.allowed_per_room_type'));
        return;
      }

      let onRequest;
      let ratesId: number[] = [];

      hotel.value!.rooms.forEach((r) => {
        r.rates.forEach((r_p) => {
          if (hotelRatesSelected.value[r_p.rateId]) {
            onRequest = r_p.onRequest;
            ratesId.push(r_p.rateId);
          }
        });
      });

      if (ratesId.length > 0) {
        ratesToAdd.push({
          quote_id: quote.value.id,
          type: 'hotel',
          categories: categoriesId,
          object_id: hotel.value!.id,
          service_code: hotel.value!.code,
          date_in: searchParameters.value!.date_from,
          date_out: searchParameters.value!.date_to,
          service_rate_ids: ratesId,
          on_request: onRequest == 1 ? 0 : 1,
          adult: quote.value.people[0].adults,
          child: quote.value.people[0].child,
          // single: quote.value.accommodation.single,
          // double: quote.value.accommodation.double,
          // triple: quote.value.accommodation.triple,
          single: single,
          double: double,
          triple: triple,

          // Todo: implement this for hotel replacement
          // extension_parent_id: _extension_parent_id
          extension_parent_id: null,
          new_extension_id: hotelService.value ? hotelService.value.new_extension_id : null,
        });
      }
    } else {
      for (const [key, checked] of Object.entries(hotelRatesPromoSelected.value)) {
        if (checked) {
          const [hotelId, rateId] = key.split('-');

          const hotel = promotions.value.find((h) => h.id === Number(hotelId));

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
                // single: quote.value.accommodation.single,
                // double: quote.value.accommodation.double,
                // triple: quote.value.accommodation.triple,
                single: single,
                double: double,
                triple: triple,

                // Todo: implement this for hotel replacement
                // extension_parent_id: _extension_parent_id
                extension_parent_id: null,
                new_extension_id: hotelService.value ? hotelService.value.new_extension_id : null,
              });
            }

            const room = hotel.rooms.find((r) =>
              r.rates.find((rr) => rr.rateId === Number(rateId))
            );

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
    }

    if (ratesToAdd.length) {
      if (hotelService.value) {
        await replaceService(ratesToAdd);
        openItemService.value = true;
      } else {
        await addServices(ratesToAdd);
      }

      storeSidebar.setStatus(false, 'hotel', 'search');
    }
  };

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

  // Tab change handler
  const activeTab = ref<string>('hotel');
  // Rates selected
  const hotelRatesSelected = ref<{ [key: string | number]: boolean }>({});
  const hotelRatesPromoSelected = ref<{ [key: string | number]: boolean }>({});
</script>

<template>
  <ModalComponent :modal-active="true" class="modal-itinerario-detail" @close="closeModal">
    <template #body>
      <div class="icon-close-modal" @click="closeModal">
        <icon-close />
      </div>

      <ModalRoomDetail
        :accommodation="accommodation"
        :hotel="hotel"
        :hotel-rates-selected="hotelRatesSelected"
        :hotel-rates-promo-selected="hotelRatesPromoSelected"
        @change-tab="(val) => (activeTab = val)"
      />
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

<style scoped lang="scss"></style>
