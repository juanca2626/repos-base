<template>
  <svg
    :height="props.height"
    :viewBox="`0 0 ${props.width} ${props.height}`"
    :width="props.width"
    fill="none"
    xmlns="http://www.w3.org/2000/svg"
  >
    <path
      :stroke="props.color"
      d="M20 15.16V20.5C20 21.0304 19.7893 21.5391 19.4142 21.9142C19.0391 22.2893 18.5304 22.5 18 22.5H4C3.46957 22.5 2.96086 22.2893 2.58579 21.9142C2.21071 21.5391 2 21.0304 2 20.5V6.5C2 5.96957 2.21071 5.46086 2.58579 5.08579C2.96086 4.71071 3.46957 4.5 4 4.5H9.34"
      stroke-linecap="round"
      stroke-linejoin="round"
      stroke-width="2"
    />
    <path
      :stroke="props.color"
      d="M18 2.5L22 6.5L12 16.5H8V12.5L18 2.5Z"
      stroke-linecap="round"
      stroke-linejoin="round"
      stroke-width="2"
    />
  </svg>
</template>

<script lang="ts" setup>
  import { defineProps } from 'vue';

  const props = defineProps({
    color: { type: String, default: '#3D3D3D' },
    width: { type: Number, default: 25 },
    height: { type: Number, default: 25 },
  });
</script>
<style lang="scss" scoped></style>
