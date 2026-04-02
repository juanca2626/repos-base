<template>
  <div class="a-collapse">
    <div class="a-collapse__title" @click="showContent()">
      <span>{{ props.title }}</span>
      <font-awesome-icon class="i" :icon="getIcon()" />
    </div>
    <transition-expand :isExpanded="show">
      <div class="a-collapse__content">
        <slot />
      </div>
    </transition-expand>
  </div>
</template>

<script setup>
  import TransitionExpand from './TrasitionExpand.vue';
  import { ref } from 'vue';
  const props = defineProps({
    title: {
      type: String,
    },
    value: {
      type: Boolean,
      default: true,
    },
  });
  const emit = defineEmits(['input']);

  const show = ref(props.value);
  const getIcon = () => (show.value ? 'fa-solid fa-minus' : 'fa-solid fa-plus');
  const showContent = () => {
    emit('input', !show.value);
    show.value = !show.value;
  };
</script>

<style lang="sass" scoped>
  @import '@/scss/_variables.scss'
  .a-collapse
    width: 100%
    text-align: left
    border: 1px solid $color-white-3
    overflow: hidden
    border-radius: 6px
    &__title
      background-color: $color-white-3
      min-height: 50px
      display: flex
      justify-content: space-between
      align-items: center
      padding: 0 24px
      cursor: pointer
    &__content
      min-height: 50px
      padding: 15px 24px 30px
</style>
