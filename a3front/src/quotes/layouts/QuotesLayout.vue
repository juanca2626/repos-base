<template>
  <a-spin
    class="global-quote-loader"
    :tip="t('quote.label.please')"
    :spinning="isLoading"
    size="large"
  >
    <a-spin
      class="text-carga"
      :tip="t('quote.label.loading')"
      :spinning="isLoading"
      size="large"
    ></a-spin>
    <a-layout id="quotes-layout">
      <a-layout-header class="custom-layout-header sticky">
        <header-component />
      </a-layout-header>
      <a-layout-content
        :style="{ minHeight: 'calc(100vh - 100px)' }"
        class="quotes-layout-root"
        :class="[
          {
            hotel_details: [
              'quotes-reservations-confirmation',
              'quotes-reports',
              ,
              'quotes-hotel-details',
            ].includes(route.name as string),
          },
          {
            hotel_details: [
              'details-price',
              'reports',
              'hotel-details',
              'reservations',
              'reservations-confirmation',
            ].includes(page as string),
          },
        ]"
      >
        <!-- 'hotel_details' : page === 'details-price' -->
        <router-view></router-view>
      </a-layout-content>
    </a-layout>
  </a-spin>
  <div
    v-if="isModalOpened"
    id="quotes-layout-overlay"
    :style="{ display: isModalOpened }"
    @click="hideModals"
  ></div>

  <!-- Users Connected Floating Component (Left Side) -->
  <users-connected position="left" :title="noUsersObservingTitle" />
</template>
<script lang="ts" setup>
  import { computed, onMounted, onUnmounted, watch } from 'vue';
  import HeaderComponent from '@/components/global/HeaderComponent.vue';
  import UsersConnected from '@/components/global/UsersConnected.vue';
  import { useRoute } from 'vue-router';
  import { useQuotesStore } from '@store/quotes-store';
  import useLoader from '@/quotes/composables/useLoader';
  import { useQuote } from '@/quotes/composables/useQuote';
  import { useQuoteSocket } from '@/quotes/composables/useQuoteSocket';
  import { useI18n } from 'vue-i18n';
  import axios from 'axios';
  import { storeToRefs } from 'pinia';
  import { useLanguagesStore } from '@/stores/global';
  import { getUrlAuroraFront /*, getUserType, getUserClientId*/ } from '@/utils/auth';

  const languageStore = useLanguagesStore();
  const { getLocaleMessage, mergeLocaleMessage, t } = useI18n({
    useScope: 'global',
  });

  const noUsersObservingTitle = computed(() => {
    return t('quote.notification.no_users_observing');
  });

  const { page, quote } = useQuote();
  const route = useRoute();
  const store = useQuotesStore();
  const { isLoading } = useLoader();
  const { connectSocketQuoteId, disconnectSocket } = useQuoteSocket();

  const isModalOpened = computed(() => store.isModalOpened);
  const hideModals = () => {
    store.closeModals();
  };

  onMounted(async () => {
    /*
    const userType = getUserType();
    if (userType != '4') {
      const clientId = getUserClientId();
    }

    if (getUserType() != '4') {
      document.location.href = getUrlAuroraFront() + 'packages/cotizacion';
    }
    */

    const data = import.meta.glob('../../lang/**/quotes.json');
    let iso = '';
    for (const path in data) {
      data[path]().then((mod) => {
        iso = path.substring(11, 13);
        const messages = {
          quote: JSON.parse(JSON.stringify(mod)),
        };

        addTranslations(iso, messages);
      });
    }

    await getLanguagesFiles();
    // await getLanguages();
  });

  const addTranslations = (iso: string, messages: object) => {
    let currentMessages = getLocaleMessage(iso);
    currentMessages = { ...currentMessages, ...messages };
    mergeLocaleMessage(iso, currentMessages);
  };

  const getLanguagesFiles = async () => {
    const quote = await axios.get(
      getUrlAuroraFront() + 'translation/' + languageStore.currentLanguage + '/slug/quote'
    );

    const flights = await axios.get(
      getUrlAuroraFront() + 'translation/' + languageStore.currentLanguage + '/slug/flights'
    );

    const messages = {
      quote: quote.data,
      flights: flights.data,
    };

    addTranslations(languageStore.currentLanguage, messages);
  };

  const { currentLanguage } = storeToRefs(languageStore);

  watch(currentLanguage, async () => {
    await getLanguagesFiles();
  });

  // Connect to socket when quote is loaded
  watch(
    () => quote.value?.id,
    (newQuoteId) => {
      if (newQuoteId) {
        connectSocketQuoteId(newQuoteId);
      } else {
        disconnectSocket();
      }
    },
    { immediate: true }
  );

  // Disconnect socket when leaving quotes
  onUnmounted(() => {
    disconnectSocket();
  });
