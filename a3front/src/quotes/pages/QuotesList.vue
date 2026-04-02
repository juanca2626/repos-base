<script lang="ts" setup>
  import { computed, onMounted, ref, watch } from 'vue';
  import ErrorView from '@/views/ErrorView.vue';

  import InfoComponent from '@/quotes/components/info/InfoComponent.vue';
  import AddServiceComponent from '@/quotes/components/AddServiceComponent.vue';
  import ActionsComponent from '@/quotes/components/ActionsComponent.vue';
  import ActionsNewComponent from '@/quotes/components/ActionsNewComponent.vue';
  import InfoButtonsComponent from '@/quotes/components/InfoButtonsComponent.vue';
  import PriceDetailComponent from '@/quotes/components/PriceDetailComponent.vue';
  import DetailComponent from '@/quotes/components/DetailComponent.vue';
  import SideBarComponent from '@/quotes/components/SidebarComponent.vue';
  import SidebarComponentEdit from '@/quotes/components/SidebarComponentEdit.vue';
  import CalendarComponent from '@/quotes/components/CalendarComponent.vue';
  import QuotesDetail from '@/quotes/pages/QuotesDetail.vue';
  import QuotesReservations from '@/quotes/pages/QuotesReservations.vue';
  import QuotesHotelDetails from '@/quotes/pages/QuotesHotelDetails.vue';
  import QuotesReports from '@/quotes/pages/QuotesReports.vue';
  import QuotesReservationsConfirmation from '@/quotes/pages/QuotesReservationsConfirmation.vue';
  import IconArrowView from '@/quotes/components/icons/IconArrowView.vue';

  import { useQuote } from '@/quotes/composables/useQuote';
  import { useQuoteHotelCategories } from '@/quotes/composables/useQuoteHotelCategories';
  import useQuoteDestinations from '@/quotes/composables/useQuoteDestinations';
  import { useSiderBarStore } from '@/quotes/store/sidebar';
  import { useQuoteServices } from '@/quotes/composables/useQuoteServices';
  import useQuoteLanguages from '@/quotes/composables/useQuoteLanguages';
  import useQuoteDocTypes from '@/quotes/composables/useQuoteDocTypes';
  import useCountries from '@/quotes/composables/useCountries';

  import { useI18n } from 'vue-i18n';
  import { useLanguagesStore } from '@/stores/global';
  // import { useLanguagesStore } from "@/stores/global";
  import { storeToRefs } from 'pinia';
  import { getUserType, getUserClientId } from '@/utils/auth';

  const { t } = useI18n({
    useScope: 'global',
  });

  const storeSidebar = useSiderBarStore();
  const storeLanguage = useLanguagesStore();

  const loadingResources = ref<boolean>(true);

  // Check if user is type 4 (can see inline AddServiceComponent)
  const isUserType4 = computed(() => getUserType() === '4');

  const { view, page, quote, getQuote, processing } = useQuote();
  const { getQuoteHotelCategories } = useQuoteHotelCategories();
  const {
    getServicesCategories,
    getServicesExperiences,
    getServicesDurations,
    getServiceTypeMeals,
    getServicesDestinations,
  } = useQuoteServices();
  const { getDestinations } = useQuoteDestinations();
  const { getLanguages } = useQuoteLanguages();
  const { getDoctypes } = useQuoteDocTypes();
  const { getCountries } = useCountries();

  const getComponents = async () => {
    if (getUserClientId()) {
      loadingResources.value = true;
      const resources = [];

      resources.push(getQuoteHotelCategories());
      resources.push(getServicesCategories());
      resources.push(getServicesExperiences());
      resources.push(getServicesDurations());
      resources.push(getServiceTypeMeals());
      resources.push(getServicesDestinations());
      resources.push(getDestinations());
      resources.push(getLanguages());
      resources.push(getDoctypes());
      resources.push(getQuote());
      resources.push(getCountries());

      await Promise.all(resources).then(() => (loadingResources.value = false));
    }
  };

  onMounted(() => {
    handleUpdateLanguage();
  });

  const { currentLanguage } = storeToRefs(storeLanguage);
  watch(currentLanguage, () => {
    handleUpdateLanguage();
  });

  const handleUpdateLanguage = () => {
    getComponents();
  };

  window.addEventListener('scroll', () => {
    const arrowViewTopID = document.getElementById('arrowViewTop');

    // Usamos window.scrollY que es más moderno y directo que getBoundingClientRect().top
    const currentScroll = Math.abs(window.scrollY);

    if (arrowViewTopID) {
      if (currentScroll > 500) {
        arrowViewTopID.classList.remove('hide');
      } else {
        arrowViewTopID.classList.add('hide');
      }
    }
  });

  const scrollTop = () => {
    window.scroll({
      top: 500,
      left: 0,
      behavior: 'smooth',
    });
  };

  const title = computed(() =>
    view.value === 'table' ? t('quote.label.program_day') : t('quote.label.calendar')
  );
