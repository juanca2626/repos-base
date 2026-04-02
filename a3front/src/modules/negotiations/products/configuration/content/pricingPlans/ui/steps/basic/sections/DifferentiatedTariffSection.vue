<template>
  <div class="differentiated-wrapper">
    <!-- Header -->
    <div class="header-row" :class="{ 'has-margin': formState.differentiatedTariff }">
      <span class="label-text">Días con tarifa diferenciada:</span>

      <a-radio-group v-model:value="formState.differentiatedTariff">
        <a-radio :value="true">Sí</a-radio>
        <a-radio :value="false">No</a-radio>
      </a-radio-group>
    </div>

    <!-- Content -->
    <div v-if="formState.differentiatedTariff" class="content">
      <!-- Selector días -->
      <div class="day-selector-row">
        <span class="label-text">Selecciona los días:</span>

        <div class="day-toggles">
          <span
            v-for="day in daysOptions"
            :key="day.value"
            class="day-circle"
            :class="{ selected: formState.selectedDays.includes(day.value) }"
            @click="toggleDay(day.value)"
          >
            {{ day.label }}
          </span>
        </div>

        <span class="help-text"> Marque los días que tendrán precio diferente al estándar </span>
      </div>

      <!-- Tarifa Preview -->
      <div class="tariff-preview">
        <!-- Caso: Sin selección -->
        <div v-if="formState.selectedDays.length === 0" class="tariff-row default">
          <div class="tariff-left">
            <CalendarCheck class="icon" />
            <span class="title">Tarifa</span>
            <span class="subtitle">Todos los días</span>
          </div>

          <div class="days-list">
            <span v-for="day in daysOptions" :key="day.value">
              {{ day.name }}
            </span>
          </div>
        </div>

        <!-- Caso: Con selección -->
        <div v-else class="tariff-grid">
          <!-- Seleccionados -->
          <div class="tariff-card selected">
            <div class="card-header">
              <div class="left">
                <CalendarCheck class="icon" />
                <span class="title">Tarifa</span>
                <span class="subtitle">
                  {{ unselectedLabel }}
                </span>
              </div>

              <span class="close-btn" @click="clearSelection">✕</span>
            </div>

            <div class="days-list">
              <span v-for="day in selectedFullNames" :key="day.value">
                {{ day.name }}
              </span>
            </div>
          </div>

          <!-- No seleccionados -->
          <div class="tariff-card secondary" v-if="unselectedFullNames.length > 0">
            <div class="card-header">
              <div class="left">
                <CalendarCheck class="icon" />
                <span class="title">Tarifa</span>
                <span class="subtitle">Días de semana</span>
              </div>
            </div>

            <div class="days-list">
              <span v-for="day in unselectedFullNames" :key="day.value">
                {{ day.name }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
  import { computed } from 'vue';
  import { resetBasicStateAfterSelectedDaysChange } from '@/modules/negotiations/products/configuration/content/pricingPlans/state/princingPlanStateActions';
  import CalendarCheck from '@/modules/negotiations/products/configuration/content/pricingPlans/icons/CalendarCheck.vue';

  const props = defineProps<{
    model: any;
    errors: any;
    daysOptions: any;
  }>();

  const formState = props.model;

  formState.standardDays = props.daysOptions.map((day: any) => day.value);

  const toggleDay = (dayValue: string) => {
    const index = formState.selectedDays.indexOf(dayValue);

    if (index > -1) {
      formState.selectedDays.splice(index, 1);
      formState.standardDays.push(dayValue);
    } else {
      formState.selectedDays.push(dayValue);
      formState.standardDays.splice(formState.standardDays.indexOf(dayValue), 1);
    }
  };

  const selectedFullNames = computed(() => {
    return formState.selectedDays.map((d: string) => {
      const day = props.daysOptions.find((day: any) => day.value === d);
      return day;
    });
  });

  const unselectedFullNames = computed(() =>
    props.daysOptions.filter((d: any) => !formState.selectedDays.includes(d.value))
  );

  const selectedLabel = computed(() => {
    const days = formState.selectedDays;

    if (days.includes('SAT') || days.includes('SUN')) {
      return 'Fin de semana';
    }

    if (['MON', 'TUE', 'WED', 'THU', 'FRI'].some((d) => days.includes(d))) {
      return 'Días de semana';
    }

    return 'Personalizada';
  });

  const unselectedLabel = computed(() => {
    return selectedLabel.value;
  });

  const clearSelection = () => {
    resetBasicStateAfterSelectedDaysChange(formState);
  };
</script>

<style scoped>
  .differentiated-wrapper {
    background: #f9f9f9;
    padding: 16px;
    border-radius: 8px;
  }

  .header-row {
    display: flex;
    align-items: center;
    gap: 16px;
  }

  .header-row.has-margin {
    margin-bottom: 16px;
  }

  .day-selector-row {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 16px;
  }

  .day-circle {
    width: 24.43px;
    height: 22px;
    border-radius: 10px;
    border: 1px solid #babcbd;
    background: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    text-transform: center;
  }

  .day-circle.selected {
    background: #1284ed;
    color: white;
    border-color: #1284ed;
  }

  .tariff-tabs-row {
    display: flex;
    gap: 16px;
  }

  .tariff-tab {
    padding: 12px;
    border-radius: 8px;
    background: white;
    cursor: pointer;
    border: 1px solid #e5e5e5;
  }

  .tariff-tab.active {
    border-color: #1284ed;
  }

  .help-text {
    color: #8c8c8c;
    font-size: 12px;
  }

  .day-toggles {
    display: flex;
    gap: 8px;
  }

  .tariff-preview {
    margin-top: 16px;
  }

  .tariff-row.default {
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .tariff-left {
    position: relative;
    display: flex;
    align-items: center;
  }

  .tariff-grid {
    display: flex;
    gap: 16px;
  }

  .tariff-card {
    padding: 12px;
    border-radius: 8px;
  }

  .tariff-card.selected {
    background: #ffffff;
    border: 2px solid #1284ed;
  }

  .card-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 20px;
  }

  .card-header .left {
    display: flex;
    align-items: center;
  }

  .days-list span {
    background: #ffffff;
    padding: 4px 8px;
    border-radius: 8px;
    margin-right: 6px;
    font-size: 14px;
    font-weight: 500;
    border: 1px solid #f2f2f2;
  }

  .subtitle {
    color: #8c8c8c;
    margin-left: 6px;
  }

  .close-btn {
    margin-left: 12px;
    cursor: pointer;
  }

  .label-text {
    font-size: 14px;
    font-weight: 500;
    color: #2f353a;
  }
</style>
