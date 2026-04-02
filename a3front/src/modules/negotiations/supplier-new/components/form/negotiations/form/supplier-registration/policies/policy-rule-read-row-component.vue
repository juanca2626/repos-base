<template>
  <div class="container-bordered" v-if="isMultiple">
    <div class="form-row-container">
      <span class="text-form">
        <slot name="text"></slot>
      </span>
      <font-awesome-icon
        :icon="['far', 'pen-to-square']"
        class="row-edit-icon"
        @click="emit('onEdit')"
      />
    </div>
  </div>
  <div class="container-bordered container-relative" v-else>
    <font-awesome-icon
      :icon="['far', 'pen-to-square']"
      class="row-edit-icon row-edit-icon-absolute"
      @click="emit('onEdit')"
    />
    <div>
      <slot name="text"></slot>
    </div>
  </div>
</template>

<script setup lang="ts">
  import { defineProps } from 'vue';

  defineProps({
    isMultiple: {
      type: Boolean,
      required: true,
    },
  });

  const emit = defineEmits(['onEdit']);
</script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .container-relative {
    position: relative;
  }

  .container-bordered {
    border: 1px solid $color-black-4;
    padding: 24px;
    border-radius: 8px;
  }

  .form-row-container {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
  }

  .row-edit-icon {
    top: 24px;
    right: 24px;
    font-size: 18px;
    cursor: pointer;
    transition: color 0.2s;
    color: $color-blue;

    &-absolute {
      position: absolute;
    }
  }

  .text-form {
    font-size: 16px;
    font-weight: 400;
    color: $color-black;
  }

  .text-bold {
    font-weight: bold;
  }
</style>
