<script setup lang="ts">
  import { reactive, computed, toRef } from 'vue';
  import { useQuote } from '@/quotes/composables/useQuote';
  import { useI18n } from 'vue-i18n';
  import { useQuoteServices } from '@/quotes/composables/useQuoteServices';
  import dayjs from 'dayjs';
  import QuoteHotelRoomsAndPromotions from '@/quotes/components/modals/ModalRoomAndPromotions.vue';
  import ModalItinerarioDetail from '@/quotes/components/modals/ModalItinerarioDetail.vue';
  import QuoteShiftServiceModal from '@/quotes/components/modals/QuoteShiftServiceModal.vue';
  import { useLanguagesStore } from '@/stores/global';
  import { getHotelById } from '@/quotes/helpers/get-hotel-by-id';
  import useNotification from '@/quotes/composables/useNotification';

  const { t } = useI18n();
  const languageStore = useLanguagesStore();
  const { searchParameters } = useQuoteServices();
  const { showErrorNotification, showSuccessNotification } = useNotification();
  const {
    quoteCategories,
    selectedCategory,
    serviceSelected,
    quote,
    addServices,
    replaceService,
    updateServiceDate,
  } = useQuote();

  const state = reactive({
    showHotelRoomsAndPromotions: false,
    showDetailModal: false,
    hotelSelectedDetail: null as any,
    modalAddServiceShift: {
      isOpen: false,
      pendingPayload: null as any,
      nextService: null as any,
      shiftDays: 0,
      insertionDate: null as string | null,
    },
    sectionToShow: 'default' as string,
  });

  const props = defineProps({
    service: { type: Object, required: true },
    type: { type: String, default: 'service' }, // 'service' or 'hotel'
    userType: { type: Number, default: 0 },
  });

  const service = toRef(props, 'service');

  const selectedTranslation = computed(() => {
    return service.value.selected_translation || service.value.service_translations?.[0] || {};
  });

  const name = computed(() => {
    if (props.type === 'hotel') return service.value.name;
    return selectedTranslation.value.name || service.value.name || 'No Name';
  });

  const description = computed(() => {
    if (props.type === 'hotel') return service.value.description;
    return selectedTranslation.value.description || selectedTranslation.value.itinerary || '';
  });

  const hasNotes = computed(() => {
    return !!(service.value.notes && service.value.notes !== '<p><br></p>');
  });

  const hasItinerary = computed(() => {
    return !!(
      selectedTranslation.value.itinerary && selectedTranslation.value.itinerary !== '<p><br></p>'
    );
  });

  const categoryName = computed(() => {
    if (props.type === 'hotel') return service.value.category?.name || '';
    return service.value.service_sub_category?.service_categories?.translations?.[0]?.value || '';
  });

  const price = computed(() => {
    if (props.type === 'hotel') return service.value.price;
    return service.value.service_rate?.[0]?.service_rate_plans?.[0]?.price_adult || 0;
  });

  const serviceImage = computed(() => {
    if (props.type === 'hotel') return service.value.galleries?.[0]?.url;
    if (service.value.galleries?.length) {
      return service.value.galleries[0].url;
    }
    return '';
  });

  const origin = computed(() => {
    if (props.type === 'hotel') {
      return `${service.value.state?.translations?.[0]?.value || ''}, ${service.value.country?.translations?.[0]?.value || ''}`;
    }
    const state = service.value.service_origin?.[0]?.state?.translations?.[0]?.value || '';
    const city = service.value.service_origin?.[0]?.city?.translations?.[0]?.value || '';
    return city && state !== city ? `${state}, ${city}` : state;
  });

  const handleAction = async () => {
    if (props.type === 'hotel') {
      state.showHotelRoomsAndPromotions = true;
      return;
    }

    const categoriesId: any[] = [];
    quoteCategories.value.forEach((c: any) => {
      if (selectedCategory.value === c.type_class_id) {
        categoriesId.push(c.id);
      }
    });

    // Determine insertion date - usually from search parameters
    const insertionDate = searchParameters.value!.date_from;
    const duration = service.value.duration || 1; // Default to 1 day if not specified

    const ratesToAdd = [
      {
        quote_id: quote.value.id,
        type: 'service',
        categories: categoriesId,
        object_id: service.value!.id,
        service_code: service.value!.aurora_code,
        date_in: insertionDate,
        date_out: insertionDate, // Ideally calculate based on duration
        service_rate_ids: [service.value!.service_rate[0].id],
        adult: quote.value.people[0].adults > 0 ? quote.value.people[0].adults : 1,
        child: quote.value.people[0].child > 0 ? quote.value.people[0].child : 0,
        single: 0,
        double: 0,
        triple: 0,
        extension_parent_id: null,
        new_extension_id:
          Object.keys(serviceSelected.value).length > 0
            ? serviceSelected.value.service.new_extension_id
            : null,
      },
    ];

    if (Object.keys(serviceSelected.value).length > 0) {
      await replaceService(ratesToAdd);
    } else {
      let nextService = null;
      let minDateDiff = Infinity;

      for (const cat of quote.value.categories) {
        if (!categoriesId.includes(cat.id)) continue;
        const services = cat.services || [];
        for (const s of services) {
          if (s.date) {
            const sDate = dayjs(s.date);
            const iDate = dayjs(insertionDate);
            if (sDate.isSame(iDate) || sDate.isAfter(iDate)) {
              const diff = sDate.diff(iDate, 'day');
              if (diff < minDateDiff) {
                minDateDiff = diff;
                nextService = s;
              }
            }
          }
        }
      }

      if (nextService) {
        state.modalAddServiceShift = {
          isOpen: true,
          pendingPayload: ratesToAdd,
          nextService: nextService,
          shiftDays: duration,
          insertionDate: insertionDate,
        };
      } else {
        await addServices(ratesToAdd);

        // Saved correctly..
        showSuccessNotification(t('quote.label.service_added_successfully'));
      }
    }
  };

  const confirmAddService = async (shift: boolean) => {
    state.modalAddServiceShift.isOpen = false;
    const { pendingPayload, nextService, shiftDays } = state.modalAddServiceShift;

    try {
      if (shift && nextService) {
        const currentNextDate = dayjs(nextService.date_in);
        const newDate = currentNextDate.add(shiftDays, 'day').format('YYYY-MM-DD');
        const originalServiceSelected = serviceSelected.value;
        serviceSelected.value = nextService;

        await updateServiceDate(newDate, null, null, null, null, [], true, true, false);
        serviceSelected.value = originalServiceSelected;
      }

      await addServices(pendingPayload);
      // Saved correctly..
      showSuccessNotification(t('quote.label.service_added_successfully'));
    } catch (e: any) {
      console.error('Error in confirmAddService', e);
      showErrorNotification(e.response.data.error);
    }
  };

  const openDetail = async () => {
    if (props.type === 'hotel') {
      state.hotelSelectedDetail = await getHotelById(
        service.value.id,
        languageStore.currentLanguage
      );
    }
    state.showDetailModal = true;
  };

  const openDetailVal = async (val: string) => {
    state.sectionToShow = val;
    await openDetail();
  };