</script>

<template>
  <template v-if="quote?.id">
    <template v-if="quote.file.file_code && page != 'reports'">
      <div class="capa" :class="{ isFile: quote.file.file_code }"></div>
    </template>

    <template v-if="page === 'details-price'">
      <quotes-detail />
    </template>

    <template v-if="page === 'reservations'">
      <quotes-reservations />
    </template>

    <template v-if="page === 'reservations-confirmation'">
      <quotes-reservations-confirmation />
    </template>

    <template v-if="page === 'hotel-details'">
      <quotes-hotel-details />
    </template>

    <template v-if="page === 'reports'">
      <quotes-reports />
    </template>

    <template v-if="page === 'details'">
      <ErrorView
        v-if="!getUserClientId()"
        :status="500"
        :title="t('quote.error.select_client_title')"
        :subtitle="t('quote.error.select_client_subtitle')"
      ></ErrorView>
      <template v-else>
        <info-component />
        <add-service-component v-if="view === 'table' && isUserType4" />
        <actions-component :viewBtn="view" :processing="processing" />
        <div id="viewBtn">
          <info-buttons-component />
        </div>
        <price-detail-component :title="title" :viewBtn="view" />
        <div class="relative" v-if="view === 'table'">
          <div @click="scrollTop" id="arrowViewTop" class="arrowViewTop hide">
            <a-tooltip placement="top">
              <template #title>
                <span> {{ t('quote.label.up') }}</span>
              </template>
              <icon-arrow-view></icon-arrow-view>
            </a-tooltip>
          </div>

          <div :class="{ openSideBar: storeSidebar.status }" class="body-container">
            <div class="list">
              <detail-component />
            </div>

            <div v-if="storeSidebar.status" class="sidebar-container">
              <side-bar-component
                v-if="storeSidebar.modePage == 'search'"
                id="sidebarRef"
                :page="storeSidebar.searchPage"
                rel="headRef"
              />

              <sidebar-component-edit
                v-if="storeSidebar.modePage == 'edit'"
                id="sidebarRef"
                :page="storeSidebar.searchPage"
                rel="headRef"
              />
            </div>
          </div>

          <price-detail-component :hideTitle="true" :title="title" :viewBtn="view" />
        </div>
        <div v-if="view === 'calendar'">
          <calendar-component />
        </div>
      </template>
    </template>
    <template v-if="quote.file.file_code && page != 'reports'">
      <div class="capa"></div>
    </template>
  </template>

  <template v-else>
    <template v-if="page === 'reports'">
      <quotes-reports />
    </template>
    <template v-else>
      <ErrorView
        v-if="!getUserClientId()"
        :status="500"
        :title="t('quote.error.select_client_title')"
        :subtitle="t('quote.error.select_client_subtitle')"
      ></ErrorView>
      <template v-else>
        <div style="margin-top: 50px">
          <info-component />
          <actions-new-component :viewBtn="view" />
        </div>
      </template>
    </template>
  </template>
</template>

<style lang="scss" scoped>
  .relative {
    position: relative;
  }

  .arrowViewTop {
    position: fixed;
    right: 30px;
    cursor: pointer;
    bottom: 15px;
    z-index: 2;
    line-height: 54px;
    text-align: center;
    width: 59px;
    height: 59px;
    border-radius: 40px;
    border: 2px solid #e9e9e9;
    background: #fff;
    outline: none;

    svg {
      display: inline-block;
      vertical-align: middle;
      outline: none;
    }

    &.hide {
      display: none;
    }

    &:hover {
      border: 2px solid #ffe1e1;
      background: #ffe1e1;
    }

    &:focus,
    &:active {
      border: 2px solid #ff9494;
      background: #ffe1e1;
    }
  }

  @media only screen and (max-width: 1200px) {
    .arrowViewTop {
      display: none !important;
    }
  }

  .capa {
    position: absolute;
    left: 0;
    top: 70px;
    right: 0;
    bottom: 0;
    z-index: 1;
    background: rgba(0, 0, 0, 0.1);
  }

  .body-container {
    display: flex;
    gap: 35px;
    position: relative;

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
      width: 27.4%;
      position: relative;
      margin-bottom: 48px;
    }
  }
</style>
