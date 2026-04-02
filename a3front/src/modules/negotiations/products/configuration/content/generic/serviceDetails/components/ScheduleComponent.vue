<template>
  <a-form-item name="schedule">
    <a-row :gutter="16" align="middle">
      <a-col :span="6">
        <span class="form-label">Rangos operativos:</span>
      </a-col>
      <a-col :span="18">
        <a-radio-group v-model:value="scheduleType" name="radioGroup">
          <a-radio :value="1">Todos los días</a-radio>
          <a-radio :value="2">Personalizado</a-radio>
        </a-radio-group>
      </a-col>
    </a-row>

    <div class="schedule-type-info">
      <div class="schedule-options-row">
        <div
          v-if="scheduleGeneral.length < 2 || isSelectingSingleTime"
          @click="
            !isSelectingTwentyFourHours && !isSelectingSingleTime && handleToggleTwentyFourHours()
          "
          :class="[
            'clickable-option',
            { disabled: isSelectingTwentyFourHours || isSelectingSingleTime },
          ]"
        >
          <svg
            width="16"
            height="19"
            viewBox="0 0 16 19"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              d="M15.5 7.66406V16.7266C15.5 17.3745 14.9637 17.9138 14.2861 17.9141H1.71387C1.03604 17.9138 0.5 17.3746 0.5 16.7266V7.66406H15.5ZM2.85742 8.91406C2.2722 8.91406 1.78613 9.38446 1.78613 9.97656V13.3516C1.78613 13.9437 2.2722 14.4141 2.85742 14.4141H6.28613C6.86892 14.4138 7.35742 13.9443 7.35742 13.3516V9.97656C7.35742 9.38379 6.86892 8.91429 6.28613 8.91406H2.85742ZM11.4287 0.914062C11.7918 0.914136 12.0713 1.20113 12.0713 1.53906V3.16406H14.2861C14.9637 3.16428 15.5 3.70331 15.5 4.35156V5.53906H0.5V4.35156C0.5 3.70324 1.03597 3.16428 1.71387 3.16406H3.92871V1.53906C3.92871 1.20113 4.20821 0.914136 4.57129 0.914062C4.93443 0.914062 5.21387 1.20109 5.21387 1.53906V3.16406H10.7861V1.53906C10.7861 1.20108 11.0656 0.914062 11.4287 0.914062Z"
              :fill="isSelectingTwentyFourHours || isSelectingSingleTime ? '#D9D9D9' : '#575B5F'"
              :stroke="isSelectingTwentyFourHours || isSelectingSingleTime ? '#D9D9D9' : '#575B5F'"
            />
          </svg>

          <span
            class="schedule-text-info"
            :class="{ 'disabled-text': isSelectingTwentyFourHours || isSelectingSingleTime }"
            >Aplicar 24hrs</span
          >
        </div>

        <div
          v-if="!isSelectingTwentyFourHours"
          @click="!isSelectingSingleTime && !isSelectingTwentyFourHours && handleToggleSingleTime()"
          :class="[
            'clickable-option',
            { disabled: isSelectingSingleTime || isSelectingTwentyFourHours },
          ]"
        >
          <svg
            width="15"
            height="19"
            viewBox="0 0 15 19"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
            <g clip-path="url(#clip0_12913_15398)">
              <path
                d="M7.5 0.914062C9.85965 0.914062 11.8213 2.9377 11.8213 5.47656V5.53906H10.6787V5.47656C10.6787 3.67251 9.27533 2.16406 7.5 2.16406C5.72467 2.16406 4.32129 3.67251 4.32129 5.47656V8.78906H12.8574C13.7358 8.78922 14.4998 9.54617 14.5 10.5703V16.1953C14.5 17.1485 13.7465 17.9139 12.8574 17.9141H2.14258C1.2582 17.9139 0.5 17.1524 0.5 16.1641V10.5391C0.5 9.54927 1.25795 8.78922 2.14258 8.78906H3.17871V5.47656C3.17871 2.9377 5.14035 0.914062 7.5 0.914062ZM7.5 11.1953C6.62792 11.1953 5.92871 11.896 5.92871 12.7891V13.9141C5.92871 14.7895 6.60982 15.5391 7.5 15.5391C8.39048 15.5391 9.07129 14.7881 9.07129 13.9141V12.7891C9.07129 11.8947 8.37177 11.1953 7.5 11.1953Z"
                :fill="isSelectingSingleTime || isSelectingTwentyFourHours ? '#D9D9D9' : '#575B5F'"
                :stroke="
                  isSelectingSingleTime || isSelectingTwentyFourHours ? '#D9D9D9' : '#575B5F'
                "
              />
            </g>
            <defs>
              <clipPath id="clip0_12913_15398">
                <rect width="15" height="18" fill="white" transform="translate(0 0.414062)" />
              </clipPath>
            </defs>
          </svg>

          <span
            class="schedule-text-info"
            :class="{ 'disabled-text': isSelectingSingleTime || isSelectingTwentyFourHours }"
            >Hora única</span
          >
        </div>

        <a-button
          v-if="isSelectingTwentyFourHours"
          @click="handleSaveTwentyFourHours"
          class="save-button"
        >
          Guardar
        </a-button>

        <a-button v-if="isSelectingSingleTime" @click="handleSaveSingleTime" class="save-button">
          Guardar
        </a-button>
      </div>
    </div>

    <a-form-item-rest>
      <div v-if="isSelectingTwentyFourHours || isSelectingSingleTime" class="select-all-container">
        <a-checkbox
          :checked="isSelectingTwentyFourHours ? allDaysSelected24Hours : allDaysSelectedSingleTime"
          @change="
            (checked: boolean) =>
              isSelectingTwentyFourHours
                ? handleSelectAll24Hours(checked)
                : handleSelectAllSingleTime(checked)
          "
        >
          <span class="select-all-text">Seleccionar todos</span>
        </a-checkbox>
      </div>
      <div class="schedules-container">
        <template v-if="scheduleType == 1">
          <a-row
            v-for="(schedule, scheduleIndex) in scheduleGeneral"
            :key="scheduleIndex"
            :gutter="12"
            align="middle"
            class="schedule-row"
          >
            <template v-if="schedule.twenty_four_hours">
              <a-col :flex="1">
                <span class="all-day-text">Todo el día</span>
              </a-col>
            </template>
            <template v-else-if="schedule.single_time">
              <a-col :flex="0">
                <a-input
                  v-model:value="schedule.open"
                  style="width: 120px"
                  placeholder="00:00"
                  :maxlength="5"
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
              </a-col>
              <a-col :flex="0">
                <div class="schedule-actions">
                  <div class="icon-button" @click="handleAddGeneralSchedule(false, true)">
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

                  <div
                    v-if="scheduleGeneral.length > 1"
                    class="icon-button"
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
              </a-col>
            </template>
            <template v-else>
              <a-col :flex="0">
                <a-input
                  v-model:value="schedule.open"
                  style="width: 120px"
                  placeholder="00:00"
                  :maxlength="5"
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
              </a-col>
              <a-col :flex="0">
                <span class="separator">a</span>
              </a-col>
              <a-col :flex="0">
                <a-input
                  v-model:value="schedule.close"
                  style="width: 120px"
                  placeholder="00:00"
                  :maxlength="5"
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
              </a-col>
              <a-col :flex="0">
                <div class="schedule-actions">
                  <div class="icon-button" @click="handleAddGeneralSchedule(false, false)">
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

                  <div
                    v-if="scheduleGeneral.length > 1"
                    class="icon-button"
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
              </a-col>
            </template>
          </a-row>
        </template>

        <template v-else>
          <template v-for="(item, dayIndex) in schedule" :key="dayIndex">
            <template v-if="item.available_day">
              <a-row
                v-for="(scheduleItem, scheduleIndex) in item.schedules"
                :key="`${dayIndex}-${scheduleIndex}`"
                :gutter="12"
                align="middle"
                class="schedule-row"
              >
                <a-col
                  v-if="
                    (isSelectingTwentyFourHours || item.twenty_four_hours) && scheduleIndex === 0
                  "
                  :flex="0"
                >
                  <a-checkbox
                    :checked="
                      item.twenty_four_hours ||
                      (isSelectingTwentyFourHours && selectedDaysFor24Hours[dayIndex])
                    "
                    @change="handleToggleDayCheckbox(dayIndex)"
                    :disabled="!isSelectingTwentyFourHours && item.twenty_four_hours"
                  />
                </a-col>
                <a-col
                  v-if="(isSelectingSingleTime || item.single_time) && scheduleIndex === 0"
                  :flex="0"
                >
                  <a-checkbox
                    :checked="
                      item.single_time ||
                      (isSelectingSingleTime && selectedDaysForSingleTime[dayIndex])
                    "
                    @change="handleToggleSingleTimeCheckbox(dayIndex)"
                    :disabled="!isSelectingSingleTime && item.single_time"
                  />
                </a-col>
                <a-col :flex="0" style="min-width: 44px">
                  <span class="day-label">
                    {{ scheduleIndex === 0 ? item.label : '' }}
                  </span>
                </a-col>
                <template v-if="item.twenty_four_hours && scheduleIndex === 0">
                  <a-col :flex="1">
                    <span class="all-day-text">Todo el día</span>
                  </a-col>
                </template>
                <template v-else-if="item.single_time && scheduleIndex === 0">
                  <a-col :flex="0">
                    <a-input
                      v-model:value="scheduleItem.open"
                      style="width: 90px"
                      placeholder="00:00"
                      :maxlength="5"
                      @click="
                        (e: Event) =>
                          handleTimeInputClick(e, 'custom', 'open', dayIndex, scheduleIndex)
                      "
                      @focus="
                        (e: Event) =>
                          handleTimeInputFocus(e, 'custom', 'open', dayIndex, scheduleIndex)
                      "
                      @input="
                        (e: Event) =>
                          handleTimeInputChange(e, 'custom', 'open', dayIndex, scheduleIndex)
                      "
                      @blur="
                        (e: Event) =>
                          handleTimeInputBlur(e, 'custom', 'open', dayIndex, scheduleIndex)
                      "
                      @keydown="handleTimeKeyDown"
                    />
                  </a-col>
                </template>
                <template v-else-if="!item.twenty_four_hours && !item.single_time">
                  <a-col :flex="0">
                    <a-input
                      v-model:value="scheduleItem.open"
                      style="width: 90px"
                      placeholder="00:00"
                      :maxlength="5"
                      @click="
                        (e: Event) =>
                          handleTimeInputClick(e, 'custom', 'open', dayIndex, scheduleIndex)
                      "
                      @focus="
                        (e: Event) =>
                          handleTimeInputFocus(e, 'custom', 'open', dayIndex, scheduleIndex)
                      "
                      @input="
                        (e: Event) =>
                          handleTimeInputChange(e, 'custom', 'open', dayIndex, scheduleIndex)
                      "
                      @blur="
                        (e: Event) =>
                          handleTimeInputBlur(e, 'custom', 'open', dayIndex, scheduleIndex)
                      "
                      @keydown="handleTimeKeyDown"
                    />
                  </a-col>
                  <a-col :flex="0">
                    <span class="separator">a</span>
                  </a-col>
                  <a-col :flex="0">
                    <a-input
                      v-model:value="scheduleItem.close"
                      style="width: 90px"
                      placeholder="00:00"
                      :maxlength="5"
                      @click="
                        (e: Event) =>
                          handleTimeInputClick(e, 'custom', 'close', dayIndex, scheduleIndex)
                      "
                      @focus="
                        (e: Event) =>
                          handleTimeInputFocus(e, 'custom', 'close', dayIndex, scheduleIndex)
                      "
                      @input="
                        (e: Event) =>
                          handleTimeInputChange(e, 'custom', 'close', dayIndex, scheduleIndex)
                      "
                      @blur="
                        (e: Event) =>
                          handleTimeInputBlur(e, 'custom', 'close', dayIndex, scheduleIndex)
                      "
                      @keydown="handleTimeKeyDown"
                    />
                  </a-col>
                  <a-col :flex="0">
                    <div class="schedule-actions">
                      <div class="icon-button" @click="handleAddSchedule(dayIndex)">
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

                      <div
                        v-if="item.schedules.length > 1"
                        class="icon-button"
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

                      <div
                        v-if="item.schedules.length === 1"
                        class="icon-button"
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
                  </a-col>
                </template>
              </a-row>
            </template>

            <template v-else>
              <a-row :gutter="12" align="middle" class="schedule-row">
                <a-col :flex="0" style="min-width: 44px">
                  <span class="day-label">{{ item.label }}</span>
                </a-col>
                <a-col :flex="0">
                  <div class="unavailable-container">
                    <span class="unavailable-text">No disponible</span>
                    <div class="icon-button" @click="handleToggleAvailableDay(dayIndex)">
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
                </a-col>
              </a-row>
            </template>
          </template>
        </template>
      </div>
    </a-form-item-rest>
  </a-form-item>
