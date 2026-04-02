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
        <a-button
          href="javascript:void(0)"
          class="ant-btn-lg"
          @click="dataStore.handleUnnestServices(record, files?.selectedRows)"
        >
          Desanidar
        </a-button>
        <a-button type="primary" class="ant-btn-lg" @click="$emit('closeExpand', record)">
          <font-awesome-icon :icon="['fas', 'xmark']" />
          Cerrar
        </a-button>
      </a-space>
    </a-flex>

    <a-table
      ref="tableRef"
      rowKey="id"
      table-layout="fixed"
      expand-icon-as-cell="false"
      expand-icon-column-index="{-1}"
      :loading="false"
      :columns="columns"
      :dataSource="files"
      :row-key="(files: any) => files.file._id"
      :pagination="false"
      :row-selection="getRowSelection(files)"
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
                {{ formatTime(record.datetime_start, record.datetime_end) }}
              </span>
              <a-popover v-if="record.service.type === 'TIN' || record.service.type === 'TOT'">
                <template #content>
                  <a-row style="margin-bottom: 7px; width: 200px">
                    <a-col :span="24">
                      <a-flex justify="space-between" align="center">
                        <a-typography-text style="font-size: 14px; color: #1284ed; fill: #1284ed">
                          <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 640 512"
                            style="width: 17px"
                          >
                            <path
                              d="M.3 166.9L0 68C0 57.7 9.5 50.1 19.5 52.3l35.6 7.9c10.6 2.3 19.2 9.9 23 20L96 128l127.3 37.6L181.8 20.4C178.9 10.2 186.6 0 197.2 0l40.1 0c11.6 0 22.2 6.2 27.9 16.3l109 193.8 107.2 31.7c15.9 4.7 30.8 12.5 43.7 22.8l34.4 27.6c24 19.2 18.1 57.3-10.7 68.2c-41.2 15.6-86.2 18.1-128.8 7L121.7 289.8c-11.1-2.9-21.2-8.7-29.3-16.9L9.5 189.4c-5.9-6-9.3-14.1-9.3-22.5zM32 448l576 0c17.7 0 32 14.3 32 32s-14.3 32-32 32L32 512c-17.7 0-32-14.3-32-32s14.3-32 32-32zm96-80a32 32 0 1 1 64 0 32 32 0 1 1 -64 0zm128-16a32 32 0 1 1 0 64 32 32 0 1 1 0-64z"
                            />
                          </svg>
                          Miami <span style="color: #babcbd">(MIA)</span>
                        </a-typography-text>
                        <a-typography-text style="font-size: 14px; color: #1284ed; fill: #1284ed">
                          <svg
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 640 512"
                            style="width: 17px"
                          >
                            <path
                              d="M381 114.9L186.1 41.8c-16.7-6.2-35.2-5.3-51.1 2.7L89.1 67.4C78 73 77.2 88.5 87.6 95.2l146.9 94.5L136 240 77.8 214.1c-8.7-3.9-18.8-3.7-27.3 .6L18.3 230.8c-9.3 4.7-11.8 16.8-5 24.7l73.1 85.3c6.1 7.1 15 11.2 24.3 11.2l137.7 0c5 0 9.9-1.2 14.3-3.4L535.6 212.2c46.5-23.3 82.5-63.3 100.8-112C645.9 75 627.2 48 600.2 48l-57.4 0c-20.2 0-40.2 4.8-58.2 14L381 114.9zM0 480c0 17.7 14.3 32 32 32l576 0c17.7 0 32-14.3 32-32s-14.3-32-32-32L32 448c-17.7 0-32 14.3-32 32z"
                            />
                          </svg>
                          Lima <span style="color: #babcbd">(LIM)</span>
                        </a-typography-text>
                      </a-flex>

                      <a-typography-text
                        style="display: block; color: #7e8285; font-size: 12px; line-height: 15px"
                        v-if="record.routes && record.routes[0]?.flight"
                      >
                        Vuelo
                        <span style="font-weight: 700">
                          {{ record.routes[0].flight.airline_number }}
                        </span>
                        -
                        {{ record.routes[0].flight.airline_name }}
                      </a-typography-text>

                      <a-typography-text
                        style="display: block; color: #7e8285; font-size: 12px; line-height: 15px"
                        v-if="record.routes && record.routes[0]?.flight"
                      >
                        PNR: {{ record.routes[0].flight.pnr }}
                      </a-typography-text>
                    </a-col>
                  </a-row>

                  <a-row>
                    <a-col :span="10">
                      <a-typography-text
                        type="secondary"
                        style="
                          font-size: 10px;
                          font-weight: 400;
                          display: block;
                          padding-left: 2px;
                          margin-bottom: 4px;
                        "
                      >
                        Salida:
                      </a-typography-text>
                      <a-typography-text
                        style="
                          font-size: 20px;
                          font-weight: 700;
                          line-height: 15px;
                          display: block;
                          word-wrap: break-word;
                          color: #2f353a;
                        "
                      >
                        10:00
                        <span
                          style="
                            font-size: 12px;
                            font-weight: 400;
                            color: #7e8285;
                            word-wrap: break-word;
                          "
                          >HRS</span
                        >
                      </a-typography-text>
                      <a-typography-text
                        type="secondary"
                        style="
                          font-size: 12px;
                          font-weight: 500;
                          display: block;
                          padding-left: 2px;
                          line-height: 15px;
                        "
                      >
                        20 Oct 2022
                      </a-typography-text>
                    </a-col>
                    <a-col :span="4" />
                    <a-col :span="10">
                      <a-typography-text
                        type="secondary"
                        style="
                          font-size: 10px;
                          font-weight: 400;
                          display: block;
                          padding-left: 2px;
                          margin-bottom: 4px;
                        "
                      >
                        Llegada:
                      </a-typography-text>
                      <a-typography-text
                        style="
                          font-size: 20px;
                          font-weight: 700;
                          line-height: 15px;
                          display: block;
                          word-wrap: break-word;
                          color: #2f353a;
                        "
                      >
                        16:00
                        <span
                          style="
                            font-size: 12px;
                            font-weight: 400;
                            color: #7e8285;
                            word-wrap: break-word;
                          "
                          >HRS</span
                        >
                      </a-typography-text>
                      <a-typography-text
                        type="secondary"
                        style="
                          font-size: 12px;
                          font-weight: 500;
                          display: block;
                          padding-left: 2px;
                          line-height: 15px;
                        "
                      >
                        20 Oct 2022
                      </a-typography-text>
                    </a-col>
                  </a-row>

                  <a-divider style="margin: 10px 0 5px 0" />

                  <a-row>
                    <a-col :span="24" justify="space-around" align="middle">
                      <a-typography-text type="secondary" style="font-size: 10px; font-weight: 400">
                        Actualizado hace <span style="font-weight: 600">3h 11m</span>
                      </a-typography-text>
                    </a-col>
                  </a-row>
                </template>
                <a-button
                  type="link"
                  style="display: block; color: #1284ed; padding: 0; margin: 0"
                  v-if="record.service?.type === 'TIN' && record.routes && record.routes[0]?.flight"
                >
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 640 512"
                    style="width: 15px; fill: #1284ed; margin-top: 7px"
                  >
                    <path
                      v-if="record.service.type === 'TIN'"
                      d="M.3 166.9L0 68C0 57.7 9.5 50.1 19.5 52.3l35.6 7.9c10.6 2.3 19.2 9.9 23 20L96 128l127.3 37.6L181.8 20.4C178.9 10.2 186.6 0 197.2 0l40.1 0c11.6 0 22.2 6.2 27.9 16.3l109 193.8 107.2 31.7c15.9 4.7 30.8 12.5 43.7 22.8l34.4 27.6c24 19.2 18.1 57.3-10.7 68.2c-41.2 15.6-86.2 18.1-128.8 7L121.7 289.8c-11.1-2.9-21.2-8.7-29.3-16.9L9.5 189.4c-5.9-6-9.3-14.1-9.3-22.5zM32 448l576 0c17.7 0 32 14.3 32 32s-14.3 32-32 32L32 512c-17.7 0-32-14.3-32-32s14.3-32 32-32zm96-80a32 32 0 1 1 64 0 32 32 0 1 1 -64 0zm128-16a32 32 0 1 1 0 64 32 32 0 1 1 0-64z"
                    />
                  </svg>
                  {{ record.routes[0].flight.pnr }}
                </a-button>
              </a-popover>
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
          {{ record.file.adults + record.file.children + record.file.infants }}
        </template>
        <template v-else-if="column.key === 'executive'">
          {{ record.file.executive_code }}
        </template>
        <template v-else-if="column.key === 'hotel'">
          <!-- {{ record.routes }} -->
          <span v-for="route in record.routes" :key="route" style="display: block">
            <template v-if="route.pickup_point.type === 'HOTEL'">
              {{ route.pickup_point.provider.fullname }}
            </template>
          </span>
        </template>
        <template v-else-if="column.key === 'actions'">
          <a-space class="actions" style="gap: 4px">
            <!-- Ver información general y del servicio -->
            <a-button type="link" style="padding: 0" @click="">
              <i class="icon-info" style="margin: 0"></i>
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
  import moment from 'moment';
  import { ref } from 'vue';
  import { FileOutlined, StarOutlined, StarFilled } from '@ant-design/icons-vue';
  import { useColumnStore } from '@operations/shared/stores/column.store';
  import { useFormStore } from '@operations/modules/service-management/store/form.store';
  import { useDataStore } from '@operations/modules/service-management/store/data.store';

  const tableRef = ref(null);

  const columnStore = useColumnStore();
  const formStore = useFormStore();
  const dataStore = useDataStore();
  // Obtenemos las columnas desde el store
  const columns = columnStore.getColumnsByType('nested');

  // const selectedRowKeys = ref<number[]>([]);
  // const selectedRows = ref<[]>([]);

  defineProps({
    files: Object,
    record: Object,
  });

  const formatDate = (date: string): string => {
    return moment(date).format('DD/MM');
  };

  const formatTime = (dateStart: string, dateEnd: string): string => {
    const formattedDateStart = moment(dateStart).format('HH:mm');
    const formattedDateEnd = moment(dateEnd).format('HH:mm');
    if (formattedDateStart === formattedDateEnd) return formattedDateStart;
    else return `${formattedDateStart} - ${formattedDateEnd}`;
  };

  const getRowSelection = (files: any) => {
    // Asegúrate de que cada location tenga su propio estado de selección
    if (!files.selectedRowKeys) {
      files.selectedRowKeys = [];
    }

    return {
      selectedRowKeys: files.selectedRowKeys,
      onChange: (keys: any, rows: any) => {
        files.selectedRowKeys = keys; // Guarda las claves seleccionadas solo para esa tabla
        files.selectedRows = rows;
      },
    };
  };

  // const getRowClassName = (record: any) => {
  //   // Si la fila está expandida, aplicar la clase 'expanded-row'
  //   if (expandedRowKeys.value.includes(record.id)) {
  //     return 'expanded-row'; // Aplicar clase específica solo para la fila expandida
  //   }

  //   // Puedes aplicar la lógica de filas seleccionadas u otras condiciones aquí si es necesario.
  //   return '';
  // };

  // const rowSelection = {
  //   onChange: (keys: number[], rows: []) => {
  //     selectedRowKeys.value = keys;
  //     selectedRows.value = rows;
  //     // selectionStore.selectedOperationalServiceItems = rows;
  //   },
  // };

  // const rowSelection = {
  //   onChange: (keys: number[], rows: []) => {
  //     selectedRowKeys.value = keys; // Actualiza las claves seleccionadas
  //     selectedRows.value = rows; // Actualiza las filas seleccionadas
  //   },
  //   onSelect: (record: any, selected: boolean) => {
  //     if (selected) {
  //       selectedRowKeys.value.push(record.id);
  //     } else {
  //       const index = selectedRowKeys.value.indexOf(record.id);
  //       if (index > -1) {
  //         selectedRowKeys.value.splice(index, 1);
  //       }
  //     }
  //   },
  //   onSelectAll: (selected: boolean, selectedRows: any[]) => {
  //     if (!selected) {
  //       selectedRowKeys.value = []; // Vacía la selección si se desactiva "seleccionar todo"
  //     } else {
  //       selectedRowKeys.value = selectedRows.map((row) => row.id); // Selecciona todas las claves disponibles
  //     }
  //   },
  //   getCheckboxProps: (record: any) => ({
  //     disabled: record.someCondition, // Opcional: deshabilita filas específicas según una condición
  //   }),
  // };
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
</style>
