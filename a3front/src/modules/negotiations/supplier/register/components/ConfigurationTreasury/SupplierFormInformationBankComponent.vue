<template>
  <a-spin :spinning="isLoadingForm">
    <div class="form-information-bank-component">
      <a-form layout="vertical" ref="formRef" :model="formBankInformationTreasury">
        <div>
          <div class="section-header">
            <span class="rule-description">1. Información básica</span>
            <hr class="section-divider" />
          </div>

          <div class="grid-container">
            <div>
              <a-form-item label="Banco:" v-bind="validateInfos.bank_id">
                <a-select
                  placeholder="Selecciona"
                  v-model:value="formBankInformationTreasury.bank_id"
                >
                  <a-select-option v-for="item in state.bank" :key="item.id" :value="item.id">
                    {{ item.name }}
                  </a-select-option>
                </a-select>
              </a-form-item>
            </div>
            <div>
              <a-form-item label="Sucursal:" v-bind="validateInfos.branch_office">
                <a-input
                  placeholder="Sucursal"
                  v-model:value="formBankInformationTreasury.branch_office"
                />
              </a-form-item>
            </div>
          </div>

          <div class="grid-container">
            <div>
              <a-form-item label="Dirección principal:" v-bind="validateInfos.address">
                <a-input
                  placeholder="Avenida / Calle / Jirón, Nº, Mz, Lote"
                  v-model:value="formBankInformationTreasury.address"
                />
              </a-form-item>
            </div>
            <div>
              <a-form-item label="País:" v-bind="validateInfos.country_id">
                <a-select
                  placeholder="Selecciona"
                  v-model:value="formBankInformationTreasury.country_id"
                  :loading="state.countryLoading"
                  :disabled="state.countryDisabled"
                >
                  <a-select-option v-for="item in state.countries" :key="item.id" :value="item.id">
                    {{ item.name }}
                  </a-select-option>
                </a-select>
              </a-form-item>
            </div>
          </div>

          <div class="grid-container">
            <div>
              <a-form-item label="Ciudad:" v-bind="validateInfos.state_id">
                <a-select
                  placeholder="Selecciona"
                  v-model:value="formBankInformationTreasury.state_id"
                  :loading="state.statesLoading"
                  :disabled="state.statesDisabled"
                >
                  <a-select-option v-for="item in state.states" :key="item.id" :value="item.id">
                    {{ item.name }}
                  </a-select-option>
                </a-select>
              </a-form-item>
            </div>
            <div>
              <a-form-item label="ABA #:" v-bind="validateInfos.number_aba">
                <a-input
                  placeholder="ABA #"
                  v-model:value="formBankInformationTreasury.number_aba"
                />
              </a-form-item>
            </div>
          </div>

          <div class="grid-container">
            <div>
              <a-form-item
                label="Cuenta moneda nacional:"
                v-bind="validateInfos.accounts_national_bank_account_number"
              >
                <a-input
                  placeholder="Cuenta moneda nacional"
                  v-model:value="formBankInformationTreasury.accounts_national_bank_account_number"
                />
              </a-form-item>
            </div>
            <div>
              <a-form-item
                label="Tipo:"
                v-bind="validateInfos.accounts_national_type_bank_accounts_id"
              >
                <a-select
                  placeholder="Selecciona"
                  v-model:value="
                    formBankInformationTreasury.accounts_national_type_bank_accounts_id
                  "
                >
                  <a-select-option
                    v-for="item in state.typeBankAccount"
                    :key="item.id"
                    :value="item.id"
                  >
                    {{ item.name }}
                  </a-select-option>
                </a-select>
              </a-form-item>
            </div>
          </div>

          <div class="grid-container">
            <div>
              <a-form-item
                label="Cuenta moneda extranjera:"
                v-bind="validateInfos.accounts_foreign_bank_account_number"
              >
                <a-input
                  placeholder="Cuenta moneda nacional"
                  v-model:value="formBankInformationTreasury.accounts_foreign_bank_account_number"
                />
              </a-form-item>
            </div>
            <div>
              <a-form-item
                label="Tipo:"
                v-bind="validateInfos.accounts_foreign_type_bank_accounts_id"
              >
                <a-select
                  placeholder="Selecciona"
                  v-model:value="formBankInformationTreasury.accounts_foreign_type_bank_accounts_id"
                >
                  <a-select-option
                    v-for="item in state.typeBankAccount"
                    :key="item.id"
                    :value="item.id"
                  >
                    {{ item.name }}
                  </a-select-option>
                </a-select>
              </a-form-item>
            </div>
          </div>
        </div>

        <div>
          <div class="section-header">
            <span class="rule-description">2. Beneficiario</span>
            <hr class="section-divider" />
          </div>

          <div class="grid-container">
            <div>
              <div class="nested-grid">
                <div>
                  <a-form-item label="Tipo de documento:" v-bind="validateInfos.type_document_id">
                    <a-select
                      placeholder="Selecciona"
                      v-model:value="formBankInformationTreasury.type_document_id"
                    >
                      <a-select-option
                        v-for="item in state.typeDocument"
                        :key="item.id"
                        :value="item.id"
                      >
                        {{ item.name }}
                      </a-select-option>
                    </a-select>
                  </a-form-item>
                </div>
                <div>
                  <a-form-item label="Número:" v-bind="validateInfos.document_number">
                    <a-input
                      placeholder="Ingrese el número"
                      v-model:value="formBankInformationTreasury.document_number"
                    />
                  </a-form-item>
                </div>
                <div>
                  <a-form-item label="% de detracción:" v-bind="validateInfos.deduction_percentage">
                    <a-input-number
                      placeholder="0"
                      :min="0"
                      class="full-width"
                      v-model:value="formBankInformationTreasury.deduction_percentage"
                    />
                  </a-form-item>
                </div>
                <div>
                  <a-form-item
                    label="Cuenta banco de la nación:"
                    v-bind="validateInfos.national_bank_account"
                  >
                    <a-input
                      placeholder="Cuenta banco de la nación"
                      v-model:value="formBankInformationTreasury.national_bank_account"
                    />
                  </a-form-item>
                </div>
              </div>
            </div>

            <div>
              <div>
                <a-form-item label="Nombre:" v-bind="validateInfos.main_name">
                  <a-input
                    placeholder="Nombre"
                    v-model:value="formBankInformationTreasury.main_name"
                  />
                </a-form-item>
              </div>
              <div>
                <a-form-item
                  label="Correo electrónico principal:"
                  v-bind="validateInfos.main_email"
                >
                  <a-input
                    placeholder="Ingresa correo electrónico"
                    v-model:value="formBankInformationTreasury.main_email"
                  />
                </a-form-item>
              </div>
              <div>
                <a-form-item label="Correos electrónicos:">
                  <div v-for="(email, index) in emails" :key="index" style="margin-bottom: 0.75rem">
                    <a-input
                      class="input-email"
                      placeholder="Ingresa correo electrónico"
                      v-model:value="emails[index]"
                    >
                      <template #addonAfter>
                        <font-awesome-icon
                          :icon="['fas', 'trash-can']"
                          @click.prevent="setRemoveEmails(index)"
                        />
                      </template>
                    </a-input>
                  </div>
                  <div class="add-email-link" @click="setAddEmails">
                    <font-awesome-icon :icon="['fas', 'plus']" /> Agregar correo electrónico
                  </div>
                </a-form-item>
              </div>
            </div>
          </div>
        </div>

        <div class="form-footer">
          <a-button class="a-button-cancel" size="large" @click.prevent="handleCancel">
            Cancelar
          </a-button>
          <a-button
            class="a-button-save"
            type="primary"
            size="large"
            :loading="isLoadingButton"
            @click.prevent="handleOk"
          >
            Guardar cambios
          </a-button>
        </div>
      </a-form>
    </div>
  </a-spin>
