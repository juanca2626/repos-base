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
  <svg fill="none" height="20" viewBox="0 0 20 20" width="20" xmlns="http://www.w3.org/2000/svg">
    <path
      d="M9.16667 15.8333C12.8486 15.8333 15.8333 12.8486 15.8333 9.16667C15.8333 5.48477 12.8486 2.5 9.16667 2.5C5.48477 2.5 2.5 5.48477 2.5 9.16667C2.5 12.8486 5.48477 15.8333 9.16667 15.8333Z"
      :stroke="color"
      stroke-linecap="round"
      stroke-linejoin="round"
      stroke-width="2"
    />
    <path
      d="M17.5 17.5L13.875 13.875"
      :stroke="color"
      stroke-linecap="round"
      stroke-linejoin="round"
      stroke-width="2"
    />
  </svg>
</template>

<style lang="scss"></style>
