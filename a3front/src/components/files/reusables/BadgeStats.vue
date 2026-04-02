<template>
  <base-badge :type="type" :is-full-width="true">
    <span>
      <slot name="badge-title">x</slot>
    </span>
    <strong class="badge-stats--number">
      <slot name="badge-number">y</slot>
    </strong>
  </base-badge>
</template>

<script setup>
  import BaseBadge from './BaseBadge.vue';

  defineProps({ type: String });
</script>

<style scoped>
  .badge-stats--number {
    font-weight: 600;
    font-size: 0.9rem;
    line-height: 21px;
  }
</style>
