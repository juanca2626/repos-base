<script lang="ts" setup>
  import { toRef } from 'vue';

  interface Props {
    color?: string;
    width?: number;
    height?: number;
  }

  const props = withDefaults(defineProps<Props>(), {
    color: '#FFCC00',
    width: 24,
    height: 24,
  });

  const color = toRef(props, 'color');
  // const width = toRef(props, 'width');
  // const height = toRef(props, 'height');
</script>

<template>
  <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path
      d="M13 17L18 12L13 7"
      :stroke="color"
      stroke-width="2"
      stroke-linecap="round"
      stroke-linejoin="round"
    />
    <path
      d="M6 17L11 12L6 7"
      :stroke="color"
      stroke-width="2"
      stroke-linecap="round"
      stroke-linejoin="round"
    />
  </svg>
</template>

<style lang="scss"></style>
