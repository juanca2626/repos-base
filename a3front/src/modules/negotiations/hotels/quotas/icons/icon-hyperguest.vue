<template>
  <svg
    :width="width"
    :height="height"
    viewBox="0 0 21 22"
    fill="none"
    xmlns="http://www.w3.org/2000/svg"
  >
    <rect :width="width" :height="height" rx="10.5" :fill="color" />
    <g clip-path="url(#clip0_16826_4114)">
      <path
        d="M10.5 4C6.63359 4 3.5 7.13359 3.5 11C3.5 14.8664 6.63359 18 10.5 18C14.3664 18 17.5 14.8664 17.5 11C17.5 7.13359 14.3664 4 10.5 4ZM13.5625 13.8438C13.5625 14.2061 13.2686 14.5 12.9062 14.5C12.5439 14.5 12.25 14.2074 12.25 13.8438V11.6562H8.75V13.8438C8.75 14.2074 8.45742 14.5 8.09375 14.5C7.73008 14.5 7.4375 14.2074 7.4375 13.8438V8.15625C7.4375 7.79531 7.73281 7.5 8.09375 7.5C8.45469 7.5 8.75 7.79531 8.75 8.15625V10.3438H12.25V8.15625C12.25 7.79531 12.5453 7.5 12.9062 7.5C13.2672 7.5 13.5625 7.79395 13.5625 8.15625V13.8438Z"
        fill="#0F766E"
        fill-opacity="0.5"
      />
    </g>
    <defs>
      <clipPath id="clip0_16826_4114">
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
