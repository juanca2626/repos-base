<template>
  <div class="item-collapse-component-general" :class="{ 'is-collapsed': isCollapsed }">
    <!-- Vista colapsada: solo iconos -->
    <transition name="sidebar-fade" mode="out-in">
      <div v-if="isCollapsed" key="collapsed" class="collapsed-view">
        <!-- Barra de progreso compacta -->
        <div class="collapsed-progress">
          <div class="progress-text">{{ totalProgress }}%</div>
          <div class="progress-bar-mini">
            <div class="progress-fill-mini" :style="{ width: totalProgress + '%' }"></div>
          </div>
        </div>

        <!-- Iconos de todos los items -->
        <div class="collapsed-icons">
          <template v-for="panel in panelSideBar" :key="panel.key">
            <template v-for="subPanel in panel.subPanels" :key="subPanel.key">
              <div
                v-for="item in subPanel.items"
                :key="item.key"
                class="collapsed-icon-item"
                :class="{ active: item.isActive, disabled: item.isActive }"
                @click="handleSidebarItemClick(panel.key, subPanel.key, item.key, item.isActive)"
              >
                <font-awesome-icon
                  :icon="['fas', 'circle-check']"
                  :class="item.isComplete ? 'icon-complete' : 'icon-incomplete'"
                />
              </div>
            </template>
          </template>
        </div>
      </div>

      <!-- Vista expandida: normal -->
      <div v-else key="expanded" class="expanded-view">
        <a-collapse
          expand-icon-position="end"
          v-model:activeKey="activeSideBarCollapseKey"
          v-for="panel in panelSideBar"
          :key="panel.key"
        >
          <a-collapse-panel :key="panel.key" collapsible="disabled">
            <template v-slot:header>
              <div>
                <div class="title-item">{{ panel.title }}</div>
                <div class="progress-item">{{ (panel as any).progress ?? 0 }}% Completado</div>
                <div>
                  <div class="progress-bar-container">
                    <div
                      class="progress-bar"
                      :style="{ width: ((panel as any).progress ?? 0) + '%' }"
                    ></div>
                  </div>
                </div>
              </div>
            </template>

            <div class="sub-item-collapse-component-general">
              <a-collapse
                ghost
                expand-icon-position="end"
                v-model:activeKey="activeSubSideBarCollapseKey"
                @change="(key: any) => handleChangeGroupSideBarCollapseKey(panel.key, key)"
              >
                <a-collapse-panel v-for="subPanel in panel.subPanels" :key="subPanel.key">
                  <template v-slot:header>
                    <div>
                      <div class="title-item">{{ subPanel.title }}</div>
                      <div class="progress-item">
                        {{
                          `${(subPanel as any).complete ?? 0} de ${(subPanel as any).total ?? 0} completados`
                        }}
                      </div>
                    </div>
                  </template>

                  <div class="sub-item-collapse-component">
                    <a-collapse ghost expand-icon-position="end">
                      <a-collapse-panel
                        v-for="item in subPanel.items"
                        :key="item.key"
                        :class="item.isActive ? 'is-active is-current' : 'is-disabled'"
                        collapsible="icon"
                        @click="
                          handleSidebarItemClick(panel.key, subPanel.key, item.key, item.isActive)
                        "
                      >
                        <template v-slot:header>
                          <div
                            class="item-complete"
                            :class="{
                              'cursor-pointer': !item.isActive,
                              'cursor-default': item.isActive,
                            }"
                          >
                            <div>
                              <font-awesome-icon
                                :icon="['fas', 'circle-check']"
                                :class="item.isComplete ? 'icon-complete' : 'icon-incomplete'"
                              />
                            </div>
                            <div class="title-item">{{ item.title }}</div>
                          </div>
                        </template>
                      </a-collapse-panel>
                    </a-collapse>
                  </div>
                </a-collapse-panel>
              </a-collapse>
            </div>
          </a-collapse-panel>
        </a-collapse>
      </div>
    </transition>
  </div>
</template>

