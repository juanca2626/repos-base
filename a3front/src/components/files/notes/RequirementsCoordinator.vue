<template>
  <a-skeleton active v-if="serviceNotesStore.isLoadingNote" />
  <a-card v-else class="requirements-coordinator" :bordered="false">
    <div style="display: flex">
      <!-- {{ data }} -->
      <div style="width: 100%; min-height: 700px">
        <a-tabs
          v-model:activeKey="activeKey"
          :tab-position="mode"
          :style="{ width: '100%', height: '100%' }"
          @tabScroll="callback"
          class="tab-custom"
          @change="handleTabChange"
        >
          <a-tab-pane v-for="i in locations" :key="i">
            <template #tab>
              <div class="custom-tab-header">
                <div class="icon">
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="18"
                    height="18"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    class="feather feather-map-pin"
                    v-if="activeKey !== i"
                  >
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                    <circle cx="12" cy="10" r="3"></circle>
                  </svg>
                  <svg
                    v-else
                    width="18"
                    height="18"
                    viewBox="0 0 24 24"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                  >
                    <path
                      d="M21 10C21 17 12 23 12 23C12 23 3 17 3 10C3 7.61305 3.94821 5.32387 5.63604 3.63604C7.32387 1.94821 9.61305 1 12 1C14.3869 1 16.6761 1.94821 18.364 3.63604C20.0518 5.32387 21 7.61305 21 10Z"
                      fill="#575757"
                    />
                    <path
                      d="M12 13C13.6569 13 15 11.6569 15 10C15 8.34315 13.6569 7 12 7C10.3431 7 9 8.34315 9 10C9 11.6569 10.3431 13 12 13Z"
                      fill="#575757"
                      stroke="#FAFAFA"
                      stroke-width="2"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                    />
                  </svg>
                </div>
                <div class="text">
                  {{ i }}
                </div>
              </div>
            </template>
            <div style="width: 100%; padding: 20px 40px">
              <a-radio-group v-model:value="value1" size="large" @change="filteredData">
                <a-radio-button value="pending">
                  <span class="radio-content">
                    <font-awesome-icon :icon="['fas', 'file-lines']" v-if="value1 === 'all'" />
                    <svg
                      v-else
                      xmlns="http://www.w3.org/2000/svg"
                      width="18"
                      height="18"
                      viewBox="0 0 24 24"
                      fill="none"
                      stroke="currentColor"
                      stroke-width="2"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      class="feather feather-file-text"
                    >
                      <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                      <polyline points="14 2 14 8 20 8"></polyline>
                      <line x1="16" y1="13" x2="8" y2="13"></line>
                      <line x1="16" y1="17" x2="8" y2="17"></line>
                      <polyline points="10 9 9 9 8 9"></polyline>
                    </svg>
                    {{ t('global.label.requirements') }} ({{ countPending }})
                  </span>
                </a-radio-button>
                <a-radio-button value="refused">
                  <span class="radio-content">
                    <font-awesome-icon
                      :icon="['fas', 'circle-xmark']"
                      v-if="value1 === 'refused'"
                    />
                    <svg
                      v-else
                      xmlns="http://www.w3.org/2000/svg"
                      width="18"
                      height="18"
                      viewBox="0 0 24 24"
                      fill="none"
                      stroke="currentColor"
                      stroke-width="2"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      class="feather feather-x-circle"
                    >
                      <circle cx="12" cy="12" r="10"></circle>
                      <line x1="15" y1="9" x2="9" y2="15"></line>
                      <line x1="9" y1="9" x2="15" y2="15"></line>
                    </svg>
                    {{ t('global.label.rejected') }} ({{ countRefused }})
                  </span>
                </a-radio-button>
                <a-radio-button value="received">
                  <span class="radio-content">
                    <font-awesome-icon
                      :icon="['fas', 'circle-check']"
                      v-if="value1 === 'received'"
                    />
                    <svg
                      v-else
                      xmlns="http://www.w3.org/2000/svg"
                      width="18"
                      height="18"
                      viewBox="0 0 24 24"
                      fill="none"
                      stroke="currentColor"
                      stroke-width="2"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      class="feather feather-check-circle"
                    >
                      <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                      <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                    {{ t('global.label.accepted') }} ({{ countReceived }})
                  </span>
                </a-radio-button>
                <a-radio-button value="approved">
                  <span class="radio-content">
                    <font-awesome-icon
                      :icon="['fas', 'circle-check']"
                      v-if="value1 === 'approved'"
                    />
                    <svg
                      v-else
                      xmlns="http://www.w3.org/2000/svg"
                      width="18"
                      height="18"
                      viewBox="0 0 24 24"
                      fill="none"
                      stroke="currentColor"
                      stroke-width="2"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      class="feather feather-check-circle"
                    >
                      <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                      <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                    {{ t('global.label.completed') }} ({{ countApproved }})
                  </span>
                </a-radio-button>
              </a-radio-group>
            </div>
            <div class="pt-2">
              <div class="custom-collapse-container">
                <!-- Filtro por Requerimiento -->
                <!-- <a-input-search
                  v-model:value="searchText"
                  placeholder="Buscar requerimientos..."
                  style="margin-bottom: 16px"
                  @search="handleSearch"
                /> -->

                <!-- Cabecera estilo tabla -->
                <a-row class="table-header">
                  <a-col :span="12" class="header-cell">
                    <div style="cursor: pointer">
                      <a-dropdown v-model:visible="filterDropdownVisible">
                        <svg
                          class="mt-1"
                          width="19"
                          height="18"
                          viewBox="0 0 22 21"
                          fill="none"
                          xmlns="http://www.w3.org/2000/svg"
                        >
                          <path
                            d="M21 1.5H1L9 10.96V17.5L13 19.5V10.96L21 1.5Z"
                            stroke="#C4C4C4"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                          />
                        </svg>
                        <template #overlay>
                          <div class="custom-filter-dropdown">
                            <a-input
                              v-model:value="searchText"
                              :placeholder="t('files.message.search_by_requirement')"
                              @pressEnter="filterDropdownVisible = false"
                            />
                            <div style="display: flex; flex-direction: row; gap: 10px">
                              <!-- <a-button
                                type="primary"
                                @click="handleSearch"
                                class="mt-2"
                              >
                                Aplicar
                              </a-button> -->
                              <a-button class="mt-2" @click="resetSearch">{{
                                t('global.button.clear')
                              }}</a-button>
                            </div>
                          </div>
                        </template>
                      </a-dropdown>
                    </div>
                    <strong class="title-table">{{ t('global.label.requirement') }}</strong>
                  </a-col>
                  <a-col :span="6" class="header-cell"
                    ><strong class="title-table">{{ t('global.column.date') }}</strong></a-col
                  >
                  <a-col :span="6" class="header-cell"
                    ><strong class="title-table">{{
                      t('global.column.responsible')
                    }}</strong></a-col
                  >
                </a-row>

                <!-- Lista de requerimientos -->
                <a-collapse
                  v-model:activeKey="activeKeys"
                  accordion
                  class="collapse-container"
                  @change="handleCollapseChange"
                >
                  <a-collapse-panel
                    v-for="item in filteredData"
                    :key="item.entity + '' + item.id"
                    :show-arrow="false"
                    :panel-key="String(item.entity + '' + item.id)"
                    class="custom-collapse-panel"
                    :style="
                      item.isCreated || item.isUpdated
                        ? 'border: 1px solid #eb5757 !important;'
                        : ''
                    "
                  >
                    <template #extra v-if="item.isCreated || item.isUpdated">
                      <a-badge-ribbon
                        :color="'#EB5757'"
                        class="cursor-pointer ribbon-right"
                        v-if="item.isCreated || item.isUpdated"
                        @click="toggleViewStatus(item)"
                      >
                        <template #text>
                          <span>
                            <a-tooltip>
                              <template #title>
                                <small
                                  >{{
                                    item.isCreated
                                      ? 'Creado'
                                      : item.isUpdated
                                        ? 'Actualizado'
                                        : 'Sin cambios'
                                  }}
                                  #{{ item.id }}</small
                                >
                              </template>
                              <template v-if="true">
                                <font-awesome-icon :icon="['far', 'circle-check']" />
                              </template>
                            </a-tooltip>
                          </span>
                        </template>
                      </a-badge-ribbon>
                    </template>

                    <template #header style="border-radius: 0 !important">
                      <!-- Fila principal (alineada con la cabecera) -->
                      <a-row class="panel-row">
                        <a-col :span="12" class="panel-cell panel-requirement">
                          <svg
                            v-if="item.status != 'refused'"
                            xmlns="http://www.w3.org/2000/svg"
                            width="18"
                            height="18"
                            viewBox="0 0 24 24"
                            fill="none"
                            :stroke="getColor(item.status)"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            class="feather feather-check-circle"
                          >
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                          </svg>
                          <svg
                            v-else
                            width="18"
                            height="18"
                            viewBox="0 0 27 27"
                            fill="none"
                            xmlns="http://www.w3.org/2000/svg"
                          >
                            <path
                              d="M13.5 0.583496C6.36456 0.583496 0.583313 6.36475 0.583313 13.5002C0.583313 20.6356 6.36456 26.4168 13.5 26.4168C20.6354 26.4168 26.4166 20.6356 26.4166 13.5002C26.4166 6.36475 20.6354 0.583496 13.5 0.583496ZM13.5 23.9168C7.74477 23.9168 3.08331 19.2554 3.08331 13.5002C3.08331 7.74495 7.74477 3.0835 13.5 3.0835C19.2552 3.0835 23.9166 7.74495 23.9166 13.5002C23.9166 19.2554 19.2552 23.9168 13.5 23.9168ZM18.8021 10.2606L15.5625 13.5002L18.8021 16.7397C19.0469 16.9845 19.0469 17.3804 18.8021 17.6252L17.625 18.8022C17.3802 19.047 16.9844 19.047 16.7396 18.8022L13.5 15.5627L10.2604 18.8022C10.0156 19.047 9.61977 19.047 9.37498 18.8022L8.1979 17.6252C7.9531 17.3804 7.9531 16.9845 8.1979 16.7397L11.4375 13.5002L8.1979 10.2606C7.9531 10.0158 7.9531 9.61995 8.1979 9.37516L9.37498 8.19808C9.61977 7.95329 10.0156 7.95329 10.2604 8.19808L13.5 11.4377L16.7396 8.19808C16.9844 7.95329 17.3802 7.95329 17.625 8.19808L18.8021 9.37516C19.0469 9.61995 19.0469 10.0158 18.8021 10.2606Z"
                              :fill="getColor(item.status)"
                            />
                          </svg>
                          <div
                            :style="{
                              color: activeKeys?.includes(item.entity + '' + item.id)
                                ? getColor(item.status)
                                : '',
                            }"
                            v-if="item.status != 'approved'"
                          >
                            <font-awesome-icon
                              :icon="['fas', 'calendar-check']"
                              v-if="!activeKeys?.includes(item.entity + '' + item.id)"
                              style="padding-right: 5px"
                            />
                            {{
                              activeKeys?.includes(item.entity + '' + item.id)
                                ? getLabel(item.status)
                                : truncateText(item.description)
                            }}
                          </div>
                          <div v-else>
                            <font-awesome-icon
                              :icon="['fas', 'calendar-check']"
                              style="padding-right: 5px"
                            />
                            {{ truncateText(item.description) }}
                          </div>
                          <font-awesome-icon :icon="['fas', 'bars']" style="color: #c4c4c4" />
                        </a-col>
                        <a-col :span="6" class="panel-cell panel-date">
                          {{ item.date_human }}
                        </a-col>
                        <a-col :span="6" class="panel-cell panel-user">
                          {{ item.created_by_name }}
                        </a-col>
                      </a-row>
                    </template>

                    <!-- Contenido colapsable -->
                    <div class="collapse-content">
                      <!-- <a-divider style="margin: 12px 0" /> -->
                      <div class="container-details" v-if="item.status !== 'approved'">
                        <a-row
                          :gutter="16"
                          class="mb-4"
                          style="background-color: #fff2f2; padding: 20px 0px; line-height: 19px"
                          v-if="item.status === 'refused'"
                        >
                          <a-col :span="3" class="text-title" style="text-align: center">
                            {{ t('global.label.rejection') }}:
                          </a-col>
                          <a-col
                            :span="21"
                            class="text-description"
                            style="text-align: justify; padding: 0px 20px 0px 2px"
                            >{{ findLastComment(item.status_history) }}</a-col
                          >
                        </a-row>

                        <a-row :gutter="16" class="mb-3">
                          <a-col :span="3" class="text-title">{{ t('global.label.days') }}: </a-col>
                          <a-col :span="8">
                            <div style="display: flex; align-items: center; gap: 5px">
                              <a-tag class="tag-date" v-for="date in item.dates" :key="date">{{
                                formatDate(date)
                              }}</a-tag>
                              <div class="text-title">{{ t('global.label.classification') }}:</div>
                              <a-tag class="tag-classification">{{
                                item.classification_name
                              }}</a-tag>
                            </div>
                          </a-col>
                        </a-row>

                        <a-row :gutter="16" class="mb-3">
                          <a-col :span="3" class="text-title"
                            >{{ t('global.label.services') }}:
                          </a-col>
                          <a-col
                            :span="21"
                            class="mb-2"
                            style="display: grid; grid-template-columns: repeat(3, 32%); gap: 10px"
                          >
                            <div
                              class="tag-service"
                              v-for="service in item.services"
                              :key="service.service_id"
                            >
                              <font-awesome-icon :icon="['fas', 'file-import']" class="icon" />
                              <span class="text">{{ truncateText(service.service_name, 40) }}</span>
                            </div>
                          </a-col>
                        </a-row>

                        <a-row :gutter="16" class="mb-3">
                          <a-col :span="3" class="text-title"
                            >{{ t('global.label.description') }}:
                          </a-col>
                          <a-col
                            :span="21"
                            class="text-description"
                            style="text-align: justify; line-height: 19px"
                            >{{ item.description }}</a-col
                          >
                        </a-row>

                        <a-row :gutter="16" class="mb-3" v-if="item.status === 'refused'">
                          <a-col :span="24" style="text-align: right">
                            <a-button
                              type="primary"
                              danger
                              class="btn-edit-requirement"
                              @click="handleEditRequirement(item.id, item.entity)"
                              >{{ t('global.message.modify_requirement') }}</a-button
                            >
                          </a-col>
                        </a-row>
                      </div>
                      <!-- Lista de tareas -->
                      <a-list v-else item-layout="horizontal" :data-source="item.status_history">
                        <template #renderItem="{ item: status_history }">
                          <a-list-item
                            v-if="
                              item.status === status_history.status || item.status === 'approved'
                            "
                          >
                            <a-collapse
                              style="width: 100%"
                              accordion
                              v-model:activeKey="activeTaskKeys"
                              @change="handleTaskCollapseChange"
                            >
                              <!-- <template #expandIcon="{ isActive }">
                                {{ isActive ? '−' : '+' }} 
                              </template> -->
                              <a-collapse-panel
                                :key="status_history.id"
                                :panel-key="String(status_history.id)"
                                class="task-panel"
                                :show-arrow="false"
                              >
                                <template #header>
                                  <a-row :gutter="16" align="middle">
                                    <a-col
                                      :span="2"
                                      style="display: flex; justify-content: flex-end; gap: 10px"
                                    >
                                      <!-- <a-badge :status="getStatus(tarea.estado)" /> -->
                                      <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="18"
                                        height="18"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="#C4C4C4"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        class="feather feather-corner-down-right"
                                      >
                                        <polyline points="15 10 20 15 15 20"></polyline>
                                        <path d="M4 4v7a4 4 0 0 0 4 4h12"></path>
                                      </svg>
                                      <svg
                                        v-if="status_history.status != 'refused'"
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="18"
                                        height="18"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        :stroke="getColor(status_history.status)"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        class="feather feather-check-circle"
                                      >
                                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                      </svg>
                                      <svg
                                        v-else
                                        width="18"
                                        height="18"
                                        viewBox="0 0 27 27"
                                        fill="none"
                                        xmlns="http://www.w3.org/2000/svg"
                                      >
                                        <path
                                          d="M13.5 0.583496C6.36456 0.583496 0.583313 6.36475 0.583313 13.5002C0.583313 20.6356 6.36456 26.4168 13.5 26.4168C20.6354 26.4168 26.4166 20.6356 26.4166 13.5002C26.4166 6.36475 20.6354 0.583496 13.5 0.583496ZM13.5 23.9168C7.74477 23.9168 3.08331 19.2554 3.08331 13.5002C3.08331 7.74495 7.74477 3.0835 13.5 3.0835C19.2552 3.0835 23.9166 7.74495 23.9166 13.5002C23.9166 19.2554 19.2552 23.9168 13.5 23.9168ZM18.8021 10.2606L15.5625 13.5002L18.8021 16.7397C19.0469 16.9845 19.0469 17.3804 18.8021 17.6252L17.625 18.8022C17.3802 19.047 16.9844 19.047 16.7396 18.8022L13.5 15.5627L10.2604 18.8022C10.0156 19.047 9.61977 19.047 9.37498 18.8022L8.1979 17.6252C7.9531 17.3804 7.9531 16.9845 8.1979 16.7397L11.4375 13.5002L8.1979 10.2606C7.9531 10.0158 7.9531 9.61995 8.1979 9.37516L9.37498 8.19808C9.61977 7.95329 10.0156 7.95329 10.2604 8.19808L13.5 11.4377L16.7396 8.19808C16.9844 7.95329 17.3802 7.95329 17.625 8.19808L18.8021 9.37516C19.0469 9.61995 19.0469 10.0158 18.8021 10.2606Z"
                                          :fill="getColor(status_history.status)"
                                        />
                                      </svg>
                                    </a-col>
                                    <a-col :span="10" class="panel-cell panel-requirement">
                                      <div :style="{ color: getColor(status_history.status) }">
                                        {{ getLabel(status_history.status) }}
                                      </div>
                                      <!-- {{ activeTaskKeys?.includes(status_history.id) ? 'Abierto' : 'Cerrado' }} -->
                                      <!-- {{ truncateText(item.description) }} -->
                                      <!-- ESTE ICONO SOLO QUIERO MOSTRAR CUANDO ESTE ABIERTO EL COLLAPSE -->
                                      <svg
                                        v-if="activeTaskKeys?.includes(status_history.id)"
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="18"
                                        height="18"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        class="feather feather-chevron-up"
                                      >
                                        <polyline points="18 15 12 9 6 15"></polyline>
                                      </svg>
                                    </a-col>
                                    <a-col :span="6" class="panel-date">{{
                                      status_history.date_human
                                    }}</a-col>
                                    <a-col :span="6" class="panel-user">{{
                                      status_history.created_by_name
                                    }}</a-col>
                                  </a-row>
                                </template>
                                <!-- CARD -->
                                <div class="container-details">
                                  <a-row
                                    :gutter="16"
                                    class="mb-4"
                                    style="
                                      background-color: #fff2f2;
                                      padding: 20px 0px;
                                      line-height: 19px;
                                    "
                                    v-if="status_history.status === 'refused'"
                                  >
                                    <a-col :span="3" class="text-title" style="text-align: center">
                                      {{ t('global.label.rejection') }}:
                                    </a-col>
                                    <a-col
                                      :span="21"
                                      class="text-description"
                                      style="text-align: justify; padding: 0px 20px 0px 2px"
                                      >{{ status_history.comment }}</a-col
                                    >
                                  </a-row>

                                  <a-row :gutter="16" class="mb-3">
                                    <a-col :span="3" class="text-title"
                                      >{{ t('global.label.days') }}:
                                    </a-col>
                                    <a-col :span="8">
                                      <div style="display: flex; align-items: center; gap: 5px">
                                        <a-tag
                                          class="tag-date"
                                          v-for="date in item.dates"
                                          :key="date"
                                          >{{ formatDate(date) }}</a-tag
                                        >
                                        <div class="text-title">
                                          {{ t('global.label.classification') }}:
                                        </div>
                                        <a-tag class="tag-classification">{{
                                          item.classification_name
                                        }}</a-tag>
                                      </div>
                                    </a-col>
                                  </a-row>

                                  <a-row :gutter="16" class="mb-3">
                                    <a-col :span="3" class="text-title"
                                      >{{ t('global.label.services') }}:
                                    </a-col>
                                    <a-col
                                      :span="21"
                                      class="mb-2"
                                      style="
                                        display: grid;
                                        grid-template-columns: repeat(3, 32%);
                                        gap: 10px;
                                      "
                                    >
                                      <div
                                        class="tag-service"
                                        v-for="service in item.services"
                                        :key="service.service_id"
                                      >
                                        <font-awesome-icon
                                          :icon="['fas', 'file-import']"
                                          class="icon"
                                        />
                                        <span class="text">{{
                                          truncateText(service.service_name, 40)
                                        }}</span>
                                      </div>
                                    </a-col>
                                  </a-row>

                                  <a-row :gutter="16" class="mb-3">
                                    <a-col :span="3" class="text-title"
                                      >{{ t('global.label.description') }}:
                                    </a-col>
                                    <a-col
                                      :span="21"
                                      class="text-description"
                                      style="text-align: justify; line-height: 19px"
                                      >{{ item.description }}</a-col
                                    >
                                  </a-row>
                                  <a-row
                                    :gutter="16"
                                    class="mb-3"
                                    v-if="
                                      status_history.status === 'refused' &&
                                      item.status === 'refused'
                                    "
                                  >
                                    <a-col :span="24" style="text-align: right">
                                      <a-button
                                        type="primary"
                                        danger
                                        class="btn-edit-requirement"
                                        @click="handleEditRequirement(item.id, item.entity)"
                                        >{{ t('global.message.modify_requirement') }}</a-button
                                      >
                                    </a-col>
                                  </a-row>
                                </div>
                              </a-collapse-panel>
                            </a-collapse>
                          </a-list-item>
                        </template>
                      </a-list>
                    </div>
                  </a-collapse-panel>
                </a-collapse>
              </div>
            </div>
          </a-tab-pane>
        </a-tabs>
      </div>
    </div>
  </a-card>

  <ModalNotes
    :open-modal="modalNotesOpen"
    @close-modal="handleCloseModal"
    :id="requirement_id"
    type="REQUIREMENT"
  />

  <ModalNoteForService
    :open-modal="modalNotesOpenForService"
    @close-modal="handleCloseModalForService"
    :id="requirement_id"
    type="REQUIREMENT"
    :entity="requirement_entity"
  />
