<template>
  <template v-if="filesStore.isLoadingPassengers || (filesStore.isLoadingAsync && !flag_modify)">
    <loading-skeleton />
  </template>
  <div class="files-paxs-info" v-else>
    <header class="files-paxs-info-header" v-if="flag_external_link">
      <h3 class="files-paxs-info-title text-uppercase mb-0">
        <font-awesome-icon :icon="['fas', 'user-lock']" class="me-3" />
        <small>{{ t('files.label.register_passengers') }}</small>
      </h3>
      <div class="files-paxs-info-buttons">
        <!-- div @click="copyLinkToClipboard()" class="cursor-pointer">
          <font-awesome-icon :icon="['fas', 'link']" size="xl" class="text-danger" />
        </div -->
        <template
          v-if="
            !(
              filesStore.getFile.status === 'xl' ||
              filesStore.getFile.status === 'ce' ||
              filesStore.getFile.status === 'bl' ||
              filesStore.getFile.status === 'pf'
            )
          "
        >
          <a-button
            type="default"
            size="large"
            v-bind:disabled="filesStore.isLoading || filesStore.isLoadingAsync"
            @click="toggleEdit = !toggleEdit"
          >
            <font-awesome-icon :icon="['fas', 'user-pen']" v-if="!toggleEdit" />
            <font-awesome-icon icon="arrow-left" v-else></font-awesome-icon>
          </a-button>
          <a-button
            type="primary"
            size="large"
            :loading="filesStore.isLoadingDownload"
            v-if="toggleEdit"
            @click="update()"
          >
            <font-awesome-icon :icon="['fas', 'save']" v-if="!filesStore.isLoadingDownload" />
          </a-button>
        </template>

        <template
          v-if="
            !(
              filesStore.getFile.status === 'xl' ||
              filesStore.getFile.status === 'ce' ||
              filesStore.getFile.status === 'bl' ||
              filesStore.getFile.status === 'pf'
            )
          "
        >
          <a-button type="default" size="large" :loading="filesStore.isLoadingDownload">
            <label for="file" class="upload_passengers cursor-pointer">
              <font-awesome-icon :icon="['fas', 'upload']" v-if="!filesStore.isLoadingDownload" />
              <input
                type="file"
                id="file"
                ref="formRefInput"
                accept=".xlsx, .xls"
                v-on:change="handleFileUpload($event)"
                style="display: none"
              />
            </label>
          </a-button>

          <a-button
            type="default"
            size="large"
            :loading="filesStore.isLoadingDownload"
            @click="filesStore.downloadPassengerExcel({ fileId: filesStore.getFile.id })"
          >
            <font-awesome-icon :icon="['fas', 'download']" v-if="!filesStore.isLoadingDownload" />
          </a-button>
        </template>

        <a-select
          v-if="languagesStore.getLanguages.length > 0"
          v-model:value="currentLang"
          style="min-width: 60px"
          ghost
          size="large"
          @change="handleChangeLanguage"
        >
          <template #suffixIcon>
            <font-awesome-icon :icon="['fas', 'language']" />
          </template>
          <a-select-option
            v-for="lang in languagesStore.getLanguages"
            :key="lang.id"
            :value="lang.value"
          >
            {{ lang.value.toUpperCase() }}
          </a-select-option>
        </a-select>
      </div>
    </header>

    <template v-if="!flag_modify">
      <a-alert type="info" v-if="flag_external_link">
        <template #message>
          <a-row type="flex" align="middle" style="gap: 15px; flex-flow: nowrap">
            <a-col>
              <font-awesome-icon :icon="['fas', 'circle-info']" size="xl" beat />
            </a-col>
            <a-col>
              <strong class="m-0">{{ t('files.label.save_time') }}</strong>
              {{ t('files.label.save_time_passengers') }}
            </a-col>
          </a-row>
        </template>
      </a-alert>

      <header class="files-paxs-info-header" v-if="!flag_external_link && !showRoomingList">
        <h4 class="files-paxs-info-title-info">
          <span style="font-size: 18px">{{ t('files.label.passengers_data') }}:</span>

          <!--<a-button type="default" v-if="!toggleEdit">
            <font-awesome-icon icon="stopwatch"></font-awesome-icon>
          </a-button>-->

          <template
            v-if="
              !(
                filesStore.getFile.status === 'xl' ||
                filesStore.getFile.status === 'ce' ||
                filesStore.getFile.status === 'bl' ||
                filesStore.getFile.status === 'pf'
              )
            "
          >
            <a-button
              type="default"
              v-bind:disabled="filesStore.isLoading || filesStore.isLoadingAsync"
              @click="toggleEdit = !toggleEdit"
            >
              <font-awesome-icon :icon="['fas', 'user-pen']" v-if="!toggleEdit" />
              <font-awesome-icon icon="arrow-left" v-else></font-awesome-icon>
            </a-button>

            <a-button
              type="primary"
              v-if="toggleEdit"
              v-bind:disabled="
                filesStore.isLoading ||
                filesStore.isLoadingAsync ||
                uploadsStore.isLoadingAsync ||
                uploadsStore.isLoadingChunk ||
                uploadsStore.isLoading
              "
              @click="update()"
            >
              <a-tooltip>
                <template #title>{{ t('global.button.save') }}</template>
                <font-awesome-icon icon="save" />
              </a-tooltip>
            </a-button>
            <a-button type="default" v-if="!toggleEdit">
              <label for="file" class="upload_passengers cursor-pointer">
                <font-awesome-icon icon="upload" />
                <input
                  type="file"
                  id="file"
                  ref="formRefInput"
                  accept=".xlsx, .xls"
                  v-on:change="handleFileUpload($event)"
                  style="display: none"
                />
              </label>
            </a-button>
          </template>

          <a-popover placement="bottom">
            <template #content>
              <div class="sub_download_passengers">
                <ul>
                  <li class="disabled">{{ t('global.label.list_general') }}</li>
                  <li @click="filesStore.downloadPassengerExcel({ fileId: filesStore.getFile.id })">
                    <span class="text-capitalize">{{ t('global.label.passengers') }}</span>
                  </li>
                  <li class="disabled text-capitalize">{{ t('global.label.formats') }}</li>
                  <li
                    @click="
                      filesStore.downloadPassengerExcelPerurail({ fileId: filesStore.getFile.id })
                    "
                  >
                    Perú Rail
                  </li>
                  <li
                    @click="
                      filesStore.downloadPassengerExcelAmadeus({ fileId: filesStore.getFile.id })
                    "
                  >
                    Amadeus
                  </li>
                </ul>
              </div>
            </template>
            <a-button type="default" v-if="!toggleEdit">
              <svg width="22" height="18" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                  d="m7.592 13.553 3.333 3.334 3.334-3.334M10.925 9.386v7.5"
                  stroke="#3d3d3d"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
                <path
                  d="M18.326 14.461a4.167 4.167 0 0 0-2.4-7.576h-1.05A6.666 6.666 0 0 0 3.034 4.608a6.667 6.667 0 0 0 .39 8.353"
                  stroke="#3d3d3d"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
              </svg>
            </a-button>
          </a-popover>
        </h4>

        <div class="files-paxs-info-buttons">
          <div @click="copyLinkToClipboard()" class="cursor-pointer">
            <font-awesome-icon :icon="['fas', 'link']" size="xl" class="text-danger" />
          </div>

          <base-button
            danger
            type="outline-main"
            size="large"
            @click="viewRoomingList()"
            :disabled="toggleEdit"
            style="border: solid 1px; border-color: #eb5757; color: #eb5757"
          >
            <span>Rooming list</span>
          </base-button>

          <template
            v-if="
              !(
                filesStore.getFile.status === 'xl' ||
                filesStore.getFile.status === 'ce' ||
                filesStore.getFile.status === 'bl' ||
                filesStore.getFile.status === 'pf'
              )
            "
          >
            <base-button
              type="primary"
              size="large"
              :disabled="toggleEdit"
              @click="handleGoToModify()"
              v-if="isAdmin()"
            >
              <div style="display: flex; gap: 4px">
                <span>Modificar cantidad</span>
              </div>
            </base-button>
          </template>
        </div>
      </header>

      <a-collapse
        v-if="!showRoomingList"
        class="collapse-paxs"
        v-model:activeKey="activeKey"
        :bordered="false"
        accordion
        expandIconPosition="end"
      >
        <template #expandIcon="{ isActive }">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            width="28"
            height="28"
            viewBox="0 0 28 28"
            fill="none"
            :class="{ 'rotate-180': isActive }"
          >
            <path
              d="M21 17.5L14 10.5L7 17.5"
              stroke="#3D3D3D"
              stroke-width="3"
              stroke-linecap="round"
              stroke-linejoin="round"
              transform="rotate(180 14 14)"
            />
          </svg>
        </template>

        <template v-for="(item, index) in filesStore.getFilePassengers" :key="item.id">
          <a-collapse-panel
            :header="
              item.name || item.surnames
                ? (item.name ? item.name : '') + ' ' + (item.surnames ? item.surnames : '')
                : 'Pasajero ' + (index + 1)
            "
            :style="customStyle"
          >
            <template #extra>
              <div class="panel-extra-icon">
                <font-awesome-icon
                  v-show="item.type.toUpperCase() === 'ADL'"
                  icon="fa-solid fa-user"
                />
                <font-awesome-icon
                  v-show="item.type.toUpperCase() === 'CHD'"
                  icon="fa-solid fa-child-reaching"
                />
                <font-awesome-icon
                  v-show="item.type.toUpperCase() === 'INF'"
                  icon="fa-solid fa-baby-carriage"
                />
              </div>
            </template>
            <a-form
              :model="formState"
              style="margin-top: 20px; gap: 80px"
              v-bind="formItemLayout"
              labelAlign="left"
              :colon="false"
            >
              <a-row :gutter="24" justify="center" style="padding-left: 30px; padding-right: 30px">
                <a-col :span="11">
                  <a-form-item class="mb-2">
                    <template #label>
                      <div style="font-weight: 500; font-size: 12px; line-height: 19px">
                        {{ t('global.label.name') }}
                      </div>
                      <span class="is-mandatory">*</span></template
                    >
                    <a-input
                      size="middle"
                      placeholder="Escribe aquí..."
                      v-model:value="item.name"
                      v-if="toggleEdit"
                    />
                    <span v-else>{{ item.name != null ? item.name : '-' }}</span>
                  </a-form-item>
                  <a-form-item class="mb-2">
                    <template #label>
                      <div style="font-weight: 500; font-size: 12px; line-height: 19px">
                        {{ t('global.label.doctype') }}
                      </div>
                      <span class="is-mandatory">*</span>
                    </template>
                    <a-select
                      placeholder="Selecciona tipo de Documento"
                      size="middle"
                      :options="documentTypes"
                      v-model:value="item.doctype_iso"
                      :field-names="{ label: 'label', value: 'code' }"
                      v-if="toggleEdit"
                      @change="(value) => handleDocumentType(value, index)"
                    />
                    <span v-else>
                      {{ getDocumentTypeLabel(item.doctype_iso) }}
                    </span>
                  </a-form-item>
                  <a-form-item class="mb-2">
                    <template #label>
                      <div style="font-weight: 500; font-size: 12px; line-height: 19px">
                        {{ t('global.label.country') }}
                      </div>
                      <span class="is-mandatory">*</span>
                    </template>
                    <a-select
                      showSearch
                      v-model:value="item.country_iso"
                      :options="countries"
                      optionFilterProp="label"
                      placeholder="Seleccione un país"
                      label-in-value
                      :field-names="{ label: 'label', value: 'code' }"
                      @change="(value) => countryChange(value, index)"
                      v-if="toggleEdit"
                    ></a-select>
                    <span v-else>
                      {{ getCountryLabel(item.country_iso) }}
                    </span>
                  </a-form-item>
                  <a-form-item class="mb-2">
                    <template #label>
                      <div style="font-weight: 500; font-size: 12px; line-height: 19px">
                        {{ t('global.label.gender') }}
                      </div>
                      <span class="is-mandatory">*</span>
                    </template>
                    <a-select
                      size="middle"
                      :options="genders"
                      placeholder="Escribe aquí..."
                      v-model:value="item.genre"
                      :field-names="{ label: 'label', value: 'code' }"
                      @change="(value) => handleGender(value, index)"
                      v-if="toggleEdit"
                    />
                    <span class="text-capitalize" v-else>
                      {{ getGenderLabel(item.genre) }}
                    </span>
                  </a-form-item>
                  <a-form-item class="mb-2">
                    <template #label>
                      <div style="display: flex; justify-items: center">
                        <div style="font-weight: 500; font-size: 12px; line-height: 19px">
                          {{ t('global.label.email') }}
                        </div>
                        <span class="is-mandatory" style="display: flex; align-items: center">
                          <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="20"
                            height="20"
                            fill="none"
                            stroke="currentColor"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            class="feather feather-alert-circle"
                            viewBox="0 0 24 24"
                          >
                            <circle cx="12" cy="12" r="10" />
                            <path d="M12 8v4M12 16h.01" />
                          </svg>
                        </span>
                      </div>
                    </template>
                    <a-input
                      size="middle"
                      placeholder="Escribe aquí..."
                      v-model:value="item.email"
                      v-if="toggleEdit"
                    />
                    <span v-else>{{ item.email != null ? item.email : '-' }}</span>
                  </a-form-item>
                  <a-form-item class="mb-2">
                    <template #label>
                      <div style="font-weight: 500; font-size: 12px; line-height: 19px">
                        {{ t('global.label.attach_doc') }}
                      </div>
                      <span class="is-mandatory" style="display: flex; align-items: center">
                        <svg
                          xmlns="http://www.w3.org/2000/svg"
                          width="20"
                          height="20"
                          fill="none"
                          stroke="currentColor"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          class="feather feather-alert-circle"
                          viewBox="0 0 24 24"
                        >
                          <circle cx="12" cy="12" r="10" />
                          <path d="M12 8v4M12 16h.01" />
                        </svg>
                      </span>
                    </template>
                  </a-form-item>
                  <div class="button-full-width mb-1">
                    <file-upload
                      class="upload"
                      v-bind:index="index"
                      v-bind:folder="'passengers'"
                      v-bind:title="t('global.label.select_file_to_passenger')"
                      v-bind:multiple="false"
                      v-bind:editable="toggleEdit"
                      v-bind:link="item.document_url"
                      @onResponseFiles="(value) => responseFilesFrom(value, index)"
                    />
                  </div>
                  <a-form-item class="mb-2">
                    <template #label>
                      <div style="font-weight: 500; font-size: 12px; line-height: 19px">
                        {{ t('global.label.medical_restrictions') }}
                      </div>
                    </template>
                  </a-form-item>
                  <div class="mb-2">
                    <a-textarea
                      class="w-100 d-block"
                      placeholder="Especifique las restricciones médicas."
                      :maxlength="100"
                      show-count
                      :rows="4"
                      v-model:value="item.medical_restrictions"
                      v-if="toggleEdit"
                    />
                    <span v-else>{{
                      item.medical_restrictions != null ? item.medical_restrictions : '-'
                    }}</span>
                  </div>
                </a-col>
                <a-col :span="12" :offset="1">
                  <a-form-item class="mb-2">
                    <template #label>
                      <div style="font-weight: 500; font-size: 12px; line-height: 19px">
                        {{ t('global.label.surnames') }}
                      </div>
                      <span class="is-mandatory">*</span>
                    </template>
                    <a-input
                      size="middle"
                      placeholder="Escribe aquí..."
                      v-model:value="item.surnames"
                      v-if="toggleEdit"
                    />
                    <span v-else>{{ item.surnames != null ? item.surnames : '-' }}</span>
                  </a-form-item>
                  <a-form-item class="mb-2">
                    <template #label>
                      <div style="font-weight: 500; font-size: 12px; line-height: 19px">
                        N° <span class="text-lowercase">{{ t('global.label.of') }} doc</span>
                      </div>
                      <span class="is-mandatory">*</span>
                    </template>
                    <a-input
                      size="middle"
                      placeholder="Escribe aquí..."
                      v-model:value="item.document_number"
                      v-if="toggleEdit"
                    />
                    <span v-else>{{
                      item.document_number != null ? item.document_number : '-'
                    }}</span>
                  </a-form-item>
                  <a-form-item class="mb-2">
                    <template #label>
                      <div style="font-weight: 500; font-size: 12px; line-height: 19px">
                        {{ t('global.label.city') }}
                      </div>
                      <span class="is-mandatory">*</span>
                    </template>
                    <a-select
                      placeholder="Seleccione una ciudad"
                      showSearch
                      v-model:value="item.city_iso"
                      :options="item.states"
                      :not-found-content="null"
                      label-in-value
                      optionFilterProp="label"
                      :field-names="{ label: 'label', value: 'code' }"
                      @change="(value) => handleCity(value, index)"
                      v-if="toggleEdit"
                    ></a-select>
                    <span class="text-capitalize" v-if="!loading_async && !toggleEdit">
                      {{ getCityLabel(item) }}
                    </span>
                  </a-form-item>
                  <a-form-item class="mb-2">
                    <template #label>
                      <div style="font-weight: 500; font-size: 12px; line-height: 19px">
                        {{ t('global.label.birthday') }}
                      </div>
                    </template>
                    <a-date-picker
                      size="middle"
                      placeholder="DD/MM/YYYY"
                      style="width: 100%"
                      :format="dateFormat"
                      v-model:value="item.date_birth"
                      v-if="toggleEdit"
                    />
                    <span v-else>{{ formatDate(item.date_birth) }}</span>
                  </a-form-item>
                  <a-form-item class="mb-2">
                    <template #label>
                      <div style="font-weight: 500; font-size: 12px; line-height: 19px">
                        N°
                        <span class="text-lowercase"
                          >{{ t('global.label.of') }} {{ t('global.label.phone') }}</span
                        >
                      </div>
                      <span class="is-mandatory" style="display: flex; align-items: center">
                        <svg
                          xmlns="http://www.w3.org/2000/svg"
                          width="20"
                          height="20"
                          fill="none"
                          stroke="currentColor"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          class="feather feather-alert-circle"
                          viewBox="0 0 24 24"
                        >
                          <circle cx="12" cy="12" r="10" />
                          <path d="M12 8v4M12 16h.01" />
                        </svg>
                      </span>
                    </template>
                    <div>
                      <a-row type="flex" style="gap: 10px">
                        <a-col>
                          <a-select
                            showSearch
                            size="middle"
                            placeholder="Código +"
                            optionFilterProp="label"
                            v-model:value="item.phone_code"
                            :options="listPhoneCode"
                            :field-names="{ label: 'label', value: 'code' }"
                            @change="(value) => handlePhoneCode(value, index)"
                            label-in-value
                            v-if="toggleEdit"
                          />
                          <span v-else>{{
                            item.phone_code
                              ? listPhoneCode.find((option) => option.code === item.phone_code)
                                  ?.label
                              : ''
                          }}</span>
                        </a-col>
                        <a-col flex="auto">
                          <a-input
                            size="middle"
                            placeholder="000 000 000"
                            v-model:value="item.phone"
                            v-if="toggleEdit"
                          />
                          <span v-else>{{ item.phone != null ? item.phone : '' }}</span>
                        </a-col>
                      </a-row>
                    </div>
                  </a-form-item>
                  <a-form-item class="mb-2">
                    <template #label>
                      <div
                        style="font-weight: 500; font-size: 12px; line-height: 19px"
                        class="text-capitalize"
                      >
                        {{ t('global.label.accommodation') }}
                      </div>
                      <span class="is-mandatory">*</span>
                    </template>
                    <a-select
                      optionFilterProp="label"
                      v-model:value="item.room_type"
                      :options="roomTypes"
                      :field-names="{ label: 'label', value: 'code' }"
                      label-in-value
                      @change="(value) => roomTypeChange(value, index)"
                      v-if="toggleEdit"
                    ></a-select>
                    <span v-else>{{
                      item.room_type_description ? item.room_type_description : '-'
                    }}</span>
                  </a-form-item>
                  <a-form-item class="mb-2">
                    <template #label>
                      <div style="font-weight: 500; font-size: 12px; line-height: 19px">
                        {{ t('global.label.dietary_restrictions') }}
                      </div>
                    </template>
                  </a-form-item>
                  <div class="mb-2">
                    <a-textarea
                      class="w-100 d-block"
                      placeholder="Especifique las restricciones almenticias."
                      show-count
                      :maxlength="100"
                      :rows="4"
                      v-model:value="item.dietary_restrictions"
                      v-if="toggleEdit"
                    />
                    <span v-else>{{
                      item.dietary_restrictions != null ? item.dietary_restrictions : '-'
                    }}</span>
                  </div>
                </a-col>
              </a-row>
            </a-form>

            <div
              style="
                display: flex;
                justify-content: flex-start;
                gap: 5px;
                margin: 10px 0;
                color: #eb5757;
              "
            >
              <svg width="22" height="21" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                  d="M10.834 20.001a9.5 9.5 0 1 0 0-19 9.5 9.5 0 0 0 0 19ZM10.623 6.975v4.116M10.623 15.207h.011"
                  stroke="#EB5757"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
              </svg>
              <span style="font-weight: 500; font-size: 14px; line-height: 21px"
                >{{ t('global.message.passengers_required') }}
                <a
                  href="#"
                  @click="masi_link()"
                  style="
                    font-weight: 500;
                    line-height: 23px;
                    color: #80baff;
                    border-bottom: 1.5px solid #80baff;
                  "
                >
                  {{ t('global.button.click_here') }}
                </a>
              </span>
            </div>
            <div
              style="
                display: flex;
                justify-content: flex-start;
                gap: 5px;
                margin: 10px 0;
                color: #eb5757;
                font-weight: 700;
                font-size: 14px;
                line-height: 23px;
              "
            >
              * {{ t('global.label.required_field') }}
            </div>
          </a-collapse-panel>
        </template>
      </a-collapse>
      <div
        style="display: flex; justify-content: flex-end; gap: 5px; margin: 10px 0"
        v-if="!showRoomingList"
      >
        <a
          href="#"
          style="
            font-weight: 500;
            font-size: 16px;
            line-height: 23px;
            color: #80baff;
            border-bottom: 1.5px solid #80baff;
          "
          >{{ t('global.label.data_and_policy_privacy') }}</a
        >
      </div>
    </template>

    <template v-if="flag_modify">
      <paxs-modify @onCloseModify="closeModify" />
    </template>

    <div v-if="showRoomingList">
      <RoomingListPage @onBack="goBackPassengersPage" />
    </div>
  </div>
