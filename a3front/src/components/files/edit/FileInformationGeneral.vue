<template>
  <loading-skeleton v-if="isLoading" />
  <template v-else>
    <loading-skeleton v-if="isLoadingNoteRequirement" />
    <div
      class="custom-tabs-container"
      v-else-if="(serviceNotesStore.getAllRequirementFileNote.length || 0) > 0"
    >
      <a-tabs v-model:activeKey="activeKey" class="custom-tabs" :tabBarStyle="tabBarStyle">
        <a-tab-pane key="1">
          <!-- Slot personalizado para el título del Tab 1 -->
          <template #tab>
            <div class="custom-tab-header">{{ t('global.label.general_information') }}</div>
          </template>

          <!-- Contenido personalizado del Tab 1 -->
          <!-- <div class="tab-content">
            Contenido del tab 1
          </div> -->
        </a-tab-pane>

        <a-tab-pane key="2">
          <!-- Slot personalizado para el título del Tab 2 -->
          <template #tab>
            <div class="custom-tab-header">
              {{ t('global.label.requirements_coordination') }}
              <a-tag
                :style="{
                  background: activeKey == 2 ? '#E0453D' : '#979797',
                  color: '#FFFFFF',
                  'font-weight': 700,
                  'font-size': '12px',
                }"
                >{{ countRequirement }} {{ t('global.label.requests') }}</a-tag
              >
            </div>
          </template>

          <!-- Contenido personalizado del Tab 2 -->
          <!-- <div class="tab-content">
            contenido del tab 2
          </div> -->
        </a-tab-pane>
      </a-tabs>
    </div>
    <div v-if="activeKey == 1">
      <a-card
        style="width: 100%; border-radius: 6px; border: 1px solid #c4c4c4"
        :bordered="false"
        v-if="props.showNoteClient"
      >
        <a-row>
          <a-col :span="1">
            <a-avatar style="background-color: white; border-color: #e9e9e9" class="text-600"
              >00</a-avatar
            >
          </a-col>
          <a-col :span="1">
            <a-avatar style="background-color: white">
              <svg
                width="15"
                height="16"
                viewBox="0 0 15 16"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M7.50002 8.12293C9.63223 8.12293 11.3603 6.39485 11.3603 4.26264C11.3603 2.13043 9.63223 0.402344 7.50002 0.402344C5.36781 0.402344 3.63973 2.13043 3.63973 4.26264C3.63973 6.39485 5.36781 8.12293 7.50002 8.12293ZM10.2022 9.08801H9.69858C9.02906 9.39562 8.28414 9.57054 7.50002 9.57054C6.7159 9.57054 5.974 9.39562 5.30146 9.08801H4.79782C2.56005 9.08801 0.744507 10.9036 0.744507 13.1413V14.3959C0.744507 15.1951 1.39292 15.8435 2.19212 15.8435H12.8079C13.6071 15.8435 14.2555 15.1951 14.2555 14.3959V13.1413C14.2555 10.9036 12.44 9.08801 10.2022 9.08801Z"
                  fill="#3D3D3D"
                />
              </svg>
            </a-avatar>
          </a-col>
          <a-col
            :span="3"
            style="vertical-align: middle; color: #979797"
            class="text-font-12 flex mt-1"
          >
            Nota de cliente:
          </a-col>
          <a-col :span="19">
            <p style="color: #c4c4c4; font-size: 16px; text-align: justify">
              Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse tristique orci
              sapien, nec consequat risus tempus et. Ut porttitor auctor nisi sed hendrerit. Integer
              posuere, mi vitae dapibus rutrum, nisl mauris viverra nulla, quis porttitor libero
              tortor faucibus neque. Ut viverra fringilla elit, et eleifend tortor. Aenean pretium
              pretium enim, a pellentesque eros posuere eget. Nulla efficitur mauris eu luctus
              lobortis. Suspendisse velit arcu, viverra nec finibus sit amet, convallis eget nisi.
              Sed posuere arcu turpis, vitae luctus elit vehicula sit amet.
            </p>
          </a-col>
        </a-row>
      </a-card>
      <div
        style="background: #fafafa; padding: 20px; border-radius: 6px"
        class="mt-5"
        v-if="!hasEvent && !hasPagingBoard && validateDateFile"
      >
        <a-row class="mt-2">
          <a-col :span="2" class="flex justify-content-center aligns-center">
            <a-avatar
              class="text-600"
              style="background-color: white; color: #3d3d3d; border-color: #e9e9e9"
              >01</a-avatar
            >
          </a-col>
          <a-col :span="1" class="flex justify-content-center aligns-center">
            <svg
              width="20"
              height="19"
              viewBox="0 0 20 19"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M16.6667 7.40107H12.6982L9.04891 1.01424C9.00025 0.929205 8.92998 0.858539 8.84523 0.809394C8.76047 0.760248 8.66424 0.734369 8.56627 0.734375L6.29195 0.734375C5.92285 0.734375 5.65653 1.0875 5.75792 1.44236L7.46036 7.40107H3.88881L2.38881 5.40106C2.28395 5.26113 2.11901 5.17884 1.94436 5.17884H0.555813C0.194353 5.17884 -0.0709257 5.51842 0.0169219 5.86912L1.11102 9.6233L0.0169219 13.3775C-0.0709257 13.7282 0.194353 14.0678 0.555813 14.0678H1.94436C2.11936 14.0678 2.28395 13.9855 2.38881 13.8455L3.88881 11.8455H7.46036L5.75792 17.8039C5.65653 18.1588 5.92285 18.5122 6.29195 18.5122H8.56627C8.76557 18.5122 8.9496 18.4053 9.04856 18.2324L12.6982 11.8455H16.6667C17.8941 11.8455 20 10.8507 20 9.6233C20 8.39587 17.8941 7.40107 16.6667 7.40107Z"
                fill="#3D3D3D"
              />
            </svg>
          </a-col>
          <a-col :span="5" class="flex justify-content-center aligns-center">
            <div style="color: #979797" class="text-font-12">
              {{ t('global.message.issuance_of_airline_tickets') }}
            </div>
          </a-col>
          <a-col :span="5" class="flex direction-column justify-content-center aligns-start">
            <div>
              <span class="text-600 text-font-12">{{ t('global.label.internationals') }}: </span>
              <span style="color: #979797" class="text-font-12">{{
                t('global.label.client')
              }}</span>
            </div>
            <div>
              <span class="text-600 text-font-12">{{ t('global.label.nationals') }}: </span>
              <span style="color: #979797" class="text-font-12">LIMA TOURS</span>
            </div>
          </a-col>
          <template v-if="isVip">
            <a-col :span="2" class="flex justify-content-center aligns-center">
              <a-avatar
                class="text-600"
                style="background-color: white; color: #3d3d3d; border-color: #e9e9e9"
                >02</a-avatar
              >
            </a-col>
            <a-col :span="1" class="flex justify-content-center aligns-center" v-if="isVip">
              <span class="group-vip">
                <font-awesome-icon v-if="isVip" class="is-vip" icon="fa-solid fa-star" />
                <font-awesome-icon v-else icon="fa-regular fa-star" />
              </span>
            </a-col>
            <a-col :span="4" class="flex justify-content-center aligns-center" v-if="isVip">
              <div>{{ t('global.message.file_with_vip_treatment') }} “{{ lastVip?.vipName }}”</div>
            </a-col>
          </template>
          <a-col :span="4"></a-col>
        </a-row>
      </div>
      <div
        style="background: #fafafa; padding: 30px; border-radius: 6px; border: 1px solid #fafafa"
        class="mt-5"
        v-if="(!hasEvent || !hasPagingBoard) && validateDateFile"
      >
        <p style="font-size: 14px; font-weight: 500; color: #3d3d3d">
          {{ t('global.message.complete_the_fields_if_you_require_them_to_appear_in_the_file') }}
        </p>
        <a-row class="mt-4" :gutter="[18, 18]">
          <a-col :span="hasPagingBoard ? 24 : 12" v-if="!hasEvent">
            <a-card
              :style="{
                'border-radius': '6px',
                background: '#ffffff',
                border:
                  errors.date_event || errors.type_event || errors.description_event
                    ? '1px solid #eb5757'
                    : '1px solid #e9e9e9 !important',
              }"
            >
              <a-row class="flex justify-content-center aligns-center">
                <a-col :span="3">
                  <a-avatar
                    class="text-600"
                    style="background-color: white; color: #3d3d3d; border-color: #e9e9e9"
                    >03</a-avatar
                  >
                </a-col>
                <a-col :span="21">
                  <a-checkbox v-model:checked="checkEventSpecial" @change="changeEventSpecial">{{
                    t('global.message.special_event_on_the_trip')
                  }}</a-checkbox>
                </a-col>
              </a-row>
              <a-row
                class="ant-form-item mt-3"
                :class="{ 'ant-form-item-has-error': errors.date_event }"
                v-show="checkEventSpecial"
              >
                <a-col :span="8">
                  <label class="ant-form-item-label"
                    >{{ t('global.message.event_day') }}<span>*</span></label
                  >
                </a-col>
                <a-col :span="16" class="ant-form-item-control">
                  <a-select
                    v-model:value="formData.date_event"
                    placeholder="Selecciona"
                    @blur="validateField('date_event')"
                    :status="errors.date_event ? 'error' : undefined"
                    style="width: 100%"
                    v-model="formData.date_event"
                  >
                    <a-select-option value="" selected hidden>{{
                      t('global.label.select')
                    }}</a-select-option>
                    <a-select-option v-for="date in availableDates" :key="date" :value="date">
                      {{ formatDate(date) }}
                    </a-select-option>
                  </a-select>
                </a-col>
                <a-col :span="8" class="flex justify-content-center"></a-col>
                <a-col
                  :span="16"
                  v-if="errors.date_event"
                  class="ant-form-item-explain ant-form-item-explain-error"
                >
                  <span class="error-message">{{ errors.date_event }}</span>
                </a-col>

                <!-- <a-col :span="16" class="gutter-row">
                  <a-select placeholder="Selecciona" style="width: 100%">
                    <a-select-option value="" selected hidden>Selecciona</a-select-option>
                    <a-select-option v-for="item, key in availableDates" :value="item" :key="key">{{ formatDate(item) }}</a-select-option>
                  </a-select>
                </a-col> -->
              </a-row>
              <a-row
                class="ant-form-item mt-3"
                :class="{ 'ant-form-item-has-error': errors.type_event }"
                v-show="checkEventSpecial"
              >
                <a-col :span="8">
                  <label class="ant-form-item-label"
                    >{{ t('global.label.event_type') }} <span>*</span></label
                  >
                </a-col>
                <a-col :span="16" class="ant-form-item-control">
                  <a-input
                    v-model:value="formData.type_event"
                    placeholder="Escribe el tipo de evento"
                    @blur="validateField('type_event')"
                    :status="errors.type_event ? 'error' : undefined"
                  />
                </a-col>
                <a-col :span="8"></a-col>
                <a-col
                  :span="16"
                  v-if="errors.type_event"
                  class="ant-form-item-explain ant-form-item-explain-error"
                >
                  <span class="error-message">{{ errors.type_event }}</span>
                </a-col>
              </a-row>
              <a-row
                class="ant-form-item mt-3"
                :class="{ 'ant-form-item-has-error': errors.description_event }"
                v-show="checkEventSpecial"
              >
                <a-col :span="8">
                  <label class="ant-form-item-label"
                    >{{ t('global.label.specify') }} <span>*</span></label
                  >
                </a-col>
                <a-col :span="16" class="ant-form-item-control">
                  <a-textarea
                    v-model:value="formData.description_event"
                    placeholder="Descripción del evento"
                    :rows="4"
                    show-count
                    :maxlength="500"
                    @blur="validateField('description_event')"
                    :status="errors.description_event ? 'error' : undefined"
                  />
                </a-col>
                <a-col :span="8"></a-col>
                <a-col
                  :span="16"
                  v-if="errors.description_event"
                  class="ant-form-item-explain ant-form-item-explain-error"
                >
                  <span class="error-message">{{ errors.description_event }}</span>
                </a-col>
              </a-row>
            </a-card>
          </a-col>
          <a-col :span="hasEvent ? 24 : 12" v-if="!hasPagingBoard">
            <a-card
              :style="{
                'border-radius': '6px',
                background: '#ffffff',
                border: notExitsFile ? '1px solid #eb5757' : '1px solid #e9e9e9',
              }"
            >
              <a-row class="flex justify-content-center aligns-center">
                <a-col :span="3">
                  <a-avatar class="text-600" style="background-color: white; border-color: #e9e9e9"
                    >04</a-avatar
                  >
                </a-col>
                <a-col :span="21">
                  <a-checkbox v-model:checked="checkPagingBoard" @change="changePagingBoard">{{
                    t('global.message.paging_board_special') + ' ' + t('global.message.agency_logo')
                  }}</a-checkbox>
                </a-col>
              </a-row>
              <a-row class="mt-3" v-show="checkPagingBoard">
                <a-col :span="24" style="display: flex; justify-content: flex-end">
                  <file-upload-general
                    v-bind:folder="'communications'"
                    v-bind:multiple="false"
                    @onResponseFiles="responseFilesFrom"
                  />
                </a-col>
                <a-col
                  :span="24"
                  v-if="notExitsFile"
                  class="ant-form-item-explain ant-form-item-explain-error flex justify-content-center mt-3"
                >
                  <span class="error-message">{{ t('global.label.required_file') }}</span>
                </a-col>
              </a-row>
            </a-card>
          </a-col>
        </a-row>
        <a-row class="mt-2" v-if="checkEventSpecial || checkPagingBoard">
          <a-col :span="24" style="display: flex; justify-content: flex-end">
            <a-button
              type="primary"
              class="base-button"
              @click="handleSubmit"
              :loading="isloadingSaveOrUpdate"
            >
              <span :style="{ 'margin-left': isloadingSaveOrUpdate ? '10px' : '0px' }">
                {{ t('global.button.save') }}
              </span>
            </a-button>
          </a-col>
        </a-row>
      </div>

      <a-card
        style="width: 100%; border-radius: 6px !important"
        class="mt-3"
        v-if="
          hasEvent ||
          hasPagingBoard ||
          (serviceNotesStore?.getExternalHousing?.length || 0) > 0 ||
          (serviceNotesStore?.getAllFileNote?.additional_information?.length || 0) > 0 ||
          Object.entries(serviceNotesStore?.getAllFileNote?.for_service || []).length > 0 ||
          !validateDateFile
        "
      >
        <a-row>
          <a-col :span="24">
            <div class="title-info">{{ t('global.label.general_information') }}</div>
          </a-col>
        </a-row>
        <a-row class="border-b mt-5 pb-4" v-if="hasEvent || hasPagingBoard || !validateDateFile">
          <a-col :span="1" class="flex justify-content-center aligns-center">
            <svg
              width="20"
              height="19"
              viewBox="0 0 20 19"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M16.6667 7.40107H12.6982L9.04891 1.01424C9.00025 0.929205 8.92998 0.858539 8.84523 0.809394C8.76047 0.760248 8.66424 0.734369 8.56627 0.734375L6.29195 0.734375C5.92285 0.734375 5.65653 1.0875 5.75792 1.44236L7.46036 7.40107H3.88881L2.38881 5.40106C2.28395 5.26113 2.11901 5.17884 1.94436 5.17884H0.555813C0.194353 5.17884 -0.0709257 5.51842 0.0169219 5.86912L1.11102 9.6233L0.0169219 13.3775C-0.0709257 13.7282 0.194353 14.0678 0.555813 14.0678H1.94436C2.11936 14.0678 2.28395 13.9855 2.38881 13.8455L3.88881 11.8455H7.46036L5.75792 17.8039C5.65653 18.1588 5.92285 18.5122 6.29195 18.5122H8.56627C8.76557 18.5122 8.9496 18.4053 9.04856 18.2324L12.6982 11.8455H16.6667C17.8941 11.8455 20 10.8507 20 9.6233C20 8.39587 17.8941 7.40107 16.6667 7.40107Z"
                fill="#3D3D3D"
              />
            </svg>
          </a-col>
          <a-col :span="5" class="flex aligns-center">
            <div style="color: #979797" class="text-font-12">
              {{ t('global.message.issuance_of_airline_tickets') }}
            </div>
          </a-col>
          <a-col :span="18" class="flex direction-column justify-content-center aligns-start">
            <div>
              <span class="text-500 text-font-12">{{ t('global.label.internationals') }}: </span>
              <span style="color: #979797">{{ t('global.label.client') }}</span>
            </div>
            <div>
              <span class="text-500 text-font-12">{{ t('global.label.nationals') }}: </span>
              <span style="color: #979797">LIMA TOURS</span>
            </div>
          </a-col>
        </a-row>

        <a-row class="border-b mt-5 pb-4" v-if="isVip">
          <a-col :span="1" class="flex justify-content-center aligns-center">
            <span class="group-vip">
              <font-awesome-icon v-if="isVip" class="is-vip" icon="fa-solid fa-star" />
              <font-awesome-icon v-else icon="fa-regular fa-star" />
            </span>
          </a-col>
          <a-col :span="23" class="flex">
            <div style="color: #979797" class="text-font-12" v-if="isVip">
              {{ t('global.message.file_with_vip_treatment') }} “{{ lastVip?.vipName }}”
            </div>
          </a-col>
        </a-row>

        <a-row class="border-b mt-5 pb-4" v-if="hasEvent">
          <a-col :span="1" class="flex justify-content-center aligns-center">
            <span class="group-vip">
              <svg
                width="12"
                height="21"
                viewBox="0 0 12 21"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M4.74996 13.0038V19.1246L5.61014 20.4144C5.79569 20.6926 6.20468 20.6926 6.39023 20.4144L7.25001 19.1246V13.0038C6.84414 13.0788 6.42734 13.1233 5.99999 13.1233C5.57264 13.1233 5.15583 13.0788 4.74996 13.0038ZM5.99999 0.623047C2.89329 0.623047 0.374878 3.14146 0.374878 6.24816C0.374878 9.35486 2.89329 11.8733 5.99999 11.8733C9.10669 11.8733 11.6251 9.35486 11.6251 6.24816C11.6251 3.14146 9.10669 0.623047 5.99999 0.623047ZM5.99999 3.59185C4.53512 3.59185 3.34369 4.78328 3.34369 6.24816C3.34369 6.50676 3.13353 6.71692 2.87493 6.71692C2.61633 6.71692 2.40617 6.50676 2.40617 6.24816C2.40617 4.26648 4.0187 2.65434 5.99999 2.65434C6.25859 2.65434 6.46875 2.8645 6.46875 3.1231C6.46875 3.38169 6.25859 3.59185 5.99999 3.59185Z"
                  fill="#3D3D3D"
                />
              </svg>
            </span>
          </a-col>
          <a-col :span="5" class="flex aligns-center">
            <div style="color: #979797" class="text-font-12">
              {{ t('global.label.special_event') }}
            </div>
          </a-col>

          <a-col
            :span="18"
            class="flex direction-column justify-content-center aligns-start"
            :class="{
              'action-updated': getServiceNoteGeneral.isCreatedEvent,
              'action-padding': getServiceNoteGeneral.isCreatedEvent,
            }"
          >
            <div style="position: absolute; z-index: 999; top: -20px; padding: 0 5px; right: -5px">
              <a-badge-ribbon
                :color="'#EB5757'"
                class="cursor-pointer"
                v-if="getServiceNoteGeneral.isCreatedEvent"
                @click="toggleViewStatus()"
              >
                <template #text>
                  <span>
                    <a-tooltip v-if="false">
                      <template #title>
                        <small
                          >{{ t('global.label.create') }} #{{ getServiceNoteGeneral.id }}</small
                        >
                      </template>
                      <font-awesome-icon :icon="['fas', 'bolt']" />
                    </a-tooltip>
                  </span>
                </template>
              </a-badge-ribbon>
            </div>
            <a-row style="width: 100%">
              <a-col :span="4" class="text-500 text-font-12"
                >{{ t('global.label.event_date') }}:
              </a-col>
              <a-col :span="20" style="color: #979797" class="text-font-12"
                >{{ t('global.label.day') }} {{ getDayNumber(getServiceNoteGeneral.date_event) }} -
                {{ formatDate(getServiceNoteGeneral.date_event) }}</a-col
              >
            </a-row>
            <a-row style="width: 100%" class="mt-1">
              <a-col :span="4" class="text-500 text-font-12"
                >{{ t('global.label.event_type') }}:
              </a-col>
              <a-col :span="20" style="color: #979797" class="text-font-12">{{
                getServiceNoteGeneral.type_event
              }}</a-col>
            </a-row>
            <a-row style="width: 100%" class="mt-1">
              <a-col :span="4" class="text-500 text-font-12"
                >{{ t('global.column.description') }}:
              </a-col>
              <a-col :span="20" style="color: #979797" class="text-font-12">{{
                getServiceNoteGeneral.description_event
              }}</a-col>
            </a-row>
          </a-col>
        </a-row>
        <a-row class="border-b mt-5 pb-4" v-if="hasPagingBoard">
          <a-col :span="1" class="flex justify-content-center aligns-center">
            <span class="group-vip">
              <font-awesome-icon icon="fa-solid fa-link" />
            </span>
          </a-col>
          <a-col :span="5" class="flex aligns-center">
            <div style="color: #979797" class="text-font-12">
              {{ t('global.message.paging_board_special') }}:
            </div>
          </a-col>

          <a-col
            :span="18"
            class="flex aligns-center"
            :class="{
              'action-updated': getServiceNoteGeneral.isCreatedImage,
              'action-padding': getServiceNoteGeneral.isCreatedImage,
            }"
          >
            <div style="position: absolute; z-index: 999; top: -20px; padding: 0 5px; right: -5px">
              <a-badge-ribbon
                :color="'#EB5757'"
                class="cursor-pointer"
                v-if="getServiceNoteGeneral.isCreatedImage"
                @click="toggleViewStatus()"
              >
                <template #text>
                  <span>
                    <a-tooltip>
                      <template #title>
                        <small
                          >{{ t('global.label.create') }} #{{ getServiceNoteGeneral.id }}</small
                        >
                      </template>
                      <template v-if="true">
                        <font-awesome-icon :icon="['far', 'circle-check']" />
                      </template>
                    </a-tooltip>
                  </span>
                </template>
              </a-badge-ribbon>
            </div>
            <div>
              <img
                :src="getServiceNoteGeneral.image_logo"
                alt=""
                style="width: 60px; height: 60px; object-fit: cover"
              />
            </div>
            <div>
              <div style="color: #1890ff; font-size: 14px; margin-left: 10px">
                {{ extractFileName(getServiceNoteGeneral.image_logo) }}
              </div>
            </div>
          </a-col>
        </a-row>
        <!-- ALOJAMIENTO EXTERNO -->
        <template v-if="serviceNotesStore.isLoadingExternalHousing">
          <loading-skeleton />
        </template>
        <div v-else-if="(serviceNotesStore?.getExternalHousing?.length || 0) > 0">
          <a-row class="border-b mt-2 pb-4">
            <a-col :span="1" class="flex justify-content-center">
              <font-awesome-icon icon="fa-solid fa-hotel" style="padding-top: 12px" />
            </a-col>
            <a-col :span="5" class="flex">
              <div style="color: #979797" class="pt-2">
                {{ t('global.label.external_housing') }}
              </div>
            </a-col>
            <a-col :span="18">
              <div class="pt-2" v-for="item in getExternalHousing || []" :key="item.id">
                <ListExternalHousting
                  :amount="serviceNotesStore?.getExternalHousing?.length || 0"
                  :item="item"
                  :fileId="fileStore.file.id"
                  @edit="handleEditFromChild"
                  :revisionStages="fileStore.getFile.revisionStages"
                />
              </div>
            </a-col>
          </a-row>
        </div>
        <!-- {{ serviceNotesStore?.getAllFileNote || [] }} -->
        <template v-if="serviceNotesStore.isLoadingNote">
          <loading-skeleton />
        </template>
        <div
          v-else-if="(serviceNotesStore?.getAllFileNote?.additional_information?.length || 0) > 0"
        >
          <!-- INFORMACION ADICIONAL -->
          <a-row class="border-b mt-4 pb-4">
            <a-col :span="1" class="flex justify-content-center">
              <font-awesome-icon :icon="['fas', 'plus']" style="padding-top: 12px" />
            </a-col>
            <a-col :span="5" class="flex">
              <div style="color: #979797" class="pt-2">
                {{ t('global.label.additional_information') }}
              </div>
            </a-col>
            <a-col :span="18">
              <div
                v-for="(item, key) in serviceNotesStore?.getAllFileNote?.additional_information ||
                []"
                :key="item.id"
              >
                <hr v-if="key !== 0" style="border: 0; height: 1px; background-color: #e9e9e9" />
                <InformationAditionalNotes
                  :item="item"
                  :fileId="fileStore.file.id"
                  :fileNumber="fileStore.getFile.fileNumber"
                  :revisionStages="fileStore.getFile.revisionStages"
                />
              </div>
            </a-col>
          </a-row>
        </div>
        <!-- INFO POR CIUDADES -->
        <template v-if="serviceNotesStore.loadingNote">
          <loading-skeleton />
        </template>
        <div
          v-else-if="
            Object.entries(serviceNotesStore?.getAllFileNote?.for_service || []).length > 0
          "
        >
          <!-- INFORMACION ADICIONAL -->
          <div>
            <a-row class="pb-2 mt-4">
              <a-col
                :span="24"
                style="color: #c4c4c4; font-size: 18px; font-weight: 700; margin-left: 20px"
                >{{ t('global.message.information_by_city') }}</a-col
              >
            </a-row>
            <a-row class="pb-2">
              <a-col :span="24">
                <div v-for="(cityData, cityName) in dataCities" :key="item" class="pt-3">
                  <CityInformationNotes
                    :item="cityData"
                    :city="cityName"
                    :fileId="fileStore.file.id"
                    :fileNumber="fileStore.getFile.fileNumber"
                    :dateIn="fileStore.getFile.dateIn"
                    :revisionStages="fileStore.getFile.revisionStages"
                  />
                </div>
              </a-col>
            </a-row>
          </div>
        </div>
      </a-card>
    </div>
    <div v-else>
      <template v-if="isLoadingNoteRequirement">
        <loading-skeleton />
      </template>
      <div v-else>
        <RequirementsCoordinator />
      </div>
    </div>
  </template>

  <!-- <ModalNotes :open-modal="modalNotesOpen" @close-modal="handleCloseModal" :item="itemEdit" :type="typeItem"/> -->
