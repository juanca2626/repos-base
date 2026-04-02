<template>
  <a-spin :tip="t('files.message.waiting')" :spinning="loading" size="large">
    <a-layout id="files-layout">
      <div class="files-layout-row">
        <div class="files-edit">
          <div class="files-layout bg-white h-screen">
            <paxs-info-page :flag_external_link="true" v-if="!loading" />
          </div>
        </div>
      </div>
    </a-layout>
  </a-spin>

  <users-connected></users-connected>
  <div class="notifications-floating" v-if="socketsStore.isConnected">
    <header-notifications />
  </div>
</template>

<script setup>
  import { onMounted, ref } from 'vue';
  import dayjs from 'dayjs';
  import axios from 'axios';
  import { getUrlAuroraFront } from '@/utils/auth';
  import PaxsInfoPage from '@/components/files/paxs/FilesPaxsInfo.vue';
  import { useFilesStore } from '@store/files';
  import { useRoute, useRouter } from 'vue-router';
  import { useLanguagesStore } from '@/stores/global';
  import { useSocketsStore } from '@/stores/global';
  import UsersConnected from '@/components/global/UsersConnected.vue';
  import HeaderNotifications from '@/components/global/HeaderNotifications.vue';

  import { useSocketHelper } from '@/utils/socketHelper';
  const { connectSocketFileId, reconnectOnVisibility } = useSocketHelper();

  import { useI18n } from 'vue-i18n';
  const { getLocaleMessage, locale, mergeLocaleMessage, t } = useI18n({
    useScope: 'global',
  });

  const socketsStore = useSocketsStore();
  const languagesStore = useLanguagesStore();
  const filesStore = useFilesStore();
  const route = useRoute();
  const router = useRouter();

  const loading = ref(true);

  const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

  const validateFile = async () => {
    if (filesStore.getFile?.id > 0) {
      if (filesStore.getFile.dateIn <= dayjs().format('YYYY-MM-DD')) {
        router.push({ name: 'link_completed' });
      }
    } else {
      router.push({ name: 'error_404' });
    }

    await sleep(500);
  };

  onMounted(async () => {
    let data, iso;
    const { nrofile } = route.params;
    const { lang = 'en' } = route.query;

    localStorage.setItem('lang', lang);

    await filesStore.getByNumber({ nrofile });

    if (filesStore.getFile?.id) {
      await validateFile();
      await filesStore.getPassengersById({ fileId: filesStore.getFile.id });
    } else {
    }

    await validateFile();

    languagesStore.setCurrentLanguage(lang);
    locale.value = languagesStore.getLanguage;

    data = import.meta.glob('../lang/**/files.json');
    iso = '';
    for (const path in data) {
      data[path]().then((mod) => {
        iso = path.substring(8, 10);
        const messages = {
          files: JSON.parse(JSON.stringify(mod)),
        };
        addTranslations(iso, messages);
      });
    }

    data = import.meta.glob('../lang/**/global.json');
    iso = '';
    for (const path in data) {
      data[path]().then((mod) => {
        iso = path.substring(8, 10);
        const messages = {
          global: JSON.parse(JSON.stringify(mod)),
        };
        addTranslations(iso, messages);
      });
    }

    data = import.meta.glob('../lang/**/quotes.json');
    iso = '';
    for (const path in data) {
      data[path]().then((mod) => {
        iso = path.substring(8, 10);
        const messages = {
          quote: JSON.parse(JSON.stringify(mod)),
        };

        addTranslations(iso, messages);
      });
    }

    if (filesStore.getFile?.id) {
      connectSocketFileId(filesStore.getFile.id);
    }

    loading.value = false;
    await getLanguagesFiles();
    filesStore.finished();
  });

  const addTranslations = (iso, messages) => {
    let currentMessages = getLocaleMessage(iso);
    currentMessages = { ...currentMessages, ...messages };
    mergeLocaleMessage(iso, currentMessages);
  };

  const getLanguagesFiles = async () => {
    const files = await axios.get(
      getUrlAuroraFront() + 'translation/' + languagesStore.getLanguage + '/slug/files'
    );

    const quotes = await axios.get(
      getUrlAuroraFront() + 'translation/' + languagesStore.getLanguage + '/slug/quote'
    );

    const flights = await axios.get(
      getUrlAuroraFront() + 'translation/' + languagesStore.getLanguage + '/slug/flights'
    );

    const messages = {
      files: files.data,
      quote: quotes.data,
      flights: flights.data,
    };

    addTranslations(languagesStore.currentLanguage, messages);
  };

  document.addEventListener('visibilitychange', reconnectOnVisibility);
  window.addEventListener('beforeunload', () => {
    socketsStore.disconnect();
  });
</script>

<style lang="scss" scoped>
  :deep(.ant-spin) {
    background: none !important;
    border: 0 !important;
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
      display: block !important;
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
    background: red url('../images/quotes/logo.png') 100% 100%;
    background-size: cover;
    animation: rotate 2s infinite ease-in-out;
    border-radius: 0 !important;
  }

  :deep(.spin-white .ant-spin-container.ant-spin-blur:after) {
    background: rgba(255, 255, 255, 0.95) !important;
    opacity: 0.8 !important;
    z-index: 25;
  }

  :deep(.spin-white .ant-spin-spinning) {
    position: absolute !important;
  }

  :deep(.ant-spin-container.ant-spin-blur:after) {
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
</style>
