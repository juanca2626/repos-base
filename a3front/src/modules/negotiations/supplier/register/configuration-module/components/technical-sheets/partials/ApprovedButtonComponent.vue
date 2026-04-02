<template>
  <a-button
    type="primary"
    class="custom-document-review-buttons ant-btn-md w-100"
    :disabled="disabled"
    @click="emit('onApproved')"
  >
    <div class="container-buttons">
      <font-awesome-icon :icon="['fas', 'circle-check']" class="icon-button icon-button-approved" />
      <span class="text-button text-button-approved"> Aprobar </span>
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

  const emit = defineEmits(['onApproved']);
</script>

<style scoped lang="scss"></style>
