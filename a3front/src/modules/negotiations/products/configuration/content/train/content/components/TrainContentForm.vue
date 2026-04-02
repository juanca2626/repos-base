<template>
  <div class="service-content-form-container">
    <!-- Modo lectura -->
    <ReadModeComponent v-if="!isEditingContent" title="Contenido" @edit="handleEditMode">
      <div class="content-read-grid">
        <ContentReadTextTypeCard
          title="Remark"
          :status="remarkStatus"
          :excerpt="getRemarkExcerpt()"
          :full-html="textRemark"
          :is-expanded="remarkExpanded"
          expand-link-text="Ver itinerario completo"
          @toggle="remarkExpanded = !remarkExpanded"
        />
      </div>
      <div class="content-read-grid mb-4">
        <ContentReadInclusionsCard :items="inclusionReadItems" />
        <ContentReadRequirementsCard :items="requirementReadItems" />
      </div>
    </ReadModeComponent>

    <!-- Modo edición -->
    <div v-else class="edit-mode-container mb-4">
      <WizardHeaderComponent title="Contenido" :completed="completedFields" :total="totalItems" />

      <div class="container-title">
        <span class="main-title">Texto</span>
        <span class="info">Informaciones dirigidas al cliente</span>
      </div>

      <div>
        <a-card size="small" class="card-text-type mt-4">
          <template #title>
            <div class="card-text-type-title">Remark</div>
          </template>
          <template #extra>
            <a-button
              type="text"
              class="card-text-type-action"
              :loading="isSendingReview"
              :disabled="!textRemark || isSendingReview"
              @click="handleSendForReview"
            >
              Enviar a revisión
              <IconArrowRight />
            </a-button>
          </template>

          <div class="editor-container mt-5">
            <EditorQuillComponent class="custom-editor" v-model="textRemarkModel" />
          </div>
        </a-card>
      </div>
      <div class="container-title mt-5">
        <span class="main-title">Inclusiones</span>
        <span class="info">Información de qué incluye el servicio para el cliente</span>
      </div>

      <div class="inclusions-section inclusions-card mt-4">
        <div class="inclusions-header">
          <div class="header-col description-col">Descripción</div>
          <div class="header-col toggle-col">Incluye | No incluye</div>
          <div class="header-col visible-col">Visible al cliente</div>
          <div class="header-col actions-col">Acciones</div>
        </div>

        <div class="inclusions-rows">
          <div v-for="(inclusion, index) in inclusions" :key="index" class="inclusions-row">
            <div class="row-col description-col">
              <div v-if="inclusion.id && inclusion.editMode">
                <span class="inclusion-description">{{ inclusion.description }}</span>
              </div>
              <div v-else>
                <a-select
                  v-model:value="inclusion.description"
                  placeholder="Selecciona"
                  style="width: 100%"
                  :suffixIcon="null"
                >
                  <template #suffixIcon>
                    <DownOutlined style="color: #999; font-size: 12px" />
                  </template>
                  <a-select-option
                    v-for="option in inclusionOptions"
                    :key="option.value"
                    :value="option.value"
                    >{{ option.label }}</a-select-option
                  >
                </a-select>
              </div>
            </div>

            <div class="row-col toggle-col">
              <div v-if="inclusion.id && inclusion.editMode">
                <span class="inclusion-incluye">
                  <span
                    class="inclusion-status-dot"
                    :class="{ included: inclusion.incluye, 'not-included': !inclusion.incluye }"
                  ></span>
                  {{ inclusion.incluye ? 'Incluido' : 'No incluye' }}
                </span>
              </div>
              <div v-else>
                <a-switch v-model:checked="inclusion.incluye" />
                <span class="switch-label ml-10">{{
                  inclusion.incluye ? 'Activado' : 'Desactivado'
                }}</span>
              </div>
            </div>

            <div class="row-col visible-col">
              <div v-if="inclusion.id && inclusion.editMode">
                <span class="inclusion-incluye">
                  <span
                    class="inclusion-status-dot"
                    :class="{
                      included: inclusion.visibleCliente,
                      'not-included': !inclusion.visibleCliente,
                    }"
                  ></span>
                  {{ inclusion.visibleCliente ? 'Activado' : 'Desactivado' }}
                </span>
              </div>
              <div v-else>
                <a-switch v-model:checked="inclusion.visibleCliente" />
                <span class="switch-label ml-10">{{
                  inclusion.visibleCliente ? 'Activado' : 'Desactivado'
                }}</span>
              </div>
            </div>

            <div class="row-col actions-col">
              <IconActionButtonComponent
                v-if="inclusion.id && inclusion.editMode"
                action-type="edit"
                icon-color="#1284ED"
                @click="
                  () => {
                    inclusion.editMode = false;
                  }
                "
              />
              <IconActionButtonComponent
                v-else
                action-type="add"
                icon-color="#1284ED"
                @click="addInclusion"
              />
              <IconActionButtonComponent
                v-if="inclusionsWithoutId.length > 1 || (inclusion.id && inclusion.editMode)"
                action-type="delete_v2"
                icon-color="#1284ED"
                @click="removeInclusion(index)"
              />
            </div>
          </div>
        </div>
      </div>

      <div class="container-title mt-5">
        <span class="main-title">Requisitos</span>
        <span class="info">Información de los requisitos necesarios para el servicio</span>
      </div>

      <div class="requirements-section requirements-card mt-4">
        <div class="requirements-header">
          <div class="header-col description-col">Descripción</div>
          <div class="header-col visible-col">Visible al cliente</div>
          <div class="header-col actions-col">Acciones</div>
        </div>

        <div class="requirements-rows">
          <div v-for="(requirement, index) in requirements" :key="index" class="requirements-row">
            <div class="row-col description-col">
              <a-select
                v-model:value="requirement.description"
                placeholder="Selecciona"
                style="width: 100%"
                :suffixIcon="null"
              >
                <template #suffixIcon>
                  <DownOutlined style="color: #999; font-size: 12px" />
                </template>
                <a-select-option
                  v-for="option in requirementOptions"
                  :key="option.value"
                  :value="option.value"
                  >{{ option.label }}</a-select-option
                >
              </a-select>
            </div>

            <div class="row-col visible-col">
              <a-switch v-model:checked="requirement.visibleCliente" />
              <span class="switch-label ml-10">{{
                requirement.visibleCliente ? 'Activado' : 'Desactivado'
              }}</span>
            </div>

            <div class="row-col actions-col">
              <IconActionButtonComponent
                action-type="add"
                icon-color="#1284ED"
                @click="addRequirement"
              />
              <IconActionButtonComponent
                v-if="requirements.length > 1"
                action-type="delete_v2"
                icon-color="#1284ED"
                @click="removeRequirement(index)"
              />
            </div>
          </div>
        </div>
      </div>

      <div class="mt-4">
        <a-button
          size="large"
          type="primary"
          class="button-action-primary-strong btn-save"
          :disabled="isLoadingButton || !isFormValid"
          :loading="isLoadingButton"
          @click="handleSave"
        >
          Guardar datos
        </a-button>
      </div>
    </div>

    <!-- Modal de confirmación de edición -->
    <ModalEditComponent
      :visible="showEditModal"
      @confirm="handleConfirmEdit"
      @cancel="handleCancelEdit"
    />
  </div>
