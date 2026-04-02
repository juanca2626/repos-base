<template>
  <div class="polices-form-component">
    <spin-global-component :spinning="isLoading">
      <div>
        <div class="backend-form cursor-pointer page-header" @click="backToPolicies">
          <font-awesome-icon :icon="['fas', 'chevron-left']" class="custom-chevron-icon" />
          <span class="page-header-title"> Volver a Políticas </span>
        </div>
      </div>
      <div class="container-title-add-policy mt-3">
        <div>
          <span class="title-add-policy">
            {{ formTitle }}
          </span>
        </div>
        <div>
          <template v-if="showCloneButton">
            <a-button
              size="large"
              type="default"
              class="button-cancel-white custom-btn-cancel"
              @click="handleClone"
            >
              <font-awesome-icon :icon="['far', 'clone']" />
              <span> Copiar Política </span>
            </a-button>
          </template>
        </div>
      </div>

      <div v-show="showRegisteredDetails">
        <PolicyInformationBasicSummaryComponent />
        <PolicyRulesComponent />
      </div>
      <div v-show="showInformationBasic">
        <PoliciesInformationBasicComponent />
      </div>
    </spin-global-component>

    <policy-clone-search-component v-model:showModal="showPolicyCloneSearch" />
  </div>
</template>

<script setup lang="ts">
  import { onUnmounted, ref } from 'vue';
  import PoliciesInformationBasicComponent from '@/modules/negotiations/supplier-new/components/form/negotiations/form/supplier-registration/policies/policies-information-basic-component.vue';
  import PolicyInformationBasicSummaryComponent from '@/modules/negotiations/supplier-new/components/form/negotiations/form/supplier-registration/policies/policy-information-basic-summary-component.vue';
  import { usePolicyManagerComposable } from '@/modules/negotiations/supplier-new/composables/form/negotiations/supplier-registration/policies/policy-manager.composable';
  import PolicyRulesComponent from '@/modules/negotiations/supplier-new/components/form/negotiations/form/supplier-registration/policies/policy-rules-component.vue';
  import SpinGlobalComponent from '@/modules/negotiations/supplier-new/components/global/spin-global-component.vue';
  import { usePolicyStoreFacade } from '@/modules/negotiations/supplier-new/composables/form/negotiations/supplier-registration/policies/policy-store-facade.composable';
  import PolicyCloneSearchComponent from '@/modules/negotiations/supplier-new/components/form/negotiations/form/supplier-registration/policies/policy-clone-search-component.vue';

  const showPolicyCloneSearch = ref<boolean>(false);

  const handleClone = () => {
    showPolicyCloneSearch.value = true;
  };

  const {
    showInformationBasic,
    showRegisteredDetails,
    formTitle,
    backToPolicies,
    exitPolicyManager,
    showCloneButton,
  } = usePolicyManagerComposable();

  const { isLoading } = usePolicyStoreFacade();

  onUnmounted(() => {
    exitPolicyManager();
  });
