<template>
  <a-button class="base-button" :class="customClass" :type="type" :size="size" v-bind="$attrs">
    <slot />
  </a-button>
</template>

<script setup>
  import { ref } from 'vue';

  // const customClass = {}

  const props = defineProps({
    label: {
      type: String,
      default: '',
    },
    type: {
      type: String,
      default: 'primary',
    },
    modelValue: {
      type: [String, Number],
      default: '',
    },
    size: {
      type: String,
      default: '',
      validator(sizeValue) {
        return ['large', 'small'].includes(sizeValue);
      },
    },
    width: {
      type: String,
      default: '132',
      validator(widthValue) {
        return ['60', '89', '132', '149', '119'].includes(widthValue);
      },
    },
    height: {
      type: String,
      default: '25',
      validator(heightValue) {
        return ['25', '50'].includes(heightValue);
      },
    },
    class: {
      type: String,
      default: '',
    },
  });

  const customClass = ref({
    [`base-button-w${props.width}`]: props.width,
    [`base-button-h${props.height}`]: props.height,
    [`${props.class}`]: props.class,
  });
</script>

<style scoped lang="scss">
  .base-button.ant-btn {
    font-weight: 600;
    font-size: 14px;
    line-height: 21px;
    letter-spacing: 0.015em;
    border-radius: 6px;

    //  background-color:#EB5757;
    //  border-color: #EB5757;

    &-lg {
      height: 45px;
    }
  }
  .base-button {
    &-w89 {
      min-width: 89px;
    }
    &-w132 {
      min-width: 132px;
    }
    &-w149 {
      min-width: 149px;
    }
    &-w119 {
      min-width: 119px;
    }
    &-h25 {
      height: 25px;
    }
  }
</style>