</script>

<template>
  <div class="service-modal-item" :class="{ 'row-layout': props.userType === 3 }">
    <template v-if="props.userType === 3">
      <!-- Table Row Layout for User Type 3 -->
      <div class="col-code">{{ service.aurora_code || service.id }}</div>
      <div class="col-desc">
        <div class="desc-header">
          <span class="desc-title">{{ name }}</span>
        </div>
        <div class="desc-actions">
          <span class="action-link text-danger" @click="openDetailVal('inclusions')">
            <font-awesome-icon :icon="['fas', 'list-check']" />
            {{ $t('quote.label.included_not_included') }}
          </span>
          <span
            v-if="hasItinerary"
            class="action-link text-danger"
            @click="openDetailVal('itinerary')"
          >
            <font-awesome-icon :icon="['fas', 'list']" /> {{ $t('quote.label.itinerary') }}
          </span>
          <span class="action-link text-danger" @click="openDetailVal('schedule')">
            <font-awesome-icon :icon="['far', 'clock']" />
            {{ $t('quote.label.schedules_restrictions') }}
          </span>
          <span v-if="hasNotes" class="action-link text-danger" @click="openDetailVal('remarks')">
            {{ $t('quote.label.remarks') }}
          </span>
        </div>
      </div>
      <div class="col-rate">
        <div class="rate-option">
          <div class="radio-circle selected">
            <div class="inner-circle"></div>
          </div>
          <span class="rate-label">{{
            service.service_rate?.[0]?.name || $t('quote.label.regular_rate')
          }}</span>
        </div>
        <div class="rate-price">
          ${{ Math.round(price) }} <span class="badge-rq" v-if="service.on_request">RQ</span>
        </div>
      </div>
      <div class="col-action">
        <button class="btn-add-large" @click="handleAction">
          <font-awesome-icon :icon="['fas', 'plus']" />
        </button>
      </div>
    </template>

    <template v-else>
      <!-- Original Card Layout -->
      <div
        class="item-image"
        :style="{ backgroundImage: `url(${serviceImage})` }"
        @click="openDetail"
      >
        <div class="item-category-badge" v-if="categoryName">
          {{ categoryName }}
        </div>
      </div>
      <div class="item-info">
        <div class="item-header">
          <h4 class="item-name" @click="openDetail">{{ name }}</h4>
          <div class="item-price">
            <span class="currency">$</span>
            <span class="value">{{ price }}</span>
            <span class="per-person" v-if="props.type === 'hotel'">{{
              t('quote.label.per_room')
            }}</span>
            <span class="per-person" v-else>{{ t('quote.label.per_person') }}</span>
          </div>
        </div>

        <div class="item-origin">
          <i class="bi bi-geo-alt"></i>
          {{ origin }}
        </div>

        <div class="item-description" v-html="description"></div>

        <div class="item-footer">
          <a-button class="btn-action" @click="handleAction">
            <span v-if="props.type === 'hotel'">{{ t('quote.label.escolha_o_quarto') }}</span>
            <span v-else-if="Object.keys(serviceSelected).length > 0">{{
              t('quote.label.replace')
            }}</span>
            <span v-else>{{ t('quote.label.add') }}</span>
          </a-button>
        </div>
      </div>
    </template>

    <quote-hotel-rooms-and-promotions
      v-if="state.showHotelRoomsAndPromotions"
      :hotel="service"
      @close="state.showHotelRoomsAndPromotions = false"
    />

    <modal-itinerario-detail
      v-if="state.showDetailModal"
      :hotel="props.type === 'hotel' ? state.hotelSelectedDetail : null"
      :service="props.type !== 'hotel' ? service : null"
      :title="name"
      :type="props.type === 'hotel' ? 'group_header' : 'service'"
      :category="String(service.service_sub_category?.service_categories?.id || '')"
      :categoryName="categoryName"
      :show="state.showDetailModal"
      :section="state.sectionToShow"
      :serviceDateOut="searchParameters?.date_from"
      @close="state.showDetailModal = false"
    />

    <quote-shift-service-modal
      v-if="state.modalAddServiceShift.isOpen"
      :is-open="state.modalAddServiceShift.isOpen"
      :service-name="name"
      :insertion-date="state.modalAddServiceShift.insertionDate"
      @confirm="confirmAddService"
      @close="state.modalAddServiceShift.isOpen = false"
    />
  </div>