</script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';
  @import '@/scss/components/negotiations/_supplierForm.scss';

  .custom-btn-cancel {
    min-width: 189px;
    font-weight: 600;

    span {
      margin-left: 8px;
    }
  }

  .container-title-add-policy {
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  .title-add-policy {
    font-weight: 600;
    font-size: 24px;
    color: $color-black;
  }

  .page-header {
    display: flex;
    align-items: center;
    gap: 8px;

    .custom-chevron-icon {
      font-size: 22px;
    }

    .page-header-title {
      font-size: 16px;
      font-weight: 500;
      color: $color-black;
    }
  }
</style>
<style lang="scss">
  .polices-form-component {
    margin: 20px;
    border: 1px solid #e4e5e6;
    border-radius: 8px;
    padding: 20px !important;

    .title-form {
      font-weight: 600;
      font-size: 16px;
      line-height: 23px;
      color: #2f353a;
      margin-bottom: 1rem;
      gap: 0.75rem;
    }

    .section-title {
      font-weight: 600;
      font-size: 16px;
      line-height: 23px;
      color: #2f353a;
      margin-bottom: 0.5rem;
    }

    .form-bordered {
      border: 1px solid #babcbd;
      padding: 20px;
      border-radius: 5px;
    }

    .form-row {
      display: flex;
      gap: 8%;
      align-items: center;

      .w-full {
        width: 100% !important;
      }
    }

    .mb-1 {
      margin-bottom: 1rem !important;
    }

    .mt-1 {
      margin-top: 1rem !important;
    }

    .w-full {
      width: 100% !important;
    }

    .form-group {
      display: flex;
      gap: 0.5rem;
      align-items: center;
    }

    .input-small {
      width: 6rem;
    }

    .input-medium {
      width: 7rem;
    }

    .select-medium {
      width: 10rem;
    }

    .new-rule {
      margin-top: 1rem;
      font-weight: 600;
      font-size: 16px;
      line-height: 24px;
      color: #1284ed;
      cursor: pointer;
    }

    .ant-form-item-control {
      width: 422px;
    }

    .container {
      margin-top: 20px;
    }

    .ant-form-item {
      margin-bottom: 10px !important;
    }

    .ant-form-item-required::before {
      display: none !important;
    }

    .form-label {
      font-weight: 700;
      font-size: 14px;
      line-height: 20px;
      color: #2f353a;
    }

    .ant-row {
      display: block;
    }

    .ant-form-item-required::after {
      display: inline-block;
      margin-inline-end: 4px;
      color: #ff4d4f;
      font-size: 14px;
      line-height: 1;
      content: '*' !important;
    }

    .tooltip-float {
      position: absolute;
      bottom: 37px;
      left: 161px;

      svg {
        cursor: pointer;
      }
    }

    .list-items-form {
      .container-item {
        display: flex;
        gap: 0.5rem;
      }

      .title-item {
        font-weight: 600;
        font-size: 14px;
        line-height: 24px;
        color: #7e8285;
      }

      .value-item {
        font-weight: 400;
        font-size: 14px;
        line-height: 24px;
        color: #7e8285;
      }
    }

    .item-form-code {
      display: flex;
      gap: 0.5rem;
      align-items: center;

      svg {
        height: 20px;
        color: #575b5f;
        cursor: pointer;
      }
    }

    .options-button {
      display: flex;
      gap: 1rem;

      .ant-btn-default {
        width: 118px;
        height: 48px;
        gap: 8px;
        border-radius: 5px;
        border-width: 1px;
        color: #2f353a;
        background: #ffffff;
        border-color: #2f353a !important;

        &:hover,
        &:active {
          color: #2f353a !important;
          background: #ffffff !important;
          border-color: #2f353a !important;
        }
      }

      .ant-btn-primary {
        width: 159px;
        height: 48px;
        gap: 8px;
        border-radius: 5px;
        border-width: 1px;
        color: #ffffff;
        background: #2f353a;
        border-color: #2f353a !important;

        &:hover,
        &:active {
          color: #ffffff;
          background: #2f353a;
          border-color: #2f353a !important;
        }
      }

      .ant-btn-primary:disabled {
        color: #ffffff !important;
        background: #acaeb0 !important;
        border-color: #acaeb0 !important;

        &:hover,
        &:active {
          color: #ffffff !important;
          background: #acaeb0 !important;
          border-color: #acaeb0 !important;
        }
      }
    }

    .container-maps {
      margin: 20px 0 20px 0;

      .title-maps {
        font-weight: 500;
        font-size: 14px;
        line-height: 20px;
        color: #2f353a;
      }
    }

    .ant-collapse-header {
      background: white;
      border-radius: 10px !important;
    }

    .form-label {
      color: #2f353a !important;
      font-weight: 400 !important;
    }

    .ant-checkbox-wrapper {
      span {
        color: #2f353a !important;
        font-weight: 400 !important;
      }
    }

    .collapse-panel-header {
      font-weight: 600;
      font-size: 16px;
      line-height: 23px;
      color: #2f353a;
      margin-bottom: 1rem;
    }

    .form-divider {
      margin-top: 1rem;
      margin-bottom: 1rem;
      border: 1px solid #babcbd !important;
      opacity: 0.4;
    }

    .checkbox-margin {
      margin-bottom: 1rem;
    }

    .new-rule {
      margin-top: 1rem;
      font-weight: 600;
      font-size: 16px;
      line-height: 24px;
      color: #1284ed;
      cursor: pointer;
    }
  }
</style>
