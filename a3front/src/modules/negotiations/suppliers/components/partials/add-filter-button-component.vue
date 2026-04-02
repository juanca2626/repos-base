<template>
  <button class="button-add-filter" @click="$emit('addFilter')">
    <font-awesome-icon :icon="['fas', 'filter']" /> Añadir filtro
  </button>
</template>
<script setup lang="ts"></script>
<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .button-add-filter {
    width: auto;
    min-width: 166px;
    max-width: 100%;
    height: 48px;
    gap: 8px;
    border-radius: 4px;
    padding: 8px 16px;
    border: 1px solid $color-blue;
    color: $color-blue;
    background-color: $color-white;
    text-decoration: underline;
    display: flex;
    justify-content: center;
    justify-items: center;
    align-items: center;
    transition: all 0.2s ease-in-out;
    cursor: pointer;

    &:hover {
      background: $color-white-2;
    }
  }

  @media (max-width: 1200px) {
    .button-add-filter {
      min-width: 140px;
      padding-inline: 14px;
    }
  }

  @media (max-width: 768px) {
    .button-add-filter {
      width: 100%;
      min-width: 0;
    }
  }
</style>
