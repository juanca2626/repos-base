<template>
  <div class="module-treasury-account-contable-component">
    <div class="container">
      <EmptyStateFormGlobalComponent
        v-if="!getShowFormComponent(FormComponentEnum.MODULE_TREASURY_BANK_INFORMATION)"
        title="Información bancaria"
        :formSpecific="FormComponentEnum.MODULE_TREASURY_BANK_INFORMATION"
      />
      <div v-else>
        <div class="title-form">
          <div>Información bancaria</div>
          <div
            v-if="getIsEditFormComponent(FormComponentEnum.MODULE_TREASURY_BANK_INFORMATION)"
            class="edit-form"
            @click="handleShowForm"
          >
            Editar <font-awesome-icon :icon="['fas', 'pen-to-square']" />
          </div>
        </div>
        <spin-global-component
          tip="Cargando..."
          :spinning="getLoadingFormComponent(FormComponentEnum.MODULE_TREASURY_BANK_INFORMATION)"
        >
          <div v-if="!getIsEditFormComponent(FormComponentEnum.MODULE_TREASURY_BANK_INFORMATION)">
            <a-form :model="formState" ref="formRef" :rules="formRules">
              <a-form-item name="bank_id">
                <template v-slot:label>
                  <div class="form-label">Banco:</div>
                </template>
                <a-select
                  placeholder="Selecciona"
                  v-model:value="formState.bank_id"
                  allow-clear
                  :options="bank"
                  :field-names="{ label: 'name', value: 'id' }"
                />
              </a-form-item>

              <a-form-item name="branch_office">
                <template v-slot:label>
                  <div class="form-label">Sucursal:</div>
                </template>
                <a-input placeholder="Sucursal" v-model:value="formState.branch_office" />
              </a-form-item>

              <a-form-item name="address">
                <template v-slot:label>
                  <div class="form-label">Dirección principal:</div>
                </template>
                <a-input
                  placeholder="Avenida / Calle / Jirón, Nº, Mz, Lote"
                  v-model:value="formState.address"
                />
              </a-form-item>

              <a-form-item name="country_id">
                <template v-slot:label>
                  <div class="form-label">País:</div>
                </template>
                <a-select
                  placeholder="Selecciona"
                  v-model:value="formState.country_id"
                  allow-clear
                  show-search
                  :filter-option="filterOption"
                  :options="countries"
                  :field-names="{ label: 'name', value: 'id' }"
                />
              </a-form-item>

              <a-form-item name="state_id">
                <template v-slot:label>
                  <div class="form-label">Ciudad:</div>
                </template>
                <a-select
                  placeholder="Selecciona"
                  v-model:value="formState.state_id"
                  allow-clear
                  :options="states"
                  :field-names="{ label: 'name', value: 'id' }"
                  :disabled="stateDisabled"
                />
              </a-form-item>

              <a-form-item name="number_aba">
                <template v-slot:label>
                  <div class="form-label">ABA #:</div>
                </template>
                <a-input placeholder="ABA #" v-model:value="formState.number_aba" />
              </a-form-item>

              <a-form-item name="accounts_national_bank_account_number">
                <template v-slot:label>
                  <div class="form-label">Cuenta moneda nacional:</div>
                </template>
                <a-input
                  placeholder="Cuenta moneda nacional"
                  v-model:value="formState.accounts_national_bank_account_number"
                />
              </a-form-item>

              <a-form-item name="accounts_national_type_bank_accounts_id">
                <template v-slot:label>
                  <div class="form-label">Tipo:</div>
                </template>
                <a-select
                  placeholder="Selecciona"
                  v-model:value="formState.accounts_national_type_bank_accounts_id"
                  allow-clear
                  :options="typeBankAccount"
                  :field-names="{ label: 'name', value: 'id' }"
                />
              </a-form-item>

              <a-form-item name="accounts_foreign_bank_account_number">
                <template v-slot:label>
                  <div class="form-label">Cuenta moneda extranjera:</div>
                </template>
                <a-input
                  placeholder="Cuenta moneda nacional"
                  v-model:value="formState.accounts_foreign_bank_account_number"
                />
              </a-form-item>

              <a-form-item name="accounts_foreign_type_bank_accounts_id">
                <template v-slot:label>
                  <div class="form-label">Tipo:</div>
                </template>
                <a-select
                  placeholder="Selecciona"
                  v-model:value="formState.accounts_foreign_type_bank_accounts_id"
                  allow-clear
                  :options="typeBankAccount"
                  :field-names="{ label: 'name', value: 'id' }"
                />
              </a-form-item>
            </a-form>
            <div class="options-button">
              <a-button size="large" type="default" @click="handleClose"> Cancelar </a-button>

              <a-button
                size="large"
                type="primary"
                :disabled="!getIsFormValid"
                :loading="
                  getLoadingButtonComponent(FormComponentEnum.MODULE_TREASURY_BANK_INFORMATION)
                "
                @click="handleSave"
              >
                Guardar datos
              </a-button>
            </div>
          </div>
          <ListItemsGlobalComponent v-else :items="getListItem" />
        </spin-global-component>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
  import EmptyStateFormGlobalComponent from '@/modules/negotiations/supplier-new/components/global/empty-state-form-global-component.vue';
  import { FormComponentEnum } from '@/modules/negotiations/supplier-new/enums/form-component.enum';
  import ListItemsGlobalComponent from '@/modules/negotiations/supplier-new/components/global/list-items-global-component.vue';
  import SpinGlobalComponent from '@/modules/negotiations/supplier-new/components/global/spin-global-component.vue';
  import { useModuleTreasuryBankInformationComponenComposable } from '@/modules/negotiations/supplier-new/composables/form/treasury/module-configuration/module-treasury-bank-information.composable';

  defineOptions({
    name: 'ModuleTreasuryBankInformationComponent',
  });

  const {
    formState,
    formRef,
    formRules,
    countries,
    states,
    typeBankAccount,
    bank,
    stateDisabled,
    getShowFormComponent,
    getIsEditFormComponent,
    getLoadingFormComponent,
    getLoadingButtonComponent,
    getListItem,
    getIsFormValid,
    filterOption,
    handleClose,
    handleSave,
    handleShowForm,
  } = useModuleTreasuryBankInformationComponenComposable();
</script>

<style lang="scss">
  .module-treasury-account-contable-component {
    border-top: 1px solid #babcbd;
    margin-bottom: 1rem;

    .title-form {
      font-weight: 600;
      font-size: 16px;
      line-height: 23px;
      color: #2f353a;
      margin-bottom: 1rem;
      display: flex;
      gap: 0.75rem;

      .edit-form {
        font-weight: 500;
        font-size: 16px;
        line-height: 24px;
        color: #1284ed;
        cursor: pointer;
      }
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
  }
</style>
