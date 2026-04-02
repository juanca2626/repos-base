<template>
  <button class="button-add-filter" @click="$emit('addFilter')">
    <font-awesome-icon :icon="['fas', 'filter']" /> Todos los filtros
  </button>
</template>
<script setup lang="ts"></script>
<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .button-add-filter {
    width: 166px;
    height: 48px;
    gap: 8px;
    border-radius: 4px;
    padding: 8px 16px;
    border: none;
    color: $color-blue;
    background-color: transparent;
    text-decoration: underline;
    display: flex;
    justify-content: center;
    justify-items: center;
    align-items: center;
    transition: all 0.2s ease-in-out;
    cursor: pointer;
    margin-left: auto;

    &:hover {
      background: transparent;
    }
  }

  // Media query para pantallas menores a 1280px
  @media (max-width: 1280px) {
    .button-add-filter {
      width: 150px;
      height: 42px;
      padding: 6px 12px;
      font-size: 13px;
    }
  }
</style>