</template>

<script setup lang="ts">
  import { useSchedule } from '../composables/useSchedule';

  defineOptions({
    name: 'ScheduleComponent',
  });

  interface Props {
    currentKey?: string;
    currentCode?: string;
  }

  const props = withDefaults(defineProps<Props>(), {
    currentKey: '',
    currentCode: '',
  });

  const scheduleComposable = useSchedule(props.currentKey, props.currentCode);

  const {
    scheduleType,
    scheduleGeneral,
    schedule,
    isSelectingTwentyFourHours,
    selectedDaysFor24Hours,
    isSelectingSingleTime,
    selectedDaysForSingleTime,
    allDaysSelected24Hours,
    allDaysSelectedSingleTime,
    handleTimeInputClick,
    handleTimeInputFocus,
    handleTimeInputChange,
    handleTimeInputBlur,
    handleTimeKeyDown,
    handleAddSchedule,
    handleRemoveSchedule,
    handleToggleAvailableDay,
    handleAddGeneralSchedule,
    handleRemoveGeneralSchedule,
    handleToggleTwentyFourHours,
    handleToggleDayCheckbox,
    handleSaveTwentyFourHours,
    handleToggleSingleTime,
    handleToggleSingleTimeCheckbox,
    handleSaveSingleTime,
    handleSelectAll24Hours,
    handleSelectAllSingleTime,
  } = scheduleComposable;
