<template>
  <a-tree
    class="draggable-tree custom-tree-style"
    v-model:expandedKeys="expandedKeys"
    :tree-data="transformedTreeData"
    draggable
    block-node
    @drop="onDrop"
  >
    <template #title="{ dataRef: node }">
      <div :class="['node-row-wrapper', node.dataType + '-row']" @click.stop>
        <div class="col-name">
          <a-row type="flex" justify="end" align="middle" style="gap: 7px">
            <a-col>
              <menu-outlined />
            </a-col>
            <a-col>
              <span class="node-title">{{ node.title }}</span>
            </a-col>

            <a-col v-if="node.dataType === 'category'">
              <a-tag color="red">{{ node.originalData.equivalence }}</a-tag>
            </a-col>

            <template v-if="node.dataType === 'service'">
              <a-col>
                <a-tag color="red">{{ node.category.category.equivalence }}</a-tag>
              </a-col>
              <a-col v-if="node.originalData.providers.length > 0">
                <a-tag>{{
                  node.originalData.providers.map((provider) => provider.code).join(', ')
                }}</a-tag>
              </a-col>
              <a-col>
                <a-tooltip
                  :title="
                    node.originalData.paymentType === 'credit'
                      ? 'COBRO AL CRÉDITO'
                      : 'COBRO AL CONTADO'
                  "
                >
                  <credit-card-outlined v-if="node.originalData.paymentType === 'credit'" />
                  <dollar-outlined v-else />
                </a-tooltip>
              </a-col>
              <a-col v-if="node.originalData.providerScaling.length > 0">
                <a-tooltip title="CUADRO DE PROPORCIONALIDAD">
                  <CalendarOutlined @click="showModal(node)" />
                </a-tooltip>
              </a-col>
              <a-col v-if="node.originalData.isProgrammable">
                <a-tooltip title="SERVICIO PROGRAMABLE">
                  <clock-circle-outlined />
                </a-tooltip>
              </a-col>
              <a-col>
                <span v-if="node.dataType === 'service'" class="node-currency">
                  ({{ node.originalData.currencyConverted }})
                </span>
              </a-col>
            </template>
          </a-row>
        </div>

        <div class="col-price">
          <template v-if="node.dataType === 'service'">
            <a-row type="flex" justify="end" align="middle" style="gap: 7px">
              <template v-for="(p, index) in node.originalData.pricingConverted" :key="p.pax">
                <a-col v-if="index < 5">
                  <div class="bg-light p-2" style="border: 1px dashed #ddd; border-radius: 6px">
                    <p
                      class="mb-0"
                      style="line-height: 12px"
                      v-if="node.originalData.type === 'range'"
                    >
                      <small
                        ><b>HASTA {{ p.pax }} PAX:</b></small
                      >
                    </p>
                    <p
                      class="mb-0"
                      style="line-height: 12px"
                      v-if="node.originalData.type === 'ratePerDay'"
                    >
                      <small><b>TARIFA/DÍA:</b></small>
                    </p>
                    <p
                      class="mb-0"
                      style="line-height: 12px"
                      v-if="node.originalData.type === 'costPerPerson'"
                    >
                      <small><b>COSTO/PAX:</b></small>
                    </p>
                    <p class="mb-0 text-center" style="line-height: 12px">
                      <small
                        >{{ node.originalData.currencyConverted }} {{ p.value.toFixed(2) }}</small
                      >
                    </p>
                  </div>
                </a-col>
              </template>
              <a-col v-if="node.originalData.type === 'ratePerDay'">
                <div class="bg-light p-2" style="border: 1px dashed #ddd; border-radius: 6px">
                  <p class="mb-0" style="line-height: 12px">
                    <small><b>TOTAL:</b></small>
                  </p>
                  <p class="mb-0 text-center" style="line-height: 12px">
                    <small
                      >{{ node.originalData.currencyConverted }}
                      <template v-if="node.originalData.type === 'ratePerDay'">
                        {{
                          parseFloat(
                            node.originalData.pricingConverted[0].value *
                              node.originalData.days.length
                          ).toFixed(2)
                        }}
                      </template>
                    </small>
                  </p>
                </div>
              </a-col>
              <a-col>
                <div
                  class="bg-light p-2"
                  style="border: 1px dashed #ddd; border-radius: 6px"
                  v-if="node.originalData.pricing.length >= 5"
                  @click="handleViewClick(node)"
                >
                  <a-tooltip title="Visualizar">
                    <p class="mb-0">
                      <small
                        ><b><EyeOutlined /></b
                      ></small>
                    </p>
                  </a-tooltip>
                </div>
              </a-col>
            </a-row>
          </template>
        </div>

        <div class="col-actions">
          <a-row
            type="flex"
            justify="end"
            align="middle"
            style="gap: 7px"
            v-if="node.dataType === 'service'"
          >
            <a-col>
              <a-dropdown placement="bottomRight">
                <a-button type="dashed">
                  <SettingOutlined />
                </a-button>
                <template #overlay>
                  <a-menu>
                    <a-menu-item
                      key="scaling"
                      @click="showModal(node)"
                      :disabled="node.originalData.providerScaling.length === 0"
                    >
                      <template #icon><CalendarOutlined /></template>
                      CUADRO DE PROPORCIONALIDAD
                    </a-menu-item>
                    <a-menu-item key="edit" @click="handleEditClick(node)">
                      <template #icon><EditOutlined /></template>
                      EDITAR
                    </a-menu-item>
                    <a-menu-item key="delete" @click="handleDelete(node)">
                      <template #icon><DeleteOutlined /></template>
                      ELIMINAR
                    </a-menu-item>
                  </a-menu>
                </template>
              </a-dropdown>
            </a-col>
          </a-row>
        </div>
      </div>
    </template>
  </a-tree>
