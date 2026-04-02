<template>
  <a-spin :tip="t('quote.label.please')" :spinning="claimStore.isLoading" size="large">
    <a-layout id="claims-layout">
      <a-layout-header class="custom-layout-header sticky">
        <header-component />
      </a-layout-header>
      <a-layout class="claims-layout-root">
        <a-layout-content>
          <div class="claims-layout bg-white h-screen">
            <div class="claims-layout-row">
              <div class="claims-layout-container mt-4">
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
  import { useClaimsStore } from '@/modules/claims/stores/index.js';
  // const route = useRoute();
  // const languagesStore = useLanguagesStore();
  const claimStore = useClaimsStore();
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
  /* para el ancho del Home/CLAIMS y del contenedor de la pantalla principal done esta el listado de rutas max-width*/
  .claims-layout {
    padding: 16px;
    display: flex;
    justify-content: center;
    width: 100%;
  }
  .claims-layout-container {
    width: 100%;
    max-width: 1400px;
  }
  .claims-layout-row {
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
