<template>
  <div>
    <a-form layout="vertical" :model="formState">
      <!-- Section 1: Period Selection -->
      <div class="content-card">
        <div class="row" style="margin-bottom: 20px">
          <div class="col-12">
            <span class="section-instruction">
              Seleccione la fecha para configurar las tarifas correspondientes
            </span>
            <span class="section-instruction-hint q-ml-sm"> Fechas configuradas en el paso 1 </span>
          </div>
        </div>

        <div class="period-cards-row">
          <label
            v-for="(period, index) in periodCards"
            :key="index"
            class="period-card"
            :class="{ 'period-card--selected': formState.selectedPeriod === index }"
          >
            <div class="period-card__header">
              <span class="period-card__name">{{ period.name }}</span>
              <a-radio
                :checked="formState.selectedPeriod === index"
                @change="changePeriod(index)"
              />
            </div>
            <div class="period-card__dates">
              <font-awesome-icon :icon="['far', 'calendar']" class="period-card__icon" />
              {{ period.dates }}
            </div>
          </label>
        </div>
      </div>

      <!-- Section 2: Estatus de tarifa -->
      <div class="content-card">
        <div class="row">
          <div style="width: 472px">
            <a-form-item>
              <template #label>
                <span>Estatus de tarifa: <span class="required">*</span></span>
              </template>
              <a-select
                v-model:value="formState.tariffStatus"
                placeholder="Selecciona"
                size="large"
                :options="tariffStatusOptions"
              />
            </a-form-item>
          </div>
        </div>
      </div>

      <!-- Section 3: Políticas asociadas -->
      <div class="content-card bg-color-custom">
        <div class="policies-header">
          <span class="policies-title">Políticas asociadas:</span>
          <svg
            class="policies-refresh"
            width="24"
            height="24"
            viewBox="0 0 24 24"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
            @click="openPolicyDrawer"
          >
            <g clip-path="url(#clip0_refresh)">
              <path
                d="M17 1L21 5L17 9"
                stroke="#575B5F"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
              <path
                d="M3 11V9C3 7.93913 3.42143 6.92172 4.17157 6.17157C4.92172 5.42143 5.93913 5 7 5H21"
                stroke="#575B5F"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
              <path
                d="M7 23L3 19L7 15"
                stroke="#575B5F"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
              <path
                d="M21 13V15C21 16.0609 20.5786 17.0783 19.8284 17.8284C19.0783 18.5786 18.0609 19 17 19H3"
                stroke="#575B5F"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
            </g>
            <defs>
              <clipPath id="clip0_refresh">
                <rect width="24" height="24" fill="white" />
              </clipPath>
            </defs>
          </svg>
        </div>
        <div class="policies-tags">
          <div v-for="(policy, index) in associatedPolicies" :key="index" class="policy-tag">
            <font-awesome-icon :icon="['fas', 'file-lines']" class="policy-tag__icon" />
            <span class="policy-tag__name">{{ policy.name }}</span>
            <span class="policy-tag__separator">•</span>
            <font-awesome-icon :icon="['fas', 'users']" class="policy-tag__users-icon" />
            <span class="policy-tag__passengers">{{ policy.passengers }}</span>
          </div>
        </div>
      </div>

      <!-- Drawer: Cambiar política de tarifa -->
      <a-drawer
        v-model:open="showPolicyDrawer"
        :width="500"
        :maskClosable="false"
        :keyboard="false"
        @close="closePolicyDrawer"
        class="policy-change-drawer"
      >
        <template #title>
          <div class="drawer-title-block">
            <div class="drawer-title">Cambiar política de tarifa</div>
            <div class="drawer-subtitle">Elimina las actuales y establece nuevas políticas</div>
          </div>
        </template>

        <a-alert type="warning" show-icon closable class="drawer-alert">
          <template #icon>
            <svg
              width="16"
              height="14"
              viewBox="0 0 16 14"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M15.8214 12.0317L9.1555 0.656273C8.64517 -0.218758 7.35731 -0.218758 6.84354 0.656273L0.180799 12.0317C-0.332032 12.9036 0.30671 14.0005 1.33459 14.0005H14.6663C15.6901 14.0005 16.3308 12.9067 15.8214 12.0317ZM7.24918 4.25015C7.24918 3.83607 7.58513 3.50012 7.99921 3.50012C8.41328 3.50012 8.74923 3.83764 8.74923 4.25015V8.25029C8.74923 8.66437 8.41328 9.00032 8.02733 9.00032C7.64138 9.00032 7.24918 8.66593 7.24918 8.25029V4.25015ZM7.99921 12.0004C7.45669 12.0004 7.01667 11.5604 7.01667 11.0179C7.01667 10.4754 7.45637 10.0354 7.99921 10.0354C8.54204 10.0354 8.98174 10.4754 8.98174 11.0179C8.98049 11.5598 8.54297 12.0004 7.99921 12.0004Z"
                fill="#FFCC00"
              />
            </svg>
          </template>
          <template #message>
            <span style="font-weight: 600; color: #2f353a">Atención</span>
          </template>
          <template #description>
            <span style="color: #2f353a">
              Cambiar la política desactiva la asignación automática.<br />
              Acepta ser responsable de posibles errores.
            </span>
          </template>
        </a-alert>

        <div style="margin-top: 24px">
          <label class="drawer-field-label">Actual:</label>
          <a-select
            v-model:value="policyDrawerState.currentPolicy"
            placeholder="Selecciona"
            class="full-width"
            size="large"
            allow-clear
          >
            <a-select-option v-for="opt in policyDrawerOptions" :key="opt.value" :value="opt.value">
              <span class="policy-option-name">{{ opt.name }}</span>
              <span class="policy-option-detail"> • {{ opt.passengers }}</span>
            </a-select-option>
          </a-select>
          <div v-if="currentPolicyDetails" class="policy-details">
            <div v-for="(detail, idx) in currentPolicyDetails" :key="idx" class="policy-detail-row">
              <span class="policy-detail-dot" :style="{ backgroundColor: detail.color }"></span>
              <span class="policy-detail-label">{{ detail.label }}</span>
              <span class="policy-detail-value" v-html="detail.value"></span>
            </div>
          </div>
        </div>

        <div style="margin-top: 24px">
          <label class="drawer-field-label">Nueva política</label>
          <a-select
            v-model:value="policyDrawerState.newPolicy"
            placeholder="Selecciona"
            class="full-width"
            size="large"
            allow-clear
          >
            <a-select-option v-for="opt in policyDrawerOptions" :key="opt.value" :value="opt.value">
              <span class="policy-option-name">{{ opt.name }}</span>
              <span class="policy-option-detail"> • {{ opt.passengers }}</span>
            </a-select-option>
          </a-select>
          <div v-if="newPolicyDetails" class="policy-details">
            <div v-for="(detail, idx) in newPolicyDetails" :key="idx" class="policy-detail-row">
              <span class="policy-detail-dot" :style="{ backgroundColor: detail.color }"></span>
              <span class="policy-detail-label">{{ detail.label }}</span>
              <span class="policy-detail-value" v-html="detail.value"></span>
            </div>
          </div>
        </div>

        <div class="drawer-footer">
          <a-button class="button-cancel-white" @click="closePolicyDrawer"> Cancelar </a-button>
          <a-button
            :class="
              policyDrawerState.currentPolicy && policyDrawerState.newPolicy
                ? 'button-action-danger'
                : 'button-action-primary-strong'
            "
            :disabled="!policyDrawerState.newPolicy"
            @click="handleChangePolicy"
          >
            Cambiar
          </a-button>
        </div>
      </a-drawer>

      <!-- Section 4: Ingreso de tarifas -->
      <div class="row q-mb-lg tariff-input-row">
        <div class="col-auto flex-center">
          <span class="field-section-label q-mr-md">Ingreso de tarifas:</span>
          <a-radio-group v-model:value="formState.tariffInputMode">
            <a-radio value="unica">Única</a-radio>
            <a-radio value="rangos">Rangos</a-radio>
          </a-radio-group>
        </div>
        <div class="col-auto flex-center q-ml-auto">
          <a-checkbox v-model:checked="formState.includeTaxes">
            Incluye impuestos en tarifa
          </a-checkbox>
        </div>
      </div>

      <!-- Section 5: Passenger Type Cards (Única mode) -->
      <div v-if="formState.tariffInputMode === 'unica'" class="passenger-cards-row">
        <!-- Adulto -->
        <div class="passenger-card">
          <div class="passenger-card__header">
            <div class="passenger-card__info">
              <font-awesome-icon
                :icon="['fas', 'user-group']"
                class="passenger-card__icon passenger-card__icon--adulto"
              />
              <div>
                <span class="passenger-card__type">Adulto</span>
                <span class="passenger-card__age">Mayor de 12 años</span>
              </div>
            </div>
          </div>
          <div class="passenger-card__body">
            <label class="passenger-card__label">Tarifa neta ($)</label>
            <a-input-number
              v-model:value="formState.adultoRate"
              :min="0"
              :precision="2"
              :controls="false"
              size="large"
              class="full-width"
            />
            <div class="passenger-card__breakdown">
              <div class="breakdown-row">
                <span>Servicios ({{ servicePercent }}%)</span>
                <span>{{ formatNumber(adultoServices) }}</span>
              </div>
              <div class="breakdown-row">
                <span>IGV ({{ igvPercent }}%)</span>
                <span>{{ formatNumber(adultoIGV) }}</span>
              </div>
              <div class="breakdown-row breakdown-row--total">
                <span>Total</span>
                <span>{{ formatNumber(adultoTotal) }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Niño -->
        <div class="passenger-card">
          <div class="passenger-card__header">
            <div class="passenger-card__info">
              <font-awesome-icon
                :icon="['fas', 'user']"
                class="passenger-card__icon passenger-card__icon--nino"
              />
              <div>
                <span class="passenger-card__type">Niño</span>
                <span class="passenger-card__age">Mayor de 12 años</span>
              </div>
            </div>
            <div class="passenger-card__discount">
              <div class="discount-toggle-row">
                <span class="discount-label">% descuento</span>
                <a-switch v-model:checked="formState.ninoDiscount" size="small" />
              </div>
              <div v-if="formState.ninoDiscount" class="discount-input-row">
                <a-input-number
                  v-model:value="formState.ninoDiscountPercent"
                  :min="0"
                  :max="100"
                  :controls="false"
                  size="large"
                  :formatter="(value: number) => `% ${value}`"
                  :parser="(value: string) => value.replace(/\%\s?/g, '')"
                  class="discount-percent-input"
                />
              </div>
            </div>
          </div>
          <div class="passenger-card__body">
            <div v-if="formState.ninoDiscount" class="net-rate-row">
              <span class="passenger-card__label" style="margin-bottom: 0">Tarifa neta ($)</span>
              <span class="net-rate-value">{{ formatNumber(effectiveNinoRate) }}</span>
            </div>
            <template v-else>
              <label class="passenger-card__label">Tarifa neta ($)</label>
              <a-input-number
                v-model:value="formState.ninoRate"
                :min="0"
                :precision="2"
                :controls="false"
                size="large"
                class="full-width"
              />
            </template>
            <div class="passenger-card__breakdown">
              <div class="breakdown-row">
                <span>Servicios ({{ servicePercent }}%)</span>
                <span>{{ formatNumber(ninoServices) }}</span>
              </div>
              <div class="breakdown-row">
                <span>IGV ({{ igvPercent }}%)</span>
                <span>{{ formatNumber(ninoIGV) }}</span>
              </div>
              <div class="breakdown-row breakdown-row--total">
                <span>Total</span>
                <span>{{ formatNumber(ninoTotal) }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Infante -->
        <div class="passenger-card">
          <div class="passenger-card__header">
            <div class="passenger-card__info">
              <svg
                width="28"
                height="28"
                viewBox="0 0 28 28"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <g clip-path="url(#clip0_21606_71173)">
                  <path
                    d="M13.9998 7.87503C16.4841 7.87503 18.4998 6.11136 18.4998 3.93753C18.4998 1.7637 16.4873 0 13.9998 0C11.5123 0 9.44351 1.7637 9.44351 3.93753C9.44351 6.11136 11.5185 7.87503 13.9998 7.87503ZM25.9435 6.0594C25.0754 5.12206 23.5023 4.97659 22.4279 5.73642L19.2248 8.00628C16.2773 10.0948 11.7185 10.0948 8.76851 8.00628L5.57289 5.73675C4.50101 4.97659 2.92476 5.12261 2.05726 6.0594C1.18851 7.00003 1.35351 8.37816 2.42664 9.13831L5.62601 11.4062C6.35914 11.9255 7.16539 12.3446 7.99976 12.7143L7.94351 26.2501C7.94351 27.2136 8.84226 28.0001 9.94351 28.0001H10.8873C11.9885 28.0001 12.8873 27.2136 12.8873 26.2501V20.125H14.8873V26.2501C14.8873 27.2136 15.786 28.0001 16.8873 28.0001H17.831C18.9323 28.0001 19.831 27.2136 19.831 26.2501L19.831 12.7149C20.6654 12.3454 21.4716 11.9263 22.2041 11.4073L25.4041 9.13941C26.6435 8.37816 26.756 7.00003 25.9435 6.0594Z"
                    fill="#2F353A"
                  />
                </g>
                <defs>
                  <clipPath id="clip0_21606_71173">
                    <rect width="28" height="28" fill="white" />
                  </clipPath>
                </defs>
              </svg>

              <div>
                <span class="passenger-card__type">Infante</span>
                <span class="passenger-card__age">2 - 12 años</span>
              </div>
            </div>
            <div class="passenger-card__discount">
              <div class="discount-toggle-row">
                <span class="discount-label">% descuento</span>
                <a-switch v-model:checked="formState.infanteDiscount" size="small" />
              </div>
              <div v-if="formState.infanteDiscount" class="discount-input-row">
                <a-input-number
                  v-model:value="formState.infanteDiscountPercent"
                  :min="0"
                  :max="100"
                  :controls="false"
                  size="large"
                  :formatter="(value: number) => `% ${value}`"
                  :parser="(value: string) => value.replace(/\%\s?/g, '')"
                  class="discount-percent-input"
                />
              </div>
            </div>
          </div>
          <div class="passenger-card__body">
            <div v-if="formState.infanteDiscount" class="net-rate-row">
              <span class="passenger-card__label" style="margin-bottom: 0">Tarifa neta ($)</span>
              <span class="net-rate-value">{{ formatNumber(effectiveInfanteRate) }}</span>
            </div>
            <template v-else>
              <label class="passenger-card__label">Tarifa neta ($)</label>
              <a-input-number
                v-model:value="formState.infanteRate"
                :min="0"
                :precision="2"
                :controls="false"
                size="large"
                class="full-width"
              />
            </template>
            <div class="passenger-card__breakdown">
              <div class="breakdown-row">
                <span>Servicios ({{ servicePercent }}%)</span>
                <span>{{ formatNumber(infanteServices) }}</span>
              </div>
              <div class="breakdown-row">
                <span>IGV ({{ igvPercent }}%)</span>
                <span>{{ formatNumber(infanteIGV) }}</span>
              </div>
              <div class="breakdown-row breakdown-row--total">
                <span>Total</span>
                <span>{{ formatNumber(infanteTotal) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Section 5b: Rangos mode -->
      <div v-if="formState.tariffInputMode === 'rangos'" class="content-card">
        <div class="rangos-header">
          <span class="rangos-title">Rango de pasajeros</span>
          <div class="rangos-discount-toggle">
            <span class="discount-label">Descuento general (%)</span>
            <a-switch v-model:checked="formState.rangosGeneralDiscount" size="small" />
            <a-input-number
              v-if="formState.rangosGeneralDiscount"
              v-model:value="formState.rangosGeneralDiscountPercent"
              :min="0"
              :max="100"
              :controls="false"
              size="large"
              :formatter="(value: number) => `% ${value}`"
              :parser="(value: string) => value.replace(/\%\s?/g, '')"
              class="discount-percent-input"
            />
          </div>
        </div>

        <!-- Table header -->
        <div class="rangos-table-header">
          <span class="rangos-col rangos-col--rango">Rango</span>
          <span class="rangos-col rangos-col--tipo">Tipo</span>
          <span class="rangos-col rangos-col--descuento">Descuento %</span>
          <span class="rangos-col rangos-col--tarifa">Tarifa neta ($)</span>
          <span class="rangos-col rangos-col--servicios">Servicios ({{ servicePercent }}%)</span>
          <span class="rangos-col rangos-col--igv">IGV ({{ igvPercent }}%)</span>
          <span class="rangos-col rangos-col--total">TOTAL</span>
          <span class="rangos-col rangos-col--actions"></span>
        </div>

        <!-- Range groups -->
        <div
          v-for="(range, rangeIdx) in formState.rangos"
          :key="rangeIdx"
          class="rangos-group"
          :class="{ 'rangos-group--border': rangeIdx > 0 }"
        >
          <!-- Adulto row -->
          <div class="rangos-row">
            <div class="rangos-col rangos-col--rango">
              <a-input-number
                v-model:value="range.maxPax"
                :min="1"
                :controls="false"
                size="large"
                class="rangos-input-small"
              />
            </div>
            <div class="rangos-col rangos-col--tipo">Adulto</div>
            <div class="rangos-col rangos-col--descuento"></div>
            <div class="rangos-col rangos-col--tarifa">
              <a-input-number
                v-model:value="range.adultoRate"
                :min="0"
                :precision="2"
                :controls="false"
                size="large"
                class="rangos-input-tarifa"
              />
            </div>
            <div class="rangos-col rangos-col--servicios">
              {{ formatNumber(((range.adultoRate || 0) * servicePercent) / 100) }}
            </div>
            <div class="rangos-col rangos-col--igv">
              {{ formatNumber(((range.adultoRate || 0) * igvPercent) / 100) }}
            </div>
            <div class="rangos-col rangos-col--total rangos-col--pax-total">
              <span class="rangos-pax-label">{{ getRangeLabel(rangeIdx) }}</span>
              <strong>USD {{ formatNumber(getRangeAdultoTotal(rangeIdx)) }}</strong>
            </div>
            <div class="rangos-col rangos-col--actions">
              <div class="rangos-actions">
                <div class="action-icon" tabindex="-1" @click="addRange">
                  <svg
                    width="24"
                    height="24"
                    viewBox="0 0 24 24"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                  >
                    <path
                      d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z"
                      stroke="#1284ED"
                      stroke-width="2"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                    />
                    <path
                      d="M12 8V16"
                      stroke="#1284ED"
                      stroke-width="2"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                    />
                    <path
                      d="M8 12H16"
                      stroke="#1284ED"
                      stroke-width="2"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                    />
                  </svg>
                </div>
                <div
                  v-if="formState.rangos.length > 1"
                  class="action-icon"
                  tabindex="-1"
                  @click="removeRange(rangeIdx)"
                >
                  <font-awesome-icon
                    icon="fa-regular fa-trash-can"
                    style="font-size: 20px; color: #1284ed"
                  />
                </div>
              </div>
            </div>
          </div>

          <!-- Niño row -->
          <div class="rangos-row">
            <div class="rangos-col rangos-col--rango"></div>
            <div class="rangos-col rangos-col--tipo">Niño</div>
            <div class="rangos-col rangos-col--descuento">
              <a-switch v-model:checked="range.ninoDiscount" size="small" />
            </div>
            <div class="rangos-col rangos-col--tarifa">
              <a-input-number
                v-model:value="range.ninoRate"
                :min="0"
                :precision="2"
                :controls="false"
                size="large"
                class="rangos-input-tarifa"
              />
            </div>
            <div class="rangos-col rangos-col--servicios">
              {{ formatNumber(((range.ninoRate || 0) * servicePercent) / 100) }}
            </div>
            <div class="rangos-col rangos-col--igv">
              {{ formatNumber(((range.ninoRate || 0) * igvPercent) / 100) }}
            </div>
            <div class="rangos-col rangos-col--total">
              <strong>USD {{ formatNumber(getRangeNinoTotal(rangeIdx)) }}</strong>
            </div>
            <div class="rangos-col rangos-col--actions"></div>
          </div>

          <!-- Infante row -->
          <div class="rangos-row">
            <div class="rangos-col rangos-col--rango"></div>
            <div class="rangos-col rangos-col--tipo">Infante</div>
            <div class="rangos-col rangos-col--descuento">
              <a-switch v-model:checked="range.infanteDiscount" size="small" />
            </div>
            <div class="rangos-col rangos-col--tarifa">
              <a-input-number
                v-model:value="range.infanteRate"
                :min="0"
                :precision="2"
                :controls="false"
                size="large"
                class="rangos-input-tarifa"
              />
            </div>
            <div class="rangos-col rangos-col--servicios">
              {{ formatNumber(((range.infanteRate || 0) * servicePercent) / 100) }}
            </div>
            <div class="rangos-col rangos-col--igv">
              {{ formatNumber(((range.infanteRate || 0) * igvPercent) / 100) }}
            </div>
            <div class="rangos-col rangos-col--total">
              <strong>USD {{ formatNumber(getRangeInfanteTotal(rangeIdx)) }}</strong>
            </div>
            <div class="rangos-col rangos-col--actions"></div>
          </div>
        </div>
      </div>
    </a-form>
  </div>
</template>

<script setup lang="ts">
  import { computed, ref, reactive } from 'vue';
  import { usePricingPlansStore } from '@/modules/negotiations/products/configuration/stores/usePricingPlansStore';

  defineOptions({
    name: 'AmountsForm',
  });

  const store = usePricingPlansStore();
  const formState = store.amounts;
  const { saveAmounts, loadAmounts } = store;

  const changePeriod = (index: number) => {
    if (index === formState.selectedPeriod) return;
    saveAmounts(formState.selectedPeriod);
    loadAmounts(index);
  };

  // Percentages from Step 2
  const servicePercent = computed(() => store.taxAndStaff.servicePercentage || 0);
  const igvPercent = computed(() => (store.taxAndStaff.affectedByIGV ? 18 : 0));

  // Period cards data from Step 1
  const periodCards = computed(() => store.formattedPeriods);

  const tariffStatusOptions = [
    { value: 'confirmada', label: 'Confirmada' },
    { value: 'protegida', label: 'Protegida' },
    { value: 'cerrada', label: 'Cerrada' },
    { value: 'finalizada', label: 'Finalizada' },
    { value: 'dinamica', label: 'Dinámica' },
  ];

  const associatedPolicies = [
    { name: 'Política general', passengers: '1 - 99 pasajeros' },
    { name: 'Política Fits: Travel Ja Vu, otro cliente', passengers: '1 - 99 pasajeros' },
  ];

  // Computed: Adulto
  const adultoServices = computed(() => ((formState.adultoRate || 0) * servicePercent.value) / 100);
  const adultoIGV = computed(() => ((formState.adultoRate || 0) * igvPercent.value) / 100);
  const adultoTotal = computed(
    () => (formState.adultoRate || 0) + adultoServices.value + adultoIGV.value
  );

  // Computed: Niño
  const effectiveNinoRate = computed(() => {
    if (formState.ninoDiscount) {
      const base = formState.adultoRate || 0;
      return base - (base * formState.ninoDiscountPercent) / 100;
    }
    return formState.ninoRate || 0;
  });
  const ninoServices = computed(() => (effectiveNinoRate.value * servicePercent.value) / 100);
  const ninoIGV = computed(() => (effectiveNinoRate.value * igvPercent.value) / 100);
  const ninoTotal = computed(() => effectiveNinoRate.value + ninoServices.value + ninoIGV.value);

  // Computed: Infante
  const effectiveInfanteRate = computed(() => {
    if (formState.infanteDiscount) {
      const base = formState.adultoRate || 0;
      return base - (base * formState.infanteDiscountPercent) / 100;
    }
    return formState.infanteRate || 0;
  });
  const infanteServices = computed(() => (effectiveInfanteRate.value * servicePercent.value) / 100);
  const infanteIGV = computed(() => (effectiveInfanteRate.value * igvPercent.value) / 100);
  const infanteTotal = computed(
    () => effectiveInfanteRate.value + infanteServices.value + infanteIGV.value
  );

  const formatNumber = (value: number) => value.toFixed(2);

  // Rangos helpers
  const addRange = () => {
    formState.rangos.push({
      maxPax: null,
      adultoRate: null,
      ninoRate: null,
      ninoDiscount: false,
      infanteRate: null,
      infanteDiscount: false,
      infanteDiscountPercent: 0,
    });
  };

  const removeRange = (index: number) => {
    if (formState.rangos.length > 1) {
      formState.rangos.splice(index, 1);
    }
  };

  const getRangeLabel = (rangeIdx: number) => {
    const prevMax = rangeIdx > 0 ? (formState.rangos[rangeIdx - 1].maxPax || 0) + 1 : 1;
    const curMax = formState.rangos[rangeIdx].maxPax || prevMax;
    return `${prevMax} - ${curMax} pax`;
  };

  const getRangeAdultoTotal = (rangeIdx: number) => {
    const rate = formState.rangos[rangeIdx].adultoRate || 0;
    return rate + (rate * servicePercent.value) / 100 + (rate * igvPercent.value) / 100;
  };

  const getRangeNinoTotal = (rangeIdx: number) => {
    const rate = formState.rangos[rangeIdx].ninoRate || 0;
    return rate + (rate * servicePercent.value) / 100 + (rate * igvPercent.value) / 100;
  };

  const getRangeInfanteTotal = (rangeIdx: number) => {
    const rate = formState.rangos[rangeIdx].infanteRate || 0;
    return rate + (rate * servicePercent.value) / 100 + (rate * igvPercent.value) / 100;
  };

  // Policy change drawer
  const showPolicyDrawer = ref(false);
  const policyDrawerState = reactive({
    currentPolicy: null as string | null,
    newPolicy: null as string | null,
  });

  const policyDrawerOptions = [
    { value: 'politica_general', name: 'Política General', passengers: '1 - 99 pasajeros' },
    {
      value: 'politica_grupos_canada',
      name: 'Política Grupos: Canadá',
      passengers: '1 - 99 pasajeros',
    },
    { value: 'politica_fits', name: 'Política Fits', passengers: '1 - 15 pasajeros' },
  ];

  const policyDetailsMap: Record<string, { color: string; label: string; value: string }[]> = {
    politica_general: [
      {
        color: '#1284ED',
        label: 'Condición de pago:',
        value: 'Prepago a 15 días antes del servicio',
      },
      {
        color: '#E53935',
        label: 'Cancelación',
        value: '15 días antes del servicio, <strong>penalidad 100%</strong>',
      },
      {
        color: '#2f353a',
        label: 'Reconfirmación:',
        value: 'Confirmación a 15 días antes del servicio',
      },
      {
        color: '#43A047',
        label: 'Liberados:',
        value: 'Por cada 20 pasajeros, <strong style="color:#43A047">2 liberados</strong>',
      },
      { color: '#FFCC00', label: 'Edades:', value: 'Niños + 3 años  -  Infantes: 0 a 2 años' },
    ],
    politica_grupos_canada: [
      {
        color: '#1284ED',
        label: 'Condición de pago:',
        value: 'Prepago a 15 días antes del servicio',
      },
      {
        color: '#E53935',
        label: 'Cancelación',
        value: '15 días antes del servicio, <strong>penalidad 100%</strong>',
      },
      {
        color: '#2f353a',
        label: 'Reconfirmación:',
        value: 'Confirmación a 15 días antes del servicio',
      },
      {
        color: '#43A047',
        label: 'Liberados:',
        value: 'Por cada 20 pasajeros, <strong style="color:#43A047">2 liberados</strong>',
      },
      { color: '#FFCC00', label: 'Edades:', value: 'Niños + 3 años  -  Infantes: 0 a 2 años' },
    ],
    politica_fits: [
      {
        color: '#1284ED',
        label: 'Condición de pago:',
        value: 'Prepago a 15 días antes del servicio',
      },
      {
        color: '#E53935',
        label: 'Cancelación',
        value: '15 días antes del servicio, <strong>penalidad 100%</strong>',
      },
      {
        color: '#2f353a',
        label: 'Reconfirmación:',
        value: 'Confirmación a 15 días antes del servicio',
      },
      {
        color: '#43A047',
        label: 'Liberados:',
        value: 'Por cada 20 pasajeros, <strong style="color:#43A047">2 liberados</strong>',
      },
      { color: '#FFCC00', label: 'Edades:', value: 'Niños + 3 años  -  Infantes: 0 a 2 años' },
    ],
  };

  const currentPolicyDetails = computed(() => {
    if (!policyDrawerState.currentPolicy) return null;
    return policyDetailsMap[policyDrawerState.currentPolicy] || null;
  });

  const newPolicyDetails = computed(() => {
    if (!policyDrawerState.newPolicy) return null;
    return policyDetailsMap[policyDrawerState.newPolicy] || null;
  });

  const openPolicyDrawer = () => {
    showPolicyDrawer.value = true;
  };

  const closePolicyDrawer = () => {
    showPolicyDrawer.value = false;
    policyDrawerState.currentPolicy = null;
    policyDrawerState.newPolicy = null;
  };

  const handleChangePolicy = () => {
    console.log(
      'Changing policy:',
      policyDrawerState.currentPolicy,
      '->',
      policyDrawerState.newPolicy
    );
    closePolicyDrawer();
  };
</script>

<style lang="scss" scoped>
  .content-card {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 24px;
    margin-bottom: 24px;
  }

  .bg-color-custom {
    background-color: #f9f9f9;
  }

  .section-instruction {
    font-family: 'Inter', sans-serif;
    font-weight: 600;
    font-size: 14px;
    color: #2f353a;
  }

  .section-instruction-hint {
    font-family: 'Inter', sans-serif;
    font-weight: 400;
    font-size: 13px;
    color: #9e9e9e;
  }

  /* Period Cards */
  .period-cards-row {
    display: flex;
    gap: 8px;
    overflow-x: auto;
  }

  .period-card {
    flex: 1;
    min-width: 0;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 12px 10px 12px 16px;
    cursor: pointer;
    transition: border-color 0.2s;

    &--selected {
      border-color: #c63838;
    }

    &__header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 8px;

      .ant-radio-wrapper {
        margin-right: 0;
      }
    }

    &__name {
      font-family: 'Inter', sans-serif;
      font-weight: 500;
      font-size: 14px;
      color: #2f353a;
    }

    &__dates {
      font-family: 'Inter', sans-serif;
      font-weight: 400;
      font-size: 12px;
      color: #7e8285;
      display: flex;
      align-items: center;
      gap: 6px;
    }

    &__icon {
      font-size: 12px;
      color: #7e8285;
    }
  }

  /* Required asterisk */
  .required {
    color: #c63838;
  }

  /* Policies Section */
  .policies-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
  }

  .policies-title {
    font-family: 'Inter', sans-serif;
    font-weight: 600;
    font-size: 14px;
    color: #2f353a;
  }

  .policies-refresh {
    cursor: pointer;
  }

  .policies-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
  }

  .policy-tag {
    background: #ffffff;
    border: 1px solid #e8e8e8;
    border-radius: 8px;
    padding: 8px 16px;
    font-family: 'Inter', sans-serif;
    font-size: 13px;
    display: flex;
    align-items: center;
    gap: 8px;

    &__icon {
      color: #575b5f;
      font-size: 14px;
    }

    &__name {
      font-weight: 500;
      color: #2f353a;
    }

    &__separator {
      color: #9e9e9e;
    }

    &__users-icon {
      color: #9e9e9e;
      font-size: 13px;
    }

    &__passengers {
      font-weight: 400;
      color: #9e9e9e;
    }
  }

  /* Tariff Input Row */
  .tariff-input-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 24px;

    .ant-radio-group {
      display: inline-flex;
      gap: 16px;
    }
  }

  .flex-center {
    display: flex;
    align-items: center;
  }

  .field-section-label {
    font-family: 'Inter', sans-serif;
    font-weight: 600;
    font-size: 14px;
    color: #2f353a;
    margin-right: 16px;
  }

  /* Rangos Mode */
  .rangos-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
  }

  .rangos-title {
    font-family: 'Inter', sans-serif;
    font-weight: 600;
    font-size: 14px;
    color: #2f353a;
  }

  .rangos-discount-toggle {
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .rangos-table-header {
    display: flex;
    align-items: center;
    padding-bottom: 12px;
    border-bottom: 1px solid #e8e8e8;
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    font-size: 12px;
    color: #7e8285;
  }

  .rangos-group {
    &--border {
      border-top: 1px solid #e8e8e8;
      padding-top: 16px;
      margin-top: 16px;
    }
  }

  .rangos-row {
    display: flex;
    align-items: center;
    min-height: 48px;
    padding: 6px 0;
  }

  .rangos-col {
    font-family: 'Inter', sans-serif;
    font-size: 13px;
    color: #2f353a;

    &--rango {
      width: 80px;
      flex-shrink: 0;
    }

    &--tipo {
      width: 80px;
      flex-shrink: 0;
      font-weight: 500;
    }

    &--descuento {
      width: 100px;
      flex-shrink: 0;
    }

    &--tarifa {
      width: 140px;
      flex-shrink: 0;
    }

    &--servicios {
      width: 100px;
      flex-shrink: 0;
      text-align: right;
      color: #575b5f;
    }

    &--igv {
      width: 80px;
      flex-shrink: 0;
      text-align: right;
      color: #575b5f;
    }

    &--total {
      flex: 1;
      text-align: right;
      font-weight: 600;
    }

    &--pax-total {
      display: flex;
      align-items: center;
      justify-content: flex-end;
      gap: 12px;
    }

    &--actions {
      width: 70px;
      flex-shrink: 0;
      display: flex;
      justify-content: flex-end;
    }
  }

  .rangos-pax-label {
    font-weight: 400;
    font-size: 12px;
    color: #7e8285;
    white-space: nowrap;
  }

  .rangos-input-small {
    width: 64px;

    .ant-input-number-input {
      text-align: center;
    }
  }

  .rangos-input-tarifa {
    width: 120px;
  }

  .rangos-actions {
    display: flex;
    align-items: center;
    gap: 8px;
  }

  /* Passenger Cards */
  .passenger-cards-row {
    display: flex;
    gap: 16px;
    margin-bottom: 24px;
  }

  .passenger-card {
    flex: 1;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 20px;
    min-width: 0;

    &__header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 16px;
    }

    &__info {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    &__icon {
      font-size: 20px;
      width: 32px;
      height: 32px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 50%;

      &--adulto {
        color: #2f353a;
      }

      &--nino {
        color: #2f353a;
      }

      &--infante {
        color: #c63838;
      }
    }

    &__type {
      font-family: 'Inter', sans-serif;
      font-weight: 600;
      font-size: 16px;
      color: #2f353a;
      display: block;
    }

    &__age {
      font-family: 'Inter', sans-serif;
      font-weight: 400;
      font-size: 12px;
      color: #7e8285;
      display: block;
    }

    &__discount {
      display: flex;
      flex-direction: column;
      align-items: flex-end;
    }

    .discount-toggle-row {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .discount-input-row {
      margin-top: 4px;
    }

    &__label {
      font-family: 'Inter', sans-serif;
      font-weight: 500;
      font-size: 13px;
      color: #2f353a;
      margin-bottom: 6px;
      display: block;
    }

    &__body {
      .ant-input-number {
        width: 100%;
      }
    }

    &__breakdown {
      margin-top: 16px;
    }

    .net-rate-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .net-rate-value {
      font-weight: 600;
      font-size: 16px;
      color: #2f353a;
    }
  }

  .discount-label {
    font-family: 'Inter', sans-serif;
    font-weight: 400;
    font-size: 12px;
    color: #7e8285;
  }

  .discount-percent-input {
    width: 70px;
  }

  .breakdown-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 4px 0;
    font-family: 'Inter', sans-serif;
    font-size: 13px;
    color: #7e8285;

    &--total {
      font-weight: 600;
      color: #2f353a;
      border-top: 1px solid #e0e0e0;
      margin-top: 4px;
      padding-top: 8px;
    }
  }

  .full-width {
    width: 100%;
  }
</style>

<style lang="scss">
  /* Global: Switch color override for Amounts form */
  .service-pricing-plans-form-container {
    .passenger-card {
      .ant-switch-checked {
        background-color: #c63838 !important;
      }
    }
  }

  /* Policy change drawer styles */
  .policy-change-drawer {
    .ant-drawer-header {
      flex-direction: row-reverse;

      .ant-drawer-header-title {
        flex-direction: row-reverse;
      }
    }

    .ant-drawer-body {
      padding-top: 0;
    }
    .drawer-title-block {
      .drawer-title {
        font-family: 'Inter', sans-serif;
        font-weight: 600;
        font-size: 18px;
        color: #2f353a;
      }

      .drawer-subtitle {
        font-family: 'Inter', sans-serif;
        font-weight: 400;
        font-size: 14px;
        color: #9e9e9e;
        margin-top: 4px;
      }
    }

    .drawer-alert {
      margin-bottom: 8px;
    }

    .drawer-field-label {
      font-family: 'Inter', sans-serif;
      font-weight: 500;
      font-size: 14px;
      color: #2f353a;
      display: block;
      margin-bottom: 8px;
    }

    .drawer-footer {
      display: flex;
      justify-content: flex-end;
      gap: 16px;
      margin-top: 32px;
      margin-left: -24px;
      margin-right: -24px;
      padding: 16px 24px 0;
      border-top: 1px solid #e8e8e8;

      .button-cancel-white {
        font-size: 16px !important;
        height: 48px;
        border-radius: 5px;
        background: #fff;
        color: #2f353a;
        border: 1px solid #2f353a;
        box-shadow: none !important;
        min-width: 140px;

        &:hover {
          background: #f5f5f5;
          border: 1px solid #2f353a !important;
        }
      }

      .button-action-primary-strong {
        font-size: 16px !important;
        height: 48px;
        border-radius: 5px;
        border-width: 1px;
        color: #fff;
        background: #1284ed;
        border-color: #1284ed !important;
        min-width: 140px;

        &:hover {
          background: #0d6ecc;
          border-color: #0d6ecc !important;
        }

        &:disabled {
          background: #e0e0e0;
          border-color: #e0e0e0 !important;
          color: #fff;
        }
      }
    }
  }

  .policy-option-name {
    font-weight: 500;
    color: #2f353a;
  }

  .policy-option-detail {
    font-weight: 400;
    color: #9e9e9e;
  }

  .policy-change-drawer {
    .policy-details {
      margin-top: 16px;
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    .policy-detail-row {
      display: flex;
      align-items: flex-start;
      gap: 8px;
      font-family: 'Inter', sans-serif;
      font-size: 13px;
      line-height: 20px;
    }

    .policy-detail-dot {
      width: 8px;
      height: 8px;
      min-width: 8px;
      border-radius: 50%;
      margin-top: 6px;
    }

    .policy-detail-label {
      font-weight: 600;
      color: #2f353a;
      white-space: nowrap;
    }

    .policy-detail-value {
      font-weight: 400;
      color: #575b5f;
    }

    .button-action-danger {
      font-size: 16px !important;
      height: 48px;
      border-radius: 5px;
      border-width: 1px;
      color: #fff;
      background: #bd0d12;
      border-color: #bd0d12 !important;
      min-width: 140px;

      &:hover {
        color: #fff !important;
        background: #9b0b0f;
        border-color: #9b0b0f !important;
      }
    }
  }
</style>
