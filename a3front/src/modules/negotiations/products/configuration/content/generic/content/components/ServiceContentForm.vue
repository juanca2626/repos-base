<template>
  <div class="service-content-form-container">
    <!-- Modo lectura -->
    <ReadModeComponent v-if="!isEditingContent" title="Contenido" @edit="handleEditMode">
      <div v-if="formState.schedules?.length" class="operability-read-card">
        <div class="operability-read-header">
          <span class="operability-read-bullet" />
          <span class="operability-read-title">Operatividad del servicio:</span>
          <span class="operability-read-summary">{{ operabilitySummaryText }}</span>
        </div>
        <div class="operability-read-divider" />
        <div class="operability-read-tabs">
          <button
            v-for="schedule in schedulesWithActivities"
            :key="schedule.id"
            type="button"
            class="operability-read-tab"
            :class="{ 'operability-read-tab--selected': schedule.selected }"
            @click="selectSchedule(schedule.id)"
          >
            {{ schedule.label }}
          </button>
        </div>
        <template v-if="selectedSchedule">
          <div class="operability-read-schedule">
            <div class="operability-read-schedule-item">
              <ClockCircleOutlined class="operability-read-icon" />
              <span class="operability-read-label">Horario de salida:</span>
              <span class="operability-read-badge">{{ getTimeSchedule(selectedSchedule) }}</span>
            </div>
            <div class="operability-read-schedule-item">
              <IconCalendarCheck :height="16" :width="16" class="operability-read-icon" />
              <span class="operability-read-label">Duración:</span>
              <span class="operability-read-duration">{{ totalDuration || '00:00' }} horas</span>
            </div>
          </div>
          <div class="operability-read-details">
            <div
              v-for="(activity, index) in selectedSchedule.activities"
              :key="index"
              class="operability-read-detail-row"
            >
              <span class="operability-read-detail-time">{{
                activity.duration ? activity.duration : '--:--'
              }}</span>
              <span class="operability-read-detail-label">{{
                getActivityLabel(activity.activityId) || '—'
              }}</span>
            </div>
          </div>
        </template>
      </div>

      <div class="content-read-grid">
        <ContentReadTextTypeCard
          v-for="textTypeId in formState.textTypeId"
          :key="textTypeId"
          :title="getlabelFromTextTypes(textTypeId) ?? ''"
          :status="getTextTypeStatus(textTypeId)"
          :excerpt="getTextTypeExcerpt(textTypeId)"
          :full-html="getTextTypeHtml(textTypeId)"
          :is-expanded="isTextTypeExpanded(textTypeId)"
          expand-link-text="Ver itinerario completo"
          @toggle="toggleTextTypeExpanded(textTypeId)"
        />
      </div>
      <div class="content-read-grid mb-4">
        <ContentReadInclusionsCard :items="inclusionReadItems" />
        <ContentReadRequirementsCard :items="requirementReadItems" />
      </div>
    </ReadModeComponent>

    <!-- Modo edición -->
    <div v-else class="edit-mode-container mb-4">
      <WizardHeaderComponent title="Contenido" :completed="completedFields" :total="totalFields" />

      <div class="container-title">
        <span class="main-title"> Operatividad del servicio </span>
        <span class="info"> configura la duración del servicio </span>
      </div>
      <div class="schedules-wrapper">
        <button v-if="canGoPrevious" class="chevron-button chevron-left" @click="goToPreviousPage">
          <IconChevronLeft />
        </button>
        <div class="schedules-container" :style="{ '--items-per-page': ITEMS_PER_PAGE }">
          <template v-for="(schedule, _index) in visibleSchedules" :key="schedule.id">
            <div
              class="schedule-card"
              :class="{ selected: schedule.selected }"
              @click="selectSchedule(schedule.id)"
            >
              <div class="schedule-title-container">
                <span class="schedule-title">{{ schedule.label }}</span>
                <a-radio class="schedule-radio" v-model:checked="schedule.selected"></a-radio>
              </div>
              <div class="schedule-time-container">
                <ClockCircleOutlined class="clock-icon" />
                <span class="schedule-time-title">{{ getTimeSchedule(schedule) }}</span>
              </div>
            </div>
          </template>
        </div>
        <button v-if="canGoNext" class="chevron-button chevron-right" @click="goToNextPage">
          <IconChevronRight />
        </button>
      </div>

      <div class="days-container">
        <div class="card-day mt-3">
          <div class="card-header">
            <div class="card-title-container">
              <IconCalendarCheck :height="18" :width="18" />
              <span class="card-title">
                Duración total del servicio {{ totalDuration || '00:00' }}</span
              >
            </div>
            <div v-if="!isFirstSchedule" class="card-copy-container">
              <a-dropdown :trigger="['click']" placement="bottomLeft">
                <a-button class="copy-operability-btn" :disabled="copyScheduleOptions.length === 0">
                  <IconCopy class="copy-icon" />
                  <span>Copiar operatividad</span>
                </a-button>
                <template #overlay>
                  <a-menu @click="(e: any) => copyActivitiesFromSchedule(e.key)">
                    <a-menu-item v-for="option in copyScheduleOptions" :key="option.value">
                      {{ option.label }}
                    </a-menu-item>
                  </a-menu>
                </template>
              </a-dropdown>
            </div>
          </div>
          <div class="card-body">
            <div class="day-schedules-header day-schedule-grid-cols">
              <div>
                <span class="header-col-title"> Duración </span>
              </div>
              <div>
                <span class="header-col-title"> Actividad a realizar </span>
              </div>
              <div class="text-align-center">
                <span class="header-col-title"> Horario calculado </span>
              </div>
              <div class="text-align-center actions-col">
                <span class="header-col-title"> Acciones </span>
              </div>
            </div>

            <div class="schedule-rows">
              <template v-for="(activity, index) in selectedSchedule?.activities">
                <div class="schedule-row day-schedule-grid-cols">
                  <div class="duration-input-wrapper">
                    <a-input-group compact>
                      <a-input
                        :value="activity.duration"
                        placeholder="00:00"
                        @blur="
                          (e: any) =>
                            handleDurationChange(
                              typeof e === 'string' ? e : e.target?.value || '',
                              Number(index)
                            )
                        "
                        @input="
                          (e: any) => {
                            const value = typeof e === 'string' ? e : e.target?.value || '';
                            if (value) {
                              handleDurationChange(value, Number(index));
                            }
                          }
                        "
                        class="duration-input"
                      />
                      <a-dropdown :trigger="['click']" placement="bottomRight">
                        <a-button class="duration-dropdown-btn">
                          <DownOutlined :style="{ fontSize: '11px' }" />
                        </a-button>
                        <template #overlay>
                          <a-menu @click="(e: any) => handleDurationChange(e.key, Number(index))">
                            <a-menu-item v-for="option in quickDurationOptions" :key="option">
                              {{ option }}
                            </a-menu-item>
                          </a-menu>
                        </template>
                      </a-dropdown>
                    </a-input-group>
                  </div>

                  <div>
                    <a-select
                      v-model:value="activity.activityId"
                      placeholder="Selecciona"
                      :suffixIcon="null"
                      :options="activities"
                      class="w-100"
                      show-search
                    />
                  </div>

                  <div class="calculated-schedule-col">
                    <ClockCircleOutlined class="clock-icon-small" />
                    <span class="text">{{ activity?.calculatedSchedule || '00:00 / 00:00' }}</span>
                  </div>

                  <div class="actions-col">
                    <IconActionButtonComponent
                      action-type="add"
                      @click="handleAddSchedule(Number(index))"
                    />
                    <IconActionButtonComponent
                      v-if="
                        selectedSchedule?.activities?.length &&
                        selectedSchedule.activities.length > 1
                      "
                      action-type="delete_v2"
                      @click="handleDeleteSchedule(Number(index))"
                    />
                  </div>
                </div>
              </template>
            </div>
          </div>
        </div>
      </div>

      <div class="container-title mt-4">
        <span class="main-title"> Texto </span>
        <span class="info"> Informaciones dirigidas al cliente </span>
      </div>

      <div class="mt-4">
        <a-form layout="vertical">
          <a-row :gutter="8">
            <a-col :span="24" :lg="12">
              <a-form-item name="textTypeId">
                <template #label>
                  <div class="text-type-container">
                    <span>Tipo de texto</span>
                    <a-tooltip
                      placement="topLeft"
                      :overlayInnerStyle="{ width: '420px', padding: '8px 12px' }"
                    >
                      <template #title>
                        <span class="title-tooltip-info">
                          Una vez complete cada texto envíelo a revisión por Marketing
                        </span>
                      </template>
                      <font-awesome-icon :icon="['fa', 'circle-info']" />
                    </a-tooltip>
                  </div>
                </template>
                <a-select
                  placeholder="Selecciona"
                  class="custom-select-multiple"
                  mode="multiple"
                  v-model:value="formState.textTypeId"
                  :options="textTypes"
                  @change="handleChangeTextType"
                  :filter-option="filterOption"
                >
                  <template #maxTagPlaceholder="omittedValues">
                    <span>+ {{ omittedValues.length }} ...</span>
                  </template>
                  <template #option="{ value, label }">
                    <div class="custom-options-select-multiple">
                      <font-awesome-icon
                        :class="[
                          isSelectedTextType(value)
                            ? 'icon-color-selected'
                            : 'icon-color-not-selected',
                        ]"
                        :icon="[
                          isSelectedTextType(value) ? 'fas' : 'far',
                          isSelectedTextType(value) ? 'square-check' : 'square',
                        ]"
                        size="xl"
                      />
                      <span>
                        {{ label }}
                      </span>
                    </div>
                  </template>
                  <template #tagRender="{ label, onClose }">
                    <a-tag class="tag-close-option" closable @close="onClose">
                      <span>
                        {{ label }}
                      </span>
                    </a-tag>
                  </template>
                  <template #menuItemSelectedIcon />
                </a-select>
              </a-form-item>
            </a-col>
          </a-row>
        </a-form>
      </div>

      <div class="mt-2" v-if="formState.textTypeId.length > 0 && showMarketingAlert">
        <a-alert
          type="warning"
          class="marketing-alert"
          :closable="true"
          @close="showMarketingAlert = false"
        >
          <template #message>
            <div class="container-alert-icon">
              <IconWarningAlert />
              <span>
                Una vez complete cada texto envíelo al área de Marketing para que lo revise y
                completar el proceso. Si no es enviado a revisión, al guardar la sección
                "Contenido", se enviarán todos automáticamente
              </span>
            </div>
          </template>
        </a-alert>
      </div>

      <div>
        <template v-for="(textTypeId, index) in formState.textTypeId">
          <a-card size="small" class="card-text-type" :class="{ 'mt-4': Number(index) > 0 }">
            <template #title>
              <div class="card-text-type-title">
                {{ getlabelFromTextTypes(textTypeId) }}
              </div>
            </template>
            <template #extra>
              <a-button
                type="text"
                class="card-text-type-action"
                :loading="isSendingReview(textTypeId)"
                :disabled="!getTextTypeHtml(textTypeId) || isSendingReview(textTypeId)"
                @click="handleSendForReview(textTypeId)"
              >
                Enviar a revisión
                <IconArrowRight />
              </a-button>
            </template>
            <div class="editor-container mt-5">
              <EditorQuillComponent
                class="custom-editor"
                :model-value="getTextTypeHtml(textTypeId)"
                @update:model-value="updateTextTypeHtml(textTypeId, $event)"
              />
            </div>
          </a-card>
        </template>
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
                  >
                    {{ option.label }}
                  </a-select-option>
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
                >
                  {{ option.label }}
                </a-select-option>
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
          @click="handleSaveAndAdvance"
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
  import { ref } from 'vue';
  import WizardHeaderComponent from '@/modules/negotiations/products/configuration/components/WizardHeaderComponent.vue';
  import { ClockCircleOutlined, DownOutlined } from '@ant-design/icons-vue';
  import IconCalendarCheck from '@/modules/negotiations/products/configuration/icons/IconCalendarCheck.vue';
  import ReadModeComponent from '@/modules/negotiations/products/configuration/content/shared/components/ReadModeComponent.vue';
  import ContentReadTextTypeCard from '@/modules/negotiations/products/configuration/content/shared/components/content/ContentReadTextTypeCard.vue';
  import ContentReadInclusionsCard from '@/modules/negotiations/products/configuration/content/shared/components/content/ContentReadInclusionsCard.vue';
  import ContentReadRequirementsCard from '@/modules/negotiations/products/configuration/content/shared/components/content/ContentReadRequirementsCard.vue';
  import ModalEditComponent from '@/modules/negotiations/products/configuration/components/ModalEditComponent.vue';
  import IconActionButtonComponent from '@/modules/negotiations/products/configuration/components/IconActionButtonComponent.vue';
  import EditorQuillComponent from '@/modules/negotiations/products/configuration/components/EditorQuillComponent.vue';
  import IconArrowRight from '@/modules/negotiations/products/configuration/icons/IconArrowRight.vue';
  import { useServiceContentForm } from '../composables/useServiceContentForm';
  import IconChevronLeft from '@/modules/negotiations/products/configuration/content/shared/icons/IconChevronLeft.vue';
  import IconChevronRight from '@/modules/negotiations/products/configuration/content/shared/icons/IconChevronRight.vue';
  import IconCopy from '@/modules/negotiations/products/configuration/icons/IconCopy.vue';
  import IconWarningAlert from '@/modules/negotiations/products/configuration/icons/IconWarningAlert.vue';

  interface Props {
    currentKey: string;
    currentCode: string;
  }

  const props = defineProps<Props>();

  const expandedTextTypeIds = ref<number[]>([]);

  const isTextTypeExpanded = (textTypeId: number) => expandedTextTypeIds.value.includes(textTypeId);

  const toggleTextTypeExpanded = (textTypeId: number) => {
    const ids = expandedTextTypeIds.value;
    const i = ids.indexOf(textTypeId);
    if (i >= 0) {
      expandedTextTypeIds.value = ids.filter((id) => id !== textTypeId);
    } else {
      expandedTextTypeIds.value = [...ids, textTypeId];
    }
  };

  const {
    totalFields,
    isFormValid,
    isLoadingButton,
    formState,
    activities,
    completedFields,
    textTypes,
    requirements,
    inclusions,
    inclusionsWithoutId,
    inclusionOptions,
    requirementOptions,
    selectedSchedule,
    schedulesWithActivities,

    addInclusion,
    removeInclusion,
    addRequirement,
    removeRequirement,
    handleChangeTextType,
    selectSchedule,
    handleAddSchedule,
    handleDeleteSchedule,
    isSelectedTextType,
    filterOption,
    getlabelFromTextTypes,
    getTextTypeHtml,
    getTextTypeExcerpt,
    getTextTypeStatus,
    updateTextTypeHtml,
    inclusionReadItems,
    requirementReadItems,
    handleSaveAndAdvance,
    handleSendForReview,
    isSendingReview,

    isEditingContent,
    showEditModal,
    handleEditMode,
    handleConfirmEdit,
    handleCancelEdit,

    ITEMS_PER_PAGE,
    visibleSchedules,
    canGoPrevious,
    canGoNext,
    goToPreviousPage,
    goToNextPage,
    handleDurationChange,
    totalDuration,
    quickDurationOptions,
    operabilitySummaryText,
    getActivityLabel,
    copyScheduleOptions,
    copyActivitiesFromSchedule,
    isFirstSchedule,
    showMarketingAlert,

    getTimeSchedule,
  } = useServiceContentForm(props);
