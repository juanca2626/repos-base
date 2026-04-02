<template>
  <a-drawer
    class="policies-form-component"
    v-model:open="showForm"
    :closable="false"
    :title="formState.id ? 'Editar politicas' : 'Agregar politicas'"
    width="58rem"
  >
    <div class="group-policies-inputs">
      <a-spin :spinning="isLoadingForm">
        <a-form layout="vertical" :model="formState" ref="formRef" :rules="formRules">
          <div>
            <div class="section-header">
              <span class="rule-description">1. Información básica</span>
              <hr class="section-divider" />
            </div>

            <div class="flex-between">
              <div class="half-width">
                <a-form-item label="Política para:" name="business_group_id">
                  <a-select
                    class="full-width"
                    v-model:value="formState.business_group_id"
                    placeholder="Seleccionar"
                    :options="businessGroup"
                    allow-clear
                    :field-names="{ label: 'name', value: 'id' }"
                  />
                </a-form-item>
              </div>
              <div class="auto-width">
                <div>
                  <div class="label">Cantidad permitida:</div>
                  <div class="flex-between gap-0.5">
                    <div class="replace-style flex-center">
                      <div>Mínimo</div>
                      <a-form-item name="pax_min" class="no-margin-bottom">
                        <a-input-number
                          placeholder="0"
                          :min="1"
                          v-model:value="formState.pax_min"
                        />
                      </a-form-item>
                    </div>
                    <div class="replace-style flex-center">
                      <div>Máximo</div>
                      <a-form-item name="pax_max">
                        <a-input-number
                          placeholder="0"
                          :min="1"
                          v-model:value="formState.pax_max"
                        />
                      </a-form-item>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="section-header">
            <span class="rule-description">2. Reglas</span>
            <hr class="section-divider" />
            <span>
              <a-button type="primary" class="a-button-cancel" @click="handleAddRule">
                <font-awesome-icon icon="fa-solid fa-plus" /> Agregar regla
              </a-button>
            </span>
          </div>

          <div v-for="(item, index) in formState.rules" :key="item?.id" class="rule-container">
            <div style="width: 100%; display: flex; gap: 1rem">
              <div style="width: 100%">
                <div>
                  <ul class="rule-list">
                    <li class="rule-item">
                      <div class="rule-content">
                        <div>El proveedor admite desde</div>
                        <div>
                          <a-form-item
                            class="ant-form-item-replace"
                            :name="['rules', index, 'min_num']"
                            :rules="{
                              required: true,
                              message: 'Ingresa el número mínimo.',
                            }"
                          >
                            <a-input-number placeholder="0" :min="0" v-model:value="item.min_num" />
                          </a-form-item>
                        </div>
                        <div>hasta</div>
                        <div>
                          <a-form-item
                            class="ant-form-item-replace"
                            :name="['rules', index, 'max_num']"
                            :rules="{
                              required: true,
                              message: 'Ingresa el número máximo.',
                            }"
                          >
                            <a-input-number placeholder="0" :min="0" v-model:value="item.max_num" />
                          </a-form-item>
                        </div>
                        <div>
                          <a-form-item
                            class="ant-form-item-replace"
                            :name="['rules', index, 'unit_duration_id']"
                            :rules="{
                              required: true,
                              message: 'Selecciona una unidad de duración.',
                            }"
                          >
                            <a-select
                              class="select-width"
                              placeholder="Seleccionar"
                              v-model:value="item.unit_duration_id"
                              :options="unitDuration"
                              allow-clear
                              :field-names="{ label: 'name', value: 'id' }"
                            />
                          </a-form-item>
                        </div>
                        <div>, antes del inicio del servicio.</div>
                      </div>
                    </li>
                    <li class="rule-item">
                      <div class="rule-content-cancel">
                        <div>Si cancela fuera del plazo se cobrará un</div>
                        <div>
                          <a-form-item
                            class="ant-form-item-replace"
                            :name="['rules', index, 'type_penalty_id']"
                            :rules="{
                              required: true,
                              message: 'Selecciona un tipo de penalización.',
                            }"
                          >
                            <a-select
                              class="select-width"
                              placeholder="Seleccionar"
                              v-model:value="item.type_penalty_id"
                              :options="typePenalty"
                              allow-clear
                              :field-names="{ label: 'name', value: 'id' }"
                            />
                          </a-form-item>
                        </div>
                        <div>de</div>
                        <div>
                          <a-form-item
                            class="ant-form-item-replace"
                            :name="['rules', index, 'penalty']"
                            :rules="{
                              required: true,
                              message: 'Ingresa la penalización.',
                            }"
                          >
                            <a-input-number
                              placeholder="0.00"
                              :min="0.0"
                              v-model:value="item.penalty"
                            />
                          </a-form-item>
                        </div>
                      </div>
                    </li>
                  </ul>
                </div>
                <div class="switch-container">
                  <div>
                    <a-form-item
                      :name="['rules', index, 'charged_taxes']"
                      :rules="{
                        required: true,
                        message: 'Selecciona si se cobran impuestos.',
                      }"
                    >
                      <a-switch v-model:checked="item.charged_taxes" /> Cobro de impuesto
                    </a-form-item>
                  </div>
                  <div>
                    <a-form-item
                      :name="['rules', index, 'charged_services']"
                      :rules="{
                        required: true,
                        message: 'Selecciona si se cobran impuestos.',
                      }"
                    >
                      <a-switch v-model:checked="item.charged_services" /> Cobro de servicios
                    </a-form-item>
                  </div>
                </div>
              </div>
              <div class="close-icon" @click="handleRemoveRule(index)">
                <font-awesome-icon class="cursor-pointer" :icon="['fas', 'circle-xmark']" />
              </div>
            </div>
          </div>
        </a-form>
      </a-spin>
    </div>

    <template #footer>
      <div class="text-end">
        <div>
          <a-button class="a-button-cancel" @click="handleClose">Cancelar</a-button>
        </div>
        <div>
          <a-button
            class="a-button-save"
            type="primary"
            @click="handleSave"
            :loading="loadingButton"
          >
            Guardar
          </a-button>
        </div>
      </div>
    </template>
  </a-drawer>
