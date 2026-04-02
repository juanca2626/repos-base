<template>
  <div class="information-commercial-schedule-component">
    <div class="container">
      <EmptyStateFormGlobalComponent
        v-if="
          isEditMode && !getShowFormComponent(FormComponentEnum.COMMERCIAL_INFORMATION) && !spinning
        "
        title="Información comercial"
        :formSpecific="FormComponentEnum.COMMERCIAL_INFORMATION"
      />

      <!-- ✅ Spinner centrado cuando no hay formulario pero sí está cargando -->
      <div
        v-else-if="!getShowFormComponent(FormComponentEnum.COMMERCIAL_INFORMATION) && spinning"
        class="loading-container"
      >
        <SpinGlobalComponent :spinning="true" />
      </div>

      <!-- ✅ Formulario / Resumen -->
      <div v-else>
        <div class="title-form">
          <div>Información comercial</div>
          <div
            v-if="getIsEditFormComponent(FormComponentEnum.COMMERCIAL_INFORMATION)"
            class="edit-form"
            @click="handleShowForm"
          >
            Editar <font-awesome-icon :icon="['fas', 'pen-to-square']" />
          </div>
        </div>

        <SpinGlobalComponent :tip="spinTip" :spinning="spinning">
          <!-- MODO LECTURA -->
          <template v-if="getIsEditFormComponent(FormComponentEnum.COMMERCIAL_INFORMATION)">
            <div class="list-items-form">
              <div class="container-item">
                <div class="bullet-point"></div>
                <div class="title-item">Administración:</div>
                <div class="value-item">
                  {{ formState.administration === 'public' ? 'Público' : 'Privado' }}
                </div>
              </div>

              <template v-if="formState.scheduleType === 1">
                <div class="container-item">
                  <div class="bullet-point"></div>
                  <div class="title-item">Todos los días:</div>
                  <div class="value-item">
                    <span v-for="(schedule, sIndex) in formState.scheduleGeneral" :key="sIndex">
                      <template v-if="schedule.open && schedule.close">
                        {{ schedule.open }} - {{ schedule.close }}
                        <span
                          v-if="
                            sIndex <
                            formState.scheduleGeneral.filter((s: any) => s.open && s.close).length -
                              1
                          "
                          >,
                        </span>
                      </template>
                    </span>
                  </div>
                </div>
              </template>

              <template v-else>
                <div class="container-item">
                  <div class="bullet-point"></div>
                  <div class="title-item">Horario de apertura/cierre:</div>
                  <div class="value-item"></div>
                </div>
                <div
                  v-for="item in formState.schedule"
                  :key="item.day"
                  class="container-item schedule-item"
                  v-show="item.available_day"
                >
                  <div class="bullet-point sub-bullet"></div>
                  <div class="title-item schedule-day">{{ item.label }}:</div>
                  <div class="value-item">
                    <span v-for="(schedule, sIndex) in item.schedules" :key="sIndex">
                      {{ schedule.open }} - {{ schedule.close }}
                      <span v-if="sIndex < item.schedules.length - 1">, </span>
                    </span>
                  </div>
                </div>
              </template>
            </div>
          </template>

          <!-- MODO EDICIÓN -->
          <template v-else>
            <a-form ref="formRef">
              <!-- Administración -->
              <a-row>
                <a-col :span="12">
                  <a-form-item>
                    <template #label>
                      <div class="form-label">Administración</div>
                    </template>
                    <a-radio-group v-model:value="formState.administration">
                      <a-radio value="public">Público</a-radio>
                      <a-radio value="private">Privado</a-radio>
                    </a-radio-group>
                  </a-form-item>
                </a-col>
              </a-row>

              <a-form-item name="schedule">
                <template #label>
                  <div class="form-label">Horarios</div>
                </template>
                <a-form-item-rest>
                  <div style="margin: 0 0 1rem 0">
                    <a-radio-group v-model:value="formState.scheduleType" name="radioGroup">
                      <a-radio :value="1">Todos los días</a-radio>
                      <a-radio :value="2">Personalizado</a-radio>
                    </a-radio-group>
                  </div>

                  <template v-if="formState.scheduleType == 1">
                    <div
                      v-for="(schedule, scheduleIndex) in formState.scheduleGeneral"
                      :key="scheduleIndex"
                      class="schedule-row"
                    >
                      <div class="schedule-time">
                        <div>
                          <a-input
                            v-model:value="schedule.open"
                            style="width: 120px"
                            placeholder="00:00"
                            maxlength="5"
                            @click="
                              (e: Event) =>
                                handleTimeInputClick(
                                  e,
                                  'scheduleGeneral',
                                  'open',
                                  undefined,
                                  undefined,
                                  scheduleIndex
                                )
                            "
                            @focus="
                              (e: Event) =>
                                handleTimeInputFocus(
                                  e,
                                  'scheduleGeneral',
                                  'open',
                                  undefined,
                                  undefined,
                                  scheduleIndex
                                )
                            "
                            @input="
                              (e: Event) =>
                                handleTimeInputChange(
                                  e,
                                  'scheduleGeneral',
                                  'open',
                                  undefined,
                                  undefined,
                                  scheduleIndex
                                )
                            "
                            @blur="
                              (e: Event) =>
                                handleTimeInputBlur(
                                  e,
                                  'scheduleGeneral',
                                  'open',
                                  undefined,
                                  undefined,
                                  scheduleIndex
                                )
                            "
                            @keydown="handleTimeKeyDown"
                          />
                        </div>
                      </div>
                      <div class="schedule-time">
                        <div>a</div>
                        <div>
                          <a-input
                            v-model:value="schedule.close"
                            style="width: 120px"
                            placeholder="00:00"
                            maxlength="5"
                            @click="
                              (e: Event) =>
                                handleTimeInputClick(
                                  e,
                                  'scheduleGeneral',
                                  'close',
                                  undefined,
                                  undefined,
                                  scheduleIndex
                                )
                            "
                            @focus="
                              (e: Event) =>
                                handleTimeInputFocus(
                                  e,
                                  'scheduleGeneral',
                                  'close',
                                  undefined,
                                  undefined,
                                  scheduleIndex
                                )
                            "
                            @input="
                              (e: Event) =>
                                handleTimeInputChange(
                                  e,
                                  'scheduleGeneral',
                                  'close',
                                  undefined,
                                  undefined,
                                  scheduleIndex
                                )
                            "
                            @blur="
                              (e: Event) =>
                                handleTimeInputBlur(
                                  e,
                                  'scheduleGeneral',
                                  'close',
                                  undefined,
                                  undefined,
                                  scheduleIndex
                                )
                            "
                            @keydown="handleTimeKeyDown"
                          />
                        </div>
                      </div>
                      <div class="schedule-actions">
                        <div
                          style="height: 24px; cursor: pointer"
                          @click="handleAddGeneralSchedule"
                        >
                          <svg
                            width="24"
                            height="24"
                            viewBox="0 0 24 24"
                            fill="none"
                            xmlns="http://www.w3.org/2000/svg"
                          >
                            <path
                              d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z"
                              stroke="#2F353A"
                              stroke-width="2"
                              stroke-linecap="round"
                              stroke-linejoin="round"
                            />
                            <path
                              d="M12 8V16"
                              stroke="#2F353A"
                              stroke-width="2"
                              stroke-linecap="round"
                              stroke-linejoin="round"
                            />
                            <path
                              d="M8 12H16"
                              stroke="#2F353A"
                              stroke-width="2"
                              stroke-linecap="round"
                              stroke-linejoin="round"
                            />
                          </svg>
                        </div>

                        <!-- Botón para eliminar horario (solo cuando hay múltiples) -->
                        <div
                          v-if="formState.scheduleGeneral.length > 1"
                          style="height: 24px; cursor: pointer"
                          @click="handleRemoveGeneralSchedule(scheduleIndex)"
                        >
                          <svg
                            width="24"
                            height="24"
                            viewBox="0 0 24 24"
                            fill="none"
                            xmlns="http://www.w3.org/2000/svg"
                          >
                            <path
                              d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z"
                              stroke="#2F353A"
                              stroke-width="2"
                              stroke-linecap="round"
                              stroke-linejoin="round"
                            />
                            <path
                              d="M4.92969 4.92969L19.0697 19.0697"
                              stroke="#2F353A"
                              stroke-width="2"
                              stroke-linecap="round"
                              stroke-linejoin="round"
                            />
                          </svg>
                        </div>
                      </div>
                    </div>
                  </template>

                  <template v-else>
                    <template v-for="(item, dayIndex) in formState.schedule" :key="dayIndex">
                      <template v-if="item.available_day">
                        <div
                          v-for="(schedule, scheduleIndex) in item.schedules"
                          :key="`${dayIndex}-${scheduleIndex}`"
                          class="schedule-row"
                        >
                          <div style="width: 2rem">
                            {{ scheduleIndex === 0 ? item.label : '' }}
                          </div>
                          <div class="schedule-time">
                            <div>
                              <a-input
                                v-model:value="schedule.open"
                                style="width: 120px"
                                placeholder="00:00"
                                maxlength="5"
                                @click="
                                  (e: Event) =>
                                    handleTimeInputClick(
                                      e,
                                      'custom',
                                      'open',
                                      dayIndex,
                                      scheduleIndex
                                    )
                                "
                                @focus="
                                  (e: Event) =>
                                    handleTimeInputFocus(
                                      e,
                                      'custom',
                                      'open',
                                      dayIndex,
                                      scheduleIndex
                                    )
                                "
                                @input="
                                  (e: Event) =>
                                    handleTimeInputChange(
                                      e,
                                      'custom',
                                      'open',
                                      dayIndex,
                                      scheduleIndex
                                    )
                                "
                                @blur="
                                  (e: Event) =>
                                    handleTimeInputBlur(
                                      e,
                                      'custom',
                                      'open',
                                      dayIndex,
                                      scheduleIndex
                                    )
                                "
                                @keydown="handleTimeKeyDown"
                              />
                            </div>
                          </div>
                          <div class="schedule-time">
                            <div>a</div>
                            <div>
                              <a-input
                                v-model:value="schedule.close"
                                style="width: 120px"
                                placeholder="00:00"
                                maxlength="5"
                                @click="
                                  (e: Event) =>
                                    handleTimeInputClick(
                                      e,
                                      'custom',
                                      'close',
                                      dayIndex,
                                      scheduleIndex
                                    )
                                "
                                @focus="
                                  (e: Event) =>
                                    handleTimeInputFocus(
                                      e,
                                      'custom',
                                      'close',
                                      dayIndex,
                                      scheduleIndex
                                    )
                                "
                                @input="
                                  (e: Event) =>
                                    handleTimeInputChange(
                                      e,
                                      'custom',
                                      'close',
                                      dayIndex,
                                      scheduleIndex
                                    )
                                "
                                @blur="
                                  (e: Event) =>
                                    handleTimeInputBlur(
                                      e,
                                      'custom',
                                      'close',
                                      dayIndex,
                                      scheduleIndex
                                    )
                                "
                                @keydown="handleTimeKeyDown"
                              />
                            </div>
                          </div>
                          <div class="schedule-actions">
                            <div
                              style="height: 24px; cursor: pointer"
                              @click="handleAddSchedule(dayIndex)"
                            >
                              <svg
                                width="24"
                                height="24"
                                viewBox="0 0 24 24"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                              >
                                <path
                                  d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z"
                                  stroke="#2F353A"
                                  stroke-width="2"
                                  stroke-linecap="round"
                                  stroke-linejoin="round"
                                />
                                <path
                                  d="M12 8V16"
                                  stroke="#2F353A"
                                  stroke-width="2"
                                  stroke-linecap="round"
                                  stroke-linejoin="round"
                                />
                                <path
                                  d="M8 12H16"
                                  stroke="#2F353A"
                                  stroke-width="2"
                                  stroke-linecap="round"
                                  stroke-linejoin="round"
                                />
                              </svg>
                            </div>

                            <!-- Botón para eliminar horario (solo cuando hay múltiples) -->
                            <div
                              v-if="item.schedules.length > 1"
                              style="height: 24px; cursor: pointer"
                              @click="handleRemoveSchedule(dayIndex, scheduleIndex)"
                            >
                              <svg
                                width="24"
                                height="24"
                                viewBox="0 0 24 24"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                              >
                                <path
                                  d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z"
                                  stroke="#2F353A"
                                  stroke-width="2"
                                  stroke-linecap="round"
                                  stroke-linejoin="round"
                                />
                                <path
                                  d="M4.92969 4.92969L19.0697 19.0697"
                                  stroke="#2F353A"
                                  stroke-width="2"
                                  stroke-linecap="round"
                                  stroke-linejoin="round"
                                />
                              </svg>
                            </div>

                            <!-- Botón para marcar día como no disponible (solo cuando hay UN solo horario) -->
                            <div
                              v-if="item.schedules.length === 1"
                              style="height: 24px; cursor: pointer"
                              @click="handleToggleAvailableDay(dayIndex)"
                            >
                              <svg
                                width="24"
                                height="24"
                                viewBox="0 0 24 24"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                              >
                                <path
                                  d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z"
                                  stroke="#2F353A"
                                  stroke-width="2"
                                  stroke-linecap="round"
                                  stroke-linejoin="round"
                                />
                                <path
                                  d="M4.92969 4.92969L19.0697 19.0697"
                                  stroke="#2F353A"
                                  stroke-width="2"
                                  stroke-linecap="round"
                                  stroke-linejoin="round"
                                />
                              </svg>
                            </div>
                          </div>
                        </div>
                      </template>

                      <template v-else>
                        <div class="schedule-row">
                          <div style="width: 2rem">{{ item.label }}</div>
                          <div style="color: #2f353a; font-weight: 500">No disponible</div>
                          <div class="schedule-actions">
                            <div
                              style="height: 24px; cursor: pointer"
                              @click="handleToggleAvailableDay(dayIndex)"
                            >
                              <svg
                                width="24"
                                height="24"
                                viewBox="0 0 24 24"
                                fill="none"
                                xmlns="http://www.w3.org/2000/svg"
                              >
                                <path
                                  d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z"
                                  stroke="#2F353A"
                                  stroke-width="2"
                                  stroke-linecap="round"
                                  stroke-linejoin="round"
                                />
                                <path
                                  d="M12 8V16"
                                  stroke="#2F353A"
                                  stroke-width="2"
                                  stroke-linecap="round"
                                  stroke-linejoin="round"
                                />
                                <path
                                  d="M8 12H16"
                                  stroke="#2F353A"
                                  stroke-width="2"
                                  stroke-linecap="round"
                                  stroke-linejoin="round"
                                />
                              </svg>
                            </div>
                          </div>
                        </div>
                      </template>
                    </template>
                  </template>
                </a-form-item-rest>
              </a-form-item>

              <!-- BOTONES -->
              <div class="options-button">
                <a-button @click="handleClose">Cancelar</a-button>
                <a-button
                  type="primary"
                  :disabled="!getIsFormValid || isLoadingButton"
                  :loading="isLoadingButton"
                  @click="handleSave"
                >
                  Guardar datos
                </a-button>
              </div>
            </a-form>
          </template>
        </SpinGlobalComponent>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
  import EmptyStateFormGlobalComponent from '@/modules/negotiations/supplier-new/components/global/empty-state-form-global-component.vue';
  import SpinGlobalComponent from '@/modules/negotiations/supplier-new/components/global/spin-global-component.vue';
  import { FormComponentEnum } from '@/modules/negotiations/supplier-new/enums/form-component.enum';
  import { useCommercialScheduleComposable } from '@/modules/negotiations/supplier-new/composables/form/negotiations/supplier-registration/supplier-commercial-schedule.composable';

  defineOptions({ name: 'CommercialScheduleComponent' });

  const {
    // estado / refs
    formRef,
    formState,
    isLoadingButton,
    spinTip,
    spinning,
    isEditMode,

    // helpers UI
    getShowFormComponent,
    getIsEditFormComponent,
    getIsFormValid,

    // acciones
    handleAddGeneralSchedule,
    handleRemoveGeneralSchedule,
    handleAddSchedule,
    handleRemoveSchedule,
    handleToggleAvailableDay,
    handleSave,
    handleClose,
    handleShowForm,

    // Nuevas funciones para entrada manual de tiempo
    handleTimeInputChange,
    handleTimeInputBlur,
    handleTimeInputFocus,
    handleTimeInputClick,
    handleTimeKeyDown,
  } = useCommercialScheduleComposable();
