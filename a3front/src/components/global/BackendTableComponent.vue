<template>
  <a-table
    :dataSource="items"
    :columns="processedColumns"
    :pagination="paginationOptions"
    :loading="loading"
    :rowKey="rowKey"
    :scroll="scroll"
    :size="size"
    :bordered="bordered"
    @change="handleTableChange"
  >
    <template #headerCell="{ column }" v-if="actionHeader">
      <template v-if="column.key === 'actions'">
        <a-tooltip>
          <template #title>{{ actionTitle }}</template>
          <a-button type="primary" size="small" @click="actionHeader">
            <template #icon><PlusOutlined /></template>
          </a-button>
        </a-tooltip>
      </template>
    </template>
    <!-- Slot para acciones personalizadas -->
    <template #bodyCell="{ column, record, index }">
      <template v-if="column.key === 'actions'">
        <slot name="actions" :record="record" :index="index">
          <!-- Acciones por defecto -->
          <a-space>
            <a-tooltip>
              <template #title>
                <small>EDITAR</small>
              </template>
              <a-button v-if="options?.showEdit" type="dashed" @click="handleEdit(record)">
                <EditOutlined />
              </a-button>
            </a-tooltip>
            <a-tooltip>
              <template #title>
                <small>ELIMINAR</small>
              </template>
              <a-button
                v-if="options?.showDelete"
                danger
                type="dashed"
                @click="handleDelete(record)"
              >
                <DeleteOutlined />
              </a-button>
            </a-tooltip>
            <a-tooltip>
              <template #title>
                <small>VER DETALLES</small>
              </template>
              <a-button v-if="options?.showView" type="dashed" @click="handleView(record)">
                <EyeOutlined />
              </a-button>
            </a-tooltip>
          </a-space>
        </slot>
      </template>

      <!-- Slot para contenido personalizado en otras columnas -->
      <template v-else>
        <slot
          :name="`column-${column.dataIndex || column.key}`"
          :record="record"
          :index="index"
          :value="record[column.dataIndex]"
          :column="column"
        >
          <!-- Renderizado condicional para diferentes tipos de datos -->
          <template v-if="column.isSlot">
            <slot :name="column.dataIndex" :record="record" :index="index" />
          </template>
          <template v-else>
            <template v-if="column.customRender">
              <template v-if="column.isComponent">
                <component :is="column.customRender(record[column.dataIndex], record)"></component>
              </template>
              <div v-else v-html="column.customRender(record[column.dataIndex], record)"></div>
            </template>
            <template v-else-if="isBooleanField(record[column.dataIndex])">
              <a-tag :color="record[column.dataIndex] ? 'green' : 'red'">
                {{ record[column.dataIndex] ? 'Sí' : 'No' }}
              </a-tag>
            </template>
            <template v-else>
              {{ record[column.dataIndex] }}
            </template>
          </template>
        </slot>
      </template>
    </template>

    <!-- Slot para cuando no hay datos -->
    <template #emptyText>
      <slot name="empty">
        <a-empty :description="emptyText" />
      </slot>
    </template>

    <!-- Slot personalizado para el footer de la tabla -->
    <template #footer v-if="$slots.footer">
      <slot name="footer" />
    </template>
  </a-table>
</template>

