<script lang="ts" setup>
  interface Props {
    width?: string;
    height?: string;
  }

  const props = withDefaults(defineProps<Props>(), {
    width: '24',
    height: '24',
  });
</script>

<template>
  <svg
    :width="props.width"
    :height="props.height"
    viewBox="0 0 24 25"
    fill="none"
    xmlns="http://www.w3.org/2000/svg"
  >
    <g clip-path="url(#clip0_23665_42501)">
      <g clip-path="url(#clip1_23665_42501)">
        <path
          d="M12 22.623C17.5228 22.623 22 18.1459 22 12.623C22 7.1002 17.5228 2.62305 12 2.62305C6.47715 2.62305 2 7.1002 2 12.623C2 18.1459 6.47715 22.623 12 22.623Z"
          stroke="#5C5AB4"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
        />
        <path
          d="M12 16.623V12.623"
          stroke="#5C5AB4"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
        />
        <path
          d="M12 8.62305H12.01"
          stroke="#5C5AB4"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
        />
      </g>
    </g>
    <defs>
      <clipPath id="clip0_23665_42501">
        <rect width="24" height="24" fill="white" transform="translate(0 0.623047)" />
      </clipPath>
      <clipPath id="clip1_23665_42501">
        <rect width="24" height="24" fill="white" transform="translate(0 0.623047)" />
      </clipPath>
    </defs>
  </svg>
</template>
