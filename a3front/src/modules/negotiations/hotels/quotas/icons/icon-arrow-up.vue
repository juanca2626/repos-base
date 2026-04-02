<template>
  <svg
    :width="width"
    :height="height"
    viewBox="0 0 19 11"
    fill="none"
    xmlns="http://www.w3.org/2000/svg"
  >
    <path
      d="M15.7708 10.4876L9.30417 4.02093L2.8375 10.4876C2.1875 11.1376 1.1375 11.1376 0.487499 10.4876C-0.1625 9.8376 -0.1625 8.7876 0.487499 8.1376L8.1375 0.487597C8.7875 -0.162403 9.8375 -0.162403 10.4875 0.487597L18.1375 8.1376C18.7875 8.7876 18.7875 9.8376 18.1375 10.4876C17.4875 11.1209 16.4208 11.1376 15.7708 10.4876Z"
      fill="#575B5F"
    />
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
    width: 19,
    height: 11,
  });
</script>

<style lang="scss"></style>
