<template>
  <a-card>
    <!-- <a-row>
      <a-col :span="24" style="text-align: right">
        LEVEL {{ getUserLevel }}
      </a-col>
    </a-row> -->
    <div class="mt-3" style="display: flex; gap: 1rem">
      <div class="column">
        <label>{{ t('global.column.search_by_word') }}</label>
        <a-input
          style="min-height: 45px"
          class="base-input"
          :class="{
            'base-input-w480': [3].includes(getUserLevel),
            'base-input-w340': [2].includes(getUserLevel),
          }"
          v-model:value="formFilter.filter"
          :placeholder="t('files.label.search') + '...'"
          size="large"
        />
      </div>

      <div class="column">
        <label>{{ t('global.column.choose_the_dates') }}</label>
        <a-range-picker
          class="base-input"
          :class="{
            'base-input-w210': [1, 2, 3].includes(getUserLevel),
          }"
          style="min-height: 45px"
          valueFormat="YYYY-MM-DD"
          size="large"
          v-model:value="formFilter.dateRange"
        />
      </div>

      <div class="column" v-if="getUserLevel === 1">
        <label>KAM</label>
        <a-select
          v-model:value="selectedKamCode"
          style="min-height: 45px"
          class="base-select"
          :class="{
            'base-select-w210': [1].includes(getUserLevel),
          }"
          placeholder="KAM"
          :filter-option="false"
          :showSearch="true"
          :allowClear="true"
          :options="all_kams"
          @change="handleKams"
        />
      </div>

      <div class="column" v-if="getUserLevel === 1">
        <label>{{ t('global.label.executive') }}</label>
        <a-select
          v-model:value="selectedSpecialistCode"
          style="min-height: 45px"
          class="base-select"
          :class="{
            'base-select-w210': [1, 2, 3].includes(getUserLevel),
          }"
          :placeholder="t('global.label.executive')"
          :filter-option="false"
          :showSearch="true"
          :allowClear="true"
          :options="all_specialists"
        />
      </div>

      <div class="column" v-if="getUserLevel === 2">
        <label>{{ t('global.label.executive') }}</label>
        <a-select
          v-model:value="selectedSpecialistCode"
          style="min-height: 45px"
          class="base-select base-select-w210"
          :placeholder="t('global.label.executive')"
          :filter-option="false"
          :showSearch="true"
          :allowClear="true"
          :options="all_specialists"
        />
      </div>

      <div class="column">
        <label>{{ t('global.label.client') }}</label>
        <a-select
          style="min-height: 45px"
          class="base-select"
          :class="{
            'base-select-w210': [1, 2, 3].includes(getUserLevel),
          }"
          :placeholder="t('global.label.client')"
          :filter-option="false"
          :showSearch="true"
          :allowClear="true"
          :options="clientsStore.getClients"
          v-model:value="formFilter.clientId"
          :loading="clientsStore.loading"
          @search="handleSearchClients"
        >
          <template #notFoundContent>
            <div v-if="clientsStore.loading">
              <a-spin size="small" />
              <span style="margin-left: 8px">Cargando clientes...</span>
            </div>
            <div v-else-if="clientsStore.getClients.length === 0">No se encontraron clientes</div>
          </template>
        </a-select>
      </div>

      <div class="column">
        <a-button danger size="large" class="base-input" @click="handleClean">
          <font-awesome-icon :icon="['fas', 'wand-magic-sparkles']" />
        </a-button>
      </div>
      <div class="column">
        <a-button
          type="primary"
          danger
          size="large"
          class="base-input base-input-w140 btn-primary"
          @click="handleForm"
          >{{ t('global.button.update') }}</a-button
        >
      </div>
    </div>
  </a-card>
  <a-row class="mt-4">
    <FileBalanceTable
      :data="filesStore?.getFileBalance || []"
      :pagination="filesStore.getFileBalancePagination"
      @page-change="handlePageChangeEvent"
      @download="handleDownload"
      :loading="filesStore.isLoadingBalance"
    />
  </a-row>