</template>

<script setup>
  import { formatDate } from '@/utils/files.js';
  import { onMounted, ref, watch, computed } from 'vue';
  import BaseButton from '../reusables/BaseButton.vue';
  import { notification } from 'ant-design-vue';
  import { useFilesStore } from '@store/files';
  import { useUploadsStore, useLanguagesStore } from '@/stores/global';
  import useCountries from '@/quotes/composables/useCountries';
  import useStates from '@/quotes/composables/useStates';
  import dayjs from 'dayjs';
  import * as XLSX from 'xlsx';
  import FileUpload from '@/components/global/FileUploadComponent.vue';
  import PaxsModify from '@/components/files/paxs/ModifyView.vue';
  import RoomingListPage from '@/components/files/rooming/page/RoomingListPage.vue';
  import LoadingSkeleton from '@/components/global/LoadingSkeleton.vue';

  import { isAdmin } from '@/utils/auth';
  import { useI18n } from 'vue-i18n';

  import { Fancybox } from '@fancyapps/ui';
  import '@fancyapps/ui/dist/fancybox/fancybox.css';

  const languagesStore = useLanguagesStore();
  const { t, locale } = useI18n({
    useScope: 'global',
  });

  const dateFormat = 'DD/MM/YYYY';
  const { countries, getCountries, getPhoneCode } = useCountries();
  const { states, getStates } = useStates();
  const loadingResources = ref(false);
  const lang = localStorage.getItem('lang');
  const formRefInput = ref(null);

  const my_cities = ref([]);
  const loading_async = ref(true);

  const flag_modify = ref(false);
  const showRoomingList = ref(false);

  const listPhoneCode = ref([]);

  const getComponents = async () => {
    if (countries.value.length === 0) {
      loadingResources.value = true;
      const resources = [];
      resources.push(getCountries());
      await Promise.all(resources).then(() => (loadingResources.value = false));
    }

    listPhoneCode.value = getPhoneCode();

    let passengers = filesStore.getFilePassengers;

    passengers.forEach(async (p) => {
      await findCities(p.country_iso);
      p.states = my_cities.value[p.country_iso];
      p.date_birth =
        p.date_birth && p.date_birth != '0000-00-00' ? dayjs(p.date_birth, 'DD/MM/YYYY') : '';
    });
  };

  const findCities = async (country_iso) => {
    if (country_iso && country_iso != null && country_iso != '') {
      if (my_cities.value[country_iso] == undefined) {
        loading_async.value = true;
        await getStates(country_iso);
        loading_async.value = false;
        my_cities.value[country_iso] = states.value;
      }
    }
  };

  const countryChange = async (value, index) => {
    let passengers = filesStore.getFilePassengers;
    passengers[index].city_iso = null;
    await findCities(value.option.iso);
    passengers[index].country_iso = value.option.iso;
    passengers[index].phone_code = value.option.phone_code;
    passengers[index].states = my_cities.value[value.option.iso];
  };

  const roomTypeChange = async (value, index) => {
    let passengers = filesStore.getFilePassengers;
    console.log('SELECCIONADO: ', value.option);
    passengers[index].room_type = value.option.code;
    passengers[index].room_type_description = value.option.label;
  };

  const handleDocumentType = async (value, index) => {
    let passengers = filesStore.getFilePassengers;
    passengers[index].doctype_iso = value.option.code;
  };

  const handleCity = async (value, index) => {
    let passengers = filesStore.getFilePassengers;
    passengers[index].city_iso = value.option.code;
  };

  const handleGender = async (value, index) => {
    let passengers = filesStore.getFilePassengers;
    passengers[index].genre = value.option.code;
  };

  const handlePhoneCode = async (value, index) => {
    let passengers = filesStore.getFilePassengers;
    passengers[index].phone_code = value.option.code;
  };

  // Método para manejar la carga de archivos
  const handleFileUpload = async (event) => {
    const file = event.target.files[0]; // Obtener el primer archivo seleccionado

    try {
      const data = await readExcelFile(file);
      const max_pax_file = filesStore.getFilePassengers.length;
      console.log(max_pax_file);
      console.log(data.length - 1);
      if (data.length - 1 > max_pax_file) {
        notification.error({
          message: 'Error',
          description: 'El total de pasajeros en el excel no puede ser superior a ' + max_pax_file,
        });
        return;
      }

      // Procesar los datos obtenidos del archivo Excel
      data.forEach((row, index) => {
        if (index === 0) {
          console.log('Encabezado:', row);
        } else {
          let index_ = index - 1;
          let nombres = row[1]; // Nombres
          let apellidos = row[2]; // Apellidos
          let tipoDocumento = row[3]; // Tipo Documento
          let nroDocumento = row[4]; // Nro Documento
          let pais = row[5]; // País
          let genero = row[6]; // Género
          let fechaNacimiento = row[7]; // Fecha Nacimiento
          let email = row[8]; // Email
          let codigoTelefonico = row[9]; // Código telefónico
          let numeroTelefono = row[10]; // Número Teléfono
          let tipoHabitacion = row[11]; // Tipo Habitación
          let restriccionesMedicas = row[12]; // Restricciones Médicas
          let restriccionesAlimenticias = row[13]; // Restricciones Alimenticias
          putPassengerInFormRow(
            index_,
            nombres,
            apellidos,
            tipoDocumento,
            nroDocumento,
            pais,
            genero,
            fechaNacimiento,
            email,
            codigoTelefonico,
            numeroTelefono,
            tipoHabitacion,
            restriccionesMedicas,
            restriccionesAlimenticias
          );
        }
      });
    } catch (error) {
      console.error('Error al leer el archivo Excel:', error);
    }
  };

  // Función para leer el archivo Excel
  const readExcelFile = (file) => {
    return new Promise((resolve, reject) => {
      const reader = new FileReader();

      reader.onload = (e) => {
        const data = new Uint8Array(e.target.result);
        const workbook = XLSX.read(data, { type: 'array' });

        // Obtener la hoja 'passenger'
        const sheetName = workbook.SheetNames[0]; // Suponiendo que 'passenger' es la primera hoja
        const sheet = workbook.Sheets[sheetName];

        // Convertir la hoja a un objeto JSON
        const jsonData = XLSX.utils.sheet_to_json(sheet, { header: 1 });

        resolve(jsonData);
      };

      reader.onerror = (e) => {
        reject(e);
      };

      // Verificar que el archivo sea del tipo Blob antes de leerlo
      if (file instanceof Blob) {
        reader.readAsArrayBuffer(file);
      } else {
        reject(new Error('El archivo proporcionado no es del tipo Blob.'));
      }
    });
  };

  const putPassengerInFormRow = (
    index,
    nombres,
    apellidos,
    tipoDocumento,
    nroDocumento,
    pais,
    genero,
    fechaNacimiento,
    email,
    codigoTelefonico,
    numeroTelefono,
    tipoHabitacion,
    restriccionesMedicas,
    restriccionesAlimenticias
  ) => {
    let pax = filesStore.getFilePassengers[index];
    // console.log(pax);
    pax.name = nombres ? nombres : null;
    pax.surnames = apellidos ? apellidos : '';
    pax.country_iso = pais ? pais : '';
    pax.date_birth = fechaNacimiento ? dayjs(fechaNacimiento, 'DD/MM/YYYY') : '';
    pax.dietary_restrictions = restriccionesAlimenticias ? restriccionesAlimenticias : '';
    pax.doctype_iso = tipoDocumento ? tipoDocumento : '';
    pax.document_number = nroDocumento ? nroDocumento : '';
    pax.email = email ? email : '';
    pax.genre = genero ? genero.toUpperCase() : '';
    pax.label = apellidos ? apellidos : '';
    pax.label += nombres ? ',' + nombres : '';
    pax.medical_restrictions = restriccionesMedicas ? restriccionesMedicas : '';
    pax.phone_code = codigoTelefonico ? codigoTelefonico : '';
    pax.room_type = tipoHabitacion ? tipoHabitacion : '';
    const type_ = tipoHabitacion ? roomTypes.value.find((type) => type.code == tipoHabitacion) : '';
    pax.room_type_description = type_ !== '' ? type_.label : '';
    pax.type = fechaNacimiento ? calculateAgeCategory(fechaNacimiento) : 'ADL';
    toggleEdit.value = true;
  };

  const calculateAgeCategory = (birthdate) => {
    const today = dayjs();
    const birth = dayjs(birthdate, 'DD/MM/YYYY');
    const age = today.diff(birth, 'year');
    let ageCategory;
    if (age > 3 && age < 18) {
      ageCategory = 'CHD';
    } else if (age <= 3) {
      ageCategory = 'INF';
    } else {
      ageCategory = 'ADL';
    }
    return ageCategory;
  };

  const update = async () => {
    const fileNumber = filesStore.getFile.fileNumber;
    const fileId = filesStore.getFile.id;
    const originalPassengers = filesStore.getFilePassengers;

    // Clonar profundamente el array y aplicar transformaciones
    const passengers = originalPassengers.map((p) => ({
      ...p,
      states: null,
      date_birth:
        p.date_birth && p.date_birth !== '0000-00-00'
          ? dayjs(p.date_birth, 'DD/MM/YYYY').format('YYYY-MM-DD')
          : '',
    }));
    await filesStore.updatePassengers({ fileId, fileNumber, data: passengers });
    await filesStore.storeRepository({
      fileNumber: fileNumber,
      data: passengers,
    });

    toggleEdit.value = !toggleEdit.value;
  };

  const filesStore = useFilesStore();
  const uploadsStore = useUploadsStore();

  const first_pax_id =
    filesStore.getFilePassengers.length > 0 ? filesStore.getFilePassengers[0].id : '';
  const activeKey = ref([first_pax_id]);
  const formState = ref({});
  const toggleEdit = ref(false);

  const customStyle =
    'background: #f7f7f7;border-radius: 6px;margin-bottom: 10px;border: 0;overflow: hidden';

  const formItemLayout = {
    labelCol: {
      xs: { span: 24 },
      sm: { span: 8 },
    },
    wrapperCol: {
      xs: { span: 24 },
      sm: { span: 16 },
    },
  };

  const roomTypes = ref([
    {
      label: 'SGL',
      code: '1',
    },
    {
      label: 'DBL',
      code: '2',
    },
    {
      label: 'TPL',
      code: '3',
    },
  ]);

  const documentTypes = ref([
    {
      label: 'National identity document',
      code: 'DNI',
    },
    {
      label: 'Passport',
      code: 'PAS',
    },
    {
      label: 'Immigration card',
      code: 'CEX',
    },
    {
      label: 'Single taxpayer registration',
      code: 'RUC',
    },
    {
      label: 'Other types of documents',
      code: 'OTR',
    },
  ]);

  const genders = computed(() => [
    { label: t('global.label.female'), code: 'F' },
    { label: t('global.label.male'), code: 'M' },
  ]);

  const getGenderLabel = (code) => {
    const gender = genders.value.find((g) => g.code === code);
    return gender ? gender.label : '';
  };

  const getDocumentTypeLabel = (code) => {
    const el = documentTypes.value.find((g) => g.code === code);
    return el ? el.label : '';
  };
  const getCountryLabel = (code) => {
    const country = countries.value.find((g) => g.code === code);
    return country ? country.label : '';
  };

  const getCityLabel = (passenger) => {
    const rawCityIso = passenger.city_iso;

    if (!rawCityIso) return '-';

    const cityIso = typeof rawCityIso === 'object' ? rawCityIso.value : rawCityIso;
    const countryCities = my_cities.value?.[passenger.country_iso] || [];
    const city = countryCities.find((c) => c.code === cityIso);

    return city?.label || '-';
  };

  const copyLinkToClipboard = () => {
    let nrofile = filesStore.getFile.fileNumber;
    let lang = localStorage.getItem('lang');

    const link = `${window.url_app}register_paxs/${nrofile}?lang=${lang}`;

    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(link).then(() => {
        notification.success({
          message: 'Éxito',
          description: 'Enlace copiado al portapapeles',
        });
      });
    } else {
      notification.error({
        message: 'Falló',
        description: 'Clipboard API not available on Local',
      });
      console.error('La API de portapapeles no está disponible');
    }

    setTimeout(() => {
      window.open(link);
    }, 350);
  };

  const masi_link = () => {
    let link_;

    if (lang == 'es') {
      link_ = 'https://drive.google.com/file/d/1yZ3GH1tBAmNMLN_gr7D7Ekc_l_LXsqng/view?usp=sharing';
    } else if (lang == 'pt') {
      link_ = 'https://drive.google.com/file/d/1vVOqGVjyOslLLJxM-g1gaHKhg72b20e6/view?usp=sharing';
    } else {
      link_ = 'https://drive.google.com/file/d/1bUxFk2w2aX0wEwJyNc33Z07P2d3wozgE/view?usp=sharing';
    }
    window.open(link_, '_blank');
  };

  const responseFilesFrom = (value, index) => {
    let passengers = filesStore.getFilePassengers;
    passengers[index].document_url = '';

    if (typeof value[0] != 'undefined' && value[0].link != '') {
      passengers[index].document_url = value[0].link;
    }
  };

  const handleGoToModify = () => {
    flag_modify.value = true;
  };

  const closeModify = () => {
    flag_modify.value = false;
  };

  const viewRoomingList = () => {
    showRoomingList.value = true;
  };

  const goBackPassengersPage = () => {
    showRoomingList.value = false;
  };

  const props = defineProps({
    flag_external_link: {
      type: Boolean,
      default: false,
    },
  });

  watch(
    () => filesStore.isLoadingPassengers,
    (newValue) => {
      if (!newValue) {
        getComponents();
      }
    }
  );

  const currentLang = ref('');

  const handleChangeLanguage = (value) => {
    locale.value = value;
    localStorage.setItem('lang', value.toLowerCase());
    languagesStore.setCurrentLanguage(value);
  };

  onMounted(async () => {
    if (props.flag_external_link) {
      currentLang.value = languagesStore.getLanguage;
    }

    getComponents();

    Fancybox.bind('[data-fancybox]');
  });
