<template>
  <div class="basic-information-component">
    <div class="container">
      <EmptyStateFormGlobalComponent
        v-if="!getShowFormComponent(FormComponentEnum.BASIC_INFORMATION)"
        title="Información básica"
        :formSpecific="FormComponentEnum.BASIC_INFORMATION"
      />
      <div v-else>
        <div class="title-form">
          <div>Información básica</div>
          <div
            v-if="getIsEditFormComponent(FormComponentEnum.BASIC_INFORMATION)"
            class="edit-form"
            @click="handleShowForm"
          >
            Editar <font-awesome-icon :icon="['fas', 'pen-to-square']" />
          </div>
        </div>
        <spin-global-component
          tip="Cargando..."
          :spinning="getLoadingFormComponent(FormComponentEnum.BASIC_INFORMATION)"
        >
          <div v-if="!getIsEditFormComponent(FormComponentEnum.BASIC_INFORMATION)">
            <a-form :model="formState" ref="formRef" :rules="formRules">
              <a-form-item name="credit_days">
                <template v-slot:label>
                  <div class="form-label">Días de crédito:</div>
                </template>
                <a-input-number
                  v-model:value="formState.credit_days"
                  min="0"
                  placeholder="Días de crédito"
                  style="width: 382px"
                />
              </a-form-item>

              <a-form-item name="sunat_start_date">
                <template v-slot:label>
                  <div class="form-label">Fecha de inicio SUNAT:</div>
                </template>
                <a-date-picker
                  v-model:value="formState.sunat_start_date"
                  placeholder="Selecciona fecha"
                  :allowClear="false"
                  format="DD/MM/YYYY"
                  value-format="YYYY-MM-DD"
                  style="width: 382px"
                />
              </a-form-item>

              <a-form-item name="credit_days_sunat">
                <template v-slot:label>
                  <div class="form-label">Días de crédito SUNAT:</div>
                </template>
                <a-input-number
                  v-model:value="formState.credit_days_sunat"
                  min="0"
                  placeholder="Días de crédito SUNAT"
                  style="width: 382px"
                />
              </a-form-item>
            </a-form>
            <div class="options-button">
              <a-button size="large" type="default" @click="handleClose"> Cancelar </a-button>

              <a-button
                size="large"
                type="primary"
                :disabled="!getIsFormValid"
                :loading="getLoadingButtonComponent(FormComponentEnum.BASIC_INFORMATION)"
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
  import { useBasicInformationComposable } from '@/modules/negotiations/supplier-new/composables/form/treasury/supplier-registration/basic-information.composable';

  defineOptions({
    name: 'BasicInformationComponent',
  });

  const {
    formState,
    formRef,
    formRules,
    getShowFormComponent,
    getIsEditFormComponent,
    getLoadingFormComponent,
    getLoadingButtonComponent,
    getListItem,
    getIsFormValid,
    handleClose,
    handleSave,
    handleShowForm,
  } = useBasicInformationComposable();
</script>

<style lang="scss">
  .basic-information-component {
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
  }
</style>
