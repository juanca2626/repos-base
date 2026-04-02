<template>
  <a-spin :tip="t('global.loading')" :spinning="isLoading || loadingBreadcrumb" size="large">
    <a-layout id="files-layout">
      <a-layout-header class="custom-layout-header sticky">
        <HeaderComponent />
      </a-layout-header>

      <a-layout class="files-layout-root">
        <a-layout-content>
          <div class="files-layout bg-white h-screen">
            <div class="files-layout-row">
              <div class="files-layout-container mt-4">
                <div class="justify-content-md-center">
                  <template v-if="loadingBreadcrumb">
                    <skeleton active />
                    <skeleton active />
                  </template>
                  <a-breadcrumb class="breadcrumb-component" v-if="!loadingBreadcrumb">
                    <template #separator>
                      <span class="separator"> / </span>
                    </template>
                  </a-breadcrumb>
                  <div v-else>
                    <slot name="breadcrumb" />
                  </div>
                  <slot />
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
  import { ref } from 'vue';
  import { useI18n } from 'vue-i18n';
  import ABreadcrumb from '@/components/global/ABreadcrumbRoutes.vue';
  import HeaderComponent from '@/components/global/HeaderComponent.vue';

  defineProps({
    isLoading: Boolean,
  });

  const loadingBreadcrumb = ref(false);
  const { t } = useI18n();
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
