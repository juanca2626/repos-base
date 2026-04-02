<template>
  <!-- ... el resto del template sin cambios ... -->
  <div class="base-table container-fluid p-0">
    <div class="files-edit files-edit__border">
      <a-row style="padding: 0 0 20px 0">
        <a-col :span="24">
          <a-flex justify="space-between" align="center">
            <h3 class="files-edit__filelabel" style="text-transform: capitalize; margin: 0">
              Control de Pedidos
            </h3>
            <base-button
              v-if="can('mfmyorders', 'managetemplates')"
              danger
              type="outline-main"
              size="large"
              @click="handleGoToTemplateManagement()"
              style="border: solid 1px; border-color: #eb5757; color: #eb5757"
            >
              <div style="display: flex; gap: 4px">
                <svg
                  width="22"
                  height="20"
                  viewBox="0 0 24 25"
                  fill="none"
                  xmlns="http://www.w3.org/2000/svg"
                >
                  <g clip-path="url(#clip0_817_2428)">
                    <path
                      d="M12 15.5C13.6569 15.5 15 14.1569 15 12.5C15 10.8431 13.6569 9.5 12 9.5C10.3431 9.5 9 10.8431 9 12.5C9 14.1569 10.3431 15.5 12 15.5Z"
                      stroke="#EB5757"
                      stroke-width="2"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                    />
                    <path
                      d="M19.4 15.5C19.2669 15.8016 19.2272 16.1362 19.286 16.4606C19.3448 16.785 19.4995 17.0843 19.73 17.32L19.79 17.38C19.976 17.5657 20.1235 17.7863 20.2241 18.0291C20.3248 18.2719 20.3766 18.5322 20.3766 18.795C20.3766 19.0578 20.3248 19.3181 20.2241 19.5609C20.1235 19.8037 19.976 20.0243 19.79 20.21C19.6043 20.396 19.3837 20.5435 19.1409 20.6441C18.8981 20.7448 18.6378 20.7966 18.375 20.7966C18.1122 20.7966 17.8519 20.7448 17.6091 20.6441C17.3663 20.5435 17.1457 20.396 16.96 20.21L16.9 20.15C16.6643 19.9195 16.365 19.7648 16.0406 19.706C15.7162 19.6472 15.3816 19.6869 15.08 19.82C14.7842 19.9468 14.532 20.1572 14.3543 20.4255C14.1766 20.6938 14.0813 21.0082 14.08 21.33V21.5C14.08 22.0304 13.8693 22.5391 13.4942 22.9142C13.1191 23.2893 12.6104 23.5 12.08 23.5C11.5496 23.5 11.0409 23.2893 10.6658 22.9142C10.2907 22.5391 10.08 22.0304 10.08 21.5V21.41C10.0723 21.079 9.96512 20.758 9.77251 20.4887C9.5799 20.2194 9.31074 20.0143 9 19.9C8.69838 19.7669 8.36381 19.7272 8.03941 19.786C7.71502 19.8448 7.41568 19.9995 7.18 20.23L7.12 20.29C6.93425 20.476 6.71368 20.6235 6.47088 20.7241C6.22808 20.8248 5.96783 20.8766 5.705 20.8766C5.44217 20.8766 5.18192 20.8248 4.93912 20.7241C4.69632 20.6235 4.47575 20.476 4.29 20.29C4.10405 20.1043 3.95653 19.8837 3.85588 19.6409C3.75523 19.3981 3.70343 19.1378 3.70343 18.875C3.70343 18.6122 3.75523 18.3519 3.85588 18.1091C3.95653 17.8663 4.10405 17.6457 4.29 17.46L4.35 17.4C4.58054 17.1643 4.73519 16.865 4.794 16.5406C4.85282 16.2162 4.81312 15.8816 4.68 15.58C4.55324 15.2842 4.34276 15.032 4.07447 14.8543C3.80618 14.6766 3.49179 14.5813 3.17 14.58H3C2.46957 14.58 1.96086 14.3693 1.58579 13.9942C1.21071 13.6191 1 13.1104 1 12.58C1 12.0496 1.21071 11.5409 1.58579 11.1658C1.96086 10.7907 2.46957 10.58 3 10.58H3.09C3.42099 10.5723 3.742 10.4651 4.0113 10.2725C4.28059 10.0799 4.48572 9.81074 4.6 9.5C4.73312 9.19838 4.77282 8.86381 4.714 8.53941C4.65519 8.21502 4.50054 7.91568 4.27 7.68L4.21 7.62C4.02405 7.43425 3.87653 7.21368 3.77588 6.97088C3.67523 6.72808 3.62343 6.46783 3.62343 6.205C3.62343 5.94217 3.67523 5.68192 3.77588 5.43912C3.87653 5.19632 4.02405 4.97575 4.21 4.79C4.39575 4.60405 4.61632 4.45653 4.85912 4.35588C5.10192 4.25523 5.36217 4.20343 5.625 4.20343C5.88783 4.20343 6.14808 4.25523 6.39088 4.35588C6.63368 4.45653 6.85425 4.60405 7.04 4.79L7.1 4.85C7.33568 5.08054 7.63502 5.23519 7.95941 5.294C8.28381 5.35282 8.61838 5.31312 8.92 5.18H9C9.29577 5.05324 9.54802 4.84276 9.72569 4.57447C9.90337 4.30618 9.99872 3.99179 10 3.67V3.5C10 2.96957 10.2107 2.46086 10.5858 2.08579C10.9609 1.71071 11.4696 1.5 12 1.5C12.5304 1.5 13.0391 1.71071 13.4142 2.08579C13.7893 2.46086 14 2.96957 14 3.5V3.59C14.0013 3.91179 14.0966 4.22618 14.2743 4.49447C14.452 4.76276 14.7042 4.97324 15 5.1C15.3016 5.23312 15.6362 5.27282 15.9606 5.214C16.285 5.15519 16.5843 5.00054 16.82 4.77L16.88 4.71C17.0657 4.52405 17.2863 4.37653 17.5291 4.27588C17.7719 4.17523 18.0322 4.12343 18.295 4.12343C18.5578 4.12343 18.8181 4.17523 19.0609 4.27588C19.3037 4.37653 19.5243 4.52405 19.71 4.71C19.896 4.89575 20.0435 5.11632 20.1441 5.35912C20.2448 5.60192 20.2966 5.86217 20.2966 6.125C20.2966 6.38783 20.2448 6.64808 20.1441 6.89088C20.0435 7.13368 19.896 7.35425 19.71 7.54L19.65 7.6C19.4195 7.83568 19.2648 8.13502 19.206 8.45941C19.1472 8.78381 19.1869 9.11838 19.32 9.42V9.5C19.4468 9.79577 19.6572 10.048 19.9255 10.2257C20.1938 10.4034 20.5082 10.4987 20.83 10.5H21C21.5304 10.5 22.0391 10.7107 22.4142 11.0858C22.7893 11.4609 23 11.9696 23 12.5C23 13.0304 22.7893 13.5391 22.4142 13.9142C22.0391 14.2893 21.5304 14.5 21 14.5H20.91C20.5882 14.5013 20.2738 14.5966 20.0055 14.7743C19.7372 14.952 19.5268 15.2042 19.4 15.5V15.5Z"
                      stroke="#EB5757"
                      stroke-width="2"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                    />
                  </g>
                  <defs>
                    <clipPath id="clip0_817_2428">
                      <rect width="24" height="24" fill="white" transform="translate(0 0.5)" />
                    </clipPath>
                  </defs>
                </svg>

                <span>Gestionar Plantillas</span>
              </div>
            </base-button>
          </a-flex>
        </a-col>
      </a-row>

      <!-- ======================================================================= -->
      <!-- SECCIÓN FILTROS -->
      <!-- ======================================================================= -->

      <div style="background-color: #fafafa; padding-top: 30px">
        <!-- Contenedor principal del formulario -->
        <div class="form-filter-container" style="padding: 0 16px 20px 16px">
          <a-form
            ref="formFilterRef"
            class="form-filter-grid"
            :model="formFilter"
            autocomplete="off"
            @finish="onFilter"
            @finishFailed="onFilterFailed"
            style="
              display: grid;
              grid-template-columns: repeat(6, 1fr);
              gap: 12px;
              align-items: start;
            "
          >
            <div class="filter-item">
              <a-range-picker
                style="width: 100%; height: 45px"
                v-model:value="formFilter['last_communication']"
                format="DD/MM/YYYY"
                class="custom-range-picker"
              />
            </div>

            <base-input-search
              name="filter"
              placeholder="N° de pedido, referencia..."
              width="100%"
              autocomplete="off"
              :allow-clear="true"
              :debounceTime="500"
              :minChars="3"
              :isLoading="isLoading"
              v-model:value="formFilter.filter"
              style="width: 100%"
            />

            <base-select
              placeholder="Estados"
              :filter-option="false"
              :showSearch="false"
              :allowClear="false"
              :loading="generalStore.isLoading"
              :options="orderStatusStore.getOrderStatuses"
              v-model:value="formFilter.status"
              width="100%"
              style="width: 100%"
            />

            <base-select-multiple
              v-if="can('mfmyorders', 'filteradvanced')"
              width="100%"
              placeholder="Mercado"
              :showSearch="true"
              :allowClear="true"
              :filter-option="true"
              :multiple="true"
              :comma="true"
              :maxTagCount="1"
              :loading="marketStore.isLoading"
              :options="marketStore.getMarkets"
              v-model:value="formFilter.market"
              :maxTagTextLength="10"
              style="width: 100%"
            />

            <base-select
              v-if="can('mfmyorders', 'filteradvanced')"
              width="100%"
              placeholder="Todos"
              :filter-option="false"
              :showSearch="false"
              :allowClear="false"
              :loading="generalStore.isLoading"
              :options="generalStore.getTeams"
              v-model:value="formFilter.executive"
              style="width: 100%"
            />

            <base-select-multiple
              width="100%"
              placeholder="Cliente"
              :showSearch="true"
              :allowClear="true"
              :filter-option="true"
              :multiple="true"
              :maxTagCount="1"
              :maxTagTextLength="10"
              :loading="customerStore.isLoading"
              :options="customerStore.getCustomersWithTruncatedLabel"
              v-model:value="formFilter.customer"
              style="width: 100%"
            />
          </a-form>
        </div>

        <!-- Contenedor de botones -->
        <div
          style="
            padding: 0 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 20px;
          "
        >
          <a-button
            type="link"
            danger
            @click="handleRefresh"
            style="padding: 0; text-decoration: underline; font-weight: 500; height: auto"
          >
            <font-awesome-icon
              icon="fa-solid fa-eraser"
              style="margin-right: 5px; color: #eb5757 !important"
            />
            <span style="text-decoration: underline; color: #eb5757 !important">
              Borrar filtros
            </span>
          </a-button>

          <base-button
            size="large"
            type="primary"
            danger
            :disabled="isLoading"
            @click="handleSearch"
            style="
              background-color: #eb5757;
              border-color: #eb5757;
              color: white;
              min-width: 120px;
              height: 40px;
            "
          >
            <i class="bi bi-search" style="font-size: 16px; margin-right: 8px"></i>
            Buscar
          </base-button>
        </div>
      </div>
    </div>

    <div style="padding-bottom: 20px">
      <a-alert class="custom-purple-alert" type="info" show-icon closable>
        <template #icon>
          <InfoCircleOutlined style="font-size: 18px; color: #5e59b3; margin-left: 5px" />
        </template>
        <template #message>
          <span class="alert-content">
            <strong style="margin-right: 4px">Importante:</strong>
            <span class="alert-text"
              >Solo se muestran controles de pedidos de los últimos 3 meses. Para consultar periodos
              anteriores, aplica un filtro de fechas.</span
            >
          </span>
        </template>
      </a-alert>
    </div>
    <!-- ======================================================================= -->
    <!-- SECCIÓN TABLA -->
    <!-- ======================================================================= -->
    <div class="files-edit files-edit__border">
      <a-skeleton active v-if="orderStore.isLoading" />
      <template v-else>
        <a-tabs v-model:activeKey="activeKey">
          <a-tab-pane key="1" :class="{ 'block-style': !flagEditable }">
            <template #tab>
              <font-awesome-icon :icon="['fas', 'list']" style="margin-right: 4px" />
              <span style="text-transform: capitalize"> Listado</span>
            </template>
            <div v-if="parseInt(activeKey) === 1">
              <!-- ACCIONES MASIVAS -->
              <!-- PERMISO: bulkactions -->
              <a-space
                direction="horizontal"
                style="width: 100%; padding: 30px 0"
                v-if="anyOrderSelected && can('mfmyorders', 'bulkactions')"
              >
                <!--                  :options="orderStatusStore.getOrderStatuses"-->
                <a-select
                  v-if="can('mfmyorders', 'bulkstatus')"
                  size="large"
                  class="w-100"
                  v-model:value="selectedStatusIdForBulk"
                  :options="filteredOrderStatuses"
                  placeholder="Cambiar estado"
                  style="text-align: left"
                  @change="handleStatusSelectForBulk"
                  :allow-clear="true"
                />

                <a-select
                  v-if="can('mfmyorders', 'bulkfollowup')"
                  size="large"
                  class="w-100"
                  v-model:value="selectedTemplateIdForBulk"
                  :options="availableTemplatesOptions"
                  placeholder="Enviar seguimiento a seleccionados"
                  style="text-align: left"
                  @change="handleTemplateSelectForBulk"
                  :allow-clear="true"
                />

                <div v-if="selectAllGlobal" class="ml-4 text-blue-600 font-medium">
                  <font-awesome-icon icon="check-double" />
                  Seleccionado todo el universo de datos ({{ total - excludedIds.size }} registros)
                </div>
              </a-space>

              <div class="row row-header">
                <div class="" style="width: 60px !important">
                  <a-checkbox
                    :checked="selectAllGlobal"
                    :indeterminate="selectedItemsMap.size > 0 && !selectAllGlobal"
                    @change="toggleSelectAll"
                  />
                </div>

                <template v-for="column in config.columns.slice(1)" :key="column.id">
                  <div
                    :class="{ 'col-small': column.fieldName !== 'lights' }"
                    :style="{ width: column.fieldName === 'lights' ? '60px' : 'auto' }"
                  >
                    <span v-if="!column?.isFiltered || noSortColumns.includes(column.fieldName)">
                      {{ column.title }}
                    </span>

                    <div
                      v-else-if="column.fieldName === 'travel_date'"
                      class="filter-column-header"
                      :class="{ 'active-filter': hasDateFilter }"
                    >
                      <a-popover
                        v-model:open="dateFilterOpen"
                        trigger="click"
                        placement="bottom"
                        @openChange="onDateFilterVisibleChange"
                        overlayClassName="date-filter-popover"
                      >
                        <template #content>
                          <div style="padding: 8px; width: 300px">
                            <p style="margin-bottom: 8px; font-weight: 600; color: #555">
                              Seleccionar rango:
                            </p>

                            <a-range-picker
                              v-model:value="tempDateState"
                              format="DD/MM/YYYY"
                              style="width: 100%; margin-bottom: 12px"
                            />

                            <div style="display: flex; justify-content: space-between; gap: 8px">
                              <a-button
                                size="small"
                                class="btn-clean"
                                style="flex: 1; border-color: #eb5757; color: #eb5757"
                                :disabled="!tempDateState || tempDateState.length === 0"
                                @click="handleClearDateFilter"
                              >
                                Limpiar
                              </a-button>
                              <a-button
                                type="primary"
                                danger
                                size="small"
                                class="btn-apply"
                                style="flex: 1; background-color: #eb5757; border-color: #eb5757"
                                :disabled="!tempDateState || tempDateState.length !== 2"
                                @click="handleApplyDateFilter"
                              >
                                Aplicar
                              </a-button>
                            </div>
                          </div>
                        </template>

                        <span class="row-header-sort d-flex align-items-center gap-2">
                          {{ column.title }}
                          <font-awesome-icon
                            :icon="['fas', 'filter']"
                            :style="{
                              color: hasDateFilter ? '#eb5757' : 'inherit',
                              marginLeft: '10px',
                            }"
                          />
                        </span>
                      </a-popover>
                    </div>

                    <div v-else-if="column.fieldName === 'status'" class="filter-column-header">
                      <a-popover
                        v-model:open="templateFilterOpen"
                        trigger="click"
                        placement="bottomRight"
                        overlayClassName="custom-filter-popover"
                        @openChange="handleOpenTemplateFilter"
                      >
                        <template #content>
                          <div class="filter-content-wrapper">
                            <h4 class="filter-title">Filtro</h4>

                            <div class="filter-search">
                              <a-input
                                v-model:value="templateSearchTerm"
                                placeholder="Buscar plantilla..."
                                allow-clear
                                :disabled="areTemplatesLoading"
                              >
                                <template #prefix>
                                  <i class="bi bi-search" style="color: #bfbfbf"></i>
                                </template>
                              </a-input>
                            </div>

                            <div class="filter-list-container">
                              <!-- ESTADO DE CARGA -->
                              <div
                                v-if="isTemplateFilterLoading"
                                class="text-center py-4"
                                style="text-align: center; padding: 20px 0"
                              >
                                <a-spin />
                                <div style="font-size: 12px; color: #999; margin-top: 5px">
                                  {{ showAllTemplates ? 'Cargando todas...' : 'Cargando...' }}
                                </div>
                              </div>

                              <!-- LISTA DE CHECKBOXES (Solo se muestra cuando termina de cargar) -->
                              <template v-else>
                                <div
                                  class="filter-item-row select-all-row"
                                  style="
                                    display: flex;
                                    justify-content: space-between;
                                    align-items: center;
                                  "
                                >
                                  <a-checkbox
                                    :checked="isAllTemplatesSelected"
                                    :indeterminate="isTemplateIndeterminate"
                                    @change="toggleSelectAllTemplates"
                                  >
                                    (Seleccionar todo)
                                  </a-checkbox>
                                  <a-tooltip
                                    :title="
                                      showAllTemplates
                                        ? 'Ver solo filtradas'
                                        : 'Ver todas las plantillas'
                                    "
                                  >
                                    <div
                                      v-show="false"
                                      class="eye-toggle-btn"
                                      :class="{ active: showAllTemplates }"
                                      @click="toggleShowAllTemplates"
                                    >
                                      <EyeInvisibleOutlined v-if="showAllTemplates" />
                                      <EyeOutlined v-else />
                                    </div>
                                  </a-tooltip>
                                </div>

                                <div
                                  class="filter-item-row"
                                  style="border-bottom: 1px solid #f0f0f0"
                                >
                                  <a-checkbox
                                    :checked="tempTemplateState.includes('empty')"
                                    @change="
                                      (e) => handleSingleTemplateChange('empty', e.target.checked)
                                    "
                                  >
                                    (Sin plantilla)
                                  </a-checkbox>
                                </div>

                                <div
                                  v-for="template in filteredTemplateOptions"
                                  :key="template.value"
                                  class="filter-item-row"
                                >
                                  <a-checkbox
                                    :checked="tempTemplateState.includes(template.value)"
                                    @change="
                                      (e) =>
                                        handleSingleTemplateChange(template.value, e.target.checked)
                                    "
                                  >
                                    {{ template.label }}
                                  </a-checkbox>
                                </div>

                                <div
                                  v-if="filteredTemplateOptions.length === 0"
                                  class="empty-search"
                                >
                                  No se encontraron plantillas
                                  {{ showAllTemplates ? '' : 'en esta vista' }}
                                </div>
                              </template>
                            </div>

                            <div class="filter-footer">
                              <a-button class="btn-clean" @click="handleClearTemplateFilter">
                                Limpiar
                              </a-button>
                              <a-button
                                type="primary"
                                danger
                                class="btn-apply"
                                @click="handleApplyTemplateFilter"
                                :disabled="areTemplatesLoading"
                              >
                                Aplicar
                              </a-button>
                            </div>
                          </div>
                        </template>

                        <span class="row-header-sort d-flex align-items-center gap-2">
                          {{ column.title }}
                          <font-awesome-icon
                            :icon="['fas', 'filter']"
                            :style="{
                              color: hasTemplateFilter ? '#eb5757' : 'inherit',
                              marginLeft: '10px',
                            }"
                          />
                        </span>
                      </a-popover>
                    </div>

                    <span v-else class="row-header-sort">
                      <span
                        v-if="isDesc && isSelectedFilterBy(column.fieldName)"
                        @click="onFilterBy({ filterBy: column.fieldName, filterByType: 'asc' })"
                      >
                        {{ column.title }}
                        <font-awesome-icon icon="fa-solid fa-arrow-up-short-wide" />
                      </span>
                      <span
                        v-else
                        @click="onFilterBy({ filterBy: column.fieldName, filterByType: 'desc' })"
                      >
                        {{ column.title }}
                        <font-awesome-icon icon="fa-solid fa-arrow-down-wide-short" />
                      </span>
                    </span>
                  </div>
                </template>
              </div>

              <div v-if="isLoading" class="container-body is-loading">
                <a-spin :tip="t('files.label.loading')" />
              </div>
              <div v-else-if="data?.length === 0" class="container-body is-loading">
                <a-empty>
                  <template #description>
                    <span>{{ t('global.label.empty') }}</span>
                  </template>
                </a-empty>
              </div>

              <div v-else class="container-body">
                <template v-for="order in ordersWithLatestQuotation" :key="order._id">
                  <div v-bind:class="['row row-body', showClass(order.createdAt)]">
                    <div class="" style="width: 60px !important">
                      <a-checkbox
                        :checked="isOrderSelected(order._id)"
                        @update:checked="(checked) => handleOrderSelection(order, checked)"
                      />
                    </div>
                    <div class="" style="width: 60px !important">
                      <!-- TODO: Revisar por el campo respondedAt -->
                      <template v-if="order.lights.days !== 0 || order.lights.hours !== 0">
                        <a-popover title="" v-if="order.lights">
                          <template #content>
                            <p style="margin-bottom: 0; padding-bottom: 0">
                              <template v-if="order.lights.days" style="display: block">
                                <strong>{{ order.lights.days }}</strong>
                                <span v-if="order.lights.days > 1"> días </span>
                                <span v-else> día </span>
                              </template>
                              <template v-if="order.lights.hours" style="display: block">
                                <span v-if="order.lights?.days">| </span>
                                <strong>{{ order.lights.hours }}</strong> horas
                              </template>
                            </p>
                          </template>
                          <StatusDot :status="order.lights.alert" />
                        </a-popover>
                      </template>
                      <template v-else>
                        <a-popover title="">
                          <template #content>
                            <p style="margin-bottom: 0; padding-bottom: 0">
                              <template style="display: block">
                                <strong>0</strong> hora(s)
                              </template>
                            </p>
                          </template>
                          <StatusDot :status="order.lights.alert" />
                        </a-popover>
                      </template>
                    </div>
                    <div class="col-small">
                      <!--                      <template v-if="order.latestQuotation?.travelDate?.dateTca">-->
                      <!--                        {{ formatDate(order.latestQuotation?.travelDate?.dateTca).date }}-->
                      <!--                      </template>-->
                      <!--                      <template v-else> - </template>-->

                      <template v-if="getTravelDate(order.latestQuotation) !== '-'">
                        {{ formatDate(getTravelDate(order.latestQuotation)).date }}
                      </template>
                      <template v-else> - </template>
                    </div>
                    <div class="col-small">
                      <template v-if="order.metadata.respondedAt">
                        <EditableField
                          @click="showReceivedAtModal(order)"
                          v-if="can('mfmyorders', 'changereceivedat')"
                        >
                          <span>
                            {{ formatDate(order.metadata.respondedAt).date }}
                          </span>
                        </EditableField>
                        <span v-else>
                          {{ formatDate(order.metadata.respondedAt).date }}
                        </span>
                      </template>
                      <template v-else> - </template>
                    </div>
                    <div class="col-small">
                      <a-popover title="">
                        <template #content>
                          <p style="margin-bottom: 0; padding-bottom: 0">
                            <strong style="display: block">Cliente:</strong>
                            ({{ order.customer?.code?.toUpperCase() }}) {{ order.customer.name }}
                          </p>
                        </template>
                        {{ order.customer?.code?.toUpperCase() }}
                      </a-popover>
                    </div>
                    <div class="col-small">
                      <a-popover title="" v-if="order.executive || order.kam">
                        <template #content>
                          <p v-if="order.executive && Object.keys(order.executive).length > 0">
                            <strong>Especialista:</strong><br />
                            ({{ order.executive?.code?.toUpperCase() }})
                            {{ order.executive?.fullname }}
                          </p>
                          <p
                            v-if="order.kam && Object.keys(order.kam).length > 0"
                            style="margin-bottom: 0; padding-bottom: 0"
                          >
                            <strong>Bajo supervisión de:</strong><br />
                            ({{ order.kam?.code?.toUpperCase() }}) {{ order.kam?.fullname }}
                          </p>
                        </template>
                        {{ order.executive?.code?.toUpperCase() }}
                        <span style="display: block; color: #979797; font-size: 12px">
                          <template v-if="order.customer.market.code === '3N'">ES/IT/PT</template>
                          <template v-else>
                            {{ order.customer.market?.name }}
                          </template>
                        </span>
                      </a-popover>
                      <span v-else style="display: block; color: #979797; font-size: 12px">
                        {{ order.customer.market?.name }}
                      </span>
                    </div>
                    <div class="col-small">
                      <span
                        v-if="
                          order.latestQuotation?.prices && order.latestQuotation?.prices?.estimated
                        "
                      >
                        {{
                          order.latestQuotation?.prices?.moneda +
                          ' ' +
                          parseFloat(order.latestQuotation?.prices?.estimated).toFixed(2)
                        }}
                      </span>
                      <span v-else> - </span>
                    </div>
                    <div class="col-small">
                      <div>
                        {{ order.code }}
                        <span
                          style="display: block; color: #979797; font-size: 12px"
                          v-if="order.latestQuotation?.number"
                        >
                          (Cot. {{ order.latestQuotation?.number }})
                        </span>
                      </div>
                    </div>
                    <div class="col-small">
                      <span style="padding: 10px !important">
                        <small>{{ order.type.name }}</small>
                      </span>
                    </div>
                    <div class="col-small" v-if="can('mfmyorders', 'changestatus')">
                      <StatusTag
                        :currentStatus="order.currentStatus"
                        :order="order"
                        @update-status="showUpdateStatusModal"
                      />
                    </div>
                    <div class="col-small col-body-options">
                      <a
                        v-if="order.versionList?.length > 1"
                        href="javascript:;"
                        class="text-danger"
                        @click="toggleShowQuotations(order._id)"
                      >
                        <b>
                          <font-awesome-icon
                            :icon="['fas', expandedRows[order._id] ? 'chevron-up' : 'chevron-down']"
                          />
                        </b>
                      </a>

                      <popover-hover-and-click
                        v-if="can('mfmyorders', 'sendtemplate')"
                        :buttons="false"
                        placementClick="bottom"
                        ref="popoverRef"
                      >
                        <font-awesome-icon :icon="['fas', 'paper-plane']" />
                        <template #content-hover>Plantillas</template>
                        <template #content-click>
                          <div class="actions-content-click" style="width: auto">
                            <div
                              class="actions-content-click-content"
                              v-if="order.filteredActiveTemplates.length > 0"
                            >
                              <a-button
                                v-for="template in order.filteredActiveTemplates"
                                :key="template._id"
                                type="link"
                                style="height: auto; font-size: 16px"
                                size="small"
                                @click="showOrderModal(template, order)"
                              >
                                <div
                                  style="
                                    display: flex;
                                    gap: 5px;
                                    justify-content: flex-start;
                                    align-items: center;
                                  "
                                >
                                  <span style="font-weight: 500">{{ template.name }}</span>
                                </div>
                              </a-button>
                            </div>
                            <div v-else style="padding: 8px; text-align: center; color: #888">
                              No hay plantillas activas.
                            </div>
                          </div>
                        </template>
                      </popover-hover-and-click>

                      <popover-hover-and-click
                        v-if="can('mfmyorders', 'viewhistory')"
                        :buttons="false"
                        ref="popoverRef"
                      >
                        <font-awesome-icon
                          :icon="['fas', 'clock-rotate-left']"
                          :class="!order.followUps ? 'color-gray' : ''"
                        />
                        <template #content-hover>Historial de seguimientos</template>
                        <template #content-click>
                          <FollowUpHistory :followUps="order.followUps" />
                        </template>
                      </popover-hover-and-click>

                      <popover-hover-and-click
                        :buttons="false"
                        placementClick="bottom"
                        ref="popoverRef"
                      >
                        <font-awesome-icon icon="fa-solid fa-ellipsis-vertical" size="sm" />
                        <template #content-hover>Acciones de cotización</template>
                        <template #content-click>
                          <div class="actions-content-click-content">
                            <!-- Ver cotización -->
                            <a-button
                              type="link"
                              :href="quoteUrl"
                              target="_blank"
                              style="height: auto; font-size: 16px"
                              size="small"
                            >
                              <div
                                style="
                                  display: flex;
                                  gap: 5px;
                                  justify-content: flex-start;
                                  align-items: center;
                                "
                              >
                                <font-awesome-icon icon="eye" />
                                <span style="font-weight: 500">Ver cotización</span>
                              </div>
                            </a-button>

                            <!-- Ver último correo -->
                            <a-button
                              v-if="can('mfmyorders', 'viewmail')"
                              type="link"
                              style="height: auto; font-size: 16px"
                              size="small"
                              @click="showEmailsModal(order)"
                            >
                              <div
                                style="
                                  display: flex;
                                  gap: 5px;
                                  justify-content: flex-start;
                                  align-items: center;
                                "
                              >
                                <font-awesome-icon icon="fa-regular fa-envelope" />
                                <span style="font-weight: 500">Ver último correo</span>
                              </div>
                            </a-button>
                            <a-button
                              v-if="
                                ['NC', 'NR'].includes(order.currentStatus?.iso) &&
                                can('mfmyorders', 'viewcancellation')
                              "
                              type="link"
                              style="height: auto; font-size: 16px"
                              size="small"
                              @click="showCancellationModal(order)"
                            >
                              <div
                                style="
                                  display: flex;
                                  gap: 5px;
                                  justify-content: flex-start;
                                  align-items: center;
                                "
                              >
                                <font-awesome-icon icon="fa-regular fa-circle-xmark" />
                                <span style="font-weight: 500">Razón de cancelación</span>
                              </div>
                            </a-button>
                            <a-button
                              v-if="can('mfmyorders', 'reassign')"
                              type="link"
                              style="height: auto; font-size: 16px"
                              size="small"
                              @click="showReassignModal(order)"
                            >
                              <div
                                style="
                                  display: flex;
                                  gap: 5px;
                                  justify-content: flex-start;
                                  align-items: center;
                                "
                              >
                                <font-awesome-icon icon="fa-solid fa-arrow-right-arrow-left" />
                                <span style="font-weight: 500">Reasignar pedido</span>
                              </div>
                            </a-button>
                            <a-button
                              v-if="can('mfmyorders', 'editorder')"
                              type="link"
                              style="height: auto; font-size: 16px"
                              size="small"
                              @click="showEditOrderModal(order)"
                            >
                              <div
                                style="
                                  display: flex;
                                  gap: 5px;
                                  justify-content: flex-start;
                                  align-items: center;
                                "
                              >
                                <font-awesome-icon icon="fa-solid fa-pen-to-square" />
                                <span style="font-weight: 500">Editar pedido</span>
                              </div>
                            </a-button>
                            <a-button
                              v-if="can('mfmyorders', 'editresponse')"
                              type="link"
                              style="height: auto; font-size: 16px"
                              size="small"
                              @click="showEditResponseModal(order)"
                            >
                              <div
                                style="
                                  display: flex;
                                  gap: 5px;
                                  justify-content: flex-start;
                                  align-items: center;
                                "
                              >
                                <font-awesome-icon icon="fa-solid fa-pen-to-square" />
                                <span style="font-weight: 500">Editar respuesta</span>
                              </div>
                            </a-button>
                            <a-button
                              v-if="can('mfmyorders', 'editobservation')"
                              type="link"
                              style="height: auto; font-size: 16px"
                              size="small"
                              @click="showObservationsModal(order)"
                            >
                              <div
                                style="
                                  display: flex;
                                  gap: 5px;
                                  justify-content: flex-start;
                                  align-items: center;
                                "
                              >
                                <font-awesome-icon icon="fa-regular fa-file-lines" />
                                <span style="font-weight: 500">Observaciones</span>
                              </div>
                            </a-button>
                            <a-button
                              v-if="can('mfmyorders', 'cancel')"
                              type="link"
                              style="height: auto; font-size: 16px"
                              size="small"
                              @click="showCancelOrderModal(order)"
                            >
                              <div
                                style="
                                  display: flex;
                                  gap: 5px;
                                  justify-content: flex-start;
                                  align-items: center;
                                "
                              >
                                <font-awesome-icon icon="fa-solid fa-trash-can" />
                                <span style="font-weight: 500">Anular pedido</span>
                              </div>
                            </a-button>
                          </div>
                        </template>
                      </popover-hover-and-click>
                    </div>
                  </div>

                  <div
                    v-if="expandedRows[order._id] && order.versionList.length > 1"
                    class="version-history-container"
                  >
                    <template v-for="(version, index) in order.versionList" :key="version.order">
                      <div class="row version-history-row g-0">
                        <div class="version-spacer"></div>
                        <div class="version-spacer"></div>

                        <div class="version-col version-date-wrapper">
                          {{ formatDateShort(version.metadata?.receivedAt || version.createdAt) }}
                        </div>

                        <div class="version-col version-wide-content">
                          <div class="version-content-wrapper">
                            <template
                              v-if="
                                (version.lights?.days || 0) !== 0 ||
                                (version.lights?.hours || 0) !== 0
                              "
                            >
                              <a-popover title="">
                                <template #content>
                                  <p style="margin-bottom: 0; padding-bottom: 0">
                                    <template v-if="version.lights?.days" style="display: block">
                                      <strong>{{ version.lights.days }}</strong>
                                      <span v-if="version.lights.days > 1"> días </span>
                                      <span v-else> día </span>
                                    </template>
                                    <template v-if="version.lights?.hours" style="display: block">
                                      <span v-if="version.lights?.days">| </span>
                                      <strong>{{ version.lights.hours }}</strong> horas
                                    </template>
                                  </p>
                                </template>
                                <StatusDot
                                  :status="version.lights?.alert || 'success'"
                                  :minimal="true"
                                  style="margin-right: 8px; cursor: pointer"
                                />
                              </a-popover>
                            </template>

                            <!-- Caso 0 horas (Recién creado) -->
                            <template v-else>
                              <a-popover title="">
                                <template #content>
                                  <p style="margin-bottom: 0; padding-bottom: 0">
                                    <strong>0</strong> horas
                                  </p>
                                </template>
                                <StatusDot
                                  :status="version.lights?.alert || 'success'"
                                  :minimal="true"
                                  style="margin-right: 8px; cursor: pointer"
                                />
                              </a-popover>
                            </template>

                            <span class="version-detail-code">#{{ order.code }}</span>

                            <span class="version-detail-quotations">
                              (Cot: {{ version.quotationString }})
                            </span>

                            <span v-if="version.fileNumber" class="version-file-tag">
                              File: {{ version.fileNumber }}
                            </span>

                            <a-tag v-if="index === 0" color="blue" class="version-tag-status">
                              Última versión
                            </a-tag>
                          </div>
                        </div>

                        <div class="version-col version-detail-actions">
                          <div class="meta-wrapper">
                            <div class="gray-dot"></div>
                            <span class="version-meta-info">
                              {{ version.executive?.code || 'N/A' }}
                              {{
                                formatTimeShort(
                                  version.metadata?.respondedAt || version.metadata?.receivedAt
                                )
                              }}
                            </span>
                          </div>

                          <div class="col-small col-body-options">
                            <popover-hover-and-click
                              v-if="can('mfmyorders', 'sendtemplate')"
                              :buttons="false"
                              ref="popoverRef"
                            >
                              <font-awesome-icon
                                :icon="['fas', 'clock-rotate-left']"
                                :class="!version.followUps ? 'color-gray' : ''"
                              />
                              <template #content-hover>Historial de seguimientos</template>
                              <template #content-click>
                                <FollowUpHistory :followUps="version.followUps" />
                              </template>
                            </popover-hover-and-click>

                            <popover-hover-and-click
                              :buttons="false"
                              placementClick="bottom"
                              ref="popoverRef"
                            >
                              <font-awesome-icon icon="fa-solid fa-ellipsis-vertical" size="sm" />
                              <template #content-hover>Acciones de cotización</template>
                              <template #content-click>
                                <div class="actions-content-click-content">
                                  <!-- Ver cotización -->
                                  <a-button
                                    type="link"
                                    :href="quoteUrl"
                                    target="_blank"
                                    style="height: auto; font-size: 16px"
                                    size="small"
                                  >
                                    <div
                                      style="
                                        display: flex;
                                        gap: 5px;
                                        justify-content: flex-start;
                                        align-items: center;
                                      "
                                    >
                                      <font-awesome-icon icon="eye" />
                                      <span style="font-weight: 500">Ver cotización</span>
                                    </div>
                                  </a-button>

                                  <!-- Ver último correo -->
                                  <a-button
                                    v-if="can('mfmyorders', 'viewmail')"
                                    type="link"
                                    style="height: auto; font-size: 16px"
                                    size="small"
                                    @click="showEmailsModal(version)"
                                  >
                                    <div
                                      style="
                                        display: flex;
                                        gap: 5px;
                                        justify-content: flex-start;
                                        align-items: center;
                                      "
                                    >
                                      <font-awesome-icon icon="fa-regular fa-envelope" />
                                      <span style="font-weight: 500">Ver último correo</span>
                                    </div>
                                  </a-button>
                                  <a-button
                                    v-if="
                                      ['NC', 'NR'].includes(version.currentStatus?.iso) &&
                                      can('mfmyorders', 'viewcancellation')
                                    "
                                    type="link"
                                    style="height: auto; font-size: 16px"
                                    size="small"
                                    @click="showCancellationModal(version)"
                                  >
                                    <div
                                      style="
                                        display: flex;
                                        gap: 5px;
                                        justify-content: flex-start;
                                        align-items: center;
                                      "
                                    >
                                      <font-awesome-icon icon="fa-regular fa-circle-xmark" />
                                      <span style="font-weight: 500">Razón de cancelación</span>
                                    </div>
                                  </a-button>
                                  <a-button
                                    v-if="can('mfmyorders', 'reassign')"
                                    type="link"
                                    style="height: auto; font-size: 16px"
                                    size="small"
                                    @click="showReassignModal(version)"
                                  >
                                    <div
                                      style="
                                        display: flex;
                                        gap: 5px;
                                        justify-content: flex-start;
                                        align-items: center;
                                      "
                                    >
                                      <font-awesome-icon
                                        icon="fa-solid fa-arrow-right-arrow-left"
                                      />
                                      <span style="font-weight: 500">Reasignar pedido</span>
                                    </div>
                                  </a-button>
                                  <a-button
                                    v-if="can('mfmyorders', 'editorder')"
                                    type="link"
                                    style="height: auto; font-size: 16px"
                                    size="small"
                                    @click="showEditOrderModal(version)"
                                  >
                                    <div
                                      style="
                                        display: flex;
                                        gap: 5px;
                                        justify-content: flex-start;
                                        align-items: center;
                                      "
                                    >
                                      <font-awesome-icon icon="fa-solid fa-pen-to-square" />
                                      <span style="font-weight: 500">Editar pedido</span>
                                    </div>
                                  </a-button>
                                  <a-button
                                    v-if="can('mfmyorders', 'editresponse')"
                                    type="link"
                                    style="height: auto; font-size: 16px"
                                    size="small"
                                    @click="showEditResponseModal(version)"
                                  >
                                    <div
                                      style="
                                        display: flex;
                                        gap: 5px;
                                        justify-content: flex-start;
                                        align-items: center;
                                      "
                                    >
                                      <font-awesome-icon icon="fa-solid fa-pen-to-square" />
                                      <span style="font-weight: 500">Editar respuesta</span>
                                    </div>
                                  </a-button>
                                  <a-button
                                    v-if="can('mfmyorders', 'editobservation')"
                                    type="link"
                                    style="height: auto; font-size: 16px"
                                    size="small"
                                    @click="showObservationsModal(version)"
                                  >
                                    <div
                                      style="
                                        display: flex;
                                        gap: 5px;
                                        justify-content: flex-start;
                                        align-items: center;
                                      "
                                    >
                                      <font-awesome-icon icon="fa-regular fa-file-lines" />
                                      <span style="font-weight: 500">Observaciones</span>
                                    </div>
                                  </a-button>
                                  <a-button
                                    v-if="can('mfmyorders', 'cancel')"
                                    type="link"
                                    style="height: auto; font-size: 16px"
                                    size="small"
                                    @click="showCancelOrderModal(version)"
                                  >
                                    <div
                                      style="
                                        display: flex;
                                        gap: 5px;
                                        justify-content: flex-start;
                                        align-items: center;
                                      "
                                    >
                                      <font-awesome-icon icon="fa-solid fa-trash-can" />
                                      <span style="font-weight: 500">Anular pedido</span>
                                    </div>
                                  </a-button>
                                </div>
                              </template>
                            </popover-hover-and-click>
                          </div>
                        </div>
                      </div>
                    </template>
                  </div>
                </template>
              </div>

              <div class="row g-0 row-pagination">
                <a-pagination
                  v-model:current="currentPageValue"
                  v-model:pageSize="currentPageSize"
                  :disabled="data?.length === 0"
                  :pageSizeOptions="DEFAULT_PAGE_SIZE_OPTIONS"
                  :total="total"
                  show-size-changer
                  show-quick-jumper-off
                  @change="onChange"
                  @showSizeChange="onShowSizeChange"
                >
                  <template #buildOptionText="props">
                    <span>{{ props.value }}</span>
                  </template>
                </a-pagination>

                <template v-if="!formFilter.flag_stella && can('mfmyorders', 'exportexcel')">
                  <a-button
                    type="primary"
                    danger
                    ghost
                    size="large"
                    :disabled="orderStore.isDownloading"
                    :loading="orderStore.isDownloading"
                    v-if="data?.length > 0"
                    @click="handleDownloadList"
                  >
                    <template #icon>
                      <font-awesome-icon :icon="['fas', 'download']" />
                    </template>
                    <span class="ms-2">Descargar Listado</span>
                  </a-button>
                </template>
              </div>
            </div>
          </a-tab-pane>
          <a-tab-pane key="2" :class="{ 'block-style': !flagEditable }" :disabled="true">
            <template #tab>
              <font-awesome-icon :icon="['fas', 'chart-pie']" style="margin-right: 4px" />
              <span style="text-transform: capitalize">Dashboard</span>
            </template>
            <FilesEditPaxs />
          </a-tab-pane>
        </a-tabs>
      </template>
    </div>

    <!-- Modal: Ver último correo -->
    <BaseModal
      width="750px"
      ref="emailsModalRef"
      :open="modalEmails"
      title="Correos asociados"
      okText="Cerrar"
      cancelText="Cancelar"
      :show-cancel-button="false"
      :validateForm="false"
      :okLoading="isLoadingEmails"
      @ok="handleOkEmails"
      @cancel="handleCancelEmails"
    >
      <div v-if="selectedOrderForEmails?.emailLogs?.length > 0">
        <a-collapse v-model:activeKey="activeKeyCollapse" accordion>
          <a-collapse-panel
            v-for="(log, index) in [...selectedOrderForEmails.emailLogs].reverse()"
            :key="index"
          >
            <template #header>
              <div style="text-align: right; width: 100%">
                {{ formatDate(log.createdAt).date + ' ' + formatDate(log.createdAt).time }}
              </div>
            </template>
            <div v-html="log.html" style="overflow: auto; text-align: justify !important"></div>
          </a-collapse-panel>
        </a-collapse>
      </div>
      <a-empty v-else description="No se encontraron correos para este pedido." />
    </BaseModal>

    <!-- Modal: Observaciones del pedido -->
    <BaseModal
      width="500px"
      ref="observationsModalRef"
      :open="modalObservations"
      title="Observaciones"
      okText="Guardar"
      cancelText="Cancelar"
      showFooter
      :validateForm="true"
      :okLoading="isLoadingObservations"
      @ok="handleOkObservations"
      @cancel="handleCancelObservations"
    >
      <a-form layout="vertical">
        <a-textarea
          v-model:value="observationText"
          :disabled="isLoadingObservations"
          placeholder="Escribe tus observaciones aquí..."
          :rows="4"
        />
      </a-form>
    </BaseModal>

    <!-- Modal: Razón de cancelación -->
    <BaseModal
      width="600px"
      ref="cancellationModalRef"
      :open="modalCancellation"
      title="Razón de cancelación"
      okText="Guardar"
      cancelText="Cancelar"
      showFooter
      :validateForm="true"
      :okLoading="isLoadingCancellation"
      @ok="handleOkCancellation"
      @cancel="handleCancelCancellation"
    >
      <a-form
        layout="vertical"
        :model="formCancellationState"
        :rules="cancellationRules"
        ref="cancellationModalRef?.formRef"
        style="padding: 20px; background-color: #f8f8f8; border-radius: 10px"
      >
        <a-form-item label="Seleccionar razón:" name="reason">
          <a-select
            v-model:value="formCancellationState.reason"
            :options="cancellationReasonStore.getCancellationReasons"
            :loading="cancellationReasonStore.isLoading"
            placeholder="Seleccione una razón"
            style="text-align: left"
          />
        </a-form-item>

        <a-form-item label="Observaciones:" name="observation">
          <a-textarea
            v-model:value="formCancellationState.observation"
            placeholder="Escriba sus observaciones"
            :rows="4"
          />
        </a-form-item>
      </a-form>
    </BaseModal>

    <!-- Modal: Editar Fecha de Recepción -->
    <BaseModal
      width="500px"
      ref="receivedAtModalRef"
      :open="modalReceivedAt"
      title="Editar Fecha de Recepción"
      okText="Guardar"
      cancelText="Cancelar"
      showFooter
      :validateForm="true"
      :okLoading="isLoadingReceivedAt"
      @ok="handleOkReceivedAt"
      @cancel="handleCancelReceivedAt"
    >
      <a-form
        ref="receivedAtFormRef"
        layout="vertical"
        :model="formReceivedAtState"
        :rules="{
          date: [{ required: true, message: 'La fecha es requerida.' }],
        }"
        style="padding: 20px; background-color: #f8f8f8; border-radius: 10px"
      >
        <a-form-item label="Fecha de recepción:" name="date">
          <a-date-picker
            v-model:value="formReceivedAtState.date"
            style="width: 100%"
            placeholder="Seleccione la fecha"
            format="DD/MM/YYYY"
          />
        </a-form-item>
      </a-form>
    </BaseModal>

    <!-- Modal: Editar Pedido -->
    <BaseModal
      width="600px"
      ref="editOrderModalRef"
      :open="modalEditOrder"
      title="Editar Pedido"
      okText="Guardar"
      cancelText="Cancelar"
      showFooter
      :validateForm="true"
      :okLoading="isLoadingEditOrder"
      @ok="handleOkEditOrder"
      @cancel="handleCancelEditOrder"
    >
      <a-form
        ref="editOrderFormRef"
        layout="vertical"
        :model="formEditState"
        :rules="editOrderRules"
        style="padding: 20px; background-color: #f8f8f8; border-radius: 10px"
      >
        <base-input
          v-model:value="formEditState.groupName"
          placeholder="Ingrese el nombre"
          label="Nombre del Pasajero/Grupo:"
          name="groupName"
          size="small"
        />

        <base-select
          v-model:value="formEditState.product"
          :options="productStore.getProducts"
          placeholder="Seleccione el producto"
          label="Producto:"
          name="product"
          size="small"
          :show-search="false"
          :loading="productStore.isLoading"
          style="margin-bottom: 10px"
        />

        <base-select
          v-model:value="formEditState.customer"
          :options="customerStore.getCustomersWithFullLabel"
          placeholder="Seleccione el cliente"
          label="Cliente:"
          name="customer"
          size="small"
          :loading="customerStore.isLoading"
          style="margin-bottom: 10px"
        />

        <!-- <a-form-item label="Cliente:" name="customer">
          <a-input v-model:value="formEditState.customer" placeholder="Ingrese el cliente" />
        </a-form-item> -->
        <!-- <a-form-item label="Referencia:" name="referencia">
          <a-input v-model:value="formEditState.referencia" placeholder="Ingrese la referencia" />
        </a-form-item> -->

        <base-input
          v-model:value="formEditState.externalReference"
          placeholder="Ingrese la referencia"
          label="Referencia:"
          name="externalReference"
          size="small"
        />

        <a-form-item label="Observaciones:" name="observaciones">
          <a-textarea
            v-model:value="formEditState.observation"
            placeholder="Escriba sus observaciones"
            :rows="4"
          />
        </a-form-item>
      </a-form>
    </BaseModal>

    <!-- Modal: Editar Respuesta -->
    <BaseModal
      width="500px"
      ref="editResponseModalRef"
      :open="modalEditResponse"
      title="Editar Respuesta"
      okText="Guardar"
      cancelText="Cancelar"
      showFooter
      :validateForm="true"
      :okLoading="isLoadingEditResponse"
      @ok="handleOkEditResponse"
      @cancel="handleCancelEditResponse"
    >
      <a-form
        ref="editResponseFormRef"
        layout="vertical"
        :model="formEditResponseState"
        :rules="editResponseRules"
        style="padding: 20px; background-color: #f8f8f8; border-radius: 10px"
      >
        <a-form-item label="Fecha de respuesta:" name="responded_at_date">
          <a-date-picker
            v-model:value="formEditResponseState.responded_at_date"
            style="width: 100%"
            format="DD/MM/YYYY"
            placeholder="Seleccione la fecha"
          />
        </a-form-item>
        <a-form-item label="Hora de respuesta:" name="responded_at_hour">
          <a-time-picker
            v-model:value="formEditResponseState.responded_at_hour"
            style="width: 100%"
            placeholder="Seleccione la hora"
            format="HH:mm"
          />
        </a-form-item>
      </a-form>
    </BaseModal>

    <!-- Modal: Anular Pedido -->
    <BaseModal
      width="450px"
      ref="cancelOrderModalRef"
      :open="modalCancelOrder"
      title="Anular Pedido"
      okText="Sí, anular"
      cancelText="Cancelar"
      showFooter
      :validateForm="false"
      :okLoading="isLoadingCancelOrder"
      @ok="handleOkCancelOrder"
      @cancel="handleCancelCancelOrder"
    >
      <p>
        ¿Estás seguro de que deseas anular el pedido
        <strong>{{ selectedOrderForCancel?.code }}</strong
        >? Esta acción no se puede deshacer.
      </p>
    </BaseModal>

    <!-- Modal: Enviar seguimiento -->
    <BaseModal
      width="450px"
      ref="modalRef"
      :open="modalIsOpenOrder"
      title="Enviar seguimiento"
      okText="Sí, continuar"
      cancelText="Cancelar"
      showFooter
      :validateForm="false"
      :okLoading="isLoadingFollowUp"
      @ok="handleDownload"
      @cancel="handleCancelFollowUp"
    >
      <p>
        Estás a punto de enviar el seguimiento <strong>"{{ selectedTemplate?.name }}"</strong> al
        pedido <strong>{{ selectedOrderForFollowUp?.code }}</strong
        >. ¿Estás seguro?
      </p>
    </BaseModal>

    <!-- Modal: Enviar seguimiento masivo -->
    <BaseModal
      width="450px"
      ref="bulkFollowUpModalRef"
      :open="modalIsOpenBulkFollowUp"
      title="Enviar seguimiento masivo"
      okText="Sí, enviar"
      cancelText="Cancelar"
      showFooter
      :validateForm="false"
      :okLoading="isLoadingFollowUp"
      @ok="handleOkBulkFollowUp"
      @cancel="handleCancelBulkFollowUp"
    >
      <p>
        Estás a punto de enviar el seguimiento
        <strong>"{{ selectedTemplateForBulk?.name }}"</strong> a
        <strong v-if="selectAllGlobal">TODOS los {{ total - excludedIds.size }}</strong>
        <strong v-else>{{ finalOrdersToProcessCount }}</strong>
        pedido(s) único(s). ¿Estás seguro?
      </p>
    </BaseModal>

    <!-- Modal: Cambiar estado de pedido -->
    <BaseModal
      width="500px"
      ref="updateStatusModalRef"
      :open="modalUpdateStatus"
      title="Actualizar estado de pedido"
      okText="Guardar"
      cancelText="Cancelar"
      showFooter
      :validateForm="true"
      :okLoading="isLoadingUpdateStatus"
      @ok="handleOkUpdateStatus"
      @cancel="handleCancelUpdateStatus"
    >
      <a-form
        ref="updateStatusFormRef"
        layout="vertical"
        :model="formUpdateStatusState"
        :rules="updateStatusRules"
        style="padding: 20px; background-color: #f8f8f8; border-radius: 10px"
      >
        <!--        :options="orderStatusStore.getOrderStatuses"-->
        <a-form-item label="Seleccionar estado:" name="statusId">
          <a-select
            v-model:value="formUpdateStatusState.statusId"
            :options="filteredOrderStatuses"
            placeholder="Seleccione un estado"
            style="text-align: left"
          />
        </a-form-item>
      </a-form>
    </BaseModal>

    <!-- Modal: Cambiar estado masivo -->
    <BaseModal
      width="450px"
      ref="bulkStatusUpdateModalRef"
      :open="modalIsOpenBulkStatusUpdate"
      title="Cambiar estado masivo"
      okText="Sí, cambiar"
      cancelText="Cancelar"
      showFooter
      :validateForm="false"
      :okLoading="isLoadingBulkStatusUpdate"
      @ok="handleOkBulkStatusUpdate"
      @cancel="handleCancelBulkStatusUpdate"
    >
      <p>
        Estás a punto de cambiar el estado de
        <strong v-if="selectAllGlobal">TODOS los {{ total - excludedIds.size }}</strong>
        <strong v-else>{{ selectedItemsMap.size }}</strong>
        pedido(s) seleccionado(s). ¿Estás seguro?
      </p>
    </BaseModal>

    <!-- Modal: Reasignar Pedido -->
    <BaseModal
      width="600px"
      ref="reassignOrderModalRef"
      :open="modalReassignOrder"
      title="Reasignar pedido"
      okText="Reasignar"
      cancelText="Cancelar"
      showFooter
      :validateForm="true"
      :okLoading="isLoadingReassignOrder"
      @ok="handleOkReassignOrder"
      @cancel="handleCancelReassignOrder"
    >
      <a-form
        ref="reassignOrderFormRef"
        layout="vertical"
        :model="formReassignState"
        :rules="reassignOrderRules"
        style="padding: 20px; background-color: #f8f8f8; border-radius: 10px"
      >
        <base-select
          v-model:value="formReassignState.product"
          :loading="productStore.isLoading"
          :options="productStore.getProducts"
          placeholder="Seleccione el producto"
          label="Producto:"
          name="product"
          size="small"
          :show-search="true"
          style="margin-bottom: 10px"
        />

        <!-- RADIO BUTTONS: TIPO DE ASIGNACIÓN -->
        <a-form-item label="Tipo de Asignación:" style="margin-bottom: 15px">
          <a-radio-group v-model:value="assignType" name="assignType">
            <a-radio value="R">Por Región (Automático)</a-radio>
            <a-radio value="E">Por Ejecutivo (Manual)</a-radio>
          </a-radio-group>
        </a-form-item>

        <!-- REGIÓN (Solo si assignType == 'R') -->
        <div v-if="assignType === 'R'">
          <base-select
            v-model:value="formReassignState.region"
            :options="[
              { value: 1, label: 'C1' },
              { value: 2, label: 'C2' },
              { value: 3, label: 'C3' },
              { value: 4, label: 'C4' },
            ]"
            placeholder="Mercado"
            label="Región:"
            name="region"
            size="small"
            :show-search="true"
            :loading="marketStore.isLoading"
            style="margin-bottom: 10px"
          />
        </div>

        <!-- EJECUTIVO (Solo si assignType == 'E') -->
        <div v-if="assignType === 'E'">
          <base-select
            v-model:value="formReassignState.executive"
            :loading="userStore.isLoadingExternal"
            :options="userStore.getExternalUsers"
            placeholder="Seleccionar especialista"
            label="Especialista:"
            name="executive"
            size="small"
            :show-search="true"
            style="margin-bottom: 10px"
          />
        </div>
      </a-form>
    </BaseModal>
  </div>
