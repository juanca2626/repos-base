<template>
  <a-form layout="vertical" :model="formState">
    <!-- Section 1: Basic Info -->
    <div class="content-card">
      <div class="row">
        <div class="col-5">
          <a-form-item required>
            <template #label>
              <span class="label-with-tooltip"
                >Nombre de la tarifa: <span class="required">*</span>
                <a-tooltip
                  title="Nombre automático completado con selección realizada"
                  placement="topLeft"
                  overlay-class-name="tooltip-nowrap"
                >
                  <font-awesome-icon :icon="['fas', 'circle-info']" class="info-icon-inline" />
                </a-tooltip>
              </span>
            </template>
            <a-input
              v-model:value="formState.name"
              :disabled="true"
              placeholder="Ingresa"
              size="large"
            />
          </a-form-item>
        </div>
      </div>

      <div class="row">
        <div class="col-12 flex-center">
          <span class="label-text q-mr-md">Requiere código de reserva:</span>
          <a-radio-group v-model:value="formState.requiresBookingCode">
            <a-radio :value="true">Sí</a-radio>
            <a-radio :value="false">No</a-radio>
          </a-radio-group>
        </div>
      </div>

      <div v-if="formState.requiresBookingCode" class="row">
        <div class="col-5">
          <a-input v-model:value="formState.bookingCode" placeholder="Ingresa" size="large" />
        </div>
      </div>
    </div>

    <!-- Section 2: Details -->
    <div class="content-card">
      <div class="row">
        <div class="col-5">
          <a-form-item required>
            <template #label>
              <span>Tipo de tarifa: <span class="required">*</span></span>
            </template>
            <a-select
              v-model:value="formState.tariffType"
              placeholder="Selecciona"
              size="large"
              :options="tariffTypeOptions"
            />
          </a-form-item>
        </div>
      </div>

      <!-- Nombre de promoción para tipo Promocional -->
      <div v-if="formState.tariffType === 'promocional'" class="row">
        <div class="col-5">
          <a-form-item label="Nombre de promoción">
            <a-select
              v-model:value="formState.promotionName"
              mode="multiple"
              placeholder="Selecciona"
              size="large"
              :options="promotionOptions"
            />
          </a-form-item>
        </div>
      </div>

      <!-- Campos para tipo Específica -->
      <template v-if="formState.tariffType === 'especifica'">
        <div class="row">
          <div class="col-5">
            <a-form-item label="Segmentación de tarifa:">
              <a-select
                v-model:value="formState.tariffSegmentation"
                mode="multiple"
                placeholder="Selecciona"
                size="large"
                :options="segmentationOptions"
              />
            </a-form-item>
          </div>
        </div>

        <div v-if="formState.tariffSegmentation.includes('mercados')" class="row">
          <div class="col-5">
            <a-form-item label="Especificación mercados:">
              <a-select
                v-model:value="formState.specificMarkets"
                mode="multiple"
                placeholder="Selecciona"
                size="large"
                :options="marketOptions"
              />
            </a-form-item>
          </div>
        </div>

        <div v-if="formState.tariffSegmentation.includes('clientes')" class="row">
          <div class="col-5">
            <a-form-item label="Especificación clientes:">
              <a-select
                v-model:value="formState.specificClients"
                mode="multiple"
                placeholder="Selecciona"
                size="large"
                :options="clientOptions"
              />
            </a-form-item>
          </div>
        </div>

        <div v-if="formState.tariffSegmentation.includes('series')" class="row">
          <div class="col-5">
            <a-form-item label="Especificación series:">
              <a-select
                v-model:value="formState.specificSeries"
                mode="multiple"
                placeholder="Selecciona"
                size="large"
                :options="seriesOptions"
              />
            </a-form-item>
          </div>
        </div>
      </template>

      <!-- Periodo de viaje para tipo Periodos -->
      <div v-if="formState.tariffType === 'periodos'" class="periods-section">
        <div class="periods-header">
          <label class="field-section-label"
            >Periodo de viaje: <span class="required">*</span>
            <a-tooltip
              title="Fechas en las que viajará el pasajero"
              placement="topLeft"
              overlay-class-name="tooltip-nowrap"
            >
              <font-awesome-icon :icon="['fas', 'circle-info']" class="info-icon-inline" />
            </a-tooltip>
          </label>
          <span class="add-period-link" @click="addDateRangeToLastGroup">+ Periodo adicional</span>
        </div>

        <div
          v-for="(periodGroup, groupIndex) in formState.periods"
          :key="groupIndex"
          class="period-row"
        >
          <div class="period-group-card">
            <div class="period-card-content">
              <!-- Period Type (Fixed width) -->
              <div class="period-type-col">
                <a-form-item>
                  <template #label>
                    <span>Tipo de periodo: <span class="required">*</span></span>
                  </template>
                  <a-select
                    v-model:value="periodGroup.periodType"
                    placeholder="Selecciona"
                    size="large"
                    :options="periodTypeOptions"
                    allow-clear
                  />
                </a-form-item>
              </div>

              <!-- Ranges (Flexible width) -->
              <div class="period-ranges-col">
                <div
                  v-for="(range, rangeIndex) in periodGroup.ranges"
                  :key="rangeIndex"
                  class="period-range-row"
                >
                  <div class="range-inputs-wrapper">
                    <div class="range-date-input">
                      <a-form-item label="Desde:">
                        <a-date-picker
                          v-model:value="range.dateFrom"
                          placeholder="DD/MM/AAAA"
                          format="DD/MM/YYYY"
                          size="large"
                          class="full-width"
                        />
                      </a-form-item>
                    </div>

                    <div class="range-date-input">
                      <a-form-item label="Hasta:">
                        <a-date-picker
                          v-model:value="range.dateTo"
                          placeholder="DD/MM/AAAA"
                          format="DD/MM/YYYY"
                          size="large"
                          class="full-width"
                        />
                      </a-form-item>
                    </div>
                  </div>

                  <div class="range-actions">
                    <!-- Row 0: Add Group (+) and Delete Group (Trash) -->
                    <template v-if="rangeIndex === 0">
                      <div class="action-icon" @click="addNewPeriodGroup(groupIndex)">
                        <PlusCircleOutlined style="font-size: 20px; color: #1284ed" />
                      </div>
                      <div
                        v-if="formState.periods.length > 1"
                        class="action-icon"
                        @click="removePeriod(groupIndex)"
                      >
                        <font-awesome-icon
                          icon="fa-regular fa-trash-can"
                          style="font-size: 20px; color: #1284ed"
                        />
                      </div>
                    </template>

                    <!-- Row > 0: Delete Range (X) -->
                    <template v-else>
                      <div class="action-icon" @click="removeRange(groupIndex, rangeIndex)">
                        <CloseOutlined style="font-size: 16px; color: #7e8285" />
                      </div>
                    </template>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Travel Period (para otros tipos de tarifa) -->
      <template v-else>
        <div class="row section-header-row">
          <div class="col-12">
            <label class="field-section-label"
              >Periodo de viaje: <span class="required">*</span>
              <a-tooltip
                title="Fechas en las que viajará el pasajero"
                placement="topLeft"
                overlay-class-name="tooltip-nowrap"
              >
                <font-awesome-icon :icon="['fas', 'circle-info']" class="info-icon-inline"
              /></a-tooltip>
            </label>
          </div>
        </div>
        <div class="row">
          <div class="col-5">
            <div class="row">
              <div class="col-6">
                <a-form-item label="Desde:">
                  <a-date-picker
                    v-model:value="formState.travelFrom"
                    placeholder="DD/MM/AAAA"
                    format="DD/MM/YYYY"
                    size="large"
                    class="full-width"
                  />
                </a-form-item>
              </div>
              <div class="col-6">
                <a-form-item label="Hasta:">
                  <a-date-picker
                    v-model:value="formState.travelTo"
                    placeholder="DD/MM/AAAA"
                    format="DD/MM/YYYY"
                    size="large"
                    class="full-width"
                  />
                </a-form-item>
              </div>
            </div>
          </div>
        </div>
      </template>

      <!-- Booking Period -->
      <div class="row section-header-row">
        <div class="col-12 flex-center">
          <label class="field-section-label">Periodo de reserva</label>
          <a-tooltip
            title="Fechas en las que se puede reservar el servicio"
            placement="topLeft"
            overlay-class-name="tooltip-nowrap"
          >
            <font-awesome-icon :icon="['fas', 'circle-info']" class="info-icon-inline q-mr-md" />
          </a-tooltip>
          <a-checkbox v-model:checked="formState.modifyBookingPeriod"
            >Modificar periodo de reserva</a-checkbox
          >
        </div>
      </div>

      <div class="row">
        <div class="col-5">
          <div class="row">
            <div class="col-6">
              <a-form-item label="Desde:">
                <a-date-picker
                  v-model:value="formState.bookingFrom"
                  placeholder="01/01/2000"
                  format="DD/MM/YYYY"
                  size="large"
                  :class="['full-width', { 'bg-gray': !formState.modifyBookingPeriod }]"
                  :disabled="!formState.modifyBookingPeriod"
                />
              </a-form-item>
            </div>
            <div class="col-6">
              <a-form-item label="Hasta:">
                <a-date-picker
                  v-model:value="formState.bookingTo"
                  placeholder="01/01/2050"
                  format="DD/MM/YYYY"
                  size="large"
                  :class="['full-width', { 'bg-gray': !formState.modifyBookingPeriod }]"
                  :disabled="!formState.modifyBookingPeriod"
                />
              </a-form-item>
            </div>
          </div>
        </div>
      </div>

      <!-- Radios -->
      <!-- Radios -->
      <div class="row bg-light-gray q-pa-sm q-mb-sm rounded-borders">
        <div class="col-12">
          <div class="flex-center" :class="{ 'q-mb-md': formState.differentiatedTariff }">
            <span class="label-text q-mr-md">Días con tarifa diferenciada:</span>
            <a-radio-group v-model:value="formState.differentiatedTariff">
              <a-radio :value="true">Sí</a-radio>
              <a-radio :value="false">No</a-radio>
            </a-radio-group>
          </div>

          <div v-if="formState.differentiatedTariff" class="differentiated-content">
            <!-- Day Selector -->
            <div class="day-selector-row flex-center q-mb-md">
              <span class="label-text q-mr-md">Selecciona los días:</span>
              <div class="day-toggles q-mr-md">
                <span
                  v-for="day in daysList"
                  :key="day.key"
                  class="day-circle"
                  :class="{ selected: formState.selectedDays.includes(day.key) }"
                  @click="toggleDay(day.key)"
                >
                  {{ day.label }}
                </span>
              </div>
              <span class="help-text"
                >Marque los días que tendrán precio diferente al estándar</span
              >
            </div>

            <!-- Tabs/Navigation -->
            <div class="tariff-tabs-row">
              <!-- Tarifa Fin de semana -->
              <div class="tariff-tab" :class="{ active: isWeekendSelected }" @click="selectWeekend">
                <div class="tariff-tab-header">
                  <a-tooltip title="Definir tarifa para fin de semana">
                    <font-awesome-icon :icon="['fas', 'calendar-check']" class="tab-icon" />
                  </a-tooltip>
                  <span class="tab-label">Tarifa</span>
                  <span class="tab-value">Fin de semana</span>
                  <span v-if="isWeekendSelected" class="close-btn" @click.stop="clearWeekend"
                    >×</span
                  >
                </div>
                <div class="tariff-tab-days">
                  <span>Sábado</span>
                  <span>Domingo</span>
                </div>
              </div>

              <!-- Tarifa Días de semana -->
              <div
                class="tariff-tab"
                :class="{ active: isWeekdaysSelected }"
                @click="selectWeekdays"
              >
                <div class="tariff-tab-header">
                  <a-tooltip title="Definir tarifa para días de semana">
                    <font-awesome-icon :icon="['fas', 'calendar-check']" class="tab-icon" />
                  </a-tooltip>
                  <span class="tab-label">Tarifa</span>
                  <span class="tab-value">Días de semana</span>
                </div>
                <div class="tariff-tab-days">
                  <span>Lunes</span>
                  <span>Martes</span>
                  <span>Miércoles</span>
                  <span>Jueves</span>
                  <span>Viernes</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div
        v-if="formState.tariffType && ['plana', 'periodos'].includes(formState.tariffType)"
        class="row bg-light-gray q-pa-sm rounded-borders"
      >
        <div class="col-12 flex-center">
          <span class="label-text q-mr-md">Incluir tarifas festivas</span>
          <a-radio-group v-model:value="formState.includeHolidayTariffs">
            <a-radio :value="true">Sí</a-radio>
            <a-radio :value="false">No</a-radio>
          </a-radio-group>
        </div>
      </div>

      <!-- Hidden Section: Holiday Tariffs -->
      <div v-if="formState.includeHolidayTariffs" class="holiday-tariffs-container">
        <!-- Left Side: List -->
        <div class="left-panel">
          <div class="holiday-section-header">
            <span class="title-section-label">Tarifas festivas 2025</span>
            <a-dropdown trigger="click">
              <a @click="addDateRangeToLastGroup" class="add-holiday-label">
                <svg
                  width="24"
                  height="24"
                  viewBox="0 0 24 24"
                  fill="none"
                  xmlns="http://www.w3.org/2000/svg"
                  class="q-mr-xs"
                >
                  <path
                    d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z"
                    stroke="#575B5F"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                  <path
                    d="M12 8V16"
                    stroke="#575B5F"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                  <path
                    d="M8 12H16"
                    stroke="#575B5F"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                </svg>
              </a>
              <template #overlay>
                <a-menu>
                  <a-menu-item key="global">Global</a-menu-item>
                  <a-menu-item key="festiva">Festiva</a-menu-item>
                  <a-menu-item key="ciudad">Ciudad</a-menu-item>
                </a-menu>
              </template>
            </a-dropdown>
          </div>

          <div
            v-for="category in holidayCategories"
            :key="category.id"
            class="category-group"
            :class="{ selected: selectedCategoryId === category.id }"
            :style="{ opacity: category.checked ? 1 : 0.6 }"
            @click="selectCategory(category.id)"
          >
            <div class="category-header">
              <div class="flex-center">
                <a-checkbox v-model:checked="category.checked" class="q-mr-sm" />
                <a-tooltip :title="category.name">
                  <font-awesome-icon :icon="['fas', category.icon]" class="q-mr-sm" />
                </a-tooltip>
                <span>{{ category.name }}</span>
              </div>
              <div class="category-meta">
                {{ category.count }}
                <svg
                  width="16"
                  height="16"
                  viewBox="0 0 16 16"
                  fill="none"
                  xmlns="http://www.w3.org/2000/svg"
                >
                  <path
                    d="M6.06861 3.7C5.95116 3.58048 5.80506 3.49304 5.64423 3.44599C5.48341 3.39895 5.31321 3.39387 5.14986 3.43125C4.98503 3.46768 4.83211 3.54524 4.70534 3.65672C4.57857 3.76819 4.48208 3.90993 4.42486 4.06875L1.05611 13.325C1.00358 13.4756 0.987621 13.6366 1.00955 13.7947C1.03149 13.9527 1.09068 14.1032 1.18226 14.2339C1.27384 14.3645 1.39517 14.4715 1.53624 14.5461C1.67731 14.6206 1.83408 14.6605 1.99361 14.6625C2.1105 14.6627 2.22663 14.6437 2.33736 14.6062L11.5936 11.2375C11.7524 11.1803 11.8942 11.0838 12.0056 10.957C12.1171 10.8303 12.1947 10.6773 12.2311 10.5125C12.2685 10.3492 12.2634 10.179 12.2164 10.0181C12.1693 9.8573 12.0819 9.7112 11.9624 9.59375L6.06861 3.7ZM3.14986 10.4875L4.34986 7.1875L8.47486 11.3125L5.17486 12.5125L3.14986 10.4875ZM9.16236 5C9.17156 4.66184 9.25252 4.3295 9.39986 4.025C9.73111 3.3625 10.3561 3 11.1624 3C11.5811 3 11.8499 2.85625 12.0186 2.55C12.1046 2.37658 12.1536 2.18714 12.1624 1.99375C12.1624 1.92838 12.1753 1.86366 12.2005 1.80335C12.2257 1.74303 12.2627 1.68832 12.3092 1.64239C12.3557 1.59646 12.4109 1.56022 12.4715 1.53578C12.5321 1.51133 12.597 1.49917 12.6624 1.5C12.795 1.5 12.9221 1.55268 13.0159 1.64645C13.1097 1.74021 13.1624 1.86739 13.1624 2C13.1624 2.80625 12.6311 4 11.1624 4C10.7436 4 10.4749 4.14375 10.3061 4.45C10.2201 4.62342 10.1711 4.81286 10.1624 5.00625C10.1624 5.07162 10.1494 5.13634 10.1242 5.19665C10.099 5.25697 10.0621 5.31168 10.0155 5.35761C9.96903 5.40354 9.91386 5.43978 9.85323 5.46422C9.79261 5.48867 9.72773 5.50083 9.66236 5.5C9.52975 5.5 9.40258 5.44732 9.30881 5.35355C9.21504 5.25979 9.16236 5.13261 9.16236 5ZM7.66236 3V1.5C7.66236 1.36739 7.71504 1.24021 7.80881 1.14645C7.90258 1.05268 8.02976 1 8.16236 1C8.29497 1 8.42215 1.05268 8.51592 1.14645C8.60969 1.24021 8.66236 1.36739 8.66236 1.5V3C8.66236 3.13261 8.60969 3.25979 8.51592 3.35355C8.42215 3.44732 8.29497 3.5 8.16236 3.5C8.02976 3.5 7.90258 3.44732 7.80881 3.35355C7.71504 3.25979 7.66236 3.13261 7.66236 3ZM14.0186 8.14375C14.1125 8.23855 14.1652 8.36658 14.1652 8.5C14.1652 8.63342 14.1125 8.76145 14.0186 8.85625C13.923 8.94866 13.7953 9.00032 13.6624 9.00032C13.5294 9.00032 13.4017 8.94866 13.3061 8.85625L12.3061 7.85625C12.2264 7.7591 12.1856 7.63577 12.1918 7.51024C12.198 7.38472 12.2506 7.26597 12.3395 7.17711C12.4283 7.08824 12.5471 7.0356 12.6726 7.02944C12.7981 7.02327 12.9215 7.06402 13.0186 7.14375L14.0186 8.14375ZM14.3186 5.475L12.8186 5.975C12.7684 5.99237 12.7155 6.00083 12.6624 6C12.5434 6.00003 12.4284 5.95768 12.3379 5.88054C12.2474 5.8034 12.1874 5.69654 12.1685 5.57912C12.1497 5.4617 12.1734 5.34142 12.2352 5.23987C12.2971 5.13831 12.3931 5.06213 12.5061 5.025L14.0061 4.525C14.0685 4.50448 14.1343 4.49645 14.1998 4.50136C14.2653 4.50628 14.3291 4.52404 14.3878 4.55364C14.4464 4.58324 14.4986 4.62409 14.5414 4.67387C14.5842 4.72365 14.6168 4.78137 14.6374 4.84375C14.6579 4.90613 14.6659 4.97194 14.661 5.03742C14.6561 5.1029 14.6383 5.16677 14.6087 5.22539C14.5791 5.28401 14.5383 5.33622 14.4885 5.37905C14.4387 5.42188 14.381 5.45448 14.3186 5.475Z"
                    fill="#2F353A"
                  />
                </svg>
              </div>
            </div>

            <!-- Items -->
            <template v-if="category.checked">
              <div class="category-month-title">
                <svg
                  width="10"
                  height="10"
                  viewBox="0 0 10 10"
                  fill="none"
                  xmlns="http://www.w3.org/2000/svg"
                >
                  <g clip-path="url(#clip0_17850_145933)">
                    <path
                      d="M9.5 4.25V9.0625C9.5 9.24329 9.3088 9.49994 8.92871 9.5H1.07129C0.691052 9.49994 0.5 9.24335 0.5 9.0625V4.25H9.5ZM1.78613 4.5C1.37661 4.5 0.928711 4.80461 0.928711 5.3125V7.1875C0.928711 7.69539 1.37661 8 1.78613 8H3.92871C4.33644 7.99993 4.78613 7.69609 4.78613 7.1875V5.3125C4.78613 4.80391 4.33644 4.50007 3.92871 4.5H1.78613ZM7.14258 0.5C7.2226 0.5 7.28307 0.528718 7.31836 0.55957C7.35265 0.589594 7.35742 0.613637 7.35742 0.625V1.75H8.92871C9.30887 1.75006 9.5 2.00658 9.5 2.1875V2.625H0.5V2.1875C0.5 2.00652 0.69098 1.75006 1.07129 1.75H2.64258V0.625C2.64258 0.613637 2.64735 0.589594 2.68164 0.55957C2.71693 0.528718 2.7774 0.5 2.85742 0.5C2.93737 0.500066 2.99797 0.528724 3.0332 0.55957C3.06728 0.589502 3.07129 0.613662 3.07129 0.625V1.75H6.92871V0.625C6.92871 0.613662 6.93272 0.589502 6.9668 0.55957C7.00203 0.528724 7.06263 0.500066 7.14258 0.5Z"
                      fill="#BABCBD"
                      stroke="#BABCBD"
                    />
                  </g>
                  <defs>
                    <clipPath id="clip0_17850_145933">
                      <rect width="10" height="10" fill="white" />
                    </clipPath>
                  </defs>
                </svg>
                Enero
              </div>
              <div v-for="item in category.items.slice(0, 1)" :key="item.id" class="holiday-item">
                <div class="flex-center holiday-name">
                  <a-checkbox v-model:checked="item.checked" class="q-mr-sm" />
                  <span>{{ item.name }}</span>
                  <svg
                    v-if="!item.isEditing"
                    @click="toggleEdit(item)"
                    width="18"
                    height="18"
                    viewBox="0 0 18 18"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                    style="margin-left: auto; cursor: pointer"
                  >
                    <g clip-path="url(#clip0_18116_54202)">
                      <path
                        d="M10.5 1.5L13.5 4.5L5.25 12.75H2.25V9.75L10.5 1.5Z"
                        stroke="#BABCBD"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                      />
                      <path
                        d="M2.25 16.5H15.75"
                        stroke="#BABCBD"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                      />
                    </g>
                    <defs>
                      <clipPath id="clip0_18116_54202">
                        <rect width="18" height="18" fill="white" />
                      </clipPath>
                    </defs>
                  </svg>
                  <svg
                    v-else
                    @click="toggleEdit(item)"
                    width="18"
                    height="18"
                    viewBox="0 0 18 18"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                    style="margin-left: auto; cursor: pointer"
                  >
                    <path
                      d="M14.25 15.75H3.75C3.35218 15.75 2.97064 15.592 2.68934 15.3107C2.40804 15.0294 2.25 14.6478 2.25 14.25V3.75C2.25 3.35218 2.40804 2.97064 2.68934 2.68934C2.97064 2.40804 3.35218 2.25 3.75 2.25H12L15.75 6V14.25C15.75 14.6478 15.592 15.0294 15.3107 15.3107C15.0294 15.592 14.6478 15.75 14.25 15.75Z"
                      stroke="#2F353A"
                      stroke-width="2"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                    />
                    <path
                      d="M12.75 15.75V9.75H5.25V15.75"
                      stroke="#2F353A"
                      stroke-width="2"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                    />
                    <path
                      d="M5.25 2.25V6H11.25"
                      stroke="#2F353A"
                      stroke-width="2"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                    />
                  </svg>
                </div>
                <div class="holiday-date">• {{ item.date }}</div>
              </div>

              <div class="category-month-title">
                <svg
                  width="10"
                  height="10"
                  viewBox="0 0 10 10"
                  fill="none"
                  xmlns="http://www.w3.org/2000/svg"
                >
                  <g clip-path="url(#clip0_17850_145933)">
                    <path
                      d="M9.5 4.25V9.0625C9.5 9.24329 9.3088 9.49994 8.92871 9.5H1.07129C0.691052 9.49994 0.5 9.24335 0.5 9.0625V4.25H9.5ZM1.78613 4.5C1.37661 4.5 0.928711 4.80461 0.928711 5.3125V7.1875C0.928711 7.69539 1.37661 8 1.78613 8H3.92871C4.33644 7.99993 4.78613 7.69609 4.78613 7.1875V5.3125C4.78613 4.80391 4.33644 4.50007 3.92871 4.5H1.78613ZM7.14258 0.5C7.2226 0.5 7.28307 0.528718 7.31836 0.55957C7.35265 0.589594 7.35742 0.613637 7.35742 0.625V1.75H8.92871C9.30887 1.75006 9.5 2.00658 9.5 2.1875V2.625H0.5V2.1875C0.5 2.00652 0.69098 1.75006 1.07129 1.75H2.64258V0.625C2.64258 0.613637 2.64735 0.589594 2.68164 0.55957C2.71693 0.528718 2.7774 0.5 2.85742 0.5C2.93737 0.500066 2.99797 0.528724 3.0332 0.55957C3.06728 0.589502 3.07129 0.613662 3.07129 0.625V1.75H6.92871V0.625C6.92871 0.613662 6.93272 0.589502 6.9668 0.55957C7.00203 0.528724 7.06263 0.500066 7.14258 0.5Z"
                      fill="#BABCBD"
                      stroke="#BABCBD"
                    />
                  </g>
                  <defs>
                    <clipPath id="clip0_17850_145933">
                      <rect width="10" height="10" fill="white" />
                    </clipPath>
                  </defs>
                </svg>
                Febrero
              </div>
              <div v-for="item in category.items.slice(1, 2)" :key="item.id" class="holiday-item">
                <div class="flex-center holiday-name">
                  <a-checkbox v-model:checked="item.checked" class="q-mr-sm" />
                  <span>{{ item.name }}</span>
                  <svg
                    v-if="!item.isEditing"
                    @click="toggleEdit(item)"
                    width="18"
                    height="18"
                    viewBox="0 0 18 18"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                    style="margin-left: auto; cursor: pointer"
                  >
                    <g clip-path="url(#clip0_18116_54202)">
                      <path
                        d="M10.5 1.5L13.5 4.5L5.25 12.75H2.25V9.75L10.5 1.5Z"
                        stroke="#BABCBD"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                      />
                      <path
                        d="M2.25 16.5H15.75"
                        stroke="#BABCBD"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                      />
                    </g>
                    <defs>
                      <clipPath id="clip0_18116_54202">
                        <rect width="18" height="18" fill="white" />
                      </clipPath>
                    </defs>
                  </svg>
                  <svg
                    v-else
                    @click="toggleEdit(item)"
                    width="18"
                    height="18"
                    viewBox="0 0 18 18"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                    style="margin-left: auto; cursor: pointer"
                  >
                    <path
                      d="M14.25 15.75H3.75C3.35218 15.75 2.97064 15.592 2.68934 15.3107C2.40804 15.0294 2.25 14.6478 2.25 14.25V3.75C2.25 3.35218 2.40804 2.97064 2.68934 2.68934C2.97064 2.40804 3.35218 2.25 3.75 2.25H12L15.75 6V14.25C15.75 14.6478 15.592 15.0294 15.3107 15.3107C15.0294 15.592 14.6478 15.75 14.25 15.75Z"
                      stroke="#2F353A"
                      stroke-width="2"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                    />
                    <path
                      d="M12.75 15.75V9.75H5.25V15.75"
                      stroke="#2F353A"
                      stroke-width="2"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                    />
                    <path
                      d="M5.25 2.25V6H11.25"
                      stroke="#2F353A"
                      stroke-width="2"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                    />
                  </svg>
                </div>
                <div class="holiday-date">• {{ item.date }}</div>
              </div>
              <div class="category-month-title">
                <svg
                  width="10"
                  height="10"
                  viewBox="0 0 10 10"
                  fill="none"
                  xmlns="http://www.w3.org/2000/svg"
                >
                  <g clip-path="url(#clip0_17850_145933)">
                    <path
                      d="M9.5 4.25V9.0625C9.5 9.24329 9.3088 9.49994 8.92871 9.5H1.07129C0.691052 9.49994 0.5 9.24335 0.5 9.0625V4.25H9.5ZM1.78613 4.5C1.37661 4.5 0.928711 4.80461 0.928711 5.3125V7.1875C0.928711 7.69539 1.37661 8 1.78613 8H3.92871C4.33644 7.99993 4.78613 7.69609 4.78613 7.1875V5.3125C4.78613 4.80391 4.33644 4.50007 3.92871 4.5H1.78613ZM7.14258 0.5C7.2226 0.5 7.28307 0.528718 7.31836 0.55957C7.35265 0.589594 7.35742 0.613637 7.35742 0.625V1.75H8.92871C9.30887 1.75006 9.5 2.00658 9.5 2.1875V2.625H0.5V2.1875C0.5 2.00652 0.69098 1.75006 1.07129 1.75H2.64258V0.625C2.64258 0.613637 2.64735 0.589594 2.68164 0.55957C2.71693 0.528718 2.7774 0.5 2.85742 0.5C2.93737 0.500066 2.99797 0.528724 3.0332 0.55957C3.06728 0.589502 3.07129 0.613662 3.07129 0.625V1.75H6.92871V0.625C6.92871 0.613662 6.93272 0.589502 6.9668 0.55957C7.00203 0.528724 7.06263 0.500066 7.14258 0.5Z"
                      fill="#BABCBD"
                      stroke="#BABCBD"
                    />
                  </g>
                  <defs>
                    <clipPath id="clip0_17850_145933">
                      <rect width="10" height="10" fill="white" />
                    </clipPath>
                  </defs>
                </svg>
                Marzo
              </div>
              <div v-for="item in category.items.slice(2, 3)" :key="item.id" class="holiday-item">
                <div class="flex-center holiday-name">
                  <a-checkbox v-model:checked="item.checked" class="q-mr-sm" />
                  <span>{{ item.name }}</span>
                  <svg
                    v-if="!item.isEditing"
                    @click="toggleEdit(item)"
                    width="18"
                    height="18"
                    viewBox="0 0 18 18"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                    style="margin-left: auto; cursor: pointer"
                  >
                    <g clip-path="url(#clip0_18116_54202)">
                      <path
                        d="M10.5 1.5L13.5 4.5L5.25 12.75H2.25V9.75L10.5 1.5Z"
                        stroke="#BABCBD"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                      />
                      <path
                        d="M2.25 16.5H15.75"
                        stroke="#BABCBD"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                      />
                    </g>
                    <defs>
                      <clipPath id="clip0_18116_54202">
                        <rect width="18" height="18" fill="white" />
                      </clipPath>
                    </defs>
                  </svg>
                  <svg
                    v-else
                    @click="toggleEdit(item)"
                    width="18"
                    height="18"
                    viewBox="0 0 18 18"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                    style="margin-left: auto; cursor: pointer"
                  >
                    <path
                      d="M14.25 15.75H3.75C3.35218 15.75 2.97064 15.592 2.68934 15.3107C2.40804 15.0294 2.25 14.6478 2.25 14.25V3.75C2.25 3.35218 2.40804 2.97064 2.68934 2.68934C2.97064 2.40804 3.35218 2.25 3.75 2.25H12L15.75 6V14.25C15.75 14.6478 15.592 15.0294 15.3107 15.3107C15.0294 15.592 14.6478 15.75 14.25 15.75Z"
                      stroke="#2F353A"
                      stroke-width="2"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                    />
                    <path
                      d="M12.75 15.75V9.75H5.25V15.75"
                      stroke="#2F353A"
                      stroke-width="2"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                    />
                    <path
                      d="M5.25 2.25V6H11.25"
                      stroke="#2F353A"
                      stroke-width="2"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                    />
                  </svg>
                </div>
                <div class="holiday-date">• {{ item.date }}</div>
              </div>
            </template>
          </div>
        </div>

        <!-- Right Side: Calendars -->

        <div class="right-panel">
          <div class="calendar-grid">
            <div v-for="month in monthsList.slice(0, 3)" :key="month.name" class="month-container">
              <div class="month-title">{{ month.name }}</div>
              <div class="mini-calendar">
                <div class="weekdays-row">
                  <span>Do</span><span>Lu</span><span>Ma</span><span>Mi</span><span>Ju</span
                  ><span>Vi</span><span>Sá</span>
                </div>
                <div class="days-grid">
                  <div
                    v-for="(day, idx) in month.days"
                    :key="idx"
                    class="day-cell"
                    :class="{
                      empty: day === 0,
                      'selected-holiday': day === 1 || day === 15 || day === 24,
                      'grayed-out': day > 28 && month.name === 'Febrero', // Mock logic
                    }"
                  >
                    {{ day > 0 ? day : '' }}
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="calendar-grid">
            <div v-for="month in monthsList.slice(3, 6)" :key="month.name" class="month-container">
              <div class="month-title">{{ month.name }}</div>
              <div class="mini-calendar">
                <div class="weekdays-row">
                  <span>Do</span><span>Lu</span><span>Ma</span><span>Mi</span><span>Ju</span
                  ><span>Vi</span><span>Sá</span>
                </div>
                <div class="days-grid">
                  <div
                    v-for="(day, idx) in month.days"
                    :key="idx"
                    class="day-cell"
                    :class="{ empty: day === 0, 'selected-holiday': day === 2 || day === 29 }"
                  >
                    <!-- Mock highlights -->
                    {{ day > 0 ? day : '' }}
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="calendar-grid">
            <div v-for="month in monthsList.slice(6, 9)" :key="month.name" class="month-container">
              <div class="month-title">{{ month.name }}</div>
              <div class="mini-calendar">
                <div class="weekdays-row">
                  <span>Do</span><span>Lu</span><span>Ma</span><span>Mi</span><span>Ju</span
                  ><span>Vi</span><span>Sá</span>
                </div>
                <div class="days-grid">
                  <div
                    v-for="(day, idx) in month.days"
                    :key="idx"
                    class="day-cell"
                    :class="{ empty: day === 0, 'selected-holiday': day === 1 || day === 24 }"
                  >
                    {{ day > 0 ? day : '' }}
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="calendar-grid">
            <div v-for="month in monthsList.slice(9, 12)" :key="month.name" class="month-container">
              <div class="month-title">{{ month.name }}</div>
              <div class="mini-calendar">
                <div class="weekdays-row">
                  <span>Do</span><span>Lu</span><span>Ma</span><span>Mi</span><span>Ju</span
                  ><span>Vi</span><span>Sá</span>
                </div>
                <div class="days-grid">
                  <div
                    v-for="(day, idx) in month.days"
                    :key="idx"
                    class="day-cell"
                    :class="{
                      empty: day === 0,
                      'selected-holiday': day === 9 && month.name === 'Noviembre',
                    }"
                  >
                    {{ day > 0 ? day : '' }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Section 3: Currency -->
    <div class="content-card">
      <div class="row section-header-row">
        <div class="col-12">
          <label class="field-section-label">Moneda base: <span class="required">*</span></label>
        </div>
      </div>
      <div class="row">
        <div class="col-5">
          <div class="row">
            <div class="col-6">
              <a-form-item>
                <template #label>
                  <span
                    >Compra: <span class="required">*</span>
                    <a-tooltip
                      title="Moneda para tarifario del proveedor"
                      placement="topLeft"
                      overlay-class-name="tooltip-nowrap"
                    >
                      <font-awesome-icon :icon="['fas', 'circle-info']" />
                    </a-tooltip>
                  </span>
                </template>
                <a-select
                  v-model:value="formState.currencyBuy"
                  placeholder="Selecciona"
                  size="large"
                  :options="currencyOptions"
                />
              </a-form-item>
            </div>
            <div class="col-6">
              <a-form-item>
                <template #label>
                  <span
                    >Venta: <span class="required">*</span>
                    <a-tooltip
                      title="Trato con el cliente"
                      placement="topLeft"
                      overlay-class-name="tooltip-nowrap"
                    >
                      <font-awesome-icon :icon="['fas', 'circle-info']" />
                    </a-tooltip>
                  </span>
                </template>
                <a-select
                  v-model:value="formState.currencySell"
                  placeholder="USD $"
                  size="large"
                  :options="currencyOptions"
                />
              </a-form-item>
            </div>
          </div>
        </div>
      </div>
    </div>
  </a-form>
</template>

<script setup lang="ts">
  import { computed, reactive, watch, ref } from 'vue';
  import { PlusCircleOutlined, CloseOutlined } from '@ant-design/icons-vue';
  import { usePricingPlansStore } from '@/modules/negotiations/products/configuration/stores/usePricingPlansStore';
  import { useServiceConfigurationSidebar } from '@/modules/negotiations/products/configuration/composables/useServiceConfigurationSidebar';

  defineOptions({
    name: 'BasicDataForm',
  });

  const store = usePricingPlansStore();
  const formState = store.basicData;
  const { setSidebarCollapsed } = useServiceConfigurationSidebar();

  watch(
    () => formState.includeHolidayTariffs,
    (newValue) => {
      setSidebarCollapsed(newValue);
    }
  );

  watch(
    () => formState.tariffType,
    (newValue) => {
      if (!['plana', 'periodos'].includes(newValue || '')) {
        formState.includeHolidayTariffs = false;
      }
    }
  );

  // Opciones de segmentación de tarifa
  const segmentationOptions = [
    { value: 'mercados', label: 'Mercados' },
    { value: 'clientes', label: 'Clientes' },
    { value: 'series', label: 'Series' },
  ];

  // Opciones de especificación (datos de ejemplo)
  const marketOptions = [
    { value: 'canada', label: 'Canada' },
    { value: 'usa', label: 'USA' },
    { value: 'mexico', label: 'México' },
    { value: 'europa', label: 'Europa' },
  ];

  const clientOptions = [
    { value: 'travel_ja_vu', label: '(2TJAVU) Travel Ja Vu' },
    { value: 'viajes_express', label: 'Viajes Express' },
    { value: 'turismo_global', label: 'Turismo Global' },
  ];

  const seriesOptions = [
    { value: 'nombre_series', label: 'Nombre de series' },
    { value: 'premium_tours', label: 'Premium Tours' },
    { value: 'adventure_pack', label: 'Adventure Pack' },
  ];

  // Opciones de tipo de periodo
  const periodTypeOptions = [
    { value: 'temporada_baja', label: 'Temporada baja' },
    { value: 'temporada_alta', label: 'Temporada alta' },
    { value: 'temporada_media', label: 'Temporada media' },
  ];

  // Funciones para manejar periodos
  const addDateRangeToLastGroup = () => {
    if (formState.periods.length === 0) {
      addNewPeriodGroup(-1); // Add a new group if none exist
      return;
    }
    const lastGroup = formState.periods[formState.periods.length - 1];
    lastGroup.ranges.push({
      dateFrom: null,
      dateTo: null,
    });
  };

  const addNewPeriodGroup = (index: number) => {
    formState.periods.splice(index + 1, 0, {
      periodType: null,
      ranges: [
        {
          dateFrom: null,
          dateTo: null,
        },
      ],
    });
  };

  const removePeriod = (index: number) => {
    if (formState.periods.length > 1) {
      formState.periods.splice(index, 1);
    }
  };

  const removeRange = (groupIndex: number, rangeIndex: number) => {
    const group = formState.periods[groupIndex];
    if (group.ranges.length > 1) {
      group.ranges.splice(rangeIndex, 1);
    } else {
      group.ranges[rangeIndex].dateFrom = null;
      group.ranges[rangeIndex].dateTo = null;
    }
  };

  const currencyOptions = [
    { value: 'USD', label: 'Dólar americano - USD' },
    { value: 'CAD', label: 'Dólar canadiense - CAD' },
    { value: 'HKD', label: 'Dólar hongkongés - HKD' },
    { value: 'EUR', label: 'Euro - EUR' },
    { value: 'CHF', label: 'Franco suizo - CHF' },
    { value: 'GBP', label: 'Libra esterlina - GBP' },
    { value: 'JPY', label: 'Yen japonés - JPY' },
    { value: 'CNY', label: 'Yuan chino - CNY' },
  ];

  const tariffTypeOptions = [
    { value: 'plana', label: 'Plana' },
    { value: 'periodos', label: 'Periodos' },
    { value: 'promocional', label: 'Promocional' },
    { value: 'especifica', label: 'Específica' },
  ];

  const promotionOptions = [
    { value: 'plana', label: 'Plana' },
    { value: 'periodos', label: 'Periodos' },
    { value: 'promocional', label: 'Promocional' },
    { value: 'especifica', label: 'Específica' },
  ];

  const daysList = [
    { key: 'L', label: 'L', name: 'Lunes' },
    { key: 'M', label: 'M', name: 'Martes' },
    { key: 'X', label: 'X', name: 'Miércoles' },
    { key: 'J', label: 'J', name: 'Jueves' },
    { key: 'V', label: 'V', name: 'Viernes' },
    { key: 'S', label: 'S', name: 'Sábado' },
    { key: 'D', label: 'D', name: 'Domingo' },
  ];

  const toggleDay = (key: string) => {
    const index = formState.selectedDays.indexOf(key);
    if (index === -1) {
      formState.selectedDays.push(key);
    } else {
      formState.selectedDays.splice(index, 1);
    }
  };

  // Constantes para grupos de días
  const weekendDays = ['S', 'D'];
  const weekdays = ['L', 'M', 'X', 'J', 'V'];

  // Computed properties para detectar si se seleccionaron grupos completos
  const isWeekendSelected = computed(() => {
    // Priority Rule: If ANY weekend day (S or D) is selected, it classifies as "Weekend"
    if (!formState.differentiatedTariff) return false;
    const weekendDays = ['S', 'D'];
    return formState.selectedDays.some((day) => weekendDays.includes(day));
  });

  const isWeekdaysSelected = computed(() => {
    // Only if specific weekdays are selected AND NO weekend days are involved
    if (!formState.differentiatedTariff) return false;
    if (formState.selectedDays.length === 0) return false;

    const weekendDays = ['S', 'D'];
    const hasWeekend = formState.selectedDays.some((day) => weekendDays.includes(day));

    return !hasWeekend;
  });

  // Funciones para seleccionar/limpiar grupos de días
  const selectWeekend = () => {
    weekendDays.forEach((day) => {
      if (!formState.selectedDays.includes(day)) {
        formState.selectedDays.push(day);
      }
    });
  };

  const clearWeekend = () => {
    formState.selectedDays = formState.selectedDays.filter((day) => !weekendDays.includes(day));
  };

  const selectWeekdays = () => {
    // Remove weekend days to ensure we switch to logical "Weekdays" mode
    formState.selectedDays = formState.selectedDays.filter((day) => !weekendDays.includes(day));

    weekdays.forEach((day) => {
      if (!formState.selectedDays.includes(day)) {
        formState.selectedDays.push(day);
      }
    });
  };

  const selectedCategoryId = ref('global');

  const selectCategory = (id: string) => {
    selectedCategoryId.value = id;
  };

  const holidayCategories = reactive([
    {
      id: 'global',
      name: 'Global',
      count: '2 fechas',
      icon: 'earth-americas',
      checked: true,
      items: [
        { id: 1, name: 'Nombre del festivo', date: 'Viernes 10', checked: true, isEditing: false },
        { id: 2, name: 'Nombre del festivo', date: 'Viernes 10', checked: true, isEditing: false },
        { id: 3, name: 'Nombre del festivo', date: 'Viernes 10', checked: true, isEditing: false },
      ],
    },
    {
      id: 'turisticos',
      name: 'Turísticos',
      count: '3 fechas',
      icon: 'plane',
      checked: true,
      items: [
        { id: 4, name: 'Nombre del festivo', date: 'Viernes 10', checked: false, isEditing: false },
        { id: 5, name: 'Nombre del festivo', date: 'Martes 21', checked: false, isEditing: false },
        { id: 6, name: 'Nombre del festivo', date: 'Martes 21', checked: false, isEditing: false },
      ],
    },
    {
      id: 'cusco',
      name: 'Cusco',
      count: '2 fechas',
      icon: 'location-dot',
      checked: true,
      items: [
        { id: 7, name: 'Nombre del festivo', date: 'Viernes 10', checked: false, isEditing: false },
        { id: 8, name: 'Nombre del festivo', date: 'Viernes 10', checked: false, isEditing: false },
        { id: 9, name: 'Nombre del festivo', date: 'Viernes 10', checked: false, isEditing: false },
      ],
    },
  ]);

  const toggleEdit = (item: any) => {
    item.isEditing = !item.isEditing;
  };

  watch(
    holidayCategories,
    (categories) => {
      const selected: Array<{ name: string; date: string }> = [];
      categories.forEach((cat) => {
        // Option A: If category is checked, include all its checked items?
        // Or if category is checked it just enables the section?
        // Assuming individual items must be checked.
        if (cat.checked) {
          cat.items.forEach((item) => {
            if (item.checked) {
              selected.push({ name: item.name, date: item.date });
            }
          });
        }
      });
      formState.selectedHolidays = selected;
    },
    { deep: true, immediate: true }
  );

  // Helper to generate a simple month grid
  // 0 = empty, number = day, negative = gray/disabled/next month preview
  const generateMonthDays = (startDay: number, daysInMonth: number) => {
    const arr = [];
    // Pad start
    for (let i = 0; i < startDay; i++) arr.push(0);
    // Days
    for (let i = 1; i <= daysInMonth; i++) arr.push(i);
    // Fill rest of week if needed (visual only)
    return arr;
  };

  const monthsList = [
    { name: 'Enero', days: generateMonthDays(3, 31) }, // Starts Wed (mock)
    { name: 'Febrero', days: generateMonthDays(6, 28) },
    { name: 'Marzo', days: generateMonthDays(6, 31) },
    { name: 'Abril', days: generateMonthDays(2, 30) },
    { name: 'Mayo', days: generateMonthDays(4, 31) },
    { name: 'Junio', days: generateMonthDays(0, 30) },
    { name: 'Julio', days: generateMonthDays(2, 31) },
    { name: 'Agosto', days: generateMonthDays(5, 31) },
    { name: 'Septiembre', days: generateMonthDays(1, 30) },
    { name: 'Octubre', days: generateMonthDays(3, 31) }, // Mock shifted for visual variety
    { name: 'Noviembre', days: generateMonthDays(6, 30) },
    { name: 'Diciembre', days: generateMonthDays(1, 31) },
  ];
</script>

<style lang="scss" scoped>
  @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

  .content-card {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 24px;
    margin-bottom: 24px;
  }

  .row {
    display: flex;
    gap: 24px;

    &.section-header-row {
      margin-bottom: 8px;
    }
  }

  .col-12 {
    flex: 0 0 100%;
    width: 100%;
  }
  .col-8 {
    flex: 0 0 50%;
    max-width: 50%;
  }
  .col-6 {
    flex: 0 0 calc(50% - 12px);
    width: calc(50% - 12px);
  }
  .col-5 {
    flex: 0 0 41.66%;
    width: 41.66%;
  }

  .full-width {
    width: 100%;
  }

  .info-icon {
    display: inline-block;
    margin-left: 8px;
    color: #2f353a;
    cursor: pointer;
  }

  .info-icon-inline {
    margin-left: 4px;
    color: #2f353a;
  }

  .label-with-tooltip {
    display: inline-flex;
    align-items: center;
  }

  .field-section-label {
    font-size: 14px;
    font-weight: 500;
    color: #2f353a;
    display: flex;
    align-items: center;
  }

  .required {
    color: #ff4d4f;
    margin-left: 4px;
  }

  .bg-gray {
    background-color: #f9f9f9;

    :deep(.ant-input) {
      background-color: #f5f5f5;
    }
  }

  .bg-light-gray {
    background-color: #f9f9f9;
  }

  .rounded-borders {
    border-radius: 4px;
  }

  .flex-center {
    display: flex;
    align-items: center;
  }

  .label-text {
    font-weight: 500;
    color: #2f353a;
  }

  /* Estilos para la sección de periodos */
  .periods-section {
    margin-bottom: 24px;
    background-color: #f9f9f9;
    padding: 20px;
    border-radius: 6px;
  }

  .periods-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
  }

  .add-period-link {
    color: #1284ed;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;

    &:hover {
      text-decoration: underline;
    }
  }

  .period-row {
    margin-bottom: 16px;

    &:last-child {
      margin-bottom: 0;
    }
  }

  .period-fields {
    display: flex;
    align-items: flex-start;
    gap: 16px;
  }

  .period-field {
    &.period-type {
      flex: 0 0 200px;
      width: 200px;
    }

    &.period-date {
      flex: 0 0 160px;
      width: 160px;
    }
  }

  .period-actions {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-top: 38px; /* Compensar altura del label para alinear con inputs */
  }

  .action-icon {
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;

    &:hover {
      opacity: 0.8;
    }
  }

  /* Estilos para los tags de selects múltiples */
  :deep(.ant-select-multiple) {
    .ant-select-selection-item {
      background-color: #dcdcdc;
      border-color: #dcdcdc;
      color: #2f353a;
      border-radius: 4px;
    }

    .ant-select-selection-item-remove {
      color: #2f353a;
    }
  }

  /* Enhancements to match image specifically */
  :deep(.ant-form-item-label > label) {
    font-weight: 500;
    color: #595959;
  }

  .q-mr-md {
    margin-right: 16px;
  }
  .q-pa-sm {
    padding: 8px 16px;
  }
  .q-mb-sm {
    margin-bottom: 8px;
  }
  .q-mb-md {
    margin-bottom: 16px;
  }
  .q-mt-md {
    margin-top: 16px;
  }
  .q-mr-sm {
    margin-right: 8px;
  }

  /* Day Selector Styles */
  .day-toggles {
    display: flex;
    gap: 8px;
  }

  .day-circle {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    border: 1px solid #d9d9d9;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    color: #595959;
    cursor: pointer;
    background: #fff;
    transition: all 0.2s;
    user-select: none;

    &.selected {
      border-color: #1284ed;
      background-color: #1284ed;
      color: #fff;
      font-weight: 600;
    }

    &:hover {
      border-color: #1284ed;
    }
  }

  .help-text {
    font-size: 12px;
    color: #8c8c8c;
  }

  .text-gray {
    color: #bfbfbf;
  }

  .tab-item {
    font-size: 14px;
    line-height: 16px;
    font-weight: 500;
    color: #595959;
    cursor: pointer;
    background: #fff;
    border: 1px solid #d9d9d9;
    border-radius: 4px;
    padding: 4px 8px;
    transition: all 0.2s;

    &:hover {
      color: #2f353a;
      border-color: #2f353a;
    }
  }

  .tab-item-faded {
    font-size: 14px;
    color: #bfbfbf;
    cursor: default;
  }

  /* Tariff Tabs - Fin de semana / Días de semana */
  .tariff-tabs-row {
    display: flex;
    gap: 16px;
  }

  .tariff-tab {
    border: 1px solid #d9d9d9;
    border-radius: 8px;
    padding: 12px 16px;
    min-width: 140px;
    cursor: pointer;
    transition: all 0.2s;
    background: #fff;

    &.active {
      border-color: #1284ed;
      border-width: 2px;
    }

    &:hover:not(.active) {
      border-color: #8c8c8c;
    }
  }

  .tariff-tab-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;

    .tab-icon {
      color: #8c8c8c;
      font-size: 14px;
    }

    .tab-label {
      font-size: 14px;
      color: #8c8c8c;
      font-weight: 400;
    }

    .tab-value {
      font-size: 14px;
      color: #262626;
      font-weight: 500;
    }

    .close-btn {
      margin-left: auto;
      font-size: 16px;
      color: #8c8c8c;
      cursor: pointer;
      line-height: 1;

      &:hover {
        color: #262626;
      }
    }
  }

  .tariff-tab-days {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;

    span {
      font-size: 12px;
      color: #595959;
    }
  }

  /* Holiday Tariffs Section */
  .holiday-tariffs-container {
    padding: 24px;
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    margin-top: 16px;
    display: flex;
    gap: 32px;
  }

  .left-panel {
    width: 280px;
    flex-shrink: 0;

    h3 {
      font-size: 14px;
      font-weight: 600;
      color: #8c8c8c;
      margin-bottom: 16px;
    }
  }

  .category-group {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 16px;
    cursor: pointer;
    transition: all 0.2s;

    &.selected {
      border: 1px solid #1284ed;
    }

    .category-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 12px;
      font-weight: 600;
      color: #2f353a;
    }

    .category-meta {
      font-size: 12px;
      color: #2f353a;
      background-color: #f9f9f9;
      border-radius: 4px;
      padding: 4px 8px;
      display: flex;
      align-items: center;
      gap: 8px;
      font-weight: 600;
    }

    .category-month-title {
      font-size: 10px;
      color: #8c8c8c;
      margin-bottom: 4px;
      margin-top: 8px;
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .holiday-item {
      margin-top: 12px;
      padding-left: 2px;

      .holiday-name {
        display: flex;
        align-items: center;
        margin-bottom: 4px;

        span {
          font-weight: 500;
          margin-right: 8px;
        }

        .edit-icon {
          color: #bfbfbf;
        }
      }

      .holiday-date {
        font-size: 12px;
        color: #8c8c8c;
        margin-left: 24px; /* Align with text start */
      }
    }
  }

  .right-panel {
    flex: 1;
    overflow-x: auto;
  }

  .calendar-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
    margin-bottom: 24px;
  }

  .month-container {
    h4 {
      text-align: center;
      font-size: 12px;
      line-height: 19px;
      font-weight: 600;
      letter-spacing: 0.015em;
      color: #595959;
      margin-bottom: 10px;
    }
  }

  .mini-calendar {
    width: 100%;

    .weekdays-row {
      display: flex;
      justify-content: space-between;
      margin-bottom: 8px;

      span {
        width: 24px;
        text-align: center;
        font-size: 10px;
        color: #8c8c8c;
      }
    }

    .days-grid {
      display: grid;
      grid-template-columns: repeat(7, 1fr);
      row-gap: 4px;

      .day-cell {
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        color: #595959;

        &.empty {
          pointer-events: none;
        }

        &.selected-holiday {
          background-color: #595959;
          color: #fff;
          border-radius: 50%;
          width: 24px;
          margin: 0 auto;
        }

        &.grayed-out {
          color: #bfbfbf;
        }
      }
    }
  }
  .title-section-label {
    font-size: 14px !important;
    line-height: 19px;
    font-weight: 600;
    letter-spacing: 0.015em;
    color: #7e8285;
    margin-bottom: 10px;
  }

  .month-title {
    font-size: 12px;
    line-height: 19px;
    font-weight: 600;
    letter-spacing: 0.015em;
    color: #4f4b4b;
    text-align: center;
    margin-bottom: 10px;
  }
  .action-icon:focus,
  .action-icon:active,
  .action-icon:focus-visible,
  .col-actions *:focus,
  .col-actions *:active,
  .col-actions *:focus-visible {
    outline: none !important;
    box-shadow: none !important;
    border: none;
  }

  .info-icon-inline:focus,
  .info-icon-inline:active,
  .info-icon-inline:focus-visible {
    outline: none !important;
    box-shadow: none !important;
  }

  .holiday-section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
  }

  .add-holiday-icon {
    font-size: 20px;
    cursor: pointer;
    color: #1a1a1a;
  }

  .period-group-card {
    background: #fbfbfb;
    border-radius: 4px;
    padding: 24px;
    position: relative;
    margin-bottom: 24px;
  }

  .period-card-content {
    display: flex;
    gap: 24px;
    align-items: flex-start;
  }

  .period-type-col {
    width: 250px;
    flex-shrink: 0;
  }

  .period-ranges-col {
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    gap: 16px;
  }

  .period-range-row {
    display: flex;
    gap: 16px;
    align-items: center;
    width: 100%;
  }

  .range-inputs-wrapper {
    width: 358px;
    display: flex;
    gap: 16px;
  }

  .range-date-input {
    flex: 1;
  }

  .range-actions {
    display: flex;
    gap: 8px;
    margin-top: 4px; /* Align with inputs */
    align-items: center;
  }

  .remove-group-icon {
    position: absolute;
    top: 16px;
    right: 16px;
    cursor: pointer;
    z-index: 10;

    &:hover {
      opacity: 0.8;
    }
  }

  .add-holiday-icon:hover {
    color: #40a9ff;
  }
</style>

<style lang="scss">
  .tooltip-nowrap {
    max-width: none !important;

    .ant-tooltip-content {
      max-width: none !important;
    }

    .ant-tooltip-inner {
      white-space: nowrap !important;
      max-width: none !important;
      font-family: 'Inter', sans-serif;
      font-size: 12px;
      font-weight: 500;
      line-height: 18px;
      text-align: center;
    }
  }
</style>
