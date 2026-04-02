<template>
  <div class="contacts-component">
    <div>
      <div class="form-label">Contactos</div>
      <div class="form-container">
        <div>
          <a-input
            allow-clear
            @change="handleSearch"
            v-model="searchQuery"
            class="input-field"
            placeholder="Buscar contacto"
          >
            <<template #suffix> <IconSearch /> </template>
          </a-input>
        </div>
        <div class="action-buttons">
          <div @click="handleShowFormContact(true)">
            <font-awesome-icon icon="xmark" /> <span>Crear contacto</span>
          </div>
        </div>
      </div>
    </div>
    <div class="container-table">
      <div class="title-table">Listado de contactos</div>
      <div>
        <a-table
          :bordered="true"
          :columns="columns"
          :row-key="(record: any) => record.id"
          :data-source="sourceData"
          :pagination="false"
          :showSorterTooltip="false"
          :loading="loading"
          :scroll="{ x: true }"
        >
          <template #bodyCell="{ column, text, record }">
            <template v-if="column.dataIndex === 'firstname'">
              {{ record.firstname + ' ' + record.surname }}
            </template>
            <template v-if="column.dataIndex === 'department'">
              {{ record.department.name }}
            </template>
            <template v-if="column.dataIndex === 'typeContact'">
              {{ record.typeContact.name }}
            </template>
            <template v-if="column.dataIndex === 'supplierBranchOffice'">
              {{ record.supplierBranchOffice.location }}
            </template>
            <template v-if="column.dataIndex === 'actions'">
              <a-dropdown :trigger="['click']">
                <template #overlay>
                  <a-menu>
                    <a-menu-item key="1" @click="handleEditContact(record)"> Editar </a-menu-item>
                    <a-menu-item key="2" @click="handleDeleteContact(record.id)">
                      Eliminar
                    </a-menu-item>
                  </a-menu>
                </template>
                <a class="ant-dropdown-link" @click.prevent>
                  <font-awesome-icon class="actions" :icon="['fas', 'ellipsis']" />
                </a>
              </a-dropdown>
            </template>
          </template>
        </a-table>
        <div class="container-footer">
          <div>
            <a-pagination
              :showSizeChanger="false"
              :showLessItems="true"
              :hideOnSinglePage="true"
              v-model:current="currentPage"
              v-model:pageSize="pageSize"
              :total="total"
              :show-quick-jumper="false"
              @change="handleChangePagination"
            />
          </div>
        </div>
      </div>
    </div>

    <ModuleContactFormComponent />
  </div>
</template>

<script setup lang="ts">
  import IconSearch from '@/modules/negotiations/supplier-new/icons/icon-search.vue';
  import { useContactsComposable } from '@/modules/negotiations/supplier-new/composables/form/negotiations/module-configuration/contacts.composable';
  import ModuleContactFormComponent from '@/modules/negotiations/supplier-new/components/form/negotiations/form/module-configuration/module-contact-form-component.vue';

  defineOptions({
    name: 'ModuleContactsComponent',
  });

  const {
    columns,
    currentPage,
    pageSize,
    total,
    sourceData,
    loading,
    searchQuery,
    handleDeleteContact,
    handleChangePagination,
    handleSearch,
    handleShowFormContact,
    handleEditContact,
  } = useContactsComposable();
</script>

