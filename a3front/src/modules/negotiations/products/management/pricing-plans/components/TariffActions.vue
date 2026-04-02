<template>
  <div class="actions">
    <font-awesome-icon :icon="['fas', 'magnifying-glass']" class="action-icon" />

    <font-awesome-icon :icon="['fas', 'pen-to-square']" class="action-icon" />

    <font-awesome-icon :icon="['fas', 'copy']" class="action-icon" />
  </div>
</template>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .actions {
    display: flex;
    gap: 16px;
  }

  .action-icon {
    color: $color-primary;
    cursor: pointer;
  }
</style>
