<template>
  <div class="base-table">
    <div class="row g-2 row-header">
      <div class="col-small" v-for="column in config.columns" :key="column.id">
        <span v-if="!column?.isFiltered">{{ column.title }}</span>
        <span v-else class="row-header-sort">
          <span
            v-if="isDesc && isSelectedFilterBy(column.fieldName)"
            @click="onFilterBy({ filterBy: column.fieldName, filterByType: 'asc' })"
          >
            {{ column.title }}
            <font-awesome-icon icon="fa-solid fa-arrow-up-short-wide" />
          </span>
          <span v-else @click="onFilterBy({ filterBy: column.fieldName, filterByType: 'desc' })">
            {{ column.title }}
            <font-awesome-icon icon="fa-solid fa-arrow-down-wide-short" />
          </span>
        </span>
      </div>
    </div>

    <div v-if="isLoading" class="container-body is-loading">
      <a-spin :tip="t('files.label.loading')" />
    </div>
    <div v-else-if="(props.data?.length || 0) === 0" class="container-body is-loading">
      <a-empty>
        <template #description>
          <span>{{ t('global.label.empty') }}</span>
        </template>
      </a-empty>
    </div>
    <div v-else class="container-body">
      <div
        v-bind:class="['row g-2 row-body', getMuClass(file.profitability)]"
        v-for="file in data"
        :key="file.id"
      >
        <div class="col-small">{{ file.client_code }}</div>
        <div class="col-small col-small-break col-small-200 text-uppercase">
          <a-tooltip>
            <template #title>{{ file.client_name }}</template>
            {{ file.client_name }}
          </a-tooltip>
        </div>

        <div class="col-small col-small-break col-small-200 text-uppercase">
          <a-tooltip>
            <template #title>{{ file.description }}</template>
            {{ file.description }}
          </a-tooltip>
        </div>
        <div class="col-small col-small-200 text-uppercase">
          <a-tooltip>
            <template #title>{{ file.executive_name || 'Usuario no encontrado' }}</template>
            {{ file.executive_code }}
          </a-tooltip>
        </div>
        <div class="col-small col-small-200 text-uppercase">
          <a-tooltip>
            <template #title>{{ file.executive_kam_name || 'Usuario no encontrado' }}</template>
            {{ file.executive_kam_code || 'Usuario no encontrado' }}
          </a-tooltip>
        </div>
        <div class="col-small">{{ file.file_number }}</div>
        <div class="col-small">{{ file.total_pax }}</div>
        <div class="col-small">{{ formatDate(file.date_in) }}</div>
        <div class="col-small">{{ formatDate(file.date_out) }}</div>
        <div class="col-small">{{ formatNumber({ number: file.markup_client, digits: 2 }) }}</div>
        <div class="col-small">{{ formatNumber({ number: file.markup_qr, digits: 2 }) }}</div>
        <div class="col-small">{{ formatNumber({ number: file.profitability, digits: 2 }) }}</div>
      </div>
    </div>
    <div class="row g-0 row-pagination">
      <a-config-provider :locale="customLocale">
        <a-pagination
          v-model:current="currentPageValue"
          v-model:pageSize="currentPageSize"
          :disabled="(props.data?.length || 0) === 0 || props.loading"
          :pageSizeOptions="DEFAULT_PAGE_SIZE_OPTIONS"
          :total="pagination.total"
          show-size-changer
          :showQuickJumper="true"
          @change="handlePageChange"
        >
          <template #buildOptionText="props">
            <span>{{ props.value }}</span>
          </template>
        </a-pagination>
      </a-config-provider>

      <div>
        <a-button
          type="primary"
          danger
          ghost
          size="large"
          class="btn-primary"
          :disabled="downloadStore.isLoading || (props.data?.length || 0) === 0"
          :loading="downloadStore.isLoading"
          @click="handleDownload"
        >
          <template #icon>
            <font-awesome-icon icon="fa-solid fa-arrow-down" />
          </template>
          <span class="ms-2">{{ t('files.label.list_download') }}</span>
        </a-button>
      </div>
    </div>
  </div>
</template>

