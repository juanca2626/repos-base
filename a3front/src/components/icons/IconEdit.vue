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
      <g clip-path="url(#clip0_18430_72194)">
        <g clip-path="url(#clip1_18430_72194)">
          <path
            d="M15.8333 11.7292V15.9567C15.8333 16.3766 15.6665 16.7794 15.3696 17.0763C15.0726 17.3732 14.6699 17.54 14.25 17.54H3.16665C2.74672 17.54 2.34399 17.3732 2.04706 17.0763C1.75013 16.7794 1.58331 16.3766 1.58331 15.9567V4.87337C1.58331 4.45345 1.75013 4.05072 2.04706 3.75379C2.34399 3.45685 2.74672 3.29004 3.16665 3.29004H7.39415"
            :stroke="props.color"
            stroke-width="1.5"
            stroke-linecap="round"
            stroke-linejoin="round"
          />
          <path
            d="M14.25 1.70679L17.4166 4.87345L9.49998 12.7901H6.33331V9.62345L14.25 1.70679Z"
            :stroke="props.color"
            stroke-width="1.5"
            stroke-linecap="round"
            stroke-linejoin="round"
          />
        </g>
      </g>
      <defs>
        <clipPath id="clip0_18430_72194">
          <rect
            :width="props.width"
            :height="props.height"
            fill="white"
            transform="translate(0 0.123413)"
          />
        </clipPath>
        <clipPath id="clip1_18430_72194">
          <rect
            :width="props.width"
            :height="props.height"
            fill="white"
            transform="translate(0 0.123413)"
          />
        </clipPath>
      </defs>
    </svg>
  </span>
</template>
