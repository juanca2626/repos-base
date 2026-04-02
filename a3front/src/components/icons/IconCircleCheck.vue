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
  <span class="d-flex cursor-pointer">
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
        <path d="M21.801 10A10 10 0 1 1 17 3.335" />
        <path d="m9 11l3 3L22 4" />
      </g>
    </svg>
  </span>
</template>
