<template>
  <div class="module-treasury-beneficiaries-component">
    <div class="container">
      <EmptyStateFormGlobalComponent
        v-if="!getShowFormComponent(FormComponentEnum.MODULE_TREASURY_BENEFICIARIES)"
        title="Beneficiario"
        :formSpecific="FormComponentEnum.MODULE_TREASURY_BENEFICIARIES"
      />
      <div v-else>
        <div class="title-form">
          <div>Beneficiario</div>
          <div
            v-if="getIsEditFormComponent(FormComponentEnum.MODULE_TREASURY_BENEFICIARIES)"
            class="edit-form"
            @click="handleShowForm"
          >
            Editar <font-awesome-icon :icon="['fas', 'pen-to-square']" />
          </div>
        </div>
        <spin-global-component
          tip="Cargando..."
          :spinning="getLoadingFormComponent(FormComponentEnum.MODULE_TREASURY_BENEFICIARIES)"
        >
          <div v-if="!getIsEditFormComponent(FormComponentEnum.MODULE_TREASURY_BENEFICIARIES)">
            {{ formState }}
            <a-form :model="formState" ref="formRef" :rules="formRules">
              <a-form-item name="type_document_id">
                <template v-slot:label>
                  <div class="form-label">Tipo de documento:</div>
                </template>
                <a-select
                  placeholder="Selecciona"
                  v-model:value="formState.type_document_id"
                  allow-clear
                  :options="typeDocument"
                  :field-names="{ label: 'name', value: 'id' }"
                />
              </a-form-item>

              <a-form-item name="document_number">
                <template v-slot:label>
                  <div class="form-label">Número:</div>
                </template>
                <a-input
                  placeholder="Ingrese el número"
                  v-model:value="formState.document_number"
                />
              </a-form-item>

              <a-form-item name="deduction_percentage">
                <template v-slot:label>
                  <div class="form-label">% de detracción:</div>
                </template>
                <a-input-number
                  placeholder="0"
                  :min="0"
                  class="full-width"
                  v-model:value="formState.deduction_percentage"
                />
              </a-form-item>

              <a-form-item name="national_bank_account">
                <template v-slot:label>
                  <div class="form-label">Cuenta banco de la nación:</div>
                </template>
                <a-input
                  placeholder="Cuenta banco de la nación"
                  v-model:value="formState.national_bank_account"
                />
              </a-form-item>

              <a-form-item name="main_name">
                <template v-slot:label>
                  <div class="form-label">Nombre:</div>
                </template>
                <a-input placeholder="Nombre" v-model:value="formState.main_name" />
              </a-form-item>

              <a-form-item name="main_email">
                <template v-slot:label>
                  <div class="form-label">Correo electrónico principal:</div>
                </template>
                <a-input
                  placeholder="Ingresa correo electrónico"
                  v-model:value="formState.main_email"
                />
              </a-form-item>

              <div class="ant-col ant-form-item-label css-dev-only-do-not-override-w750bm">
                Correos electrónicos:
              </div>
              <div
                v-for="(item, index) in formState.emails"
                :key="index"
                style="margin-bottom: 0.75rem"
              >
                <a-form-item :name="['emails', index, 'email']">
                  <a-input
                    style="width: 26.5rem"
                    placeholder="Ingresa correo electrónico"
                    v-model:value="item.email"
                  >
                    <template #addonAfter>
                      <font-awesome-icon
                        :icon="['fas', 'trash-can']"
                        @click.prevent="handleRemoveEmails(index)"
                      />
                    </template>
                  </a-input>
                </a-form-item>
              </div>
              <div class="add-email-link" @click="handleAddEmails">
                <font-awesome-icon :icon="['fas', 'plus']" /> Agregar correo electrónico
              </div>
            </a-form>
            <div class="options-button">
              <a-button size="large" type="default" @click="handleClose"> Cancelar </a-button>

              <a-button
                size="large"
                type="primary"
                :disabled="!getIsFormValid"
                :loading="
                  getLoadingButtonComponent(FormComponentEnum.MODULE_TREASURY_BENEFICIARIES)
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
  import { useModuleTreasuryBeneficiariesComposable } from '@/modules/negotiations/supplier-new/composables/form/treasury/module-configuration/module-treasury-beneficiaries.composable';

  defineOptions({
    name: 'ModuleTreasuryBeneficiariesComponent',
  });

  const {
    formState,
    formRef,
    formRules,
    typeDocument,
    getShowFormComponent,
    getIsEditFormComponent,
    getLoadingFormComponent,
    getLoadingButtonComponent,
    getListItem,
    getIsFormValid,
    handleClose,
    handleSave,
    handleShowForm,
    handleAddEmails,
    handleRemoveEmails,
  } = useModuleTreasuryBeneficiariesComposable();
</script>

<style lang="scss">
  .module-treasury-beneficiaries-component {
    border-top: 1px solid #babcbd;

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

    .add-email-link {
      color: #bd0d12;
      margin-top: 5px;
      font-weight: 500;
      font-size: 14px;
      margin-bottom: 1rem;
      cursor: pointer;
      width: 18%;
    }
  }
</style>
