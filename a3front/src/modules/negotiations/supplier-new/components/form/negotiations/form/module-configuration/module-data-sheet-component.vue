<template>
  <div class="data-sheet-component">
    <TechnicalSheetComponent />
  </div>
</template>

<script setup lang="ts">
  import TechnicalSheetComponent from '@/modules/negotiations/supplier/register/configuration-module/components/technical-sheets/TechnicalSheetComponent.vue';

  defineOptions({
    name: 'ModuleDataSheetComponent',
  });
</script>

<style lang="scss">
  .data-sheet-component {
    border-top: 1px solid #babcbd;
    padding-top: 1rem;

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

    .table-negotiation-services {
      div:nth-child(2) {
        display: flex;
        justify-content: end !important;
        align-items: center;

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
  }
</style>
