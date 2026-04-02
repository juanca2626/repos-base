<script lang="ts" setup>
  import { computed, reactive, ref, toRef } from 'vue';
  import ModalItinerarioDetail from '@/quotes/components/modals/ModalItinerarioDetail.vue';
  import { useSiderBarStore } from '@/quotes/store/sidebar';
  import type { ServiceExtensionsResponse } from '@/quotes/interfaces/services';
  import { useQuoteServices } from '@/quotes/composables/useQuoteServices';
  import { useQuote } from '@/quotes/composables/useQuote';
  import { useI18n } from 'vue-i18n';

  const { t } = useI18n();

  // Store
  // TODO refactor to use composable instead
  const storeSidebar = useSiderBarStore();
  // const filesStore = useFilesStore();
  // composable
  const { searchParameters } = useQuoteServices();
  const { addExtension, accommodation, openItemService } = useQuote();

  // Props
  interface Props {
    service: ServiceExtensionsResponse;
  }

  const props = defineProps<Props>();

  // Service
  const service = toRef(props, 'service');

  const single = ref<number>(accommodation.value.single);
  const double = ref<number>(accommodation.value.double);
  const triple = ref<number>(accommodation.value.triple);

  const name = computed(() => {
    return service.value.translations && service.value.translations.length > 0
      ? service.value.translations[0].tradename
      : '';
  });

  const nights = computed(() => {
    return service.value.nights + 1 + 'D / ' + service.value.nights + 'N';
  });

  // const service_type = computed(() => {
  //   return (service.value.plan_rates && service.value.plan_rates.length>0 ) ? service.value.plan_rates[0].service_type_id : ''
  // })

  const type_class = computed(() => {
    return service.value.plan_rates &&
      service.value.plan_rates.length > 0 &&
      service.value.plan_rates[0].plan_rate_categories.length > 0
      ? service.value.plan_rates[0].plan_rate_categories[0].type_class_id
      : '';
  });

  const image = computed(() => {
    return service.value.image_link ? service.value.image_link : '';
  });

  const description = computed(() => {
    return service.value.translations && service.value.translations.length > 0
      ? service.value.translations[0].description_commercial
      : '';
  });

  const destination = computed(() => service.value.destinations);

  const state = reactive({
    modalHotelDetail: {
      isOpen: false,
    },
  });

  const toggleModalDetail = () => {
    state.modalHotelDetail.isOpen = !state.modalHotelDetail.isOpen;
  };

  // Add hotel to quotation
  const addService = async () => {
    if (!searchParameters.value!.type_service) {
      return false;
    }
    if (!type_class.value) {
      return false;
    }

    // agregamos la extension
    await addExtension(
      service.value.id,
      searchParameters.value!.type_service,
      type_class.value,
      searchParameters.value!.date,
      single.value,
      double.value,
      triple.value
    );

    // console.log('Agregando al Layer..', service.value);

    // DataLayer..
    /*
    window.dataLayer.push({
      event: 'add_to_cart',
      currency: 'USD',
      value: null,
      package_id: null,
      package_name: null,
      items: [
        {
          item_id: service.value.id,
          item_sku: service.value.aurora_code,
          item_name: service.value.name.toUpperCase(),
          price: null,
          item_brand: service.value.service_origin[0].state.iso,
          item_category: service.value.type,
          item_category2: 'single_product',
          item_list_id: null,
          item_list_name: null,
        },
      ],
    });
    */

    storeSidebar.setStatus(false, '', '');
    // cargamos la cotizacion
    openItemService.value = true;
  };
</script>

