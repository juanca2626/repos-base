<template>
  <div
    class="editable-field-wrapper"
    @mouseenter="isHovered = true"
    @mouseleave="isHovered = false"
  >
    <a-flex gap="small" align="center" justify="center" horizontal>
      <slot></slot>
      <a-tooltip v-if="isHovered" title="Editar">
        <font-awesome-icon
          :icon="['fas', 'pen-to-square']"
          class="edit-icon"
          @click.stop="handleClick"
        />
      </a-tooltip>
    </a-flex>
  </div>
</template>

<script setup>
  import { ref } from 'vue';

  const emit = defineEmits(['click']);

  const isHovered = ref(false);

  const handleClick = () => {
    emit('click');
  };
</script>

<style scoped>
  .editable-field-wrapper {
    cursor: pointer;
    padding: 5px;
    display: inline-flex;
    align-items: center;
    width: 100%;
    justify-content: center;
  }

  .edit-icon {
    margin-left: 8px;
    cursor: pointer;
    color: #3d3d3d;
    transition: color 0.3s;
  }

  .edit-icon:hover {
    color: #d80404;
  }
</style>