</template>

<script setup>
  import { checkDates } from '@/utils/files.js';
  import dayjs from 'dayjs';
  import { computed, onBeforeMount, reactive, ref, watch } from 'vue';

  import { useRouter } from 'vue-router';

  import { usePermission } from '@ordercontrol/composables/usePermission';

  import BaseInput from '@/components/files/reusables/BaseInput.vue';
  import BaseSelect from '@/components/files/reusables/BaseSelect.vue';
  import BaseInputSearch from '@/components/files/reusables/BaseInputSearch.vue';
  import BaseButton from '@/components/files/reusables/BaseButton.vue';

  import BaseSelectMultiple from '@/components/files/reusables/BaseSelectMultiple.vue';
  import PopoverHoverAndClick from '@/components/files/reusables/PopoverHoverAndClick.vue';

  import BaseModal from '@ordercontrol/components/BaseModal.vue';
  import FollowUpHistory from '@ordercontrol/components/FollowUpHistory.vue';
  import EditableField from '@ordercontrol/shared/EditableField.vue';

  import { useOrderStore } from '@ordercontrol/store/order.store';
  import { useMarketStore } from '@ordercontrol/store/market.store';
  import { useCustomerStore } from '@ordercontrol/store/customer.store';
  import { useTemplateStore } from '@ordercontrol/store/template.store';
  import { useProductStore } from '@ordercontrol/store/product.store';
  import { useGeneralStore } from '@ordercontrol/store/general.store';
  import { useUserStore } from '@ordercontrol/store/user.store';
  import { useOrderStatusStore } from '@ordercontrol/store/order-status.store';
  import { useCancellationReasonStore } from '@ordercontrol/store/cancellation-reason.store';
  import { useOrderControlResourceStore } from '@ordercontrol/store/orderControlResource.store';

  import { notification } from 'ant-design-vue';
  import { debounce } from 'lodash-es';
  import { useI18n } from 'vue-i18n';

  import StatusDot from '@ordercontrol/shared/StatusDot.vue';
  import StatusTag from '@ordercontrol/shared/StatusTag.vue';

  import { InfoCircleOutlined, EyeOutlined, EyeInvisibleOutlined } from '@ant-design/icons-vue';

  const getTravelDate = (quotation) => {
    if (!quotation || !quotation.travelDate) return '-';

    const date = quotation.travelDate.date;
    console.log(date);
    const dateTca = quotation.travelDate.dateTca;
    console.log(dateTca);

    return date || dateTca || '-';
  };

  // --- LÓGICA DE SELECCIÓN GLOBAL ---
  const selectAllGlobal = ref(false); // Bandera para "Seleccionar todas las páginas"
  const selectedItemsMap = ref(new Map()); // Selección Manual: ID -> Objeto
  const excludedIds = ref(new Set()); // IDs que el usuario desmarcó estando en modo Global

  // Toggle del Header (Select All)
  const toggleSelectAll = (e) => {
    const checked = e.target.checked;
    selectAllGlobal.value = checked;

    // Reseteamos las selecciones manuales y exclusiones al cambiar el maestro
    selectedItemsMap.value.clear();
    excludedIds.value.clear();
  };

  const validateGlobalFilters = () => {
    // Si NO es selección global, pasa (true)
    if (!selectAllGlobal.value) return true;

    const f = formFilter;
    // Verifica si existe algún filtro con valor
    const hasActiveFilter =
      (f.filter && f.filter.trim() !== '') ||
      f.status ||
      (Array.isArray(f.market) && f.market.length > 0) ||
      f.executive ||
      (Array.isArray(f.customer) && f.customer.length > 0) ||
      (Array.isArray(f.travel_date) && f.travel_date.length > 0) ||
      (Array.isArray(f.last_communication) && f.last_communication.length > 0) ||
      (Array.isArray(f.templates) && f.templates.length > 0);

    if (!hasActiveFilter) {
      notification.warn({
        message: 'Acción restringida',
        description:
          'Por seguridad, debe aplicar al menos un filtro para acciones masivas globales.',
        duration: 5,
      });
      return false; // Bloquea la acción
    }
    return true; // Permite continuar
  };

  const isOrderSelected = (orderId) => {
    if (selectAllGlobal.value) {
      // En modo global, está seleccionado si NO está en la lista de excluidos
      return !excludedIds.value.has(orderId);
    }
    // En modo manual, está seleccionado si está en el mapa
    return selectedItemsMap.value.has(orderId);
  };

  const dateFilterOpen = ref(false);
  const tempDateState = ref([]); // Almacena la selección antes de dar "Aplicar"

  // Computed para saber si hay un filtro activo (para pintar el ícono de rojo)
  const hasDateFilter = computed(() => {
    return formFilter.travel_date && formFilter.travel_date.length > 0;
  });

  const filteredOrderStatuses = computed(() => {
    const statuses = orderStatusStore.getOrderStatuses || [];

    // Si tiene el permiso 'bds', puede ver todos (incluido CB)
    if (can('mfmyorders', 'bds')) {
      return statuses;
    }

    // Si NO tiene permiso, filtramos aquellos cuyo iso sea 'CB'
    return statuses.filter((status) => status.label !== 'Concretado BDS');
  });

  const onDateFilterVisibleChange = (visible) => {
    if (visible) {
      if (formFilter.travel_date && formFilter.travel_date.length) {
        tempDateState.value = [...formFilter.travel_date];
      } else {
        tempDateState.value = [];
      }
    }
  };

  const handleClearDateFilter = () => {
    tempDateState.value = [];
    formFilter.travel_date = null;

    dateFilterOpen.value = false;
    handleSearch();
  };

  const handleApplyDateFilter = () => {
    if (tempDateState.value && tempDateState.value.length === 2) {
      formFilter.travel_date = tempDateState.value;
    } else {
      formFilter.travel_date = null;
    }

    dateFilterOpen.value = false;
    handleSearch();
  };

  const templateFilterOpen = ref(false);
  const tempTemplateState = ref([]); // IDs de plantillas seleccionadas temporalmente
  const templateSearchTerm = ref('');

  // Computed: ¿Hay filtro activo?
  const hasTemplateFilter = computed(() => {
    // Verificamos si existe el array 'templates' en formFilter y si tiene elementos
    return formFilter.templates && formFilter.templates.length > 0;
  });

  const showAllTemplates = ref(false); // Estado del toggle (false = filtradas, true = todas)
  const isLoadingAllTemplates = ref(false); // Loading específico para la carga bajo demanda

  // Computed que decide qué lista de opciones base usar
  const sourceTemplateOptions = computed(() => {
    if (showAllTemplates.value) {
      return orderStore.getAllUsedTemplatesOptions || [];
    }
    return orderStore.getUsedTemplatesOptions || [];
  });

  const filteredTemplateOptions = computed(() => {
    const source = sourceTemplateOptions.value;

    // 2. Si no hay término de búsqueda, devolvemos la fuente tal cual
    if (!templateSearchTerm.value || templateSearchTerm.value.trim() === '') {
      return source; // <--- CORRECCIÓN: Devolver 'source', no 'templateOptions'
    }

    // 3. Si hay búsqueda, filtramos sobre la fuente seleccionada
    const term = templateSearchTerm.value.toLowerCase();
    return source.filter((t) => t.label.toLowerCase().includes(term));
  });

  const toggleShowAllTemplates = async () => {
    // Si vamos a mostrar todas y aún no están cargadas en el store...
    if (!showAllTemplates.value && orderStore.getAllUsedTemplatesOptions.length === 0) {
      isLoadingAllTemplates.value = true;
      try {
        await orderStore.fetchUsedTemplates();
      } catch (error) {
        console.error('Error cargando plantillas globales', error);
      } finally {
        isLoadingAllTemplates.value = false;
      }
    }

    // Cambiamos el estado
    showAllTemplates.value = !showAllTemplates.value;
  };

  const isTemplateFilterLoading = computed(() => {
    return areTemplatesLoading.value || isLoadingAllTemplates.value;
  });

  const isAllTemplatesSelected = computed(() => {
    if (filteredTemplateOptions.value.length === 0) return false;
    return filteredTemplateOptions.value.every((t) => tempTemplateState.value.includes(t.value));
  });

  // Computed: "Seleccionar todo" (Indeterminate state)
  const isTemplateIndeterminate = computed(() => {
    if (filteredTemplateOptions.value.length === 0) return false;
    const selectedCount = filteredTemplateOptions.value.filter((t) =>
      tempTemplateState.value.includes(t.value)
    ).length;
    return selectedCount > 0 && selectedCount < filteredTemplateOptions.value.length;
  });

  // 1. Abrir Popover
  const handleOpenTemplateFilter = (isOpen) => {
    if (isOpen) {
      // Cargar selección actual del formFilter
      if (Array.isArray(formFilter.templates)) {
        tempTemplateState.value = [...formFilter.templates];
      } else {
        tempTemplateState.value = [];
      }
      templateSearchTerm.value = '';
    }
  };

  // 2. Toggle Seleccionar Todo
  const toggleSelectAllTemplates = (e) => {
    const checked = e.target.checked;
    const visibleIds = filteredTemplateOptions.value.map((t) => t.value);

    if (checked) {
      // Agregar visibles que no estén ya seleccionados
      visibleIds.forEach((id) => {
        if (!tempTemplateState.value.includes(id)) {
          tempTemplateState.value.push(id);
        }
      });
    } else {
      // Quitar visibles de la selección
      tempTemplateState.value = tempTemplateState.value.filter((id) => !visibleIds.includes(id));
    }
  };

  // 3. Toggle Individual
  const handleSingleTemplateChange = (id, checked) => {
    if (checked) {
      tempTemplateState.value.push(id);
    } else {
      tempTemplateState.value = tempTemplateState.value.filter((item) => item !== id);
    }
  };

  // 4. Limpiar
  const handleClearTemplateFilter = () => {
    tempTemplateState.value = [];
    templateSearchTerm.value = '';
    formFilter.templates = []; // Limpiamos el filtro real
    templateFilterOpen.value = false;
    handleSearch();
  };

  // 5. Aplicar
  const handleApplyTemplateFilter = () => {
    // Aplicamos al formFilter real para que active el watch/search
    if (tempTemplateState.value.length > 0) {
      formFilter.templates = [...tempTemplateState.value];
    } else {
      formFilter.templates = [];
    }
    templateFilterOpen.value = false;
    handleSearch();
  };

  const noSortColumns = ['last_communication', 'customer', 'code'];

  const quoteUrl = computed(() => {
    const env = import.meta.env.VITE_APP_ENV;
    if (env === 'local' || env === 'dev') {
      return 'https://aurorafront.limatours.dev/packages/cotizacion';
    }
    return 'https://aurora.limatours.com.pe/packages/cotizacion';
  });

  const { t } = useI18n({
    useScope: 'global',
  });

  const { can } = usePermission();
  const router = useRouter();

  const orderStore = useOrderStore();
  const marketStore = useMarketStore();
  const customerStore = useCustomerStore();
  const templateStore = useTemplateStore();
  const productStore = useProductStore();
  const userStore = useUserStore();
  const generalStore = useGeneralStore();
  const orderStatusStore = useOrderStatusStore();
  const cancellationReasonStore = useCancellationReasonStore();
  const orderControlResourceStore = useOrderControlResourceStore();
  // const downloadStore = useDownloadStore();

  const activeKey = ref('1');

  const expandedRows = ref({});

  const DEFAULT_FILTER_BY = null;
  const selectedFilter = ref(DEFAULT_FILTER_BY);

  const INIT_CURRENT_PAGE_VALUE = 1;
  const INIT_PAGE_SIZE = 9;
  const DEFAULT_PAGE_SIZE_OPTIONS = [6, 9, 18];

  const modalIsOpenOrder = ref(false);
  const popoverRef = ref(null);

  const emailsModalRef = ref(null);
  const selectedOrderForEmails = ref(null);
  const activeKeyCollapse = ref([0]);
  const modalEmails = ref(false);
  const isLoadingEmails = ref(false);

  const observationsModalRef = ref(null);
  const modalObservations = ref(false);
  const isLoadingObservations = ref(false);
  const selectedOrderForObservations = ref(null);
  const observationText = ref('');

  const cancellationModalRef = ref(null);
  const modalCancellation = ref(false);
  const isLoadingCancellation = ref(false);
  const selectedOrderForCancellation = ref(null);

  // --- ReceivedAt Modal State ---
  const receivedAtModalRef = ref(null);
  const receivedAtFormRef = ref(null);
  const modalReceivedAt = ref(false);
  const isLoadingReceivedAt = ref(false);
  const selectedOrderForReceivedAt = ref(null);
  const formReceivedAtState = reactive({
    date: null,
    time: null,
  });
  const editOrderModalRef = ref(null); // para el modal
  const editOrderFormRef = ref(null); // para el formulario
  const modalEditOrder = ref(false);
  const isLoadingEditOrder = ref(false);
  const selectedOrderForEdit = ref(null);

  const cancelOrderModalRef = ref(null);
  const modalCancelOrder = ref(false);
  const isLoadingCancelOrder = ref(false);
  const selectedOrderForCancel = ref(null);
  const selectedOrderForFollowUp = ref(null);

  const editResponseModalRef = ref(null);
  const editResponseFormRef = ref(null);
  const modalEditResponse = ref(false);
  const isLoadingEditResponse = ref(false);
  const selectedOrderForEditResponse = ref(null);

  const isLoadingFollowUp = ref(false);

  const updateStatusModalRef = ref(null);
  const updateStatusFormRef = ref(null);
  const modalUpdateStatus = ref(false);
  const isLoadingUpdateStatus = ref(false);
  const selectedOrderForUpdateStatus = ref(null);
  const formUpdateStatusState = reactive({
    statusId: null,
    order: '',
  });
  const updateStatusRules = {
    statusId: [{ required: true, message: 'Por favor seleccione un estado.' }],
  };

  const selectedStatusIdForBulk = ref(null);
  const modalIsOpenBulkStatusUpdate = ref(false);
  const isLoadingBulkStatusUpdate = ref(false);

  const selectedTemplateIdForBulk = ref(null);
  const modalIsOpenBulkFollowUp = ref(false);

  const selectedTemplateForBulk = computed(() => {
    if (!selectedTemplateIdForBulk.value) return null;
    return templateStore.getTemplates.find((t) => t._id === selectedTemplateIdForBulk.value);
  });

  const finalOrdersToProcessCount = ref(0);

  const formEditResponseState = reactive({
    responded_at_date: null,
    responded_at_hour: null,
  });

  const reassignOrderModalRef = ref(null);
  const reassignOrderFormRef = ref(null);
  const modalReassignOrder = ref(false);
  const isLoadingReassignOrder = ref(false);
  const selectedOrderForReassign = ref(null);

  const selectAll = ref(false);

  const formReassignState = reactive({
    product: null,
    region: null,
    executive: null,
  });

  const assignType = ref('E');

  const editResponseRules = {
    responded_at_date: [{ required: true, message: 'La fecha es requerida.' }],
    responded_at_hour: [{ required: true, message: 'La hora es requerida.' }],
  };

  const formEditState = reactive({
    groupName: '',
    product: null,
    customer: null,
    externalReference: '',
    observation: '',
    version: '',
  });

  const formCancellationState = reactive({
    reason: null,
    observation: '',
  });

  const cancellationRules = {
    reason: [],
    observation: [],
  };

  const editOrderRules = {
    groupName: [],
    product: [],
    customer: [],
    externalReference: [],
    observation: [],
  };

  const reassignOrderRules = {
    product: [{ required: true, message: 'El producto es requerido.' }],
    region: [{ required: true, message: 'La región es requerida.' }],
    executive: [{ required: true, message: 'El especialista es requerido.' }],
  };

  const selectedTemplate = ref(null);
  const activeTemplates = computed(() => {
    return templateStore.getTemplates.filter((template) => template.status === true);
  });

  const areTemplatesLoading = computed(() => orderStore.isLoading);

  const availableTemplatesOptions = computed(() => {
    const allTemplates = templateStore.getTemplates || [];
    return allTemplates
      .filter((t) => t.status === true)
      .map((t) => ({
        value: t._id,
        label: t.name,
      }));
  });

  const anyOrderSelected = computed(() => {
    if (selectAllGlobal.value) {
      // Si es global, hay seleccionados a menos que haya excluido TODO el total (raro pero posible)
      return props.total - excludedIds.value.size > 0;
    }
    return selectedItemsMap.value.size > 0;
    // return props.data.some((order) => order.isSelected);
  });
  const getQuotationNumbers = (quotations) => {
    if (!quotations || !Array.isArray(quotations)) return '-';
    // Filtramos aquellas que tengan 'number' y las unimos con comas.
    const numbers = quotations
      .filter((q) => q.number || (q.file && q.file.number))
      .map((q) => q.number || q.file.number);
    return numbers.length > 0 ? numbers.join(', ') : '-';
  };

  // Helper para fecha corta tipo "Feb 28"
  const formatDateShort = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    // 'en-US' suele dar "Feb 28", 'es-ES' daría "28 feb".
    // Ajusta el locale según tu preferencia visual, en la imagen parece formato inglés (Mes Día)
    const formatter = new Intl.DateTimeFormat('en-US', { month: 'short', day: 'numeric' });
    return formatter.format(date);
  };

  const formatTimeShort = (isoString) => {
    if (!isoString) return '';
    const date = new Date(isoString);
    // Usamos 'es-ES' con opciones para obtener la hora y minutos en formato local
    const options = {
      hour: '2-digit',
      minute: '2-digit',
      hour12: false, // Puedes cambiar a true si prefieres AM/PM
    };
    return date.toLocaleTimeString('es-ES', options);
  };

  const ordersWithLatestQuotation = computed(() => {
    const activeTemplatesValue = activeTemplates.value;

    return props.data.map((orderDoc) => {
      const versions = Array.isArray(orderDoc.versions)
        ? [...orderDoc.versions].sort((a, b) => a.order - b.order)
        : [];

      const originalQuotations = [...(orderDoc.quotations || [])];
      const validQuotations = originalQuotations.filter((q) => q.number || q.file?.number);
      const latestQuotation = validQuotations.length > 0 ? validQuotations.at(-1) : null;

      // --- AQUÍ ESTÁ EL PARCHE CENTRALIZADO ---
      const versionList = versions
        .map((v) => {
          const vQuotes = v.quotations || [];
          const lastQuote = vQuotes.length > 0 ? vQuotes[vQuotes.length - 1] : null;
          const fileNumber = lastQuote?.file?.number || null;

          return {
            ...v,
            // Si la versión no tiene objeto executive o customer, usamos el del documento principal
            executive: v.executive || orderDoc.executive || { code: 'N/A', fullname: 'N/A' },
            customer: v.customer || orderDoc.customer || { code: 'N/A', name: 'N/A' },
            product: v.product || orderDoc.product || { name: 'N/A' },
            type: v.type || orderDoc.type || { name: 'N/A' },

            lights: v.lights || { days: 0, hours: 0, alert: 'success' },
            displayOrder: v.order,
            quotationString: getQuotationNumbers(vQuotes),
            fileNumber: fileNumber,
          };
        })
        .reverse();

      const filteredActiveTemplates = activeTemplatesValue.filter((template) => {
        if (orderDoc.customer?.language) {
          return template.languages.includes(orderDoc.customer.language.toUpperCase());
        }
        return false;
      });

      return {
        ...orderDoc,
        latestQuotation,
        filteredActiveTemplates,
        versionList,
      };
    });
  });

  const DEFAULT_FORM_FILTER = {
    filter: '',
    market: [],
    customer: [],
    executive: null,
    status: null,
    templates: [],
    travel_date: null,
    last_communication: null,
  };

  const formFilter = reactive({ ...DEFAULT_FORM_FILTER });

  const formFilterRef = ref();

  const props = defineProps({
    config: {
      type: Object,
      default: () => ({}),
    },
    total: {
      type: Number,
      default: 0,
    },
    isLoading: {
      type: Boolean,
      default: false,
    },
    data: {
      type: Array,
      default: () => [],
    },
    currentPage: {
      type: Number,
      default: 0,
    },
    defaultPerPage: {
      type: Number,
      default: 0,
    },
    perPage: {
      type: Number,
      default: 0,
    },
  });

  const emit = defineEmits([
    'onChange',
    'onShowSizeChange',
    'onFilterBy',
    'onFilter',
    'onRefresh',
    'handleRefreshCache',
  ]);

  const currentPageValue = ref(INIT_CURRENT_PAGE_VALUE);
  const currentPageSize = ref(INIT_PAGE_SIZE);

  // watch(formFilter, debounce(handleSearch, 500));

  watch(
    formFilter,
    () => {
      selectAllGlobal.value = false;
      selectedItemsMap.value.clear();
      excludedIds.value.clear();
      debounce(handleSearch, 500);
    },
    { deep: true }
  );

  watch(
    () => orderStore.isDownloading,
    (newValue, oldValue) => {
      // Cuando la descarga termina (isDownloading cambia de true a false)
      if (oldValue === true && newValue === false) {
        if (orderStore.error) {
          notification.error({
            message: 'Error en la descarga',
            description: orderStore.error,
          });
        }
        // Opcional: Se puede añadir una notificación de éxito aquí si se desea.
        // Por lo general, la propia descarga del navegador es suficiente feedback.
      }
    }
  );

  watch(
    () => props.perPage,
    (newPerPage) => {
      if (newPerPage) {
        currentPageSize.value = newPerPage;
      }
    }
  );

  watch(
    () => props.currentPage,
    (newCurrentPage) => {
      if (newCurrentPage) {
        currentPageValue.value = newCurrentPage;
      }
    }
  );

  watch(
    () => props.data.map((order) => order.isSelected),
    (newValues) => {
      const total = newValues.length;
      const selected = newValues.filter(Boolean).length;
      selectAll.value = selected === total && total > 0;
    },
    { deep: true }
  );

  const onFilter = (values) => {
    emit('onFilter', { perPage: props.perPage, form: values });
  };

  const onFilterFailed = (errorInfo) => {
    notification.error({
      message: `Filter`,
      description: errorInfo,
      duration: 8,
    });
  };

  const onChange = (page, perSize) => {
    const {
      filter,
      market,
      customer,
      status,
      executive,
      travel_date,
      last_communication,
      templates,
    } = formFilter;
    const payload = {
      currentPage: page,
      perPage: perSize,
      filter,
      market,
      status,
      executive,
      customer,
      travel_date,
      last_communication,
      templates,
    };
    if (selectedFilter.value) {
      payload.filterBy_ = selectedFilter.value.filterBy;
      payload.filterByType_ = selectedFilter.value.filterByType;
    }
    emit('onChange', payload);
  };

  const onShowSizeChange = (current, size) => {
    onChange(1, size);
  };

  const isDesc = computed(() => selectedFilter?.value?.filterByType === 'desc');

  const isSelectedFilterBy = (fieldName) => selectedFilter?.value?.filterBy === fieldName;

  const onFilterBy = ({ filterBy, filterByType }) => {
    //selectedFilter.value = DEFAULT_FILTER_BY;
    //selectedFilter.value = { filterBy, filterByType };
    //handleSearch();
    selectedFilter.value = DEFAULT_FILTER_BY;
    selectedFilter.value = { filterBy, filterByType };
    onChange(1, currentPageSize.value);
  };

  const handleDownloadList = () => {
    const formattedFormFilter = { ...formFilter };
    if (formattedFormFilter.travel_date && Array.isArray(formattedFormFilter.travel_date)) {
      formattedFormFilter.travel_date = formattedFormFilter.travel_date.map((d) =>
        d ? dayjs(d).format('YYYY-MM-DD') : null
      );
    }
    if (
      formattedFormFilter.last_communication &&
      Array.isArray(formattedFormFilter.last_communication)
    ) {
      formattedFormFilter.last_communication = formattedFormFilter.last_communication.map((d) =>
        d ? dayjs(d).format('YYYY-MM-DD') : null
      );
    }
    const params = {
      page: currentPageValue.value,
      per_page: currentPageSize.value,
      ...formattedFormFilter,
      filterBy: selectedFilter.value?.filterBy,
      filterByType: selectedFilter.value?.filterByType,
    };
    orderStore.downloadOrdersExcel(params);
  };

  const handleGoToTemplateManagement = () => {
    router.push({ name: 'templates' });
  };

  const handleOrderSelection = (order, checked) => {
    const orderId = order._id;
    const minimalOrderData = { code: order.code, order: order.order };

    if (selectAllGlobal.value) {
      // MODO GLOBAL: Si está checkeado globalmente, desmarcar significa "excluir"
      if (checked) {
        excludedIds.value.delete(orderId);
      } else {
        excludedIds.value.add(orderId);
      }
    } else {
      // MODO MANUAL: Comportamiento normal
      if (checked) {
        selectedItemsMap.value.set(orderId, minimalOrderData);
      } else {
        selectedItemsMap.value.delete(orderId);
      }
    }
  };

  const toggleShowQuotations = (orderId) => {
    expandedRows.value[orderId] = !expandedRows.value[orderId];
  };

  const handleRefresh = () => {
    Object.assign(formFilter, DEFAULT_FORM_FILTER);
    if (formFilterRef.value) {
      formFilterRef.value.resetFields();
    }
    formFilter.status = null;
    formFilter.market = [];
    formFilter.customer = [];
    formFilter.templates = [];
    formFilter.travel_date = null;
    formFilter.last_communication = null;

    currentPageValue.value = 1;
  };

  const showEmailsModal = async (order) => {
    popoverRef.value?.hide?.();
    activeKeyCollapse.value = [0];
    modalEmails.value = true;

    // Limpiamos selección previa y activamos carga
    selectedOrderForEmails.value = null;
    isLoadingEmails.value = true;

    try {
      // Obtenemos el detalle completo (que sí incluye emailLogs) desde el backend
      const fullOrderData = await orderStore.fetchByCodeAndOrder(order.code, order.order);
      selectedOrderForEmails.value = fullOrderData;
    } catch (error) {
      console.error('Error cargando emails:', error);
      notification.error({
        message: 'Error',
        description: 'No se pudieron cargar los correos del pedido.',
      });
    } finally {
      isLoadingEmails.value = false;
    }
  };

  const handleOkEmails = () => {
    modalEmails.value = false;
    selectedOrderForEmails.value = null;
  };

  const handleCancelEmails = () => {
    modalEmails.value = false;
    selectedOrderForEmails.value = null;
  };

  const showObservationsModal = (order) => {
    popoverRef.value?.hide?.();
    selectedOrderForObservations.value = order;
    observationText.value = order.metadata.observation || '';
    modalObservations.value = true;
  };

  const showCancellationModal = async (order) => {
    popoverRef.value?.hide?.();
    selectedOrderForCancellation.value = order;

    if (cancellationReasonStore.getCancellationReasons.length === 0) {
      await cancellationReasonStore.fetchAll();
    }

    const cancellation = order.currentStatus?.cancellation;
    if (cancellation?.reasonId) {
      formCancellationState.reason = cancellation.reasonId;
    } else {
      if (cancellationReasonStore.getCancellationReasons.length > 0) {
        formCancellationState.reason = cancellationReasonStore.getCancellationReasons[0].value;
      } else {
        formCancellationState.reason = null;
      }
    }
    formCancellationState.observation = cancellation?.note || '';

    modalCancellation.value = true;
  };

  const handleOkCancellation = async () => {
    try {
      // await cancellationModalRef.value.formRef.validate();
      isLoadingCancellation.value = true;

      // 1. Obtener identificadores del objeto seleccionado (sea padre o versión)
      const currentOrder = selectedOrderForCancellation.value;
      const orderCode = currentOrder.code;
      const targetVersionOrder = currentOrder.order; // Número de versión

      const payload = {
        cancellationReasonId: formCancellationState.reason,
        note: formCancellationState.observation,
        version: targetVersionOrder, // IMPORTANTE: Enviamos la versión al backend
      };

      // 2. Actualizar en Backend usando CODE
      await orderStore.updateCancellationReason(orderCode, payload);

      // 3. Actualizar en Frontend (Mutación Local)
      // Buscamos el pedido padre en la lista
      const orderInList = props.data.find((o) => o.code === orderCode);

      if (orderInList && orderInList.versions) {
        // A. Buscar la versión específica
        const targetVersion = orderInList.versions.find((v) => v.order === targetVersionOrder);

        // B. Determinar si es la última versión
        const maxOrder = Math.max(...orderInList.versions.map((v) => v.order));
        const isLatestVersion = targetVersionOrder === maxOrder;

        // Helper para actualizar el objeto de estado de cancelación
        const reasonOption = cancellationReasonStore.getCancellationReasons.find(
          (r) => r.value === formCancellationState.reason
        );
        const reasonText = reasonOption ? reasonOption.label : '';

        const updateCancellationData = (obj) => {
          if (!obj.currentStatus) obj.currentStatus = {};
          if (!obj.currentStatus.cancellation) obj.currentStatus.cancellation = {};

          obj.currentStatus.cancellation.reasonId = formCancellationState.reason;
          obj.currentStatus.cancellation.reasonText = reasonText;
          obj.currentStatus.cancellation.note = formCancellationState.observation;
        };

        // Actualizar la versión específica (para el desglose)
        if (targetVersion) {
          updateCancellationData(targetVersion);
        }

        // Actualizar la raíz (para la fila principal) solo si es la última versión
        if (isLatestVersion) {
          updateCancellationData(orderInList);
        }
      }

      notification.success({
        message: 'Razón de cancelación guardada',
        description: 'La razón de cancelación del pedido se ha guardado correctamente.',
      });

      handleCancelCancellation();
    } catch (error) {
      console.error('Error al guardar la razón de cancelación:', error);
      notification.error({
        message: 'Error al guardar',
        description:
          'Ocurrió un error al guardar la razón de cancelación. Por favor, intente de nuevo.',
      });
    } finally {
      isLoadingCancellation.value = false;
    }
  };

  const handleCancelCancellation = () => {
    modalCancellation.value = false;
    selectedOrderForCancellation.value = null;
    formCancellationState.reason = null;
    formCancellationState.observation = '';
  };

  const handleOkObservations = async () => {
    isLoadingObservations.value = true;
    try {
      const currentOrder = selectedOrderForObservations.value;
      const orderCode = currentOrder.code;
      const targetVersionOrder = currentOrder.order; // Versión

      await orderStore.update(orderCode, {
        metadata_observation: observationText.value,
        version: targetVersionOrder,
      });

      // Buscamos el pedido padre en la lista
      const orderInList = props.data.find((o) => o.code === orderCode);

      if (orderInList && orderInList.versions) {
        // A. Buscar versión específica
        const targetVersion = orderInList.versions.find((v) => v.order === targetVersionOrder);

        // B. Verificar si es la última versión (para actualizar la fila principal)
        const maxOrder = Math.max(...orderInList.versions.map((v) => v.order));
        const isLatestVersion = targetVersionOrder === maxOrder;

        // Actualizar datos en memoria de la versión específica
        if (targetVersion) {
          if (!targetVersion.metadata) targetVersion.metadata = {};
          targetVersion.metadata.observation = observationText.value;
        }

        // Si es la última versión, actualizamos también la visualización principal
        if (isLatestVersion) {
          if (!orderInList.metadata) orderInList.metadata = {};
          orderInList.metadata.observation = observationText.value;
        }
      }

      notification.success({
        message: 'Observación actualizada',
        description: 'La observación del pedido se ha guardado correctamente.',
      });

      handleCancelObservations();
    } catch (error) {
      console.log('handleOkObservations ~ error:', error);
      notification.error({
        message: 'Error al actualizar',
        description: 'Ocurrió un error al guardar la observación. Por favor, intente de nuevo.',
      });
    } finally {
      isLoadingObservations.value = false;
    }
  };

  const handleCancelObservations = () => {
    modalObservations.value = false;
    selectedOrderForObservations.value = null;
    observationText.value = '';
  };

  const showReceivedAtModal = (order) => {
    popoverRef.value?.hide?.();
    selectedOrderForReceivedAt.value = order;

    if (order.metadata.receivedAt) {
      const receivedAtDate = dayjs(order.metadata.receivedAt);
      formReceivedAtState.date = receivedAtDate;
      formReceivedAtState.time = receivedAtDate;
    } else {
      formReceivedAtState.date = null;
      formReceivedAtState.time = null;
    }

    modalReceivedAt.value = true;
  };

  const handleOkReceivedAt = async () => {
    try {
      await receivedAtFormRef.value.validate();
      isLoadingReceivedAt.value = true;

      const currentOrder = selectedOrderForReceivedAt.value;
      const datePart = formReceivedAtState.date.format('YYYY-MM-DD');
      const timePart = formReceivedAtState.time
        ? formReceivedAtState.time.format('HH:mm:ss')
        : '00:00:00';
      const combinedDateTime = `${datePart}T${timePart}`;

      const payload = {
        syncType: 'DETAILS',
        metadata_receivedAt: dayjs(combinedDateTime).toISOString(),
        version: currentOrder.order,
      };

      await orderStore.update(currentOrder.code, payload);

      notification.success({
        message: 'Fecha de recepción actualizada',
        description: 'La fecha se ha actualizado correctamente.',
      });

      handleSearch();
      handleCancelReceivedAt();
    } catch (error) {
      console.error('Error al actualizar la fecha de recepción:', error);
      notification.error({
        message: 'Error al actualizar',
        description: 'Ocurrió un error al actualizar la fecha. Por favor, intente de nuevo.',
      });
    } finally {
      isLoadingReceivedAt.value = false;
    }
  };

  const handleCancelReceivedAt = () => {
    modalReceivedAt.value = false;
    selectedOrderForReceivedAt.value = null;
    receivedAtFormRef.value?.resetFields();
    Object.assign(formReceivedAtState, { date: null, time: null });
  };

  const showEditOrderModal = async (order) => {
    console.log('🚀 ~ showEditOrderModal ~ order:', order);
    popoverRef.value?.hide?.();
    selectedOrderForEdit.value = order;

    isLoadingEditOrder.value = true;
    try {
      await productStore.fetchAll();
    } catch (error) {
      console.log('🚀 ~ showEditOrderModal ~ error:', error);
      notification.error({
        message: 'Error',
        description: 'No se pudieron cargar los productos.',
      });
    } finally {
      isLoadingEditOrder.value = false;
    }

    formEditState.groupName = order.metadata?.groupName || '';
    formEditState.product = order.product?.code || null;
    formEditState.customer = order.customer?.code || '';
    formEditState.externalReference = order.metadata?.externalReference || '';
    formEditState.observation = order.metadata?.observation || '';
    formEditState.version = order.order;
    modalEditOrder.value = true;
  };

  const handleOkEditOrder = async () => {
    try {
      await editOrderFormRef.value.validate();
      isLoadingEditOrder.value = true;

      // 1. Obtener identificadores
      // 'code' viene del objeto seleccionado (gracias a la proyección del backend)
      const orderCode = selectedOrderForEdit.value.code;
      const targetVersionOrder = formEditState.version; // El número de "order" de la versión (ej: 1, 2, 3)

      const payload = {
        syncType: 'DETAILS',
        metadata_groupName: formEditState.groupName,
        metadata_externalReference: formEditState.externalReference,
        metadata_observation: formEditState.observation,
        product_code: formEditState.product,
        customer_code: formEditState.customer,
        version: targetVersionOrder,
      };

      // 2. Actualizar en Backend
      await orderStore.update(orderCode, payload);

      // 3. Actualizar en Frontend (Mutación Reactiva)
      // Buscamos el pedido padre en la lista local
      const orderInList = props.data.find((o) => o.code === orderCode);

      if (orderInList && orderInList.versions) {
        // A. Buscar la versión específica que se editó
        const targetVersion = orderInList.versions.find((v) => v.order === targetVersionOrder);

        // B. Determinar si esta versión es la última (la más reciente)
        // Esto es clave para saber si debemos actualizar también la fila principal
        const maxOrder = Math.max(...orderInList.versions.map((v) => v.order));
        const isLatestVersion = targetVersionOrder === maxOrder;

        if (targetVersion) {
          // --- ACTUALIZACIÓN DE LA VERSIÓN ESPECÍFICA ---

          // 1. Metadata
          if (!targetVersion.metadata) targetVersion.metadata = {};
          targetVersion.metadata.groupName = formEditState.groupName;
          targetVersion.metadata.externalReference = formEditState.externalReference;
          targetVersion.metadata.observation = formEditState.observation;

          // 2. Producto (Buscamos el objeto completo en el store para obtener el nombre)
          const selectedProduct = productStore.getProducts.find(
            (p) => p.value === formEditState.product
          );
          if (selectedProduct) {
            targetVersion.productId = selectedProduct.value;
            // Actualizamos objeto populado en la versión
            targetVersion.product = {
              ...targetVersion.product,
              code: selectedProduct.value,
              name: selectedProduct.label,
            };
          }

          // 3. Cliente
          const selectedCustomer = customerStore.getCustomers.find(
            (c) => c.value === formEditState.customer
          );
          if (selectedCustomer) {
            targetVersion.customerId = selectedCustomer.value;
            // Actualizamos objeto populado en la versión
            targetVersion.customer = {
              ...targetVersion.customer,
              code: selectedCustomer.value,
              name: selectedCustomer.label,
            };
          }

          // --- ACTUALIZACIÓN DEL PADRE (SÓLO SI ES LA ÚLTIMA VERSIÓN) ---
          if (isLatestVersion) {
            // Replicamos los cambios en la raíz del objeto 'orderInList'
            // para que la fila principal se actualice visualmente.

            // 1. Metadata Raíz
            if (!orderInList.metadata) orderInList.metadata = {};
            orderInList.metadata.groupName = formEditState.groupName;
            orderInList.metadata.externalReference = formEditState.externalReference;
            orderInList.metadata.observation = formEditState.observation;

            // 2. Producto/Tipo Raíz
            if (selectedProduct) {
              // Asumiendo que 'type' y 'product' en la raíz reflejan esta selección
              if (!orderInList.type) orderInList.type = {};
              orderInList.type.name = selectedProduct.label;
              orderInList.type.code = selectedProduct.value;

              if (!orderInList.product) orderInList.product = {};
              orderInList.product.name = selectedProduct.label;
              orderInList.product.code = selectedProduct.value;
            }

            // 3. Cliente Raíz
            if (selectedCustomer) {
              orderInList.customerCode = selectedCustomer.value; // Actualizamos código plano
              if (!orderInList.customer) orderInList.customer = {};
              orderInList.customer.name = selectedCustomer.label;
              orderInList.customer.code = selectedCustomer.value;
            }
          }
        }
      }

      notification.success({
        message: 'Pedido actualizado',
        description: 'El pedido se ha actualizado correctamente.',
      });

      handleCancelEditOrder();
    } catch (error) {
      console.error('Error al actualizar el pedido:', error);
      notification.error({
        message: 'Error al actualizar',
        description: 'Ocurrió un error al actualizar el pedido. Por favor, intente de nuevo.',
      });
    } finally {
      isLoadingEditOrder.value = false;
    }
  };

  const handleCancelEditOrder = () => {
    modalEditOrder.value = false;
    selectedOrderForEdit.value = null;
    Object.assign(formEditState, {
      groupName: '',
      product: null,
      customer: null,
      externalReference: '',
      observation: '',
      version: '',
    });
  };

  const showEditResponseModal = (order) => {
    popoverRef.value?.hide?.();
    selectedOrderForEditResponse.value = order;
    modalEditResponse.value = true;
  };

  const handleOkEditResponse = async () => {
    try {
      await editResponseFormRef.value.validate();
      isLoadingEditResponse.value = true;

      const currentOrder = selectedOrderForEditResponse.value;
      const orderCode = currentOrder.code;
      const targetVersionOrder = currentOrder.order; // Versión

      const payload = {
        syncType: 'RESPONSE',
        responded_at_date: formEditResponseState.responded_at_date.format('YYYY-MM-DD'),
        responded_at_hour: formEditResponseState.responded_at_hour.format('HH:mm'),
        version: targetVersionOrder,
      };

      await orderStore.update(orderCode, payload);

      const orderInList = props.data.find((o) => o.code === orderCode);

      if (orderInList && orderInList.versions) {
        const targetVersion = orderInList.versions.find((v) => v.order === targetVersionOrder);
        const maxOrder = Math.max(...orderInList.versions.map((v) => v.order));
        const isLatestVersion = targetVersionOrder === maxOrder;

        // Construir la nueva fecha combinada ISO
        const newRespondedAt = dayjs(
          `${payload.responded_at_date}T${payload.responded_at_hour}`
        ).toISOString();

        // Actualizar versión específica
        if (targetVersion) {
          if (!targetVersion.metadata) targetVersion.metadata = {};
          targetVersion.metadata.respondedAt = newRespondedAt;
        }

        // Actualizar fila principal si es la última versión
        if (isLatestVersion) {
          if (!orderInList.metadata) orderInList.metadata = {};
          orderInList.metadata.respondedAt = newRespondedAt;
        }
      }

      notification.success({
        message: 'Respuesta actualizada',
        description: 'La respuesta se ha actualizado correctamente.',
      });
      handleSearch();
      handleCancelEditResponse();
    } catch (error) {
      console.error('Error al actualizar la respuesta:', error);
      notification.error({
        message: 'Error al actualizar',
        description: 'Ocurrió un error al actualizar la respuesta. Por favor, intente de nuevo.',
      });
    } finally {
      isLoadingEditResponse.value = false;
    }
  };

  const handleCancelEditResponse = () => {
    modalEditResponse.value = false;
    selectedOrderForEditResponse.value = null;
    editResponseFormRef.value?.resetFields();
  };

  const showUpdateStatusModal = (order) => {
    selectedOrderForUpdateStatus.value = order;
    formUpdateStatusState.statusId = order.currentStatus?.statusId || null;
    formUpdateStatusState.order = order.order || null;
    modalUpdateStatus.value = true;
  };

  const handleOkUpdateStatus = async () => {
    try {
      await updateStatusFormRef.value.validate();
      isLoadingUpdateStatus.value = true;

      const order = selectedOrderForUpdateStatus.value;

      if (!order.code) {
        throw new Error('CODE de pedido no encontrado para la actualización de estado.');
      }

      const payload = {
        statusId: formUpdateStatusState.statusId,
        version: formUpdateStatusState.order,
      };

      const success = await orderStore.updateStatus(order.code, payload);

      if (success) {
        notification.success({
          message: 'Estado actualizado',
          description: 'El estado del pedido se ha actualizado correctamente.',
        });

        handleSearch(); // To refresh the list
        handleCancelUpdateStatus();
      } else {
        notification.error({
          message: 'Error al actualizar',
          description:
            orderStore.error ||
            'Ocurrió un error al actualizar el estado. Por favor, intente de nuevo.',
        });
      }
    } catch (error) {
      const errorMessage = error.errors
        ? error.errors[0].message
        : error.message || 'Ocurrió un error al actualizar el estado. Por favor, intente de nuevo.';
      console.error('Error al actualizar el estado:', error);
      notification.error({
        message: 'Error al actualizar',
        description: errorMessage,
      });
    } finally {
      isLoadingUpdateStatus.value = false;
    }
  };

  const handleCancelUpdateStatus = () => {
    modalUpdateStatus.value = false;
    selectedOrderForUpdateStatus.value = null;
    if (updateStatusFormRef.value) {
      updateStatusFormRef.value.resetFields();
    }
    formUpdateStatusState.statusId = null;
    formUpdateStatusState.order = '';
  };

  const showReassignModal = async (order) => {
    popoverRef.value?.hide?.();
    selectedOrderForReassign.value = order;

    formReassignState.product = order.product?.code || null;

    // Valores por defecto
    assignType.value = 'E';
    let calculatedRegion = null;
    const marketCode = order.customer?.market?.code || '';

    if (marketCode) {
      if (marketCode.toUpperCase().startsWith('H')) {
        // Excepción: Si empieza con H, región 4
        calculatedRegion = 4;
      } else {
        // Regla general: Primer dígito (si es 1, 2, 3 o 4)
        const firstDigit = parseInt(marketCode.charAt(0), 10);
        if ([1, 2, 3, 4].includes(firstDigit)) {
          calculatedRegion = firstDigit;
        }
      }
    }
    formReassignState.region = calculatedRegion;
    formReassignState.executive = order.executive?.code || null;

    try {
      await productStore.fetchAll();
      // Solo cargamos usuarios si el usuario cambia a modo Manual, o precargamos por si acaso
      await userStore.fetchUsersExternal();
    } catch (error) {
      console.log('Error loading reassign data:', error);
    }
    modalReassignOrder.value = true;
  };

  const handleOkReassignOrder = async () => {
    try {
      if (!formReassignState.product) {
        notification.warn({ message: 'Falta información', description: 'Seleccione un producto.' });
        return;
      }

      if (assignType.value === 'R' && !formReassignState.region) {
        notification.warn({ message: 'Falta información', description: 'Seleccione una región.' });
        return;
      }

      if (assignType.value === 'E' && !formReassignState.executive) {
        notification.warn({
          message: 'Falta información',
          description: 'Seleccione un ejecutivo.',
        });
        return;
      }

      isLoadingReassignOrder.value = true;

      const payload = {
        executive_code_current: selectedOrderForReassign.value?.executive?.code,
        customer_code: selectedOrderForReassign.value?.customer?.code,
        product_code: formReassignState.product,

        // Lógica condicional clave
        region: assignType.value === 'R' ? formReassignState.region : null,
        executive_code: assignType.value === 'E' ? formReassignState.executive : null,
      };

      const response = await orderStore.reassign(
        selectedOrderForReassign.value?.code,
        selectedOrderForReassign.value?.order,
        payload
      );

      if (response.success) {
        // ... (Éxito) ...
        notification.success({
          message: 'Pedido Reasignado',
          description: 'El pedido se ha reasignado correctamente.',
        });
        handleSearch();
        handleCancelReassignOrder();
      } else {
        // ... (Error) ...
        const errorMessage = response?.data?.message || 'Error al reasignar';
        notification.error({ message: 'Error', description: errorMessage });
      }
    } catch (error) {
      console.error('Error en la validación o reasignación del pedido:', error);
    } finally {
      isLoadingReassignOrder.value = false;
    }
  };

  const handleCancelReassignOrder = () => {
    modalReassignOrder.value = false;
    selectedOrderForReassign.value = null;
    if (reassignOrderFormRef.value) {
      reassignOrderFormRef.value.resetFields();
    }
    Object.assign(formReassignState, { product: null, region: null, executive: null });
  };

  const showCancelOrderModal = (order) => {
    popoverRef.value?.hide?.();
    selectedOrderForCancel.value = order;
    modalCancelOrder.value = true;
  };

  const handleOkCancelOrder = async () => {
    if (!selectedOrderForCancel.value) return;
    isLoadingCancelOrder.value = true;
    try {
      await orderStore.update(selectedOrderForCancel.value.code, { syncType: 'CANCEL' });
      handleSearch();

      notification.success({
        message: 'Pedido Anulado',
        description: `El pedido ${selectedOrderForCancel.value.code} ha sido anulado correctamente.`,
      });
      handleCancelCancelOrder();
    } catch (error) {
      console.error('Error al anular el pedido:', error);
      notification.error({
        message: 'Error al anular',
        description: 'Ocurrió un error al anular el pedido. Por favor, intente de nuevo.',
      });
    } finally {
      isLoadingCancelOrder.value = false;
    }
  };

  const handleCancelCancelOrder = () => {
    modalCancelOrder.value = false;
    selectedOrderForCancel.value = null;
  };

  function handleSearch() {
    const values = { ...formFilter }; // Captura todo el estado actual del formulario
    console.log('🚀 ~ handleSearch ~ values:', values);
    onFilter(values); // Reutiliza el mismo método que `@finish` usa
  }

  const showClass = (_dateIn) => {
    return '';
    let days = parseInt(checkDates(new Date(), _dateIn));

    if (days >= 0) {
      if (days <= 7) {
        days = 7;
      }

      if (days <= 15 && days > 7) {
        days = 15;
      }

      if (days <= 30 && days > 15) {
        days = 30;
      }

      return `days-color-${days}`;
    } else {
      return '';
    }
  };

  const showOrderModal = (template, order) => {
    selectedTemplate.value = template;
    selectedOrderForFollowUp.value = order;
    popoverRef.value?.hide?.();
    modalIsOpenOrder.value = true;
  };

  const handleDownload = async () => {
    if (!selectedTemplate.value || !selectedOrderForFollowUp.value) return;

    const order = selectedOrderForFollowUp.value;

    // Construye el payload con userId real
    const payload = {
      templateId: selectedTemplate.value._id,
    };

    try {
      isLoadingFollowUp.value = true;
      const success = await orderStore.sendFollowUpEmail(order.code, order.order, payload);

      if (success) {
        notification.success({
          message: 'Seguimiento enviado',
          description: `Se ha enviado el seguimiento con la plantilla "${selectedTemplate.value.name}".`,
        });
        handleSearch();
        handleCancelFollowUp();
      } else {
        notification.error({
          message: 'Error',
          description: 'No se pudo enviar el seguimiento. Intenta nuevamente.',
        });
      }
    } catch (error) {
      console.error('Error al enviar seguimiento:', error);
      notification.error({
        message: 'Error inesperado',
        description: 'Ocurrió un error al enviar el seguimiento.',
      });
    } finally {
      isLoadingFollowUp.value = false;
    }
  };

  const handleCancelFollowUp = () => {
    modalIsOpenOrder.value = false;
    selectedTemplate.value = null;
    selectedOrderForFollowUp.value = null;
  };

  const handleTemplateSelectForBulk = (templateId) => {
    if (!templateId) return;

    // Calculamos cuántos vamos a procesar para mostrar en el modal
    if (selectAllGlobal.value) {
      finalOrdersToProcessCount.value = props.total - excludedIds.value.size;
    } else {
      // Lógica manual: agrupar por code para no repetir versiones del mismo pedido
      const ordersToProcessMap = new Map();
      selectedItemsMap.value.forEach((order) => {
        const existing = ordersToProcessMap.get(order.code);
        if (!existing || order.order > existing.order) {
          ordersToProcessMap.set(order.code, order);
        }
      });
      finalOrdersToProcessCount.value = ordersToProcessMap.size;
    }

    modalIsOpenBulkFollowUp.value = true;
  };

  const handleOkBulkFollowUp = async () => {
    if (!validateGlobalFilters()) return;

    // Validación inicial
    if (!selectedTemplateIdForBulk.value) return;

    // Si no es global y no hay items manuales, salir
    if (!selectAllGlobal.value && selectedItemsMap.value.size === 0) return;

    const template = selectedTemplateForBulk.value;
    if (!template) return;

    isLoadingFollowUp.value = true;

    try {
      // ---------------------------------------------------------
      // CASO 1: SELECCIÓN GLOBAL (Todo el universo - Excluidos)
      // ---------------------------------------------------------
      if (selectAllGlobal.value) {
        // 1. Clonar y formatear filtros (igual que en exportación)
        const formattedFormFilter = { ...formFilter };

        if (formattedFormFilter.travel_date && Array.isArray(formattedFormFilter.travel_date)) {
          formattedFormFilter.travel_date = formattedFormFilter.travel_date.map((d) =>
            d ? dayjs(d).format('YYYY-MM-DD') : null
          );
        }
        if (
          formattedFormFilter.last_communication &&
          Array.isArray(formattedFormFilter.last_communication)
        ) {
          formattedFormFilter.last_communication = formattedFormFilter.last_communication.map(
            (d) => (d ? dayjs(d).format('YYYY-MM-DD') : null)
          );
        }

        // 2. Construir Payload Global
        const payload = {
          templateId: template._id,
          isGlobal: true,
          filter: formattedFormFilter, // Backend usará esto para buscar
          excludedIds: Array.from(excludedIds.value), // Backend excluirá estos
          // search: formattedFormFilter.filter // Opcional si tu backend lo requiere separado
        };

        // 3. Llamada al Store (Método masivo)
        // Asegúrate de tener 'sendFollowUpEmailBulk' en tu store
        await orderStore.sendFollowUpEmailBulk(payload);

        notification.success({
          message: 'Proceso iniciado',
          description: `Se está procesando el envío masivo a los pedidos filtrados.`,
        });
      } else {
        // ---------------------------------------------------------
        // CASO 2: SELECCIÓN MANUAL (Iterar Mapa)
        // ---------------------------------------------------------

        // 1. Agrupar por 'code' para tomar solo la versión más alta seleccionada
        const ordersToProcessMap = new Map();

        // Iteramos sobre los valores del Mapa (ya no selectedOrders array)
        selectedItemsMap.value.forEach((order) => {
          const existing = ordersToProcessMap.get(order.code);
          // Lógica: Si no existe o el actual tiene mayor versión (order.order), reemplazar
          if (!existing || order.order > existing.order) {
            ordersToProcessMap.set(order.code, order);
          }
        });

        const finalOrdersToProcess = Array.from(ordersToProcessMap.values());

        if (finalOrdersToProcess.length === 0) {
          isLoadingFollowUp.value = false;
          return;
        }

        const payload = {
          templateId: template._id,
        };

        // 2. Ejecutar promesas individuales
        const promises = finalOrdersToProcess.map((order) =>
          orderStore.sendFollowUpEmail(order.code, order.order, payload)
        );

        const results = await Promise.allSettled(promises);

        const successfulSends = results.filter((r) => r.status === 'fulfilled' && r.value).length;
        const failedSends = results.length - successfulSends;

        if (successfulSends > 0) {
          notification.success({
            message: 'Seguimiento enviado',
            description: `Se ha enviado el seguimiento a ${successfulSends} pedido(s) correctamente.`,
          });
        }
        if (failedSends > 0) {
          notification.error({
            message: 'Error en envío',
            description: `Falló el envío de seguimiento para ${failedSends} pedido(s).`,
          });
        }
      }

      handleSearch(); // Recargar datos
      handleCancelBulkFollowUp(); // Cerrar modal

      // Resetear estados de selección
      selectAllGlobal.value = false;
      selectedItemsMap.value.clear();
      excludedIds.value.clear();
      // selectAll.value = false; // (Si aún usas la ref antigua visual, reseteala aquí)
    } catch (e) {
      console.error(e);
      notification.error({
        message: 'Error',
        description: e.message || 'Ocurrió un error en el envío masivo.',
      });
    } finally {
      isLoadingFollowUp.value = false;
    }
  };

  const handleCancelBulkFollowUp = () => {
    modalIsOpenBulkFollowUp.value = false;
    selectedTemplateIdForBulk.value = null;
  };

  const handleStatusSelectForBulk = (statusId) => {
    if (!statusId) return;
    const hasSelection = selectAllGlobal.value || selectedItemsMap.value.size > 0;

    if (!hasSelection) {
      notification.warn({
        message: 'No hay pedidos seleccionados',
        description: 'Por favor, seleccione al menos un pedido para cambiar el estado.',
      });
      selectedStatusIdForBulk.value = null;
      return;
    }
    modalIsOpenBulkStatusUpdate.value = true;
  };

  const handleOkBulkStatusUpdate = async () => {
    // Validación inicial
    if (!selectedStatusIdForBulk.value) return;

    // Si no es global y no hay manuales, salir
    if (!selectAllGlobal.value && selectedItemsMap.value.size === 0) return;

    isLoadingBulkStatusUpdate.value = true;

    try {
      // ---------------------------------------------------------
      // CASO 1: SELECCIÓN GLOBAL
      // ---------------------------------------------------------
      if (selectAllGlobal.value) {
        if (!validateGlobalFilters()) return;

        // 1. Formatear Filtros
        const formattedFormFilter = { ...formFilter };

        if (formattedFormFilter.travel_date && Array.isArray(formattedFormFilter.travel_date)) {
          formattedFormFilter.travel_date = formattedFormFilter.travel_date.map((d) =>
            d ? dayjs(d).format('YYYY-MM-DD') : null
          );
        }
        if (
          formattedFormFilter.last_communication &&
          Array.isArray(formattedFormFilter.last_communication)
        ) {
          formattedFormFilter.last_communication = formattedFormFilter.last_communication.map(
            (d) => (d ? dayjs(d).format('YYYY-MM-DD') : null)
          );
        }

        // 2. Payload Global
        const payload = {
          statusId: selectedStatusIdForBulk.value,
          isGlobal: true,
          filter: formattedFormFilter,
          excludedIds: Array.from(excludedIds.value),
        };

        // 3. Llamada al Store (Método masivo)
        // Asegúrate de tener 'updateStatusBulk' en tu store
        await orderStore.updateStatusBulk(payload);

        notification.success({
          message: 'Actualización iniciada',
          description: `Se están actualizando los estados masivamente.`,
        });
      } else {
        // ---------------------------------------------------------
        // CASO 2: SELECCIÓN MANUAL
        // ---------------------------------------------------------

        // Convertimos los valores del mapa a array
        const ordersToUpdate = Array.from(selectedItemsMap.value.values());

        const promises = ordersToUpdate.map((order) =>
          orderStore.updateStatus(order.code, {
            statusId: selectedStatusIdForBulk.value,
            order: order.order, // Versión
          })
        );

        const results = await Promise.allSettled(promises);
        const successfulUpdates = results.filter((r) => r.status === 'fulfilled' && r.value).length;
        const failedUpdates = results.length - successfulUpdates;

        if (successfulUpdates > 0) {
          notification.success({
            message: 'Estado actualizado',
            description: `Se ha actualizado el estado de ${successfulUpdates} pedido(s) correctamente.`,
          });
        }
        if (failedUpdates > 0) {
          notification.error({
            message: 'Error en actualización',
            description: `Falló la actualización de estado para ${failedUpdates} pedido(s).`,
          });
        }
      }

      handleSearch();
      handleCancelBulkStatusUpdate();

      selectAllGlobal.value = false;
      selectedItemsMap.value.clear();
      excludedIds.value.clear();
    } catch (e) {
      console.error(e);
      notification.error({
        message: 'Error',
        description: e.message || 'Error en actualización masiva.',
      });
    } finally {
      isLoadingBulkStatusUpdate.value = false;
    }
  };

  const handleCancelBulkStatusUpdate = () => {
    modalIsOpenBulkStatusUpdate.value = false;
    selectedStatusIdForBulk.value = null;
  };

  onBeforeMount(async () => {
    try {
      await orderControlResourceStore.fetchAll();
    } catch (error) {
      console.error('Error loading initial data:', error);
    } finally {
      areTemplatesLoading.value = false;
    }
  });

  const formatDate = (isoString) => {
    const date = new Date(isoString);
    const formattedDate = date.toLocaleDateString('es-ES'); // ej: "29/07/2025"
    const formattedTime = date.toLocaleTimeString('es-ES', {
      hour: '2-digit',
      minute: '2-digit',
    }); // ej: "20:05"
    return { date: formattedDate, time: formattedTime };
  };
