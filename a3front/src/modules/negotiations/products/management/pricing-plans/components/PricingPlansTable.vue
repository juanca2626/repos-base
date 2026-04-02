<template>
  <div class="pricing-plans-wrapper">
    <a-table
      :columns="columns"
      :data-source="tableData"
      :pagination="false"
      row-key="id"
      class="custom-pricing-table"
      :expandable="{ showExpandColumn: false }"
      :rowClassName="getRowClassName"
    >
      <!-- HEADER -->
      <template #headerCell="{ column }">
        <div class="header-cell">
          {{ column.title }}
        </div>
      </template>

      <!-- BODY -->
      <template #bodyCell="{ column, record }">
        <!-- PROVEEDOR -->
        <template v-if="column.key === 'provider'">
          <div class="tree-cell" :style="{ paddingLeft: `${record.level * 24}px` }">
            <!-- EXPAND ICON -->
            <font-awesome-icon
              v-if="record.hasChildren"
              :icon="['fas', expandedKeys.includes(record.id) ? 'chevron-down' : 'chevron-right']"
              class="expand-icon"
              @click="toggleRow(record.id)"
            />

            <span v-if="record.type === 'provider'">
              {{ record.provider }}
            </span>

            <UserCheck v-if="record.type === 'provider'" class="row-icon provider-icon" />

            <div v-else-if="record.type === 'plan'" class="plan-product-cell"></div>
          </div>
        </template>

        <!-- PRODUCTO -->
        <template v-else-if="column.key === 'product'">
          <div class="tree-cell">
            <span v-if="record.type === 'provider'"> {{ record.productCount }} productos </span>

            <font-awesome-icon
              v-if="record.type === 'service'"
              :icon="getServiceIcon(record.serviceName)"
              class="row-icon service-icon"
            />

            <span v-if="record.type === 'service'">
              {{ record.serviceName }}
            </span>
          </div>
        </template>

        <!-- SERVICIO -->
        <template v-else-if="column.key === 'service'">
          <span v-if="record.type !== 'plan'"> {{ record.serviceCount }} servicios </span>

          <span v-if="record.type === 'plan'">
            {{ record.productName }}
          </span>
        </template>

        <!-- CIUDAD -->
        <template v-else-if="column.key === 'city'">
          <a-tag v-if="record.type === 'plan'">
            {{ record.city }}
          </a-tag>
        </template>

        <!-- CATEGORIA -->
        <template v-else-if="column.key === 'category'">
          <a-tag v-if="record.type === 'plan'">
            {{ record.category }}
          </a-tag>
        </template>

        <!-- COSTO -->
        <template v-else-if="column.key === 'cost'">
          <div v-if="record.type === 'plan'" class="cost-cell">
            USD {{ record.cost }}

            <font-awesome-icon
              v-if="record.cost < 30"
              :icon="['fas', 'triangle-exclamation']"
              class="warning-icon"
            />
          </div>
        </template>

        <!-- TIPO TARIFA -->
        <template v-else-if="column.key === 'tariffType'">
          <span v-if="record.type === 'plan'">
            {{ record.tariffType }}
          </span>
        </template>

        <!-- VIGENCIA -->
        <template v-else-if="column.key === 'validity'">
          <span v-if="record.type === 'plan'">
            {{ record.validity }}
          </span>
        </template>

        <!-- ACCIONES -->
        <template v-else-if="column.key === 'actions'">
          <div v-if="record.type === 'plan'" class="actions">
            <font-awesome-icon :icon="['fas', 'magnifying-glass']" />
            <font-awesome-icon :icon="['fas', 'pen-to-square']" />
            <font-awesome-icon :icon="['fas', 'copy']" />
          </div>
        </template>
      </template>
    </a-table>
  </div>
</template>

