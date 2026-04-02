<template>
  <div>
    <div class="container-form">
      <div>
        <span> Registro del producto </span>
        <div>
          <div class="progress-item">10% Completado</div>
          <div>
            <div class="progress-bar-container">
              <div class="progress-bar" :style="{ width: 11 + '%' }"></div>
            </div>
          </div>
        </div>

        <a-divider style="width: 2px; background-color: #eb5757" />
        <div>
          <span> Información general </span>
        </div>

        <div class="sub-item-collapse-component">
          <a-collapse ghost expand-icon-position="end">
            <a-collapse-panel
              v-for="item in items"
              :key="item.key"
              :class="item.isActive ? 'is-active' : 'is-disabled'"
              collapsible=" icon"
            >
              <template v-slot:header>
                <div class="item-complete cursor-pointer">
                  <div>
                    <font-awesome-icon
                      :icon="['fas', 'circle-check']"
                      :class="item.isComplete ? 'icon-complete' : 'icon-incomplete'"
                    />
                    <span class="title-item">{{ item.title }}</span>
                  </div>
                </div>
              </template>
            </a-collapse-panel>
          </a-collapse>
        </div>
      </div>
      <div>
        <span class="title-form"> Creación de producto genérico </span>
      </div>
    </div>
  </div>
</template>
<script lang="ts" setup>
  import { ref } from 'vue';

  const items = ref<any[]>([
    {
      key: 1,
      isActive: false,
      isComplete: false,
      title: 'Información general',
    },
    {
      key: 2,
      isActive: true,
      isComplete: true,
      title: 'Proveedores',
    },
  ]);
</script>
<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .title-form {
    font-weight: 700;
    font-size: 24px;
    color: $color-black;
  }

  .container-form {
    display: grid;
    grid-template-columns: 20% 80%;
  }

  // menu
  .icon-complete {
    color: #288a5f !important;
  }

  .icon-incomplete {
    color: #cad0cd !important;
  }

  .progress-item {
    font-size: 12px;
    line-height: 16px;
    color: #2f353a;
    margin-bottom: 4px;
  }

  .progress-bar-container {
    width: 100%;
    background-color: #e1edf9;
    border-radius: 4px;
    overflow: hidden;
    height: 8px;

    .progress-bar {
      height: 100%;
      background-color: #1284ed;
      width: 0;
      transition: width 0.3s ease;
    }
  }

  .title-item {
    font-size: 16px;
    line-height: 24px;
    color: #212121;
    margin-bottom: 8px;
  }

  .item-collapse-component {
    .is-active {
      .ant-collapse-header {
        border-left: 4px solid #bd0d12;
        border-radius: 0 !important;
        background: #e4e4e4;
      }
    }

    .item-complete {
      display: flex;
      gap: 8px;

      svg {
        color: #cad0cd;
      }

      .title-item {
        font-weight: 600;
        font-size: 12px;
        letter-spacing: 0;
        margin-bottom: 0 !important;
      }
    }
  }
</style>
