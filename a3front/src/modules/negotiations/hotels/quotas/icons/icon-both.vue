<template>
  <svg
    :width="width"
    :height="height"
    viewBox="0 0 21 22"
    fill="none"
    xmlns="http://www.w3.org/2000/svg"
  >
    <rect :width="width" :height="height" rx="10.5" :fill="color" />
    <g clip-path="url(#clip0_16826_2965)">
      <path
        d="M10.5 4C6.63359 4 3.5 7.13359 3.5 11C3.5 14.8664 6.63359 18 10.5 18C14.3664 18 17.5 14.8664 17.5 11C17.5 7.13359 14.3664 4 10.5 4ZM10.5 16.25V5.75C13.3957 5.75 15.75 8.10512 15.75 11C15.75 13.8949 13.3957 16.25 10.5 16.25Z"
        fill="#BBADE5"
      />
    </g>
    <defs>
      <clipPath id="clip0_16826_2965">
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
