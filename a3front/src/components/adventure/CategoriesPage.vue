<template>
  <a-row type="flex" justify="space-between" align="middle" class="bg-light header-bar">
    <a-col>
      <h1 class="page-title">Categorías</h1>
    </a-col>
  </a-row>
  <div class="content-page">
    <backend-table-component
      :items="categories"
      :columns="columns"
      :options="tableOptions"
      @edit="handleEdit"
      @delete="handleDelete"
      size="small"
    >
    </backend-table-component>
  </div>

  <!-- Modal para agregar/actualizar categoría -->
  <a-modal
    v-model:visible="showModal"
    title="Categoría"
    :confirm-loading="isLoading"
    @ok="handleOk"
    @cancel="handleCancel"
    okText="Guardar"
    cancelText="Cancelar"
    width="600px"
  >
    <a-form :model="category" :rules="rules" layout="vertical" @finish="handleOk">
      <a-form-item label="Nombre" name="name">
        <a-input v-model:value="category.name" placeholder="Ingrese el nombre" size="large" />
      </a-form-item>
      <a-form-item label="Equivalencia" name="equivalence">
        <a-select
          v-model:value="category.equivalence"
          placeholder="Seleccione una equivalencia"
          size="large"
          :show-search="false"
          :allow-clear="false"
          :options="
            categories.map((category: any) => {
              return {
                label: `${category.equivalence} - ${category.description}`,
                value: category.equivalence,
              };
            })
          "
        >
        </a-select>
      </a-form-item>
    </a-form>
  </a-modal>
</template>

<script setup lang="ts">
  import { ref, onBeforeMount } from 'vue';
  import { useCategories } from '@/composables/adventure';
  import BackendTableComponent from '@/components/global/BackendTableComponent.vue';

  const { isLoading, categories, fetchCategories, category, saveCategory, updateCategory, error } =
    useCategories();

  const rules = {
    name: [{ required: true, message: 'Por favor ingrese el nombre', trigger: 'blur' }],
    equivalence: [
      { required: true, message: 'Por favor ingrese la equivalencia', trigger: 'blur' },
      {
        min: 1,
        max: 50,
        message: 'La equivalencia debe tener entre 1 y 50 caracteres',
        trigger: 'blur',
      },
    ],
  };

  const columns = [
    {
      title: 'Equivalencia',
      dataIndex: 'equivalence',
      key: 'equivalence',
    },
    {
      title: 'Nombre',
      dataIndex: 'name',
      key: 'name',
    },
    {
      title: 'Descripción',
      dataIndex: 'description',
      key: 'description',
    },
  ];

  const tableOptions = {
    showEdit: true,
    showDelete: false,
    pagination: false,
    rowKey: '_id',
    bordered: true,
  };

  const showModal = ref(false);

  onBeforeMount(async () => {
    await fetchCategories();
  });

  const handleOk = async () => {
    console.log(category.value);
    // return false;

    if (category.value._id) {
      await updateCategory();
    } else {
      await saveCategory();
    }

    if (!error.value) {
      handleCancel();
      await fetchCategories();
    }
  };

  const handleCancel = () => {
    category.value = {};
    showModal.value = false;
  };

  const handleEdit = (_category: any) => {
    category.value = JSON.parse(JSON.stringify(_category));
    showModal.value = true;
  };

  const handleDelete = (category: any) => {
    console.log('Eliminar:', category);
  };
</script>

<style scoped>
  @import '../../scss/views/adventure.scss';
</style>
