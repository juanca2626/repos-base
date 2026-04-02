<template>
  <a-menu
    class="files-sidebar-menu"
    mode="horizontal"
    :selectable="false"
    v-model:openKeys="openKeys"
    v-model:selectedKeys="selectedKeys"
  >
    <a-menu-item-group v-for="menuItem in menuItems" :key="menuItem.id">
      <template #title>
        <span
          class="files-sidebar-item"
          :class="{
            'files-sidebar-item--active': route.name === menuItem.routeName,
          }"
          @click="handleGoTo(menuItem.routeName)"
        >
          {{ menuItem.title }}
        </span>
      </template>
    </a-menu-item-group>
  </a-menu>
</template>

<script setup>
  import { ref } from 'vue';
  import { useRoute, useRouter } from 'vue-router';

  const openKeys = ref(['']);
  const selectedKeys = ref(['']);

  const menuItems = [
    {
      id: 'group-item-1',
      title: 'Programa',
      routeName: 'files-services',
    },
    {
      id: 'group-item-2',
      title: 'Pasajeros',
      routeName: 'files-paxs',
    },
    {
      id: 'group-item-3',
      title: 'Vuelos',
      routeName: 'files-flights',
    },
    {
      id: 'group-item-4',
      title: 'Statement',
      routeName: 'files-statement',
    },
    /*
    {
      id: 'group-item-5',
      title: 'Cotización',
      routeName: 'files-quote',
    },*/
  ];

  const route = useRoute();
  const router = useRouter();

  const handleGoTo = (name) => {
    const { params } = route;
    router.replace({ name, params });
  };
</script>

<style scoped lang="scss">
  .files-sidebar-menu {
    background-color: transparent;
    margin-bottom: 2.625rem; // 42px

    :deep(.ant-menu-item-group-title) {
      padding-bottom: 0px;
    }
  }
  .files-sidebar-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    font-weight: 700;
    font-size: 16px;
    line-height: 18px;
    letter-spacing: -0.015em;
    color: #575757;
    padding: 10px;
    cursor: pointer;

    &__icon {
      color: #eb5757;
    }

    &-subitem {
      padding-left: 1.6rem;

      &--active {
        border-radius: 8px 0 0 8px;
        background-color: #fff;
        padding-right: 50px;
        padding-top: 10px;
        padding-bottom: 10px;
      }
    }

    &--active {
      border-bottom: 2.5px solid #eb5757;
      color: #eb5757;
      padding: 10px;
    }
  }
  .files-layout-sider {
    background-color: var(--files-default-light);

    :deep(.ant-menu-item-group-title) {
      padding: 8px 0 0 16px;
      margin-right: -1px;
    }
  }

  .files-notification-alert {
    padding: 20px;
  }
</style>
