<template>
  <div class="period-row">
    <!-- Tipo solo se muestra en el primer rango -->
    <div class="col-type" v-if="rangeIndex === 0">
      <a-form-item>
        <template #label>
          <span class="field-section-label">Tipo de periodo: <span class="required">*</span></span>
        </template>
        <a-select v-model:value="group.periodId" size="large" :options="periodTypeOptions" />
      </a-form-item>
    </div>

    <!-- Espaciador cuando no es el primero -->
    <div class="col-type" v-else></div>

    <!-- Desde -->
    <div class="col-date">
      <a-form-item>
        <template #label>
          <span class="field-section-label">Desde:</span>
        </template>
        <a-date-picker
          v-model:value="range.dateFrom"
          size="large"
          format="DD/MM/YYYY"
          class="full-width"
          :disabled-date="getDisabledDate(groupIndex, rangeIndex, 'from')"
        />
      </a-form-item>
    </div>

    <!-- Hasta -->
    <div class="col-date">
      <a-form-item>
        <template #label>
          <span class="field-section-label">Hasta:</span>
        </template>
        <a-date-picker
          v-model:value="range.dateTo"
          size="large"
          format="DD/MM/YYYY"
          class="full-width"
          :disabled-date="getDisabledDate(groupIndex, rangeIndex, 'to')"
        />
      </a-form-item>
    </div>

    <!-- Acciones -->
    <div class="col-actions">
      <div @click="$emit('addRange')">
        <PlusCircleIcon :color="color" />
      </div>

      <div v-if="totalRanges > 1" @click="$emit('removeRange', rangeIndex)">
        <TrashIcon :color="color" />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
  import {
    PlusCircleIcon,
    TrashIcon,
  } from '@/modules/negotiations/products/configuration/content/pricingPlans/icons';

  defineProps<{
    range: any;
    rangeIndex: number;
    group: any;
    groupIndex: number;
    totalRanges: number;
    periodTypeOptions: any;
    getDisabledDate: any;
  }>();

  defineEmits(['addRange', 'removeRange']);

  const color = '#1284ED';
</script>

<style scoped>
  .period-row {
    display: flex;
    align-items: center;
    gap: 16px;
  }

  .col-type {
    width: 240px;
  }

  .col-date {
    width: 220px;
  }

  .col-actions {
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .icon-btn {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #e6f4ff;
    color: #1677ff;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-weight: bold;
  }

  .icon-btn.delete {
    background: #f0f0f0;
  }

  .full-width {
    width: 100%;
  }

  .field-section-label {
    font-size: 14px;
    font-weight: 500;
    color: #2f353a;
  }

  .required {
    color: #ff4d4f;
  }
</style>