</template>

<script setup>
  import { ref, watch, computed } from 'vue';
  import { Tree as ATree, Button as AButton } from 'ant-design-vue';
  import {
    EditOutlined,
    DeleteOutlined,
    CalendarOutlined,
    EyeOutlined,
    CreditCardOutlined,
    DollarOutlined,
    ClockCircleOutlined,
    MenuOutlined,
    SettingOutlined,
  } from '@ant-design/icons-vue';

  const props = defineProps({
    data: { type: Array, required: true },
  });
  const emit = defineEmits(['data-updated']);

  const transformDataForTree = (data) => {
    return data.map((categoryGroup) => {
      return {
        key: categoryGroup.category._id,
        title: categoryGroup.category.name,
        dataType: 'category',
        originalData: categoryGroup.category,
        children: categoryGroup.services.map((service) => ({
          category: categoryGroup,
          key: service._id,
          title: service.name,
          dataType: 'service',
          originalData: service,
          isLeaf: true,
        })),
      };
    });
  };

  const treeDataInternal = ref([]);
  const expandedKeys = ref([]);

  watch(
    () => props.data,
    (newRawData) => {
      if (newRawData && newRawData.length > 0) {
        treeDataInternal.value = transformDataForTree(newRawData);
        expandedKeys.value = treeDataInternal.value.map((node) => node.key);
      }
    },
    { immediate: true, deep: true }
  );

  const transformedTreeData = computed(() => treeDataInternal.value);

  const loop = (data, key, callback) => {
    for (let i = 0; i < data.length; i++) {
      if (data[i].key === key) {
        return callback(data[i], i, data);
      }
      if (data[i].children) {
        const result = loop(data[i].children, key, callback);
        if (result) return result;
      }
    }
  };

  const onDrop = (info) => {
    const dropKey = info.node.key;
    const dragKey = info.dragNode.key;
    const dropPos = info.node.pos.split('-');
    const dropPosition = info.dropPosition - Number(dropPos[dropPos.length - 1]);

    const data = [...transformedTreeData.value];

    let dragItem;
    loop(data, dragKey, (item, index, arr) => {
      arr.splice(index, 1);
      dragItem = item;
    });

    if (!info.dropToGap) {
      if (info.node.dataType === 'category' && dragItem.dataType === 'service') {
        loop(data, dropKey, (item) => {
          item.children = item.children || [];
          item.children.push(dragItem);
        });
        message.success(`Servicio "${dragItem.title}" movido a la categoría "${info.node.title}"`);
      } else {
        message.error('Movimiento no permitido: Solo servicios pueden ser hijos de categorías.');
        return;
      }
    } else {
      let ar;
      let i;

      loop(data, dropKey, (item, index, arr) => {
        ar = arr;
        i = index;
      });

      if (ar === data && dragItem.dataType === 'service') {
        message.error(
          'Movimiento no permitido: Los servicios deben estar dentro de una categoría.'
        );
        return;
      }

      if (dropPosition === -1) {
        ar.splice(i, 0, dragItem);
      } else {
        ar.splice(i + 1, 0, dragItem);
      }

      message.success(`Elemento "${dragItem.title}" reorganizado.`);
    }

    treeDataInternal.value = data;
    emit('data-updated', treeDataInternal.value);
  };

  const handleEditClick = (node) => {
    emit('edit', node.originalData);
  };

  const handleViewClick = (node) => {
    emit('view', node.originalData);
  };

  const handleDelete = (node) => {
    emit('delete', node.originalData);
  };

  const showModal = (node) => {
    emit('show-modal', node.originalData);
  };
