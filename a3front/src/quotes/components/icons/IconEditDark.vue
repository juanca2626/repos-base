<script setup lang="ts">
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
  <svg
    fill="none"
    :height="props.height"
    :viewBox="`0 0 ${props.width} ${props.height}`"
    :width="props.width"
    xmlns="http://www.w3.org/2000/svg"
  >
    <g clip-path="url(#clip0_8304_20922)">
      <path
        d="M14.166 10.8842V14.6667C14.166 15.0424 14.0168 15.4027 13.7511 15.6684C13.4854 15.9341 13.1251 16.0833 12.7493 16.0833H2.83268C2.45696 16.0833 2.09662 15.9341 1.83095 15.6684C1.56527 15.4027 1.41602 15.0424 1.41602 14.6667V4.74999C1.41602 4.37427 1.56527 4.01394 1.83095 3.74826C2.09662 3.48258 2.45696 3.33333 2.83268 3.33333H6.61518"
        :stroke="props.color"
        stroke-linecap="round"
        stroke-linejoin="round"
        stroke-width="2"
      />
      <path
        d="M12.7493 1.91667L15.5827 4.75001L8.49935 11.8333H5.66602V9L12.7493 1.91667Z"
        :stroke="props.color"
        stroke-linecap="round"
        stroke-linejoin="round"
        stroke-width="2"
      />
    </g>
    <defs>
      <clipPath id="clip0_8304_20922">
        <rect fill="white" height="17" transform="translate(0 0.5)" width="17" />
      </clipPath>
    </defs>
  </svg>
</template>

<style scoped lang="scss"></style>