</script>

<style scoped lang="scss">
  /* Clase raíz del Popover (definida en overlayClassName) */
  .custom-filter-popover .ant-popover-inner {
    padding: 0;
    border-radius: 8px;
    box-shadow:
      0 3px 6px -4px rgba(0, 0, 0, 0.12),
      0 6px 16px 0 rgba(0, 0, 0, 0.08),
      0 9px 28px 8px rgba(0, 0, 0, 0.05);
  }

  .custom-filter-popover .ant-popover-inner-content {
    padding: 0; /* Reseteo padding default de ant */
  }

  /* Contenedor Interno */
  .filter-content-wrapper {
    width: 280px;
    display: flex;
    flex-direction: column;
    background: #fff;
    border-radius: 8px;
    overflow: hidden;
  }

  /* Título "Filtro" */
  .filter-title {
    margin: 0;
    padding: 12px 16px;
    font-size: 14px;
    font-weight: 600;
    color: #000;
  }

  /* Buscador */
  .filter-search {
    padding: 0 16px 10px 16px;
  }
  .filter-search .ant-input-affix-wrapper {
    border-radius: 4px;
    border-color: #d9d9d9;
  }

  /* Contenedor de lista (Scrollable) */
  .filter-list-container {
    max-height: 250px;
    overflow-y: auto;
    padding: 0 16px;
    margin-bottom: 10px;
  }

  /* Filas de checkboxes */
  .filter-item-row {
    padding: 8px 0;
  }

  .select-all-row {
    border-bottom: 1px solid #f0f0f0;
    margin-bottom: 5px;
    font-weight: 500;
  }

  .empty-search {
    color: #999;
    text-align: center;
    padding: 10px 0;
    font-size: 12px;
  }

  /* Footer Botones */
  .filter-footer {
    display: flex;
    justify-content: space-between;
    padding: 12px 16px;
    border-top: 1px solid #f0f0f0;
    gap: 10px;
  }

  .filter-footer .btn-clean {
    border: 1px solid #eb5757;
    color: #eb5757;
    border-radius: 4px;
    flex: 1;
  }

  .filter-footer .btn-apply {
    background-color: #eb5757;
    border-color: #eb5757;
    border-radius: 4px;
    flex: 1;
  }

  /* Scrollbar estética */
  .filter-list-container::-webkit-scrollbar {
    width: 6px;
  }
  .filter-list-container::-webkit-scrollbar-track {
    background: #f1f1f1;
  }
  .filter-list-container::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
  }
  .custom-red-datepicker .ant-picker-range-arrow {
    display: none; /* Ocultar flechita superior si se desea estilo plano */
  }

  /* Footer con botones */
  .custom-red-datepicker .ant-picker-footer {
    padding: 0;
    border-top: 1px solid #f0f0f0;
  }

  .custom-picker-footer {
    display: flex;
    justify-content: space-between;
    padding: 10px 12px;
    background: #fff;
    gap: 10px;
  }

  .custom-picker-footer .btn-clean {
    border: 1px solid #eb5757;
    color: #eb5757;
    border-radius: 4px;
    width: 48%;
  }

  .custom-picker-footer .btn-apply {
    background-color: #eb5757;
    border-color: #eb5757;
    width: 48%;
    border-radius: 4px;
  }

  /* --- OVERRIDES DE COLOR ANT DESIGN PARA VOLVERLO ROJO --- */

  /* Cabecera del calendario (Mes/Año) */
  .custom-red-datepicker .ant-picker-header-view button:hover {
    color: #eb5757;
  }

  /* Día seleccionado (círculo rojo oscuro) */
  .custom-red-datepicker .ant-picker-cell-range-start .ant-picker-cell-inner,
  .custom-red-datepicker .ant-picker-cell-range-end .ant-picker-cell-inner,
  .custom-red-datepicker .ant-picker-cell-selected .ant-picker-cell-inner {
    background-color: #eb5757 !important;
    border-color: #eb5757 !important;
  }

  /* Rango intermedio (fondo rojo claro) */
  .custom-red-datepicker .ant-picker-cell-in-range::before {
    background-color: #fad4d4 !important;
  }

  /* Hover sobre días */
  .custom-red-datepicker .ant-picker-cell:hover .ant-picker-cell-inner {
    background-color: #f5f5f5;
  }

  /* Día actual (borde rojo) */
  .custom-red-datepicker .ant-picker-today-btn {
    color: #eb5757;
  }

  /* Header del filtro en la tabla */
  .filter-column-header {
    cursor: pointer;
    user-select: none;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .custom-range-picker :deep(.ant-picker-suffix) {
    color: #eb5757 !important;
    margin-right: 8px !important;
  }

  .base-table {
    :deep(.single-month-range-picker) .ant-picker-panel:last-child {
      display: none;
    }

    .row {
      display: flex;
      align-items: center;
      border-radius: 6px;
      margin-bottom: 15px;
      text-align: center;
      font-size: 0.875rem;
    }

    .row-header {
      background-color: var(--files-background-1);
      color: var(--files-black-4);
      min-height: auto;
      font-weight: 700;
    }

    .row-header-sort {
      cursor: pointer;
    }

    .container-body {
      min-height: 370px;
      // min-height: 465px;
      &.is-loading {
        background: #fafafa;
        display: flex;
        justify-content: center;
        align-items: center;
      }
    }

    .row-body {
      background-color: var(--files-gray-1);
      border: 1px solid var(--files-main-colorgray-1);
      min-height: 40px;
      font-weight: 400;
    }

    .col-small {
      flex: 1 0 0%;
      display: flex;
      justify-content: center;
      align-items: center;
      overflow: hidden;

      &-break {
        word-break: break-all;
        text-align: left;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
      }

      &-200 {
        width: 200px;
      }

      &-type {
        line-height: 1.2;
      }
    }

    .col-body-options {
      display: flex;
      justify-content: center;
      align-items: center;
      color: var(--files-main-color);
      gap: 8px;

      svg {
        cursor: pointer;
        padding: 1px;
        width: 16px;
        height: 16px;
      }

      svg:focus {
        outline: none;
      }
    }

    .version-history-container {
      width: 100%;
      background-color: #fff;
      border-radius: 0 0 6px 6px;
      margin-top: -15px;
      margin-bottom: 15px;
      position: relative;
      z-index: 1;

      &::before {
        content: '';
        position: absolute;
        // Coordenadas que se alinean después de la columna de fecha (aprox. 185px)
        // 60px (Checkbox) + 60px (Lights) + 100px (Date Wrapper) - 25px (Wide Content Padding) = 195px
        left: 215px; /* Ajuste este valor (o el que necesites) para alinearlo con el inicio del contenido */
        top: 0;
        bottom: 0;
        width: 1px;
        background-color: #e0e0e0;
      }
    }

    .version-history-row {
      background-color: #fafafa;
      margin-bottom: 0 !important;
      min-height: 55px;
      display: flex;
      align-items: stretch;
    }

    .version-spacer {
      width: 60px;
      min-width: 60px;
      flex: 0 0 60px;
    }

    .version-col {
      display: flex;
      align-items: center;
      font-size: 0.85rem;
      padding-top: 8px;
      padding-bottom: 8px;
    }

    .version-date-wrapper {
      width: 100px;
      min-width: 115px;
      justify-content: flex-start;
      padding-left: 15px;
      color: #8c8c8c;
      font-weight: 500;
    }

    /* COLUMNA CONTENIDO (Ocupa el resto del espacio) */
    .version-wide-content {
      flex: 1;
      display: flex;
      align-items: center;
      border-left: none; /* Eliminamos el borde de la versión anterior */
      position: static;

      padding-left: 25px; /* Mantenemos el padding para el espacio */

      /* Línea divisora horizontal se mantiene */
      border-bottom: 1px solid #f0f0f0;

      &::before {
        content: none;
      }

      /* LÍNEA DIVISORA HORIZONTAL (Solo en esta columna) */
      border-bottom: 1px solid #f0f0f0;

      .version-content-wrapper {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
      }

      .version-detail-code {
        font-weight: 800;
        color: #000;
        font-size: 0.9rem;
      }

      .version-detail-quotations {
        color: #999;
        font-size: 0.85rem;
      }

      .version-file-tag {
        color: #1ed790;
        border: 1px solid #1ed790;
        border-radius: 4px;
        padding: 1px 8px;
        font-size: 0.75rem;
        font-weight: 600;
        background-color: #f6fffc;
      }

      .version-original-tag {
        color: #faad14;
        border: 1px solid #faad14;
        border-radius: 4px;
        padding: 1px 8px;
        font-size: 0.75rem;
        font-weight: 600;
        background-color: #fffbe6;
      }

      .version-tag-status {
        font-size: 0.75rem;
        line-height: 1.5;
      }
    }

    .color-gray {
      color: #c4c4c4;
    }

    /* COLUMNA ACCIONES */
    .version-detail-actions {
      width: auto;
      min-width: 150px; /* Un poco más de espacio para el contenido extra */
      justify-content: flex-end;
      padding-right: 30px;
      gap: 15px;
      border-bottom: 1px solid #f0f0f0;

      .meta-wrapper {
        display: flex;
        align-items: center;
        gap: 6px;
      }

      /* PUNTO GRIS */
      .gray-dot {
        width: 12px;
        height: 12px;
        background-color: #c4c4c4;
        border-radius: 50%;
      }

      .version-meta-info {
        font-size: 0.75rem;
        color: #b0b0b0;
        font-weight: 600;
      }

      .action-icon {
        color: #eb5757;
        cursor: pointer;
        font-size: 14px;
        &:hover {
          opacity: 0.8;
        }
      }
    }

    .version-history-row:last-child {
      .version-wide-content,
      .version-detail-actions {
        border-bottom: none;
      }
    }
  }

  .box-title {
    font-weight: 500;
    font-size: 12px;
    line-height: 18px;
    color: #2f353a;
  }

  .box-description {
    font-weight: 400;
    font-size: 12px;
    line-height: 18px;
  }
  .form-filter-container {
    width: 100%;
    overflow: visible;
  }

  /* Grid del formulario */
  .form-filter-grid {
    width: 100%;
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 12px;
    align-items: start;
  }

  /* Cada item del grid */
  .form-filter-grid > * {
    width: 100% !important;
    min-width: 100% !important;
    max-width: 100% !important;
  }

  /* Asegurar que los componentes internos ocupen todo el espacio */
  .form-filter-grid :deep(.ant-form-item) {
    width: 100% !important;
    margin-bottom: 0 !important;
  }

  .form-filter-grid :deep(.ant-picker) {
    width: 100% !important;
  }

  .form-filter-grid :deep(.ant-select) {
    width: 100% !important;
  }

  /* Para pantallas más pequeñas */
  @media (max-width: 1200px) {
    .form-filter-grid {
      grid-template-columns: repeat(3, 1fr);
    }
  }

  @media (max-width: 768px) {
    .form-filter-grid {
      grid-template-columns: repeat(2, 1fr);
    }
  }

  @media (max-width: 480px) {
    .form-filter-grid {
      grid-template-columns: 1fr;
    }
  }

  .form-vip {
    display: flex;
    flex-direction: column;
    gap: 1.75rem;
    margin-bottom: 1.625rem;
  }

  .group-vip {
    cursor: pointer;
    transition: 0.3s ease all;
    color: #c4c4c4;

    .is-vip {
      color: var(--files-exclusives);
    }

    &:hover {
      color: var(--files-exclusives);
    }
  }

  .row-pagination {
    display: flex;
    justify-content: center;
    gap: 60px;
    padding-top: 20px;
  }

  .text-uppercase {
    text-transform: uppercase;
  }

  .vip-content-click {
    font-family: var(--files-font-basic);
    width: 410px;
    height: 260px;
    // height: 337px;
    display: flex;
    flex-direction: column;
    gap: 4px;
    padding: 3px;

    &-title {
      font-weight: 700;
      font-size: 1rem;
      line-height: 23px;
      padding: 20px 12px 23px;
      text-align: center;
      letter-spacing: -0.015em;
      color: #3d3d3d;
      border-bottom: 1px solid #e9e9e9;
      margin-bottom: 1.8125rem;
    }
  }

  .quotation-content-click {
    font-family: var(--files-font-basic);
    width: 320px;
    height: 220px;
    display: flex;
    flex-direction: column;
    gap: 4px;
    padding: 3px;

    &-title {
      font-weight: 900;
      font-size: 1.2rem;
      line-height: 1.2;
    }

    &-subtitle {
      font-weight: 400;
      font-size: 0.8rem;

      span {
        text-decoration: underline;
        font-weight: 500;
      }
    }

    &-buttons {
      display: flex;
      gap: 5px;
      padding-bottom: 10px;
    }

    &-table {
      width: 310px;
      box-shadow:
        0 3px 6px -2px rgb(0 0 0 / 20%),
        0 6px 12px rgb(0 0 0 / 10%);
      margin-top: 5px;

      & :deep(.ant-table-cell) {
        font-size: 0.7rem;
      }
    }
  }

  .details-content-click {
    font-family: var(--files-font-basic);
    width: 275px;
    height: 220px;
    display: flex;
    flex-direction: column;
    gap: 4px;
    padding: 3px;

    font-size: 0.875rem;
    line-height: 1.3125;
    letter-spacing: 0.015em;
    color: var(--files-black-2);

    &-title {
      font-weight: 700;
      text-align: center;
      text-transform: uppercase;
    }

    &-subtitle {
      font-weight: 600;
    }

    &-description {
      font-weight: 400;
    }
  }

  .pending-tasks-content-click {
    font-family: var(--files-font-basic);
    width: 320px;
    height: 202px;
    display: flex;
    flex-direction: column;
    gap: 4px;
    padding: 3px;

    font-size: 0.875rem;
    line-height: 1.3125;
    letter-spacing: 0.015em;

    color: var(--files-black-2);

    &-body {
      overflow-y: auto;
    }

    &-footer {
      height: 100px;
      overflow-y: auto;
      display: flex;
      justify-content: end;
    }

    &-title {
      color: var(--files-black-4);
      font-weight: 700;
      text-align: center;
    }

    &-subtitle {
      font-weight: 600;
    }

    &-description {
      font-weight: 400;
      font-size: 0.75rem;
      line-height: 1.1875;
    }
  }

  .actions-content-click {
    font-family: var(--files-font-basic);
    width: 185px;
    height: auto;
    display: flex;
    flex-direction: column;
    padding: 3px;

    font-size: 0.875rem;
    line-height: 1.3125;
    letter-spacing: 0.015em;

    &-title {
      color: var(--files-black-4);
      font-weight: 700;
      text-align: center;
    }

    &-content {
      display: flex;
      flex-direction: column;
    }
  }

  .status-row {
    &-error {
      width: 7px;
      height: 7px;
      background: #c21d3b;
      box-shadow: 0px 0px 0px 2px rgba(194, 29, 59, 0.24);
      border-radius: 100px;
    }

    &-success {
      width: 7px;
      height: 7px;
      background: #1ed790;
      box-shadow: 0px 0px 0px 2px rgba(30, 215, 144, 0.25);
      border-radius: 100px;
    }

    &-trends {
      width: 7px;
      height: 7px;
      background: #9574af;
      box-shadow: 0px 0px 0px 2px #e3d7f2;
      border-radius: 100px;
    }

    &-trends {
      width: 7px;
      height: 7px;
      background: #9574af;
      box-shadow: 0px 0px 0px 2px #e3d7f2;
      border-radius: 100px;
    }
  }

  .is-col-hidden {
    display: none;
  }
  .custom-purple-alert {
    background-color: #eef2ff; /* Fondo más suave (Indigo 50) */
    border: 1px solid #c7d2fe; /* Borde más sutil (Indigo 200) */
    border-radius: 6px;
    padding: 9px 15px; /* Ajuste de padding para centrar visualmente */
    display: flex;
    align-items: center;
  }

  /* Forzar que el mensaje ocupe el espacio y se alinee */
  .custom-purple-alert :deep(.ant-alert-message) {
    color: #3730a3; /* Texto Indigo oscuro */
    font-size: 14px;
    display: flex;
    align-items: center;
    width: 100%;
  }

  /* Colores para el ícono y el botón de cerrar */
  .custom-purple-alert :deep(.ant-alert-icon),
  .custom-purple-alert :deep(.anticon) {
    color: #4f46e5; /* Ícono Indigo vibrante */
  }

  .custom-purple-alert :deep(.ant-alert-close-icon) {
    color: #6366f1;
    margin-left: auto; /* Asegura que la X se vaya al final si es necesario */
  }

  .custom-purple-alert :deep(.ant-alert-close-icon:hover) {
    color: #312e81;
  }

  /* Asegurar que el texto se vea fluido */
  .alert-text {
    font-weight: 400;
  }
  .eye-toggle-btn {
    cursor: pointer;
    padding: 4px;
    border-radius: 4px;
    color: #999;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .eye-toggle-btn:hover {
    background-color: #f5f5f5;
    color: #555;
  }

  .eye-toggle-btn.active {
    color: #eb5757; /* Color activo (rojo de tu marca) */
    background-color: #fff1f0;
  }
</style>
