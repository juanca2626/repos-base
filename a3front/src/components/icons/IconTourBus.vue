<script lang="ts" setup>
  interface Props {
    width?: string;
    height?: string;
  }

  const props = withDefaults(defineProps<Props>(), {
    width: '1.2em',
    height: '1.2em',
  });
</script>
<template>
  <svg
    :width="props.width"
    :height="props.height"
    viewBox="0 0 48 48"
    fill="none"
    xmlns="http://www.w3.org/2000/svg"
  >
    <g>
      <path
        d="M9 23H39V34C39 35.1046 38.1046 36 37 36H11C9.89543 36 9 35.1046 9 34V23Z"
        fill="#000000"
        stroke="#000000"
        stroke-width="4"
        stroke-linejoin="round"
      />
      <path
        d="M9 8C9 6.89543 9.89543 6 11 6H37C38.1046 6 39 6.89543 39 8V23H9V8Z"
        stroke="#000000"
        stroke-width="4"
        stroke-linejoin="round"
      />
      <path
        d="M15 42C13.3431 42 12 40.6569 12 39V36H18V39C18 40.6569 16.6569 42 15 42Z"
        fill="#000000"
        stroke="#000000"
        stroke-width="4"
        stroke-linecap="round"
        stroke-linejoin="round"
      />
      <path
        d="M33 42C31.3431 42 30 40.6569 30 39V36H36V39C36 40.6569 34.6569 42 33 42Z"
        fill="#000000"
        stroke="#000000"
        stroke-width="4"
        stroke-linecap="round"
        stroke-linejoin="round"
      />
      <path d="M6 12V16" stroke="#000000" stroke-width="4" stroke-linecap="round" />
      <path d="M42 12V16" stroke="#000000" stroke-width="4" stroke-linecap="round" />
      <circle cx="15" cy="30" r="2" fill="white" />
      <circle cx="33" cy="30" r="2" fill="white" />
      <path d="M31 6L22 16" stroke="#000000" stroke-width="4" stroke-linecap="round" />
      <path d="M38 7L33 13" stroke="#000000" stroke-width="4" stroke-linecap="round" />
    </g>
  </svg>
</template>
