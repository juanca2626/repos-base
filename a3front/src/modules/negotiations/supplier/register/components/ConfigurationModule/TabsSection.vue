<template>
  <div class="tabs-section">
    <div class="tabs-option-general-collaborators-title">Colaboradores:</div>
    <div>
      <a-tabs class="tabs-option-general-collaborators" @change="handleSetTabOptionCollaborator">
        <a-tab-pane key="negotiations" tab="Negociaciones"> </a-tab-pane>
        <a-tab-pane key="treasury" tab="Tesorería"> </a-tab-pane>
        <a-tab-pane key="accounting" tab="Contabilidad"> </a-tab-pane>
      </a-tabs>
    </div>
  </div>
</template>

<script setup lang="ts">
  import { useSupplierTabView } from '@/modules/negotiations/supplier/register/composables/useSupplierTabView';

  const { handleSetTabOptionCollaborator } = useSupplierTabView();
</script>

<style>
  .tabs-section .tabs-option-general-collaborators-title {
    font-weight: 600;
    font-size: 14px;
    line-height: 20px;
    color: #bdbdbd;
    margin-bottom: 3px;
    text-align: justify;
  }

  .tabs-option-general-collaborators {
    .ant-tabs {
      .ant-tabs-tab-btn {
        outline: none;
        transition: all 0ms !important;
      }
    }

    .ant-tabs-ink-bar,
    .ant-tabs-ink-bar-animated {
      display: none;
    }

    .ant-tabs-nav-operations {
      display: none;
    }

    .ant-tabs-nav-list {
      background: #f9f9f9;
      border-radius: 20px;
    }

    .ant-tabs-nav-wrap .ant-tabs-nav-list {
      width: 426px;
      height: 32px;
      display: flex;
      justify-content: space-between;

      .ant-tabs-tab {
        margin: 0 !important;
        padding: 12px 23px !important;
        color: #6e6e6e !important;
        width: 9rem;
        text-align: center;
        justify-content: center;
        font-size: 14px;
        line-height: 20px;
      }

      .ant-tabs-tab-active {
        background-color: #ffffff;
        border: 0.063rem solid #e7e7e7;
        border-radius: 1.25rem;

        .ant-tabs-tab-btn {
          color: #1284ed;
          text-shadow: 0 0 1px #1284ed;
          outline: none;
          font-size: 14px;
          line-height: 20px;
        }
      }
    }

    .ant-tabs-nav::before {
      position: absolute;
      right: 0;
      left: 0;
      border-bottom: 1px solid transparent;
      content: '';
      font-size: 14px;
      line-height: 20px;
    }
  }
</style>