<template>
  <div class="item">
    <h3>
      {{ name }}
      <span>{{ nights }}</span>
    </h3>
    <div class="img">
      <img :src="image" onerror="this.src='../../../images/quotes/1.png'" />
      <!-- <div class="tag orange">
        <span>{{ type }}</span>
      </div> -->
    </div>
    <div class="place">
      <div v-if="destination">
        <svg
          xmlns="http://www.w3.org/2000/svg"
          width="16"
          height="16"
          viewBox="0 0 16 16"
          fill="none"
        >
          <path
            d="M7.16672 9.58706V13.6676L7.74017 14.5275C7.86387 14.7129 8.13653 14.7129 8.26023 14.5275L8.83342 13.6676V9.58706C8.56284 9.63706 8.28497 9.66675 8.00007 9.66675C7.71517 9.66675 7.4373 9.63706 7.16672 9.58706ZM8.00007 1.33325C5.92894 1.33325 4.25 3.01219 4.25 5.08333C4.25 7.15446 5.92894 8.8334 8.00007 8.8334C10.0712 8.8334 11.7501 7.15446 11.7501 5.08333C11.7501 3.01219 10.0712 1.33325 8.00007 1.33325ZM8.00007 3.31246C7.02349 3.31246 6.22921 4.10674 6.22921 5.08333C6.22921 5.25572 6.0891 5.39583 5.9167 5.39583C5.7443 5.39583 5.60419 5.25572 5.60419 5.08333C5.60419 3.76221 6.67921 2.68745 8.00007 2.68745C8.17247 2.68745 8.31258 2.82755 8.31258 2.99995C8.31258 3.17235 8.17247 3.31246 8.00007 3.31246Z"
            fill="#919191"
          />
        </svg>
        {{ destination }}
      </div>
      <div @click="toggleModalDetail">
        <svg
          xmlns="http://www.w3.org/2000/svg"
          width="20"
          height="20"
          viewBox="0 0 20 20"
          fill="none"
        >
          <g clip-path="url(#clip0_8687_7815)">
            <path
              d="M9.99935 18.3334C14.6017 18.3334 18.3327 14.6025 18.3327 10.0001C18.3327 5.39771 14.6017 1.66675 9.99935 1.66675C5.39698 1.66675 1.66602 5.39771 1.66602 10.0001C1.66602 14.6025 5.39698 18.3334 9.99935 18.3334Z"
              stroke="#5C5AB4"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
            <path
              d="M10 13.3333V10"
              stroke="#5C5AB4"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
            <path
              d="M10 6.66675H10.0083"
              stroke="#5C5AB4"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
          </g>
          <defs>
            <clipPath id="clip0_8687_7815">
              <rect width="20" height="20" fill="white" />
            </clipPath>
          </defs>
        </svg>
      </div>
    </div>
    <div class="description extension">
      <p>
        {{ description }}
      </p>
    </div>

    <div class="row-flex">
      <div class="quotes-actions-btn">
        <div class="content">
          <div class="text" @click="addService">
            {{ t('quote.label.select') }}
          </div>
        </div>
      </div>

      <!-- <div class="price">${{ price }} <span>por pasajero</span></div> -->
    </div>
  </div>

  <modal-itinerario-detail
    v-if="state.modalHotelDetail.isOpen"
    :title="name"
    :type="'extension'"
    :show="state.modalHotelDetail.isOpen"
    :extension="service"
    @close="toggleModalDetail"
  />
</template>

<style lang="scss" scoped>
  /*
  .sidebar {
    flex-shrink: 0;
    border-radius: 6px;
    border: 2px solid #e9e9e9;
    background: #fafafa;

    .header {
      display: flex;
      justify-content: space-between;
      border-bottom: 1px solid #c4c4c4;
      align-items: center;
      padding: 17px;

      h2 {
        color: #2e2e2e;
        font-size: 24px !important;
        font-style: normal;
        font-weight: 700;
        line-height: 36px;
        letter-spacing: -0.36px;
        margin-bottom: 0;
      }

      span {
        color: #eb5757;
        text-align: right;
        font-size: 14px;
        font-style: normal;
        font-weight: 700;
        line-height: 22px;
        cursor: pointer;
        letter-spacing: -0.21px;
        text-decoration-line: underline;
      }
    }

    .body {
      background: #fefefe;
      padding: 17px;

      .item {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
        max-width: 97%;
        flex-shrink: 0;
        margin-bottom: 16px;
        padding-bottom: 16px;
        border-bottom: 1px solid #909090;

        h3 {
          color: #4f4b4b;
          font-size: 18px !important;
          font-style: normal;
          font-weight: 500;
          line-height: normal;
          letter-spacing: -0.27px;
          margin-bottom: 0;
        }

        .img {
          width: 100%;
          height: 125px;
          overflow: hidden;

          img {
            width: 100%;
          }

          .tag {
            display: inline-flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            box-shadow: 0 4px 4px 0 rgba(0, 0, 0, 0.25);

            span {
              display: flex;
              height: 19px;
              padding: 0 5px;
              flex-direction: column;
              align-items: center;
              border-radius: 6px;
              background: #eb5757;
              color: #ffffff;
              text-align: center;
              font-size: 12px;
              font-style: normal;
              font-weight: 700;
              line-height: 19px;
              letter-spacing: 0.18px;
            }
          }
        }

        .place {
          display: flex;
          align-items: center;
          gap: 4px;
          align-self: stretch;
          justify-content: space-between;

          div:last-child {
            cursor: pointer;
          }
        }

        .description {
          align-self: stretch;
          display: flex;
          flex-direction: column;
          justify-content: center;
          flex-shrink: 0;
          color: #2e2e2e;
          font-size: 14px;
          font-style: normal;
          font-weight: 400;
          line-height: 22px;
          letter-spacing: -0.21px;

          &.extension {
            max-height: 72px;
            overflow: hidden;
            justify-content: flex-start;

            &:before {
              content: '...';
              background: #fff;
              position: absolute;
              bottom: 6px;
              right: 0;
              z-index: 1;
              padding: 0 3px;
            }
          }

          p {
            margin: 0;
          }
        }
      }
    }
  }
  */
</style>
