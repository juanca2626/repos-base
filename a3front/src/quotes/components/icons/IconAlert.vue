<script lang="ts" setup>
  interface Props {
    color?: string;
    width?: number;
    height?: number;
  }

  const props = withDefaults(defineProps<Props>(), {
    color: '#5C5AB4',
    width: 30,
    height: 30,
  });
</script>

<template>
  <svg
    xmlns="http://www.w3.org/2000/svg"
    fill="none"
    :height="props.height"
    :viewBox="`0 0 ${props.width} ${props.height}`"
    :width="props.width"
  >
    <g clip-path="url(#clip0_8687_7815)">
      <path
        d="M9.99935 18.3334C14.6017 18.3334 18.3327 14.6025 18.3327 10.0001C18.3327 5.39771 14.6017 1.66675 9.99935 1.66675C5.39698 1.66675 1.66602 5.39771 1.66602 10.0001C1.66602 14.6025 5.39698 18.3334 9.99935 18.3334Z"
        :stroke="props.color"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
      />
      <path
        d="M10 13.3333V10"
        :stroke="props.color"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
      />
      <path
        d="M10 6.66675H10.0083"
        :stroke="props.color"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
      />
    </g>
    <defs>
      <clipPath id="clip0_8687_7815">
        <rect :width="props.width" :height="props.height" fill="white" />
      </clipPath>
    </defs>
  </svg>
</template>

<style lang="scss"></style>
