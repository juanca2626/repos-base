<template>
  <base-popover placement="left">
    <font-awesome-icon class="files-popover-add" icon="fa-regular fa-square-plus" />
    <template #content>
      <div class="files-popover-add-menu">
        <a-button class="files-popover-add-button" type="text" size="small">
          <template #icon>
            <font-awesome-icon icon="fa-solid fa-house" size="lg" />
          </template>
          Add Hotel
        </a-button>
        <a-button class="files-popover-add-button" type="text" size="small">
          <font-awesome-icon icon="fa-regular fa-square-plus" size="lg" />
          Add Servicio
        </a-button>
      </div>
    </template>
  </base-popover>
</template>

<script setup>
  import BasePopover from '@/components/files/reusables/BasePopover.vue';
</script>

<style scoped lang="scss">
  .files-popover-add {
    color: #eb5757;
    width: 37px;
    height: 38px;
    &-menu {
      display: flex;
      flex-direction: column;
    }
    &-button {
      display: flex;
      gap: 10px;
      align-items: center;
      font-weight: 700;
      font-size: 12px;
      line-height: 19px;
      letter-spacing: 0.015em;
      color: #eb5757;
    }
  }
</style>