<script setup lang="ts">
  import { EditOutlined, DeleteOutlined, EyeOutlined, PlusOutlined } from '@ant-design/icons-vue';
  import { PaginationConfig } from 'ant-design-vue';
  import { computed, ref, watch } from 'vue';

  // Interfaces para tipado
  interface Column {
    title: string;
    dataIndex: string;
    key?: string;
    width?: number | string;
    align?: 'left' | 'center' | 'right';
    sorter?: boolean | ((a: any, b: any) => number);
    fixed?: 'left' | 'right';
    ellipsis?: boolean;
    customRender?: (value: any, record: any, index: number) => any;
    isComponent?: boolean;
    type?: 'text' | 'date' | 'boolean' | 'number' | 'custom';
  }

  interface PaginationConfig {
    current?: number;
    pageSize?: number;
    total?: number;
    showSizeChanger?: boolean;
    showQuickJumper?: boolean;
    showTotal?: boolean | ((total: number, range: [number, number]) => string);
    pageSizeOptions?: string[];
    position?: (
      | 'topLeft'
      | 'topCenter'
      | 'topRight'
      | 'bottomLeft'
      | 'bottomCenter'
      | 'bottomRight'
    )[];
  }

  interface TableOptions {
    showEdit?: boolean;
    showDelete?: boolean;
    showView?: boolean;
    showActions?: boolean;
    pagination?: boolean | PaginationConfig;
    size?: 'default' | 'middle' | 'small';
    bordered?: boolean;
    scroll?: { x?: number | string; y?: number | string };
    rowKey?: string;
    loading?: boolean;
    emptyText?: string;
    dateFields?: string[]; // Campos que deben formatearse como fecha
    booleanFields?: string[]; // Campos que deben mostrarse como booleanos
  }

  // Props
  interface Props {
    items: any[];
    columns: Column[] | any[];
    options?: TableOptions;
    totalItems?: number; // Total de elementos para paginación server-side
    actionHeader?: any;
    actionTitle?: string;
  }

  const props = withDefaults(defineProps<Props>(), {
    items: () => [],
    columns: () => [],
    options: () => ({}),
    totalItems: 0,
    actionHeader: false,
  });

  // Emits
  const emit = defineEmits<{
    edit: [record: any];
    delete: [record: any];
    view: [record: any];
    change: [pagination: any];
    rowClick: [record: any];
  }>();

  // Reactive state para paginación
  const currentPage = ref(1);
  const pageSize = ref(10);

  // Watchers para props externas
  watch(
    () => props.options?.pagination,
    (newPagination) => {
      if (newPagination && typeof newPagination === 'object') {
        if (newPagination.current) currentPage.value = newPagination.current;
        if (newPagination.pageSize) pageSize.value = newPagination.pageSize;
      }
    },
    { immediate: true }
  );

  // Computed properties
  const processedColumns = computed(() => {
    const baseColumns = [...props.columns];

    // Agregar columna de acciones si está habilitada
    if (props.items.length > 0) {
      if (
        props.options?.showActions !== false ||
        props.options?.showEdit ||
        props.options?.showDelete ||
        props.options?.showView
      ) {
        baseColumns.push({
          title: '',
          key: 'actions',
          dataIndex: 'actions',
          align: 'center',
          fixed: 'right',
        });
      }
    }
    return baseColumns;
  });

  const paginationOptions = computed(() => {
    // Si la paginación está explícitamente deshabilitada
    if (props.options?.pagination === false) {
      return false;
    }

    const baseConfig: PaginationConfig = {
      current: currentPage.value,
      pageSize: pageSize.value,
      showSizeChanger: true,
      showQuickJumper: false,
      showTotal: (total: number, range: [number, number]) =>
        `${range[0]}-${range[1]} de ${total} elementos`,
      pageSizeOptions: ['10', '20', '50', '100'],
      position: ['bottomRight'],
    };

    // Para paginación server-side, usar el total de props
    if (props.totalItems > 0) {
      baseConfig.total = props.totalItems;
    } else {
      // Para paginación client-side, calcular el total basado en los items
      baseConfig.total = props.items.length;
    }

    // Combinar con configuración personalizada
    if (props.options?.pagination && typeof props.options.pagination === 'object') {
      Object.assign(baseConfig, props.options.pagination);
    }

    return baseConfig;
  });

  const rowKey = computed(() => props.options?.rowKey || 'id');
  const loading = computed(() => props.options?.loading || false);
  const size = computed(() => props.options?.size || 'default');
  const bordered = computed(() => props.options?.bordered ?? true);
  const scroll = computed(() => props.options?.scroll || { x: 'max-content' });
  const emptyText = computed(() => props.options?.emptyText || 'No hay datos');

  // Handlers
  const handleEdit = (record: any) => {
    emit('edit', record);
  };

  const handleDelete = (record: any) => {
    emit('delete', record);
  };

  const handleView = (record: any) => {
    emit('view', record);
  };

  const handleTableChange = (pagination: any) => {
    emit('change', pagination);
  };

  const isBooleanField = (value: any) => {
    return typeof value === 'boolean';
  };

  // Expose methods para control externo
  defineExpose({
    resetPagination: () => {
      currentPage.value = 1;
      pageSize.value = 10;
    },
    setPage: (page: number) => {
      currentPage.value = page;
    },
    setPageSize: (size: number) => {
      pageSize.value = size;
    },
    getCurrentPage: () => currentPage.value,
    getPageSize: () => pageSize.value,
  });
</script>

<style scoped>
  /* Mejoras de estilo adicionales */
  :deep(.ant-table-thead > tr > th) {
    font-weight: 500;
    background-color: #c00d0e !important;
    color: #ffffff !important;
    border-radius: 0px !important;
    text-transform: uppercase;
    font-size: 11px;
    text-align: center;
  }

  :deep(.ant-table-tbody > tr > td, .ant-table-tbody > tr:hover > td) {
    text-transform: uppercase;
    font-size: 11px !important;
    padding: 5px;
    background-color: inherit;
  }

  :deep(.ant-table-tbody.ant-table-hover > tr:hover > td) {
    background-color: #f5f5f5;
  }

  :deep(.ant-pagination) {
    margin: 16px 0;
  }

  :deep(.ant-empty-description) {
    font-size: 13px;
    text-transform: uppercase;
    font-weight: 500;
  }
</style>