</template>
<script setup>
  import { storeToRefs } from 'pinia';
  import { computed, onMounted, ref } from 'vue';
  import dayjs from 'dayjs';
  import { useFilesStore, useServiceNotesStore } from '@/stores/files';
  import { useFormValidator } from '@/utils/formValidator';
  import FileUploadGeneral from '@/components/global/FileUploadGeneralComponent.vue';
  import ListExternalHousting from '@/components/files/notes/ListExternalHousting.vue';
  import InformationAditionalNotes from '@/components/files/notes/InformationAditionalNotes.vue';
  import CityInformationNotes from '@/components/files/notes/CityInformationNotes.vue';
  import RequirementsCoordinator from '@/components/files/notes/RequirementsCoordinator.vue';
  import LoadingSkeleton from '@/components/global/LoadingSkeleton.vue';

  import { getUserId, getUserName, getUserCode } from '@/utils/auth';
  import { useSocketsStore } from '@/stores/global';
  // import ModalNotes from '@/components/files/notes/ModalNotes.vue';
  import { useI18n } from 'vue-i18n';
  const { t } = useI18n({
    useScope: 'global',
  });

  const fileStore = useFilesStore();
  const serviceNotesStore = useServiceNotesStore();
  const socketsStore = useSocketsStore();
  const {
    isLoading,
    isloadingSaveOrUpdate,
    getServiceNoteGeneral,
    hasEvent,
    hasPagingBoard,
    getExternalHousing,
    isLoadingNoteRequirement,
  } = storeToRefs(serviceNotesStore);

  const loadServiceNotes = async () => {
    await serviceNotesStore.fetchServiceNoteGeneral({ file_id: fileStore.file.id });
    checkEventSpecial.value = !hasEvent.value;
    checkPagingBoard.value = !hasPagingBoard.value;
  };

  const loadExternalHousing = async () => {
    await serviceNotesStore.listExternalHousing({ file_id: fileStore.file.id });
  };

  const loadAllFileNotes = async () => {
    await serviceNotesStore.fetchAllFileNotes({ file_id: fileStore.file.id });
  };

  const loadAllRequirementNotes = async () => {
    await serviceNotesStore.fetchAllRequirementFileNotes({ file_id: fileStore.file.id });
  };

  onMounted(() => {
    loadServiceNotes();
    loadExternalHousing();
    loadAllFileNotes();
    loadAllRequirementNotes();
  });

  const props = defineProps({
    showNoteClient: {
      type: Boolean,
      required: true,
    },
    initialValues: {
      type: Object,
      default: () => ({
        date_event: '',
        type_event: '',
        description_event: '',
      }),
    },
    isEditing: {
      type: Boolean,
      default: false,
    },
  });

  // CARD TAB
  const activeKey = ref('1');

  const checkEventSpecial = ref(true);
  const checkPagingBoard = ref(true);
  const isVip = computed(() => fileStore.getFile.vips.length > 0);
  const saveActive = ref(true);
  const filesFrom = ref([]);
  const notExitsFile = ref(false);

  const messages = computed(() => {
    return {
      required_date: t('global.message.required_date'),
      required_type_of_event: t('global.message.required_type_of_event'),
      required_description: t('global.message.required_description'),
      minimum_three_characters: t('global.message.minimum_three_characters'),
      maximum_fifty_characters: t('global.message.maximum_fifty_characters'),
      added_a_special_event_and_special_paging_board: t(
        'global.message.added_a_special_event_and_special_paging_board'
      ),
      special_event_has_been_added: t('global.message.special_event_has_been_added'),
      special_paging_board_added: t('global.message.special_paging_board_added'),
      saved_successfully: t('global.message.saved_successfully'),
    };
  });

  const availableDates = computed(() => {
    return [...new Set(fileStore.getFileItineraries.map((item) => item.date_in))];
  });

  const lastVip = computed(() => {
    return fileStore.getFile?.vips?.at(-1);
  });

  const validateDateFile = computed(() => {
    if (!fileStore.file?.dateIn) return false;

    const dateFile = dayjs(fileStore.file.dateIn);
    const dateNow = dayjs();
    const dateFileMinus72Hours = dateFile.subtract(72, 'hour');

    return dateFileMinus72Hours.isAfter(dateNow);
  });

  const dataCities = computed(() => {
    // 1. Obtener el orden deseado de ciudades
    const itineraryCities = [
      ...new Set(
        fileStore.getFileItineraries.map((item) => item.city_in_name?.toUpperCase()).filter(Boolean)
      ),
    ];

    // 2. Obtener el objeto original
    const forService = serviceNotesStore?.getAllFileNote?.for_service || {};

    // 3. Crear un nuevo objeto ordenado
    const orderedResult = {};

    // 4. Primero agregar las ciudades en el orden del itinerario
    itineraryCities.forEach((city) => {
      const originalKey = Object.keys(forService).find(
        (key) => key.toUpperCase() === city.toUpperCase()
      );
      if (originalKey && forService[originalKey]) {
        orderedResult[originalKey] = forService[originalKey];
      }
    });

    // 5. Luego agregar las ciudades restantes que no estaban en el itinerario
    Object.keys(forService).forEach((city) => {
      if (!itineraryCities.includes(city.toUpperCase())) {
        orderedResult[city] = forService[city];
      }
    });

    return orderedResult;
  });

  const eventValidations = {
    date_event: [
      (value) => !!value || messages.value.required_date,
      (value) => availableDates.value.includes(value) || 'Fecha no válida',
    ],
    type_event: [
      (value) => !!value || messages.value.required_type_of_event,
      (value) => value.length >= 3 || messages.value.minimum_three_characters,
      (value) => value.length <= 50 || messages.value.maximum_fifty_characters,
    ],
    description_event: [
      (value) => !!value || messages.value.required_description,
      (value) => value.length >= 3 || messages.value.minimum_three_characters,
      (value) => value.length <= 500 || messages.value.maximum_fifty_characters,
    ],
  };

  const { formData, errors, validateField, validateForm, resetForm } = useFormValidator(
    props.initialValues,
    eventValidations
  );

  const formatDate = (dateString) => {
    if (!dateString) return '';

    // Dividir la fecha por guiones
    const [year, month, day] = dateString.split('-');

    // Devolver en nuevo formato
    return `${day}/${month}/${year}`;
  };

  const getDayNumber = (date) => {
    if (!date) return '';
    const index = availableDates.value.indexOf(date);
    return index !== -1 ? index + 1 : null;
  };

  const extractFileName = (url) => {
    if (!url) return '';
    return url.split('/').pop();
  };

  const responseFilesFrom = (files) => {
    filesFrom.value = files.map((item) => item.link);
    saveActive.value = false;
    notExitsFile.value = false;
  };

  const changeEventSpecial = () => {
    if (checkEventSpecial.value) {
      resetForm();
    }
  };

  const changePagingBoard = () => {
    notExitsFile.value = false;
  };

  const saveForm = async (data) => {
    if ((getServiceNoteGeneral.value?.id || '') === '') {
      const response = await serviceNotesStore.createNoteGeneral({
        file_id: fileStore.file.id,
        data: data,
      });
      const type = 'create_note_general';
      const message = messages.value.saved_successfully;
      let description = '';
      if (data.isEvent && data.isImage) {
        description = messages.value.added_a_special_event_and_special_paging_board;
      } else {
        if (data.isEvent) {
          description = messages.value.special_event_has_been_added;
        }
        if (data.isImage) {
          description = messages.value.special_paging_board_added;
        }
      }
      sendSocketMessage(response.success, type, message, description, {
        id: response.data.id,
        isEvent: data.isEvent,
        isImage: data.isImage,
      });
    } else {
      const response = await serviceNotesStore.updateNoteGeneral({
        note_id: getServiceNoteGeneral.value.id,
        file_id: fileStore.file.id,
        data: data,
      });
      const type = 'create_note_general';
      const message = messages.value.saved_successfully;
      let description = '';
      if (data.isEvent) {
        description = messages.value.special_event_has_been_added;
      }
      if (data.isImage) {
        description = messages.value.special_paging_board_added;
      }
      sendSocketMessage(response.success, type, message, description, {
        id: getServiceNoteGeneral.value.id,
        isEvent: data.isEvent,
        isImage: data.isImage,
      });
    }
    checkEventSpecial.value = !data.isEvent;
    checkPagingBoard.value = !data.isImage;
    // await loadServiceNotes();
    // aqui el websockets
    await resetForm();
    notExitsFile.value = false;
  };

  const emit = defineEmits(['submit']);

  const handleSubmit = () => {
    // console.log(fileStore.file.isVip);
    console.log(getServiceNoteGeneral.value);
    console.log('evento', hasEvent.value);
    console.log('paging board', hasPagingBoard.value);
    console.log('check evento', checkEventSpecial.value);
    console.log('check paging board', checkPagingBoard.value);

    const sendData = {
      date_event: '',
      type_event: '',
      description_event: '',
      image_logo: '',
      created_by: getUserId(),
      created_by_code: getUserCode(),
      created_by_name: getUserName(),
      isEvent: false,
      isImage: false,
    };

    if (checkEventSpecial.value && checkPagingBoard.value) {
      if (validateForm() && typeof filesFrom.value[0] !== 'undefined') {
        emit('submit', formData.value);
        sendData.date_event = formData.value.date_event;
        sendData.type_event = formData.value.type_event;
        sendData.description_event = formData.value.description_event;
        sendData.image_logo = filesFrom.value[0];
        sendData.isEvent = true;
        sendData.isImage = true;
        console.log('ENVIA TODO EL FORMULARIO', sendData);
        saveForm(sendData);
      } else {
        if (typeof filesFrom.value[0] === 'undefined') {
          notExitsFile.value = true;
        }
      }

      return;
    }

    if (checkEventSpecial.value || checkPagingBoard.value) {
      if (checkEventSpecial.value && validateForm()) {
        emit('submit', formData.value);
        sendData.date_event = formData.value.date_event;
        sendData.type_event = formData.value.type_event;
        sendData.description_event = formData.value.description_event;
        sendData.isEvent = true;
        sendData.isImage = false;
        console.log('ENVIA EL EVENTO', sendData);
        saveForm(sendData);
        // AQUI ENVIARA LA DATA
        return;
      }

      if (checkPagingBoard.value) {
        if (typeof filesFrom.value[0] === 'undefined') {
          notExitsFile.value = true;
        } else {
          sendData.image_logo = filesFrom.value[0];
          sendData.isEvent = false;
          sendData.isImage = true;
          // AQUI ENVIARA LA DATA
          console.log('ENVIA LA IMAGEN', sendData);
          saveForm(sendData);
          return;
        }
      }
    }
  };

  const countRequirement = computed(() => {
    return serviceNotesStore.getAllRequirementFileNote.length || 0;
  });

  const sendSocketMessage = (success, type, message, description, data = {}) => {
    socketsStore.send({
      success,
      type: type,
      file_number: fileStore.file.id,
      file_id: fileStore.file.id,
      user_code: getUserCode(),
      user_id: getUserId(),
      message: message,
      description: description,
      isEvent: data.isEvent,
      isImage: data.isImage,
    });
  };

  const toggleViewStatus = () => {
    getServiceNoteGeneral.value.isCreatedEvent = false;
    getServiceNoteGeneral.value.isCreatedImage = false;
    serviceNotesStore.changeFlagChange();
  };
