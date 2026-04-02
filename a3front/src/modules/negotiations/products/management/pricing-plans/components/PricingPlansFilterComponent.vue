<template>
  <div class="filter-bar">
    <a-row :gutter="16" align="middle">
      <a-col>
        <a-select size="large" placeholder="Ciudad" class="filter-select" />
      </a-col>

      <a-col>
        <a-select size="large" placeholder="Tipo de servicio" class="filter-select" />
      </a-col>

      <a-col>
        <a-select size="large" placeholder="Estado de tarifa" class="filter-select" />
      </a-col>

      <a-col flex="auto" />

      <a-col>
        <a-input class="input-search" placeholder="Buscar proveedor o producto">
          <template #suffix>
            <font-awesome-icon :icon="['fas', 'magnifying-glass']" class="icon" />
          </template>
        </a-input>
      </a-col>
    </a-row>
  </div>
</template>

<script setup lang="ts"></script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .filter-bar {
    margin-bottom: 16px;
  }

  .filter-select {
    width: 200px;

    :deep(.ant-select-selector) {
      border-radius: 4px;
    }
  }

  .input-search {
    width: 308px;
    height: 40px;

    :deep(.ant-input) {
      border-radius: 4px;
    }

    .icon {
      color: $color-black-4;
    }

    :deep(.ant-input:hover),
    :deep(.ant-input:focus) {
      box-shadow: none;
    }
  }
</style>
