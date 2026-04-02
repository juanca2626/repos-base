<template>
  <div class="train-service-details">
    <!-- Modo lectura -->
    <ReadModeComponent
      v-if="!isEditingContent"
      title="Detalles del servicio"
      @edit="handleEditMode"
    >
      <div class="read-item">
        <span class="read-item-label">Nombre del servicio</span>
        <span class="read-item-value">{{ formState.serviceName || '-' }}</span>
      </div>

      <div class="read-item">
        <span class="read-item-label">Punto de inicio</span>
        <span class="read-item-value">{{ getStartPointLabel(formState.startPoint) || '-' }}</span>
      </div>

      <div class="read-item">
        <span class="read-item-label">Punto de fin</span>
        <span class="read-item-value">{{ getEndPointLabel(formState.endPoint) || '-' }}</span>
      </div>

      <div class="read-item">
        <span class="read-item-label">Estado</span>
        <span class="read-item-value">{{ getStatusLabel(formState.status) || '-' }}</span>
      </div>

      <div class="read-item">
        <span class="read-item-label">¿Lo visualiza el cliente?</span>
        <span class="read-item-value">{{ formState.showToClient ? 'Sí' : 'No' }}</span>
      </div>

      <div class="read-item">
        <span class="read-item-label">Horarios configurados</span>
        <span class="read-item-value"
          >{{ schedules.length }} {{ schedules.length === 1 ? 'horario' : 'horarios' }}</span
        >
      </div>
    </ReadModeComponent>

    <!-- Modo edición -->
    <template v-else>
      <WizardHeaderComponent
        title="Detalles del servicio"
        :completed="completedFieldsCount"
        :total="totalFieldsCount"
      />

      <a-form
        :model="formState"
        ref="formRef"
        :rules="formRules"
        layout="vertical"
        class="compact-form"
      >
        <a-row :gutter="16">
          <a-col :span="24" :lg="10">
            <a-form-item name="serviceName">
              <template #label>
                <span class="form-label">
                  Nombre del servicio: <span class="required-asterisk">*</span>
                </span>
                <a-tooltip placement="topLeft" overlay-class-name="naming-guidelines-tooltip">
                  <template #title>
                    <div class="tooltip-content">
                      <div class="tooltip-title">Pautas para redactar nombre comercial</div>
                      <ul class="tooltip-list">
                        <li>Tipo de servicio + categoría + duración o detalle de la ubicación.</li>
                        <li>No se utiliza la palabra paquete</li>
                      </ul>
                      <div class="tooltip-examples">
                        <div class="tooltip-subtitle">Ejemplos:</div>
                        <div class="tooltip-example">
                          1. Tour compartido a la carta de 5 días / 4 noches en Treehouse Lodge.
                        </div>
                        <div class="tooltip-example">
                          2. Traslado privado desde la estación de bus de Chachapoyas al hotel en
                          centro con un representante.
                        </div>
                      </div>
                    </div>
                  </template>
                  <svg
                    width="16"
                    height="17"
                    viewBox="0 0 16 17"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                    style="margin-left: 10px"
                  >
                    <g clip-path="url(#clip0_12682_16615)">
                      <path
                        d="M8 0.414062C3.58125 0.414062 0 3.99531 0 8.41406C0 12.8328 3.58125 16.4141 8 16.4141C12.4187 16.4141 16 12.8328 16 8.41406C16 3.99531 12.4187 0.414062 8 0.414062ZM8 4.41406C8.55219 4.41406 9 4.86188 9 5.41406C9 5.96625 8.55219 6.41406 8 6.41406C7.44781 6.41406 7 5.96719 7 5.41406C7 4.86094 7.44688 4.41406 8 4.41406ZM9.25 12.4141H6.75C6.3375 12.4141 6 12.0797 6 11.6641C6 11.2484 6.33594 10.9141 6.75 10.9141H7.25V8.91406H7C6.58594 8.91406 6.25 8.57812 6.25 8.16406C6.25 7.75 6.5875 7.41406 7 7.41406H8C8.41406 7.41406 8.75 7.75 8.75 8.16406V10.9141H9.25C9.66406 10.9141 10 11.25 10 11.6641C10 12.0781 9.66562 12.4141 9.25 12.4141Z"
                        fill="#2F353A"
                      />
                    </g>
                    <defs>
                      <clipPath id="clip0_12682_16615">
                        <rect
                          width="16"
                          height="16"
                          fill="white"
                          transform="translate(0 0.414062)"
                        />
                      </clipPath>
                    </defs>
                  </svg>
                </a-tooltip>
              </template>
              <a-input v-model:value="formState.serviceName" placeholder="Ingresa" />
            </a-form-item>
          </a-col>
        </a-row>

        <a-row :gutter="16">
          <a-col :span="24" :sm="12" :lg="5">
            <a-form-item name="startPoint">
              <template #label>
                <span class="form-label">
                  Punto de inicio: <span class="required-asterisk">*</span>
                </span>
              </template>
              <a-select
                v-model:value="formState.startPoint"
                placeholder="Selecciona"
                :options="startPointOptions"
              />
            </a-form-item>
          </a-col>

          <a-col :span="24" :sm="12" :lg="5">
            <a-form-item name="endPoint">
              <template #label>
                <span class="form-label">
                  Punto de fin: <span class="required-asterisk">*</span>
                </span>
              </template>
              <a-select
                v-model:value="formState.endPoint"
                placeholder="Selecciona"
                :options="endPointOptions"
              />
            </a-form-item>
          </a-col>
        </a-row>

        <!-- Train Schedule Table -->
        <TrainScheduleTable
          :schedules="schedules"
          :current-key="currentKey"
          :current-code="currentCode"
          @add="addSchedule"
          @remove="removeSchedule"
          @duplicate="duplicateSchedule"
        />

        <a-row :gutter="16">
          <a-col :span="24" :lg="10">
            <a-form-item name="status">
              <template #label>
                <span class="form-label"> Estado: <span class="required-asterisk">*</span> </span>
              </template>
              <a-select
                v-model:value="formState.status"
                placeholder="Activo"
                :options="statusOptions"
                :allow-clear="true"
              />
            </a-form-item>
          </a-col>

          <a-col v-show="shouldShowReason" :span="24" :lg="10">
            <a-form-item name="reason">
              <template #label>
                <span class="form-label">Motivo:</span>
              </template>
              <a-textarea
                v-model:value="formState.reason"
                placeholder="Motivo descrito por especialista"
                :rows="1"
                :maxlength="150"
                :showCount="true"
              />
            </a-form-item>
          </a-col>
        </a-row>

        <a-row :gutter="16">
          <a-col :span="24">
            <a-form-item name="showToClient">
              <template #label>
                <span class="form-label">
                  ¿Lo visualiza el cliente? <span class="required-asterisk">*</span>
                </span>
              </template>
              <a-radio-group v-model:value="formState.showToClient">
                <a-radio :value="true">Sí</a-radio>
                <a-radio :value="false">No</a-radio>
              </a-radio-group>
            </a-form-item>
          </a-col>
        </a-row>

        <div class="form-actions">
          <a-button
            size="large"
            type="primary"
            :disabled="isLoadingButton || !getIsFormValid"
            @click="onSaveAndAdvance"
          >
            Guardar Datos
          </a-button>
        </div>
      </a-form>
    </template>

    <!-- Modal de confirmación de edición -->
    <ModalEditComponent
      :visible="showEditModal"
      @confirm="handleConfirmEdit"
      @cancel="handleCancelEdit"
    />
  </div>