<script setup lang="ts">
  import { ref, computed } from 'vue';
  import { UserCheck } from '../icons';

  interface Row {
    id: string;
    type: 'provider' | 'service' | 'plan';
    level?: number;
    hasChildren?: boolean;

    provider?: string;
    productCount?: number;
    serviceCount?: number;

    serviceName?: string;

    productName?: string;
    city?: string;
    category?: string;
    cost?: number;
    tariffType?: string;
    validity?: string;

    children?: Row[];
  }

  const rawData: Row[] = [
    {
      id: 'p1',
      type: 'provider',
      provider: 'COD P.',
      productCount: 3,
      serviceCount: 8,
      children: [
        {
          id: 's1',
          type: 'service',
          serviceName: 'Almuerzos',
          serviceCount: 4,
          children: [
            {
              id: 't1',
              type: 'plan',
              productName: 'COD S.',
              city: 'Lima',
              category: 'Privado',
              cost: 25,
              tariffType: 'Confirmada',
              validity: '2025 - 2026',
            },
            {
              id: 't2',
              type: 'plan',
              productName: 'COD S.',
              city: 'Cusco',
              category: 'Compartido',
              cost: 52,
              tariffType: 'Confirmada',
              validity: '2025 - 2026',
            },
          ],
        },
      ],
    },
  ];

  const expandedKeys = ref<string[]>([]);

  const toggleRow = (id: string) => {
    if (expandedKeys.value.includes(id)) {
      expandedKeys.value = expandedKeys.value.filter((k) => k !== id);
    } else {
      expandedKeys.value.push(id);
    }
  };

  const flattenData = (nodes: Row[], level = 0): Row[] => {
    let result: Row[] = [];

    nodes.forEach((node) => {
      const { children, ...rest } = node;

      result.push({
        ...rest,
        level,
        hasChildren: !!children?.length,
      });

      if (children && expandedKeys.value.includes(node.id)) {
        result = result.concat(flattenData(children, level + 1));
      }
    });

    return result;
  };

  const tableData = computed(() => flattenData(rawData));

  const getRowClassName = (record: any, index: number) => {
    if (record.type !== 'plan') return '';

    // Buscar el plan anterior en la lista visible
    const previous = tableData.value[index - 1];

    // Si el anterior NO es plan, entonces este es el primero del grupo
    if (!previous || previous.type !== 'plan') {
      return 'plan-first-row';
    }

    return 'plan-row-no-border';
  };

  const columns = [
    { title: 'Proveedor', key: 'provider' },
    { title: 'Producto', key: 'product' },
    { title: 'Servicio', key: 'service' },
    { title: 'Ciudad', key: 'city' },
    { title: 'Categoría', key: 'category' },
    { title: 'Costo', key: 'cost' },
    { title: 'Tipo de tarifa', key: 'tariffType' },
    { title: 'Vigencia', key: 'validity' },
    { title: 'Acciones', key: 'actions' },
  ];

  const getServiceIcon = (serviceName?: string) => {
    if (!serviceName) return ['fas', 'circle'];

    if (serviceName.includes('Almuerzos')) return ['fas', 'utensils'];

    return ['fas', 'bus'];
  };
</script>
<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .custom-pricing-table {
    margin-top: 24px;

    :deep(table) {
      border: 1px $color-white-4 solid;
    }

    :deep(.ant-table-container) {
      border-radius: 8px !important;
      overflow: hidden;
    }

    :deep(.ant-table-thead) {
      .ant-table-cell {
        background: $color-black;
        // border-radius: 0 !important;
        font-weight: 500;
        font-size: 16px;
        color: $color-white;
        padding: 16px;

        &::before {
          display: none !important;
        }
      }
    }

    :deep(.ant-table-tbody) {
      .ant-table-cell {
        font-size: 16px;
        color: $color-black-graphite;
      }
    }
  }

  .tree-cell {
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .expand-icon {
    cursor: pointer;
    font-size: 13px;
    transition: transform 0.2s ease;
  }

  .cost-cell {
    display: flex;
    align-items: center;
    gap: 6px;
  }

  .warning-icon {
    color: $color-warning;
  }

  .actions {
    display: flex;
    gap: 14px;
    color: $color-primary;
  }

  .plan-product-cell {
    position: relative;
    padding-left: 16px;
  }

  .plan-product-cell::before {
    content: '';
    position: absolute;
    left: -18px;
    top: -18px;
    bottom: -16px;
    width: 0.5px;
    background: #babcbd;
  }

  :deep(.plan-row-no-border > td) {
    border-top: none !important;
    border-bottom: none !important;
  }

  :deep(.plan-first-row > td) {
    border-bottom: none !important;
  }
</style>
