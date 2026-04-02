<template>
  <div class="maintenance-sanctions">
    <!-- Vista de Listado -->
    <a-card class="filters-card" :bordered="false">
      <div class="filter-section">
        <div class="filter-group">
          <a-button type="primary" danger @click="openModal"> Agregar Sanción </a-button>
        </div>

        <div class="filter-group">
          <a-input
            v-model:value="textFilter"
            placeholder="Búsqueda sobre el resultado..."
            style="width: 300px"
          >
            <template #prefix><SearchOutlined /></template>
          </a-input>
        </div>

        <div class="filter-group">
          <a-button type="primary" @click="searchMaintenance" :loading="loading">
            <SearchOutlined />
            Buscar
          </a-button>
        </div>
      </div>
    </a-card>

    <a-card style="margin-top: 16px">
      <a-table
        :columns="columns"
        :data-source="filteredPenalties"
        :loading="loading"
        :pagination="{ pageSize: 20 }"
        size="small"
        class="responsive-table"
      >
        <template #bodyCell="{ column, record }">
          <template v-if="column.key === 'actions'">
            <a-button type="link" size="small" @click="editPenalty(record)" title="Editar Sanción">
              <EyeOutlined />
            </a-button>
          </template>

          <template v-else-if="column.dataIndex && record[column.dataIndex]">
            <div class="multiline-cell">
              {{ record[column.dataIndex]?.trim() }}
            </div>
          </template>

          <template v-else> - </template>
        </template>
      </a-table>
    </a-card>

    <!-- Modal Component -->
    <PenaltyModal
      v-model:open="showModal"
      :penalty="selectedPenalty"
      @close="closeModal"
      @saved="handleSaved"
    />
  </div>
</template>

<script setup>
  import { ref, computed, onMounted } from 'vue';
  import { maintenanceSanctionsApi } from '../../services/api';
  import PenaltyModal from './PenaltyModal.vue';
  import { SearchOutlined, EyeOutlined } from '@ant-design/icons-vue';

  // Estados
  const textFilter = ref('');
  const loading = ref(false);
  const penalties = ref([]);
  const showModal = ref(false);
  const selectedPenalty = ref(null);

  // Columnas de la tabla
  const columns = [
    {
      title: 'Tipo de Servicio',
      dataIndex: 'descri_servicio',
      key: 'descri_servicio',
      width: 150,
    },
    {
      title: 'Categoría',
      dataIndex: 'descri_categoria',
      key: 'descri_categoria',
      width: 120,
    },
    {
      title: 'Subcategoría',
      dataIndex: 'subcat',
      key: 'subcat',
      width: 120,
    },
    {
      title: 'Tipo de Sanción',
      dataIndex: 'descri_falta',
      key: 'descri_falta',
      width: 150,
    },
    {
      title: 'Acciones',
      key: 'actions',
      width: 80,
      align: 'center',
    },
  ];

  // Penalties filtradas
  const filteredPenalties = computed(() => {
    if (!textFilter.value) return penalties.value;

    const searchTerm = textFilter.value.toLowerCase();
    return penalties.value.filter(
      (penalty) =>
        penalty.descri_servicio?.toLowerCase().includes(searchTerm) ||
        penalty.descri_categoria?.toLowerCase().includes(searchTerm) ||
        penalty.subcat?.toLowerCase().includes(searchTerm) ||
        penalty.descri_falta?.toLowerCase().includes(searchTerm)
    );
  });

  // Métodos
  const searchMaintenance = async () => {
    try {
      loading.value = true;
      const response = await maintenanceSanctionsApi.get('/search-penalties');
      penalties.value = response.data.data.results || [];
    } catch (error) {
      console.error('Error al buscar sanciones:', error);
      penalties.value = [];
    } finally {
      loading.value = false;
    }
  };

  const openModal = (penalty = null) => {
    selectedPenalty.value = penalty;
    showModal.value = true;
  };

  const editPenalty = (penalty) => {
    openModal(penalty);
  };

  const closeModal = () => {
    showModal.value = false;
    selectedPenalty.value = null;
  };

  const handleSaved = () => {
    closeModal();
    searchMaintenance();
  };

  // Cargar datos iniciales
  onMounted(() => {
    searchMaintenance();
  });
</script>

<style scoped>
  .maintenance-sanctions {
    padding: 16px;
  }

  .filters-card {
    margin-bottom: 16px;
    padding: 16px;
  }

  .filter-section {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 16px;
    flex-wrap: wrap;
  }

  .filter-group {
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .responsive-table {
    font-size: 12px;
  }

  .responsive-table :deep(.ant-table-thead > tr > th) {
    white-space: normal;
    word-wrap: break-word;
    line-height: 1.2;
    padding: 8px 4px;
    font-size: 11px;
    font-weight: 600;
    background-color: #fafafa;
  }

  .responsive-table :deep(.ant-table-tbody > tr > td) {
    white-space: normal;
    word-wrap: break-word;
    line-height: 1.3;
    padding: 6px 4px;
    vertical-align: top;
    font-size: 11px;
  }

  .multiline-cell {
    min-height: 32px;
    display: flex;
    align-items: center;
    word-wrap: break-word;
    word-break: break-word;
  }

  /* Responsive */
  @media (max-width: 768px) {
    .filter-section {
      flex-direction: column;
      align-items: flex-start;
      gap: 12px;
    }

    .filter-group {
      width: 100%;
    }

    .filter-group :deep(.ant-input) {
      width: 100% !important;
    }
  }
</style>
