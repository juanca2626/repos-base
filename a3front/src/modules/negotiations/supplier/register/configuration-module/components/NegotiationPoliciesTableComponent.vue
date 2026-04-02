<template>
  <div class="table-negotiation-policies">
    <div class="custom-tab-content">
      <div class="header-table">
        <div class="title-table">Listado de políticas</div>
        <div>
          <a-button type="primary" class="button-option" @click="handleVisibleModal">
            <font-awesome-icon icon="fa-solid fa-plus" /> Crear política
          </a-button>
        </div>
      </div>
      <a-table
        :dataSource="dataSource"
        :columns="columns"
        :loading="state.isLoading"
        :pagination="false"
      >
        <template #bodyCell="{ column, record }">
          <template v-if="column.key === 'policy'">
            <div>
              {{ `Política para ${record.supplierBusinessGroupPolicy?.business_group?.name}` }}
            </div>
          </template>
          <template v-else-if="column.key === 'pax'">
            <div>{{ `${record.pax_min} - ${record.pax_max} pax` }}</div>
          </template>
          <template v-else-if="column.key === 'name'">
            <div>{{ `-` }}</div>
          </template>
          <template v-else-if="column.key === 'period'">
            <div>{{ `-` }}</div>
          </template>
          <template v-else-if="column.key === 'status'">
            <div>
              <a-switch
                v-model:checked="record.status"
                @change="updateStatusApiPolicy(record.id, record)"
              />
            </div>
          </template>
          <template v-else-if="column.key === 'action'">
            <div class="options">
              <div>
                <font-awesome-icon
                  :icon="['far', 'trash-can']"
                  class="cursor-pointer"
                  @click="deleteApiPolicy(record.id)"
                  :style="{ height: '20px' }"
                />
              </div>
              <div>
                <font-awesome-icon
                  :icon="['far', 'pen-to-square']"
                  class="cursor-pointer"
                  @click="showApiPolicy(record.id)"
                  :style="{ height: '20px' }"
                />
              </div>
              <div>
                <font-awesome-icon :icon="['fas', 'circle-info']" :style="{ height: '20px' }" />
              </div>
            </div>
          </template>
        </template>
      </a-table>
      <CustomPagination
        v-if="dataSource.length > 0"
        v-model:current="state.pagination.current"
        v-model:pageSize="state.pagination.pageSize"
        :total="state.pagination.total"
        :disabled="dataSource?.length === 0"
        :showSizeChanger="false"
        @change="handleOnChange"
      />
      <NegotiationPoliciesModalComponent />
    </div>
  </div>
</template>

<script setup lang="ts">
  import CustomPagination from '@/components/global/CustomPaginationComponent.vue';
  import NegotiationPoliciesModalComponent from '@/modules/negotiations/supplier/register/configuration-module/components/NegotiationPoliciesModalComponent.vue';
  import { UseNegotiationPolicies } from '@/modules/negotiations/supplier/register/configuration-module/composables/useNegotiationPolicies';

  const columns = [
    {
      title: 'Politicas',
      dataIndex: 'policy',
      key: 'policy',
      align: 'left',
    },
    {
      title: 'Nombre',
      dataIndex: 'name',
      key: 'name',
      align: 'left',
    },
    {
      title: 'Rango de pax',
      dataIndex: 'pax',
      key: 'pax',
      align: 'left',
    },
    {
      title: 'Periodo',
      dataIndex: 'period',
      key: 'period',
      align: 'left',
    },
    {
      title: 'Estado',
      dataIndex: 'status',
      key: 'status',
      align: 'left',
    },
    {
      title: 'Acciones',
      dataIndex: 'action',
      key: 'action',
      align: 'center',
    },
  ];

  const {
    handleVisibleModal,
    handleOnChange,
    state,
    deleteApiPolicy,
    showApiPolicy,
    updateStatusApiPolicy,
    dataSource,
  } = UseNegotiationPolicies();
</script>

<style>
  .table-negotiation-policies {
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
