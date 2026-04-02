<template>
  <span class="d-flex ps-2 text-success cursor-pointer">
    <a-tooltip>
      <template #title>
        <small class="text-uppercase">{{ text }}</small>
      </template>
      <img
        style="width: 100%; height: 100%; object-fit: cover; opacity: 0.8"
        class="d-flex"
        src="https://cdn.prod.website-files.com/63dcd07c76cffb83ef457ea8/63dcd07c76cffb5e63458041_Favicon%20Hyperguest.png"
      />
    </a-tooltip>
  </span>
</template>

<script setup>
  defineProps({
    text: {
      type: String,
      default: '',
    },
  });
</script>
