<template>
  <div>
    <a-card class="custom-card">
      <div class="d-flex" style="justify-content: space-between; align-items: center">
        <div>
          <div style="color: #575757; font-weight: 600; font-size: 24px">
            {{ filesStore.getServiceHotelCode[0]?.hotel_name || '' }}
          </div>
        </div>
        <div class="d-flex" style="flex-direction: column">
          <div style="text-align: right; font-weight: 400; font-size: 10px; color: #575757">
            Clasificación del proveedor
          </div>
          <div style="text-align: right" class="mt-2">
            <a-button type="primary" style="background-color: #e0453d">
              <svg
                class="mr-2"
                width="14"
                height="10"
                viewBox="0 0 16 14"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M15.0833 2.00016C15.3135 2.00016 15.5 1.8137 15.5 1.5835V0.750163C15.5 0.519954 15.3135 0.333496 15.0833 0.333496H0.916667C0.686458 0.333496 0.5 0.519954 0.5 0.750163V1.5835C0.5 1.8137 0.686458 2.00016 0.916667 2.00016H1.33281V12.0002H0.916667C0.686458 12.0002 0.5 12.1866 0.5 12.4168V13.2502C0.5 13.4804 0.686458 13.6668 0.916667 13.6668H7.16667V11.5835C7.16667 11.3543 7.35417 11.1668 7.58333 11.1668H8.41667C8.64583 11.1668 8.83333 11.3543 8.83333 11.5835V13.6668H15.0833C15.3135 13.6668 15.5 13.4804 15.5 13.2502V12.4168C15.5 12.1866 15.3135 12.0002 15.0833 12.0002H14.6667V2.00016H15.0833ZM7.16667 3.16683C7.16667 3.00016 7.33333 2.8335 7.5 2.8335H8.5C8.66667 2.8335 8.83333 3.00016 8.83333 3.16683V4.16683C8.83333 4.3335 8.66667 4.50016 8.5 4.50016H7.5C7.33333 4.50016 7.16667 4.3335 7.16667 4.16683V3.16683ZM7.16667 5.66683C7.16667 5.50016 7.33333 5.3335 7.5 5.3335H8.5C8.66667 5.3335 8.83333 5.50016 8.83333 5.66683V6.66683C8.83333 6.8335 8.66667 7.00016 8.5 7.00016H7.5C7.33333 7.00016 7.16667 6.8335 7.16667 6.66683V5.66683ZM3.83333 3.16683C3.83333 3.00016 4 2.8335 4.16667 2.8335H5.16667C5.33333 2.8335 5.5 3.00016 5.5 3.16683V4.16683C5.5 4.3335 5.33333 4.50016 5.16667 4.50016H4.16667C4 4.50016 3.83333 4.3335 3.83333 4.16683V3.16683ZM5.16667 7.00016H4.16667C4 7.00016 3.83333 6.8335 3.83333 6.66683V5.66683C3.83333 5.50016 4 5.3335 4.16667 5.3335H5.16667C5.33333 5.3335 5.5 5.50016 5.5 5.66683V6.66683C5.5 6.8335 5.33333 7.00016 5.16667 7.00016ZM5.5 10.3335C5.5 8.95277 6.61927 7.8335 8 7.8335C9.38073 7.8335 10.5 8.95277 10.5 10.3335H5.5ZM12.1667 6.66683C12.1667 6.8335 12 7.00016 11.8333 7.00016H10.8333C10.6667 7.00016 10.5 6.8335 10.5 6.66683V5.66683C10.5 5.50016 10.6667 5.3335 10.8333 5.3335H11.8333C12 5.3335 12.1667 5.50016 12.1667 5.66683V6.66683ZM12.1667 4.16683C12.1667 4.3335 12 4.50016 11.8333 4.50016H10.8333C10.6667 4.50016 10.5 4.3335 10.5 4.16683V3.16683C10.5 3.00016 10.6667 2.8335 10.8333 2.8335H11.8333C12 2.8335 12.1667 3.00016 12.1667 3.16683V4.16683Z"
                  fill="white"
                />
              </svg>
              {{ filesStore.getFilePublic?.category || 'Sin categoría' }}
            </a-button>
          </div>
        </div>
      </div>
      <hr class="custom-hr" />
      <div class="d-flex mt-3 mb-3" style="justify-content: space-between; align-items: center">
        <div class="d-flex" style="display: flex; align-items: center">
          <div class="mr-4" style="color: #2e2e2e; font-size: 18px; font-weight: 600">
            Número de file:
          </div>
          <span class="color-red font-600">{{ props.nroFile }}</span>
        </div>
        <div class="d-flex">
          <div class="font-500 mr-8" style="display: flex; align-items: center">
            <svg
              class="mr-4"
              width="10"
              height="16"
              viewBox="0 0 12 21"
              fill="#eb5757"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M4.74996 13.0038V19.1246L5.61014 20.4144C5.79569 20.6926 6.20468 20.6926 6.39023 20.4144L7.25001 19.1246V13.0038C6.84414 13.0788 6.42734 13.1233 5.99999 13.1233C5.57264 13.1233 5.15583 13.0788 4.74996 13.0038ZM5.99999 0.623047C2.89329 0.623047 0.374878 3.14146 0.374878 6.24816C0.374878 9.35486 2.89329 11.8733 5.99999 11.8733C9.10669 11.8733 11.6251 9.35486 11.6251 6.24816C11.6251 3.14146 9.10669 0.623047 5.99999 0.623047ZM5.99999 3.59185C4.53512 3.59185 3.34369 4.78328 3.34369 6.24816C3.34369 6.50676 3.13353 6.71692 2.87493 6.71692C2.61633 6.71692 2.40617 6.50676 2.40617 6.24816C2.40617 4.26648 4.0187 2.65434 5.99999 2.65434C6.25859 2.65434 6.46875 2.8645 6.46875 3.1231C6.46875 3.38169 6.25859 3.59185 5.99999 3.59185Z"
                fill="#eb5757"
              ></path>
            </svg>
            <span style="color: #575757; font-weight: 600; font-size: 16px">
              {{ filesStore.getServiceHotelCode[0]?.city || '' }}
            </span>
          </div>
          <div class="d-flex" style="align-items: center">
            <div class="mr-2">
              <a-avatar style="background-color: #eb5757" size="small">
                <template #icon>
                  <UserOutlined />
                </template>
              </a-avatar>
            </div>
            <a href="#" style="text-decoration: underline" class="color-gray"
              >Nombre de contacto:
              {{ filesStore.getServiceProviders?.hotel_contacts?.[0] || '' }}</a
            >
          </div>
        </div>
      </div>
      <hr class="custom-hr" />
      <div class="d-flex">
        <div class="font-600 mr-4 mt-2" style="color: #3d3d3d">Nombre del grupo:</div>
        <div class="mt-2">{{ filesStore.getFilePublic?.description }}</div>
      </div>
      <div class="d-flex">
        <div class="font-600 mr-4 mt-2" style="color: #3d3d3d">Cantidad del pax:</div>
        <div class="mt-2">
          {{
            filesStore.getFilePublic?.adults
              ? String(filesStore.getFilePublic?.adults).padStart(2, '0')
              : '00'
          }}
          adultos &nbsp;
          {{
            filesStore.getFilePublic?.children
              ? String(filesStore.getFilePublic?.children).padStart(2, '0')
              : '00'
          }}
          niños
        </div>
      </div>
      <div class="d-flex">
        <div class="font-600 mr-4 mt-2" style="color: #3d3d3d">Nacionalidad:</div>
        <div class="mt-2">{{ filesStore.getServiceProviders?.client?.pais || '' }}</div>
      </div>
    </a-card>
  </div>
  <div>
    <template v-if="filesStore.getServiceHotelCode?.length > 0">
      <template v-if="filesStore.getServiceHotelCode?.length > 0">
        <!-- <div class="reservations-title my-4">
          <h6 class="mb-0">Hoteles</h6>
        </div> -->

        <div class="reservations-content">
          <div class="mt-3">
            <div class="files-edit__service-row-2" v-show="props.loading">
              <a-skeleton active />
            </div>
          </div>
        </div>
      </template>
      <table
        v-show="!props.loading"
        class="ant-table ant-table-small"
        style="width: 100%; border-spacing: 0 10px; border-collapse: separate"
      >
        <thead class="ant-table-thead">
          <tr>
            <td
              class="ant-table-cell p-2 text-700"
              style="color: #3d3d3d"
              v-for="(column, c) in columnsHotels"
              v-bind:key="`column-${c}`"
            >
              {{ column.title }}
            </td>
          </tr>
        </thead>
        <tbody class="ant-table-tbody">
          <template
            v-for="(record, r) in filesStore.getServiceHotelCode"
            v-bind:key="`record-${r}`"
          >
            <template v-if="props.codeHotel == record.object_code">
              <tr
                class="ant-table-row"
                :style="`background-color: ${record.flagViewRecord ? '#E9E9E9' : '#FAFAFA'}; color: #4F4B4B;`"
              >
                <td class="ant-table-cell p-2" v-for="(column, c) in columnsHotels">
                  <template
                    v-if="column.dataIndex === 'date_in' || column.dataIndex === 'date_out'"
                  >
                    {{ formatDate(record[column.dataIndex]) }}
                  </template>
                  <template
                    v-else-if="
                      column.dataIndex === 'hotel_name' || column.dataIndex === 'room_name'
                    "
                  >
                    <a-tooltip :title="record[column.dataIndex]">
                      {{ truncateString(record[column.dataIndex], 20) }}
                    </a-tooltip>
                  </template>
                  <template v-else-if="column.dataIndex === 'status'">
                    <Status :record="record" type="hotel" />
                  </template>
                  <template v-else-if="column.dataIndex === 'confirmation_code'">
                    <a-row type="flex" justify="start" align="middle" style="gap: 5px">
                      <template
                        v-if="
                          record.flag_editable ||
                          record.confirmation_code == '' ||
                          record.confirmation_code == null
                        "
                      >
                        <a-col>
                          <a-input
                            size="small"
                            v-model:value="record.alt_confirmation_code"
                            :placeholder="
                              record.confirmation_code != null && record.confirmation_code != ''
                                ? record.confirmation_code
                                : ''
                            "
                            :disabled="record.blocked"
                            style="width: 100px"
                          />
                        </a-col>
                        <a-col>
                          <IconSaveAlt v-if="!record.blocked" @click="saveRecord('room', record)" />
                        </a-col>
                      </template>
                      <template v-else>
                        <template v-if="!record.flag_editable">
                          <a-col>
                            <span>{{ record.confirmation_code || ' - ' }}</span>
                          </a-col>
                          <a-col>
                            <IconEdit @click="unLockedRecord(record)" />
                          </a-col>
                        </template>
                        <a-col v-else>
                          <IconCircleCheck
                            v-if="!record.blocked"
                            @click="saveRecordOK('room', record)"
                            color="#3D3D3D"
                            width="19"
                            height="20"
                          />
                        </a-col>
                      </template>
                      <a-col v-if="record.units.length > 1 && !record.flag_editable">
                        <IconPlusCircle @click="toggleRecord(record)" />
                      </a-col>
                    </a-row>
                  </template>
                  <template v-else>
                    {{ record[column.dataIndex] }}
                  </template>
                </td>
              </tr>
              <template v-if="record.units.length > 1 && record.flagViewRecord">
                <tr v-for="(unit, u) in record.units">
                  <td v-for="(column, c) in columnsHotels">
                    <template v-if="column.dataIndex === 'quantity'">
                      <CornerDownRight />
                    </template>
                    <template v-if="column.dataIndex === 'room_name'">
                      {{ unit[column.dataIndex] }}
                    </template>
                    <template v-else-if="column.dataIndex === 'status'">
                      <a-tag v-if="unit.status == 'RQ'" color="error" :bordered="false"> RQ </a-tag>
                      <a-tag v-if="unit.status == 'OK'" color="success" :bordered="false">
                        OK
                      </a-tag>
                      <a-tag v-if="unit.status == 'WL'" color="warning" :bordered="false">
                        WL
                      </a-tag>
                    </template>
                    <template v-else-if="column.dataIndex === 'confirmation_code'">
                      <a-row type="flex" justify="start" align="middle" style="gap: 5px">
                        <template
                          v-if="
                            (typeof unit.flag_editable == 'undefined' &&
                              unit.confirmation_code == null) ||
                            unit.flag_editable
                          "
                        >
                          <a-col>
                            <a-input
                              ref="myInput"
                              size="small"
                              v-model:value="unit.confirmation_code"
                              @input="
                                (e) =>
                                  updateStore(
                                    record.id,
                                    unit.id,
                                    'confirmation_code',
                                    e.target.value
                                  )
                              "
                              @change="handleChange"
                              :disabled="unit.blocked"
                              style="width: 100px"
                            />
                          </a-col>
                        </template>
                        <template
                          v-if="
                            (typeof unit.flag_editable == 'undefined' &&
                              unit.confirmation_code == null) ||
                            unit.flag_editable
                          "
                        >
                          <a-col>
                            <IconSaveAlt
                              v-if="!unit.blocked"
                              @click="saveRecordOK('room-unit', unit)"
                            />
                          </a-col>
                          <!-- <a-col>
                                    <IconPlusCircle @click="toggleRecord(unit)" />
                                    </a-col> -->
                        </template>
                        <template v-else>
                          <a-col>
                            <span>{{ unit.confirmation_code || ' - ' }}</span>
                          </a-col>
                          <a-col>
                            <IconEdit @click="unit.flag_editable = true" />
                          </a-col>
                        </template>
                        <!-- <template v-if="!unit.flag_editable">
                                <a-col>
                                    <span>{{ unit.confirmation_code || ' - ' }}</span>
                                </a-col>
                                <a-col>
                                    <IconEdit @click="unit.flag_editable = true" />
                                </a-col>
                                </template>
                                <a-col v-else>
                                <IconSaveAlt
                                    v-if="!unit.blocked"
                                    @click="saveRecordOK('unit', unit)"
                                    color="#3D3D3D"
                                    width="19"
                                    height="20"
                                />
                                </a-col> -->
                      </a-row>
                    </template>
                  </td>
                </tr>
              </template>
            </template>
          </template>
        </tbody>
      </table>
    </template>
  </div>
