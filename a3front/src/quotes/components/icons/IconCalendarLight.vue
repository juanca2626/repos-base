<script lang="ts" setup>
  import { toRef } from 'vue';

  interface Props {
    color?: string;
    width?: number;
    height?: number;
  }
  const props = withDefaults(defineProps<Props>(), {
    color: '#EB5757',
    width: 24,
    height: 24,
  });
  const color = toRef(props, 'color');
  const width = toRef(props, 'width');
  const height = toRef(props, 'height');
</script>

<template>
  <svg
    fill="none"
    :height="height"
    :viewBox="`0 0 24 25`"
    :width="width"
    xmlns="http://www.w3.org/2000/svg"
  >
    <path
      d="M18.2083 3.83334H4.79167C3.73312 3.83334 2.875 4.69146 2.875 5.75V19.1667C2.875 20.2252 3.73312 21.0833 4.79167 21.0833H18.2083C19.2669 21.0833 20.125 20.2252 20.125 19.1667V5.75C20.125 4.69146 19.2669 3.83334 18.2083 3.83334Z"
      :stroke="color"
      stroke-linecap="round"
      stroke-linejoin="round"
      stroke-width="2"
    />
    <path
      d="M15.334 1.91666V5.75"
      :stroke="color"
      stroke-linecap="round"
      stroke-linejoin="round"
      stroke-width="2"
    />
    <path
      d="M7.66602 1.91666V5.75"
      :stroke="color"
      stroke-linecap="round"
      stroke-linejoin="round"
      stroke-width="2"
    />
    <path
      d="M2.875 9.58334H20.125"
      :stroke="color"
      stroke-linecap="round"
      stroke-linejoin="round"
      stroke-width="2"
    />
  </svg>
</template>

<style lang="scss" scoped></style>
