<script lang="ts" setup>
  interface Props {
    color?: string;
    width?: string;
    height?: string;
  }

  const props = withDefaults(defineProps<Props>(), {
    color: '#EB5757',
    width: '1.2em',
    height: '1.2em',
  });
</script>
<template>
  <svg
    xmlns="http://www.w3.org/2000/svg"
    :width="props.width"
    :height="props.height"
    viewBox="0 0 24 24"
  >
    <path
      fill="none"
      :stroke="props.color"
      stroke-linecap="round"
      stroke-linejoin="round"
      stroke-width="2.05"
      d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0-18 0m6 0h6m-3-3v6"
    />
  </svg>
</template>
