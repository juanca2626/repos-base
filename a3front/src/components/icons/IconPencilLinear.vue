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
      stroke-width="1.1"
      d="M4 16v4h4L18.869 9.131h0c.396-.396.594-.594.668-.822a1 1 0 0 0 0-.618c-.074-.228-.272-.426-.668-.822l-1.74-1.74c-.395-.394-.592-.592-.82-.666a1 1 0 0 0-.618 0c-.228.074-.426.272-.82.667l-.002.001z"
    />
  </svg>
</template>
