<template>
  <div class="module-aventura">
    <a-spin :spinning="loading" tip="Cargando categorías...">
      <a-title-section
        title="Categorías"
        icon="categories"
        :btn="{ title: '+ Crear Categoría', action: 'showDrawer' }"
        @handlerShowDrawer="handlerShowDrawer"
      />

      <div class="p-5">
        <!-- Componente para Crear Categoría -->
        <CreateCategoryComponent
          :showDrawer="showCreateModal"
          @handlerShowDrawer="handlerShowDrawer"
          @created="fetchCategories"
        />

        <!-- Componente para Editar Categoría -->
        <EditCategoryComponent
          :showDrawer="showEditModal"
          :category="selectedCategory"
          @handlerShowDrawer="handlerShowDrawer"
          @updated="fetchCategories"
        />

        <!-- Tabla -->
        <a-table
          :dataSource="categories"
          rowKey="codigo"
          bordered
          :pagination="{ pageSize: 10 }"
          style="margin-top: 16px"
        >
          <a-table-column title="Código" dataIndex="codigo" key="codigo" />
          <a-table-column title="Descripción" dataIndex="descri" key="descri" />
          <a-table-column title="Código SVS" dataIndex="codsvs" key="codsvs" />
          <a-table-column title="Estado" dataIndex="estado" key="estado" />
          <a-table-column>
            <template #default="{ record }">
              <a-space>
                <a-button type="link" @click="openEditModal(record)">Editar</a-button>
                <a-button type="link" danger @click="deleteCategory(record.codigo)">
                  Eliminar
                </a-button>
              </a-space>
            </template>
          </a-table-column>
        </a-table>
      </div>
    </a-spin>
  </div>
</template>

<script lang="ts" setup>
  import { ref, onMounted } from 'vue';
  //import { useCategoriesStore } from '@/modules/aventura/store/categories.store';
  import CreateCategoryComponent from '@/modules/aventura/components/CreateCategoryComponent.vue';
  import EditCategoryComponent from '@/modules/aventura/components/EditCategoryComponent.vue';
  import ATitleSection from '@/components/backend/ATitleSection.vue';

  const categoriesStore = useCategoriesStore();
  const { fetchCategories, categories, loading } = categoriesStore;

  const showCreateModal = ref(false);
  const showEditModal = ref(false);
  const selectedCategory = ref(null);

  const handlerShowDrawer = (action) => {
    if (action === 'showDrawer') {
      showCreateModal.value = true;
    } else if (action === 'editDrawer') {
      showEditModal.value = true;
    }
  };

  const openEditModal = (category) => {
    selectedCategory.value = category;
    handlerShowDrawer('editDrawer');
  };

  const deleteCategory = async (codigo) => {
    try {
      await categoriesStore.deleteCategory(codigo);
      fetchCategories(); // Recargar categorías tras eliminar
    } catch (error) {
      console.error('Error eliminando la categoría:', error);
    }
  };

  onMounted(() => {
    fetchCategories();
  });
</script>
