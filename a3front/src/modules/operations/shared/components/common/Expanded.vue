<!-- TabContentComponent.vue -->
<template>
  <div class="tab-content mt-2">
    <!-- Configuración de la unidad por cada sede -->
    <!-- Filtros de búsqueda -->

    <a-flex justify="space-between" align="middle" class="pt-1 pb-4">
      <!-- Contenedor de Typography -->
      <div style="display: flex; align-items: center">
        <a-typography-title :level="5" style="margin: 0">
          <FileOutlined /> Files anidados
        </a-typography-title>
      </div>

      <!-- Contenedor de botones con a-space -->
      <a-space>
        <a-button href="javascript:void(0)" class="ant-btn-lg" @click="unnestServices(record)">
          Desanidar
        </a-button>
        <a-button
          type="primary"
          class="ant-btn-lg"
          @click="($emit('closeExpand', record), setValue('isExpanded', false))"
        >
          <font-awesome-icon :icon="['fas', 'xmark']" />
          Cerrar
        </a-button>
      </a-space>
    </a-flex>

    <a-table
      ref="tableRef"
      table-layout="fixed"
      expand-icon-as-cell="false"
      expand-icon-column-index="{-1}"
      :loading="false"
      :columns="columns"
      :dataSource="filesData"
      :row-key="(filesData: any) => filesData.file._id"
      :pagination="false"
      :row-selection="rowSelection"
      :scroll="{ x: '90%', y: 600 }"
    >
      <template #bodyCell="{ column, record }">
        <template v-if="column.key === 'datetime_start'">
          <!-- {{ record.id }} -->
          <a-row justify="center" align="middle">
            <a-col :span="24">
              <span style="display: block; font-weight: bold">
                {{ formatDate(record.datetime_start) }}
              </span>
              <span style="display: block">
                {{ formatTime(record.datetime_start) }}
              </span>
              <FlightPopover :data="record" />
            </a-col>
          </a-row>
        </template>
        <template v-else-if="column.key === 'client'">
          <!-- 
          <span style="display: block">{{ record.service.long_description }}</span> -->
          {{ record.file.client.name }}
        </template>
        <template v-else-if="column.key === 'vip'">
          <a-popover v-if="record.file.vip" title="Motivo VIP">
            <template #content>
              <a-row style="width: 250px">
                <a-col :span="24">
                  <p>{{ record.file.vip }}</p>
                </a-col>
              </a-row>
            </template>
            <StarFilled :style="{ color: '#ffcc00', fill: '#ffcc00' }" />
          </a-popover>
          <StarOutlined v-else :style="{ fill: '#BABCBD ' }" />
        </template>
        <template v-else-if="column.key === 'file'">
          <!-- {{ record.file.file_number }} {{ record.file.description }} -->
          <span style="display: block">
            <a
              href="javascript:void(0)"
              style="color: #1284ed; font-weight: bold; text-decoration: underline"
              @click="formStore.updateFileNumber(record.file.file_number)"
            >
              #{{ record.file.file_number }}
            </a>
            -
            {{ record.file.description }}
          </span>
        </template>
        <template v-else-if="column.key === 'lang'">
          {{ record.file.lang }}
        </template>
        <template v-else-if="column.key === 'pax'">
          {{ padWithZero(record.file.adults + record.file.children + record.file.infants) }}
        </template>
        <template v-else-if="column.key === 'executive'">
          <font-awesome-icon :icon="['fas', 'user']" class="executive-icon" />
          {{ record.file.executive_code }}
        </template>
        <template v-else-if="column.key === 'hotel'">
          <MatchedHotelsComponent :matched-hotels="record.matched_hotels" />
        </template>
        <template v-else-if="column.key === 'actions'">
          <a-space class="actions" style="gap: 4px">
            <!-- Ver información general y del servicio -->

            <a-button
              type="link"
              style="padding: 0"
              @click="
                console.log(record);
                modalStore.showModal(
                  'informationService',
                  'Ver información general y del servicio',
                  { data: record },
                  824
                );
              "
            >
              <ExclamationCircleOutlined />
            </a-button>

            <!-- Acciones... -->
            <a-dropdown overlayClassName="custom-dropdown-backend">
              <a-button type="link" style="padding: 0">
                <svg
                  width="28"
                  height="28"
                  viewBox="0 0 28 28"
                  fill="none"
                  xmlns="http://www.w3.org/2000/svg"
                  style="width: 23px"
                >
                  <rect x="0.5" y="0.5" width="27" height="27" rx="1.5" fill="#E4E5E6" />
                  <rect x="0.5" y="0.5" width="27" height="27" rx="1.5" stroke="#E4E5E6" />
                  <path
                    d="M19.7388 13.6464L19.3853 14L19.7388 14.3536L22.8588 17.4736C23.0796 17.6944 23.0824 18.0727 22.8566 18.307C22.6292 18.532 22.2649 18.5313 22.0384 18.3048L18.1493 14.4156C17.922 14.1884 17.922 13.8225 18.1493 13.5952L22.0384 9.70605C22.2657 9.47882 22.6316 9.47882 22.8588 9.70605C23.0861 9.93329 23.0861 10.2992 22.8588 10.5264L19.7388 13.6464ZM17.4707 20H5.55404C5.23435 20 4.9707 19.7364 4.9707 19.4167C4.9707 19.097 5.23435 18.8333 5.55404 18.8333H17.4707C17.7904 18.8333 18.054 19.097 18.054 19.4167C18.054 19.7364 17.7904 20 17.4707 20ZM14.2207 14.5833H5.55404C5.23435 14.5833 4.9707 14.3197 4.9707 14C4.9707 13.6803 5.23435 13.4167 5.55404 13.4167H14.2207C14.5404 13.4167 14.804 13.6803 14.804 14C14.804 14.3197 14.5404 14.5833 14.2207 14.5833ZM5.55404 9.16667C5.23435 9.16667 4.9707 8.90302 4.9707 8.58333C4.9707 8.26364 5.23435 8 5.55404 8H17.4707C17.7904 8 18.054 8.26364 18.054 8.58333C18.054 8.90302 17.7904 9.16667 17.4707 9.16667H5.55404Z"
                    fill="#2F353A"
                    stroke="#2F353A"
                  />
                </svg>
              </a-button>
              <template #overlay>
                <a-menu>
                  <a-menu-item>Ver pautas</a-menu-item>
                </a-menu>
              </template>
            </a-dropdown>
          </a-space>
        </template>
        <template v-else>
          {{ record[column.key] }}
        </template>
      </template>
    </a-table>
  </div>
