<template>
  <div class="supplier-form-treasury">
    <div class="tabs-option-config-treasury">
      <a-tabs>
        <a-tab-pane key="banking-information" tab="Información bancaria">
          <div class="subTabs-option-config-treasury">
            <SupplierFormInformationBankComponent />
          </div>
        </a-tab-pane>
        <a-tab-pane key="accounting-accounts" tab="Cuentas contable">
          <div class="subTabs-option-config-treasury">
            <SupplierFormAccountingAccountsComponent />
          </div>
        </a-tab-pane>
      </a-tabs>
    </div>
  </div>
</template>

<script setup lang="ts">
  import SupplierFormInformationBankComponent from '@/modules/negotiations/supplier/register/components/ConfigurationTreasury/SupplierFormInformationBankComponent.vue';
  import SupplierFormAccountingAccountsComponent from '@/modules/negotiations/supplier/register/components/ConfigurationTreasury/SupplierFormAccountingAccountsComponent.vue';
</script>

<style lang="scss">
  .supplier-form-treasury {
    .tabs-option-config-treasury {
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

      .subTabs-option-config-treasury {
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
      }
    }
  }
</style>
