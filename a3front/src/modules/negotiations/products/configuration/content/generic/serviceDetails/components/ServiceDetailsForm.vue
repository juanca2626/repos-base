<template>
  <div class="service-details-container">
    <!-- Modo lectura -->
    <ReadModeComponent
      v-if="!isEditingContent"
      title="Detalles del servicio"
      @edit="handleEditMode"
    >
      <div class="read-item">
        <span class="read-item-label">Nombre del servicio</span>
        <span class="read-item-value">
          {{ formState.serviceName || '-' }}
        </span>
      </div>

      <div v-if="subtipoOptions.length > 0" class="read-item">
        <span class="read-item-label">Subtipo</span>
        <span class="read-item-value">
          {{ getSubtipoLabel(formState.subtype) || '-' }}
        </span>
      </div>

      <div class="read-item">
        <span class="read-item-label">Perfil</span>
        <span class="read-item-value">{{ getPerfilLabel(formState.profile) || '-' }}</span>
      </div>

      <div class="read-item">
        <span class="read-item-label">Punto de inicio</span>
        <span class="read-item-value">
          {{ getPuntoInicioLabel(formState.startPoint) || '-' }}
        </span>
      </div>

      <div class="read-item">
        <span class="read-item-label">Punto de fin</span>
        <span class="read-item-value">{{ getPuntoFinLabel(formState.endPoint) || '-' }}</span>
      </div>

      <div class="custom-read-alert">
        <div class="alert-icon" v-if="shouldShowOperabilityAlert">
          <IconWarning />
        </div>
        <span v-if="!shouldShowOperabilityAlert" class="bullet-point">•</span>
        <span class="duration"
          >Duración: <span class="duration-value">{{ totalDuration }}</span></span
        >

        <span class="alert-text" v-if="shouldShowOperabilityAlert"
          >Pendiente por ingreso de operatividad</span
        >
      </div>

      <div class="read-item mt-2">
        <span class="read-item-label">Rangos operativos</span>
        <span class="read-item-value">{{ formattedOperatingRanges }}</span>
      </div>

      <div class="read-item">
        <span class="read-item-label">Días de atención</span>
        <span class="read-item-value">{{ formattedAttentionDays }}</span>
      </div>

      <div class="read-item">
        <span class="read-item-label">Estado</span>
        <span class="read-item-value">
          {{ getEstadoLabel(formState.status) || '-' }}
        </span>
      </div>

      <div class="read-item">
        <span class="read-item-label">Visualización para el cliente</span>
        <span class="read-item-value">
          {{ formState.showToClient ? 'Activa' : 'Inactiva' }}
        </span>
      </div>
    </ReadModeComponent>

    <!-- Modo edición -->
    <a-form
      v-else
      :model="formState"
      ref="formRef"
      :rules="formRules"
      layout="vertical"
      class="compact-form"
    >
      <WizardHeaderComponent
        title="Detalles del servicio"
        :completed="completedFields"
        :total="totalFields"
      />

      <a-row :gutter="8">
        <a-col :span="24" :lg="10">
          <a-form-item name="serviceName">
            <template #label>
              <required-label class="form-label" label="Nombre del servicio:" />

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
                      <rect width="16" height="16" fill="white" transform="translate(0 0.414062)" />
                    </clipPath>
                  </defs>
                </svg>
              </a-tooltip>
            </template>
            <a-input v-model:value="formState.serviceName" placeholder="Ingresa" />
          </a-form-item>
        </a-col>
      </a-row>

      <a-row v-if="subtipoOptions.length > 0" :gutter="8">
        <a-col :span="24" :lg="10">
          <a-form-item name="subtype">
            <template #label>
              <required-label class="form-label" label="Subtipo:" />
            </template>
            <a-select
              v-model:value="formState.subtype"
              placeholder="Selecciona"
              class="custom-select"
              :options="subtipoOptions"
            />
          </a-form-item>
        </a-col>
      </a-row>

      <a-row :gutter="8">
        <a-col :span="24" :lg="10">
          <a-form-item name="profile">
            <template #label>
              <required-label class="form-label" label="Perfil:" />
            </template>
            <a-select
              v-model:value="formState.profile"
              placeholder="Selecciona"
              class="custom-select"
              :options="perfilOptions"
            />
          </a-form-item>
        </a-col>
      </a-row>

      <template v-if="isMultiPointSelect">
        <a-row :gutter="8">
          <a-col :span="24" :lg="10">
            <a-form-item name="startPoint">
              <template #label>
                <required-label class="form-label" label="Punto de inicio:" />
              </template>
              <a-select
                v-model:value="formState.startPoint"
                mode="multiple"
                placeholder="Selecciona"
                class="custom-select multi-point-select"
                :options="puntoInicioOptions"
                :max-tag-count="2"
              />
            </a-form-item>
          </a-col>
        </a-row>
        <a-row :gutter="8">
          <a-col :span="24" :lg="10">
            <a-form-item name="endPoint">
              <template #label>
                <required-label class="form-label" label="Punto de fin:" />
              </template>
              <a-select
                v-model:value="formState.endPoint"
                mode="multiple"
                placeholder="Selecciona"
                class="custom-select multi-point-select"
                :options="puntoFinOptions"
                :max-tag-count="2"
              />
            </a-form-item>
          </a-col>
        </a-row>
      </template>
      <a-row v-else :gutter="8">
        <a-col :span="24" :sm="12" :lg="5">
          <a-form-item name="startPoint">
            <template #label>
              <required-label class="form-label" label="Punto de inicio:" />
            </template>
            <a-select
              v-model:value="formState.startPoint"
              placeholder="Selecciona"
              class="custom-select"
              :options="puntoInicioOptions"
            />
          </a-form-item>
        </a-col>
        <a-col :span="24" :sm="12" :lg="5">
          <a-form-item name="endPoint">
            <template #label>
              <required-label class="form-label" label="Punto de fin:" />
            </template>
            <a-select
              v-model:value="formState.endPoint"
              placeholder="Selecciona"
              class="custom-select"
              :options="puntoFinOptions"
            />
          </a-form-item>
        </a-col>
      </a-row>

      <a-row :gutter="8">
        <a-col :span="24" :lg="2">
          <a-form-item name="duration">
            <template #label>
              <span class="form-label">Duración</span>
            </template>
            <a-input
              v-model:value="formState.duration"
              placeholder="00:00"
              style="width: 100%"
              :disabled="isAutomaticService"
            />
          </a-form-item>
        </a-col>
      </a-row>

      <a-row v-if="shouldShowOperabilityAlert" :gutter="8">
        <div class="pending-alert">
          <svg
            width="16"
            height="15"
            viewBox="0 0 16 15"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              d="M15.8214 12.4453L9.1555 1.06985C8.64517 0.194816 7.35731 0.194816 6.84354 1.06985L0.180799 12.4453C-0.332032 13.3172 0.30671 14.4141 1.33459 14.4141H14.6663C15.6901 14.4141 16.3308 13.3203 15.8214 12.4453ZM7.24918 4.66373C7.24918 4.24965 7.58513 3.9137 7.99921 3.9137C8.41328 3.9137 8.74923 4.25121 8.74923 4.66373V8.66387C8.74923 9.07795 8.41328 9.41389 8.02733 9.41389C7.64138 9.41389 7.24918 9.07951 7.24918 8.66387V4.66373ZM7.99921 12.414C7.45669 12.414 7.01667 11.974 7.01667 11.4315C7.01667 10.8889 7.45637 10.4489 7.99921 10.4489C8.54204 10.4489 8.98174 10.8889 8.98174 11.4315C8.98049 11.9734 8.54297 12.414 7.99921 12.414Z"
              fill="#E4B804"
            />
          </svg>

          <span class="unavailable-text">Pendiente por ingreso de operatividad</span>
        </div>
      </a-row>

      <ScheduleComponent
        :key="`${props.currentKey}-${props.currentCode}`"
        :current-key="props.currentKey"
        :current-code="props.currentCode"
      />

      <a-row :gutter="8">
        <a-col :span="24" :lg="11">
          <a-form-item name="status">
            <template #label>
              <required-label class="form-label" label="Estado:" />
            </template>
            <a-select
              v-model:value="formState.status"
              placeholder="Suspendido"
              class="custom-select"
              :options="estadoOptions"
              :allowClear="true"
            />
          </a-form-item>
        </a-col>

        <a-col v-show="shouldShowReason" :span="24" :lg="13">
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

      <a-row :gutter="8">
        <a-col :span="24">
          <a-form-item name="showToClient">
            <template #label>
              <required-label class="form-label" label="¿Lo visualiza el cliente?" />
            </template>
            <a-radio-group v-model:value="formState.showToClient">
              <a-radio :value="true">Sí</a-radio>
              <a-radio :value="false">No</a-radio>
            </a-radio-group>
          </a-form-item>
        </a-col>
      </a-row>

      <div class="service-details-actions">
        <a-button
          size="large"
          type="primary"
          :disabled="isLoadingButton || !getIsFormValid"
          @click="handleSaveAndAdvance"
        >
          Guardar Datos
        </a-button>
      </div>
    </a-form>

    <!-- Modal de confirmación de edición -->
    <ModalEditComponent
      :visible="showEditModal"
      @confirm="handleConfirmEdit"
      @cancel="handleCancelEdit"
    />
  </div>
