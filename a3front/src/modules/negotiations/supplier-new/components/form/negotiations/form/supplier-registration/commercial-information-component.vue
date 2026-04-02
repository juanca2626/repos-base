<template>
  <div class="information-commercial-component">
    <div class="container">
      <!-- ✅ Estado vacío -->
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

        <!-- Spinner GLOBAL cuando ya hay datos -->
        <SpinGlobalComponent :tip="spinTip" :spinning="spinning">
          <div v-if="!getIsEditFormComponent(FormComponentEnum.COMMERCIAL_INFORMATION)">
            <a-form :model="formState" ref="formRef" :rules="formRules">
              <a-form-item name="type_food_id">
                <template #label>
                  <div class="form-label">Tipo de comida</div>
                </template>
                <a-select
                  v-model:value="formState.type_food_id"
                  ref="select"
                  placeholder="Seleccionar tipo de comida"
                  style="width: 20rem"
                  :options="typeFoods"
                  mode="multiple"
                />
              </a-form-item>

              <a-form-item name="classification">
                <template #label>
                  <div class="form-label">Clasificación</div>
                </template>
                <div class="classification-container">
                  <div>
                    <a-rate v-model:value="formState.classification">
                      <template #character>
                        <IconRestaurant />
                      </template>
                    </a-rate>
                  </div>
                  <div class="rating-value">{{ formState.classification }}</div>
                </div>
              </a-form-item>

              <a-form-item name="amenities">
                <template #label>
                  <div class="form-label">Amenities</div>
                </template>
                <div style="display: flex; width: 9rem">
                  <a-checkbox-group :options="amenities" v-model:value="formState.amenities" />
                </div>
              </a-form-item>

              <a-form-item name="spaces">
                <template #label>
                  <div class="form-label">Espacio + Aforo</div>
                </template>
                <a-form-item-rest>
                  <div v-for="(item, index) in formState.spaces" :key="index">
                    <div class="capacity-container">
                      <div>
                        <a-input placeholder="Espacios" v-model:value="item.spaces" />
                      </div>
                      <div>
                        <a-input-number
                          placeholder="Aforo"
                          :min="0"
                          v-model:value="item.capacity"
                        />
                      </div>
                      <div style="height: 23px; cursor: pointer; display: flex; gap: 0.5rem">
                        <div v-if="index >= formState.spaces.length - 1">
                          <svg
                            @click="handleAddSpace"
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
                        <div v-else-if="index > -1">
                          <svg
                            @click="handleRemoveSpace(index)"
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
                  </div>
                </a-form-item-rest>
              </a-form-item>

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

              <div v-if="showAdditionalInfo">
                <a-form-item name="additional_information">
                  <template #label>
                    <div class="form-label">Información adicional</div>
                  </template>
                  <a-textarea
                    v-model:value="formState.additional_information"
                    :auto-size="{ minRows: 2, maxRows: 5 }"
                    style="width: 24rem"
                  />
                </a-form-item>
              </div>
            </a-form>

            <div class="additional-info-toggle mt-2">
              <span @click="handleToggleAdditionalInfo" class="toggle-button">
                <span v-if="!showAdditionalInfo">
                  <font-awesome-icon icon="fa-solid fa-plus" /> Agregar información adicional
                </span>
                <span v-else>
                  <font-awesome-icon icon="fa-solid fa-minus" /> Eliminar información adicional
                </span>
              </span>
            </div>

            <div class="options-button">
              <a-button size="large" type="default" @click="handleClose"> Cancelar </a-button>
              <a-button
                size="large"
                type="primary"
                :disabled="!getIsFormValid || isLoadingButton"
                :loading="isLoadingButton"
                @click="handleSave"
              >
                Guardar datos
              </a-button>
            </div>
          </div>

          <!-- Vista de resumen cuando está en modo edición -->
          <div v-else class="list-items-form">
            <div class="container-item">
              <div class="bullet-point"></div>
              <div class="title-item">Tipo de comida:</div>
              <div class="value-item">{{ getTypeFoodLabel() }}</div>
            </div>

            <div class="container-item">
              <div class="bullet-point"></div>
              <div class="title-item">Clasificación:</div>
              <div class="value-item">{{ formState.classification }}</div>
            </div>

            <div class="container-item">
              <div class="bullet-point"></div>
              <div class="title-item">Amenities:</div>
              <div class="value-item">{{ getAmenitiesLabel() }}</div>
            </div>

            <div class="container-item">
              <div class="bullet-point"></div>
              <div class="title-item">Espacio + Aforo:</div>
              <div class="value-item">{{ getSpacesLabel() }}</div>
            </div>

            <!-- Horarios -->
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
                          formState.scheduleGeneral.filter((s: any) => s.open && s.close).length - 1
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
                v-show="item.available_day && item.schedules?.some((s: any) => s.open && s.close)"
              >
                <div class="bullet-point sub-bullet"></div>
                <div class="title-item schedule-day">{{ item.label }}:</div>
                <div class="value-item">
                  <span v-for="(schedule, sIndex) in item.schedules" :key="sIndex">
                    <template v-if="schedule.open && schedule.close">
                      {{ schedule.open }} - {{ schedule.close }}
                      <span
                        v-if="
                          sIndex < item.schedules.filter((s: any) => s.open && s.close).length - 1
                        "
                        >,
                      </span>
                    </template>
                  </span>
                </div>
              </div>
            </template>

            <div v-if="formState.additional_information" class="container-item">
              <div class="bullet-point"></div>
              <div class="title-item">Información adicional:</div>
              <div class="value-item">{{ formState.additional_information }}</div>
            </div>
          </div>
        </SpinGlobalComponent>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
  import EmptyStateFormGlobalComponent from '@/modules/negotiations/supplier-new/components/global/empty-state-form-global-component.vue';
  import SpinGlobalComponent from '@/modules/negotiations/supplier-new/components/global/spin-global-component.vue';
  import { FormComponentEnum } from '@/modules/negotiations/supplier-new/enums/form-component.enum';
  import { useInformationCommercialComposable } from '@/modules/negotiations/supplier-new/composables/form/negotiations/supplier-registration/information-commercial.composable';
  import IconRestaurant from '@/modules/negotiations/supplier-new/icons/icon-restaurant.vue';

  defineOptions({
    name: 'CommercialInformationComponent',
  });

  const {
    // estado del formulario
    formState,
    formRef,
    formRules,
    typeFoods,
    amenities,

    // spinner local (como en referencia)
    spinTip,
    spinning,
    isEditMode,
    isLoadingButton,

    // UI toggles
    showAdditionalInfo,

    // helpers globales
    getShowFormComponent,
    getIsEditFormComponent,
    getIsFormValid,

    // acciones
    handleClose,
    handleSave,
    handleShowForm,
    handleToggleAdditionalInfo,
    handleAddSpace,
    handleRemoveSpace,
    handleAddGeneralSchedule,
    handleRemoveGeneralSchedule,
    handleAddSchedule,
    handleRemoveSchedule,
    handleToggleAvailableDay,
    getTypeFoodLabel,
    getSpacesLabel,
    getAmenitiesLabel,

    // Funciones mejoradas para a-input
    handleTimeInputChange,
    handleTimeInputBlur,
    handleTimeInputFocus,
    handleTimeInputClick,
    handleTimeKeyDown,
  } = useInformationCommercialComposable();
