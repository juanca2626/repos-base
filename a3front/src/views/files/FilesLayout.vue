<template>
  <a-spin
    :tip="t('files.message.waiting')"
    :spinning="filesStore.isLoading || loadingBreadcrumb"
    size="large"
  >
    <a-layout id="files-layout">
      <a-layout-header class="custom-layout-header sticky">
        <header-component />
      </a-layout-header>
      <a-layout class="files-layout-root">
        <a-layout-content>
          <div class="files-layout bg-white h-screen">
            <div class="files-layout-row">
              <div class="files-layout-container mt-4">
                <div class="justify-content-md-center">
                  <template v-if="loadingBreadcrumb">
                    <skeleton active></skeleton>
                    <skeleton active></skeleton>
                  </template>
                  <a-breadcrumb class="breadcrumb-component" v-if="!loadingBreadcrumb">
                    <template #separator>
                      <span class="separator"> / </span>
                    </template>
                  </a-breadcrumb>
                  <router-view></router-view>
                </div>
              </div>
            </div>
          </div>
        </a-layout-content>
      </a-layout>
    </a-layout>
  </a-spin>

  <users-connected v-if="flagShowUsers"></users-connected>
</template>

<script setup>
  import { onMounted, watch, ref } from 'vue';
  import ABreadcrumb from '@/components/global/ABreadcrumbRoutes.vue';
  import HeaderComponent from '@/components/global/HeaderComponent.vue';
  import { useI18n } from 'vue-i18n';
  import { useRoute, useRouter } from 'vue-router';
  import { processLoadingItineraries } from '@/utils/files';
  import { useSocketsStore, useLanguagesStore } from '@/stores/global';
  import UsersConnected from '@/components/global/UsersConnected.vue';

  import { useSocketHelper } from '@/utils/socketHelper';
  const { connectSocket, reconnectOnVisibility } = useSocketHelper();

  import {
    useFilesStore,
    useItineraryStore,
    useStatusesStore,
    useHaveInvoicesStore,
    useRevisionStagesStore,
    useServiceNotesStore,
  } from '@/stores/files';

  const socketsStore = useSocketsStore();
  const route = useRoute();
  const filesStore = useFilesStore();
  const itineraryStore = useItineraryStore();

  const languagesStore = useLanguagesStore();
  const statusesStore = useStatusesStore();
  const haveInvoicesStore = useHaveInvoicesStore();
  const revisionStagesStore = useRevisionStagesStore();
  const serviceNotesStore = useServiceNotesStore();

  const loadingBreadcrumb = ref(false);
  const { t } = useI18n({
    useScope: 'global',
  });

  const flagShowUsers = ref(false);

  const router = useRouter();

  onMounted(async () => {
    const file_id = localStorage.getItem('a3_file_id');

    await Promise.all([
      languagesStore.fetchAll(),
      statusesStore.fetchAll(),
      haveInvoicesStore.fetchAll(),
      revisionStagesStore.fetchAll(),
      serviceNotesStore.getClassification(),
    ]);

    filesStore.inited();

    if (file_id > 0) {
      router.push(`/files/${file_id}/edit`);
      localStorage.removeItem('a3_file_id');
    }

    if (filesStore.getFile?.id) {
      connectSocket();
    }

    // console.log("nombre de ruta", route.name);
    if (route.name == 'files-balance') {
      filesStore.finished();
    }

    if (route.name !== 'files-edit') {
      setTimeout(() => {
        flagShowUsers.value = true;
      }, 1000);
    }
  });

  watch(
    () => filesStore.getFile?.id,
    async (newId, oldId) => {
      if (newId === oldId) return;
      socketsStore.clearNotifications();
      connectSocket();
    }
  );

  watch(route, async () => {
    loadingBreadcrumb.value = true;

    setTimeout(() => {
      loadingBreadcrumb.value = false;
      filesStore.finished();
      filesStore.changeLoaded(true);
      flagShowUsers.value = route.name === 'files-edit' ? false : true;
    }, 1000);

    if (filesStore.getFile?.id > 0) {
      itineraryStore.initedAsync();
      await processLoadingItineraries();

      const codes = filesStore.getFileItineraries
        .filter((itinerary) => itinerary.entity === 'service')
        .map((itinerary) => {
          return itinerary?.services.map((service) => service.code_ifx);
        });

      const unique_codes = [...new Set(codes.flat())];

      await Promise.all([
        filesStore.searchServicesGroups({ codes: unique_codes, loading: false }),
        filesStore.searchServicesFrequences({ codes: unique_codes, loading: false }),
        // filesStore.searchServiceSchedules({ codes: unique_codes, loading: false }),
      ]);

      itineraryStore.finished();
    }
  });

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
    background: red url('../../images/quotes/logo.png') 100% 100%;
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
