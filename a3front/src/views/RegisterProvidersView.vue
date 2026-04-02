<template>
  <div class="custom-layout-header">
    <div class="header">
      <img alt="logo-lito" class="header-logo" height="65" src="/images/logo_limatours.png" />
    </div>
  </div>
  <a-spin tip="Espere un momento" :spinning="loading" size="large">
    <a-spin class="text-carga" tip="Cargando" :spinning="loading" size="large"></a-spin>

    <a-layout id="files-layout">
      <div class="files-layout-row">
        <div class="files-edit">
          <div class="files-layout bg-white h-screen">
            <div class="files-reservations">
              <h4 class="files-reservations-title">Registro para código de confirmación</h4>

              <div>
                <ListProvidersCode
                  :code-hotel="codigohotel"
                  :nro-file="nroFileId"
                  :loading="loading"
                />
              </div>
            </div>
          </div>
        </div>
      </div>
    </a-layout>
  </a-spin>
</template>

<script setup>
  import { onMounted, ref, computed } from 'vue';
  import axios from 'axios';
  import { useI18n } from 'vue-i18n';
  import { getUrlAuroraFront } from '@/utils/auth';
  import { useFilesStore } from '@store/files';
  import { useRoute } from 'vue-router';
  import { useLanguagesStore } from '@/stores/global';
  import ListProvidersCode from '@/components/files/reservations/ListProvidersCode.vue';

  const { getLocaleMessage, locale, mergeLocaleMessage } = useI18n({
    useScope: 'global',
  });

  const languagesStore = useLanguagesStore();
  const filesStore = useFilesStore();
  const route = useRoute();

  const loading = ref(true);

  const codigohotel = computed(() => route.params.codhotel || '');
  const nroFileId = computed(() => route.params.nrofile || '');

  onMounted(async () => {
    const { nrofile, codhotel } = route.params;
    const { lang } = route.query;

    if (nrofile) {
      localStorage.setItem('lang', lang);
      const response = await filesStore.getByNumberPublic({
        nrofile,
        data: { code_hotel: codhotel },
      });
      await onLoadReport(false, codhotel, response.id);
      await onLoadProvider(response.executive_code, response.hotel_object_id, response.client_id);
    }

    locale.value = localStorage.getItem('lang');

    let data,
      iso = '';

    data = import.meta.glob('../../lang/**/files.json');
    for (const path in data) {
      data[path]().then((mod) => {
        iso = path.substring(11, 13);
        const messages = {
          files: JSON.parse(JSON.stringify(mod)),
        };

        addTranslations(iso, messages);
      });
    }

    data = import.meta.glob('../../lang/**/quotes.json');
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
  });

  const onLoadReport = async (param = false, codhotel, file_id) => {
    if (param == false) {
      await filesStore.fetchFileServiceHotelCode(file_id, codhotel);
    } else {
      loading.value = true;
      setTimeout(async () => {
        await filesStore.fetchFileServiceHotelCode(file_id, codhotel);
        loading.value = false;
      }, 5000);
    }
  };

  const onLoadProvider = async (executive_code, hotel_object_id, cliente_id) => {
    loading.value = true;
    await filesStore.fetchFileReservationProvider({
      executive_code: executive_code,
      hotel_id: hotel_object_id,
      client_id: cliente_id,
    });
    loading.value = false;
  };
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
      getUrlAuroraFront() + 'translation/' + languagesStore.currentLanguage + '/slug/quote'
    );

    const flights = await axios.get(
      getUrlAuroraFront() + 'translation/' + languagesStore.currentLanguage + '/slug/flights'
    );

    const messages = {
      files: files.data,
      quote: quotes.data,
      flights: flights.data,
    };

    addTranslations(languagesStore.currentLanguage, messages);
  };
</script>

<style lang="scss" scoped>
  .files-reservations {
    min-width: 1040px;
    &-title {
      color: #eb5757;
      font-weight: 600;
      font-size: 36px !important;
      line-height: 55px;
    }
    &-title-info {
      color: #3d3d3d;
      font-weight: 700;
      font-size: 24px !important;
      line-height: 31px;
    }
  }
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
    background: red url('../images/quotes/logo.png') 100% 100%;
    background-size: cover;
    animation: rotate 2s infinite ease-in-out;
    border-radius: 0 !important;
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

  .custom-layout-header {
    border-bottom: 1px solid #c7c3c340;
    box-shadow: 0px 4px 8px 0px #c7c3c340;
  }
</style>
