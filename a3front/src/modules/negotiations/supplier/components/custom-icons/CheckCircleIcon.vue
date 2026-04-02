<template>
  <div class="circle-container" :style="{ width: widthCircle, height: heightCircle }">
    <div class="circle" :style="{ width: widthCircle, height: heightCircle }">
      <font-awesome-icon
        class="check-icon"
        :icon="['fa', 'check']"
        :style="{ fontSize: iconSize }"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
  defineProps({
    widthCircle: {
      type: String,
      default: '30px',
    },
    heightCircle: {
      type: String,
      default: '30px',
    },
    iconSize: {
      type: String,
      default: '20px',
    },
  });
</script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .circle-container {
    display: flex;
    justify-content: center;
    align-items: center;
  }

  .circle {
    border-radius: 50%;
    background-color: $color-info-light;
    display: flex;
    justify-content: center;
    align-items: center;
  }

  .check-icon {
    color: $color-info-dark;
  }
</style>