</template>

<script lang="ts">
  import { defineComponent } from 'vue';
  import { useSupplierBankInformationTreasury } from '@/modules/negotiations/supplier/register/composables/useSupplierBankInformationTreasury';

  export default defineComponent({
    name: 'SupplierFormInformationBankComponent',
    setup() {
      const {
        formBankInformationTreasury,
        validateInfos,
        rules,
        isLoadingForm,
        handleOk,
        handleCancel,
        state,
        setAddEmails,
        setRemoveEmails,
        emails,
        isLoadingButton,
      } = useSupplierBankInformationTreasury();

      return {
        handleOk,
        handleCancel,
        formBankInformationTreasury,
        validateInfos,
        rules,
        isLoadingForm,
        state,
        setAddEmails,
        setRemoveEmails,
        emails,
        isLoadingButton,
      };
    },
  });
</script>

<style lang="scss">
  .form-information-bank-component {
    margin: 16px;

    .section-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .rule-description {
      color: #2f353a;
      font-weight: 600;
      font-size: 16px;
      line-height: 23px;
      margin-bottom: 0.75rem;
    }

    .section-divider {
      flex-grow: 1;
      border: none;
      border-top: 1px solid #e7e7e7;
      margin: 0 10px;
    }

    .grid-container {
      display: grid;
      grid-template-columns: 49% 49%;
      gap: 1rem;
    }

    .nested-grid {
      display: grid;
      grid-template-columns: 40% auto;
      column-gap: 1rem;
    }

    .full-width {
      width: 100%;
    }

    .add-email-link {
      color: #bd0d12;
      margin-top: 5px;
      cursor: pointer;
      font-weight: 500;
      font-size: 14px;
    }

    .form-footer {
      display: flex;
      justify-content: end;
      gap: 0.5rem;
    }

    .a-button-cancel,
    .a-button-save {
      width: 185px;
    }

    .input-email {
      input {
        border-right: 1px solid transparent;
      }

      .ant-input-status-error {
        border-right: 1px solid transparent !important;
      }

      .ant-input-group-addon {
        background: #ffffff;
        color: #bd0d12;
        border-left: 1px solid transparent;
        cursor: pointer;
      }
    }
  }
</style>