</template>

<script setup lang="ts">
  import { useTrainService } from '../composables/useTrainService';
  import WizardHeaderComponent from '@/modules/negotiations/products/configuration/components/WizardHeaderComponent.vue';
  import ReadModeComponent from '@/modules/negotiations/products/configuration/content/shared/components/ReadModeComponent.vue';
  import ModalEditComponent from '@/modules/negotiations/products/configuration/components/ModalEditComponent.vue';
  import TrainScheduleTable from './TrainScheduleTable.vue';

  interface Props {
    currentKey?: string;
    currentCode?: string;
  }

  const props = withDefaults(defineProps<Props>(), {
    currentKey: '',
    currentCode: '',
  });

  const {
    formState,
    formRules,
    startPointOptions,
    endPointOptions,
    statusOptions,
    isLoadingButton,
    getIsFormValid,
    schedules,
    isEditingContent,
    showEditModal,
    totalFieldsCount,
    completedFieldsCount,
    shouldShowReason,
    addSchedule,
    removeSchedule,
    duplicateSchedule,
    getStartPointLabel,
    getEndPointLabel,
    getStatusLabel,
    handleEditMode,
    handleConfirmEdit,
    handleCancelEdit,
    handleSaveAndAdvance,
  } = useTrainService({
    currentKey: props.currentKey ?? '',
    currentCode: props.currentCode ?? '',
  });

  const onSaveAndAdvance = () => {
    handleSaveAndAdvance();
  };
