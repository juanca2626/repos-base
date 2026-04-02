<template>
  <div class="link-icon-component">
    <font-awesome-icon :icon="props.icon" />
    <span>{{ props.text }}</span>
  </div>
</template>

<script lang="ts" setup>
  import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

  interface Props {
    icon: string;
    text: string;
  }

  const props = withDefaults(defineProps<Props>(), {
    icon: 'circle-info',
  });
</script>

<style lang="sass">
  .link-icon-component
    display: flex
    align-items: center
    gap: 4px
    color: #EB5757

    span
      color: #EB5757
      text-align: right
      font-size: 18px
      font-style: normal
      font-weight: 700
      line-height: 30px
      letter-spacing: -0.27px
      text-decoration-line: underline
</style>