</script>

<style scoped>
  .text-500 {
    font-weight: 500 !important;
    color: #575757;
  }

  .text-600 {
    font-weight: 500 !important;
    color: #3d3d3d;
  }
  .text-font-12 {
    font-size: 12px;
  }
  .icon-number {
    background-color: white;
    color: #3d3d3d;
    border-color: #e9e9e9;
  }
  .title-info {
    color: #979797;
    font-weight: 700;
    font-size: 18px;
    font-size: Monserat;
  }
  .flex {
    display: flex;
  }
  .justify-content-center {
    justify-content: center;
  }
  .justify-end {
    justify-content: flex-end;
  }
  .direction-column {
    flex-direction: column;
  }
  .aligns-center {
    align-items: center;
  }
  .aligns-start {
    align-items: flex-start;
  }

  .border-b {
    border-bottom: 1px solid #e9e9e9;
  }

  .group-vip {
    cursor: pointer;
    transition: 0.3s ease all;
    .is-vip {
      color: #ffb001;
    }

    &:hover {
      color: #ffb001;
    }
  }

  .custom-ant-form {
    max-width: 600px;
    margin: 0 auto;
    padding: 20px;
  }

  /* Estilos para mantener la apariencia de Ant Design */
  .ant-form-item {
    margin-bottom: 16px;
  }

  .ant-form-item-label > label {
    color: rgba(0, 0, 0, 0.88);
    font-size: 14px;
  }

  .ant-form-item-explain-error {
    color: var(--files-exclusives);
    font-size: 14px;
  }

  /* Estilos para campos con error */
  :deep(.ant-input-status-error),
  :deep(.ant-select-selector-status-error),
  :deep(.ant-input-textarea-status-error) {
    border-color: var(--files-exclusives) !important;
  }

  /* Botón deshabilitado */
  :deep(.ant-btn[disabled]) {
    background-color: #f5f5f5 !important;
    border-color: #d9d9d9 !important;
    color: rgba(0, 0, 0, 0.25) !important;
  }

  .base-button.ant-btn {
    font-weight: 600;
    font-size: 14px;
    line-height: 21px;
    letter-spacing: 0.015em;
    border-radius: 6px;
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
  :deep(.ant-card-body) {
    border: none !important;
  }

  /* Contenedor principal de los tabs */
  .custom-tabs {
    width: 100%;
  }

  /* Override de estilos de Ant Design */
  :deep(.custom-tabs .ant-tabs-nav) {
    width: 100%;
    margin: 0;
  }

  /* Cada item del tab al 50% */
  :deep(.custom-tabs .ant-tabs-nav-list) {
    display: flex;
    width: 100%;
  }

  :deep(.custom-tabs .ant-tabs-tab) {
    flex: 1;
    display: flex;
    justify-content: center;
    margin: 0;
    padding: 1rem 1rem !important;
    width: 50%; /* Cada tab ocupa exactamente el 50% */
  }
  .custom-tabs-container {
    margin-top: 50px;
  }

  /* Estilo para el header personalizado */
  .custom-tab-header {
    display: flex;
    align-items: center;
    gap: 8px;
  }

  /* Estilo para el tag de requerimientos */
  .requirement-tag {
    margin-left: 8px;
  }

  /* Indicador del tab activo (opcional personalización) */
  :deep(.custom-tabs .ant-tabs-ink-bar) {
    width: 50% !important; /* Asegura que el indicador también ocupe el 50% */
  }

  /* Contenido de los tabs */
  .tab-content {
    padding: 20px;
  }

  .action-updated {
    position: relative;
    border: 1px solid #eb5757;
    border-radius: 7px;
  }

  .action-padding {
    padding: 10px 0 10px 15px;
  }

  .border-gray {
    border: 1px solid #e9e9e9;
  }
</style>