</script>

<style scoped lang="scss">
  .files-paxs-info {
    min-width: auto;

    &-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 20px 0;
    }

    &-title {
      color: #eb5757;
      font-weight: 400;
      font-size: 48px !important;
      line-height: 55px;
    }

    &-title-info {
      color: #3d3d3d;
      font-weight: 700;
      font-size: 24px !important;
      line-height: 31px;
    }

    &-buttons {
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 10px;

      & :deep(svg) {
        vertical-align: middle;
      }

      base-button {
        display: inline-block;
        padding: 10px 20px;
        font-size: 1rem;
        border-radius: 4px;
        cursor: pointer;
        transition: background-color 0.3s ease;

        & :deep(svg) {
          margin-left: 8px;
        }

        &[danger] {
          border: 2px solid #eb5757;
          color: #eb5757;

          &:hover {
            background-color: #eb5757;
            color: #fff;
          }
        }

        &[type='outline-main'] {
          border: 2px solid #575757;
          color: #575757;

          &:hover {
            background-color: #575757;
            color: #fff;
          }
        }
      }
    }
  }

  .collapse-paxs {
    & :deep(.ant-collapse-header) {
      position: relative;
      font-weight: 700;
      font-size: 18px;
      line-height: 25px;
      color: #3d3d3d;
      padding-left: 40px;
    }

    & :deep(.ant-collapse-content-box) {
      background-color: #fff;
    }

    .panel-extra-icon {
      position: absolute;
      left: 15px;
    }
  }

  .ant-collapse-item:hover {
    background: #e9e9e9 !important;
  }

  .is-mandatory {
    color: #eb5757;
    padding-left: 3px;
  }

  .button-full-width {
    & :deep(.ant-upload) {
      width: 100%;
      font-weight: 600;
      font-size: 14px;
      line-height: 21px;

      & .ant-btn {
        color: #eb5757;
        border: 1px solid #eb5757;
        background-color: #fff;

        &:hover {
          background-color: #eb5757;
          color: #fff;
        }
      }
    }
  }

  .files-paxs-info h4.files-paxs-info-title-info {
    margin-top: 30px;
  }

  .files-paxs-info-header h4 span {
    margin-right: 10px;
  }

  .files-paxs-info-header h4 button {
    margin: 0 10px;
    font-size: 17px;
    height: 42px;
  }

  .files-paxs-info-header h4 .ant-btn-default {
    border-color: #fafafa;
    background-color: #fafafa;
  }

  .files-paxs-info-header h4 .ant-btn-default svg {
    color: #3d3d3d;
  }

  .files-paxs-info-header h4 .ant-btn-default:hover svg {
    color: #eb5757;
  }

  .files-paxs-info-header h4 .ant-btn-default:hover path {
    stroke: #eb5757;
  }

  .files-paxs-info-header h4 .ant-btn-default:hover {
    color: #eb5757;
    border-color: #eb5757;
    background-color: white;
  }

  .files-paxs-info-buttons .ant-btn-dangerous[disabled] {
    opacity: 0.5;
  }

  .sub_download_passengers {
    width: 200px;
    padding: 10px;
  }

  .sub_download_passengers ul {
    list-style-type: none;
    padding: 0;
    margin: 0;
  }

  .sub_download_passengers ul li {
    padding: 10px 0;
    cursor: pointer;
    font-size: 16px;
    color: black;
  }

  .sub_download_passengers ul li.disabled {
    color: lightgray;
    cursor: not-allowed;
  }

  .sub_download_passengers ul li:not(.disabled):hover {
    color: #890005;
  }

  .updload_passengers {
    cursor: pointer;
    display: flex;
    align-items: center;
  }

  #files-layout .files-edit [class^='icon-'],
  #files-layout .files-edit [class*='icon-'] {
    display: block !important;
  }
</style>
