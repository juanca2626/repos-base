<template>
  <a-modal
    class="module-negotiations custom-modal"
    v-model:open="showModalPolicy"
    :closable="true"
    :title="isEditModal ? 'Editar politicas' : 'Agregar politicas'"
    width="54rem"
  >
    <div class="group-policies-inputs">
      <a-spin :spinning="isLoadingForm">
        <a-form layout="vertical" ref="formRef" :model="formStatePolicy">
          <div>
            <div class="section-header">
              <span class="rule-description">1. Información básica</span>
              <hr class="section-divider" />
            </div>

            <div class="flex-between">
              <div class="half-width">
                <a-form-item label="Política para:" v-bind="validateInfos.business_group_id">
                  <a-select class="full-width" v-model:value="formStatePolicy.business_group_id">
                    <a-select-option
                      v-for="(business, index) in state.resources.businessGroup"
                      :key="index"
                      :value="business.id"
                    >
                      {{ business.name }}
                    </a-select-option>
                  </a-select>
                </a-form-item>
              </div>
              <div class="auto-width">
                <div>
                  <div class="label">Cantidad permitida:</div>
                  <div class="flex-between gap-0.5">
                    <div class="replace-style flex-center">
                      <div>Mínimo</div>
                      <a-form-item v-bind="validateInfos.pax_min" class="no-margin-bottom">
                        <a-input-number
                          placeholder="0"
                          :min="1"
                          v-model:value="formStatePolicy.pax_min"
                        />
                      </a-form-item>
                    </div>
                    <div class="replace-style flex-center">
                      <div>Máximo</div>
                      <a-form-item v-bind="validateInfos.pax_max">
                        <a-input-number
                          placeholder="0"
                          :min="1"
                          v-model:value="formStatePolicy.pax_max"
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

          <div v-for="(rule, index) in rules" :key="rule.id" class="rule-container">
            <div>
              <div>
                <ul class="rule-list">
                  <li class="rule-item">
                    <div class="rule-content">
                      <div>El proveedor admite desde</div>
                      <div>
                        <a-form-item class="ant-form-item-replace">
                          <a-input-number placeholder="0" :min="0" v-model:value="rule.min_num" />
                        </a-form-item>
                      </div>
                      <div>hasta</div>
                      <div>
                        <a-form-item class="ant-form-item-replace">
                          <a-input-number placeholder="0" :min="0" v-model:value="rule.max_num" />
                        </a-form-item>
                      </div>
                      <div>
                        <a-form-item class="ant-form-item-replace">
                          <a-select
                            class="select-width"
                            placeholder="Seleccionar"
                            v-model:value="rule.unit_duration_id"
                          >
                            <a-select-option
                              v-for="(unit, index) in state.resources.unitDuration"
                              :key="index"
                              :value="unit.id"
                            >
                              {{ unit.name }}
                            </a-select-option>
                          </a-select>
                        </a-form-item>
                      </div>
                      <div>, antes del inicio del servicio.</div>
                    </div>
                  </li>
                  <li class="rule-item">
                    <div class="rule-content-cancel">
                      <div>Si cancela fuera del plazo se cobrará un</div>
                      <div>
                        <a-form-item class="ant-form-item-replace">
                          <a-select
                            class="select-width"
                            placeholder="Seleccionar"
                            v-model:value="rule.type_penalty_id"
                          >
                            <a-select-option
                              v-for="(unit, index) in state.resources.typePenalty"
                              :key="index"
                              :value="unit.id"
                            >
                              {{ unit.name }}
                            </a-select-option>
                          </a-select>
                        </a-form-item>
                      </div>
                      <div>de</div>
                      <div>
                        <a-form-item class="ant-form-item-replace">
                          <a-input-number
                            placeholder="0.00"
                            :min="0.0"
                            v-model:value="rule.penalty"
                          />
                        </a-form-item>
                      </div>
                    </div>
                  </li>
                </ul>
              </div>
              <div class="switch-container">
                <div>
                  <a-switch v-model:checked="rule.charged_taxes" />
                  Cobro de impuesto
                </div>
                <div>
                  <a-switch v-model:checked="rule.charged_services" />
                  Cobro de servicios
                </div>
              </div>
              <ul class="rule-list list-rules-error" style="margin-top: 1rem">
                <li v-for="(item, index) in state.listError" :key="index">{{ item[0] }}</li>
              </ul>
            </div>
            <div>
              <div class="close-icon" @click="handleRemoveRule(index)">
                <font-awesome-icon class="cursor-pointer" :icon="['fas', 'circle-xmark']" />
              </div>
            </div>
          </div>
        </a-form>
      </a-spin>
    </div>

    <template #footer>
      <a-button class="a-button-cancel" @click="handleCancel" size="large">Cancelar</a-button>
      <a-button
        class="a-button-save"
        type="primary"
        @click="handleOk"
        :loading="state.isLoadingButton"
        size="large"
      >
        Guardar
      </a-button>
    </template>
  </a-modal>
</template>

<script setup lang="ts">
  import { UseNegotiationPolicies } from '@/modules/negotiations/supplier/register/configuration-module/composables/useNegotiationPolicies';

  defineOptions({
    name: 'NegotiationPoliciesModalComponent',
  });

  const {
    formStatePolicy,
    handleOk,
    handleCancel,
    handleAddRule,
    handleRemoveRule,
    validateInfos,
    state,
    showModalPolicy,
    isEditModal,
    rules,
    isLoadingForm,
  } = UseNegotiationPolicies();
</script>

<style scoped>
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
      display: flex;
      justify-content: space-between;
      gap: 0.5rem;
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
</style>
