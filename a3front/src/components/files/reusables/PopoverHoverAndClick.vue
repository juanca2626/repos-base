<template>
  <BasePopover
    trigger="hover"
    :placement="placementHover"
    :visible="hideContentHover"
    :open="hovered"
    @visibleChange="handleHoverChange"
  >
    <template #content>
      <slot v-if="props?.data" name="content-hover" v-bind="props.data"></slot>
    </template>
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
          <slot v-if="props?.data" name="content-click" v-bind="props.data"></slot>
          <slot name="content-buttons" v-if="props.buttons">
            <div
              style="
                display: flex;
                justify-content: flex-end;
                gap: 10px;
                margin-bottom: 30px;
                margin-right: 10px;
              "
            >
              <base-button
                width="60"
                size="large"
                type="text"
                class="btn-default"
                @click="handleCancel"
              >
                {{ t('global.button.close') }}
              </base-button>
              <base-button :disabled="!props?.data" width="60" size="large" @click="handleSave">
                {{ props.contentButtonSave ?? t('global.button.save') }}
              </base-button>
            </div>
          </slot>
        </div>
      </template>
    </BasePopover>
  </BasePopover>
</template>

<script setup>
  import { ref, watch } from 'vue';

  import BaseButton from './BaseButton.vue';
  import BasePopover from './BasePopover.vue';

  import { useI18n } from 'vue-i18n';
  const { t } = useI18n({
    useScope: 'global',
  });

  const clicked = ref(false);
  const hovered = ref(false);

  const emit = defineEmits(['onSave', 'onCancel', 'onPopoverClick']);

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
    buttons: {
      type: Boolean,
      default: () => true,
    },
    hideContentHover: {
      type: Boolean,
      default: () => false,
    },
    editable: {
      type: Boolean,
      default: () => true,
    },
    contentButtonSave: {
      type: String,
      default: null,
    },
    hideSave: {
      type: Boolean,
      default: true,
    },
    visible: {
      type: Boolean,
      default: true,
    },
  });

  const hide = () => {
    clicked.value = false;
    hovered.value = false;
  };

  const handleHoverChange = (visible) => {
    //clicked.value = false
    if (!props.hideContentHover) {
      hovered.value = visible;
    }
  };

  const handleClickChange = (visible) => {
    if (props.editable) {
      clicked.value = visible;
      hovered.value = false;
      emit('onPopoverClick');
    }
  };

  const handleCancel = () => {
    hide();
    emit('onCancel');
  };
  const handleSave = () => {
    emit('onSave');
    if (props.hideSave) {
      hide();
    }
  };

  watch(
    () => props.visible,
    (newVal) => {
      if (newVal !== undefined) {
        clicked.value = newVal;
        hovered.value = newVal;
      }
    }
  );

  watch([clicked, hovered], ([newClicked, newHovered]) => {
    if (props.visible === undefined) {
      // Solo emitir si no estamos controlando desde el padre
      emit('update:visible', newClicked || newHovered);
    }
  });
  defineExpose({ hide });
</script>

<style scoped lang="scss">
  .content-click {
    overflow-y: auto;
    overflow-x: hidden;
  }
</style>
