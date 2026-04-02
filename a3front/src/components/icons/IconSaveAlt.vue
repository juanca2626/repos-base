<script lang="ts" setup>
  interface Props {
    color?: string;
    width?: string;
    height?: string;
  }

  const props = withDefaults(defineProps<Props>(), {
    color: '#3D3D3D',
    width: '19',
    height: '20',
  });
</script>
<template>
  <span class="d-flex cursor-pointer">
    <svg
      :width="props.width"
      :height="props.height"
      viewBox="0 0 19 20"
      fill="none"
      xmlns="http://www.w3.org/2000/svg"
    >
      <path
        d="M15.0417 16.7484H3.95833C3.53841 16.7484 3.13568 16.5816 2.83875 16.2847C2.54181 15.9877 2.375 15.585 2.375 15.1651V4.08175C2.375 3.66182 2.54181 3.25909 2.83875 2.96216C3.13568 2.66523 3.53841 2.49841 3.95833 2.49841H12.6667L16.625 6.45675V15.1651C16.625 15.585 16.4582 15.9877 16.1613 16.2847C15.8643 16.5816 15.4616 16.7484 15.0417 16.7484Z"
        :stroke="props.color"
        stroke-width="1.5"
        stroke-linecap="round"
        stroke-linejoin="round"
      />
      <path
        d="M13.4584 16.7484V10.415H5.54175V16.7484"
        :stroke="props.color"
        stroke-width="1.5"
        stroke-linecap="round"
        stroke-linejoin="round"
      />
      <path
        d="M5.54175 2.49841V6.45675H11.8751"
        :stroke="props.color"
        stroke-width="1.5"
        stroke-linecap="round"
        stroke-linejoin="round"
      />
    </svg>
  </span>
</template>
