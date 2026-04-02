<template>
  <a-spin :tip="t('quote.label.please')" :spinning="routeStore.isLoading" size="large">
    <a-layout id="routes-layout">
      <a-layout-header class="custom-layout-header sticky">
        <header-component />
      </a-layout-header>
      <a-layout class="routes-layout-root">
        <a-layout-content>
          <div class="routes-layout bg-white h-screen">
            <div class="routes-layout-row">
              <div class="routes-layout-container mt-4">
                <div class="justify-content-md-center">
                  <template v-if="loadingBreadcrumb">
                    <skeleton active></skeleton>
                  </template>
                  <a-breadcrumb class="breadcrumb-component" v-if="!loadingBreadcrumb">
                    <template #separator>
                      <span class="separator"> / </span>
                    </template>
                  </a-breadcrumb>
                  <router-view />
                </div>
              </div>
            </div>
          </div>
        </a-layout-content>
      </a-layout>
    </a-layout>
  </a-spin>
</template>
<script setup>
  import { onMounted, ref } from 'vue';
  import ABreadcrumb from '@/components/global/ABreadcrumbRoutes.vue';
  import HeaderComponent from '@/components/global/HeaderComponent.vue';
  import { useI18n } from 'vue-i18n';
  // import { useRoute } from 'vue-router';
  // import { useLanguagesStore } from '@/stores/global/index.js';
  import { useRoutesStore } from '@/modules/routes/stores/index.js';
  // const route = useRoute();
  // const languagesStore = useLanguagesStore();
  const routeStore = useRoutesStore();
  const loadingBreadcrumb = ref(false);
  const { t } = useI18n({ useScope: 'global' });
  onMounted(() => {
    loadingBreadcrumb.value = true;
    setTimeout(() => {
      loadingBreadcrumb.value = false;
    }, 150);
  });
</script>
<style lang="scss">
  /* para el ancho del Home/ROUTES y del contenedor de la pantalla principal done esta el listado de reclamos max-width*/
  .routes-layout {
    padding: 16px;
    display: flex;
    justify-content: center;
    width: 100%;
  }
  .routes-layout-container {
    width: 100%;
    max-width: 1400px;
  }
  .routes-layout-row {
    display: flex;
    justify-content: center;
    width: 100%;
  }
  h1 {
    font-size: 24px;
    font-weight: bold;
  }
  .breadcrumb-component {
    background: #ffffff;
    border: 0.9px solid #e9e9e9;
    border-radius: 6px;
    padding: 13px 37px;
    gap: 10px;
    margin-bottom: 0.9375rem; // 15px
    font-size: 1em !important;
  }
</style>
