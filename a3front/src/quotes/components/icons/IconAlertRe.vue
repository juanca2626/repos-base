<script lang="ts" setup>
  interface Props {
    color?: string;
    width?: number;
    height?: number;
  }

  const props = withDefaults(defineProps<Props>(), {
    color: '#3D3D3D',
    width: 30,
    height: 30,
  });
</script>

<template>
  <svg
    :width="props.width"
    :height="props.height"
    :viewBox="`0 0 ${props.width} ${props.height}`"
    fill="none"
    xmlns="http://www.w3.org/2000/svg"
  >
    <g clip-path="url(#clip0_11330_113)">
      <path
        d="M10.5001 18.3334C15.1025 18.3334 18.8334 14.6025 18.8334 10.0001C18.8334 5.39771 15.1025 1.66675 10.5001 1.66675C5.89771 1.66675 2.16675 5.39771 2.16675 10.0001C2.16675 14.6025 5.89771 18.3334 10.5001 18.3334Z"
        :stroke="props.color"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
      />
      <path
        d="M10.5 13.3333V10"
        :stroke="props.color"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
      />
      <path
        d="M10.5 6.66675H10.5083"
        :stroke="props.color"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
      />
    </g>
    <defs>
      <clipPath id="clip0_11330_113">
        <rect :width="props.width" :height="props.height" fill="white" transform="translate(0.5)" />
      </clipPath>
    </defs>
  </svg>
</template>

<style lang="scss"></style>
