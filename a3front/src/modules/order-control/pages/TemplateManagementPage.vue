<template>
  <div class="files-dashboard-table">
    <BaseTable
      v-model:activeKey="activeKey"
      :config="config"
      :total="templateStore.getTotal"
      :currentPage="templateStore.getCurrentPage"
      :defaultPerPage="templateStore.getDefaultPerPage"
      :perPage="templateStore.getPerPage"
      :isLoading="templateStore.isLoading"
      :data="templateStore.getTemplates"
      @onChange="handleChange"
      @onShowSizeChange="handleShowSizeChange"
      @onFilter="handleFilter"
      @onFilterBy="handleFilterBy"
      @onRefresh="handleRefresh"
      @onRefreshCache="handleRefreshCache"
    />
  </div>
</template>

<script setup>
  import { onBeforeMount, ref, computed, watch } from 'vue';
  import { useTemplateStore } from '@ordercontrol/store/template.store';
  import BaseTable from '@ordercontrol/components/BaseTable_TM.vue';

  const templateStore = useTemplateStore();
  const activeKey = ref('1');

  const isShowSizeChange = ref(false);
  const currentPageTemp = ref(1);

  const isMyTemplates = computed(() => activeKey.value === '1');

  const baseColumns = [
    { id: 1, title: `Nombre`, fieldName: 'name', isFiltered: true },
    { id: 2, title: `Fecha edición`, fieldName: 'updatedAt', isFiltered: true },
    { id: 3, title: `Estado`, fieldName: 'status', isFiltered: true },
    { id: 4, title: 'Idiomas', fieldName: 'languages' },
  ];

  const config = computed(() => ({
    columns01: [...baseColumns, { id: 5, title: 'Opciones', fieldName: 'options' }],
    columns02: [
      ...baseColumns,
      { id: 5, title: 'Creado por', fieldName: 'user' },
      { id: 6, title: 'Opciones', fieldName: 'options' },
    ],
  }));

  const handleChange = async ({ currentPage, perPage }) => {
    if (isShowSizeChange.value) {
      isShowSizeChange.value = false;
      return;
    }
    currentPageTemp.value = currentPage;
    await templateStore.changePage({
      currentPage,
      perPage,
      isMyTemplates: isMyTemplates.value,
    });
  };

  const handleShowSizeChange = async ({ size }) => {
    isShowSizeChange.value = true;
    templateStore.perPage = size;
    await templateStore.fetchAll({
      currentPage: 1,
      perPage: size,
      isMyTemplates: isMyTemplates.value,
    });
  };

  const handleFilter = async () => {};
  const handleRefreshCache = async () => {};

  const handleFilterBy = async ({ filterBy, filterByType }) => {
    await templateStore.sortBy({ filterBy, filterByType, isMyTemplates: isMyTemplates.value });
  };

  const handleRefresh = async () => {
    await templateStore.fetchAll({
      currentPage: templateStore.getCurrentPage,
      perPage: templateStore.getPerPage,
      isMyTemplates: isMyTemplates.value,
    });
  };

  const fetchTemplatesByScope = async () => {
    await templateStore.fetchAll({
      currentPage: 1,
      perPage: templateStore.getPerPage,
      isMyTemplates: isMyTemplates.value,
    });
  };

  watch(activeKey, fetchTemplatesByScope);
  onBeforeMount(fetchTemplatesByScope);
</script>

<style scope>
  .files-dashboard-table {
    margin-top: 2rem;
    padding-left: 0;
    padding-right: 0;
  }
</style>