</template>

<script setup lang="ts">
  import { ref, computed } from 'vue';
  import ReadModeComponent from '@/modules/negotiations/products/configuration/content/shared/components/ReadModeComponent.vue';
  import ContentReadTextTypeCard from '@/modules/negotiations/products/configuration/content/shared/components/content/ContentReadTextTypeCard.vue';
  import ContentReadInclusionsCard from '@/modules/negotiations/products/configuration/content/shared/components/content/ContentReadInclusionsCard.vue';
  import ContentReadRequirementsCard from '@/modules/negotiations/products/configuration/content/shared/components/content/ContentReadRequirementsCard.vue';
  import ModalEditComponent from '@/modules/negotiations/products/configuration/components/ModalEditComponent.vue';
  import WizardHeaderComponent from '@/modules/negotiations/products/configuration/components/WizardHeaderComponent.vue';
  import EditorQuillComponent from '@/modules/negotiations/products/configuration/components/EditorQuillComponent.vue';
  import IconArrowRight from '@/modules/negotiations/products/configuration/icons/IconArrowRight.vue';
  import { useTrainContentForm } from '../composables/useTrainContentForm';
  import { DownOutlined } from '@ant-design/icons-vue';
  import IconActionButtonComponent from '@/modules/negotiations/products/configuration/components/IconActionButtonComponent.vue';

  interface Props {
    currentKey: string;
    currentCode: string;
  }

  const props = defineProps<Props>();

  const remarkExpanded = ref(false);

  const {
    isFormValid,
    totalItems,
    completedFields,
    textRemark,
    textRemarkModel,
    inclusions,
    inclusionsWithoutId,
    requirements,
    inclusionOptions,
    requirementOptions,
    isLoadingButton,
    isEditingContent,
    showEditModal,
    handleEditMode,
    handleConfirmEdit,
    handleCancelEdit,
    getInclusionLabel,
    getRequirementLabel,
    getRemarkExcerpt,
    addRequirement,
    removeRequirement,
    addInclusion,
    removeInclusion,
    handleSave,
    handleSendForReview,
    isSendingReview,
    remarkStatus,
  } = useTrainContentForm({
    currentKey: props.currentKey,
    currentCode: props.currentCode,
  });

  const inclusionReadItems = computed(() =>
    inclusions.value
      .filter((i: { description: string | null }) => i.description)
      .map((i: any) => ({
        label: getInclusionLabel(i.description),
        visibleCliente: i.visibleCliente,
        incluye: i.incluye,
      }))
  );

  const requirementReadItems = computed(() =>
    requirements.value
      .filter((r: { description: string | null }) => r.description)
      .map((r: any) => ({
        label: getRequirementLabel(r.description),
        visibleCliente: r.visibleCliente,
      }))
  );