</script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';
  @import '@/scss/components/negotiations/_supplierForm.scss';

  .operability-read-card {
    background-color: $color-white;
    border-radius: 8px;
    border: 0.5px solid #e7e7e7;
    padding: 16px 20px;
    margin-bottom: 16px;
  }

  .operability-read-header {
    display: flex;
    flex-wrap: wrap;
    align-items: baseline;
    gap: 6px;
    margin-bottom: 0;
    padding: 10px;
  }

  .operability-read-divider {
    height: 1px;
    background-color: #e7e7e7;
    margin: 12px -20px 0;
    width: calc(100% + 40px);
  }

  .operability-read-bullet {
    display: inline-block;
    width: 4px;
    height: 4px;
    border-radius: 50%;
    background-color: #7e8285;
    flex-shrink: 0;
    margin-top: 6px;
    align-self: flex-start;
  }

  .operability-read-title {
    color: #7e8285;
    font-size: 14px;
    font-weight: 600;
  }

  .operability-read-summary {
    color: #7e8285;
    font-size: 14px;
    font-weight: 400;
  }

  .operability-read-tabs {
    display: flex;
    align-items: center;
    gap: 0;
    border-bottom: 1px solid $color-black-4;
    margin: 0 -20px 12px;
    padding: 0 20px 0;
  }

  .operability-read-tab {
    padding: 18px 16px;
    background: none;
    border: none;
    cursor: pointer;
    color: #7e8285;
    font-size: 14px;
    font-weight: 500;
    position: relative;
    margin-bottom: -1px;

    &--selected {
      color: #2f353a;
      font-weight: 600;

      &::after {
        content: '';
        position: absolute;
        left: 0;
        right: 0;
        bottom: -1px;
        height: 2px;
        background-color: #2f353a;
      }
    }
  }

  .operability-read-schedule {
    background-color: #f5f5f5;
    border-radius: 4px;
    padding: 12px 25px;
    margin: 0 -20px 12px;
    min-height: 36px;
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    align-items: center;
    gap: 16px 24px;
  }

  .operability-read-schedule-item {
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .operability-read-icon {
    color: $color-black-3;
    flex-shrink: 0;
  }

  .operability-read-label {
    font-size: 14px;
    color: $color-black-3;
  }

  .operability-read-badge {
    font-size: 14px;
    background-color: $color-white;
    color: $color-black-2;
    font-weight: 600;
    height: 24px;
    display: flex;
    align-items: center;
    padding: 18px 10px;
    border-radius: 6px;
  }

  .operability-read-duration {
    font-size: 14px;
    font-weight: 600;
    color: $color-black;
  }

  .operability-read-details {
    display: flex;
    flex-direction: column;
    gap: 15px;
    padding: 10px 0px;
  }

  .operability-read-detail-row {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    color: $color-black-3;
  }

  .operability-read-detail-time {
    flex-shrink: 0;
    min-width: 40px;
    color: $color-black-3;
    font-weight: 500;
  }

  .operability-read-detail-label {
    flex: 1;
    font-weight: 600;
    color: #575b5f;
  }

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
    border-color: #e7e7e7;
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

  // Estilos globales para el dropdown de Ant Design
  :deep(.ant-select-dropdown) {
    .ant-select-item {
      display: flex !important;
      justify-content: flex-start !important;
      align-items: center !important;
      text-align: left !important;
      padding: 8px 12px !important;
      direction: ltr !important;

      &::before {
        display: none !important;
      }

      .ant-select-item-option-content {
        width: 100% !important;
        display: flex !important;
        justify-content: flex-start !important;
        align-items: center !important;
        text-align: left !important;
        margin: 0 !important;
        padding: 0 !important;
        direction: ltr !important;
      }
    }
  }

  .custom-options-select-multiple {
    display: flex;
    gap: 5px;
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
      border: 0.5px solid #e7e7e7;
    }

    .card-body {
      padding: 14px 0px;

      .day-schedule-grid-cols {
        grid-template-columns: 120px 1fr 150px 80px;
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

      .duration-input-wrapper {
        width: 100%;

        :deep(.ant-input-group) {
          display: flex;
          width: 100%;

          .duration-input {
            width: calc(100% - 40px);
            border-radius: 4px 0 0 4px;
            min-width: 80px;
            font-size: 14px;
            padding: 4px 11px;
          }

          .duration-dropdown-btn {
            width: 40px;
            padding: 0;
            border-radius: 0 4px 4px 0;
            border-left: none;
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 40px;
            color: $color-black-3;
          }
        }
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

      .card-copy-container {
        display: flex;
        align-items: center;
        gap: 12px;

        .copy-operability-btn {
          display: flex;
          align-items: center;
          gap: 8px;
          border: 0.5px solid $color-black-4;
          border-radius: 4px;
          background-color: $color-white;
          padding: 6px 12px;
          height: auto;
          color: $color-black-3;
          font-size: 14px;
          font-weight: 400;

          &:hover {
            border-color: $color-black-3;
            color: $color-black;
          }

          .copy-icon {
            font-size: 14px;
            color: $color-black-3;
          }

          span {
            color: $color-black-3;
          }

          :deep(.anticon) {
            display: none;
          }
        }
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

  .schedules-wrapper {
    margin: 20px 0;
    display: flex;
    align-items: center;
    gap: 10px;

    .chevron-button {
      flex-shrink: 0;
      width: 32px;
      height: 32px;
      display: flex;
      align-items: center;
      justify-content: center;
      border: none;
      border-radius: 4px;
      background-color: $color-white;
      cursor: pointer;
      transition: all 0.2s ease;
      padding: 0;

      &:hover:not(:disabled) {
        border-color: $color-black-3;
        background-color: $color-white-2;
      }

      &:active:not(:disabled) {
        background-color: $color-white-3;
      }

      &:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        background-color: $color-white-4;
        border-color: $color-black-5;
      }

      svg {
        width: 16px;
        height: 16px;
        color: $color-black-2;
      }

      &:disabled svg {
        color: $color-black-5;
      }
    }

    .schedules-container {
      flex: 1;
      display: grid;
      grid-template-columns: repeat(var(--items-per-page), 1fr);
      gap: 10px;
      justify-items: center;

      .schedule-card {
        width: 100%;
        max-width: 324px;
        height: 70px;
        border: 0.5px solid #e7e7e7;
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

        &.selected {
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
      }
    }
  }

  /* Estilos para el card de inclusiones */
  .inclusions-card {
    border: 1px solid #e4e5e6;
    border-radius: 8px;
    // overflow: hidden;
    width: 100%;

    :deep(.ant-card-body) {
      padding: 0;
    }
  }

  .inclusions-section {
    margin-top: 0;
    border-radius: 8px;
    // overflow: hidden;
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
    border: 1px solid #e7e7e7;
    border-radius: 8px;
    // overflow: hidden;
    width: 100%;

    :deep(.ant-card-body) {
      padding: 0;
    }
  }

  .requirements-section {
    margin-top: 0;
    border-radius: 8px;
    // overflow: hidden;
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

    .container-alert-icon {
      display: flex;
      align-items: flex-start;
      gap: 12px;

      svg {
        width: 24px;
        height: 24px;
        flex-shrink: 0;
        margin-top: 2px;
      }
    }

    :deep(.ant-alert-close-icon) {
      color: #bdbdbd !important;
      font-size: 18px;

      &:hover {
        color: #bdbdbd !important;
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

  .container-alert-icon {
    display: flex;
    align-items: flex-start;
    gap: 12px;

    svg {
      width: 24px;
      height: 24px;
      flex-shrink: 0;
      margin-top: 2px;
    }
  }
</style>

<style lang="css">
  /* Estilos globales para el dropdown de Ant Design (sin SASS para evitar errores de variables) */
  .ant-select-dropdown .ant-select-item {
    display: flex !important;
    justify-content: flex-start !important;
    align-items: center !important;
    text-align: left !important;
    direction: ltr !important;
  }

  .ant-select-dropdown .ant-select-item .ant-select-item-option-content {
    width: 100% !important;
    display: flex !important;
    justify-content: flex-start !important;
    align-items: center !important;
    text-align: left !important;
    margin: 0 !important;
    padding: 0 !important;
    direction: ltr !important;
  }

  :deep(.ant-switch.ant-switch-checked) {
    background-color: $color-primary-strong !important;
  }
</style>