</script>

<style lang="scss">
  @import '@/scss/_variables.scss';

  .information-commercial-schedule-component {
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
        color: $color-blue;
        cursor: pointer;
      }
    }

    .container {
      margin-top: 20px;
    }

    .list-items-form {
      .container-item {
        display: flex;
        align-items: center;
        margin-bottom: 5px;
        padding-left: 25px;

        .bullet-point {
          width: 5px;
          height: 5px;
          background-color: black;
          border-radius: 50%;
          margin-right: 12px;
          flex-shrink: 0;

          &.sub-bullet {
            width: 5px;
            height: 5px;
            background-color: black;
            margin-left: 18px;
          }
        }

        .title-item {
          width: auto;
          font-weight: 600;
          color: #7e8285;
        }

        .value-item {
          flex: 1;
          font-weight: 400;
          color: #7e8285;
          margin-left: 8px;
        }

        &.schedule-item {
          .schedule-day {
            width: 60px;
            padding-left: 0;
            margin-left: 0;
          }
        }
      }
    }

    .day-row {
      display: flex;
      margin-bottom: 12px;

      .day-label {
        width: 50px;
        font-weight: 500;
        color: $color-black-2;
        padding-top: 6px;
      }

      .slot-row {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;

        .svg-button {
          cursor: pointer;
          flex-shrink: 0;
        }
      }
    }

    .schedule-row {
      display: flex;
      gap: 1rem;
      align-items: center;
      margin-bottom: 1rem;
    }

    .schedule-time {
      display: flex;
      gap: 1rem;
      align-items: center;
    }

    .schedule-actions {
      display: flex;
      gap: 0.5rem;
      align-items: center;
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

    .ant-form-item {
      margin-bottom: 10px !important;
    }

    .ant-form-item-required::before,
    .ant-form-item-required::after {
      display: none !important;
    }

    .options-button {
      display: flex;
      gap: 1rem;

      .ant-btn-default {
        width: 118px;
        height: 48px;
        font-weight: 600;
        font-size: 16px;
        line-height: 24px;
        padding: 0 24px;
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
        font-weight: 600;
        font-size: 16px;
        line-height: 24px;
        padding: 0 24px;
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

  .loading-container {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 200px;
    width: 100%;
    padding: 20px;
  }
</style>
