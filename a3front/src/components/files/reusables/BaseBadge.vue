<template>
  <div class="base-badge" :class="`base-badge--${type} ${isBlock ? 'base-badge-is-block' : ''}`">
    <slot />
  </div>
</template>

<script setup>
  defineProps({
    type: {
      type: String,
      default: 'default',
      validator(typeValue) {
        return [
          '',
          'default',
          'primary',
          'secondary',
          'success',
          'error',
          'trends',
          'billed',
          'unbilled',
          'white',
        ].includes(typeValue);
      },
    },
    isBlock: {
      type: Boolean,
      default: false,
    },
  });
</script>

<style scoped lang="scss">
  .base-badge {
    display: inline-flex;
    justify-content: center;
    align-items: center;
    padding: 3px 10px;
    gap: 5px;
    color: #fff;
    background: var(--files-default-dark);
    // box-sshadow: 1px 1px 4px rgba(0, 0, 0, 0.25);
    border-radius: 6px;
    font-size: 0.55rem;
    font-weight: 700;
    letter-spacing: 0.015em;
    line-height: 1rem;
    // cursor: pointer;

    &-is-block {
      width: 100%;
    }

    &--secondary,
    &--secondary-false,
    &--secondary-true {
      // background: var(--files-gastronomy);
      color: #4f4b4b;
      background: #e9e9e9;
    }
    &--primary,
    &--primary-false {
      // background: var(--files-long-scape);
      color: #fff;
      background: #979797;
    }
    &--primary-true {
      background: var(--files-green);
      color: #fff;
    }
    &--success,
    &--green {
      background: var(--files-success-1);
    }
    &--error,
    &--red {
      background: var(--files-main-color);
    }
    &--light {
      background: #e9e9e9;
      color: #4f4b4b;
    }
    &--trends {
      background-color: var(--files-tag-trends);
      opacity: 1;
    }
    &--purple {
      background-color: var(--files-tag-purple);
      opacity: 1;
    }
    &--volcano {
      background-color: var(--files-volcano);
      opacity: 1;
    }
    &--billed {
      background: var(--files-state-billed);
      color: var(--files-black-2);
      opacity: 1;
    }
    &--unbilled {
      background: var(--files-background-2);
      color: var(--files-black-2);
      opacity: 1;
    }
    &--white {
      background: transparent;
      color: var(--files-black-2);
      opacity: 1;
    }
  }
</style>