</template>

<script setup lang="ts">
  import { usePoliciesComposable } from '@/modules/negotiations/supplier-new/composables/form/negotiations/module-configuration/policies.composable';

  defineOptions({
    name: 'ModulePoliciesFormComponent',
  });

  const {
    isLoadingForm,
    showForm,
    formState,
    formRef,
    formRules,
    businessGroup,
    typePenalty,
    unitDuration,
    loadingButton,
    handleSave,
    handleClose,
    handleAddRule,
    handleRemoveRule,
  } = usePoliciesComposable();
</script>

<style lang="scss">
  .policies-form-component {
    .group-policies-inputs {
      .replace-style {
        .ant-form-item {
          margin-bottom: 0 !important;
        }
      }

      .ant-form-item-replace {
        margin-bottom: 0 !important;
      }

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

      .flex-between {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
      }

      .half-width {
        width: 60%;
      }

      .auto-width {
        width: auto;
      }

      .full-width {
        width: 100%;
      }

      .gap-1rem {
        gap: 1.5rem;
      }

      .label {
        margin-bottom: 9px;
      }

      .replace-style {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 0.5rem;
      }

      .no-margin-bottom {
        margin-bottom: 0 !important;
      }

      .rule-container {
        margin-top: 1rem;
        padding: 0.75rem;
        border: 1px solid #e7e7e7;
        border-radius: 6px;
      }

      .rule-list {
        padding-left: 20px;
      }

      .rule-item {
        margin-bottom: 0.5rem;
      }

      .rule-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        height: 2rem;
        gap: 0.75rem;
      }

      .rule-content-cancel {
        display: flex;
        justify-content: flex-start;
        align-items: center;
        height: 2rem;
        gap: 0.75rem;
      }

      .switch-container {
        margin-top: 1rem;
        display: flex;
        gap: 1rem;
      }

      .select-width {
        width: 120px;
      }

      .close-icon {
        text-align: end;
        color: #bdbdbd;
        font-size: 18px;
      }

      .ant-form-show-help {
        display: none !important;
      }

      .ant-form-item-explain-error {
        display: none !important;
      }

      .list-rules-error {
        color: red;
      }
    }

    .ant-drawer-footer {
      display: inline-flex;
      gap: 1rem;
    }

    .text-end {
      justify-content: end;
      align-items: end;
      width: 100%;
      text-align: end;
      display: flex;
      gap: 1rem;
    }
  }
</style>