</template>

<script setup lang="ts">
  import RequiredLabel from '@/modules/negotiations/supplier-new/components/required-label.vue';
  import WizardHeaderComponent from '@/modules/negotiations/products/configuration/components/WizardHeaderComponent.vue';
  import ReadModeComponent from '@/modules/negotiations/products/configuration/content/shared/components/ReadModeComponent.vue';
  import ModalEditComponent from '@/modules/negotiations/products/configuration/components/ModalEditComponent.vue';
  import ScheduleComponent from './ScheduleComponent.vue';
  import IconWarning from '@/modules/negotiations/products/configuration/icons/IconWarning.vue';
  import { useServiceDetailsForm } from '../composables/useServiceDetailsForm';

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
    subtipoOptions,
    perfilOptions,
    puntoInicioOptions,
    puntoFinOptions,
    estadoOptions,
    isLoadingButton,
    getIsFormValid,
    isEditingContent,
    showEditModal,
    completedFields,
    totalFields,
    totalDuration,
    isAutomaticService,
    isMultiPointSelect,
    shouldShowOperabilityAlert,
    shouldShowReason,
    formattedOperatingRanges,
    formattedAttentionDays,

    getSubtipoLabel,
    getPerfilLabel,
    getPuntoInicioLabel,
    getPuntoFinLabel,
    getEstadoLabel,
    handleEditMode,
    handleConfirmEdit,
    handleCancelEdit,
    handleSaveAndAdvance,
  } = useServiceDetailsForm(props);
