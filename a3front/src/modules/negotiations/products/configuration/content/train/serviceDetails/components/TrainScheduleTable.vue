<template>
  <div class="train-schedule-table">
    <div class="table-header">
      <div class="header-cell frequency">Frecuencia</div>
      <div class="header-cell fare-type">Tipo de tarifa</div>
      <div class="header-cell time">H. inicio</div>
      <div class="header-cell time">H. fin</div>
      <div class="header-cell duration">Duración</div>
      <div class="header-cell days">Días de recorrido</div>
      <div class="header-cell actions">Acciones</div>
    </div>

    <div class="table-body">
      <template v-for="schedule in schedules" :key="schedule.id">
        <div class="table-row">
          <div class="body-cell frequency">
            <a-input v-model:value="schedule.frequency" />
          </div>

          <div class="body-cell fare-type">
            <a-select v-model:value="schedule.fareType" style="width: 100%">
              <a-select-option
                v-for="option in fareTypeOptions"
                :key="option.value"
                :value="option.value"
              >
                {{ option.label }}
              </a-select-option>
            </a-select>
          </div>

          <div class="body-cell time">
            <a-input
              v-model:value="schedule.startTime"
              @blur="handleTimeBlur(schedule, 'startTime')"
            />
          </div>

          <div class="body-cell time">
            <a-input v-model:value="schedule.endTime" @blur="handleTimeBlur(schedule, 'endTime')" />
          </div>

          <div class="body-cell duration">
            <a-input :value="getCalculatedDuration(schedule)" disabled />
          </div>

          <div class="body-cell days">
            <a-select
              v-model:value="schedule.daysOfWeek"
              mode="multiple"
              :max-tag-count="2"
              placeholder="Seleccionar días"
              style="width: 100%"
              :listHeight="500"
              popupClassName="days-select-dropdown"
              :getPopupContainer="(trigger: any) => trigger.parentNode"
            >
              <a-select-option v-for="day in daysOptions" :key="day.value" :value="day.value">
                <div class="custom-option">
                  <a-checkbox :checked="schedule.daysOfWeek?.includes(day.value)" />
                  <span class="option-label">{{ day.label }}</span>
                </div>
              </a-select-option>
              <template #maxTagPlaceholder="omittedValues">
                <span>+{{ omittedValues.length }}</span>
              </template>
            </a-select>
          </div>

          <div class="body-cell actions">
            <div class="action-buttons">
              <a-button type="link" class="action-btn" @click="handleAddValidity(schedule.id)">
                <svg
                  width="21"
                  height="25"
                  viewBox="0 0 21 25"
                  fill="none"
                  xmlns="http://www.w3.org/2000/svg"
                >
                  <g clip-path="url(#clip0_11455_138750)">
                    <path
                      d="M10.5 11.7891C11.1234 11.7891 11.625 12.2906 11.625 12.9141V15.1641H13.875C14.4984 15.1641 15 15.6656 15 16.2891C15 16.9125 14.4984 17.4141 13.875 17.4141H11.625V19.6641C11.625 20.2875 11.1234 20.7891 10.5 20.7891C9.87656 20.7891 9.375 20.2875 9.375 19.6641V17.4141H7.125C6.50156 17.4141 6 16.9125 6 16.2891C6 15.6656 6.50156 15.1641 7.125 15.1641H9.375V12.9141C9.375 12.2906 9.87656 11.7891 10.5 11.7891ZM7.125 3.91406H13.875V2.03906C13.875 1.41797 14.3766 0.914062 15 0.914062C15.6234 0.914062 16.125 1.41797 16.125 2.03906V3.91406H18C19.6547 3.91406 21 5.25703 21 6.91406V21.9141C21 23.5688 19.6547 24.9141 18 24.9141H3C1.34297 24.9141 0 23.5688 0 21.9141V6.91406C0 5.25703 1.34297 3.91406 3 3.91406H4.875V2.03906C4.875 1.41797 5.37656 0.914062 6 0.914062C6.62344 0.914062 7.125 1.41797 7.125 2.03906V3.91406ZM2.25 21.9141C2.25 22.3266 2.58562 22.6641 3 22.6641H18C18.4125 22.6641 18.75 22.3266 18.75 21.9141V9.91406H2.25V21.9141Z"
                      fill="#1284ED"
                    />
                  </g>
                  <defs>
                    <clipPath id="clip0_11455_138750">
                      <rect width="21" height="24" fill="white" transform="translate(0 0.914062)" />
                    </clipPath>
                  </defs>
                </svg>
              </a-button>
              <a-button type="link" class="action-btn add-btn" @click="emit('add')">
                <svg
                  width="24"
                  height="25"
                  viewBox="0 0 24 25"
                  fill="none"
                  xmlns="http://www.w3.org/2000/svg"
                >
                  <path
                    d="M12 22.9141C17.5228 22.9141 22 18.4369 22 12.9141C22 7.39121 17.5228 2.91406 12 2.91406C6.47715 2.91406 2 7.39121 2 12.9141C2 18.4369 6.47715 22.9141 12 22.9141Z"
                    stroke="#1284ED"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                  <path
                    d="M12 8.91406V16.9141"
                    stroke="#1284ED"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                  <path
                    d="M8 12.9141H16"
                    stroke="#1284ED"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                </svg>
              </a-button>
              <a-button
                v-if="schedules.length > 1"
                type="link"
                class="action-btn"
                @click="handleRemoveSchedule(schedule.id)"
              >
                <svg
                  width="24"
                  height="24"
                  viewBox="0 0 24 24"
                  fill="none"
                  xmlns="http://www.w3.org/2000/svg"
                >
                  <path
                    d="M3 6H5H21"
                    stroke="#1284ED"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                  <path
                    d="M19 6V20C19 20.5304 18.7893 21.0391 18.4142 21.4142C18.0391 21.7893 17.5304 22 17 22H7C6.46957 22 5.96086 21.7893 5.58579 21.4142C5.21071 21.0391 5 20.5304 5 20V6M8 6V4C8 3.46957 8.21071 2.96086 8.58579 2.58579C8.96086 2.21071 9.46957 2 10 2H14C14.5304 2 15.0391 2.21071 15.4142 2.58579C15.7893 2.96086 16 3.46957 16 4V6"
                    stroke="#1284ED"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                  <path
                    d="M10 11V17"
                    stroke="#1284ED"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                  <path
                    d="M14 11V17"
                    stroke="#1284ED"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                </svg>
              </a-button>
            </div>
          </div>
        </div>

        <!-- Filas de vigencia -->
        <div
          v-for="(validity, index) in validityRows[schedule.id] || []"
          :key="`${schedule.id}-validity-${index}`"
          class="validity-row"
        >
          <div class="validity-content">
            <span class="validity-label">Periodo de vigencia</span>
            <div class="validity-fields">
              <div
                class="date-field"
                :class="{ 'has-error': hasValidityError(schedule.id, index) }"
              >
                <label>Desde:</label>
                <a-date-picker
                  v-model:value="validity.startDate"
                  format="DD/MM/YYYY"
                  placeholder="DD/MM/AAAA"
                  :getPopupContainer="(trigger: any) => trigger.parentNode"
                  :disabledDate="getDisabledDates(schedule.id, index, 'startDate')"
                  @change="updateValidityValidation(schedule.id, index)"
                />
              </div>
              <div
                class="date-field"
                :class="{ 'has-error': hasValidityError(schedule.id, index) }"
              >
                <label>Hasta:</label>
                <a-date-picker
                  v-model:value="validity.endDate"
                  format="DD/MM/YYYY"
                  placeholder="DD/MM/AAAA"
                  :getPopupContainer="(trigger: any) => trigger.parentNode"
                  :disabledDate="getDisabledDates(schedule.id, index, 'endDate')"
                  @change="updateValidityValidation(schedule.id, index)"
                />
              </div>
              <div class="validity-actions">
                <a-button
                  v-if="(validityRows[schedule.id] || []).length > 1"
                  type="link"
                  class="remove-validity-btn"
                  @click="handleRemoveValidity(schedule.id, index)"
                >
                  <svg
                    width="24"
                    height="24"
                    viewBox="0 0 24 24"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                  >
                    <path
                      d="M3 6H5H21"
                      stroke="#1284ED"
                      stroke-width="2"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                    />
                    <path
                      d="M19 6V20C19 20.5304 18.7893 21.0391 18.4142 21.4142C18.0391 21.7893 17.5304 22 17 22H7C6.46957 22 5.96086 21.7893 5.58579 21.4142C5.21071 21.0391 5 20.5304 5 20V6M8 6V4C8 3.46957 8.21071 2.96086 8.58579 2.58579C8.96086 2.21071 9.46957 2 10 2H14C14.5304 2 15.0391 2.21071 15.4142 2.58579C15.7893 2.96086 16 3.46957 16 4V6"
                      stroke="#1284ED"
                      stroke-width="2"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                    />
                    <path
                      d="M10 11V17"
                      stroke="#1284ED"
                      stroke-width="2"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                    />
                    <path
                      d="M14 11V17"
                      stroke="#1284ED"
                      stroke-width="2"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                    />
                  </svg>
                </a-button>
                <a-button
                  type="link"
                  class="add-validity-btn"
                  @click="handleAddValidity(schedule.id)"
                >
                  <svg
                    width="17"
                    height="19"
                    viewBox="0 0 17 19"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                  >
                    <path
                      d="M16 9.41406C16 10.0094 15.4833 10.4913 14.8462 10.4913H9.65385V15.3375C9.65385 15.9328 9.13714 16.4141 8.5 16.4141C7.86286 16.4141 7.34615 15.9328 7.34615 15.3375V10.4913H2.15385C1.51671 10.4913 1 10.0094 1 9.41406C1 8.81873 1.51671 8.33748 2.15385 8.33748H7.34615V3.49132C7.34615 2.89599 7.86286 2.41406 8.5 2.41406C9.13714 2.41406 9.65385 2.89599 9.65385 3.49132V8.33748H14.8462C15.4844 8.33714 16 8.81839 16 9.41406Z"
                      fill="#1284ED"
                    />
                  </svg>
                  Vigencia adicional
                </a-button>
              </div>
            </div>
          </div>
        </div>
      </template>
    </div>
  </div>