</script>

<style>
  .ant-tree-indent,
  .ant-tree-switcher.ant-tree-switcher-noop {
    display: none !important;
  }
  .ant-tree-switcher.ant-tree-switcher_open,
  .ant-tree-switcher.ant-tree-switcher_close {
    position: absolute !important;
    width: 30px !important;
    height: 42px !important;
    z-index: 10 !important;
    display: block !important;
    left: 5px !important;
    line-height: 42px !important;
    background-color: #c00d0e !important;
    color: #fff !important;
  }
</style>

<style scoped>
  .services-tree-container {
    max-width: 100%;
    border: 1px solid #ddd;
    border-radius: 4px;
  }

  /* 1. Estilos para la Fila de Encabezados (Header de las columnas) */
  .tree-header-row {
    display: flex;
    font-weight: bold;
    background-color: #f7f7f7;
    padding: 8px 16px;
    border-bottom: 1px solid #eee;
    color: #595959;
    font-size: 0.9em;
  }

  /* 2. Estilos para la Fila de Nodos (Item) */
  .node-row-wrapper {
    display: flex;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #f0f0f0;
  }

  /* Estilo para las filas de CATEGORÍA (Barra roja/gris en tu imagen) */
  .category-row {
    background-color: #e6e6e6; /* Fondo gris claro */
    border-top: 1px solid #ddd;
    border-bottom: 1px solid #ddd;
    font-weight: bold;
  }

  /* 3. Definición de Columnas (Compartidas entre Header y Node) */
  .col-name {
    width: 60%;
    padding-left: 16px;
    display: flex;
    align-items: center;
  }

  .col-price {
    width: 50%;
    color: rgba(0, 0, 0, 0.65);
    text-align: right;
  }

  .col-actions {
    width: 5%;
    text-align: right;
    padding-right: 16px;
  }

  /* --- AJUSTES DEL ÁRBOL DE ANT DESIGN --- */

  .custom-tree-style :deep(.ant-tree-treenode) {
    padding: 0;
  }

  .custom-tree-style :deep(.ant-tree-node-content-wrapper) {
    width: 100%;
    padding: 0;
    line-height: normal;
    min-height: 40px;
  }

  /* La indentación del árbol: la usamos solo para los servicios (hijos) */
  .custom-tree-style :deep(.ant-tree-indent-unit) {
    width: 16px;
  }

  /* La columna de nombre absorbe el espacio de la indentación */
  .custom-tree-style :deep(.ant-tree-treenode-children .node-row-wrapper .col-name) {
    padding-left: 40px; /* Aplica indentación extra a los servicios */
  }

  /* Estilo al hacer hover */
  .custom-tree-style :deep(.ant-tree-node-content-wrapper:hover .node-row-wrapper) {
    background-color: #f5f5f5;
  }

  /* Estilos de texto */
  .node-code {
    font-size: 0.9em;
    color: #8c8c8c;
  }
  .node-currency {
    font-size: 0.9em;
    color: #595959;
  }
  .price-value {
    font-weight: bold;
    color: #000;
  }
  .total-value {
    font-weight: bold;
  }

  /* Ajuste para que los nodos raíz (categorías) no se vean indentados */
  .custom-tree-style :deep(.ant-tree-treenode-switcher-close .node-row-wrapper .col-name),
  .custom-tree-style :deep(.ant-tree-treenode-switcher-open .node-row-wrapper .col-name) {
    padding-left: 16px;
  }
</style>
