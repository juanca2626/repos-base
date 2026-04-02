<template>
  <svg
    :width="width"
    :height="height"
    viewBox="0 0 21 22"
    fill="none"
    xmlns="http://www.w3.org/2000/svg"
  >
    <rect :width="width" :height="height" rx="10.5" :fill="color" />
    <g clip-path="url(#clip0_16826_2445)">
      <path
        d="M10.5 8.375C9.05078 8.375 7.875 9.55078 7.875 11C7.875 12.4479 9.05215 13.625 10.5 13.625C11.9479 13.625 13.125 12.4479 13.125 11C13.125 9.55078 11.9246 8.375 10.5 8.375ZM10.5 4C6.63359 4 3.5 7.13359 3.5 11C3.5 14.8664 6.63359 18 10.5 18C14.3664 18 17.5 14.8664 17.5 11C17.5 7.13359 14.3664 4 10.5 4ZM10.5 16.6875C7.36367 16.6875 4.8125 14.1361 4.8125 11C4.8125 7.86395 7.36367 5.3125 10.5 5.3125C13.6363 5.3125 16.1875 7.86395 16.1875 11C16.1875 14.1361 13.6363 16.6875 10.5 16.6875Z"
        fill="#1284ED"
        fill-opacity="0.5"
      />
    </g>
    <defs>
      <clipPath id="clip0_16826_2445">
        <rect width="14" height="14" fill="white" transform="translate(3.5 4)" />
      </clipPath>
    </defs>
  </svg>
</template>

<script lang="ts" setup>
  interface Props {
    color?: string;
    width?: number;
    height?: number;
  }

  withDefaults(defineProps<Props>(), {
    color: '#F9F9F9',
    width: 21,
    height: 22,
  });
</script>

<style lang="scss"></style>
