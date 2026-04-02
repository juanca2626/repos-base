<script lang="ts" setup>
  import { computed, reactive, toRef } from 'vue';
  import ModalItinerarioDetail from '@/quotes/components/modals/ModalItinerarioDetail.vue';
  import { useSiderBarStore } from '@/quotes/store/sidebar';
  import type { Service } from '@/quotes/interfaces/services';
  import { useQuoteServices } from '@/quotes/composables/useQuoteServices';
  import { useQuote } from '@/quotes/composables/useQuote';
  import { useI18n } from 'vue-i18n';
  import { getPriceWithCommission, hasCommission } from '@/utils/price';
  import { getUserType } from '@/utils/auth';
  import { useQuoteStore } from '@/quotes/store/quote.store';
  import { storeToRefs } from 'pinia';
  import { LeftCircleOutlined, RightCircleOutlined } from '@ant-design/icons-vue';
  import defaultImage from '@/images/quotes/1.png';
  const quoteStore = useQuoteStore();
  const { marketList } = storeToRefs(quoteStore);
  const user_type_id = parseInt(getUserType(), 10);

  // ✅ usar directamente el cliente guardado en el store
  const client = computed(() => marketList?.value.data || null);

  // ✅ Función para mostrar el precio con comisión
  const displayPrice = (price: number) => getPriceWithCommission(price, client.value, user_type_id);
  const showCommissionBadge = computed(() => hasCommission(client.value, user_type_id));

  const { t } = useI18n();

  // Store
  // TODO refactor to use composable instead
  const storeSidebar = useSiderBarStore();
  // const filesStore = useFilesStore();
  // composable
  const { searchParameters } = useQuoteServices();
  const { quoteCategories, selectedCategory, serviceSelected, quote, addServices, replaceService } =
    useQuote();

  // Props
  interface Props {
    service: Service;
  }

  const props = defineProps<Props>();

  // Service
  const service = toRef(props, 'service');

  const name = computed(() => {
    return service.value.service_translations[0].name
      ? service.value.service_translations[0].name
      : service.value.service_translations[0].name;
  });
  const description = computed(() => service.value.service_translations[0].itinerary);
  const category = computed(() =>
    service.value.service_sub_category.service_categories.id.toString()
  );
  const categoryName = computed(() =>
    service.value.service_sub_category.service_categories.translations[0].value.toString()
  );
  const type = computed(() => service.value.service_type.translations[0].value);
  const type_id = computed(() => service.value.service_type.id);
  const price = computed(() => service.value.service_rate[0].service_rate_plans[0].price_adult);
  // const destination = computed(() => {
  //   const state = service.value.service_destination[0].state.translations[0].value;
  //   const city = service.value.service_destination[0].city
  //     ? service.value.service_destination[0].city.translations[0].value
  //     : '';
  //   const destiny = city && state != city ? state + ', ' + city : state;
  //   return destiny;
  // });

  const origin = computed(() => {
    const state = service.value.service_origin[0].state.translations[0].value;
    const city = service.value.service_origin[0].city
      ? service.value.service_origin[0].city.translations[0].value
      : '';
    const destiny = city && state != city ? state + ', ' + city : state;
    return destiny;
  });

  const galleryImages = computed(() => {
    if (service.value.galleries && service.value.galleries.length > 0) {
      return service.value.galleries;
    }
    return [{ url: defaultImage }];
  });

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
    const categoriesId: number[] = [];
    quoteCategories.value.forEach((c) => {
      if (selectedCategory.value === c.type_class_id) {
        categoriesId.push(c.id);
      }
    });

    const ratesToAdd = [
      {
        quote_id: quote.value.id,
        type: 'service',
        categories: categoriesId,
        object_id: service.value!.id,
        service_code: service.value!.aurora_code,
        date_in: searchParameters.value!.date_from,
        date_out: searchParameters.value!.date_from,
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

    // console.log(serviceSelected.value)
    if (Object.keys(serviceSelected.value).length > 0) {
      console.log(ratesToAdd);
      await replaceService(ratesToAdd);
    } else {
      await addServices(ratesToAdd);
    }

    // console.log('Agregando al Layer..', service.value, ratesToAdd);

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

    storeSidebar.setStatus(false, 'service', 'search');
  };
</script>

<template>
  <div class="item">
    <h3>{{ name }}</h3>
    <div class="img">
      <a-carousel :arrows="galleryImages.length > 1" :dots="false" :autoplay="true">
        <template #prevArrow v-if="galleryImages.length > 1">
          <div class="custom-slick-arrow" style="left: 10px; z-index: 1">
            <left-circle-outlined />
          </div>
        </template>
        <template #nextArrow v-if="galleryImages.length > 1">
          <div class="custom-slick-arrow" style="right: 10px">
            <right-circle-outlined />
          </div>
        </template>
        <div v-for="(image, index) in galleryImages" :key="index">
          <div class="img" :style="'background-image: url(' + image.url + ')'"></div>
        </div>
      </a-carousel>
      <div class="tag orange">
        <span
          v-bind:class="{
            'bg-private': type_id == 2,
            'bg-shared': type_id == 1,
            'bg-none': type_id == 3,
          }"
          >{{ type }}</span
        >
      </div>
    </div>
    <div class="place">
      <div>
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
        {{ origin }}
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
      <p v-html="description"></p>
    </div>

    <div class="row-flex">
      <div class="quotes-actions-btn">
        <div class="content">
          <div class="text" @click="addService">
            <span v-if="Object.keys(serviceSelected).length > 0">{{
              t('quote.label.replace')
            }}</span>
            <span v-else>{{ t('quote.label.add') }}</span>
          </div>
        </div>
      </div>

      <div class="price">
        ${{ displayPrice(price) }} <span>{{ t('quote.label.per_person') }}</span>
        <span
          v-if="showCommissionBadge"
          class="badge-warning ml-2"
          style="font-size: 10px; padding: 1px 2px"
          >{{ t('global.label.with_commission') }}</span
        >
      </div>
    </div>
  </div>

  <modal-itinerario-detail
    v-if="state.modalHotelDetail.isOpen"
    :title="name"
    type="service"
    :category="category"
    :categoryName="categoryName"
    :show="state.modalHotelDetail.isOpen"
    :service="service"
    :service-date-out="searchParameters!.date_from"
    @close="toggleModalDetail"
  />
</template>

<style scoped>
  .img {
    position: relative;
    width: 100%;
  }

  /* Custom arrows for the carousel */
  :deep(.slick-arrow.custom-slick-arrow) {
    width: 25px;
    height: 25px;
    font-size: 25px;
    color: #fff;
    background-color: rgba(31, 45, 61, 0.11);
    transition: ease all 0.3s;
    opacity: 0.3;
    z-index: 1;
  }

  :deep(.slick-arrow.custom-slick-arrow:before) {
    display: none;
  }

  :deep(.slick-arrow.custom-slick-arrow:hover) {
    color: #fff;
    opacity: 0.5;
  }

  .img .tag {
    position: absolute;
    left: 5px;
    top: 5px;
    z-index: 2;
  }
</style>