</template>

<script setup>
  import { ref, computed } from 'vue';
  import { useServiceNotesStore, useFilesStore } from '@/stores/files';
  import ModalNotes from '@/components/files/notes/ModalNotes.vue';
  import ModalNoteForService from '@/components/files/notes/ModalNoteForService.vue';
  import { useI18n } from 'vue-i18n';
  const { t } = useI18n({
    useScope: 'global',
  });

  const filesStore = useFilesStore();
  const serviceNotesStore = useServiceNotesStore();

  const modalNotesOpen = ref(false);
  const modalNotesOpenForService = ref(false);

  const searchText = ref('');
  const filterDropdownVisible = ref(false);
  const activeKeys = ref([]);
  const activeTaskKeys = ref([]);
  const mode = ref('right');
  const activeKey = ref('');
  const value1 = ref('pending');
  const requirement_id = ref(0);
  const requirement_entity = ref('');

  const openModalNotes = () => {
    modalNotesOpen.value = true;
  };

  const openModalNotesForService = () => {
    modalNotesOpenForService.value = true;
  };

  const handleCloseModal = () => {
    requirement_id.value = 0;
    requirement_entity.value = '';
    modalNotesOpen.value = false;
  };

  const handleCloseModalForService = () => {
    requirement_id.value = 0;
    requirement_entity.value = '';
    modalNotesOpenForService.value = false;
  };

  const handleEditRequirement = (id, entity) => {
    requirement_id.value = id;
    requirement_entity.value = entity;

    if (entity == 'mask') {
      openModalNotes();
    } else {
      openModalNotesForService();
    }
  };

  const handleTabChange = () => {
    value1.value = 'pending';
  };

  const data = computed(() => {
    const requirement = serviceNotesStore.getAllRequirementFileNote || [];
    return requirement;
  });

  // Filtramos por requerimiento (case insensitive)
  const filteredData = computed(() => {
    return data.value.filter((item) => {
      const matchStatus = item.status === value1.value;

      const matchSearchText =
        !searchText.value ||
        item.description.toLowerCase().includes(searchText.value.toLowerCase());

      const matchCity =
        !activeKey.value || item.cities.some((city) => city.name.toUpperCase() === activeKey.value);

      return matchStatus && matchSearchText && matchCity;
    });
  });

  // const handleSearch = () => {
  //   if (searchText.value) {
  //     activeKeys.value = filteredData.value.map((item) => item.id);
  //   }
  //   filterDropdownVisible.value = false;
  // };

  const resetSearch = () => {
    searchText.value = '';
  };

  const handleCollapseChange = (keys) => {
    if (typeof keys !== undefined) {
      activeKeys.value = keys;
    }
  };

  const handleTaskCollapseChange = (keys) => {
    if (typeof keys !== undefined) {
      activeTaskKeys.value = keys;
    }
  };

  // const getStatus = (estado) => {
  //   const map = {
  //     'Completado': 'success',
  //     'En progreso': 'processing',
  //     'Pendiente': 'warning'
  //   };
  //   return map[estado] || 'default';
  // };

  const getColor = (prioridad) => {
    const map = {
      pending: '#C4C4C4',
      received: '#55A3FF',
      refused: '#FF3B3B',
      approved: '#28A745',
    };
    return map[prioridad] || 'gray';
  };

  const getLabel = computed(() => (prioridad) => {
    const map = {
      pending: t('files.message.awaiting_acceptance'),
      received: t('files.message.request_accepted'),
      refused: t('files.message.request_rejected'),
      approved: t('files.message.requirement_completed'),
    };
    return map[prioridad] || 'gray';
  });

  const callback = function (val) {
    console.log(val);
  };

  const truncateText = (text, length = 25) => {
    if (!text) return '';
    return text.length > length ? text.substring(0, length) + '...' : text;
  };

  const locations = computed(() => {
    // 1. Obtener ciudades únicas de los itinerarios (eliminando duplicados)
    const uniqueItineraryCities = [
      ...new Set(
        filesStore.getFileItineraries
          .map((item) => item.city_in_name?.toUpperCase())
          .filter(Boolean) // Filtramos null/undefined
      ),
    ];

    // 2. Obtener requisitos o array vacío si no existen
    const requirements = serviceNotesStore.getAllRequirementFileNote || [];

    // 3. Extraer y normalizar todos los nombres de ciudades de los requisitos
    const allCityNames = requirements.reduce((acc, item) => {
      const cities = item.cities.map((city) => city.name.toUpperCase());
      return [...acc, ...cities];
    }, []);

    // 4. Filtrar ciudades únicas manteniendo el orden original
    const uniqueCityNames = allCityNames.filter(
      (city, index, self) => self.indexOf(city) === index
    );

    // 5. Ordenar las ciudades según el orden de uniqueItineraryCities
    const orderedCities = uniqueItineraryCities.filter((city) => uniqueCityNames.includes(city));

    // 6. Añadir las ciudades restantes que no estaban en el itinerario
    const remainingCities = uniqueCityNames.filter((city) => !uniqueItineraryCities.includes(city));
    const finalCityList = [...orderedCities, ...remainingCities];

    // 7. Actualizar activeKey si es necesario
    if (finalCityList.length > 0 && !finalCityList.includes(activeKey.value)) {
      activeKey.value = finalCityList[0];
    }

    return finalCityList;
  });

  const formatDate = (dateString) => {
    if (!dateString) return '';

    // Dividir la fecha por guiones, espacios o cualquier separador común
    const dateParts = dateString.split(/[-/ ]/); // Admite separadores: - / o espacio
    let month, day;

    // Extraer mes y día según el formato de entrada
    if (dateParts.length >= 3) {
      // Formato YYYY-MM-DD o similar
      [, month, day] = dateParts; // Ignoramos el año (primer elemento)
    } else if (dateParts.length === 2) {
      // Si ya viene solo MM-DD
      [month, day] = dateParts;
    } else {
      return dateString; // Devuelve el original si no se puede parsear
    }

    // Asegurar 2 dígitos para mes y día
    month = month.padStart(2, '0');
    day = day.padStart(2, '0');

    return `${day}/${month}`;
  };

  const countPending = computed(() => {
    return data.value.filter((item) => {
      const matchStatus = item.status === 'pending';

      const matchCity =
        !activeKey.value || item.cities.some((city) => city.name.toUpperCase() === activeKey.value);

      return matchStatus && matchCity;
    }).length;
  });

  const countRefused = computed(() => {
    return data.value.filter((item) => {
      const matchStatus = item.status === 'refused';

      const matchCity =
        !activeKey.value || item.cities.some((city) => city.name.toUpperCase() === activeKey.value);

      return matchStatus && matchCity;
    }).length;
  });

  const countReceived = computed(() => {
    return data.value.filter((item) => {
      const matchStatus = item.status === 'received';

      const matchCity =
        !activeKey.value || item.cities.some((city) => city.name.toUpperCase() === activeKey.value);

      return matchStatus && matchCity;
    }).length;
  });

  const countApproved = computed(() => {
    return data.value.filter((item) => {
      const matchStatus = item.status === 'approved';

      const matchCity =
        !activeKey.value || item.cities.some((city) => city.name.toUpperCase() === activeKey.value);

      return matchStatus && matchCity;
    }).length;
  });

  const findLastComment = (statusHistory) => {
    const refused = statusHistory.find((e) => {
      return e.status === 'refused';
    });

    return refused.comment;
  };

  const toggleViewStatus = (item) => {
    item.isCreated = false;
    item.isUpdated = false;
    serviceNotesStore.changeFlagChange();
  };
