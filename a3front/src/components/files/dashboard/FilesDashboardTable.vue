<template>
  <div class="files-dashboard-table">
    <BaseTable
      :config="config"
      :total="filesStore.getTotal"
      :currentPage="filesStore.getCurrentPage"
      :defaultPerPage="filesStore.getDefaultPerPage"
      :perPage="filesStore.getPerPage"
      :isLoading="filesStore.isLoading"
      :data="filesStore.getFiles"
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
  import { onBeforeMount, ref, watch, computed } from 'vue';
  import { useSocketsStore } from '@/stores/global';
  import BaseTable from '@/components/files/reusables/BaseTable.vue';
  import { useFilesStore, useExecutivesStore, useClientsStore, useVipsStore } from '@store/files';

  import { useI18n } from 'vue-i18n';
  const { t } = useI18n({
    useScope: 'global',
  });

  const filesStore = useFilesStore();
  const socketsStore = useSocketsStore();
  const executivesStore = useExecutivesStore();
  const clientsStore = useClientsStore();
  const vipsStore = useVipsStore();

  /* isShowSizeChange
   *
   * What: El propósito es regresar a la primera página después de cambiar el select showSize.
   * Why: Los eventos onChange y onShowSizeChange se ejecutan simultaneamente y no permiten cambiar el currentPage adecuadamente.
   */
  const isShowSizeChange = ref(false);
  const currentPageTemp = ref(1);

  const config = computed(() => ({
    columns: [
      { id: 1, title: `N° ${t('files.column.file')}`, fieldName: 'numFile' },
      { id: 2, title: t('files.column.description'), fieldName: 'description' },
      { id: 3, title: t('files.column.estado'), fieldName: 'status', isFiltered: true },
      { id: 4, title: 'VIP', fieldName: 'vips', isFiltered: true },
      { id: 5, title: `N° ${t('files.column.pax')}`, fieldName: 'paxs' },
      { id: 6, title: t('files.column.executive'), fieldName: 'executive_code' },
      { id: 7, title: t('files.column.client'), fieldName: 'client_code' },
      { id: 8, title: t('files.column.date_in'), fieldName: 'date_in' },
      {
        id: 10,
        title: t('files.label.Location'),
        fieldName: 'revision_stages',
        isFiltered: true,
      },
      { id: 11, title: t('global.column.options'), fieldName: 'options' },
    ],
  }));

  const handleChange = async ({ currentPage, perPage, filter, clientId, flag_stella }) => {
    if (isShowSizeChange.value) {
      isShowSizeChange.value = false;
      return;
    }
    currentPageTemp.value = currentPage;
    await filesStore.changePage({ currentPage, perPage, filter, clientId, flag_stella });
  };

  const handleShowSizeChange = async ({ size, flag_stella }) => {
    isShowSizeChange.value = true;
    filesStore.perPage = size;
    await filesStore.fetchAll({ currentPage: 1, perPage: size, flag_stella });
  };

  const handleFilter = async ({ form }) => {
    const { filter, executiveCode, clientId, dateRange, flag_stella } = form;
    const perPage = filesStore.perPage;
    await filesStore.search({ filter, perPage, executiveCode, clientId, dateRange, flag_stella });
  };

  const handleFilterBy = async ({ filterBy, filterByType, flag_stella }) => {
    await filesStore.sortBy({ filterBy, filterByType, flag_stella });
  };

  const handleRefresh = async () => {
    //
    filesStore.revisionStages = null;
    filesStore.filterNextDays = null;
  };

  const handleRefreshCache = async () => {
    filesStore.revisionStages = null;
    filesStore.filterNextDays = null;

    const currentPage = currentPageTemp.value;

    if (currentPage) {
      await filesStore.fetchAll({ currentPage });
    }
  };

  onBeforeMount(async () => {
    filesStore.inited();

    await Promise.all([executivesStore.fetchAll(), clientsStore.fetchAll(), vipsStore.fetchAll()]);

    await filesStore.fetchAll({ currentPage: 1 });

    setTimeout(() => {
      socketsStore.disconnect();
      filesStore.changeLoaded(true);
      filesStore.finished();
    }, 100);
  });

  watch(
    () => filesStore.getFiles,
    async () => {
      const files = filesStore.getFiles;

      // Obtener códigos únicos de ejecutivos
      const executiveCodes = [...new Set(files.map((file) => file.executiveCode))];
      const codesString = executiveCodes.join(',');

      // Buscar jefes
      await executivesStore.fetchAllBoss(codesString);
      const bossMap = executivesStore.getBoss.executives_boss;

      // Obtener códigos únicos de jefes
      const bossCodes = new Set();
      for (const file of files) {
        const bossCode = bossMap[file.executiveCode];
        if (bossCode) {
          file.bossCode = bossCode;
          bossCodes.add(bossCode);
        }
      }

      // Unir todos los códigos para buscar nombres
      const allCodes = [...new Set([...executiveCodes, ...bossCodes])].join(',');
      await executivesStore.findAll(allCodes);

      const executives = executivesStore.getAllExecutives;

      // Asignar nombres
      for (const file of files) {
        file.executiveName = executives[file.executiveCode]?.name || '';
        file.bossName = executives[file.bossCode]?.name || '';
      }
    }
  );
</script>

<style scope>
  .files-dashboard-table {
    margin-top: 2rem;
    padding-left: 0;
    padding-right: 0;
  }
</style>