</script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';
  @import '@/scss/components/negotiations/_supplierForm.scss';

  .content-read-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
    margin-top: 16px;
  }

  .btn-save {
    min-width: 160px;

    &:disabled {
      color: $color-white-4;
      background: $color-black-5;
      border-color: $color-black-5 !important;
    }
  }

  .editor-container {
    width: 100%;

    .editor-wrapper {
      position: relative;
    }

    .editor-label {
      position: absolute;
      top: 12px;
      display: flex;
      align-items: center;
      gap: 6px;
      z-index: 2;
    }

    .editor-title {
      font-size: 14px;
      font-weight: 600;
      color: $color-black;
    }
  }

  .custom-editor {
    :deep(.ql-toolbar) {
      border-bottom: none;
    }

    :deep(.ql-container) {
      border-radius: 4px;
    }

    :deep(.ql-editor) {
      border-radius: 4px;
      border-top: 1px solid #ccc;
    }
  }

  :deep(.card-text-type.ant-card) {
    box-shadow: none !important;
  }

  .card-text-type {
    background-color: $color-white;
    border-color: $color-black-4;
    border-radius: 8px;

    :deep(.ant-card-head-wrapper) {
      padding: 8px 2px 8px 12px;
    }

    :deep(.ant-card-body) {
      padding-left: 20px !important;
      padding-right: 19px !important;
      padding-bottom: 20px !important;
    }

    :deep(.ant-card-head) {
      border-bottom-color: #c5c5c5 !important;
      height: 66px;
    }

    &-title {
      font-size: 14px;
      font-weight: 600;
      color: $color-black;
    }

    &-action {
      display: flex;
      align-items: center;
      font-size: 14px;
      font-weight: 500;
      color: $color-black;
      // padding: 4px 12px;
      height: 32px;
      border: 1px solid $color-black;
      border-radius: 4px;
      background-color: $color-white;
      transition: all 0.2s ease;
      margin-right: 10px;

      &:hover {
        color: none;
        border-color: none;
        background-color: $color-white;

        svg {
          color: none;
        }
      }

      svg {
        transition: all 0.2s ease;
      }
    }
  }

  .multi-days-service-content {
    padding: 0 24px 24px 24px;
    background-color: $color-white;
  }

  .text-align-center {
    text-align: center;
  }

  .text-type-container {
    display: flex;
    align-items: center;
    gap: 10px;

    .title-tooltip-info {
      font-size: 12px;
      font-weight: 500;
    }
  }

  .custom-select-multiple {
    gap: 8px;
  }

  .custom-options-select-multiple {
    display: flex;
    gap: 1px;
    align-items: center;

    .icon-color-selected {
      color: $color-primary-strong;
      border-radius: 20px !important;
    }

    .icon-color-not-selected {
      color: $color-gray-ligth;
    }

    span {
      font-weight: 400;
      color: $color-black;
    }
  }

  .tag-close-option {
    background-color: $color-gray-ligth-4;

    span {
      font-size: 14px;
      font-weight: 400;
      color: $color-black;
    }

    :deep(.ant-tag-close-icon) {
      color: $color-black-3 !important;
    }
  }

  .days-container {
    margin-top: 10px;

    .card-day {
      border-radius: 8px;
      border: 0.5px solid $color-black-4;
    }

    .card-body {
      padding: 14px 0px;

      .day-schedule-grid-cols {
        grid-template-columns: 80px 1fr 150px 80px;
        gap: 15px;
      }

      .header-col-title {
        font-size: 14px;
        font-weight: 400;
        color: $color-black-5;
      }

      .day-schedules-header {
        display: grid;
        padding: 5px 16px;
      }

      .schedule-rows {
        background-color: $color-white;
      }

      .schedule-row {
        display: grid;
        padding: 12px 16px;
        align-items: center;
      }

      .calculated-schedule-col {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;

        .text {
          color: $color-black-2;
          font-size: 14px;
          font-weight: 600;
        }

        .clock-icon-small {
          color: $color-black-2;
          font-size: 16px;
        }
      }

      .actions-col {
        gap: 4px;
        justify-content: flex-start;
        text-align: center;
      }
    }

    .card-header {
      height: 66px;
      border-bottom: 0.5px solid $color-black-4;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 8px 20px;

      .card-title-container {
        display: flex;
        align-items: center;
        gap: 6px;
      }

      .card-title {
        font-size: 14px;
        font-weight: 600;
        color: $color-black;
        margin-top: 1px;
      }

      .add-day-button {
        cursor: pointer;
        color: $color-blue;
        font-size: 14px;
        font-weight: 500;

        &-text {
          margin-left: 4px;
        }
      }

      .delete-day-button {
        .trash-icon {
          color: $color-black-2;
        }
      }
    }
  }

  .container-title {
    display: flex;
    align-items: center;
    gap: 10px;

    .main-title {
      font-size: 16px;
      font-weight: 600;
      color: $color-black;
    }

    .info {
      font-size: 14px;
      font-weight: 400;
      color: $color-black-4;
    }
  }

  .schedules-container {
    margin: 20px 0;
    display: flex;
    align-items: center;
    gap: 10px;

    .schedule-card {
      width: 324px;
      height: 70px;
      border: 0.5px solid $color-black-4;
      border-radius: 8px;
      padding: 9px 2px 9px 12px;
      cursor: pointer;
      transition: all 0.2s ease;
      background-color: $color-white;
      display: flex;
      flex-direction: column;
      justify-content: space-between;

      &:hover {
        border-color: $color-black-3;
      }
    }

    .schedule-card.selected {
      border-color: $color-primary-strong;
      background-color: $color-primary-light;
    }

    .schedule-title-container {
      display: flex;
      justify-content: space-between;
      align-items: center;

      .schedule-title {
        font-size: 14px;
        color: $color-black-2;
        font-weight: 500;
      }

      .schedule-radio {
        :deep(.ant-radio) {
          .ant-radio-inner {
            width: 20px;
            height: 20px;
          }
        }
      }
    }

    .schedule-time-container {
      display: flex;
      align-items: center;
      gap: 8px;

      .clock-icon {
        color: $color-black-2;
        font-size: 14px;
      }

      .schedule-time-title {
        color: $color-black;
        font-size: 14px;
        font-weight: 600;
      }
    }

    .separator {
      border: 1px solid #c5c5c5;
    }
  }

  /* Estilos para el card de inclusiones */
  .inclusions-card {
    border: 1px solid #e4e5e6;
    border-radius: 8px;
    width: 100%;

    :deep(.ant-card-body) {
      padding: 0;
    }
  }

  .inclusions-section {
    margin-top: 0;
    border-radius: 8px;
  }

  .inclusions-header {
    display: grid;
    grid-template-columns: 1fr 150px 150px 80px;
    gap: 24px;
    background-color: #e6ebf2;
    color: #575757;
    padding: 18px 24px;
  }

  .inclusions-header .header-col {
    font-size: 14px;
    line-height: 24px;
    font-weight: 500;
    color: #575757;
  }

  .inclusions-rows {
    background-color: #ffffff;
    color: #575b5f;
  }

  .inclusions-row {
    display: grid;
    grid-template-columns: 1fr 150px 150px 80px;
    gap: 24px;
    padding: 12px 24px;
    border-bottom: 1px solid #f5f5f5;
    align-items: center;
  }

  .inclusions-row:last-child {
    border-bottom: none;
  }

  .inclusion-incluye {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    color: #575b5f;
  }

  .inclusion-status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
    flex-shrink: 0;

    &.included {
      background-color: #00a15b;
    }

    &.not-included {
      background-color: #575b5f;
    }
  }

  /* Estilos para el card de requisitos */
  .requirements-card {
    border: 1px solid #e4e5e6;
    border-radius: 8px;
    width: 100%;

    :deep(.ant-card-body) {
      padding: 0;
    }
  }

  .requirements-section {
    margin-top: 0;
    border-radius: 8px;
  }

  .requirements-header {
    display: grid;
    grid-template-columns: 1fr 200px 100px;
    gap: 24px;
    background-color: #e6ebf2;
    color: #575757;
    padding: 20px 24px;
    // border-radius: 8px 8px 0 0;
  }

  .requirements-header .header-col {
    font-size: 14px;
    line-height: 24px;
    font-weight: 500;
    color: #575757;
  }

  .requirements-rows {
    background-color: #ffffff;
    color: #575b5f;
  }

  .requirements-row {
    display: grid;
    grid-template-columns: 1fr 200px 100px;
    gap: 24px;
    padding: 12px 24px;
    border-bottom: 1px solid #f5f5f5;
    align-items: center;
  }

  .requirements-row:last-child {
    border-bottom: none;
  }

  .service-content-actions {
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

  .marketing-alert {
    margin-top: 10px;
    margin-bottom: 25px;
    background-color: #fffbdb;
    border: 1px solid #ffcc00;
    border-radius: 4px;

    :deep(.ant-alert-content) {
      padding-right: 20px;
    }

    :deep(.ant-alert-message) {
      color: #000000;
      font-size: 14px;
      line-height: 22px;
      font-weight: 400;
    }

    :deep(.ant-alert-icon) {
      color: #faad14;
    }

    :deep(.ant-alert-close-icon) {
      color: #8c8c8c;

      &:hover {
        color: #595959;
      }
    }
  }

  .warning-message {
    font-size: 14px;
    line-height: 20px;
    color: #2f353a;
    font-weight: 400;
  }

  .mb-25 {
    margin-bottom: 25px;
  }

  .ml-10 {
    margin-left: 10px;
  }

  .service-content-form-container {
    background-color: #ffffff;
  }

  .form-content {
    margin-top: 24px;
  }

  :deep(.ant-switch.ant-switch-checked) {
    background-color: $color-primary-strong !important;
  }
</style>
