<template>
  <div class="alert-component">
    <div :class="props.type" class="content">
      <font-awesome-icon :icon="props.icon" />
      <span>{{ props.text }}</span>
    </div>
  </div>
</template>
<script lang="ts" setup>
  import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

  enum AlertType {
    alert = 'alert',
    danger = 'danger',
  }

  interface Props {
    icon: string;
    text: string;
    type: AlertType;
  }

  const props = defineProps<Props>();
</script>

<style lang="sass">
  .alert-component
    display: flex
    flex-direction: column
    align-items: center
    gap: 10px

    .content
      display: flex
      padding: 16px
      align-items: center
      gap: 10px
      align-self: stretch
      border-radius: 4px

      span
        font-size: 16px
        font-style: normal
        font-weight: 400
        line-height: 23px
        letter-spacing: -0.24px

    .alert.content
      border: 0.5px solid #FC0
      background: #FFFBDB
      color: #E4B804
</style>
