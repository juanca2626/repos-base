<template>
  <div class="main-container">
    <span class="description"> Vigencia de tarifa: </span>
    <div class="prefix-range-picker">
      <CalendarOutlined class="prefix-icon" />
      <a-range-picker
        v-model:value="tariffValidity"
        class="range-picker-tariff-validity"
        :class="{ 'has-value': tariffValidity != null }"
        format="MMM/YYYY"
        :locale="esES"
        :placeholder="['Inicio', 'Fin']"
      >
        <template #suffixIcon>
          <font-awesome-icon :icon="['fas', 'chevron-down']" class="icon-chevron-down" />
        </template>
      </a-range-picker>
    </div>
  </div>
</template>
<script setup lang="ts">
  import { ref } from 'vue';
  import type { Dayjs } from 'dayjs';
  import { CalendarOutlined } from '@ant-design/icons-vue';
  import esES from 'ant-design-vue/es/date-picker/locale/es_ES';

  const tariffValidity = ref<[Dayjs, Dayjs]>();
</script>
<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .main-container {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 8px;
    width: 100%;
    min-width: 0;

    .description {
      flex-shrink: 0;
      white-space: nowrap;
      font-weight: 500;
      font-size: 16px;
    }
  }

  .prefix-range-picker {
    position: relative;
    display: inline-block;
    flex: 1 1 auto;
    min-width: 0;
    max-width: 260px;
    width: 100%;
  }

  .prefix-range-picker .prefix-icon {
    position: absolute;
    top: 48%;
    left: 12px;
    transform: translateY(-50%);
    color: $color-black-2;
    z-index: 1;
    pointer-events: none;
  }

  .prefix-range-picker .ant-picker {
    padding-left: 32px !important;
  }

  .range-picker-tariff-validity {
    width: 100%;
    min-width: 0;

    &.has-value :deep(.ant-picker-suffix) {
      margin-right: 12px;
    }

    .icon-chevron-down {
      color: $color-black-2;
    }
  }

  :deep(.range-picker-tariff-validity.ant-picker-range) {
    height: 48px !important;
    border-radius: 4px;
    padding: 8px 16px;
  }

  :deep(.range-picker-tariff-validity.ant-picker-range:not(.ant-picker-focused):not(:hover)) {
    border-color: $color-black-2;
  }

  @media (max-width: 1200px) {
    .main-container {
      justify-content: flex-start;
    }

    .prefix-range-picker {
      max-width: 220px;
    }
  }

  @media (max-width: 992px) {
    .prefix-range-picker {
      max-width: 200px;
    }
  }

  @media (max-width: 768px) {
    .main-container {
      flex-wrap: wrap;
    }

    .prefix-range-picker {
      max-width: none;
    }
  }
</style>
