<template>
  <a-modal
    v-model:open="visible"
    :footer="null"
    :mask="false"
    :closable="true"
    :style="modalStyle"
    wrapClassName="draggable-modal"
  >
    <div class="drag-handle" @mousedown="startDrag">
      <slot name="title">
        <strong>Modal</strong>
      </slot>
    </div>

    <div class="modal-body">
      <slot />
    </div>
  </a-modal>
</template>

<script setup lang="ts">
  import { ref, reactive, computed, onUnmounted } from 'vue';

  const visible = defineModel<boolean>('open', { default: false });

  const position = reactive({
    x: 100,
    y: 100,
  });

  const dragging = ref(false);
  const offset = reactive({ x: 0, y: 0 });

  const modalStyle = computed(() => ({
    position: 'fixed',
    left: `${position.x}px`,
    top: `${position.y}px`,
  }));

  const startDrag = (e: MouseEvent) => {
    dragging.value = true;
    offset.x = e.clientX - position.x;
    offset.y = e.clientY - position.y;

    document.addEventListener('mousemove', onDrag);
    document.addEventListener('mouseup', stopDrag);
  };

  const onDrag = (e: MouseEvent) => {
    if (!dragging.value) return;

    position.x = e.clientX - offset.x;
    position.y = e.clientY - offset.y;
  };

  const stopDrag = () => {
    dragging.value = false;

    document.removeEventListener('mousemove', onDrag);
    document.removeEventListener('mouseup', stopDrag);
  };

  onUnmounted(() => {
    stopDrag();
  });
</script>

<style scoped>
  .drag-handle {
    cursor: move;
    padding: 8px;
    background: #f5f5f5;
    border-bottom: 1px solid #eee;
  }

  .modal-body {
    padding: 12px;
  }
</style>
