<template>
  <a-card class="indicator-card" :bordered="true">
    <div class="indicator-header">
      <a-avatar
        size="large"
        class="indicator-icon"
        :style="{ backgroundColor: iconBackgroundWithOpacity }"
      >
        <FontAwesomeIcon :icon="icon" :style="{ color: iconColor, fontSize: '20px' }" />
      </a-avatar>

      <div class="indicator-info">
        <span class="indicator-title" :style="{ color: titleColor }">{{ servicioLabel }}</span>
        <span v-if="subtitle" class="indicator-subtitle">{{ subtitle }}</span>
      </div>
    </div>

    <div class="indicator-content">
      <slot />
    </div>

    <div v-if="buttons.length" class="indicator-footer">
      <a-button
        v-for="(button, index) in buttons"
        :key="index"
        type="default"
        size="large"
        class="indicator-button"
        :style="[
          {
            backgroundColor: buttonBackgroundWithOpacity,
            color: textButton,
          },
          buttons.length === 1 ? fullWidthStyle : {},
        ]"
        @click="() => emit('click', button.action, button.type)"
      >
        <ArrowRightOutlined
          v-if="isIconLeft"
          class="btn-icon-left"
          :style="{ color: colorArrow }"
        />
        {{ button.label }}
        <ArrowRightOutlined
          v-if="isIconRight"
          class="btn-icon-right"
          :style="{ color: colorArrow }"
        />
      </a-button>
    </div>
  </a-card>
</template>

<script lang="ts" setup>
  import { computed } from 'vue';
  import { ArrowRightOutlined } from '@ant-design/icons-vue';
  import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
  import type { ActionType } from '../types/indicator';

  interface ButtonProps {
    label: string;
    action: ActionType;
    type?: string;
  }

  type IconPosition = 'left' | 'right';

  const props = defineProps<{
    total: number;
    subtitle?: string;
    icon: [string, string];
    iconColor?: string;
    titleColor?: string;
    bgButton?: string;
    colorArrow?: string;
    textButton?: string;
    buttons: ButtonProps[];
    iconPosition?: IconPosition;
  }>();

  const emit = defineEmits<{
    (e: 'click', action: ActionType, type: any): void;
  }>();

  const servicioLabel = computed(() => {
    return `${props.total} ${props.total === 1 ? 'servicio' : 'servicios'}`;
  });

  const fullWidthStyle = { width: '100%' };

  // Color con opacidad
  function colorToRgba(hex: string, opacity: number): string {
    const cleanHex = hex.replace('#', '');
    const bigint = parseInt(cleanHex, 16);
    const r = (bigint >> 16) & 255;
    const g = (bigint >> 8) & 255;
    const b = bigint & 255;
    return `rgba(${r}, ${g}, ${b}, ${opacity})`;
  }

  const iconBackgroundWithOpacity = computed(() => {
    return colorToRgba(props.iconColor ?? titleColor.value, 0.15);
  });

  const buttonBackgroundWithOpacity = computed(() => {
    return colorToRgba(props.bgButton ?? titleColor.value, 0.15);
  });

  const isIconLeft = computed(() => props.iconPosition !== 'right');
  const isIconRight = computed(() => props.iconPosition === 'right');

  const iconColor = computed(() => props.iconColor ?? titleColor.value);
  const titleColor = computed(() => props.titleColor ?? '#000000');
  const colorArrow = computed(() => props.colorArrow ?? titleColor.value);
  const textButton = computed(() => props.textButton ?? titleColor.value);
</script>

<style scoped>
  .indicator-card {
    border-radius: 4px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: space-between;
    background-color: white;
    padding: 0 20px;
  }

  .indicator-header {
    display: flex;
    align-items: center;
    margin-bottom: 8px;
    width: 100%;
  }

  .indicator-icon {
    margin-right: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .indicator-info {
    display: flex;
    flex-direction: column;
  }

  .indicator-title {
    font-weight: 700;
    font-size: 1.1rem;
  }

  .indicator-subtitle {
    font-size: 12px;
    color: #8c8c8c;
  }

  .indicator-content {
    width: 100%;
  }

  .indicator-footer {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 8px;
    margin-top: 8px;
    flex-wrap: wrap;
  }

  .indicator-button {
    font-weight: 600;
    font-size: 0.875rem;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 4px 14px;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    transition: background-color 0.2s ease;
  }

  .indicator-button:hover {
    filter: brightness(0.95);
  }

  .btn-icon-left {
    font-size: 0.8rem;
    margin-right: 3px;
  }

  .btn-icon-right {
    font-size: 0.8rem;
    margin-left: 3px;
  }

  ::v-deep(.ant-card-body) {
    padding: 12px !important;
  }
</style>
