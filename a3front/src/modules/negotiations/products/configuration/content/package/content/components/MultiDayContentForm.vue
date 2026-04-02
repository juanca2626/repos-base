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
            v-for="(schedule, index) in schedulesWithActivities"
            :key="schedule.id ?? index"
            type="button"
            class="operability-read-tab"
            :class="{ 'operability-read-tab--selected': schedule.selected }"
            @click="schedule.id != null && selectSchedule(schedule.id)"
          >
            {{ schedule.label }}
          </button>
        </div>
        <template v-if="selectedSchedule">
          <div class="operability-read-schedule">
            <div class="operability-read-schedule-item">
              <ClockCircleOutlined class="operability-read-icon" />
              <span class="operability-read-label">Horario de salida:</span>
              <span class="operability-read-badge">{{
                selectedSchedule.applyAllDay
                  ? '24 hrs'
                  : `${selectedSchedule.start || ''} - ${selectedSchedule.end || ''}`
              }}</span>
            </div>
            <div class="operability-read-schedule-item">
              <IconCalendarCheck :height="16" :width="16" class="operability-read-icon" />
              <span class="operability-read-label">Duración:</span>
              <span class="operability-read-duration">{{ totalDuration || '00:00' }} horas</span>
            </div>
          </div>
          <div class="operability-read-details">
            <div
              v-for="dayGroup in activitiesGroupedByDay"
              :key="dayGroup.numberDay"
              class="operability-read-day-group"
            >
              <div class="operability-read-day-title">Día {{ dayGroup.numberDay }}</div>
              <div
                v-for="(activity, activityIndex) in dayGroup.activities"
                :key="`${dayGroup.numberDay}-${activityIndex}`"
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
          </div>
        </template>
      </div>

      <div class="content-read-grid">
        <template v-for="textTypeCode in formState.textTypeId">
          <template v-if="textTypeCode === 'ITINERARY'">
            <ContentReadTextTypeByDayCard
              :key="textTypeCode"
              :title="getLabelFromTextTypes(textTypeCode) ?? ''"
              :status="getTextTypeStatus(String(textTypeCode))"
              :days="getTextTypeDays(String(textTypeCode))"
            />
          </template>
          <template v-else>
            <ContentReadTextTypeCard
              :key="textTypeCode"
              :title="getLabelFromTextTypes(textTypeCode) ?? ''"
              :status="getTextTypeStatus(String(textTypeCode))"
              :excerpt="getTextTypeExcerpt(String(textTypeCode))"
              :full-html="getTextTypeHtml(String(textTypeCode))"
              :is-expanded="expandedReadCardKey === String(textTypeCode)"
              @toggle="toggleReadCard(String(textTypeCode))"
            />
          </template>
        </template>
      </div>
    </ReadModeComponent>

    <!-- Modo edición -->
    <div v-else class="edit-mode-container">
      <WizardHeaderComponent
        title="Contenido"
        :completed="completedFields"
        :total="totalFieldsCompleted"
      />

      <div class="container-title">
        <span class="main-title"> Operatividad del servicio </span>
        <span class="info"> configura la duración del servicio </span>
      </div>

      <div class="schedules-wrapper">
        <button v-if="canGoPrevious" class="chevron-button chevron-left" @click="goToPreviousPage">
          <IconChevronLeft />
        </button>
        <div class="schedules-container" :style="{ '--items-per-page': ITEMS_PER_PAGE }">
          <template v-for="(schedule, _index) in visibleSchedules" :key="schedule.id as string">
            <div
              class="schedule-card"
              :class="{ selected: schedule.selected }"
              @click="selectSchedule(schedule.id as string)"
            >
              <div class="schedule-title-container">
                <span class="schedule-title">{{ schedule.label }}</span>
                <a-radio class="schedule-radio" v-model:checked="schedule.selected"></a-radio>
              </div>
              <div class="schedule-time-container">
                <ClockCircleOutlined class="clock-icon" />
                <span class="schedule-time-title">{{
                  schedule.applyAllDay
                    ? '24 hrs'
                    : schedule.singleTime
                      ? schedule.start
                      : `${schedule.start} - ${schedule.end}`
                }}</span>
              </div>
            </div>
          </template>
        </div>
        <button v-if="canGoNext" class="chevron-button chevron-right" @click="goToNextPage">
          <IconChevronRight />
        </button>
      </div>

      <div class="days-container">
        <div
          class="card-day mt-3"
          v-for="(dayGroup, groupIndex) in activitiesGroupedByDay"
          :key="dayGroup.numberDay"
        >
          <div class="card-header">
            <div class="card-title-container">
              <IconCalendarCheck :height="18" :width="18" />
              <span class="card-title"> Día {{ dayGroup.numberDay }} </span>
            </div>
            <div class="card-header-actions">
              <div v-if="!isFirstSchedule" class="card-copy-container">
                <a-dropdown :trigger="['click']" placement="bottomLeft">
                  <a-button
                    class="copy-operability-btn"
                    :disabled="getCopyScheduleOptions(dayGroup.numberDay).length === 0"
                  >
                    <IconCopy class="copy-icon" />
                    <span>Copiar operatividad</span>
                  </a-button>
                  <template #overlay>
                    <a-menu>
                      <a-menu-item
                        v-for="option in getCopyScheduleOptions(dayGroup.numberDay)"
                        :key="option.value"
                        @click="copyActivitiesFromSchedule(option.value)"
                      >
                        {{ option.label }}
                      </a-menu-item>
                    </a-menu>
                  </template>
                </a-dropdown>
              </div>

              <a-button
                type="link"
                class="delete-day-button"
                @click="handleDeleteDay(dayGroup.numberDay)"
              >
                <font-awesome-icon :icon="['far', 'trash-can']" class="trash-icon" />
              </a-button>

              <div v-if="groupIndex === activitiesGroupedByDay.length - 1">
                <div class="add-day-button" @click="handleAddDay(dayGroup.numberDay)">
                  <font-awesome-icon :icon="['fas', 'plus']" />
                  <span class="add-day-button-text"> Agregar día adicional </span>
                </div>
              </div>
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
              <div
                v-for="(activity, activityIndexInDay) in dayGroup.activities"
                :key="getGlobalActivityIndex(Number(groupIndex), Number(activityIndexInDay))"
                class="schedule-row day-schedule-grid-cols"
              >
                <div class="duration-input-wrapper">
                  <a-input-group compact>
                    <a-input
                      :value="activity.duration"
                      placeholder="00:00"
                      @blur="
                        (e: any) =>
                          handleDurationChange(
                            typeof e === 'string' ? e : e.target?.value || '',
                            getGlobalActivityIndex(Number(groupIndex), Number(activityIndexInDay))
                          )
                      "
                      @input="
                        (e: any) => {
                          const value = typeof e === 'string' ? e : e.target?.value || '';
                          if (value) {
                            handleDurationChange(
                              value,
                              getGlobalActivityIndex(Number(groupIndex), Number(activityIndexInDay))
                            );
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
                        <a-menu
                          @click="
                            (e: any) =>
                              handleDurationChange(
                                e.key,
                                getGlobalActivityIndex(
                                  Number(groupIndex),
                                  Number(activityIndexInDay)
                                )
                              )
                          "
                        >
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
                    @click="
                      handleAddSchedule(
                        getGlobalActivityIndex(Number(groupIndex), Number(activityIndexInDay)),
                        dayGroup.numberDay
                      )
                    "
                  />
                  <IconActionButtonComponent
                    v-if="dayGroup.activities.length > 1"
                    action-type="delete_v2"
                    @click="
                      handleDeleteSchedule(
                        getGlobalActivityIndex(Number(groupIndex), Number(activityIndexInDay))
                      )
                    "
                  />
                </div>
              </div>
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
          <template v-if="textTypeId === 'ITINERARY'">
            <a-card size="small" class="card-text-type" :class="{ 'mt-4': Number(index) > 0 }">
              <template #title>
                <div class="card-text-type-title">
                  {{ getLabelFromTextTypes(textTypeId) }}
                </div>
              </template>
              <template #extra>
                <a-button
                  type="text"
                  class="card-text-type-action"
                  :loading="isSendingReview(String(textTypeId))"
                  :disabled="
                    !getTextTypeDays(String(textTypeId)).some((d) => !!d.html) ||
                    isSendingReview(String(textTypeId))
                  "
                  @click="handleSendForReview(String(textTypeId))"
                >
                  Enviar a revisión
                  <IconArrowRight />
                </a-button>
              </template>

              <template
                v-for="(row, dayIndex) in formState.days"
                :key="`${textTypeId}-day-${row.id}`"
              >
                <div class="editor-container" :class="{ 'mt-5': Number(dayIndex) > 0 }">
                  <div class="editor-wrapper">
                    <div class="editor-label">
                      <IconCalendarCheck :height="18" :width="18" />
                      <span class="editor-title"> Día {{ row.id }} </span>
                    </div>
                  </div>
                  <EditorQuillComponent
                    class="custom-editor"
                    :model-value="getTextTypeHtml(String(textTypeId), row.id)"
                    @update:model-value="
                      (v: string) => updateTextTypeHtml(String(textTypeId), row.id, v)
                    "
                  />
                </div>
              </template>
            </a-card>
          </template>
          <template v-else>
            <a-card size="small" class="card-text-type" :class="{ 'mt-4': Number(index) > 0 }">
              <template #title>
                <div class="card-text-type-title">
                  {{ getLabelFromTextTypes(textTypeId) }}
                </div>
              </template>
              <template #extra>
                <a-button
                  type="text"
                  class="card-text-type-action"
                  :loading="isSendingReview(String(textTypeId))"
                  :disabled="
                    !getTextTypeHtml(String(textTypeId)) || isSendingReview(String(textTypeId))
                  "
                  @click="handleSendForReview(String(textTypeId))"
                >
                  Enviar a revisión
                  <IconArrowRight />
                </a-button>
              </template>

              <div class="editor-container editor-container--no-toolbar-padding mt-5">
                <EditorQuillComponent
                  class="custom-editor"
                  :model-value="getTextTypeHtml(String(textTypeId))"
                  @update:model-value="(v: string) => updateTextTypeHtml(String(textTypeId), 1, v)"
                />
              </div>
            </a-card>
          </template>
        </template>
      </div>

      <div class="mt-3 mb-4">
        <a-button
          size="large"
          type="primary"
          class="button-action-primary-strong btn-save"
          :disabled="isLoadingButton || totalFieldsCompleted !== completedFields"
          @click="handleSaveAndAdvance"
        >
          Guardar datos
        </a-button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
  import WizardHeaderComponent from '@/modules/negotiations/products/configuration/components/WizardHeaderComponent.vue';
  import { ClockCircleOutlined } from '@ant-design/icons-vue';
  import IconCalendarCheck from '@/modules/negotiations/products/configuration/icons/IconCalendarCheck.vue';
  import ReadModeComponent from '@/modules/negotiations/products/configuration/content/shared/components/ReadModeComponent.vue';
  import ContentReadTextTypeByDayCard from '@/modules/negotiations/products/configuration/content/shared/components/content/ContentReadTextTypeByDayCard.vue';
  import IconActionButtonComponent from '@/modules/negotiations/products/configuration/components/IconActionButtonComponent.vue';
  import EditorQuillComponent from '@/modules/negotiations/products/configuration/components/EditorQuillComponent.vue';
  import IconArrowRight from '@/modules/negotiations/products/configuration/icons/IconArrowRight.vue';
  import IconChevronLeft from '@/modules/negotiations/products/configuration/content/shared/icons/IconChevronLeft.vue';
  import IconChevronRight from '@/modules/negotiations/products/configuration/content/shared/icons/IconChevronRight.vue';
  import IconCopy from '@/modules/negotiations/products/configuration/icons/IconCopy.vue';
  import { DownOutlined } from '@ant-design/icons-vue';
  import { useMultiDayContentForm } from '../composables/useMultiDayContentForm';
  import IconWarningAlert from '@/modules/negotiations/products/configuration/icons/IconWarningAlert.vue';
  import ContentReadTextTypeCard from '@/modules/negotiations/products/configuration/content/shared/components/content/ContentReadTextTypeCard.vue';

  interface Props {
    currentKey: string;
    currentCode: string;
  }

  const props = defineProps<Props>();

  const {
    isEditingContent,
    handleEditMode,
    totalFieldsCompleted,
    isLoadingButton,
    formState,
    activities,
    completedFields,
    textTypes,
    handleChangeTextType,
    selectSchedule,
    handleAddDay,
    handleDeleteDay,
    handleAddSchedule,
    handleDeleteSchedule,
    isSelectedTextType,
    filterOption,
    getLabelFromTextTypes,
    handleSaveAndAdvance,

    ITEMS_PER_PAGE,
    visibleSchedules,
    canGoPrevious,
    canGoNext,
    goToPreviousPage,
    goToNextPage,
    handleDurationChange,

    quickDurationOptions,
    getCopyScheduleOptions,
    copyActivitiesFromSchedule,

    isFirstSchedule,
    activitiesGroupedByDay,
    getGlobalActivityIndex,
    getTextTypeHtml,
    getTextTypeExcerpt,
    getTextTypeStatus,
    getTextTypeDays,
    updateTextTypeHtml,
    showMarketingAlert,

    selectedSchedule,
    schedulesWithActivities,
    operabilitySummaryText,
    totalDuration,
    getActivityLabel,

    expandedReadCardKey,
    toggleReadCard,
    isSendingReview,
    handleSendForReview,
  } = useMultiDayContentForm({
    currentKey: props.currentKey,
    currentCode: props.currentCode,
  });
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
    gap: 20px;
    padding: 10px 0px;
  }

  .operability-read-day-group {
    display: flex;
    flex-direction: column;
    gap: 10px;
  }

  .operability-read-day-title {
    font-size: 14px;
    font-weight: 600;
    color: #2f353a;
    margin-bottom: 4px;
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
    grid-template-columns: repeat(1, 1fr);
    gap: 16px;
    margin-top: 16px;
  }

  .read-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
    margin-top: 16px;
  }

  .read-item-label {
    font-size: 14px;
    font-weight: 600;
    color: #7e8285;
  }

  .read-item-value {
    font-size: 14px;
    color: #2f353a;
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
      left: 15px;
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
      padding-left: 80px;
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

  .editor-container--no-toolbar-padding .custom-editor {
    :deep(.ql-toolbar) {
      padding-left: 0;
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

  .days-container {
    margin-top: 10px;

    .card-day {
      border-radius: 8px;
      border: 0.5px solid $color-black-4;
    }

    .card-body {
      padding: 14px 0px;

      .day-schedule-grid-cols {
        grid-template-columns: 120px 1fr 200px 100px;
        gap: 24px;
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

      .card-header-actions {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-left: auto;
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
</style>
