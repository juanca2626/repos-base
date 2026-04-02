<template>
  <div class="form-container">
    <div class="form-group">
      <label class="form-label">Vigencia operativa <span class="required">*</span></label>
      <div class="date-pickers-row">
        <div class="date-picker-item">
          <span class="field-label">Desde:</span>
          <a-date-picker
            v-model:value="form.dateFrom"
            placeholder="DD/MM/AAAA"
            class="custom-date-picker"
            format="DD/MM/YYYY"
            popup-class-name="custom-datepicker-dropdown"
            :locale="datePickerLocale"
          >
            <template #suffixIcon><CalendarIcon :active="!!form.dateFrom" /></template>
          </a-date-picker>
        </div>
        <div class="date-picker-item">
          <span class="field-label">Hasta:</span>
          <a-date-picker
            v-model:value="form.dateTo"
            placeholder="DD/MM/AAAA"
            class="custom-date-picker"
            format="DD/MM/YYYY"
            popup-class-name="custom-datepicker-dropdown"
            :locale="datePickerLocale"
          >
            <template #suffixIcon><CalendarIcon :active="!!form.dateTo" /></template>
          </a-date-picker>
        </div>
      </div>
    </div>

    <div class="form-group">
      <label class="form-label">Ciudad <span class="required">*</span></label>
      <a-select
        v-model:value="form.city"
        placeholder="Selecciona"
        class="custom-select"
        :options="cityOptions"
        :bordered="false"
        popup-class-name="custom-select-dropdown"
        allow-clear
      >
        <template #suffixIcon><SelectChevronIcon /></template>
        <template #clearIcon><ClearIcon /></template>
      </a-select>
    </div>

    <div class="form-group">
      <div class="label-with-icon">
        <label class="form-label">Nombre configuración: <span class="required">*</span></label>
        <a-tooltip
          placement="top"
          :align="{ offset: [-70, 0] }"
          :overlay-inner-style="{
            background: '#212121',
            color: 'white',
            width: '380px',
            height: '34px',
            display: 'flex',
            alignItems: 'center',
            padding: '0 12px',
            borderRadius: '4px',
            fontSize: '12px',
            justifyContent: 'center',
          }"
        >
          <template #title>
            <span>Se completa automáticamente al seleccionar la configuración.</span>
          </template>
          <InfoCircleIcon style="cursor: pointer" />
        </a-tooltip>
      </div>
      <a-input disabled class="custom-input-disabled" :value="configName" />
    </div>

    <div class="form-group">
      <label class="form-label">Segmentación <span class="required">*</span></label>
      <a-select
        v-model:value="form.segmentation"
        placeholder="Selecciona"
        class="custom-select"
        :options="segmentationOptions"
        :bordered="false"
        popup-class-name="custom-select-dropdown"
        allow-clear
      >
        <template #suffixIcon><SelectChevronIcon /></template>
        <template #clearIcon><ClearIcon /></template>
      </a-select>
    </div>

    <div v-if="form.segmentation === 'cliente'" class="form-group anim-fade-in">
      <label class="form-label">Especificación clientes <span class="required">*</span></label>
      <a-select
        v-model:value="form.client"
        placeholder="Selecciona"
        class="custom-select"
        :options="clientOptions"
        :bordered="false"
        popup-class-name="custom-select-dropdown"
        allow-clear
      >
        <template #suffixIcon><SelectChevronIcon /></template>
        <template #clearIcon><ClearIcon /></template>
      </a-select>
    </div>

    <div class="form-group">
      <label class="form-label">Actividad <span class="required">*</span></label>
      <a-select
        v-model:value="form.activity"
        placeholder="Selecciona"
        class="custom-select"
        :options="activityOptions"
        :bordered="false"
        popup-class-name="custom-select-dropdown"
        allow-clear
      >
        <template #suffixIcon><SelectChevronIcon /></template>
        <template #clearIcon><ClearIcon /></template>
      </a-select>
    </div>

    <div class="form-group-radio">
      <span class="form-label-radio">Requiere detalle de actividad:</span>
      <a-radio-group v-model:value="form.requiresDetail" class="custom-radio-group">
        <a-radio :value="1">Si</a-radio>
        <a-radio :value="0">No</a-radio>
      </a-radio-group>
    </div>

    <div v-if="form.requiresDetail === 1" class="form-group anim-fade-in">
      <a-input v-model:value="form.activityDetail" placeholder="Ingresa" class="custom-input" />
    </div>
  </div>
</template>

<script setup lang="ts">
  import { computed } from 'vue';
  import { CalendarIcon, SelectChevronIcon, InfoCircleIcon, ClearIcon } from '../icons';
  import { useTransportConfiguration } from '../composables/useTransportConfiguration';
  import { datePickerLocale } from '../helpers/date-picker-locale';
  import type { TransportConfigurationFormState } from '../interfaces/transport-configuration-form.interface';

  const props = defineProps<{
    form: TransportConfigurationFormState;
  }>();

  const { cityOptions, segmentationOptions, activityOptions, clientOptions } =
    useTransportConfiguration();

  const configName = computed(() => {
    if (!props.form) return '';

    const getLabel = (val: any, options: any[]) =>
      options.find((o) => o.value === val)?.label || '';

    const segLabel = getLabel(props.form.segmentation, segmentationOptions.value);
    const actLabel = getLabel(props.form.activity, activityOptions.value);
    const cliLabel = getLabel(props.form.client, clientOptions.value);

    let baseName = props.form.segmentation === 'cliente' ? cliLabel : segLabel;
    let fullName = baseName && actLabel ? `${baseName} - ${actLabel}` : '';

    if (fullName && props.form.requiresDetail === 1 && props.form.activityDetail?.trim()) {
      fullName += ` - ${props.form.activityDetail.trim().replace(/-/g, ' ')}`;
    }

    return fullName;
  });
</script>

<style lang="scss" scoped>
  @import '../styles/form-elements.scss';

  .form-container {
    width: 100%;
    padding: 24px 52px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    background: #ffffff;

    .form-group {
      display: flex;
      flex-direction: column;
      gap: 8px;
    }

    .form-label {
      color: #2f353a;
      font-weight: 500;
      font-size: 14px;
      line-height: 20px;
      .required {
        color: #bd0d12;
      }
    }

    .label-with-icon {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .date-pickers-row {
      display: flex;
      gap: 16px;
    }

    .date-picker-item {
      display: flex;
      flex-direction: column;
      gap: 4px;
      .field-label {
        color: #2f353a;
        font-weight: 500;
        font-size: 14px;
      }
    }

    .form-group-radio {
      display: flex;
      align-items: center;
      gap: 24px;
      margin-top: 8px;
      .form-label-radio {
        color: #2f353a;
        font-weight: 500;
        font-size: 14px;
      }
    }
  }

  .anim-fade-in {
    animation: fadeIn 0.3s ease-in-out;
  }
  @keyframes fadeIn {
    from {
      opacity: 0;
      transform: translateY(-5px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }
</style>