</template>

<style lang="scss" scoped>
  .service-modal-item {
    display: flex;
    gap: 16px;
    padding: 16px;
    border-radius: 8px;
    background: #ffffff;
    border: 1px solid #e8e8e8;
    transition: all 0.3s ease;
    margin-bottom: 12px;

    &:hover {
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
      border-color: #eb5757;
    }

    .item-image {
      flex-shrink: 0;
      width: 140px;
      height: 140px;
      border-radius: 8px;
      background-size: cover;
      background-position: center;
      position: relative;
      background-color: #f5f5f5;

      .item-category-badge {
        position: absolute;
        top: 8px;
        left: 8px;
        background: #eb5757;
        color: #ffffff;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
      }
    }

    .item-info {
      flex-grow: 1;
      display: flex;
      flex-direction: column;
      gap: 8px;

      .item-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;

        .item-name {
          margin: 0;
          font-size: 16px;
          font-weight: 700;
          color: #262626;
          line-height: 1.3;
        }

        .item-price {
          text-align: right;
          color: #eb5757;

          .currency {
            font-size: 14px;
            font-weight: 600;
            vertical-align: top;
            margin-right: 2px;
          }

          .value {
            font-size: 20px;
            font-weight: 800;
          }

          .per-person {
            display: block;
            font-size: 10px;
            color: #8c8c8c;
            text-transform: uppercase;
            font-weight: 500;
          }
        }
      }

      .item-origin {
        font-size: 13px;
        color: #8c8c8c;
        display: flex;
        align-items: center;
        gap: 4px;
      }

      .item-description {
        font-size: 13px;
        color: #595959;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        line-height: 1.5;
      }

      .item-footer {
        margin-top: auto;
        display: flex;
        justify-content: flex-end;

        .btn-action {
          background: #eb5757;
          border-color: #eb5757;
          color: #ffffff;
          border-radius: 6px;
          font-weight: 600;
          height: 36px;
          padding: 0 24px;
          transition: all 0.3s ease;

          &:hover {
            color: #eb5757;
            border-color: #eb5757;
            background-color: #ffffff;
          }
        }
      }
    }
    // Styles for Row Layout (User Type 3)
    &.row-layout {
      flex-direction: row;
      align-items: center;
      padding: 16px 10px;
      border-left: none;
      border-right: none;
      border-top: none;
      border-bottom: 1px solid #f0f0f0;
      border-radius: 0;
      margin-bottom: 0;
      gap: 0;

      &:hover {
        background-color: #fafafa;
        box-shadow: none;
        border-color: #f0f0f0;
      }

      .col-code {
        width: 100px;
        text-align: center;
        color: #595959;
        font-size: 13px;
      }

      .col-desc {
        flex: 1;
        padding-left: 20px;
        display: flex;
        flex-direction: column;
        gap: 6px;

        .desc-header {
          .desc-title {
            font-size: 14px;
            color: #595959;
            font-weight: 500; // Not bold as per image look
          }
        }

        .desc-actions {
          display: flex;
          gap: 15px;
          font-size: 12px;

          .action-link {
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 4px;
            text-decoration: underline;

            &:hover {
              color: #c00d0e;
            }
          }
        }
      }

      .col-rate {
        width: 120px;
        text-align: right;
        display: flex;
        flex-direction: column;
        align-items: flex-start; // Align left within the column
        justify-content: center;
        padding-left: 10px;

        .rate-option {
          display: flex;
          align-items: center;
          gap: 6px;
          margin-bottom: 2px;
          cursor: pointer;

          .radio-circle {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            border: 1px solid #1890ff;
            display: flex;
            align-items: center;
            justify-content: center;

            &.selected {
              .inner-circle {
                width: 8px;
                height: 8px;
                border-radius: 50%;
                background-color: #1890ff;
              }
            }
          }

          .rate-label {
            font-size: 12px;
            color: #595959;
            font-weight: 600;
          }
        }

        .rate-price {
          font-size: 16px;
          font-weight: 700;
          color: #595959;
          margin-left: 20px; // Indent to match label
          display: flex;
          align-items: center;
          gap: 5px;

          .badge-rq {
            background-color: #c00d0e;
            color: #fff;
            font-size: 10px;
            padding: 1px 4px;
            border-radius: 3px;
            font-weight: bold;
          }
        }
      }

      .col-action {
        width: 100px;
        display: flex;
        justify-content: center;
        align-items: center;

        .btn-add-large {
          background-color: #eb5757; // Light red
          border: none;
          color: white;
          width: 45px;
          height: 35px;
          border-radius: 6px;
          font-size: 18px;
          cursor: pointer;
          display: flex;
          align-items: center;
          justify-content: center;
          transition: background-color 0.3s;

          &:hover {
            background-color: #c00d0e; // Darker red
          }
        }
      }
    }
  }
</style>
