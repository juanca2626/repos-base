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
    <path
      d="M15.5 6.66675C16.8807 6.66675 18 5.54746 18 4.16675C18 2.78604 16.8807 1.66675 15.5 1.66675C14.1193 1.66675 13 2.78604 13 4.16675C13 5.54746 14.1193 6.66675 15.5 6.66675Z"
      :stroke="props.color"
      stroke-width="2"
      stroke-linecap="round"
      stroke-linejoin="round"
    />
    <path
      d="M5.5 12.5C6.88071 12.5 8 11.3807 8 10C8 8.61929 6.88071 7.5 5.5 7.5C4.11929 7.5 3 8.61929 3 10C3 11.3807 4.11929 12.5 5.5 12.5Z"
      :stroke="props.color"
      stroke-width="2"
      stroke-linecap="round"
      stroke-linejoin="round"
    />
    <path
      d="M15.5 18.3333C16.8807 18.3333 18 17.214 18 15.8333C18 14.4525 16.8807 13.3333 15.5 13.3333C14.1193 13.3333 13 14.4525 13 15.8333C13 17.214 14.1193 18.3333 15.5 18.3333Z"
      :stroke="props.color"
      stroke-width="2"
      stroke-linecap="round"
      stroke-linejoin="round"
    />
    <path
      d="M7.65845 11.2583L13.3501 14.575"
      :stroke="props.color"
      stroke-width="2"
      stroke-linecap="round"
      stroke-linejoin="round"
    />
    <path
      d="M13.3418 5.42505L7.65845 8.74172"
      :stroke="props.color"
      stroke-width="2"
      stroke-linecap="round"
      stroke-linejoin="round"
    />
  </svg>
</template>

<style lang="scss"></style>