</template>

<script lang="ts" setup>
  import dayjs from 'dayjs';
  import { computed, ref } from 'vue';
  import {
    FileOutlined,
    StarOutlined,
    StarFilled,
    ExclamationCircleOutlined,
  } from '@ant-design/icons-vue';
  import { useColumnStore } from '@operations/shared/stores/column.store';
  import { useFormStore } from '@operations/modules/service-management/store/form.store';
  import { useDataStore } from '@operations/modules/service-management/store/data.store';
  import FlightPopover from './FlightPopoverComponent.vue';
  import MatchedHotelsComponent from '../MatchedHotelsComponent.vue';
  import { padWithZero } from '../../utils/padWithZero';
  import { useModalStore } from '../../stores/modal.store';
  import { useBooleans } from '@/composables/useBooleans';

  const { setValue } = useBooleans();

  const tableRef = ref(null);

  const columnStore = useColumnStore();
  const formStore = useFormStore();
  const dataStore = useDataStore();
  const modalStore = useModalStore();

  // Obtenemos las columnas desde el store
  const columns = columnStore.getColumnsByType('nested');

  // const selectedRowKeys = ref<number[]>([]);
  // const selectedRows = ref<[]>([]);

  const props = defineProps<{
    files: any;
    record: any;
  }>();

  const equivalence_id = computed(() => props.record.equivalence_id);
  const filesData = props?.files.map((file: any) => {
    return {
      ...file,
      equivalence_id: equivalence_id.value,
    };
  });

  const formatDate = (date: string): string => {
    return dayjs(date).format('DD/MM');
  };

  const formatTime = (dateStart: string): string => {
    return dayjs(dateStart).format('HH:mm');
  };

  const selectedRowKeys = ref<string[]>([]);
  const selectedRows = ref<any[]>([]);

  const rowSelection = computed(() => ({
    selectedRowKeys: selectedRowKeys.value,
    onChange: (keys: string[], rows: any[]) => {
      selectedRowKeys.value = keys;
      selectedRows.value = rows;
      console.log('Seleccionados:', rows);
    },
  }));

  const emit = defineEmits(['closeExpand']);

  const unnestServices = (record: any) => {
    dataStore.handleUnnestServices(record, selectedRows.value);
    setTimeout(() => emit('closeExpand', record), 300);
  };
</script>
<style scoped lang="scss">
  .custom-select-period {
    :deep(.ant-select) {
      .ant-select-selector {
        border: none !important;
        color: #2f353a;
        font-style: italic;
        width: auto !important;

        &:hover {
          color: #004e96;
        }
      }
    }
  }

  .executive-icon {
    font-size: 12px;
    color: #1284ed;
    margin-right: 5px;
  }
</style>