</template>
<script setup>
  import { ref, reactive, onBeforeMount, computed, watch } from 'vue';
  import FileBalanceTable from '@/components/files/balance/FileBalanceTable.vue';
  import {
    useExecutivesStore,
    useClientsStore,
    useFilesStore,
    useDownloadStore,
  } from '@store/files';
  import { useI18n } from 'vue-i18n';
  import { debounce } from 'lodash-es';
  import { getUserCode } from '@/utils/auth';

  const { t } = useI18n({
    useScope: 'global',
  });

  const executivesStore = useExecutivesStore();
  const clientsStore = useClientsStore();
  const filesStore = useFilesStore();
  const downloadStore = useDownloadStore();

  const DEFAULT_FORM_FILTER = {
    currentPage: 1,
    perPage: 9,
    filter: '',
    filterBy: '',
    filterByType: '',
    executiveCode: [],
    clientId: '',
    dateRange: [],
  };

  const formFilter = reactive(DEFAULT_FORM_FILTER);

  const code_user = computed(() => {
    return getUserCode();
  });

  const getUserLevel = computed(() => {
    if (!code_user.value) return [];

    const userCode = code_user.value;
    const all_select_box = executivesStore.getAllSelectBox || {};

    if (!all_select_box) return 3;

    // Buscar en primer nivel (keys del objeto principal)
    if (Object.keys(all_select_box).includes(userCode)) return 1;

    // Buscar en segundo nivel (dentro de cada root user)
    for (const rootUser of Object.values(all_select_box)) {
      if (!rootUser.users) continue;

      // Verificar keys de segundo nivel
      if (Object.keys(rootUser.users).includes(userCode)) return 2;

      // Buscar en arrays de usuarios (tercer nivel)
      for (const userGroup of Object.values(rootUser.users)) {
        if (userGroup.users?.some((u) => u.code === userCode)) return 3;
      }
    }

    return 3;
  });

  const handleSearchClients = debounce((value) => {
    if (value !== '' || (value === '' && clientsStore.clients.length === 0)) {
      clientsStore.fetchAll(value);
    }
  }, 300);

  const handlePageChangeEvent = async ({ page, pageSize }) => {
    formFilter.currentPage = page;
    formFilter.perPage = pageSize;
    await filesStore.fetchFileBalanceAll(formFilter);
    console.log('ejecuta el page');
  };

  const handleForm = () => {
    if (getUserLevel.value === 1) {
      console.log(selectedSpecialistCode.value);
      console.log(selectedKamCode.value);
      if (selectedSpecialistCode.value != null) {
        formFilter.executiveCode = [selectedSpecialistCode.value];
      } else if (selectedKamCode.value != null) {
        formFilter.executiveCode = all_specialists.value.map((e) => e.value);
      }
    } else if (getUserLevel.value === 2) {
      console.log(selectedSpecialistCode.value);
      if (selectedSpecialistCode.value != null) {
        formFilter.executiveCode = [selectedSpecialistCode.value];
      } else {
        formFilter.executiveCode = getAllSpecialists.value.map((e) => e.value);
      }
    } else {
      formFilter.executiveCode = [code_user.value];
    }
    formFilter.currentPage = 1;
    filesStore.fetchFileBalanceAll(formFilter);
  };

  const handleClean = () => {
    selectedKamCode.value = null;
    selectedSpecialistCode.value = null;
    formFilter.currentPage = 1;
    formFilter.perPage = 9;
    formFilter.filter = '';
    formFilter.filterBy = '';
    formFilter.filterByType = '';
    if (getUserLevel.value !== 3) {
      formFilter.executiveCode = getAllSpecialists.value.map((e) => e.value);
    } else {
      formFilter.executiveCode = [code_user.value];
    }
    formFilter.clientId = '';
    formFilter.dateRange = [];
    filesStore.fetchFileBalanceAll(formFilter);
  };

  const handleDownload = async () => {
    downloadStore.downloadFileBalance(formFilter);
  };

  const all_kams = computed(() => {
    if (!code_user.value) return [];

    const all_select_box = executivesStore.getAllSelectBox || {};
    const gerente = all_select_box[code_user.value] || {};
    const users = gerente.users || {};

    return Object.keys(users).map((code) => ({
      value: code,
      label: users[code].name,
    }));
  });

  const selectedKamCode = ref(null);
  const selectedSpecialistCode = ref(null);

  const all_specialists = computed(() => {
    // Validación inicial de datos requeridos
    if (!selectedKamCode.value && !getUserLevel.value && !code_user.value) return [];

    // Obtención segura de los datos del store
    const all_select_box = executivesStore.getAllSelectBox || {};

    // Determinación del gerente basado en el rol
    const getGerente = () => {
      if (getUserLevel.value === 2) {
        const firstKey = Object.keys(all_select_box)[0];
        return firstKey ? all_select_box[firstKey] : {};
      }
      return all_select_box?.[code_user.value] || {};
    };

    const gerente = getGerente();

    // Obtención segura del KAM seleccionado
    const getUser = () => {
      if (getUserLevel.value === 2) {
        return gerente.users?.[code_user.value];
      }
      return gerente.users?.[selectedKamCode.value];
    };

    const selectedKam = getUser();
    if (!selectedKam?.users) return [];

    // Eliminar duplicados y mapear especialistas
    const uniqueUsers = new Map();

    selectedKam.users.forEach((user) => {
      if (user?.code && !uniqueUsers.has(user.code)) {
        uniqueUsers.set(user.code, {
          value: user.code,
          label: user.name || t('global.label.executive') + ` ${user.code}`,
        });
      }
    });

    return Array.from(uniqueUsers.values());
  });

  const getAllSpecialists = computed(() => {
    if (!code_user.value && !getUserLevel.value) return [];

    if (getUserLevel.value === 3 || getUserLevel.value === 2)
      return [{ value: code_user.value, label: code_user.value }];

    const all_select_box = executivesStore.getAllSelectBox || {};

    // Determinación del gerente basado en el rol
    const getGerente = () => {
      if (getUserLevel.value === 2) {
        const firstKey = Object.keys(all_select_box)[0];
        return firstKey ? all_select_box[firstKey] : {};
      }
      return all_select_box?.[code_user.value] || {};
    };

    const gerente = getGerente();

    // Obtención segura del KAM seleccionado
    const getAllUsers = () => {
      // Si el usuario es nivel 2, devolvemos solo sus usuarios directos
      if (getUserLevel.value === 2) {
        return gerente.users?.[code_user.value]?.users || [];
      }

      // Extraemos todos los usuarios incluyendo los del segundo nivel
      const allUsers = [];

      // Primero añadimos las keys del segundo nivel (los KAMs mismos)
      Object.entries(gerente.users || {}).forEach(([kamCode, kamData]) => {
        // Añadimos el usuario KAM (segundo nivel)
        allUsers.push({
          code: kamCode,
          name: kamData.name || kamCode,
          isKam: true, // Bandera para identificar que es un KAM
        });

        // Luego añadimos sus usuarios (tercer nivel)
        if (kamData?.users) {
          allUsers.push(
            ...kamData.users.map((user) => ({
              ...user,
              isKam: false, // Estos son usuarios normales
            }))
          );
        }
      });

      return allUsers;
    };

    const allUsers = getAllUsers();
    // return allUsers;
    // Eliminar duplicados y mapear especialistas
    const uniqueUsers = new Map();

    allUsers.forEach((user) => {
      if (user?.code && !uniqueUsers.has(user.code)) {
        uniqueUsers.set(user.code, {
          value: user.code,
          label: user.name || t('global.label.executive') + ` ${user.code}`,
        });
      }
    });
    // uniqueUsers.push({ value: code_user.value, label: code_user.value });
    return Array.from(uniqueUsers.values());
  });

  const handleKams = () => {
    selectedSpecialistCode.value = null;
  };

  onBeforeMount(async () => {
    //   filesStore.finished();
    if (!filesStore.isLoaded) {
      await clientsStore.fetchAll();
      await executivesStore.fetchSelectBox(code_user.value);
    }
  });

  watch(
    getAllSpecialists,
    (newSpecialists) => {
      if (newSpecialists.length > 0) {
        formFilter.executiveCode = newSpecialists.map((e) => e.value);
        filesStore.fetchFileBalanceAll(formFilter);
      }
    },
    { immediate: true }
  );
