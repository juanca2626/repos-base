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
    <g
      fill="none"
      :stroke="props.color"
      stroke-linecap="round"
      stroke-linejoin="round"
      stroke-width="2.05"
    >
      <circle cx="18" cy="18" r="3" />
      <circle cx="6" cy="6" r="3" />
      <path d="M13 6h3a2 2 0 0 1 2 2v7M6 9v12" />
    </g>
  </svg>
</template>
