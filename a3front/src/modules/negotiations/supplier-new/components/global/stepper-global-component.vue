<template>
  <div class="stepper-global-component">
    <a-steps
      :current="stepperCurrent"
      :items="stepperOption"
      class="custom-stepper-center"
      @change="handleChangeCollapseKey"
    />
  </div>
</template>

<script setup lang="ts">
  import { useSupplierGlobalComposable } from '@/modules/negotiations/supplier-new/composables/form/supplier-global.composable';

  defineOptions({
    name: 'StepperGlobalComponent',
  });

  const { stepperOption, stepperCurrent, handleChangeCollapseKey } = useSupplierGlobalComposable();
</script>

<style lang="scss">
  .stepper-global-component {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 1.5rem;

    .custom-stepper-center {
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .stepper-center {
      width: 80%;
    }

    .stepper-full-center {
      width: 100%;
    }

    .ant-steps-item-active {
      .ant-steps-item-description {
        color: rgba(0, 0, 0, 0.88) !important;

        &:hover {
          color: rgba(0, 0, 0, 0.88) !important;
        }
      }
    }

    .ant-steps-item-title {
      &:after {
        background-color: rgba(5, 5, 5, 0.06) !important;
      }
    }

    .ant-steps-item-title,
    .ant-steps-item-description {
      font-size: 14px;
      line-height: 24px;
      vertical-align: middle;
    }

    .ant-steps-item-description {
      font-size: 12px;
      line-height: 16px;

      &:hover {
        color: #7e8285;
      }
    }

    .ant-steps-item-process {
      .ant-steps-item-title {
        font-weight: 700;
        color: #212121;
      }

      .ant-steps-item-description {
        font-weight: 400;
        color: #7e8285;
      }
    }

    .ant-steps-item-finish,
    .ant-steps-item-wait {
      .ant-steps-item-title {
        font-weight: 400;
        text-align: center;
      }

      .ant-steps-item-title {
        color: #575b5f !important;
      }

      .ant-steps-item-description {
        font-weight: 400;
        color: #7e8285;
      }

      .ant-steps-item-description {
        color: #575b5f !important;
      }
    }

    .ant-steps-item-content {
      cursor: pointer;
    }

    .ant-steps-item-disabled {
      .ant-steps-item-content,
      .ant-steps-item-icon,
      .ant-steps-item-title,
      .ant-steps-item-description {
        cursor: not-allowed !important;
      }

      .ant-steps-item-title,
      .ant-steps-item-description {
        color: #9ca3af !important;
      }
    }
  }
</style>