</template>

<script setup lang="ts">
  import { computed } from 'vue';
  import type { TrainSchedule } from '../interfaces/train-service.interface';
  import { useTrainScheduleTable } from '../composables/useTrainScheduleTable';

  interface Props {
    schedules: TrainSchedule[];
    currentKey?: string;
    currentCode?: string;
  }

  const props = defineProps<Props>();

  const emit = defineEmits<{
    (e: 'add'): void;
    (e: 'remove', id: string): void;
    (e: 'duplicate', schedule: TrainSchedule): void;
  }>();

  // Cada componente maneja su propio composable
  const schedulesRef = computed(() => props.schedules);
  const {
    validityRows,
    daysOptions,
    fareTypeOptions,
    handleAddValidity,
    handleRemoveValidity,
    getCalculatedDuration,
    handleTimeBlur,
    getDisabledDates,
    hasValidityError,
    updateValidityValidation,
    initializeValidityRowsFromData,
  } = useTrainScheduleTable(schedulesRef, props.currentKey, props.currentCode);

  // Exponer validityRows y la función de inicialización para que el padre pueda accederlos
  defineExpose({
    validityRows,
    initializeValidityRowsFromData,
  });

  const handleRemoveSchedule = (id: string) => {
    emit('remove', id);
  };
</script>

