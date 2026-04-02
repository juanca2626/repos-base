<template>
  <svg width="15" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
    <g clip-path="url(#clip0_16856_6050)">
      <path
        d="M1.875 14.5312C1.875 14.7891 1.6626 15 1.40625 15H0.46875C0.212402 15 0 14.7891 0 14.5312V0.9375C0 0.41748 0.41748 0 0.9375 0C1.45752 0 1.875 0.41748 1.875 0.9375V14.5312ZM13.9541 0C13.7676 0 13.5729 0.0395508 13.3875 0.124014C12.0489 0.735147 11.0566 0.942568 10.2293 0.942568C8.47266 0.942568 7.45166 0.00916992 5.42754 0.00887695C4.71387 0.0089502 3.85547 0.142588 2.8125 0.461426V10.73C3.75 10.4375 4.5665 10.3169 5.29834 10.3169C7.45547 10.3169 8.95752 11.2479 11.1167 11.2479C12.0516 11.2479 13.1095 11.073 14.3716 10.5715C14.7686 10.4268 15 10.0898 15 9.72949V0.900293C15 0.325195 14.5107 0 13.9541 0Z"
        fill="#575B5F"
      />
    </g>
    <defs>
      <clipPath id="clip0_16856_6050">
        <rect width="15" height="15" fill="white" />
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
    color: '#575B5F',
    width: 15,
    height: 15,
  });
</script>

<style lang="scss"></style>
