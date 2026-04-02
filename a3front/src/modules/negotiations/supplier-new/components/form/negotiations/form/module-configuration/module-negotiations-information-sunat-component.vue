<template>
  <div class="information-sunat">
    <div class="container">
      <EmptyStateFormGlobalComponent
        v-if="!getShowFormComponent(FormComponentEnum.MODULE_SUNAT_INFORMATION)"
        title="Información Sunat"
        :formSpecific="FormComponentEnum.MODULE_SUNAT_INFORMATION"
      />
      <div v-else>
        <div class="title-form">
          <div>Información Sunat</div>
          <div
            v-if="getIsEditFormComponent(FormComponentEnum.MODULE_SUNAT_INFORMATION)"
            class="edit-form"
            @click="handleShowForm"
          >
            Editar <font-awesome-icon :icon="['fas', 'pen-to-square']" />
          </div>
        </div>
        <spin-global-component
          tip="Cargando..."
          :spinning="getLoadingFormComponent(FormComponentEnum.MODULE_SUNAT_INFORMATION)"
        >
          <div v-if="!getIsEditFormComponent(FormComponentEnum.MODULE_SUNAT_INFORMATION)">
            <a-form :model="formState" ref="formRef" :rules="formRules">
              <a-form-item name="types_tax_documents_id">
                <template v-slot:label>
                  <div class="form-label">Tipo de documento:</div>
                </template>
                <a-select
                  placeholder="Selecciona el tipo de documento"
                  v-model:value="formState.types_tax_documents_id"
                >
                  <a-select-option v-for="item in typeTaxDocument" :key="item.id" :value="item.id">
                    {{ item.name }}
                  </a-select-option>
                </a-select>
              </a-form-item>

              <a-form-item name="city_id">
                <template v-slot:label>
                  <div class="form-label">Ubicación geográfica:</div>
                </template>
                <a-select
                  placeholder="Selecciona la ubicación geográfica"
                  v-model:value="formState.city_id"
                >
                  <a-select-option v-for="item in cities" :key="item.id" :value="item.id">
                    {{ item.name }}
                  </a-select-option>
                </a-select>
              </a-form-item>
            </a-form>
            <div class="options-button">
              <a-button size="large" type="default" @click="handleClose"> Cancelar </a-button>

              <a-button
                size="large"
                type="primary"
                :disabled="!getIsFormValid"
                :loading="getLoadingButtonComponent(FormComponentEnum.MODULE_SUNAT_INFORMATION)"
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
  import { FormComponentEnum } from '@/modules/negotiations/supplier-new/enums/form-component.enum';
  import SpinGlobalComponent from '@/modules/negotiations/supplier-new/components/global/spin-global-component.vue';
  import ListItemsGlobalComponent from '@/modules/negotiations/supplier-new/components/global/list-items-global-component.vue';
  import EmptyStateFormGlobalComponent from '@/modules/negotiations/supplier-new/components/global/empty-state-form-global-component.vue';
  import { useModuleSunatInformationComposable } from '@/modules/negotiations/supplier-new/composables/form/negotiations/module-configuration/sunat-information.composable';

  defineOptions({
    name: 'ModuleNegotiationsInformationSunatComponent',
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
    typeTaxDocument,
    cities,
    handleClose,
    handleSave,
    handleShowForm,
  } = useModuleSunatInformationComposable();
</script>

<style lang="scss">
  .information-sunat {
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

    .tooltip-float {
      position: absolute;
      bottom: 37px;
      left: 161px;

      svg {
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
  }
</style>
