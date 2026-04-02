<script lang="ts" setup>
  interface Props {
    color?: string;
    width?: number;
    height?: number;
  }

  const props = withDefaults(defineProps<Props>(), {
    color: '#3D3D3D',
    width: 16,
    height: 16,
  });
</script>

<template>
  <svg
    :width="props.width"
    :height="props.height"
    :viewBox="`0 0 ${props.width} ${props.height}`"
    fill="none"
    xmlns="http://www.w3.org/2000/svg"
  >
    <path
      d="M11.333 1.125L13.9997 3.625L11.333 6.125"
      :stroke="color"
      stroke-width="2"
      stroke-linecap="round"
      stroke-linejoin="round"
    />
    <path
      d="M2 7.375V6.125C2 5.46196 2.28095 4.82607 2.78105 4.35723C3.28115 3.88839 3.95942 3.625 4.66667 3.625H14"
      stroke="#3D3D3D"
      stroke-width="2"
      stroke-linecap="round"
      stroke-linejoin="round"
    />
    <path
      d="M4.66667 14.875L2 12.375L4.66667 9.875"
      :stroke="color"
      stroke-width="2"
      stroke-linecap="round"
      stroke-linejoin="round"
    />
    <path
      d="M14 8.625V9.875C14 10.538 13.719 11.1739 13.219 11.6428C12.7189 12.1116 12.0406 12.375 11.3333 12.375H2"
      :stroke="color"
      stroke-width="2"
      stroke-linecap="round"
      stroke-linejoin="round"
    />
  </svg>
</template>

<style lang="scss"></style>
