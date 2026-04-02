<script lang="ts" setup>
  interface Props {
    color?: string;
    width?: string;
    height?: string;
  }

  const props = withDefaults(defineProps<Props>(), {
    color: '#EB5757',
    width: '1.2em',
    height: '1.2em',
  });
</script>
<template>
  <svg :width="props.width" :height="props.height" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path
      data-v-2231fc89=""
      d="m7.592 13.553 3.333 3.334 3.334-3.334M10.925 9.386v7.5"
      :stroke="props.color"
      stroke-width="2"
      stroke-linecap="round"
      stroke-linejoin="round"
    ></path>
    <path
      data-v-2231fc89=""
      d="M18.326 14.461a4.167 4.167 0 0 0-2.4-7.576h-1.05A6.666 6.666 0 0 0 3.034 4.608a6.667 6.667 0 0 0 .39 8.353"
      :stroke="props.color"
      stroke-width="2"
      stroke-linecap="round"
      stroke-linejoin="round"
    ></path>
  </svg>
</template>
