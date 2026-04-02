<template>
  <a-popover :trigger="trigger" :placement="placement">
    <span class="base-popover-default"><slot /></span>
    <template #content>
      <span class="base-popover-content" v-if="!hideContent">
        <slot name="content">Content</slot>
      </span>
    </template>
  </a-popover>
</template>

<script setup>
  defineProps({
    trigger: {
      type: String,
      default: 'hover',
      validator(triggerValue) {
        return ['hover', 'click'].includes(triggerValue);
      },
    },
    placement: {
      type: String,
      default: 'topLeft',
      validator(placementValue) {
        const placements = [
          'topLeft',
          'top',
          'topRight',
          'leftTop',
          'left',
          'leftBottom',
          'rightTop',
          'right',
          'rightBottom',
          'bottomLeft',
          'bottom',
          'bottomRight',
        ];
        return placements.includes(placementValue);
      },
    },
    hideContent: {
      type: Boolean,
      default: () => false,
    },
  });
</script>

<style lang="scss" scoped>
  .base-popover {
    &-default {
      cursor: pointer;
      display: flex;
      justify-content: center;
      align-items: center;
    }
    &-content {
      font-weight: 600;
      font-size: 12px;
      line-height: 19px;
      letter-spacing: 0.015em;
      color: #3d3d3d;
    }
  }
</style>
