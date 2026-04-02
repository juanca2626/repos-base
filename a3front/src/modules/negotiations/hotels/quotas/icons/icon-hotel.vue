<template>
  <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
    <rect x="3.5" y="2.5" width="13" height="15" rx="0.5" stroke="#2F353A" />
    <rect x="8.5" y="12.5" width="3" height="5" rx="0.5" stroke="#2F353A" />
    <rect x="6.5" y="4.5" width="2" height="2" rx="0.5" stroke="#2F353A" />
    <rect x="6.5" y="8.5" width="2" height="2" rx="0.5" stroke="#2F353A" />
    <rect x="11.5" y="4.5" width="2" height="2" rx="0.5" stroke="#2F353A" />
    <rect x="11.5" y="8.5" width="2" height="2" rx="0.5" stroke="#2F353A" />
    <line x1="1" y1="17.5" x2="19" y2="17.5" stroke="#2F353A" />
  </svg>
</template>

<script lang="ts" setup>
  interface Props {
    color?: string;
    width?: number;
    height?: number;
  }

  withDefaults(defineProps<Props>(), {
    color: '#2F353A',
    width: 20,
    height: 20,
  });
</script>

<style lang="scss"></style>
