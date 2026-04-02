<template>
  <a-tag color="#C4C4C4" class="lang-tag" style="padding: 0 5px; height: 19px; line-height: 17px">
    <span class="lang-text">{{ lang }}</span>
  </a-tag>
</template>

<script setup lang="ts">
  defineProps<{
    lang: string;
  }>();
</script>

<style scoped>
  .lang-tag {
    display: inline-flex;
    justify-content: center;
    align-items: center;
    border-radius: 6px;
    background: #c4c4c4;
  }

  .lang-text {
    color: white;
    font-size: 12px;
    font-family: Montserrat, sans-serif;
    font-weight: 600;
    letter-spacing: 0.18px;
    text-align: center;
  }
</style>
