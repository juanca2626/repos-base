<template>
  <a-config-provider :theme="spinTheme">
    <a-spin class="sping-global-component" :spinning="spinning">
      <slot />
    </a-spin>
  </a-config-provider>
</template>

<script setup lang="ts">
  defineOptions({
    name: 'SpinGlobalComponent',
  });

  defineProps({
    // tip se declara para interceptar el fallthrough hacia a-spin pero no se usa
    tip: {
      type: String,
      default: '',
    },
    spinning: {
      type: Boolean,
      required: true,
    },
  });

  const spinTheme = {
    components: {
      Spin: {
        colorPrimary: '#acaeb0',
      },
    },
  };
</script>

<style lang="scss">
  .sping-global-component {
    border-radius: 4px;
    max-height: 600px !important;
  }
</style>
