<template>
  <a-modal v-model:open="props.openModal" width="70%" @cancel="closeModal" :maskClosable="false">
    <a-row>
      <a-col>
        <div class="d-flex">
          <svg
            width="24"
            height="24"
            viewBox="0 0 39 39"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              d="M19.5 35.3333C28.4746 35.3333 35.75 28.2445 35.75 19.5C35.75 10.7555 28.4746 3.66667 19.5 3.66667C10.5254 3.66667 3.25 10.7555 3.25 19.5C3.25 28.2445 10.5254 35.3333 19.5 35.3333Z"
              stroke="#212529"
              stroke-width="3.5"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
            <path
              d="M19.5 13.1667V25.8333"
              stroke="#212529"
              stroke-width="3.5"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
            <path
              d="M13 19.5H26"
              stroke="#212529"
              stroke-width="3.5"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
          </svg>
          <div class="title">{{ t('files.message.add_additional_information_to_the_file') }}</div>
        </div>
      </a-col>
    </a-row>

    <a-row class="mt-4">
      <a-col :span="24">
        <a-card
          style="width: 100%"
          :tab-list="tabListNoTitle"
          :active-tab-key="noTitleKey"
          @tabChange="(key) => onTabChange(key, 'noTitleKey')"
          :disabled="props.id != 0"
        >
          <div v-if="noTitleKey === 'INFORMATIVE'" style="padding: 10px 25px">
            <a-row v-if="formNotes.record_type === 'EXTERNAL_HOUSING'">
              <a-col :span="24">
                <a-alert
                  :message="
                    `En el rango de fecha seleccionadas ya se encuentran registrados los siguientes hoteles: \n ` +
                    notificacionHotel
                  "
                  type="error"
                  v-if="notificacionHotel != ''"
                  style="white-space: pre-line"
                />
              </a-col>
            </a-row>
            <a-row>
              <a-col :span="24" style="text-align: right">
                <a-radio-group
                  v-model:value="formNotes.record_type"
                  :options="optionsWithDisabled"
                  @change="changeRecordType"
                  :disabled="props.id != 0"
                />
              </a-col>
            </a-row>
            <!-- FOR FILE -->
            <a-row class="mt-4" v-if="formNotes.record_type === 'FOR_FILE'">
              <a-col :span="5"> {{ t('global.label.classification') }} </a-col>
              <a-col :span="19">
                <a-select
                  v-model:value="formNotes.classification_code"
                  label-in-value
                  @change="changeClasification"
                  style="width: 100%"
                  @blur="validateField('classification_code')"
                  :status="errors.classification_code ? 'error' : ''"
                  :placeholder="t('global.label.select')"
                >
                  <!-- <a-select-option value="" selected hidden>{{ t('global.label.select') }}</a-select-option> -->
                  <a-select-option
                    v-for="item in arrClassification"
                    :key="item.code.trim()"
                    :value="item.code.trim()"
                  >
                    {{ item.description }}
                  </a-select-option>
                </a-select>

                <div v-if="errors.classification_code" class="error-message">
                  {{ errors.classification_code }}
                </div>
              </a-col>
            </a-row>
            <!-- FOR DATE -->
            <a-row class="mt-4" v-if="formNotes.record_type == 'FOR_DATE'">
              <a-col :span="5">
                {{ t('global.column.choose_the_dates') }}
                <span style="color: #d80404">*</span></a-col
              >
              <a-col :span="9">
                <a-select
                  v-model:value="dates_ids"
                  mode="tags"
                  class="custom-tag-select"
                  label-in-value
                  style="width: 100%"
                  :placeholder="t('global.message.select_the_dates')"
                  @change="addDates"
                  @blur="validateField('dates_ids')"
                  :status="errors.dates_ids ? 'error' : ''"
                >
                  <a-select-option value="" selected hidden>{{
                    t('global.label.select')
                  }}</a-select-option>
                  <a-select-option
                    v-for="item in options"
                    :key="item.option"
                    :value="item.option"
                    :disabled="isDateBeforeToday(item.option)"
                  >
                    {{ item.label }}
                  </a-select-option>
                </a-select>
                <div v-if="errors.dates_ids" class="error-message">
                  {{ errors.dates_ids }}
                </div>
              </a-col>
              <a-col
                :span="3"
                style="display: flex; justify-content: flex-end; align-items: center"
              >
                {{ t('global.label.classification') }}
              </a-col>
              <a-col
                :span="7"
                style="
                  display: flex;
                  flex-direction: column;
                  justify-content: center;
                  align-items: flex-end;
                "
              >
                <a-select
                  v-model:value="formNotes.classification_code"
                  label-in-value
                  @change="changeClasification"
                  style="width: 90%"
                  @blur="validateField('classification_code')"
                  :status="errors.classification_code ? 'error' : ''"
                  :placeholder="t('global.label.select')"
                >
                  <!-- <a-select-option value="" selected hidden>{{ t('global.label.select') }}</a-select-option> -->
                  <a-select-option
                    v-for="item in serviceNotesStore?.classifications"
                    :key="item.code.trim()"
                    :value="item.code.trim()"
                  >
                    {{ item.description }}
                  </a-select-option>
                </a-select>
                <div v-if="errors.classification_code" class="error-message">
                  {{ errors.classification_code }}
                </div>
              </a-col>
            </a-row>
            <a-row class="mt-4" v-if="formNotes.record_type == 'FOR_DATE'">
              <a-col :span="5"> </a-col>
              <a-col
                :span="9"
                style="
                  text-align: center;
                  display: flex;
                  justify-content: flex-start;
                  align-items: center;
                "
              >
                <a-radio-group
                  v-model:value="formNotes.assignment_mode"
                  :options="optionsAssignmentMode"
                  @change="changeAssignment"
                />
              </a-col>
              <a-col
                :span="3"
                style="
                  text-align: center;
                  display: flex;
                  justify-content: flex-end;
                  align-items: center;
                "
              >
                {{ t('global.label.headquarters') }}
              </a-col>
              <a-col
                :span="7"
                style="display: flex; justify-content: flex-end; align-items: center"
              >
                <a-select v-model:value="headquearters" label-in-value style="width: 80%" disabled>
                </a-select>
              </a-col>
            </a-row>
            <a-row class="mt-4" v-if="formNotes.record_type == 'FOR_DATE'">
              <a-col :span="5" v-if="formNotes.assignment_mode === 'FOR_SERVICE'"
                >{{ t('global.label.services') }} <span style="color: #d80404">*</span></a-col
              >
              <a-col :span="5" v-else></a-col>
              <a-col
                :span="19"
                style="
                  display: flex;
                  flex-direction: column;
                  justify-content: flex-end;
                  align-items: flex-start;
                "
                v-if="formNotes.assignment_mode === 'FOR_SERVICE'"
              >
                <a-select
                  v-model:value="service_ids"
                  mode="tags"
                  label-in-value
                  style="width: 100%"
                  @change="addServices"
                  class="custom-tag-select"
                  @blur="validateField('service_ids')"
                  :status="errors.service_ids ? 'error' : ''"
                >
                  <template v-for="item in arrServices" :key="item.id">
                    <a-select-option :value="item.id">{{ item.name }}</a-select-option>
                  </template>
                </a-select>
                <div v-if="errors.service_ids" class="error-message">
                  {{ errors.service_ids }}
                </div>
              </a-col>
              <a-col :span="19" v-else>
                <a-alert
                  :description="t('files.message.the_note_will_be')"
                  type="info"
                  show-icon
                  closable
                  width="100%"
                >
                  <template #icon>
                    <InfoCircleOutlined style="color: #5c5ab4" />
                  </template>
                </a-alert>
              </a-col>
            </a-row>

            <a-row class="mt-4" v-if="formNotes.record_type != 'EXTERNAL_HOUSING'">
              <a-col :span="5">
                {{ t('global.label.description') }} <span style="color: #d80404">*</span>
              </a-col>
              <a-col :span="19">
                <a-textarea
                  v-model:value="formNotes.description"
                  :placeholder="t('global.message.write_here') + '...'"
                  :rows="5"
                  show-count
                  :maxlength="500"
                  @blur="validateField('description')"
                  :status="errors.description ? 'error' : ''"
                />
                <div v-if="errors.description" class="error-message">
                  {{ errors.description }}
                </div>
              </a-col>
            </a-row>
            <!-- EXTERNAL_HOUSTING -->
            <a-row class="mt-4" v-if="formNotes.record_type == 'EXTERNAL_HOUSING'">
              <a-col :span="6"
                >{{ t('global.label.check_in_out') }} <span style="color: #d80404">*</span>
              </a-col>
              <a-col :span="7">
                <a-range-picker
                  ref="rangePickerRef"
                  v-model:value="rangeDates"
                  :format="dateFormat"
                  :disabled-date="disabledDate"
                  :default-picker-value="defaultMonthView"
                  @change="changeRangeDates"
                  @blur="validateField('rangeDates')"
                  :status="errors.rangeDates ? 'error' : ''"
                />
                <div v-if="errors.rangeDates" class="error-message">
                  {{ errors.rangeDates }}
                </div>
              </a-col>
              <a-col
                :span="3"
                style="display: flex; justify-content: flex-end; align-items: center"
              >
                {{ t('global.label.passengers_capital_letters') }}
              </a-col>
              <a-col :span="1"></a-col>
              <a-col
                :span="7"
                style="
                  display: flex;
                  flex-direction: column;
                  justify-content: center;
                  align-items: flex-start;
                "
              >
                <a-select
                  v-model:value="selectPassengers"
                  mode="tags"
                  label-in-value
                  style="width: 100%"
                  :placeholder="t('global.message.select_passengers')"
                  class="custom-tag-select"
                  @change="changePassengers"
                  @blur="validateField('passengers')"
                  :status="errors.passengers ? 'error' : ''"
                >
                  <a-select-option v-for="item in passengers" :key="item.id" :value="item.id">
                    {{ item.label }}
                  </a-select-option>
                </a-select>
                <div v-if="errors.passengers" class="error-message">
                  {{ errors.passengers }}
                </div>
              </a-col>
            </a-row>
            <a-row class="mt-4" v-if="formNotes.record_type == 'EXTERNAL_HOUSING'">
              <a-col :span="6">
                {{ t('global.label.accommodation_name') }} <span style="color: #d80404">*</span>
              </a-col>
              <a-col :span="7">
                <a-input
                  v-model:value="ExternalHousing.name_housing"
                  type="text"
                  :placeholder="t('global.message.write_here') + '...'"
                  @blur="validateField('name_housing')"
                  :status="errors.name_housing ? 'error' : ''"
                />
                <div v-if="errors.name_housing" class="error-message">
                  {{ errors.name_housing }}
                </div>
              </a-col>
              <a-col
                :span="3"
                style="display: flex; justify-content: flex-end; align-items: center"
              >
                {{ t('global.label.phone') }}
              </a-col>
              <a-col :span="1"></a-col>
              <a-col :span="3">
                <a-select
                  showSearch
                  :placeholder="t('global.label.code') + '+'"
                  optionFilterProp="label"
                  v-model:value="code_phone"
                  :options="listPhoneCode"
                  @blur="validateField('code_phone')"
                  :field-names="{ label: 'label', value: 'code' }"
                  label-in-value
                  @change="(value) => handlePhoneCode(value)"
                  :status="errors.code_phone ? 'error' : ''"
                  style="width: 100%"
                />
                <div v-if="errors.code_phone" class="error-message">
                  {{ errors.code_phone }}
                </div>
              </a-col>
              <a-col :span="4" style="display: flex; flex-direction: column">
                <div style="width: 100%; display: flex; justify-content: flex-end">
                  <a-input
                    type="text"
                    style="width: 90%"
                    placeholder="000 000 000"
                    v-model:value="ExternalHousing.number_phone"
                    @keydown="handleInput"
                    @blur="validateField('number_phone')"
                    :status="errors.number_phone ? 'error' : ''"
                    :precision="0"
                  />
                </div>
                <div style="width: 100%; display: flex; justify-content: flex-end">
                  <div
                    v-if="errors.number_phone"
                    class="error-message"
                    style="text-align: left; width: 90%"
                  >
                    {{ errors.number_phone }}
                  </div>
                </div>
              </a-col>
            </a-row>
            <a-row class="mt-4" v-if="formNotes.record_type == 'EXTERNAL_HOUSING'">
              <a-col :span="6">
                {{ t('global.label.accommodation_address') }} <span style="color: #d80404">*</span>
              </a-col>
              <a-col :span="18">
                <a-input
                  v-model:value="ExternalHousing.address"
                  type="text"
                  :placeholder="t('global.message.search_address') + '...'"
                  style="width: 100%"
                  :value="selectedPlace?.properties?.formatted_address || ''"
                  @blur="validateField('address')"
                  :status="errors.address ? 'error' : ''"
                  @keyup.enter="searchLocation"
                />
                <div v-if="errors.address" class="error-message">
                  {{ errors.address }}
                </div>
              </a-col>
            </a-row>
            <a-row class="mt-4" v-if="formNotes.record_type == 'EXTERNAL_HOUSING'">
              <a-col :span="6"> </a-col>
              <a-col :span="18">
                <GoogleMaps
                  ref="mapRef"
                  :routes="mockRoutes"
                  :defaultLocation="defaultLocation"
                  @place-selected="handlePlaceSelected"
                />
              </a-col>
            </a-row>
            <!-- <a-row class="mt-4" v-if="formNotes.record_type == 'EXTERNAL_HOUSING'">
              <a-col :span="6"></a-col>
              <a-col :span="18">
                <a-card v-if="selectedPlace" style="margin-top: 16px">
                  <h3>Lugar seleccionado:</h3>
                  <p>
                    <strong>Dirección:</strong>
                    {{ selectedPlace?.properties?.formatted_address || [] }}
                  </p>
                  <p>
                    <strong>Coordenadas:</strong> {{ selectedPlace?.location?.coordinates || [] }}
                  </p>
                </a-card>
              </a-col>
            </a-row> -->
          </div>
          <div v-else-if="noTitleKey === 'REQUIREMENT'">
            <a-row>
              <a-col :span="5"
                >{{ t('global.column.choose_the_dates') }} <span style="color: #d80404">*</span>
              </a-col>
              <a-col :span="9">
                <a-select
                  v-model:value="dates_ids"
                  mode="tags"
                  class="custom-tag-select"
                  label-in-value
                  style="width: 100%"
                  :placeholder="t('global.message.select_the_dates')"
                  @change="addDates"
                  @blur="validateField('dates_ids')"
                  :status="errors.dates_ids ? 'error' : ''"
                >
                  <a-select-option value="" selected hidden>{{
                    t('global.label.select')
                  }}</a-select-option>
                  <a-select-option
                    v-for="item in options"
                    :key="item.option"
                    :value="item.option"
                    :disabled="isDateBeforeToday(item.option)"
                  >
                    {{ item.label }}
                  </a-select-option>
                </a-select>
                <div v-if="errors.dates_ids" class="error-message">
                  {{ errors.dates_ids }}
                </div>
              </a-col>
              <a-col
                :span="3"
                style="display: flex; justify-content: flex-end; align-items: center"
              >
                {{ t('global.label.classification') }}
              </a-col>
              <a-col
                :span="7"
                style="
                  display: flex;
                  flex-direction: column;
                  justify-content: flex-end;
                  align-items: center;
                "
              >
                <a-select
                  v-model:value="formNotes.classification_code"
                  label-in-value
                  @change="changeClasification"
                  style="width: 90%"
                  @blur="validateField('classification_code')"
                  :status="errors.classification_code ? 'error' : ''"
                  :placeholder="t('global.label.select')"
                >
                  <a-select-option
                    v-for="item in serviceNotesStore?.classifications"
                    :key="item.code.trim()"
                    :value="item.code.trim()"
                  >
                    {{ item.description }}
                  </a-select-option>
                </a-select>

                <div v-if="errors.classification_code" class="error-message">
                  {{ errors.classification_code }}
                </div>
              </a-col>
            </a-row>
            <a-row class="mt-4">
              <a-col :span="5"></a-col>
              <a-col
                :span="9"
                style="
                  text-align: center;
                  display: flex;
                  justify-content: flex-start;
                  align-items: center;
                "
              >
                <a-radio-group
                  v-model:value="formNotes.assignment_mode"
                  :options="optionsAssignmentMode"
                  @change="changeAssignment"
                />
              </a-col>
              <a-col
                :span="3"
                style="
                  text-align: center;
                  display: flex;
                  justify-content: flex-end;
                  align-items: center;
                "
              >
                {{ t('global.label.headquarters') }}
              </a-col>
              <a-col
                :span="7"
                style="display: flex; justify-content: flex-end; align-items: center"
              >
                <a-select v-model:value="headquearters" label-in-value style="width: 80%" disabled>
                </a-select>
              </a-col>
            </a-row>
            <a-row class="mt-4">
              <a-col :span="5" v-if="formNotes.assignment_mode === 'FOR_SERVICE'"
                >{{ t('global.label.services') }} <span style="color: #d80404">*</span></a-col
              >
              <a-col :span="5" v-else></a-col>
              <a-col
                :span="19"
                style="
                  display: flex;
                  flex-direction: column;
                  justify-content: flex-end;
                  align-items: flex-start;
                "
                v-if="formNotes.assignment_mode === 'FOR_SERVICE'"
              >
                <a-select
                  v-model:value="service_ids"
                  mode="tags"
                  label-in-value
                  style="width: 100%"
                  @change="addServices"
                  class="custom-tag-select"
                  @blur="validateField('service_ids')"
                  :status="errors.service_ids ? 'error' : ''"
                >
                  <!-- <a-select-option v-for="item in arrServices" :key="item.id" :value="item.id">{{
                    item.name
                  }}</a-select-option> -->
                  <template v-for="item in arrServices" :key="item.id">
                    <a-select-option :value="item.id">{{ item.name }}</a-select-option>
                  </template>
                </a-select>
                <div v-if="errors.service_ids" class="error-message">
                  {{ errors.service_ids }}
                </div>
              </a-col>
              <a-col :span="19" v-else>
                <a-alert
                  :description="t('files.message.the_note_will_be_requirement')"
                  type="info"
                  show-icon
                  closable
                >
                  <template #icon>
                    <InfoCircleOutlined style="color: #5c5ab4" />
                  </template>
                </a-alert>
              </a-col>
            </a-row>
            <a-row class="mt-4">
              <a-col :span="5">
                {{ t('global.label.description') }}<span style="color: #d80404">*</span>
              </a-col>
              <a-col :span="19">
                <a-textarea
                  v-model:value="formNotes.description"
                  :placeholder="t('global.message.write_here') + '...'"
                  :rows="5"
                  show-count
                  :maxlength="500"
                  @blur="validateField('description')"
                  :status="errors.description ? 'error' : ''"
                />
                <div v-if="errors.description" class="error-message">
                  {{ errors.description }}
                </div>
              </a-col>
            </a-row>
          </div>
          <!-- <a-row class="mt-4">
            <a-col :span="24">
              <h3>ExternalHousing:</h3>
              <p>
                {{ arrServices || [] }}
              </p>
              <h3>Notes</h3>
              <p>{{ formNotes || [] }}</p>
            </a-col>
          </a-row> -->
        </a-card>
      </a-col>
    </a-row>
    <template #footer>
      <a-row>
        <a-col :span="24">
          <a-button
            @click="closeModal"
            :disabled="
              serviceNotesStore.isloadingSaveOrUpdateNote ||
              serviceNotesStore.isLoadingSaveOrUpdateExternalHousing
            "
            >{{ t('global.button.cancel') }}</a-button
          >
          <a-button
            type="primary"
            class="base-button"
            @click="handleSubmit"
            :disabled="
              serviceNotesStore.isloadingSaveOrUpdateNote ||
              serviceNotesStore.isLoadingSaveOrUpdateExternalHousing
            "
            :loading="
              serviceNotesStore.isloadingSaveOrUpdateNote ||
              serviceNotesStore.isLoadingSaveOrUpdateExternalHousing
            "
            style="color: white"
            >{{ t('global.button.save') }}</a-button
          >
        </a-col>
      </a-row>
    </template>
  </a-modal>