</template>
<script setup>
  import { ref } from 'vue';
  import { debounce } from 'lodash-es';
  import { notification } from 'ant-design-vue';
  import { useFilesStore } from '@/stores/files';
  import { formatDate, truncateString } from '@/utils/files.js';
  import { useI18n } from 'vue-i18n';
  import IconSaveAlt from '@/components/icons/IconSaveAlt.vue';
  import IconPlusCircle from '@/components/icons/IconPlusCircle.vue';
  import IconCircleCheck from '@/components/icons/IconCircleCheck.vue';
  import IconEdit from '@/components/icons/IconEdit.vue';
  import CornerDownRight from '@/components/icons/CornerDownRight.vue';
  import Status from './Status.vue';
  import { UserOutlined } from '@ant-design/icons-vue';

  const props = defineProps({
    codeHotel: {
      type: String,
      required: true,
    },
    nroFile: {
      type: String,
      required: true,
    },
    loading: {
      type: Boolean,
    },
  });

  // const loading = ref(false);
  const filesStore = useFilesStore();

  const myInput = ref(null);

  useI18n({
    useScope: 'global',
  });

  const toggleRecord = (record) => {
    record.flagViewRecord = !record.flagViewRecord;
    // record.flag_editable = !record.flag_editable;
    console.log('Record: ', record);
  };

  const columnsHotels = [
    {
      title: 'Llegada',
      dataIndex: 'date_in',
      sorter: false,
    },
    {
      title: 'Salida',
      dataIndex: 'date_out',
      sorter: false,
    },
    {
      title: 'Ciudad',
      dataIndex: 'city',
      sorter: false,
    },
    {
      title: 'Hotel',
      dataIndex: 'hotel_name',
      sorter: false,
      ellipsis: true,
      width: 180,
    },
    {
      title: 'Cantidad',
      dataIndex: 'quantity',
      sorter: false,
      width: 80,
    },
    {
      title: 'Tipo de habitación',
      dataIndex: 'room_name',
      sorter: false,
      ellipsis: true,
      width: 180,
    },
    {
      title: 'Estado',
      dataIndex: 'status',
      sorter: false,
    },
    {
      title: 'Código de confirmación',
      dataIndex: 'confirmation_code',
      width: 170,
      sorter: false,
    },
  ];

  // const setLoading = async (status = false) => {
  //     loading.value = status;
  //     console.log('debe de cargar');
  // };

  const saveRecord = debounce(async (type, record) => {
    record.flag_editable = false;

    if (
      typeof record.alt_confirmation_code == 'undefined' ||
      record.alt_confirmation_code === null ||
      record.alt_confirmation_code === ''
    ) {
      return false;
    }

    localStorage.setItem('confirmation_code', record.alt_confirmation_code);
    record.blocked = true;
    record.confirmation_code = localStorage.getItem('confirmation_code');

    setTimeout(async () => {
      localStorage.removeItem('confirmation_code');
      await filesStore.saveConfirmationCode({
        type,
        id: record.id,
        confirmation_code: record.confirmation_code,
        itinerary_id: record.file_itinerary_id,
        file_number: filesStore.getFilePublic.file_number,
      });
      record.blocked = false;

      if (filesStore.getError != '') {
        notification['error']({
          message: `Ocurrió un Error`,
          description: filesStore.getError,
          duration: 5,
        });
      } else {
        record.flag_editable = false;
      }
    }, 10);
  }, 350);

  const saveRecordOK = debounce(async (type, record) => {
    if (record.confirmation_code != null && record.confirmation_code != '') {
      record.blocked = true;
      await filesStore.saveConfirmationCode({
        type,
        id: record.id,
        confirmation_code: record.confirmation_code,
        itinerary_id: record.file_itinerary_id,
        file_number: filesStore.getFilePublic.file_number,
      });
      record.blocked = false;

      if (filesStore.getError != '') {
        notification['error']({
          message: `Ocurrió un Error`,
          description: filesStore.getError,
          duration: 5,
        });
      } else {
        record.flag_editable = false;
      }
    } else {
      record.flag_editable = false;
    }
  }, 350);

  const unLockedRecord = (record) => {
    record.flag_editable = true;
    record.blocked = false;

    console.log('Record: ', record);
  };

  const updateStore = (reportId, unitId, key, value) => {
    filesStore.addNewHotelsUnitPropertyCode(reportId, unitId, 'flag_editable', true);
    filesStore.updateHotelsUnitPropertyCode(reportId, unitId, key, value);

    console.log(filesStore.getServiceHotelCode);
  };

  const handleChange = (e) => {
    if (!e || !e.target) return; // Previene el error si el evento es inválido
  };