</script>

<style lang="scss" scoped>
  @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Montserrat:wght@100;200;300;400;500;600;700&display=swap');

  :deep(.ant-spin) {
    background: none !important;
    border: 0 !important;
    min-height: 100%;
    max-height: 100%;
    position: fixed !important;
  }

  :deep(.ant-spin-text) {
    padding-top: 60px !important;
    color: #bababa;
    text-shadow: none;
    font-size: 16px;
  }

  :deep(.ant-spin-lg .ant-spin-dot i) {
    display: none;

    &:first-child {
      display: block;
      opacity: 1;
      width: auto;
      height: auto;
    }
  }

  :deep(.ant-spin-dot-spin) {
    position: absolute;
    left: 0;
    right: 0;
    top: 0;
    bottom: 0;
    margin: auto auto;
    width: 60px;
    height: 60px;
    animation: rotate 2s infinite ease-in-out;
    overflow: hidden;
    background-color: #c3141a;
    margin: -35px 0 0 -30px !important;
  }

  :deep(.ant-spin-blur) {
    opacity: 0.999;
  }

  :deep(.ant-spin-dot-item) {
    position: absolute;
    left: 0;
    right: 0;
    top: 0;
    bottom: 0;
    margin: auto auto;
    background: red url('../../images/quotes/logo.png') 100% 100%;
    background-size: cover;
    animation: rotate 2s infinite ease-in-out;
    border-radius: 0 !important;
  }

  :deep(.global-quote-loader > .ant-spin-container.ant-spin-blur:after) {
    background: rgba(0, 0, 0, 0.95) !important;
    opacity: 1 !important;
    z-index: 25;
  }

  :deep(.ant-spin-text) {
    text-shadow: none !important;
  }

  :deep(.text-carga) {
    z-index: 28 !important;
    display: none !important;

    .ant-spin-text {
      padding-top: 90px !important;
      animation: opacity 1.5s infinite ease-out;
      font-size: 80%;
      font-weight: 400;
      text-shadow: 0 1px 2px #fff !important;
    }
  }

  :deep(.ant-spin-container.ant-spin-blur .text-carga) {
    display: block !important;
  }

  :deep(.text-carga .ant-spin-dot-spin) {
    display: none !important;
  }

  #quotes-layout-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    z-index: -1;
  }

  #quotes-layout {
    font-family: 'Montserrat', sans-serif;
    background-color: #ffffff;
  }

  .quotes-layout-root {
    width: 80vw;
    margin: 0 auto;
    background-color: #ffffff;
    overflow: visible !important;
  }

  .quotes-layout-root.hotel_details {
    width: 100%;
  }

  .ant-layout-content {
    max-height: none;
    overflow: hidden;
  }

  .ant-layout-header {
    line-height: 60px;
  }

  @media only screen and (max-width: 1800px) {
    .quotes-layout-root {
      width: 85vw;
    }
  }

  @media only screen and (max-width: 1600px) {
    .quotes-layout-root {
      width: 90vw;
    }
  }

  @media only screen and (max-width: 1450px) {
    .quotes-layout-root {
      width: 95vw;
    }
  }
</style>
