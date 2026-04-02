<template>
  <div
    :class="[props.type, { disabled: props.disabled }]"
    class="button-component"
    @click="handleClick"
  >
    <div v-if="props.loading" class="icon">
      <font-awesome-icon :icon="['fas', 'spinner']" spin-pulse />
    </div>
    <div v-if="props.icon" class="icon">
      <font-awesome-icon :icon="props.icon" />
    </div>
    <slot></slot>
    <div v-if="props.afterIcon" class="icon">
      <font-awesome-icon :icon="props.afterIcon" />
    </div>
  </div>
</template>

<script lang="ts" setup>
  import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

  interface Props {
    type?: string;
    icon?: string;
    afterIcon?: string;
    loading?: boolean;
    disabled?: boolean;
  }

  const props = withDefaults(defineProps<Props>(), {
    type: 'normal',
    disabled: false,
  });

  const emit = defineEmits(['click']);

  const handleClick = (event: MouseEvent) => {
    if (props.disabled) {
      event.preventDefault();
      event.stopPropagation();
      return;
    }
    emit('click', event);
  };
</script>

<style lang="sass">
  .button-component
    border-radius: 6px
    text-align: center
    font-size: 16px
    font-style: normal
    font-weight: 500
    line-height: normal
    letter-spacing: -0.255px
    min-width: 170px
    padding: 15px 0
    border: 1px solid #EB5757 !important
    display: flex
    justify-content: center
    gap: 5px

    .icon
      font-size: 17px

    &.disabled
      background-color: #F9CDCD!important
      color: #FFDFDF!important
      border-color: transparent!important
      pointer-events: none!important

    &.normal
      background: #EB5757
      color: #FFF

    &.outline
      background: #FFF
      color: #EB5757

      &:hover
        background: rgba(255, 225, 225, 0.4)
</style>