</script>
<style scoped>
  .color-black {
    color: #575757;
  }
  .color-red {
    color: #eb5757;
  }

  .color-gray {
    color: #979797;
  }

  .font-500 {
    font-weight: 500;
  }

  .font-600 {
    font-weight: 600;
  }

  .font-700 {
    font-weight: 700;
  }

  .mr-2 {
    margin-right: 3px;
  }

  .mr-4 {
    margin-right: 6px;
  }

  .mb-2 {
    margin-bottom: 3px;
  }

  .mb-4 {
    margin-bottom: 6px;
  }

  .mr-6 {
    margin-right: 9px;
  }

  .mr-8 {
    margin-right: 12px;
  }

  .size-11 {
    font-size: 11px;
  }

  .size-16 {
    font-size: 16px;
  }

  hr {
    border: 0.5px;
    border-style: solid;
    color: #e9e9e9;
  }

  .custom-card {
    /* border: 1px solid #E9E9E9 !important; */
  }

  .custom-card ::v-deep(.ant-card-body) {
    padding: 15px 30px; /* Ajusta el padding si es necesario */
    border-radius: 6px !important;
    border: 1px solid !important;
  }

  .custom-hr {
    border: none;
    height: 1px;
    background-color: #e9e9e9;
    margin: 16px 0; /* Ajusta los márgenes según necesites */
  }
</style>