</script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .service-details-container {
    background-color: #ffffff;

    /* Todos los selects y sus estados */
    :deep(.ant-select),
    :deep(.ant-select-single),
    :deep(.ant-select-multiple) {
      box-shadow: none !important;

      .ant-select-selector {
        box-shadow: none !important;
        -webkit-box-shadow: none !important;
        -moz-box-shadow: none !important;
        outline: none !important;
        transition: border-color 0.2s !important;
      }

      /* Estado hover */
      &:hover .ant-select-selector,
      .ant-select-selector:hover {
        border-color: #bd0d12 !important;
        box-shadow: none !important;
        -webkit-box-shadow: none !important;
        outline: none !important;
      }

      /* Estado focused */
      &.ant-select-focused .ant-select-selector,
      .ant-select-selector:focus {
        border-color: #bd0d12 !important;
        box-shadow: none !important;
        outline: none !important;
      }

      /* Estado open */
      &.ant-select-open .ant-select-selector {
        border-color: #bd0d12 !important;
        box-shadow: none !important;
        -webkit-box-shadow: none !important;
        outline: none !important;
      }

      /* Combinaciones de estados */
      &.ant-select-focused:hover .ant-select-selector,
      &.ant-select-open:hover .ant-select-selector,
      &.ant-select-focused.ant-select-open .ant-select-selector {
        border-color: #bd0d12 !important;
        box-shadow: none !important;
        -webkit-box-shadow: none !important;
        outline: none !important;
      }

      /* Input interno */
      .ant-select-selection-search-input {
        box-shadow: none !important;
        outline: none !important;
      }

      &.multi-point-select {
        .ant-select-selection-placeholder {
          top: 55% !important;
        }

        .ant-select-selection-item {
          background: #dcdcdc !important;
          color: #2f353a !important;

          .ant-select-selection-item-remove {
            color: #7e8285 !important;
          }
        }
      }

      /* Todos los elementos internos */
      * {
        box-shadow: none !important;
        -webkit-box-shadow: none !important;
      }
    }

    .compact-form {
      margin-bottom: 16px;
    }

    .compact-form :deep(.ant-form-item) {
      margin-bottom: 12px; /* Ajusta este valor según necesites (default es 24px) */
    }

    .compact-form :deep(.ant-row) {
      margin-bottom: 2px;
    }

    /* Si quieres aún más compacto */
    .compact-form :deep(.ant-form-item-label) {
      padding-bottom: 4px; /* Reduce espacio entre label e input */
    }

    .form-section {
      :deep(.ant-form-item) {
        margin-bottom: 16px;
      }
    }

    .custom-duration {
      font-size: 14px;
      line-height: 24px;
      color: $color-black-3;
      font-weight: 600;
      margin-left: 8px;
      background-color: rgb(241, 241, 241);
      padding: 10px 8px;
    }

    .duration-value {
      font-weight: 400;
      font-size: 14px;
      line-height: 24px;
      margin-left: 4px;
    }

    :deep(.ant-form-item-label) {
      padding-bottom: 4px;

      > label {
        font-weight: 500;
        font-size: 14px;
        line-height: 20px;
        color: #2f353a;
        height: auto;

        &.ant-form-item-required::before {
          display: none !important;
        }
      }
    }

    :deep(.ant-input),
    :deep(.ant-input-number) {
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

    :deep(.ant-input-number:focus-within) {
      border-color: #bd0d12;
      box-shadow: none;
      outline: none;
    }

    :deep(.ant-input-number) {
      width: 100%;
    }

    :deep(.ant-select-selector) {
      display: flex;
      align-items: center;
      border-radius: 4px;
      border: 1px solid #d9d9d9;

      &::placeholder {
        color: #bfbfbf;
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
        color: $color-black-3;
        font-size: 12px;
        background: transparent;
        border: none;
      }
    }

    :deep(.ant-checkbox-wrapper) {
      font-size: 14px;
      color: #2f353a;

      .ant-checkbox {
        .ant-checkbox-inner {
          width: 18px;
          height: 18px;
          border-radius: 3px;
          border-color: #d9d9d9;
        }

        &.ant-checkbox-checked {
          .ant-checkbox-inner {
            background-color: #bd0d12;
            border-color: #bd0d12;
          }
        }

        &:hover .ant-checkbox-inner {
          border-color: #bd0d12;
        }
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

    .pending-alert {
      height: 32px;
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 6px 8px;
      background-color: #fffbe6;
      border: 1px solid $color-warning-light;
      border-radius: 4px;
      margin-top: 10px;
      margin-bottom: 20px;

      .unavailable-text {
        color: $color-warning-dark;
        font-weight: 400;
        font-size: 14px;
        line-height: 14px;
      }

      svg {
        flex-shrink: 0;
      }

      span {
        font-size: 14px;
        color: #000000;
        line-height: 22px;
      }
    }

    .service-details-actions {
      display: flex;
      gap: 1rem;

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

  .custom-read-alert {
    display: flex;
    align-items: center;
    gap: 4px;
    height: 32px;

    .alert-icon {
      background-color: $color-warning-light;
      border-radius: 8px;
      padding: 6px 8px;
    }

    .bullet-point {
      font-size: 16px;
      color: $color-black-3;
    }

    .duration {
      font-size: 14px;
      font-weight: 600;
      color: $color-black-3;

      &-value {
        font-size: 14px;
        font-weight: 400;
        color: $color-black-3;
      }
    }

    .alert-text {
      background-color: $color-warning-light;
      color: $color-warning-dark;
      border-radius: 8px;
      padding: 6px 8px;
    }
  }
</style>

<style lang="scss">
  .naming-guidelines-tooltip {
    max-width: 600px !important;

    .ant-tooltip-inner {
      padding: 16px;
      background-color: #212121;
      color: #babcbd;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    }

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
      margin-bottom: 12px;

      li {
        margin-bottom: 6px;
        color: #d9d9d9;
      }
    }

    .tooltip-examples {
      margin-top: 12px;
    }

    .tooltip-subtitle {
      font-weight: 600;
      font-size: 12px;
      margin-bottom: 8px;
      color: #babcbd;
    }

    .tooltip-example {
      margin-bottom: 6px;
      color: #d9d9d9;
      padding-left: 8px;
    }
  }
</style>
