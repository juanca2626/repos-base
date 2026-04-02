<template>
  <div class="suggestion-container">
    <span class="suggestion-message">
      {{ suggestionMessage }}
    </span>
  </div>
</template>
<script setup lang="ts">
  defineProps({
    suggestionMessage: {
      type: String,
      required: true,
    },
  });
</script>
<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .suggestion-container {
    padding: 4px 8px 0px 8px;
    .suggestion-message {
      color: $color-black-3;
      font-weight: 400;
      font-size: 14px;
    }
  }
</style>
