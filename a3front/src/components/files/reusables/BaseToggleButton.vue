<template>
  <span class="base-toggle-button">
    <font-awesome-icon
      v-if="isActived"
      class="is-vip"
      icon="fa-solid fa-star"
      @click="toggleButton(false)"
    />
    <font-awesome-icon v-else icon="fa-regular fa-star" @click="toggleButton(true)" />
  </span>
</template>

<script setup>
  import { ref } from 'vue';

  const isActived = ref(false);

  defineProps({
    isActive: {
      type: Boolean,
      default: false,
    },
  });

  const toggleButton = (value) => {
    isActived.value = value;
  };
</script>

<style scoped lang="scss">
  .base-toggle-button {
    cursor: pointer;
    transition: 0.3s ease all;
    .is-vip {
      color: var(--files-exclusives);
    }

    &:hover {
      color: var(--files-exclusives);
    }
  }
</style>
