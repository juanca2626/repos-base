<template>
  <a-button
    type="primary"
    class="custom-document-review-buttons btn-secondary ant-btn-md w-100"
    :disabled="disabled"
    @click="emit('onRejected')"
  >
    <div class="container-buttons">
      <font-awesome-icon :icon="['fas', 'circle-xmark']" class="icon-button icon-button-rejected" />
      <span class="text-button text-button-rejected"> Rechazar </span>
    </div>
  </a-button>
</template>
<script setup lang="ts">
  defineProps({
    disabled: {
      type: Boolean,
      required: true,
    },
  });

  const emit = defineEmits(['onRejected']);
</script>

<style scoped lang="scss"></style>