</script>

<style scoped lang="scss">
  .form-label {
    font-size: 14px;
    font-weight: 500;
    color: #262626;
  }

  :deep(.ant-radio-group) {
    display: flex;
    align-items: center;

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

  .schedules-container {
    margin-top: 16px;
  }

  .schedule-row {
    &:not(:last-child) {
      padding-bottom: 1px;
    }
  }

  :deep(.schedule-row) {
    margin-bottom: 20px !important;
  }

  .day-label {
    font-size: 14px;
    color: #2f353a;
    display: inline-block;
  }

  .separator {
    color: #8c8c8c;
    font-size: 14px;
    padding: 0 4px;
  }

  .unavailable-text {
    color: #2f353a;
    font-weight: 500;
    font-size: 14px;
  }

  .unavailable-container {
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .schedule-actions {
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .schedule-type-info {
    margin-top: 30px;
    display: flex;
    flex-direction: column;
    gap: 16px;
    color: #8c8c8c;
    font-size: 14px;
  }

  .schedule-options-row {
    display: flex;
    align-items: center;
    gap: 24px;

    > div {
      display: flex;
      align-items: center;
      gap: 8px;
    }
  }

  .schedule-text-info {
    color: #575b5f;
    font-size: 14px;
    line-height: 32px;
    font-weight: 500px;
  }

  .clickable-option {
    cursor: pointer;
    transition: opacity 0.2s;

    &:hover:not(.disabled) {
      opacity: 0.7;
    }

    &.disabled {
      cursor: not-allowed;
      opacity: 0.5;
    }
  }

  .disabled-text {
    color: #d9d9d9;
  }

  .all-day-text {
    color: #2f353a;
    font-weight: 500;
    font-size: 14px;
  }

  .single-time-text {
    color: #2f353a;
    font-weight: 500;
    font-size: 14px;
  }

  .select-all-container {
    margin-top: 0;
  }

  .save-button {
    margin-left: 16px;
  }

  :deep(.save-button) {
    border-color: #2f353a !important;
    color: #2f353a !important;
    background-color: transparent !important;

    &:hover {
      border-color: #2f353a !important;
      color: #2f353a !important;
      background-color: transparent !important;
    }

    &:focus {
      border-color: #2f353a !important;
      color: #2f353a !important;
      background-color: transparent !important;
    }
  }

  .icon-button {
    height: 24px;
    width: 24px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: opacity 0.2s;

    &:hover {
      opacity: 0.7;
    }

    svg {
      display: block;
    }
  }

  :deep(.ant-input) {
    height: 32px;
  }

  :deep(.ant-row.schedule-row) {
    margin-bottom: 20px !important;
  }
</style>
