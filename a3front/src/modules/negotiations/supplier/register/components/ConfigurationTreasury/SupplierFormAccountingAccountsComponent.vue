<template>
  <div class="form-accounting-accounts-component">
    <a-spin :spinning="false">
      <div>
        <div v-if="hasSelected" class="selected-items">
          <div>
            {{ `Tienes ${state.selectedRowKeys.length} items seleccionado(s)` }}
          </div>
          <div class="delete-items" @click.prevent="onDeleteBatch">
            <font-awesome-icon :icon="['far', 'trash-can']" />
            <span> Eliminar item(s)</span>
          </div>
        </div>
        <a-table
          :row-selection="{ selectedRowKeys: state.selectedRowKeys, onChange: onSelectChange }"
          :columns="state.columns"
          :data-source="state.dataSource"
          :pagination="false"
          :loading="state.loading"
          row-key="id"
        >
          <template #bodyCell="{ column, record }">
            <template v-if="column.key === 'account_number'">
              <div v-if="record.editable">
                <a-input
                  placeholder="Ingrese la cuenta"
                  :default-value="record.account_number"
                  @change.prevent="(e: any) => onSetAccountNumber(e, record)"
                />
              </div>
              <div v-else>
                {{ record.account_number }}
              </div>
            </template>
            <template v-if="column.key === 'actions'">
              <div class="actions">
                <div>
                  <a-button type="link" @click.prevent="handleDelete(record)">
                    <font-awesome-icon :icon="['far', 'square-minus']" class="icon-minus" />
                  </a-button>
                </div>
                <div>
                  <a-button type="link" @click.prevent="handleEditable(record)">
                    <font-awesome-icon :icon="['far', 'square-plus']" class="icon-plus" />
                  </a-button>
                </div>
              </div>
            </template>
          </template>
        </a-table>

        <div class="form-buttons">
          <a-button class="btn-secondary ant-btn-md" size="large" @click.prevent="handleCancel">
            Cancelar
          </a-button>
          <a-button
            type="primary"
            class="ant-btn-md"
            size="large"
            @click.prevent="handleOk"
            :loading="state.isLoadingButton"
          >
            Guardar cambios
          </a-button>
        </div>
      </div>
    </a-spin>
  </div>
</template>

<script lang="ts">
  import { defineComponent } from 'vue';
  import { useSupplierFormAccountingAccounts } from '@/modules/negotiations/supplier/register/composables/useSupplierFormAccountingAccounts';

  export default defineComponent({
    name: 'SupplierFormAccountingAccountsComponent',
    setup() {
      const {
        state,
        handleEditable,
        handleDelete,
        hasSelected,
        onSelectChange,
        onSetAccountNumber,
        handleOk,
        handleCancel,
        onDeleteBatch,
      } = useSupplierFormAccountingAccounts();

      return {
        state,
        handleEditable,
        handleDelete,
        hasSelected,
        onSelectChange,
        onSetAccountNumber,
        handleOk,
        handleCancel,
        onDeleteBatch,
      };
    },
  });
</script>

<style lang="scss">
  .form-accounting-accounts-component {
    margin: 16px;

    .ant-table {
      margin-bottom: 2rem;
    }

    .ant-table-thead tr th {
      &:before {
        background-color: transparent !important;
        border-color: transparent !important;
      }
    }

    tr {
      border-radius: 0 !important;

      th {
        border-radius: 0 !important;
      }
    }
  }

  .selected-items {
    display: flex;
    justify-content: space-between;
    text-align: justify;
    align-items: center;
    background: #ebf5ff;
    height: 48px;
    margin-bottom: 10px;
    padding: 15px;
  }

  .delete-items {
    color: #1284ed;
    cursor: pointer;
  }

  .actions {
    display: flex;
  }

  .icon-minus {
    height: 20px;
    color: #2f353a;
  }

  .icon-plus {
    height: 20px;
    color: #2f353a;
  }
</style>
