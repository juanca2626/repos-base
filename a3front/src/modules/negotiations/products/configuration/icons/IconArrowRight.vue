<template>
  <svg
    :width="width"
    :height="height"
    viewBox="0 0 24 25"
    fill="none"
    xmlns="http://www.w3.org/2000/svg"
  >
    <path
      d="M5 12.4141H19"
      stroke="#2F353A"
      stroke-width="2"
      stroke-linecap="round"
      stroke-linejoin="round"
    />
    <path
      d="M12 5.41406L19 12.4141L12 19.4141"
      stroke="#2F353A"
      stroke-width="2"
      stroke-linecap="round"
      stroke-linejoin="round"
    />
  </svg>
</template>
<script lang="ts" setup>
  import type { BaseIconProp } from '@/modules/negotiations/supplier-new/interfaces/custom-icon.interface';

  withDefaults(defineProps<BaseIconProp>(), {
    width: 24,
    height: 25,
  });
</script>