<style scoped lang="scss">
  .train-schedule-table {
    margin: 24px 0;
    background-color: #fafafa;
    border-radius: 8px;

    .table-header {
      display: grid;
      grid-template-columns: 100px 150px 100px 100px 100px 1fr 100px;
      gap: 12px;
      padding: 12px 16px;
      background-color: #e6ebf2;
      border-radius: 4px;
      margin-bottom: 8px;

      .header-cell {
        font-weight: 600;
        font-size: 13px;
        line-height: 20px;
        color: #2f353a;
        display: flex;
        align-items: center;
      }
    }

    .table-body {
      .table-row {
        display: grid;
        grid-template-columns: 100px 150px 100px 100px 100px 1fr 100px;
        gap: 12px;
        padding: 12px 16px;
        background-color: #ffffff;
        border-radius: 4px;
        margin-bottom: 0;
        align-items: center;

        .body-cell {
          display: flex;
          align-items: center;

          :deep(.ant-input),
          :deep(.ant-picker),
          :deep(.ant-select) {
            width: 100%;
            border-radius: 4px;

            &:focus,
            &:hover {
              border-color: #bd0d12;
              box-shadow: none;
            }
          }

          :deep(.ant-input) {
            border: 1px solid #d9d9d9;
          }

          :deep(.ant-input:disabled) {
            background-color: #f5f5f5;
            color: #7e8285;
            cursor: not-allowed;
          }

          :deep(.ant-select-selector) {
            border: 1px solid #d9d9d9 !important;
            border-radius: 4px !important;

            &:focus,
            &:hover {
              border-color: #bd0d12 !important;
              box-shadow: none !important;
            }
          }

          :deep(.ant-select-focused .ant-select-selector) {
            border-color: #bd0d12 !important;
            box-shadow: none !important;
          }

          &.days {
            position: relative;
            overflow: visible;

            :deep(.ant-select-selection-item) {
              background-color: #dcdcdc !important;
              border: 1px solid #d9d9d9 !important;
              border-radius: 4px !important;
              font-size: 14px !important;
              font-weight: 400 !important;
              color: #2f353a !important;
            }

            :deep(.ant-select-selection-overflow-item) {
              margin-right: 4px !important;
            }

            :deep(.ant-select-selection-overflow-item-suffix) {
              .ant-select-selection-item {
                background-color: #dcdcdc !important;
                border: 1px solid #d9d9d9 !important;
                border-radius: 4px !important;
                font-size: 14px !important;
                font-weight: 400 !important;
                color: #2f353a !important;
              }
            }

            :deep(.ant-select-selection-item) {
              .ant-checkbox-wrapper {
                display: none;
              }
            }

            :deep(.ant-select-selection-item-remove) {
              color: #2f353a !important;
              font-size: 12px !important;

              &:hover {
                color: none;
              }
            }
          }

          &.actions {
            .action-buttons {
              display: flex;
              gap: 10px;
              align-items: center;

              .action-btn {
                padding: 0;
                color: #1284ed;
                font-size: 18px;
                display: flex;
                align-items: center;
                justify-content: center;
                min-width: auto;

                &:hover {
                  color: none;
                }

                &.add-btn {
                  color: #1284ed;

                  &:hover {
                    color: none;
                  }
                }
              }
            }
          }
        }
      }

      .validity-row {
        background-color: #f2f7fa;
        border-radius: 4px;
        margin-bottom: 8px;
        margin-top: 0;
        height: 94px;
        padding: 13px 19px;

        display: flex;
        align-items: center;
        justify-content: center;

        .validity-content {
          display: flex;
          align-items: center;
          gap: 24px;
          flex: 1;

          .validity-label {
            align-self: flex-start;
            margin-top: -12px;

            font-size: 14px;
            font-weight: 400;
            color: #2f353a;
            white-space: nowrap;
          }

          .validity-fields {
            display: flex;
            align-items: center;
            gap: 16px;
            flex: 1;

            .date-field {
              display: flex;
              flex-direction: column;
              align-items: flex-start;
              gap: 8px;

              label {
                font-size: 14px;
                font-weight: 400;
                color: #2f353a;
                white-space: nowrap;
              }

              :deep(.ant-picker) {
                min-width: 200px;
                border: 1px solid #d9d9d9;
                border-radius: 4px;

                &:hover {
                  border-color: #bd0d12;
                }

                &:focus,
                &.ant-picker-focused {
                  border-color: #bd0d12;
                  box-shadow: none;
                }
              }

              &.has-error {
                :deep(.ant-picker) {
                  border-color: #ff4d4f !important;

                  &:hover {
                    border-color: #ff4d4f !important;
                  }

                  &:focus,
                  &.ant-picker-focused {
                    border-color: #ff4d4f !important;
                    box-shadow: 0 0 0 2px rgba(255, 77, 79, 0.2) !important;
                  }
                }

                label {
                  color: #ff4d4f;
                }
              }
            }

            .validity-actions {
              display: flex;
              align-items: center;
              gap: 12px;
              align-self: flex-end;
            }

            .remove-validity-btn {
              display: flex;
              align-items: center;
              justify-content: center;
              padding: 0;
              min-width: auto;
              height: auto;

              &:hover {
                opacity: 0.8;
              }

              svg {
                width: 16px;
                height: 16px;
              }
            }

            .add-validity-btn {
              display: flex;
              align-items: center;
              color: #1284ed;
              line-height: 20px;
              font-size: 14px !important;
              font-weight: 500;
              padding: 0;

              &:hover {
                color: #1284ed;
              }

              :deep(.anticon) {
                font-size: 14px;
              }
            }
          }
        }
      }
    }
  }