</script>

<style scoped>
  .requirements-coordinator {
    width: 100%;
  }

  .custom-collapse-container {
    width: 95%;
    max-width: 1200px;
    border-radius: 8px;
    margin-left: 38px;
    padding: 0 0;
    border-bottom: 1px solid #cfd1d5;
    box-shadow: 0px 0px 20px 0px #0000000d;
  }

  /* Estilo para la cabecera tipo tabla */
  .table-header {
    padding: 12px 16px;
    width: 100%;
    background-color: #ffffff;
    color: #c4c4c4;
    border-radius: 8px 8px 0 0;
    border: 1px solid #e9e9e9 !important;
  }

  .title-table {
    font-weight: 700;
    font-size: 16px;
  }

  .header-cell {
    display: flex;
    gap: 10px;
    padding: 2px;
    /* border-right: 1px solid #e8e8e8; */
  }

  .header-cell:last-child {
    border-right: none;
  }

  .panel-cell {
    padding: 1px;
    display: flex;
    gap: 12px;
    align-items: center;
  }

  .panel-requirement {
    color: #3d3d3d;
    font-weight: 700;
    font-size: 14px;
  }

  .panel-date {
    color: #737373;
    font-weight: 700;
    font-size: 14px;
  }

  .panel-user {
    color: #737373;
    font-weight: 700;
    font-size: 14px;
    padding: 0px !important;
  }
  /* Estilos para los collapses */
  .collapse-container {
    /* border: 1px solid #FFFFFF; */
    border-radius: 8px 8px 0 0;
    width: 100%;
    margin: 0 !important;
  }

  :deep(.ant-collapse) {
    border: none !important;
    margin: 0px !important;
  }

  :deep(.ant-collapse-header) {
    padding: 10px 16px !important;
    background: transparent !important;
    background-color: #ffffff !important;
    border: 1px solid #e9e9e9 !important;
  }

  .requirement-panel :deep(.ant-collapse-content) {
    border-top: none !important;
  }

  .collapse-content {
    padding: 0;
    margin: 0;
  }

  .task-panel :deep(.ant-collapse-header) {
    padding: 12px 20px 12px 15px !important;
    background-color: #fafafa !important;
    color: #3d3d3d;
    margin: 0px;
    border-radius: 0px !important;
    border-right: 1px solid #cfd1d5 !important;
    border-left: 1px solid #cfd1d5 !important;
    border-bottom: none !important;

    /* border-top: 1px solid #CFD1D5 !important; */
  }

  :deep(.ant-collapse > .ant-collapse-item) {
    border: none !important;
  }

  :deep(.ant-collapse .ant-collapse-content) {
    border: none !important;
  }

  :deep(.ant-collapse-content-box) {
    padding: 0px 0px !important;
  }

  /* Ajustes para la lista */
  :deep(.ant-list-item) {
    padding: 0 !important;
  }

  .tag-service {
    /* max-width: 250px; */
    background-color: #e9e9e9;
    border: 0;
    border-radius: 8px;
    padding: 10px 15px;
    display: flex;
    flex-direction: row;
    gap: 10px;
    align-items: center;
  }

  .tag-service .icon {
    font-size: 20px;
  }

  .tag-service .text {
    color: #2e2e2e !important;
    font-weight: 500;
    size: 12px;
    line-height: 19px;
  }

  /* CONTENIDO DE LAS VALIDACIONES */
  .container-details {
    background-color: #fafafa;
    padding: 20px 20px;
    border-left: 1px solid #cfd1d5;
    border-right: 1px solid #cfd1d5;
    border-bottom: 1px solid #cfd1d5;
  }
  .tag-date {
    background-color: #ededff;
    border: 0;
    color: #4f4b4b;
    font-weight: 400;
    font-size: 12px;
  }
  .tag-classification {
    font-weight: 400;
    size: 12px;
    color: #fafafa;
    background: #4ba3b2 !important;
    border-color: rgba(255, 255, 255, 0.3);
  }
  .text-title {
    color: #3d3d3d;
    font-size: 12px;
    font-weight: 500;
  }
  .text-description {
    color: #3d3d3d;
    font-size: 12px;
    font-weight: 400;
  }
  /* ANT RADIO GROUP */
  :deep(.ant-radio-group) {
    box-shadow: 0px 0px 16px 0px #0000000d;
    font-weight: 500;
    font-size: 14px;
  }

  :deep(.ant-radio-button-wrapper) {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: none;
    background-color: #ffffff;
    height: 60px;
    padding: 0px 20px;
    color: #c4c4c4;
  }

  :deep(.ant-radio-button-wrapper-checked) {
    background-color: #fafafa;
    color: #eb5757;
    border: none;
  }

  :deep(.ant-radio-button-wrapper) svg,
  :deep(.ant-radio-button-wrapper) .font-awesome-icon {
    margin-right: 8px;
    vertical-align: middle;
  }

  :deep(.ant-card-body) {
    padding: 0px;
  }

  .ant-radio-button-wrapper:not(:first-child)::before {
    width: 0px;
  }

  .tab-custom :deep(.ant-tabs-nav-list) {
    background-color: #ffffff !important;
    padding-top: 15px;
  }

  .tab-custom :deep(.ant-tabs-tab) {
    padding: 12px 16px;
    margin: 0;
    border: none !important;
    font-size: 12px;
  }
  .tab-custom :deep(.ant-tabs-tab-active) {
    background-color: #fafafa !important;
    border-left: none !important;
  }

  .tab-custom :deep(.ant-tabs-tab .ant-tabs-tab-btn) {
    color: #979797 !important;
  }

  .tab-custom :deep(.ant-tabs-tab.ant-tabs-tab-active .ant-tabs-tab-btn) {
    color: #575757 !important;
  }

  .tab-custom :deep(.custom-tab-header) {
    display: flex;
    flex-direction: row;
    gap: 10px;
    align-items: center;
    width: 200px;
    padding: 5px 10px;
  }
  :deep(.tab-custom .ant-tabs-ink-bar) {
    display: none !important;
  }

  .tab-custom :deep(.ant-tabs-content-holder) {
    background-color: #fafafa;
  }

  .btn-edit-requirement {
    background-color: #eb5757;
    opacity: 1;
  }

  .btn-edit-requirement:hover {
    opacity: 0.9;
  }

  .custom-filter-dropdown {
    padding: 15px 15px;
    background: #fff;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
  }

  .action-updated {
    position: relative;
    border: 1px solid #eb5757 !important;
    /* border-radius: 7px; */
  }

  .action-padding {
    padding: 10px 0 10px 15px;
  }

  .custom-collapse-panel {
    position: relative;
    overflow: visible;
  }

  :deep(.ant-collapse-extra) {
    position: absolute;
    right: 0;
    top: -15px;
  }
</style>
