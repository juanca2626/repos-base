<template>
  <component :is="currentLayout">
    <div>
      <supplier-list-navigation-tab-component />
    </div>
    <div class="supplier-layout-content">
      <div class="module-negotiations supplier-list">
        <div class="main-header-bar">
          <div class="justify-header">
            <div>
              <list-title-component />
            </div>
            <div>
              <a-button
                type="primary"
                class="button-new-supplier"
                @click.prevent="handleNewSupplier"
              >
                <font-awesome-icon :icon="['fas', 'plus']" />
                Crear nuevo proveedor
              </a-button>
            </div>
          </div>
          <div>
            <a-divider type="horizontal" />
          </div>
        </div>
      </div>
      <router-view :key="routeKey"></router-view>
    </div>
  </component>
</template>

<script setup lang="ts">
  import { computed } from 'vue';
  import { useRoute } from 'vue-router';
  import { useSupplier } from '@/modules/negotiations/suppliers/composables/supplier.composable';
  import ListTitleComponent from '@/modules/negotiations/suppliers/components/partials/list-title-component.vue';
  import SupplierListNavigationTabComponent from '@/modules/negotiations/suppliers/components/supplier-list-navigation-tab-component.vue';

  const route = useRoute();

  const currentLayout = computed(() => {
    return route.meta.layout || 'div';
  });

  const routeKey = computed(() => {
    const id = route.params.id;
    return Array.isArray(id) ? id.join('-') : String(id ?? 'no-id');
  });

  const { handleNewSupplier } = useSupplier();
</script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .supplier-list {
    .main-header-bar {
      min-height: 72px;

      .justify-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px 24px;
        flex-wrap: wrap;
      }

      .button-new-supplier {
        font-size: 16px !important;
        height: 48px;
        gap: 8px;
        padding-inline: 20px;
        background: $color-white;
        color: #2f353a;
        border: 1px solid $color-black;
        box-shadow: none !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        max-width: 100%;
        white-space: nowrap;

        &:hover {
          background: $color-white-2;
          border: 1px solid $color-black;
        }
      }
    }
  }

  .supplier-layout-content {
    padding: clamp(24px, 3vw, 40px);
  }

  @media (max-width: 1200px) {
    .supplier-list {
      .main-header-bar {
        .button-new-supplier {
          padding-inline: 16px;
        }
      }
    }
  }

  @media (max-width: 992px) {
    .supplier-layout-content {
      padding: 24px 20px;
    }

    .supplier-list {
      .main-header-bar {
        .button-new-supplier {
          width: 100%;
          white-space: normal;
        }
      }
    }
  }
</style>
