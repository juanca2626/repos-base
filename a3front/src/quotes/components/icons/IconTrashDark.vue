<script lang="ts" setup>
  interface Props {
    color?: string;
    width?: number;
    height?: number;
  }

  const props = withDefaults(defineProps<Props>(), {
    color: '#3D3D3D',
    width: 17,
    height: 18,
  });
</script>

<template>
  <svg fill="none" height="18" viewBox="0 0 17 18" width="17" xmlns="http://www.w3.org/2000/svg">
    <g clip-path="url(#clip0_8304_20926)">
      <path
        d="M2.125 4.75H3.54167H14.875"
        :stroke="props.color"
        stroke-linecap="round"
        stroke-linejoin="round"
        stroke-width="2"
      />
      <path
        d="M5.66602 4.75001V3.33334C5.66602 2.95762 5.81527 2.59728 6.08095 2.3316C6.34662 2.06593 6.70696 1.91667 7.08268 1.91667H9.91602C10.2917 1.91667 10.6521 2.06593 10.9177 2.3316C11.1834 2.59728 11.3327 2.95762 11.3327 3.33334V4.75001M13.4577 4.75001V14.6667C13.4577 15.0424 13.3084 15.4027 13.0427 15.6684C12.7771 15.9341 12.4167 16.0833 12.041 16.0833H4.95768C4.58196 16.0833 4.22162 15.9341 3.95595 15.6684C3.69027 15.4027 3.54102 15.0424 3.54102 14.6667V4.75001H13.4577Z"
        :stroke="props.color"
        stroke-linecap="round"
        stroke-linejoin="round"
        stroke-width="2"
      />
      <path
        d="M7.08398 8.29167V12.5417"
        :stroke="props.color"
        stroke-linecap="round"
        stroke-linejoin="round"
        stroke-width="2"
      />
      <path
        d="M9.91602 8.29167V12.5417"
        :stroke="props.color"
        stroke-linecap="round"
        stroke-linejoin="round"
        stroke-width="2"
      />
    </g>
    <defs>
      <clipPath id="clip0_8304_20926">
        <rect fill="white" height="17" transform="translate(0 0.5)" width="17" />
      </clipPath>
    </defs>
  </svg>
</template>

<style scoped lang="scss"></style>
