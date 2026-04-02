<script lang="ts" setup>
  interface Props {
    color?: string;
    width?: string;
    height?: string;
  }

  const props = withDefaults(defineProps<Props>(), {
    color: '#EB5757',
    width: '24',
    height: '24',
  });
</script>
<template>
  <svg
    xmlns="http://www.w3.org/2000/svg"
    :width="props.width"
    :height="props.height"
    :viewBox="`0 0 ${width} ${height}`"
    fill="none"
    stroke="currentColor"
    stroke-width="2"
    stroke-linecap="round"
    stroke-linejoin="round"
    class="feather feather-corner-down-right"
  >
    <polyline points="15 10 20 15 15 20"></polyline>
    <path d="M4 4v7a4 4 0 0 0 4 4h12"></path>
  </svg>
</template>
