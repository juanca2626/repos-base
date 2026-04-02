<template>
  <svg :width="width" :height="height" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
    <path
      d="M12.5 2.2a10.3 10.3 0 1 0 10.3 10.3A10.299 10.299 0 0 0 12.5 2.2z"
      :fill="fillCircle"
    />
    <path
      d="M12 7h1v7h-1zm.5 10.5a1 1 0 1 1 1-1 1.002 1.002 0 0 1-1 1z"
      :fill="fillExclamation"
      stroke="#FFFFFF"
      stroke-width="0.4"
    />
    <path fill="none" d="M0 0h24v24H0z" />
  </svg>
</template>

<script setup lang="ts">
  defineProps({
    width: {
      type: Number,
      default: 20,
    },
    height: {
      type: Number,
      default: 20,
    },
    fillCircle: {
      type: String,
      default: '#2f353a',
    },
    fillExclamation: {
      type: String,
      default: '#FFFFFF',
    },
  });
</script>

<style></style>
