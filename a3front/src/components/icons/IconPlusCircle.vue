<script lang="ts" setup>
  interface Props {
    color?: string;
    width?: string;
    height?: string;
  }

  const props = withDefaults(defineProps<Props>(), {
    color: '#4F4B4B',
    width: '17',
    height: '18',
  });
</script>
<template>
  <span class="d-flex cursor-pointer">
    <svg
      :width="props.width"
      :height="props.height"
      :viewBox="`0 0 ${width} ${height}`"
      fill="none"
      xmlns="http://www.w3.org/2000/svg"
    >
      <g clip-path="url(#clip0_22296_60825)">
        <path
          d="M8.50008 15.7067C12.4121 15.7067 15.5834 12.5354 15.5834 8.62337C15.5834 4.71136 12.4121 1.54004 8.50008 1.54004C4.58806 1.54004 1.41675 4.71136 1.41675 8.62337C1.41675 12.5354 4.58806 15.7067 8.50008 15.7067Z"
          :stroke="props.color"
          stroke-width="1.5"
          stroke-linecap="round"
          stroke-linejoin="round"
        />
        <path
          d="M8.5 5.79004V11.4567"
          :stroke="props.color"
          stroke-width="1.5"
          stroke-linecap="round"
          stroke-linejoin="round"
        />
        <path
          d="M5.66675 8.62341H11.3334"
          :stroke="props.color"
          stroke-width="1.5"
          stroke-linecap="round"
          stroke-linejoin="round"
        />
      </g>
      <defs>
        <clipPath id="clip0_22296_60825">
          <rect
            :width="props.width"
            :height="props.width"
            fill="white"
            transform="translate(0 0.123413)"
          />
        </clipPath>
      </defs>
    </svg>
  </span>
</template>