</script>

<style scoped lang="scss">
  :deep(.ant-card-body) {
    min-width: 1200px;
    border: 0.9px solid #e9e9e9 !important;
    border-radius: 6px !important;
  }
  .form-item {
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
  }
  .column {
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
  }
  .btn-primary {
    background-color: #eb5757 !important;
  }

  .base-input {
    &-w140 {
      width: 140px;
    }

    &-w210 {
      width: 210px;
    }

    &-w340 {
      width: 340px;
    }
    &-w350 {
      width: 350px;
    }
    &-w480 {
      width: 480px;
    }
    &-w530 {
      width: 530px;
    }
    &-w630 {
      width: 630px;
    }

    &-w660 {
      width: 660px;
    }

    height: 45px;
    font-size: 14px;
  }

  .base-select {
    &-w210 {
      width: 210px;
    }
    &-w250 {
      width: 250px;
    }
    &-w260 {
      width: 260px;
    }
    &-w340 {
      width: 340px;
    }
    &-w350 {
      width: 350px;
    }
    &-w660 {
      width: 660px;
    }

    & :deep(.ant-select-selector) {
      height: 45px;
      line-height: 45px;
    }
    & :deep(.ant-select-selection-placeholder) {
      line-height: 45px;
      text-align: left;
    }
    & :deep(.ant-select-selection-item) {
      line-height: 45px;
      text-align: left;
    }
  }
</style>