</style>

<style lang="scss">
  .days-select-dropdown {
    position: absolute !important;
    z-index: 1050;

    .rc-virtual-list {
      max-height: none !important;
      height: auto !important;
      overflow: hidden !important;
      overflow-y: hidden !important;
    }

    .rc-virtual-list-holder {
      max-height: none !important;
      height: auto !important;
      overflow: hidden !important;
      overflow-y: hidden !important;
    }

    .rc-virtual-list-holder-inner {
      max-height: none !important;
      height: auto !important;
    }

    .rc-virtual-list-scrollbar {
      display: none !important;
      width: 0 !important;
    }

    &::-webkit-scrollbar {
      display: none !important;
      width: 0 !important;
      height: 0 !important;
    }

    .rc-virtual-list::-webkit-scrollbar,
    .rc-virtual-list-holder::-webkit-scrollbar {
      display: none !important;
      width: 0 !important;
      height: 0 !important;
    }

    .ant-select-item-option-content {
      padding: 0 !important;

      .custom-option {
        display: flex;
        align-items: center;
        gap: 12px;
        width: 100%;
        padding: 8px 12px;
        cursor: pointer;

        .option-label {
          font-size: 14px;
          line-height: 22px;
          color: #2f353a;
          user-select: none;
          flex: 1;
        }

        .ant-checkbox {
          margin: 0;
        }

        .ant-checkbox-wrapper {
          margin: 0;
        }

        .ant-checkbox-inner {
          width: 16px;
          height: 16px;
          border: 1px solid #d9d9d9;
          border-radius: 2px;
        }

        .ant-checkbox-checked .ant-checkbox-inner {
          background-color: #bd0d12;
          border-color: #bd0d12;
        }

        .ant-checkbox-checked .ant-checkbox-inner::after {
          border-color: #ffffff;
        }
      }
    }

    .ant-select-item {
      padding: 0 !important;
    }

    .ant-select-item-option-active {
      background-color: #f5f5f5 !important;
    }

    .ant-select-item-option-selected {
      background-color: #ffffff !important;
      font-weight: 400 !important;
    }

    .ant-select-item-option-state {
      display: none !important;
    }
  }
</style>
