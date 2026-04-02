<template>
  <div class="table-negotiation-contacts">
    <div class="custom-tab-content">
      <div class="header-table">
        <div class="title-table">Listado de contactos</div>
        <div>
          <a-button type="primary" class="button-option" @click="handleVisibleModal">
            <font-awesome-icon icon="fa-solid fa-plus" /> Crear contacto
          </a-button>
        </div>
      </div>
      <a-table
        :dataSource="state.dataSource"
        :columns="columns"
        :loading="state.isLoading"
        :pagination="false"
      >
        <template #bodyCell="{ column, record }">
          <template v-if="column.key === 'type'">
            <div>{{ record.typeContact.name }}</div>
          </template>
          <template v-else-if="column.key === 'position'">
            <div>{{ record.department.name }}</div>
          </template>
          <template v-else-if="column.key === 'names'">
            <div>{{ `${record.firstname} ${record.surname}` }}</div>
          </template>
          <template v-else-if="column.key === 'city'">
            <div>{{ record.supplierBranchOffice.state.name }}</div>
          </template>
          <template v-else-if="column.key === 'action'">
            <div class="options">
              <div>
                <font-awesome-icon
                  :icon="['far', 'trash-can']"
                  class="cursor-pointer"
                  @click="deleteApiContact(record.id)"
                  :style="{ height: '20px' }"
                />
              </div>
              <div>
                <font-awesome-icon
                  :icon="['far', 'pen-to-square']"
                  class="cursor-pointer"
                  @click="editApiContact(record.id, record)"
                  :style="{ height: '20px' }"
                />
              </div>
            </div>
          </template>
        </template>
      </a-table>
      <CustomPagination
        v-if="state.dataSource.length > 0"
        v-model:current="state.pagination.current"
        v-model:pageSize="state.pagination.pageSize"
        :total="state.pagination.total"
        :disabled="state.dataSource?.length === 0"
        :showSizeChanger="false"
        @change="handleOnChange"
      />
    </div>
    <NegotiationContactsModalComponent />
  </div>
</template>

<script setup lang="ts">
  import CustomPagination from '@/components/global/CustomPaginationComponent.vue';
  import NegotiationContactsModalComponent from '@/modules/negotiations/supplier/register/configuration-module/components/NegotiationContactsModalComponent.vue';
  import { useNegotiationContacts } from '@/modules/negotiations/supplier/register/configuration-module/composables/useNegotiationContacts';

  const columns = [
    {
      title: 'Cargo',
      dataIndex: 'position',
      key: 'position',
      align: 'left',
    },
    {
      title: 'Nombre y apellido',
      dataIndex: 'names',
      key: 'names',
      align: 'left',
    },
    {
      title: 'Tipo',
      dataIndex: 'type',
      key: 'type',
      align: 'left',
    },
    {
      title: 'Ciudad',
      dataIndex: 'city',
      key: 'city',
      align: 'left',
    },
    {
      title: 'Correo',
      dataIndex: 'email',
      key: 'email',
      align: 'left',
    },
    {
      title: 'Teléfono',
      dataIndex: 'phone',
      key: 'phone',
      align: 'left',
    },
    {
      title: 'Acciones',
      dataIndex: 'action',
      key: 'action',
      align: 'center',
    },
  ];

  const { handleVisibleModal, handleOnChange, deleteApiContact, editApiContact, state } =
    useNegotiationContacts();
</script>

<style>
  .table-negotiation-contacts {
    .header-table {
      display: flex;
      justify-content: space-between;
      align-items: end;
      margin-bottom: 6px;

      .title-table {
        font-size: 14px;
        color: #7e8285;
        text-align: justify;
      }

      .button-option {
        height: 45px;
        font-weight: 500;
        font-size: 16px;
        line-height: 24px;
        text-align: center;
      }
    }

    .ant-table-container {
      .ant-table-cell {
        border-radius: 0 !important;

        &:before {
          display: none;
        }
      }
    }

    :where(.css-dev-only-do-not-override-w750bm).ant-table-wrapper,
    .ant-table:not(.ant-table-bordered),
    .ant-table-tbody,
    > tr,
    > td {
      border-bottom: 1px solid transparent;
    }

    .options {
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 10px;
      cursor: pointer;
      height: 20px;
    }
  }
</style>
