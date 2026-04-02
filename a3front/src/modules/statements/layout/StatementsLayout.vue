<template>
  <a-spin :tip="t('quote.label.please')" :spinning="statementStore.isLoading" size="large">
    <a-layout id="statements-layout">
      <a-layout-header class="custom-layout-header sticky">
        <header-component />
      </a-layout-header>
      <a-layout class="statements-layout-root">
        <a-layout-content>
          <div class="statements-layout bg-white h-screen">
            <div class="statements-layout-row">
              <div class="statements-layout-container mt-4">
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
  import { useStatementsStore } from '@/modules/statements/stores/index.js';
  // const route = useRoute();
  // const languagesStore = useLanguagesStore();
  const statementStore = useStatementsStore();
  const loadingBreadcrumb = ref(false);
  const { t } = useI18n({ useScope: 'global' });
  onMounted(() => {
    loadingBreadcrumb.value = true;
    setTimeout(() => {
      loadingBreadcrumb.value = false;
    }, 150);
  });
</script>
<style scoped lang="scss">
  .statements-layout {
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
    padding: 16px;
    display: flex;
    flex-direction: column;
    align-items: center;
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
