<template>
  <div class="tabs-option-config-services">
    <a-spin :spinning="loading">
      <a-tabs>
        <a-tab-pane v-for="tab in tabs" :key="tab.key" :tab="tab.tab">
          <div class="subTabs-option-config-services">
            <component class="space-margin" :is="tab.component" />
          </div>
        </a-tab-pane>
      </a-tabs>
    </a-spin>
  </div>
</template>

<script setup lang="ts">
  import { useConfigService } from '@/modules/negotiations/supplier/register/configuration-module/composables/useConfigService';

  const { tabs, loading } = useConfigService();
</script>

<style>
  .tabs-option-config-services {
    border: 1px solid #e7e7e7;
    border-radius: 4px;

    .ant-tabs {
      .ant-tabs-tab-btn {
        outline: none;
        transition: all 0ms !important;

        &:active {
          color: #6e6e6e !important;
        }
      }
    }

    .ant-tabs-ink-bar {
      display: none;
    }

    .ant-tabs-ink-bar-animated {
      display: none;
    }

    .ant-tabs-nav-operations {
      display: none;
    }

    .ant-tabs-nav-list {
      background: #e7e7e7;
    }

    .ant-tabs-nav-wrap .ant-tabs-nav-list {
      width: 100%;
      display: flex;

      .ant-tabs-tab {
        margin: 0 0 0 0;
        padding: 12px 23px;
        color: #6e6e6e;
        width: 100%;
        text-align: center;
        justify-content: center;

        &:active {
          color: #6e6e6e !important;
        }
      }

      .ant-tabs-tab-active {
        font-size: 14px;
        text-align: center;
        background-color: #ffffff;

        .ant-tabs-tab-btn {
          color: #212121;
          text-shadow: 0 0 1px #212121;
          outline: none;
          transition: all 0s !important;
        }
      }
    }

    .ant-tabs-nav {
      margin: 0;
    }

    .ant-tabs-nav::before {
      position: absolute;
      right: 0;
      left: 0;
      border-bottom: 1px solid transparent;
      content: '';
    }

    :where(.css-dev-only-do-not-override-w750bm).ant-tabs,
    .ant-tabs-tab-btn:focus:not(:focus-visible),
    :where(.css-dev-only-do-not-override-w750bm).ant-tabs,
    .ant-tabs-tab-remove:focus:not(:focus-visible),
    :where(.css-dev-only-do-not-override-w750bm).ant-tabs .ant-tabs-tab-btn:active,
    :where(.css-dev-only-do-not-override-w750bm).ant-tabs .ant-tabs-tab-remove:active {
      color: #575b5f !important;
    }

    .subTabs-option-config-services {
      all: unset;
      display: block;
      width: unset;
      background: none !important;
      color: inherit !important;
      box-sizing: border-box;
      border-bottom: 1px solid #e7e7e7;
      border-top: 1px solid #e7e7e7;

      /* .ant-tabs-content {
        padding: 20px !important;
      } */

      .custom-tab-content {
        padding: 20px !important;
      }

      .ant-tabs {
        .ant-tabs-tab-btn {
          color: #2f353a;
          text-shadow: 0 0 0.25px #2f353a;
          outline: none;
          transition: all 0ms !important;

          &:active {
            color: #2f353a;
            text-shadow: 0 0 0.25px #2f353a;
          }
        }
      }

      .ant-tabs-ink-bar,
      .ant-tabs-ink-bar-animated {
        display: none;
      }

      .ant-tabs-nav-list {
        background: #ffffff;
      }

      .ant-tabs-nav-wrap {
        border-bottom: 1px solid #e7e7e7;

        .ant-tabs-nav-list {
          width: 100%;
          display: flex;

          .ant-tabs-tab {
            padding: 0;
            color: #2f353a;
            width: 20%;
            text-align: center;
            justify-content: center;
            height: 55px;
            margin: 0 0 0 64px;

            &:active {
              color: #2f353a !important;
            }
          }

          .ant-tabs-tab-active {
            font-size: 14px;
            text-align: center;
            color: #bd0d12;
            background-color: #ffffff;
            border-bottom: 2px solid #bd0d12;

            .ant-tabs-tab-btn {
              color: #bd0d12;
              text-shadow: 0 0 1px #bd0d12;
              outline: none;
              transition: all 0s !important;
            }
          }
        }
      }

      .ant-tabs-nav {
        margin: 0;
      }

      .ant-tabs-nav::before {
        position: absolute;
        right: 0;
        left: 0;
        border-bottom: 1px solid #bd0d12;
        content: '';
      }

      .space-margin {
        /*padding: 20px !important;*/
      }
    }

    .table-negotiation-services {
      .title-table {
        font-size: 14px;
        color: #7e8285;
        text-align: justify;
      }

      .ant-table-container {
        .ant-table-cell {
          border-radius: 0 !important;

          &:before {
            display: none;
          }
        }
      }

      :where(.css-dev-only-do-not-override-w750bm).ant-table-wrapper
        .ant-table:not(.ant-table-bordered)
        .ant-table-tbody
        > tr
        > td {
        border-bottom: 1px solid transparent;
      }
    }
  }
</style>