<script setup>
  import { ref, computed, watchEffect } from 'vue';
  import { useDownloadStore } from '@store/files';
  import { useI18n } from 'vue-i18n';
  import { formatNumber } from '@/utils/files.js';
  import { formatDate } from '@/utils/files.js';
  // import { getUserName } from '@/utils/auth';

  const INIT_CURRENT_PAGE_VALUE = 1;
  const INIT_PAGE_SIZE = 9;
  const DEFAULT_PAGE_SIZE_OPTIONS = [6, 9, 18];

  const currentPageValue = ref(INIT_CURRENT_PAGE_VALUE);
  const currentPageSize = ref(INIT_PAGE_SIZE);

  const downloadStore = useDownloadStore();

  const { t } = useI18n({
    useScope: 'global',
  });

  const props = defineProps({
    data: {
      type: Array,
      default: () => [],
    },
    pagination: {
      type: Array,
      default: () => [],
    },
    loading: {
      type: Boolean,
      default: true,
    },
  });

  const customLocale = computed(() => {
    return {
      Pagination: {
        jump_to: t('files.pagination.go_to_page'),
      },
    };
  });

  const config = computed(() => ({
    columns: [
      { id: 1, title: `${t('files.column.cod_client')}`, fieldName: 'codClient' },
      { id: 2, title: t('files.column.client'), fieldName: 'client_code' },
      { id: 3, title: t('files.column.description'), fieldName: 'description' },
      { id: 4, title: t('files.column.executive'), fieldName: 'executive_code' },
      { id: 5, title: 'KAM', fieldName: 'kam_code' },
      { id: 6, title: t('files.column.file'), fieldName: 'numFile' },
      { id: 7, title: `N° ${t('files.column.pax')}`, fieldName: 'paxs' },
      { id: 8, title: `${t('files.column.date_in')}`, fieldName: 'date_in' },
      { id: 9, title: `${t('files.column.date_out')}`, fieldName: 'date_out' },
      { id: 10, title: `${t('files.column.mu_client_file')} (%)`, fieldName: 'mu_client_file' },
      { id: 11, title: `MU QR (%)`, fieldName: 'mu_qr' },
      { id: 12, title: `${t('files.column.final_mu')} (%)`, fieldName: 'final_mu' },
    ],
  }));

  const isLoading = computed(() => {
    return props.loading;
  });

  const emit = defineEmits(['page-change', 'download']);

  const data = computed(() => props.data);
  const pagination = computed(() => props.pagination);

  const handlePageChange = (page, pageSize) => {
    emit('page-change', { page, pageSize });
  };

  const handleDownload = () => {
    emit('download');
  };

  const getMuClass = (final) => {
    if (final < 0) {
      return 'red';
    }
    return 'green';
  };

  watchEffect(() => {
    currentPageValue.value = props.pagination.current_page;
    currentPageSize.value = props.pagination.per_page;
  });
</script>

<style scoped lang="scss">
  .row-pagination {
    display: flex;
    justify-content: center;
    gap: 60px;
    padding-top: 20px;
  }

  .btn-primary {
    background-color: #eb5757;
  }

  .base-table {
    width: 100%;

    .row {
      display: flex;
      align-items: center;
      border-radius: 6px;
      margin-bottom: 15px;
      text-align: center;
      color: #4f4b4b;
      font-size: 14px;
      font-weight: 400;
    }

    .row-header {
      background-color: var(--files-background-1);
      color: var(--files-black-4);
      min-height: 50px;
      font-weight: 700;
    }

    .row-header-sort {
      cursor: pointer;
    }

    .container-body {
      min-height: 370px;
      // min-height: 465px;
      &.is-loading {
        background: #fafafa;
        display: flex;
        justify-content: center;
        align-items: center;
      }
    }

    .row-body {
      background-color: var(--files-gray-1);
      border: 1px solid var(--files-main-colorgray-1);
      min-height: 40px;
      font-weight: 400;
      width: 100%;
      &.red {
        background-color: #fff2f2;
      }

      &.green {
        background-color: #dfffe9;
      }
    }

    .col-small {
      flex: 1 1 0%;

      &-break {
        word-break: break-all;
        text-align: left;

        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
      }

      &-200 {
        width: 200px;
      }
    }

    .col-body-options {
      display: flex;
      justify-content: center;
      align-items: center;
      color: var(--files-main-color);
      gap: 8px;

      svg {
        cursor: pointer;
        padding: 1px;
        width: 16px;
        height: 16px;
      }

      svg:focus {
        outline: none;
      }
    }
  }

  :deep(.ant-pagination-item-link) {
    color: #eb5757;
    border-color: #eb5757;
  }

  :deep(.ant-pagination-item-link:hover) {
    color: #eb5757;
  }

  :deep(.ant-pagination-item-active) {
    border-color: #eb5757;
  }

  :deep(.ant-pagination-item-active a) {
    color: #eb5757;
  }
</style>
