<template>
  <div class="maca-wrapper">
    <a-spin :spinning="true" wrapperClassName="maca-spin-custom">
      <template #indicator>
        <div class="maca-indicator-anchor">
          <LoadingMaca :size="rows > 2 ? 'default' : 'small'" />
        </div>
      </template>

      <a-skeleton active :paragraph="{ rows }" :title="rows > 2" :class="props.class" />
    </a-spin>
  </div>
</template>

<script setup>
  import LoadingMaca from './LoadingMaca.vue';
  const props = defineProps({
    rows: { type: Number, default: 3 },
    class: { type: String, default: 'my-3' },
  });
</script>

<style scoped>
  .maca-wrapper {
    position: relative;
    width: 100%;
  }

  :deep(.maca-spin-custom) {
    position: relative !important;
    display: block;
  }

  .maca-indicator-anchor {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 1000;
    display: flex;
    justify-content: center;
    align-items: center;
  }

  :deep(.maca-spin-custom .ant-spin) {
    position: static !important;
    margin: 0 !important;
    max-height: none !important;
  }

  :deep(.maca-spin-custom .ant-spin-dot) {
    width: auto !important;
    height: auto !important;
    font-size: 0;
  }
</style>
