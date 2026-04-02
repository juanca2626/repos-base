<template>
  <BasePopover
    style="padding: 0"
    trigger="click"
    :placement="placementClick"
    :visible="clicked"
    @visibleChange="handleClickChange"
  >
    <slot v-if="props?.data" v-bind="props.data">Action</slot>
    <template #content>
      <div class="content-click">
        <slot v-if="props?.data" name="content-click" v-bind="props.data">Click content</slot>
        <slot name="content-buttons">
          <div style="display: flex; justify-content: flex-end; gap: 10px; margin-bottom: 30px">
            <base-button width="60" size="large" type="text" @click="handleCancel"
              >Cancelar</base-button
            >
            <base-button :disabled="!props?.data" width="60" size="large" @click="handleSave"
              >Guardar</base-button
            >
          </div>
        </slot>
      </div>
    </template>
  </BasePopover>
</template>

<script setup>
  import { ref } from 'vue';

  import BaseButton from './BaseButton.vue';
  import BasePopover from './BasePopover.vue';

  const clicked = ref(false);
  const hovered = ref(false);

  const emit = defineEmits(['onPopoverClick']);

  const props = defineProps({
    data: {
      type: Object,
      default: () => ({}),
    },
    placementHover: {
      type: String,
      default: 'top',
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
    placementClick: {
      type: String,
      default: 'left',
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
    hideContentHover: {
      type: Boolean,
      default: false,
    },
  });

  const hide = () => {
    clicked.value = false;
    hovered.value = false;
  };

  const handleClickChange = (visible) => {
    clicked.value = visible;
    hovered.value = false;
    emit('onPopoverClick');
  };

  const handleCancel = () => {
    hide();
    emit('onCancel');
  };
  const handleSave = () => {
    emit('onSave');
    hide();
  };
</script>

<style scoped lang="scss">
  .content-click {
    overflow-y: auto;
    overflow-x: hidden;
  }
</style>
