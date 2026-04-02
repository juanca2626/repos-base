<template>
  <a-menu
    v-model:selectedKeys="selectedKeys"
    mode="inline"
    theme="dark"
    :open-keys="openKeys"
    :selected-keys="selectedKeys"
    :subMenuOpenDelay="0"
    @openChange="onOpenChange"
    :inline-collapsed="collapsed"
  >
    <div v-for="menu in routesRecords" :key="'sub-' + menu.id">
      <!-- App -->
      <a-menu-item-group :key="menu.id + '0-0'" v-if="'lang' in menu">
        <div class="ant-menu-item-group-title" v-if="!collapsed">{{ menu.name }}</div>
      </a-menu-item-group>
      <!-- App -->
      <!-- Menu -->
      <a-menu
        v-model:selectedKeys="selectedKeys"
        mode="inline"
        theme="dark"
        :subMenuOpenDelay="0"
        :inline-collapsed="collapsed"
        @click="openItem(menu)"
      >
        <div v-for="group in menu.children" :key="'sub-' + menu.id + '-' + group.id">
          <a-menu-item
            @click="openItem(group)"
            :key="group.id + '0-0'"
            v-if="group.children.length === 0"
          >
            <template #icon>
              <font-awesome-icon :icon="['fas', group.icon]" />
            </template>
            <span>{{ group.name }}</span>
          </a-menu-item>
          <a-sub-menu
            class="sub-menu-group"
            :class="{ 'hide-arrow': collapsed }"
            :key="menu.id + '-' + group.id"
            :expandIcon="({ isOpen }) => renderExpandIcon(isOpen)"
            v-else
          >
            <template #icon>
              <font-awesome-icon :icon="['fas', group.icon]" />
            </template>
            <template #title>
              <span>{{ group.name }}</span>
            </template>
            <!-- sub menu -->
            <!-- Item "Cupos" agregado manualmente para Hoteles - aparece arriba de "Lista" -->
            <a-menu-item
              v-if="isHotelsGroup(group)"
              :key="menu.id + '-' + group.id + '-cupos'"
              @click="goToQuotasDashboard"
            >
              <template #icon>
                <font-awesome-icon :icon="['fas', 'calendar']" />
              </template>
              <span>Cupos</span>
            </a-menu-item>
            <a-menu-item
              v-for="sub in group.children"
              :key="menu.id + '-' + group.id + '-' + sub.id"
              @click="openSubItem(sub)"
            >
              <template #icon>
                <font-awesome-icon :icon="['fas', sub.icon]" />
              </template>
              {{ sub.name }}
            </a-menu-item>
            <!-- sub menu -->
          </a-sub-menu>
        </div>
      </a-menu>
      <!-- Menu -->
    </div>
  </a-menu>
</template>

<script>
  import { defineComponent, toRefs, reactive, h } from 'vue';
  import { usePermissionStore } from '@/stores/permission-store';
  import { useRouter } from 'vue-router';
  import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'; // Importar el componente de FontAwesome

  export default defineComponent({
    components: {
      FontAwesomeIcon,
    },
    props: {
      collapsed: {
        type: Boolean,
        default: false,
      },
    },
    setup(props) {
      const router = useRouter();
      const permissionStore = usePermissionStore();
      const routesRecords = permissionStore.routes;
      console.log('Rutas', routesRecords);
      const routesRecordsKeys = routesRecords.reduce((reduceMenus, menu) => {
        reduceMenus.push(menu.id);
        return reduceMenus;
      }, []);

      const state = reactive({
        rootSubmenuKeys: routesRecordsKeys,
        openKeys: [],
        selectedKeys: [],
      });

      const onOpenChange = (openKeys) => {
        const latestOpenKey = openKeys.find((key) => state.openKeys.indexOf(key) === -1);
        if (state.rootSubmenuKeys.indexOf(latestOpenKey) === -1) {
          state.openKeys = openKeys;
        } else {
          state.openKeys = latestOpenKey ? [latestOpenKey] : [];
        }
      };

      const openItem = (item) => {
        if (item.target_site === 'a2') {
          window.open(url_back_a2 + '#/' + item.path, '_blank');
        } else if (item.target_site === 'sld') {
          window.open(item.path, '_blank');
        } else {
          if (item.path) router.push({ path: `/${item.path}` });
        }
      };

      const openSubItem = (item) => {
        if (item.target_site === 'a2') {
          window.open(url_back_a2 + '#/' + item.path, '_blank');
        } else {
          router.push({ path: `/${item.path}` });
        }
      };

      const renderExpandIcon = (isOpen) => {
        if (props.collapsed) {
          return null;
        }
        return h(FontAwesomeIcon, { icon: ['fas', isOpen ? 'chevron-down' : 'chevron-left'] });
      };

      // Función para detectar si un grupo es el de Hoteles
      const isHotelsGroup = (group) => {
        const groupName = group.name?.toLowerCase() || '';
        const groupPath = group.path?.toLowerCase() || '';
        const groupSlug = group.slug?.toLowerCase() || '';

        // Verificar si el nombre, path o slug contiene "hotel" o "hoteles"
        const isHotelsByName = groupName.includes('hotel') || groupName.includes('hoteles');
        const isHotelsByPath = groupPath.includes('hotel') || groupPath.includes('hoteles');
        const isHotelsBySlug = groupSlug.includes('hotel') || groupSlug.includes('hoteles');

        // Solo retornar true si coincide con hoteles por nombre, path o slug
        return isHotelsByName || isHotelsByPath || isHotelsBySlug;
      };

      const goToQuotasDashboard = () => {
        router.push({ path: '/negotiations/hotels/quotas/dashboard' });
      };

      return {
        ...toRefs(state),
        onOpenChange,
        openItem,
        openSubItem,
        renderExpandIcon,
        routesRecords,
        collapsed: toRefs(props).collapsed,
        isHotelsGroup,
        goToQuotasDashboard,
      };
    },
  });
</script>
<style lang="scss" scoped>
  .hide-arrow .ant-menu-submenu-arrow {
    display: none !important;
  }

  .sub-menu-group {
    .ant-menu-submenu-title {
      padding-left: 0 !important;
    }
  }

  /* Ocultar el contenido de los elementos del menú cuando está colapsado */
  .ant-menu-inline-collapsed .ant-menu-item .ant-menu-title-content,
  .ant-menu-inline-collapsed .ant-menu-submenu-title .ant-menu-title-content {
    display: none;
  }

  .ant-menu-item-group-title {
    font-size: 80%;
    font-weight: 700;
    color: #e4e7ea;
    text-transform: uppercase;
  }
</style>
