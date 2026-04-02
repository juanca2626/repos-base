<template>
  <iframe v-if="html != ''" style="border: 0" :srcdoc="html" width="100%" height="1500"></iframe>
</template>

<script setup>
  defineProps({
    html: {
      type: String,
      default: '',
    },
  });
</script>