</script>

<style lang="scss">
  .information-commercial-component {
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
        color: #1284ed;
        cursor: pointer;

        svg {
          height: 15px;
          width: 15px;
        }
      }
    }

    .ant-form-item-control {
      width: 422px;
    }

    .container {
      margin-top: 20px;
    }

    .ant-form-item {
      margin-bottom: 10px !important;
    }

    .ant-form-item-required::before {
      display: none !important;
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

    .ant-form-item-required::after {
      display: inline-block;
      margin-inline-end: 4px;
      color: #ff4d4f;
      font-size: 14px;
      line-height: 1;
      content: '*' !important;
    }

    .tooltip-float {
      position: absolute;
      bottom: 37px;
      left: 161px;

      svg {
        cursor: pointer;
      }
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

    .item-form-code {
      display: flex;
      gap: 0.5rem;
      align-items: center;

      svg {
        height: 20px;
        color: #575b5f;
        cursor: pointer;
      }
    }

    .options-button {
      display: flex;
      gap: 1rem;

      .ant-btn-default {
        width: 118px;
        height: 48px;
        gap: 8px;
        border-radius: 5px;
        border-width: 1px;
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
        gap: 8px;
        border-radius: 5px;
        border-width: 1px;
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

    .classification-container {
      display: flex;
      gap: 1rem;
      align-items: center;
    }

    .rating-value {
      color: #101010;
      font-size: 16px;
      font-weight: 700;
    }

    .amenity-tag {
      height: 40px;
      gap: 8px;
      border-radius: 32px;
      padding: 8px 16px;
      border: 1px solid #575b5f;
      display: flex;
      justify-content: center;
    }

    .capacity-container {
      display: flex;
      gap: 1rem;
      align-items: center;
      margin-bottom: 1rem;

      .ant-input-number {
        width: 11rem;
      }
    }

    .schedule-row {
      display: flex;
      gap: 1rem;
      align-items: center;
      margin-bottom: 1rem;
    }

    .schedule-break {
      width: 100%;
      height: 0.5rem;
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

    .additional-info-toggle {
      font-weight: 500;
      font-size: 16px;
      line-height: 32px;
      text-decoration: underline;
      text-decoration-style: solid;
      text-decoration-thickness: 0;
      margin-bottom: 1rem;
      color: #1284ed;

      .toggle-button {
        display: inline-block;
        cursor: pointer;

        &:hover {
          text-decoration-thickness: 1px;
        }
      }
    }

    .mt-2 {
      margin-top: 0.5rem !important;
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
