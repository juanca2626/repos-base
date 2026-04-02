<template>
  <a-popover trigger="click" placement="bottom" :arrow="false">
    <template #content>
      <div class="item-order">
        <font-awesome-icon icon="fa-solid fa-arrow-down-a-z" class="order-arrow-z-a-icon" />
        <span class="item-text"> Odernar de A a Z </span>
      </div>
      <div class="item-order">
        <font-awesome-icon icon="fa-solid fa-arrow-up-z-a" class="order-arrow-z-a-icon" />
        <span class="item-text"> Odernar de Z a A </span>
      </div>
      <div class="item-order item-order-space-between">
        <span class="item-text"> Filtros de texto </span>
        <font-awesome-icon
          :icon="['fas', 'fa-chevron-right']"
          class="chevron-right-icon cursor-pointer"
        />
      </div>
    </template>
    <div class="custom-header-container" :class="{ 'justify-center': alignCenter }">
      <span>
        {{ columnTitle }}
      </span>
      <font-awesome-icon :icon="['fas', 'fa-chevron-down']" class="order-icon cursor-pointer" />
    </div>
  </a-popover>
</template>
<script setup lang="ts">
  defineProps({
    columnTitle: {
      type: String,
      required: true,
    },
    alignCenter: {
      type: Boolean,
      required: false,
      default: false,
    },
  });
</script>
<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .custom-header-container {
    display: flex;
    align-items: center;
    gap: 8px;

    .order-icon {
      color: $color-white;
      font-size: 12px;
    }
  }

  .justify-center {
    justify-content: center;
  }

  .item-order {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 8px;
    height: 48px !important;
    width: 250px !important;
    cursor: pointer;

    &-space-between {
      justify-content: space-between;
    }

    .item-text {
      font-size: 16px;
      font-weight: 400;
      color: $color-black;
    }

    .order-arrow-z-a-icon {
      font-size: 16px;
    }

    .chevron-right-icon {
      color: $color-black-3;
      font-size: 14px;
    }
  }
</style>