</script>

<style scoped lang="scss">
  .train-service-details {
    background-color: #ffffff;

    .compact-form :deep(.ant-form-item) {
      margin-bottom: 16px;
    }

    .form-label {
      font-weight: 500;
      font-size: 14px;
      line-height: 20px;
      color: #2f353a;

      .required-asterisk {
        color: #bd0d12;
        margin-left: 2px;
      }
    }

    :deep(.ant-input),
    :deep(.ant-select-selector) {
      border-radius: 4px;
      border: 1px solid #d9d9d9;

      &::placeholder {
        color: #bfbfbf;
      }

      &:focus,
      &:hover {
        border-color: #bd0d12;
        box-shadow: none;
        outline: none;
      }
    }

    :deep(.ant-select) {
      box-shadow: none !important;

      .ant-select-selector {
        box-shadow: none !important;
        outline: none !important;
        transition: border-color 0.2s !important;
      }

      &:hover .ant-select-selector,
      .ant-select-selector:hover {
        border-color: #bd0d12 !important;
        box-shadow: none !important;
        outline: none !important;
      }

      &.ant-select-focused .ant-select-selector,
      .ant-select-selector:focus {
        border-color: #bd0d12 !important;
        box-shadow: none !important;
        outline: none !important;
      }

      &.ant-select-open .ant-select-selector {
        border-color: #bd0d12 !important;
        box-shadow: none !important;
        outline: none !important;
      }
    }

    :deep(.ant-radio-group) {
      .ant-radio-wrapper {
        font-size: 14px;
        color: #2f353a;
        margin-right: 24px;

        .ant-radio {
          .ant-radio-inner {
            width: 20px;
            height: 20px;
            border-color: #d9d9d9;
          }

          &.ant-radio-checked {
            .ant-radio-inner {
              border-color: #bd0d12;
              background-color: #bd0d12;

              &::after {
                background-color: #ffffff;
              }
            }
          }

          &:hover .ant-radio-inner {
            border-color: #bd0d12;
          }
        }
      }
    }

    :deep(.ant-input-textarea-show-count) {
      textarea {
        resize: none;
        border-radius: 4px;
        border: 1px solid #d9d9d9;

        &::placeholder {
          color: #bfbfbf;
        }

        &:focus,
        &:hover {
          border-color: #bd0d12;
          box-shadow: none;
          outline: none;
        }
      }

      .ant-input-data-count {
        position: absolute;
        bottom: -20px;
        right: 0;
        color: #2f353a;
        font-size: 12px;
        background: transparent;
        border: none;
      }
    }

    .form-actions {
      display: flex;
      gap: 1rem;
      margin-top: 24px;

      .ant-btn-primary {
        min-width: 159px;
        height: 48px;
        border-radius: 5px;
        font-weight: 500;
        font-size: 16px;
        color: #ffffff;
        background: #bd0d12;
        border-color: #bd0d12;

        &:hover:not(:disabled) {
          background: #d54247;
          border-color: #d54247;
        }

        &:disabled {
          color: #ffffff;
          background: #acaeb0;
          border-color: #acaeb0;
          cursor: not-allowed;
        }
      }
    }
  }
</style>

<style lang="scss">
  .tooltip-content {
    font-size: 12px;
    line-height: 1.6;
  }

  .tooltip-title {
    font-weight: 600;
    font-size: 12px;
    margin-bottom: 12px;
    color: #babcbd;
  }

  .tooltip-list {
    margin: 0;
    padding-left: 20px;

    li {
      margin-bottom: 6px;
      color: #d9d9d9;
    }
  }
</style>