<script setup lang="ts">
  import { computed, watch } from 'vue';
  import { useSupplierGlobalComposable } from '@/modules/negotiations/supplier-new/composables/form/supplier-global.composable';

  defineOptions({
    name: 'ItemCollapseComponent',
  });

  interface Props {
    isCollapsed?: boolean;
  }

  const props = defineProps<Props>();

  const emit = defineEmits<{
    (e: 'heightChange', height: number): void;
  }>();

  const {
    panelSideBar,
    activeSideBarCollapseKey,
    activeSubSideBarCollapseKey,
    handleChangeActiveSideBarCollapseKey,
    handleChangeGroupSideBarCollapseKey,
  } = useSupplierGlobalComposable();

  // Calcular el progreso total de todos los paneles
  const totalProgress = computed(() => {
    if (!panelSideBar.value || panelSideBar.value.length === 0) return 0;

    const panel = panelSideBar.value[0] as any;
    const progress = panel?.progress ?? 0;
    return Math.round(Number(progress));
  });

  // Calcular la altura total del contenido basado en items y estado de collapse
  const contentHeight = computed(() => {
    if (props.isCollapsed) {
      // Vista colapsada: progreso (52px) + (items × 40px cada uno)
      const totalItems = panelSideBar.value.reduce((total, panel: any) => {
        return (
          total +
          panel.subPanels.reduce((subTotal: number, subPanel: any) => {
            return subTotal + subPanel.items.length;
          }, 0)
        );
      }, 0);

      return 52 + totalItems * 40;
    }

    // Vista expandida: calcular basado en paneles abiertos
    let totalHeight = 0;

    panelSideBar.value.forEach((panel: any) => {
      // Header del panel principal: 88px
      totalHeight += 88;

      // Si el panel está activo, sumar sus subpanels
      if (activeSideBarCollapseKey.value.includes(panel.key)) {
        panel.subPanels.forEach((subPanel: any) => {
          // Header del subpanel: 56px
          totalHeight += 56;

          // Si el subpanel está activo, sumar sus items
          if (activeSubSideBarCollapseKey.value.includes(subPanel.key)) {
            // Cada item: 40px
            totalHeight += subPanel.items.length * 40;
          }
        });
      }
    });

    return totalHeight;
  });

  // Emitir cambios de altura al padre
  watch(
    contentHeight,
    (newHeight) => {
      emit('heightChange', newHeight);
    },
    { immediate: true }
  );

  const handleSidebarItemClick = (
    panelKey: string,
    subPanelKey: string,
    itemKey: string,
    isActive: boolean
  ) => {
    if (isActive) {
      return;
    }

    handleChangeActiveSideBarCollapseKey(panelKey, subPanelKey, itemKey);
  };
</script>

<style>
  .item-collapse-component-general {
    width: 258px;
    transition: width 0.3s ease;

    &.is-collapsed {
      width: 60px;
    }

    .icon-complete {
      color: #288a5f !important;
    }

    .icon-incomplete {
      color: #cad0cd !important;
    }

    /* Transiciones */
    .sidebar-fade-enter-active,
    .sidebar-fade-leave-active {
      transition: opacity 0.2s ease;
    }

    .sidebar-fade-enter-from,
    .sidebar-fade-leave-to {
      opacity: 0;
    }

    /* Vista colapsada */
    .collapsed-view {
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 16px 0;
      gap: 16px;
    }

    .collapsed-progress {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 4px;
      width: 100%;
      padding: 8px 12px;

      .progress-text {
        font-size: 11px;
        font-weight: 600;
        color: #212121;
      }

      .progress-bar-mini {
        width: 36px;
        height: 8px;
        background-color: #e1edf9;
        border-radius: 4px;
        overflow: hidden;
        position: relative;

        .progress-fill-mini {
          height: 100%;
          background-color: #1284ed;
          width: 0;
          transition: width 0.3s ease;
        }
      }
    }

    .collapsed-icons {
      display: flex;
      flex-direction: column;
      align-items: stretch;
      gap: 0;
      width: 100%;
      padding: 0;

      .collapsed-icon-item {
        width: 100%;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        background: transparent;
        border-left: 4px solid transparent;
        padding-left: 4px;

        &:hover {
          background: #f5f5f5;
        }

        &.active {
          background: #ebeff2;
          border-left-color: #000000;
        }

        &.disabled {
          cursor: default;
          pointer-events: none;
        }

        svg {
          font-size: 18px;
        }
      }
    }

    .ant-collapse-icon-position-end {
      border-radius: 0 !important;
      border-top-color: transparent;
    }

    border-top-color: transparent;
    background: #ffffff;

    .ant-collapse-header {
      border-left: 4px solid #bd0d12;
      border-radius: 0 !important;
      padding: 12px 9px;
    }

    .ant-collapse-item {
      border-radius: 0 !important;
    }

    .ant-collapse-item-active {
      border-bottom: 1px solid #e7e7e7 !important;
      border-radius: 0 !important;
    }

    .title-item,
    .progress-item {
      font-weight: 600;
      vertical-align: middle;
    }

    .title-item {
      font-size: 16px;
      line-height: 24px;
      color: #212121;
      margin-bottom: 8px;
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

    .sub-item-collapse-component-general {
      padding: 0;
      margin: -16px;

      .ant-collapse-header {
        border-left: 1px solid white;
        border-radius: 0 !important;
      }

      .ant-collapse-item:last-child {
        border-bottom: none !important;
      }

      .sub-item-collapse-component {
        margin: -16px;
        padding: 4px 0 0 0;

        .ant-collapse-item {
          border-bottom: 1px solid #e7e7e7 !important;
        }

        .ant-collapse-item:last-child {
          border-bottom: none !important;
        }

        .is-active {
          .ant-collapse-header {
            border-left: 4px solid #bd0d12;
            border-radius: 0 !important;
            background: #e4e4e4;
          }
        }

        .is-current {
          .ant-collapse-header {
            cursor: default !important;
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
    }
  }
</style>
