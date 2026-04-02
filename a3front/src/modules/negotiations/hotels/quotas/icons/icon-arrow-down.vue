<template>
  <svg
    :width="width"
    :height="height"
    viewBox="0 0 19 11"
    fill="none"
    xmlns="http://www.w3.org/2000/svg"
  >
    <path
      d="M2.85417 0.4875L9.32083 6.95417L15.7875 0.4875C16.4375 -0.1625 17.4875 -0.1625 18.1375 0.4875C18.7875 1.1375 18.7875 2.1875 18.1375 2.8375L10.4875 10.4875C9.8375 11.1375 8.7875 11.1375 8.1375 10.4875L0.4875 2.8375C-0.1625 2.1875 -0.1625 1.1375 0.4875 0.4875C1.1375 -0.145833 2.20417 -0.1625 2.85417 0.4875Z"
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