<style lang="scss">
  .contacts-component {
    border-top: 1px solid #babcbd;
    padding-top: 1rem;

    .form-label {
      font-weight: 600;
      font-size: 18px;
      line-height: 24px;
      letter-spacing: -1.5%;
      margin-bottom: 1rem;
    }

    .form-container {
      display: flex;
      justify-content: space-between;
      align-items: center;

      .ant-input {
        height: 48px;
      }
    }

    .input-field {
      width: 308px;
    }

    .actions {
      height: 24px;
      width: 24px;
      color: #101828;
      cursor: pointer;
    }

    .action-buttons {
      display: flex;
      gap: 1rem;
      color: #1284ed;
      cursor: pointer;

      span {
        font-weight: 500;
        font-size: 16px;
        line-height: 32px;
        text-decoration: underline;
        text-decoration-style: solid;
      }
    }

    .title-table {
      font-weight: 500;
      font-size: 16px;
      line-height: 24px;
      vertical-align: middle;
      color: #575b5f;
      margin-bottom: 1rem;
    }

    .container-table {
      margin-top: 1rem;
    }

    thead {
      th {
        background: #e6ebf2 !important;
        height: 56px !important;

        .ant-table-column-title {
          font-weight: 500;
          font-size: 16px;
          line-height: 24px;
          vertical-align: middle;
          color: #575b5f;
        }

        .ant-table-column-sorter-inner {
          top: 10.79px;
          left: 17.33px;
          border-width: 1.2px;
          color: #575757;
        }
      }
    }

    .tag-state-active {
      height: 32px;
      border-radius: 4px;
      padding: 4px 8px;
      background: #dfffe9;
      border-color: #dfffe9;
      color: #288a5f;
      font-weight: 600;
      font-size: 16px;
      line-height: 24px;
      letter-spacing: 0;
      vertical-align: middle;
    }

    .tag-state-inactive {
      height: 32px;
      border-radius: 4px;
      padding: 4px 8px;
      background: #fff1f0;
      border-color: #fff1f0;
      color: #dd394a;
      font-weight: 600;
      font-size: 16px;
      line-height: 24px;
      letter-spacing: 0;
      vertical-align: middle;
    }

    .container-footer {
      margin-top: 1.5rem;
      display: flex;
      justify-content: end !important;
      align-items: center;

      .a-btn-cancel {
        width: 118px;
        height: 48px;
        border-width: 1px;
        border-radius: 5px;
        padding: 12px 24px;
        color: #2f353a;
        border-color: #2f353a !important;
        font-weight: 600;
      }

      .a-btn-save {
        width: 159px;
        height: 48px;
        border-radius: 5px;
        padding: 12px 24px;
        color: #ffffff;
        background: #acaeb0;
        border: 1px solid #acaeb0;
        box-shadow: 0 2px 0 #acaeb0;
        font-weight: 600;
      }

      .group-buttons {
        display: flex;
        gap: 1rem;
      }

      .ant-pagination li:nth-child(3) {
        border-left-color: #bdbdbd !important;
        border-right-color: #bdbdbd !important;
      }

      .ant-pagination li:nth-last-child(3) {
        border-right-color: #bdbdbd !important;
      }

      .ant-pagination-prev {
        margin-inline-end: 0;
        border-radius: 4px 0 0 4px;
        border: 1px solid #bdbdbd;

        &:hover {
          border-radius: 0 !important;
          border-right-color: #bdbdbd;
        }

        &:active {
          border-radius: 0 !important;
          border-right-color: #bdbdbd;
        }
      }

      .ant-pagination-next {
        border: 1px solid #bdbdbd;
        margin-inline-end: 0;
        border-radius: 0 4px 4px 0;

        &:hover {
          border-radius: 0 !important;
          border-right-color: #bdbdbd;
        }

        &:active {
          border-radius: 0 !important;
          border-right-color: #bdbdbd;
        }
      }

      .ant-pagination-item {
        border-top-color: #bdbdbd !important;
        border-bottom-color: #bdbdbd !important;
        margin-inline-end: 0;
        border-radius: 0 !important;
      }

      .ant-pagination-item-after-jump-prev {
        border-right-color: #bdbdbd !important;
      }

      .ant-pagination-jump-next {
        border: 1px solid #bdbdbd !important;
        margin-inline-end: 0;
        border-radius: 0 !important;

        &:active {
          background: transparent !important;
        }

        &:hover {
          background: transparent !important;
        }
      }

      .ant-pagination-jump-prev {
        border: 1px solid #bdbdbd !important;
        margin-inline-end: 0;
        border-radius: 0 !important;
      }

      .ant-pagination-item-link-icon {
        color: #bdbdbd !important;

        &:hover {
          color: #939496;
        }
      }

      .ant-pagination-item-active {
        background: #939496;
        border-color: #bdbdbd;
        color: #ffffff;

        a {
          color: #ffffff;
        }
      }
    }
  }
</style>
