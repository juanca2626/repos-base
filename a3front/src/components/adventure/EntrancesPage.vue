<template>
  <a-row type="flex" justify="space-between" align="middle" class="bg-light header-bar">
    <a-col>
      <h1 class="page-title">Entradas</h1>
    </a-col>
  </a-row>
  <div class="content-page">
    <div class="filters-section mb-3">
      <div class="search-container">
        <a-row type="flex" justify="space-between" align="middle">
          <a-col :span="18">
            <a-form :model="filters" layout="vertical">
              <a-row :grutter="24" type="flex" justify="start" align="bottom" style="gap: 7px">
                <a-col :span="4">
                  <a-form-item label="Estado:" name="value" class="mb-0">
                    <a-select
                      v-model:value="filters.status"
                      placeholder="Seleccione un tipo"
                      :show-search="false"
                      :allow-clear="false"
                      :options="states"
                      @change="handleSearchEntrances"
                    >
                    </a-select>
                  </a-form-item>
                </a-col>
                <a-col :span="6">
                  <a-form-item label="Término de búsqueda:" name="value" class="mb-0">
                    <a-input
                      autocomplete="off"
                      v-model:value="filters.term"
                      placeholder="Ingrese algo para buscar.."
                    />
                  </a-form-item>
                </a-col>
                <a-col>
                  <a-button type="primary" :disabled="isLoading" @click="fetchEntrances">
                    <SearchOutlined />
                  </a-button>
                </a-col>
                <a-col>
                  <a-button type="dashed" :disabled="isLoading" @click="clearFilters">
                    <ReloadOutlined />
                  </a-button>
                </a-col>
              </a-row>
            </a-form>
          </a-col>
        </a-row>
      </div>
    </div>

    <!-- Tabla -->
    <backend-table-component
      v-if="!isLoading"
      ref="tableRef"
      :items="entrances"
      :columns="filters.status === 'NO_BOOKING' ? columnsPending : columns"
      :options="tableOptions"
      :total-items="pagination.total"
      size="small"
      @change="handleChange"
    >
      <template #user="{ record }">
        {{
          record.ticketInformation?.statusHistory?.find(
            (history: any) => history.status === 'PENDING_PAYMENT'
          )?.user ?? '-'
        }}
      </template>
      <template #receiptDate="{ record }">
        {{
          moment(
            record.ticketInformation?.statusHistory?.find(
              (history: any) => history.status === 'PENDING_PAYMENT'
            )?.timestamp
          ).format('DD/MM/YYYY') ?? '-'
        }}
      </template>
      <template #templateName="{ record }">
        <b>{{ record.templateName }}</b>
        <small class="d-block">[{{ record.serviceCode }}]</small>
      </template>
      <template #name="{ record }">
        <b>{{ record.name }}</b>
      </template>
      <template #commercialFiles="{ record }">
        <a-tag
          v-for="(file, index) in record.commercialFiles"
          class="mb-2"
          :key="index"
          color="red"
        >
          {{ file.file.number }}
        </a-tag>
      </template>
      <template #providers="{ record }">
        <a-tag color="red" v-if="record.providers.length > 0">
          {{ record.providers[0].code }}
        </a-tag>
      </template>
      <template #attachments="{ record }">
        <template v-for="(attachment, index) in record.attachments" :key="index">
          <a-tooltip>
            <template #title>
              <small>Descargar: {{ attachment.split('/').pop() }}</small>
            </template>
            <a-button
              type="dashed"
              size="small"
              danger
              class="mx-1"
              @click="handleDownloadAttachment(attachment)"
            >
              <DownloadOutlined />
            </a-button>
          </a-tooltip>
        </template>
      </template>
      <template #download="{ record }">
        <a-tooltip title="Descargar">
          <a-button type="dashed" :loading="record.loading" @click="handleDownload(record)">
            <DownloadOutlined v-if="!record.loading" />
          </a-button>
        </a-tooltip>
      </template>
      <template #reserve="{ record }">
        <a-tooltip title="Reservar">
          <a-button type="dashed" @click="handleReserve(record)">
            <ScheduleOutlined />
          </a-button>
        </a-tooltip>
      </template>
      <template #send="{ record }">
        <a-tooltip title="Reenviar notificación">
          <a-button type="dashed" @click="handleSend(record)">
            <SendOutlined />
          </a-button>
        </a-tooltip>
      </template>
    </backend-table-component>
  </div>

  <!-- Modal Reserva -->
  <entrance-reserve-modal
    v-if="showModalReserve"
    v-model:visible="showModalReserve"
    :entrance="selectedEntrance"
    @success="handleSuccessReserve"
  />
</template>

<script setup lang="ts">
  import { ref, onBeforeMount } from 'vue';
  import { useEntrances } from '@/composables/adventure';
  import BackendTableComponent from '@/components/global/BackendTableComponent.vue';
  import {
    DownloadOutlined,
    ReloadOutlined,
    ScheduleOutlined,
    SearchOutlined,
    SendOutlined,
  } from '@ant-design/icons-vue';
  import { notification } from 'ant-design-vue';
  import { columnsPending, columns } from './entrances/columns';
  import EntranceReserveModal from './entrances/EntranceReserveModal.vue';
  import moment from 'moment';

  const tableRef = ref();

  const {
    error,
    isLoading,
    entrances,
    pagination,
    filters,
    states,
    fetchEntrances,
    downloadEntrance,
    sendEntrance,
  } = useEntrances();

  const handleSearchEntrances = async () => {
    isLoading.value = true;
    entrances.value = [];
    await fetchEntrances();
    isLoading.value = false;
  };

  const clearFilters = async () => {
    filters.value = {
      status: 'NO_BOOKING',
      term: '',
    };

    await fetchEntrances();
  };

  const tableOptions = {
    showActions: false,
    pagination: pagination.value,
    rowKey: '_id',
    bordered: true,
  };

  onBeforeMount(async () => {
    await fetchEntrances();
    console.log('ENTRADAS: ', entrances.value);
  });

  const handleChange = async (_pagination: any) => {
    pagination.value = _pagination;

    tableRef.value.setPage(_pagination.current);
    tableRef.value.setPageSize(_pagination.perPage);

    await fetchEntrances();
  };

  const handleDownloadAttachment = (link: any) => {
    window.open(link, '_blank');
  };

  const handleDownload = async (record: any) => {
    console.log('record', record);
    record.loading = true;
    await downloadEntrance(record);
    record.loading = false;
  };

  const showModalReserve = ref(false);
  const selectedEntrance = ref<any>({});

  const handleReserve = (record: any) => {
    selectedEntrance.value = record;
    showModalReserve.value = true;
  };

  const handleSend = async (record: any) => {
    console.log('record', record);
    const params = {
      ticketIndex: record.ticketIndex,
    };
    await sendEntrance(record._id, params);

    if (!error.value) {
      await fetchEntrances();
      notification.success({
        message: 'Éxito',
        description: 'Entrada reenviada correctamente',
      });
    } else {
      notification.error({
        message: 'Error',
        description: error.value,
      });
    }
  };

  const handleSuccessReserve = async () => {
    await fetchEntrances();
  };
</script>

<style scoped>
  @import '../../scss/views/adventure.scss';

  /* Modal styles moved to EntranceReserveModal.vue */
</style>
