<template>
  <div class="d-flex files-edit-field-static">
    <base-popover
      placement="top"
      :visible="hideContent"
      :open="hovered"
      @visibleChange="handleHoverChange"
    >
      <div :class="{ 'files-edit-field-container--inline': inline }">
        <div
          class="files-edit-field-static-label"
          :class="{
            'file-edit-field-static-label--highlighted': highlighted,
            'file-edit-field-static-label--link': link,
          }"
        >
          <slot name="label" />
        </div>
        <div class="files-edit-field-static-value" v-if="$slots.content">
          <slot name="content" />
        </div>
      </div>
      <template #content>
        <slot name="popover-content" />
      </template>
    </base-popover>
  </div>
</template>

<script setup>
  import { ref } from 'vue';
  import BasePopover from '@/components/files/reusables/BasePopover.vue';

  const hovered = ref(false);

  const props = defineProps({
    label: {
      type: String,
      default: () => '',
    },
    value: {
      type: String,
      default: () => '',
    },
    inline: {
      type: Boolean,
      default: () => false,
    },
    highlighted: {
      type: Boolean,
      default: () => false,
    },
    hideContent: {
      type: Boolean,
      default: () => true,
    },
    link: {
      type: Boolean,
      default: () => false,
    },
    editable: {
      type: Boolean,
      default: () => true,
    },
  });

  const handleHoverChange = (visible) => {
    //clicked.value = false
    if (!props.hideContent) {
      hovered.value = visible;
    }
  };
</script>

<style scoped lang="scss">
  .files-edit-field-static {
    color: #575757;
    line-height: 17px;
    letter-spacing: 0.015em;
    &-label {
      cursor: default;
      display: flex;
      align-items: center;
      gap: 6px;
      font-weight: 400;
      font-size: 10px;
    }
    &-value {
      cursor: default;
      font-weight: 700;
      font-size: 14px;
      line-height: 21px;
      display: flex;
      align-items: center;
    }
  }
  .files-edit-field-container--inline {
    display: flex;
    flex-direction: row;
    align-items: center;
    gap: 5px;
    .files-edit-field-static-label {
      font-weight: 700;
      font-size: 14px;
      line-height: 21px;
    }
    .file-edit-field-static-label--highlighted {
      color: #eb5757;
    }
    .file-edit-field-static-label--link {
      cursor: pointer;
    }
  }
</style>
