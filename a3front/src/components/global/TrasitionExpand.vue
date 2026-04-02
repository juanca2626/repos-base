<template>
  <transition name="expand">
    <div v-show="isExpanded" ref="content">
      <slot />
    </div>
  </transition>
</template>

<script setup>
  import { onMounted, ref } from 'vue';

  defineProps({ isExpanded: Boolean });
  const content = ref();
  let height = ref();

  onMounted(() => {
    height.value = `${content.value.getBoundingClientRect().height}px`;
  });
</script>
<style scoped lang="sass">
  .expand-leave-active,
  .expand-enter-active
    transition: all 350ms ease
    overflow: hidden
  .expand-enter-to,
  .expand-leave-from
    height: v-bind(height)
  .expand-enter-from,
  .expand-leave-to
    opacity: 0
    height: 0
</style>