</template>
<script setup>
  import { ref, reactive, computed, watch, onMounted } from 'vue';
  import dayjs from 'dayjs';
  import isSameOrAfter from 'dayjs/plugin/isSameOrAfter';
  import isSameOrBefore from 'dayjs/plugin/isSameOrBefore';
  import isBetween from 'dayjs/plugin/isBetween';
  import GoogleMaps from '@/components/files/reusables/GoogleMaps.vue';
  import { InfoCircleOutlined } from '@ant-design/icons-vue';
  import { useServiceNotesStore, useFilesStore } from '../../../stores/files';
  import { getUserId, getUserName, getUserCode } from '@/utils/auth';
  // import { message, notification } from 'ant-design-vue';
  import useCountries from '@/quotes/composables/useCountries';
  import { useSocketsStore } from '@/stores/global';
  import { useI18n } from 'vue-i18n';
  const { t } = useI18n({
    useScope: 'global',
  });
  // Extiende dayjs con los plugins
  dayjs.extend(isSameOrAfter);
  dayjs.extend(isSameOrBefore);
  // Extender dayjs con el plugin isBetween
  dayjs.extend(isBetween);

  const { countries, getCountries, getPhoneCode } = useCountries();

  const serviceNotesStore = useServiceNotesStore();
  const filesStore = useFilesStore();
  const socketsStore = useSocketsStore();
  const file_id = filesStore.getFile.id;

  // console.log()
  const dateFormat = 'DD/MM/YYYY';

  const loadingResources = ref(false);
  const listPhoneCode = ref([]);
  const code_phone = ref([]);

  const messages = computed(() => {
    return {
      select_a_classification: t('files.message.select_a_classification'), // Debe seleccionar una clasificación
      description_is_required: t('files.message.description_is_required'), // La descripción es obligatoria
      description_characters: t('files.message.description_characters'), // La descripción debe tener al menos 10 caracteres
      select_a_date: t('files.message.select_a_date'), // Debe seleccionar una fecha
      select_a_service: t('files.message.select_a_service'), // Debe selecciona un servicio
      date_range: t('files.message.date_range'), // Debe seleccionar un rango de fechas
      select_a_passengers: t('files.message.select_a_passengers'), // Debe seleccionar un pasajero
      housing_is_required: t('files.message.housing_is_required'), // El nombre del alojamiento es requerido
      housing_characters: t('files.message.housing_characters'), // El nombre del alojamiento debe tener al menos 10 caracteres
      code_is_required: t('files.message.code_is_required'), // El código es requerido
      phone_is_required: t('files.message.phone_is_required'), // El número del telefono es requerido
      address_is_required: t('files.message.address_is_required'), // La dirección del alojamiento es requerido
      create_external_housing: t('files.message.create_external_housing'), // 'Creación de alojamiento externo'
      update_external_housing: t('files.message.update_external_housing'), // 'Actualización de alojamiento externo'
      an_error_occurred: t('files.message.an_error_occurred'), // ocurrio un error
      select: t('global.label.select'),
      save_note: t('files.message.save_note'),
      update_note: t('files.message.update_note'),
      saved_correctly: t('global.message.saved_correctly'),
      updated_correctly: t('global.message.updated_correctly'),
    };
  });

  const emit = defineEmits(['close-modal']);
  const props = defineProps({
    openModal: {
      type: Boolean,
      required: true,
      default: false,
    },
    id: {
      type: Number,
      default: 0,
    },
    type: {
      type: String,
    },
  });

  const closeModal = () => {
    emit('close-modal', false);
  };

  const tabListNoTitle = computed(() => {
    return [
      {
        key: 'INFORMATIVE',
        tab: t('global.label.informative'),
        disabled: (props?.id || 0) > 0,
      },
      {
        key: 'REQUIREMENT',
        tab: t('global.label.requirement'),
        disabled: (props?.id || 0) > 0,
      },
    ];
  });

  const optionsWithDisabled = computed(() => {
    return [
      {
        label: t('global.message.for_the_file'),
        value: 'FOR_FILE',
        disabled: (props?.id || 0) > 0,
      },
      { label: t('global.message.for_a_date'), value: 'FOR_DATE', disabled: (props?.id || 0) > 0 },
      {
        label: t('global.message.register_the_external_housing'),
        value: 'EXTERNAL_HOUSING',
        disabled: (props?.id || 0) > 0,
      },
    ];
  });

  const optionsAssignmentMode = [
    { label: t('global.message.for_the_days'), value: 'ALL_DAY' },
    { label: t('global.message.for_the_services'), value: 'FOR_SERVICE' },
  ];

  const options = computed(() => {
    const uniqueDates = [...new Set(filesStore.getFileItineraries.map((item) => item.date_in))];

    // 2. Formatear para Ant Design Vue (key-label)
    return uniqueDates.map((date, key) => ({
      key: key, // Valor único (puede ser la fecha en sí o un ID)
      label: formDate(date), // Formatea la fecha como texto legible
      option: date,
    }));
  });

  const arrServices = computed(() => {
    // Si no hay fechas seleccionadas, mostrar todos los servicios
    if (dates_ids.value.length === 0) {
      return [];
    }

    // Obtener solo los valores de fecha de las opciones seleccionadas
    const selectedDateValues = dates_ids.value.map((dateObj) => {
      // Dependiendo de cómo estés manejando label-in-value
      return dateObj.key;
    });

    return filesStore.getFileItineraries
      .filter((item) => {
        const isService = item.entity === 'service';
        const matchesDate = selectedDateValues.includes(item.date_in);
        return isService && matchesDate;
      })
      .map((item) => ({
        id: item.id,
        entity: item.entity,
        name: item.name,
        value: item.value,
        label: item.name,
        object_code: item.object_code,
        date_in: item.date_in,
        date_out: item.date_out,
        city_in_iso: item.city_in_iso,
        city_out_iso: item.city_out_iso,
        city_in_name: item.city_in_name,
        city_out_name: item.city_out_name,
      }));
  });

  const service_ids = ref([]);
  const dates_ids = ref([]);
  const headquearters = ref([]);
  const formNotes = reactive({
    id: 0,
    type_note: 'INFORMATIVE',
    record_type: 'FOR_FILE',
    assignment_mode: 'ALL_DAY',
    dates: [],
    description: '',
    classification_code: '',
    classification_name: messages.value.select,
    created_by: getUserId(),
    created_by_code: getUserCode(),
    created_by_name: getUserName(),
    service_ids: [],
  });

  const ExternalHousing = reactive({
    id: 0,
    rangeDates: [],
    passengers: [],
    name_housing: '',
    code_phone: '',
    number_phone: '',
    address: '',
    coordinates: [],
    city: '',
    created_by: getUserId(),
    created_by_code: getUserCode(),
    created_by_name: getUserName(),
  });

  let mockRoutes = ref([]);

  const rangePickerRef = ref(null);
  const rangeDates = ref([]);
  const defaultMonthView = ref([
    dayjs(
      dayjs(filesStore.getFile.dateIn).isAfter(dayjs())
        ? filesStore.getFile.dateIn
        : dayjs().format('YYYY-MM-DD')
    ),
    dayjs(filesStore.getFile.dateOut),
  ]);
  const noTitleKey = ref('INFORMATIVE');

  const dateRange = computed(() => ({
    start: dayjs(
      dayjs(filesStore.getFile?.dateIn).isAfter(dayjs())
        ? filesStore.getFile?.dateIn
        : dayjs().format('YYYY-MM-DD')
    ).subtract(1, 'day'),
    end: dayjs(filesStore.getFile?.dateOut).add(1, 'day'),
  }));

  // const disabledRanges = computed(() => {
  //   const external = serviceNotesStore.getExternalHousing || [];
  //   const filter = external
  //     .filter((e) => {
  //       return e.id != props.id;
  //     })
  //     .map((e) => {
  //       return { date_check_in: e.date_check_in, date_check_out: e.date_check_out };
  //     });

  //   return filter;
  // });

  const arrClassification = computed(() => {
    return serviceNotesStore?.classifications || [];
  });

  const disabledDate = (current) => {
    const { start, end } = dateRange.value;

    if (!start?.isValid() || !end?.isValid()) {
      return true;
    }

    const currentDay = dayjs(current);

    const isOutsideMainRange = currentDay.isBefore(start, 'day') || currentDay.isAfter(end, 'day');

    // const isInDisabledRange = disabledRanges.value.some((range) => {
    //   const rangeStart = dayjs(range.date_check_in);
    //   const rangeEnd = dayjs(range.date_check_out);
    //   return !currentDay.isBefore(rangeStart, 'day') && !currentDay.isAfter(rangeEnd, 'day');
    // });

    return isOutsideMainRange;
  };

  // VALIDACION DE UN RANGO DE FECHAS QUE TENGA HOTELES

  const defaultLocation = ref({
    lat: -12.046374,
    lng: -77.042793,
    address: 'Plaza Mayor de Lima, Centro Histórico',
  });

  const selectedPlace = ref(null);

  const isDateRangeOverlap = (rangeAStart, rangeAEnd, rangeBStart, rangeBEnd) => {
    return (
      rangeAStart.isBetween(rangeBStart, rangeBEnd, null, '[]') ||
      rangeAEnd.isBetween(rangeBStart, rangeBEnd, null, '[]') ||
      rangeBStart.isBetween(rangeAStart, rangeAEnd, null, '[]') ||
      rangeBEnd.isBetween(rangeAStart, rangeAEnd, null, '[]')
    );
  };

  const passengers = computed(() => {
    const getExternalHousing = serviceNotesStore.getExternalHousing || [];
    const passenger = filesStore.getFilePassengers || [];
    const [startDate, endDate] = rangeDates.value || [null, null];

    const passengerIdsToExclude = getExternalHousing.flatMap((item) => {
      const itemCheckIn = item.date_check_in ? dayjs(item.date_check_in) : null;
      const itemCheckOut = item.date_check_out ? dayjs(item.date_check_out) : null;
      const validEdit = item.id == props.id;

      if (startDate && endDate && itemCheckIn && itemCheckOut && !validEdit) {
        if (isDateRangeOverlap(startDate, endDate, itemCheckIn, itemCheckOut)) {
          return item.passengers.map((p) => p.passengers_id);
        } else {
          return [];
        }
      }

      if (validEdit) {
        return [];
      }

      return item.passengers.map((p) => p.passengers_id);
    });

    return passenger.filter((p) => !passengerIdsToExclude.includes(p.id));
  });

  const selectPassengers = ref([]);

  const errors = reactive({
    description: '',
    classification_code: '',
    dates_ids: '',
    service_ids: '',
  });

  const mapRef = ref(null);

  const searchLocation = async () => {
    if (!ExternalHousing.address.trim()) return;

    // Usamos el método expuesto por el componente hijo
    if (mapRef.value) {
      await mapRef.value.searchPlace(ExternalHousing.address);
    }
  };

  const handlePlaceSelected = (place) => {
    console.log(place);
    selectedPlace.value = place;
    ExternalHousing.address = place.properties.formatted_address;
    ExternalHousing.coordinates = place.location.coordinates;
    ExternalHousing.city = place.properties.city;
  };

  const getComponents = async () => {
    if (countries.value.length === 0) {
      loadingResources.value = true;
      const resources = [];
      resources.push(getCountries());
      await Promise.all(resources).then(() => (loadingResources.value = false));
    }

    listPhoneCode.value = getPhoneCode();
  };

  const onTabChange = (value, type) => {
    if (props.id != 0) return;
    formNotes.type_note = value;
    if (value === 'REQUIREMENT') {
      formNotes.record_type = 'FOR_DATE';
    }

    if (type === 'key') {
      key.value = value;
    } else if (type === 'noTitleKey') {
      noTitleKey.value = value;
    }
  };

  const changeClasification = (value) => {
    const searchClassification = serviceNotesStore.classifications.find((e) => {
      return e.code.trim() == value.key;
    });
    formNotes.classification_code = value.key;
    formNotes.classification_name = searchClassification.description;
  };

  const changeAssignment = (assignment) => {
    if (assignment.target.value === 'ALL_DAY') {
      const searchDays = formNotes.dates;
      const filter = filesStore.getFileItineraries
        .filter((item) => {
          const isService = item.entity === 'service';
          const matchesDate = searchDays.includes(item.date_in);
          return isService && matchesDate;
        })
        .map((item) => ({
          id: item.id,
          city_in_name: item.city_in_name,
        }));

      const itineraryIds = filter.map((item) => item.id);
      formNotes.service_ids = itineraryIds;

      const sedes = filter.map((item) => item.city_in_name);
      headquearters.value = formatCities(sedes);
    } else {
      formNotes.service_ids = [];
      service_ids.value = [];
      headquearters.value = '';
    }
  };

  const addServices = (services) => {
    formNotes.service_ids = services.map((item) => item.key);
    const filter = filesStore.getFileItineraries
      .filter((item) => {
        const isService = item.entity === 'service';
        const matchesDate = formNotes.service_ids.includes(item.id);
        return isService && matchesDate;
      })
      .map((item) => ({
        id: item.id,
        city_in_name: item.city_in_name,
      }));

    const sedes = filter.map((item) => item.city_in_name);
    headquearters.value = formatCities(sedes);
    console.log(service_ids.value);
  };

  const addDates = (dates) => {
    formNotes.dates = dates.map((item) => item.key);
    if (formNotes.assignment_mode === 'ALL_DAY') {
      const searchDays = formNotes.dates;
      const filter = filesStore.getFileItineraries
        .filter((item) => {
          const isService = item.entity === 'service';
          const matchesDate = searchDays.includes(item.date_in);
          return isService && matchesDate;
        })
        .map((item) => ({
          id: item.id,
          city_in_name: item.city_in_name,
        }));

      const itineraryIds = filter.map((item) => item.id);
      formNotes.service_ids = itineraryIds;

      const sedes = filter.map((item) => item.city_in_name);
      headquearters.value = formatCities(sedes);
    } else {
      formNotes.service_ids = [];
      service_ids.value = [];
      headquearters.value = '';
      console.log(arrServices.value);
    }
  };

  const changeRecordType = () => {
    if (formNotes.record_type === 'FOR_FILE') {
      clearForm();
    }

    if (formNotes.record_type === 'EXTERNAL_HOUSTING') {
      clearForm();
    }
  };

  const changeRangeDates = () => {
    const [startDate, endDate] = rangeDates.value || [null, null];
    if (!startDate || !endDate) {
      ExternalHousing.rangeDates = [];
      return false;
    } else {
      ExternalHousing.rangeDates = [startDate.format('YYYY-MM-DD'), endDate.format('YYYY-MM-DD')];
    }

    if (rangePickerRef.value) {
      rangePickerRef.value.blur();
      setTimeout(() => {
        rangePickerRef.value.focus();
      }, 100);
    }
    const hoteles = notificationHotel(startDate, endDate);
    console.log(hoteles);
    console.log(startDate.format('YYYY-MM-DD')); // Ej: "2024-01-01"
    console.log(endDate.format('YYYY-MM-DD')); // Ej: "2024-01-10"
  };

  const notificacionHotel = ref('');

  const notificationHotel = (startDate, endDate) => {
    const filteredHotels = filesStore.getFileItineraries
      .filter((item) => {
        if (item.entity !== 'hotel') return false;

        const itemDate = dayjs(item.date_in);
        const rangeStart = dayjs(startDate);
        const rangeEnd = dayjs(endDate);

        return (
          itemDate.isSameOrAfter(rangeStart, 'day') && itemDate.isSameOrBefore(rangeEnd, 'day')
        );
      })
      .map((item) => ({
        id: item.id,
        entity: item.entity,
        name: item.name,
        object_code: item.object_code,
        date_in: item.date_in,
        date_out: item.date_out,
      }));

    if (filteredHotels.length === 0) {
      notificacionHotel.value = '';
    }

    const notificationMessage = filteredHotels
      .map((hotel) => {
        const checkIn = dayjs(hotel.date_in).format('DD/MM/YYYY');
        const checkOut = dayjs(hotel.date_out).format('DD/MM/YYYY');
        return `🏨 ${hotel.name} - (Check-in:${checkIn} - Check-out:${checkOut})\n  ↳ Code: ${hotel.object_code}`;
      })
      .join('\n\n'); // Separa cada hotel con un salto de línea

    notificacionHotel.value = notificationMessage;

    return `Hoteles registrados: \n ${notificationMessage}`;
  };

  const handlePhoneCode = async (value) => {
    ExternalHousing.code_phone = value.option.code;
  };

  function formatCities(citiesArray) {
    return [...new Set(citiesArray)].map((city) => city.toUpperCase()).join('/');
  }

  const formDate = (date) => {
    let d = date.split('-');
    return d[2] + '/' + d[1];
  };

  const handleInput = (e) => {
    // Bloquear teclas no permitidas
    const allowedKeys = ['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'Tab', 'Home', 'End'];

    // Si no es número ni tecla permitida, bloquear
    if (!/^[0-9]$/.test(e.key) && !allowedKeys.includes(e.key)) {
      e.preventDefault();
      return;
    }

    // Manejo especial para teclas de control
    if (allowedKeys.includes(e.key)) return;

    // Limitar a 9 dígitos (para pegar contenido o autocompletar)
    if (ExternalHousing.number_phone.length >= 9) {
      e.preventDefault();
    }
  };

  const isDateBeforeToday = (date) => {
    const inputDate = new Date(date);
    const today = new Date();

    // Reset time components to compare only dates (not time)
    today.setHours(0, 0, 0, 0);
    inputDate.setHours(0, 0, 0, 0);

    return inputDate < today;
  };

  const clearForm = async () => {
    noTitleKey.value = 'INFORMATIVE';
    formNotes.type_note = 'INFORMATIVE';
    formNotes.record_type = 'FOR_FILE';
    formNotes.assignment_mode = 'ALL_DAY';
    formNotes.dates = [];
    formNotes.description = '';
    formNotes.classification_code = '';
    formNotes.classification_name = messages.value.select;
    formNotes.service_ids = [];

    service_ids.value = [];
    headquearters.value = '';
    dates_ids.value = [];

    ExternalHousing.rangeDates = [];
    ExternalHousing.passengers = [];
    ExternalHousing.name_housing = '';
    ExternalHousing.code_phone = '';
    ExternalHousing.number_phone = '';
    ExternalHousing.address = '';
    ExternalHousing.coordinates = '';
    ExternalHousing.city = '';
    selectPassengers.value = [];
    rangeDates.value = [];
    code_phone.value = [];
    // LIMPIAR NOTIFICACION

    notificacionHotel.value = '';
  };

  // ENVIO DEL FORMULARIO
  const validateField = (fieldName) => {
    if (fieldName === 'classification_code') {
      if (!formNotes.classification_code?.trim()) {
        errors.classification_code = messages.value.select_a_classification;
      } else {
        errors.classification_code = '';
      }
    }

    if (fieldName === 'dates_ids') {
      if (formNotes.dates.length < 1) {
        errors.dates_ids = messages.value.select_a_date;
      } else {
        errors.dates_ids = '';
      }
    }

    if (fieldName === 'service_ids') {
      if (formNotes.service_ids.length < 1) {
        errors.service_ids = messages.value.select_a_service;
      } else {
        errors.service_ids = '';
      }
    }

    if (fieldName === 'description') {
      if (!formNotes.description?.trim()) {
        errors.description = messages.value.description_is_required;
      } else if (formNotes.description.trim().length < 10) {
        errors.description = messages.value.description_characters;
      } else {
        errors.description = '';
      }
    }

    if (fieldName === 'rangeDates') {
      if (ExternalHousing.rangeDates.length < 1) {
        errors.rangeDates = messages.value.date_range;
      } else {
        errors.rangeDates = '';
      }
    }

    if (fieldName === 'name_housing') {
      if (!ExternalHousing.name_housing?.trim()) {
        errors.name_housing = messages.value.housing_is_required;
      } else if (ExternalHousing.name_housing.trim().length < 10) {
        errors.name_housing = messages.value.housing_characters;
      } else {
        errors.name_housing = '';
      }
    }

    if (fieldName === 'passengers') {
      if (ExternalHousing.passengers.length < 1) {
        errors.passengers = messages.value.select_a_passengers;
      } else {
        errors.passengers = '';
      }
    }

    if (fieldName === 'code_phone') {
      if (!ExternalHousing.code_phone) {
        errors.code_phone = messages.value.code_is_required;
      } else {
        errors.code_phone = '';
      }
    }

    if (fieldName === 'number_phone') {
      if (!ExternalHousing.number_phone?.trim()) {
        errors.number_phone = messages.value.phone_is_required;
      } else {
        errors.number_phone = '';
      }
    }

    if (fieldName === 'address') {
      if (!ExternalHousing.address?.trim()) {
        errors.address = messages.value.address_is_required;
      } else {
        errors.address = '';
      }
    }
  };

  const validateForm = () => {
    let isValid = true;
    if (formNotes.record_type === 'FOR_FILE') {
      validateField('classification_code');
      if (errors.classification_code) isValid = false;
    }

    if (formNotes.record_type === 'FOR_DATE') {
      validateField('classification_code');
      validateField('dates_ids');
      if (errors.classification_code) isValid = false;
      if (errors.dates_ids) isValid = false;

      if (formNotes.assignment_mode === 'FOR_SERVICE') {
        validateField('service_ids');
        if (errors.service_ids) isValid = false;
      }
    }

    if (formNotes.record_type !== 'EXTERNAL_HOUSING') {
      validateField('description');
      if (errors.description) isValid = false;
    }

    if (formNotes.record_type === 'EXTERNAL_HOUSING') {
      validateField('rangeDates');
      if (errors.rangeDates) isValid = false;

      validateField('name_housing');
      if (errors.name_housing) isValid = false;

      validateField('passengers');
      if (errors.passengers) isValid = false;

      validateField('code_phone');
      if (errors.passengers) isValid = false;

      validateField('number_phone');
      if (errors.passengers) isValid = false;

      validateField('address');
      if (errors.address) isValid = false;
    }

    return isValid;
  };

  // Función para enviar el formulario
  const handleSubmit = async () => {
    if (!validateForm()) {
      console.log('Formulario inválido', errors);
      return;
    }

    try {
      if (formNotes.record_type === 'EXTERNAL_HOUSING') {
        await handleExternalHousingSubmit();
      } else {
        // await handleRegularNoteSubmit();
        const arrEnviar = { ...formNotes };
        arrEnviar.dates = formNotes.dates.length === 0 ? null : JSON.stringify(formNotes.dates);
        arrEnviar.service_ids =
          formNotes.service_ids.length === 0 ? null : JSON.stringify(formNotes.service_ids);
        const isCreate = formNotes.id === 0;
        let response = {};

        if (formNotes.record_type == 'FOR_FILE') {
          if (isCreate) {
            response = await serviceNotesStore.createNote({
              file_id: file_id,
              data: arrEnviar,
            });
          } else {
            response = await serviceNotesStore.updateNote({
              file_id: file_id,
              id: formNotes.id,
              data: arrEnviar,
            });
          }

          await closeModal();
          await clearForm();

          const type = isCreate ? 'create_note' : 'update_note';
          const message = isCreate ? messages.value.save_note : messages.value.update_note;
          const description = isCreate
            ? messages.value.saved_correctly
            : messages.value.updated_correctly;

          sendSocketMessage(response.success, type, message, description, response.data);
        } else {
          const result = Object.values(
            arrServices.value.reduce((acc, e) => {
              // Filtramos primero verificando si el id está en formNotes.service_ids
              if (!formNotes.service_ids.includes(e.id)) return acc;

              const city = e.city_in_iso;

              if (!acc[city]) {
                acc[city] = {
                  city: city,
                  service_ids: [],
                  dates: [],
                };
              }

              // Agregar id si no existe
              if (!acc[city].service_ids.includes(e.id)) {
                acc[city].service_ids.push(e.id);
              }

              // Agregar fecha si no existe
              if (!acc[city].dates.includes(e.date_in)) {
                acc[city].dates.push(e.date_in);
              }

              return acc;
            }, {})
          );
          console.log(result);
          const ids = [];

          for (const e of result) {
            let arrServices = { ...formNotes };
            arrServices.dates = e.dates.length === 0 ? null : JSON.stringify(e.dates);
            arrServices.service_ids =
              e.service_ids.length === 0 ? null : JSON.stringify(e.service_ids);
            if (isCreate) {
              response = await serviceNotesStore.createNote({
                file_id: file_id,
                data: arrServices,
              });
            } else {
              if (JSON.stringify(formNotes.service_ids) === JSON.stringify(e.service_ids)) {
                response = await serviceNotesStore.updateNote({
                  file_id: file_id,
                  data: arrServices,
                  id: formNotes.id,
                });
              } else {
                response = await serviceNotesStore.createNote({
                  file_id: file_id,
                  data: arrServices,
                });
              }
            }
            if (response.success) {
              ids.push(response.data.id);
            }
          }

          await closeModal();
          await clearForm();

          const type = isCreate ? 'create_note' : 'update_note';
          const message = isCreate ? messages.value.save_note : messages.value.update_note;
          const description = isCreate
            ? messages.value.saved_correctly
            : messages.value.updated_correctly;

          sendSocketMessage(response.success, type, message, description, response.data, ids);
        }

        if (isCreate) {
          // else if(
          //   (formNotes.assignment_mode === 'FOR_SERVICE' ||
          //     formNotes.assignment_mode === 'ALL_DAY') &&
          //   formNotes.record_type != 'FOR_FILE'
          // ) {
          //   const result = Object.values(
          //     arrServices.value.reduce((acc, e) => {
          //       // Filtramos primero verificando si el id está en formNotes.service_ids
          //       if (!formNotes.service_ids.includes(e.id)) return acc;
          //       const city = e.city_in_iso;
          //       if (!acc[city]) {
          //         acc[city] = {
          //           city: city,
          //           service_ids: [],
          //           dates: [],
          //         };
          //       }
          //       // Agregar id si no existe
          //       if (!acc[city].service_ids.includes(e.id)) {
          //         acc[city].service_ids.push(e.id);
          //       }
          //       // Agregar fecha si no existe
          //       if (!acc[city].dates.includes(e.date_in)) {
          //         acc[city].dates.push(e.date_in);
          //       }
          //       return acc;
          //     }, {})
          //   );
          //   for (const e of result) {
          //     let arrServices = { ...formNotes };
          //     arrServices.service_ids = JSON.stringify(e.service_ids);
          //     arrServices.dates = JSON.stringify(e.dates);
          //     response = await serviceNotesStore.createNote({
          //       file_id: file_id,
          //       data: arrServices,
          //     });
          //   }
          // }
        } else {
          // if (
          //   (formNotes.assignment_mode === 'FOR_SERVICE' ||
          //     formNotes.assignment_mode === 'ALL_DAY') &&
          //   formNotes.record_type != 'FOR_FILE'
          // ) {
          //   const result = Object.values(
          //     arrServices.value.reduce((acc, e) => {
          //       // Filtramos primero verificando si el id está en formNotes.service_ids
          //       if (!formNotes.service_ids.includes(e.id)) return acc;
          //       const city = e.city_in_iso;
          //       if (!acc[city]) {
          //         acc[city] = {
          //           city: city,
          //           service_ids: [],
          //           dates: [],
          //         };
          //       }
          //       // Agregar id si no existe
          //       if (!acc[city].service_ids.includes(e.id)) {
          //         acc[city].service_ids.push(e.id);
          //       }
          //       // Agregar fecha si no existe
          //       if (!acc[city].dates.includes(e.date_in)) {
          //         acc[city].dates.push(e.date_in);
          //       }
          //       return acc;
          //     }, {})
          //   );
          //   for (const e of result) {
          //     let arrServices = { ...formNotes };
          //     arrServices.dates = JSON.stringify(e.dates);
          //     arrServices.service_ids = JSON.stringify(e.service_ids);
          //     // console.log(arrServices);
          //     // VALIDAR SI CUANDO SE EDITA SE AÑADE MAS SERVICIOS
          //     if (JSON.stringify(formNotes.service_ids) === JSON.stringify(e.service_ids)) {
          //       response = await serviceNotesStore.updateNote({
          //         file_id: file_id,
          //         id: formNotes.id,
          //         data: arrServices,
          //       });
          //     } else {
          //       response = await serviceNotesStore.createNote({
          //         file_id: file_id,
          //         data: arrServices,
          //       });
          //     }
          //   }
          //   // console.log(result);
          // } else {
          //   // console.log(formNotes);
          //   response = await serviceNotesStore.updateNote({
          //     file_id: file_id,
          //     id: formNotes.id,
          //     data: arrEnviar,
          //   });
          // }
        }
        // console.log(response);
        // if (response.success) {
        //   await closeModal();
        //   await clearForm();
        //   if (arrEnviar.type_note === 'REQUIREMENT') {
        //     await serviceNotesStore.fetchAllRequirementFileNotes({ file_id: file_id });
        //   } else {
        //     await serviceNotesStore.fetchAllFileNotes({ file_id: file_id });
        //   }
        //   notification['success']({
        //     message: `Guardado de Notas`,
        //     description: `Guardado correctamente`,
        //     duration: 10,
        //   });
        // } else {
        //   notification['error']({
        //     message: `Ocurrio un error`,
        //     description: response.errors,
        //     duration: 5,
        //   });
        // }
      }
    } catch (error) {
      console.error('Error al enviar el formulario', error);
    }
  };

  const prepareExternalHousingData = (externalHousing) => ({
    ...externalHousing,
    date_check_in: externalHousing.rangeDates[0],
    date_check_out: externalHousing.rangeDates[1],
    lat: externalHousing.coordinates[1]?.toString(),
    lng: externalHousing.coordinates[0]?.toString(),
  });

  const sendSocketMessage = (success, type, message, description, data = {}, ids = []) => {
    socketsStore.send({
      success,
      type: type,
      file_number: filesStore.getFile.fileNumber,
      file_id: filesStore.getFile.id,
      user_code: getUserCode(),
      user_id: getUserId(),
      message: message,
      description: description,
      id: data?.id || 0,
      type_note: data?.type_note || '',
      record_type: data?.record_type || '',
      ids: ids || [],
      entity: 'mask',
    });
  };

  const handleExternalHousingSubmit = async () => {
    const housingData = prepareExternalHousingData(ExternalHousing);
    const isCreate = housingData.id === 0;

    const response = isCreate
      ? await serviceNotesStore.createExternalHousing({ file_id, data: housingData })
      : await serviceNotesStore.updateExternalHousing({
          file_id,
          id: housingData.id,
          data: housingData,
        });

    console.log('data:', response);
    await closeModal();
    await clearForm();
    const type = isCreate ? 'create_external_housing' : 'update_external_housing';
    const message = isCreate
      ? messages.value.create_external_housing
      : messages.value.update_external_housing;
    const description = isCreate
      ? messages.value.create_external_housing
      : messages.value.update_external_housing;
    sendSocketMessage(response.success, type, message, description, response.data);

    if (!response.success) {
      sendSocketMessage(
        response.success,
        type,
        messages.value.an_error_occurred,
        response.errors,
        {}
      );
    }
  };

  // EXTERNAL HOUSING

  const changePassengers = (value) => {
    ExternalHousing.passengers = value.map((item) => item.key);
  };

  const requirement_information = computed(() => {
    return serviceNotesStore.getAllRequirementFileNote || [];
  });

  const additional_information = computed(() => {
    return serviceNotesStore.getAllFileNote?.additional_information || [];
  });

  const information_by_city = computed(() => {
    const for_service = serviceNotesStore.getAllFileNote.for_service || [];
    const arrInformation = Object.values(for_service) // Obtener los valores de las ciudades
      .flatMap((cityDates) => Object.values(cityDates)) // Obtener los arrays de fechas
      .flat();

    return arrInformation;
  });

  const external_housing = computed(() => {
    return serviceNotesStore.getExternalHousing || [];
  });

  watch(
    () => props.id,
    (id) => {
      console.log('ESCUCHANDO EL WATCH', id);
      if (id) {
        if (props.type === 'EXTERNAL_HOUSING') {
          const search = external_housing.value.find((e) => e.id === id);

          formNotes.record_type = props.type;

          ExternalHousing.id = id;
          ExternalHousing.rangeDates = [search.date_check_in, search.date_check_out];
          ExternalHousing.passengers = search.passengers.map((e) => {
            return e.passengers_id;
          });
          ExternalHousing.name_housing = search.name_housing;
          ExternalHousing.code_phone = search.code_phone;
          ExternalHousing.number_phone = search.phone;
          ExternalHousing.address = search.address;
          ExternalHousing.coordinates = [parseFloat(search.lng), parseFloat(search.lat)];
          ExternalHousing.city = search.city;

          selectPassengers.value = search.passengers.map((e) => {
            return e.passengers_id;
          });
          rangeDates.value = [
            dayjs(search.date_check_in, 'YYYY-MM-DD'),
            dayjs(search.date_check_out, 'YYYY-MM-DD'),
          ];
          code_phone.value = search.code_phone;
          defaultLocation.value = {
            lat: parseFloat(search.lat),
            lng: parseFloat(search.lng),
            address: search.address,
          };

          setTimeout(() => {
            if (mapRef.value) {
              mapRef.value.updateMapCenter(defaultLocation.value);
            }
          }, 1000);
          console.log(id);
          console.log(search);
        } else if (props.type === 'FILE') {
          const search = additional_information.value.find((e) => e.id === id);
          formNotes.id = search.id;
          formNotes.type_note = search.type_note;
          noTitleKey.value = formNotes.type_note;
          formNotes.record_type = search.record_type;
          formNotes.assignment_mode = search.assignment_mode;
          formNotes.description = search.description;
          formNotes.classification_code = search.classification_code;
          formNotes.classification_name = search.classification_name;
        } else if (props.type === 'INFORMATIVE') {
          const search = information_by_city.value.find((e) => e.id === id && e.entity === 'mask');
          // console.log(search);
          if (search) {
            formNotes.id = id;
            formNotes.type_note = search.type_note;
            noTitleKey.value = search.type_note;
            formNotes.record_type = search.record_type;
            formNotes.assignment_mode = search.assignment_mode;
            if (search.dates !== null) {
              formNotes.dates = search.dates;
              dates_ids.value = search.dates.map((e) => {
                return {
                  key: e, // Valor único (puede ser la fecha en sí o un ID)
                  label: formDate(e), // Formatea la fecha como texto legible
                  option: e,
                };
              });
            }
            formNotes.description = search.description;
            formNotes.classification_code = search.classification_code;
            formNotes.classification_name = search.classification_name;
            if (search.services.length > 0) {
              // addDates(dates_ids.value);
              formNotes.service_ids = search.services.map((service) => service.service_id);
              if (formNotes.assignment_mode === 'FOR_SERVICE') {
                service_ids.value = search.services.map((service) => {
                  return { value: service.service_id, label: service.service_name };
                });
                headquearters.value = search.city.toUpperCase();
              } else {
                headquearters.value = search.city.toUpperCase();
              }
            }
          }
          // console.log(search);
        } else {
          const search = requirement_information.value.find(
            (e) => e.id === id && e.entity === 'mask'
          );

          // console.log(search);

          if (search) {
            formNotes.id = id;
            formNotes.type_note = search.type_note;
            noTitleKey.value = search.type_note;
            formNotes.record_type = search.record_type;
            formNotes.assignment_mode = search.assignment_mode;
            if (search.dates !== null) {
              formNotes.dates = search.dates;
              dates_ids.value = search.dates.map((e) => {
                return {
                  key: e, // Valor único (puede ser la fecha en sí o un ID)
                  label: formDate(e), // Formatea la fecha como texto legible
                  option: e,
                };
              });
            }
            formNotes.description = search.description;
            formNotes.classification_code = search.classification_code;
            formNotes.classification_name = search.classification_name;
            if (search.services.length > 0) {
              // addDates(dates_ids.value);
              formNotes.service_ids = search.services.map((service) => service.service_id);
              if (formNotes.assignment_mode === 'FOR_SERVICE') {
                service_ids.value = search.services.map((service) => {
                  return { value: service.service_id, label: service.service_name };
                });

                headquearters.value =
                  (search?.cities || [])
                    .map((c) => c?.name?.toUpperCase())
                    .filter(Boolean)
                    .join('/') || 'N/A';
              } else {
                headquearters.value =
                  (search?.cities || [])
                    .map((c) => c?.name?.toUpperCase())
                    .filter(Boolean)
                    .join('/') || 'N/A';
              }
            }
          }

          // console.log(search);
        }
      }
    },
    { immediate: true }
  );

  onMounted(() => {
    getComponents();
  });
</script>
<style scoped>
  .title {
    color: #212529;
    font-weight: 700;
    size: 24px;
  }

  :deep(.ant-card-head) {
    padding: 0 !important;
    background-color: #e9e9e9;
  }

  :deep(.ant-card-head-tabs) {
    width: 100%;
    padding: 0 !important;
    display: flex;
  }

  :deep(.ant-tabs-tab) {
    flex: 1; /* Ocupa el 50% automáticamente */
    text-align: center;
    margin: 0 !important;
    padding: 15px 0 !important;
    justify-content: center;
    color: #979797;
    font-size: 16px;
    font-weight: 600;
  }

  :deep(.ant-tabs-nav-list) {
    width: 100%;
  }

  :deep(.ant-tabs-ink-bar) {
    width: 40% !important; /* Ajusta este valor para hacerla más o menos larga */
    margin-left: 5% !important; /* Centra la línea (calculado como (100% - width)/2) */
    height: 2px !important; /* Grosor de la línea */
  }

  /* Usando /deep/ o ::v-deep para Vue 2 */
  ::v-deep .custom-tag-select .ant-select-selection-item {
    background: #ededff;
    color: #4f4b4b;
    border: none;
  }

  ::v-deep .custom-tag-select .ant-select-selection-item-remove > span {
    color: #c4c4c4;
  }

  .base-button.ant-btn {
    font-weight: 600;
    font-size: 14px;
    line-height: 21px;
    letter-spacing: 0.015em;
    border-radius: 6px;
    background-color: #bd0d12;
    &-lg {
      height: 45px;
    }
  }

  .base-button {
    &-w89 {
      min-width: 89px;
    }
    &-w132 {
      min-width: 132px;
    }
    &-w149 {
      min-width: 149px;
    }
    &-w119 {
      min-width: 119px;
    }
    &-h25 {
      height: 25px;
    }
  }

  .error-message {
    color: #bd0d12;
    font-size: 0.8rem;
    margin-top: 4px;
  }

  :deep(.ant-select-status-error .ant-select-selector) {
    border-color: #bd0d12 !important;
  }

  :deep(.ant-input-status-error) {
    border-color: #bd0d12 !important;
  }
</style>
