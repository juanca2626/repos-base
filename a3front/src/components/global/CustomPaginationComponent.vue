<template>
  <a-row class="mt-4" align="middle" justify="center">
    <a-pagination
      class="custom-pagination"
      v-model:current="currentPage"
      v-model:pageSize="pageSize"
      :disabled="isDisabled"
      :total="total"
      :show-quick-jumper="false"
      @change="handleChange"
      :show-size-changer="showSizeChanger"
      :showLessItems="true"
    >
      <template #itemRender="{ type, originalElement }">
        <a v-if="type === 'prev'">
          <ArrowLeftOutlined />
        </a>
        <a v-else-if="type === 'next'">
          <ArrowRightOutlined />
        </a>
        <component :is="originalElement" v-else></component>
      </template>
    </a-pagination>
  </a-row>
</template>

<script setup lang="ts">
  import { computed, ref, watch } from 'vue';
  import { ArrowLeftOutlined, ArrowRightOutlined } from '@ant-design/icons-vue';

  const props = defineProps<{
    current: number;
    pageSize: number;
    total: number;
    disabled?: boolean;
    showSizeChanger?: boolean;
  }>();

  const emit = defineEmits(['update:current', 'update:pageSize', 'change']);

  const currentPage = ref(props.current);
  const pageSize = ref(props.pageSize);
  const showSizeChanger = ref(props.showSizeChanger ?? true);

  const isDisabled = computed(() => props.disabled ?? false);

  watch(
    () => props.current,
    (newValue) => {
      currentPage.value = newValue;
    }
  );

  watch(
    () => props.pageSize,
    (newValue) => {
      pageSize.value = newValue;
    }
  );

  const handleChange = (page: number, size: number) => {
    emit('update:current', page);
    emit('update:pageSize', size);
    emit('change', page, size);
  };
</script>

<style scoped>
  .custom-pagination :deep(.ant-pagination-item) {
    min-width: 40px;
    height: 40px;
    line-height: 40px;
    border-radius: 0;
    margin: 0 0;
    border: 1px solid #bdbdbd;
    background-color: #ffffff;
  }

  .custom-pagination :deep(.ant-pagination-item-active) {
    background-color: #bd0d12;
    border-color: #bd0d12;
    min-width: 40px;
    height: 40px;
    line-height: 40px;
  }

  .custom-pagination :deep(.ant-pagination-item-active a) {
    color: white !important;
  }

  .custom-pagination :deep(.ant-pagination-item:hover) {
    border-color: #939496;
  }

  .custom-pagination :deep(.ant-pagination-item:hover a) {
    color: #939496;
  }

  .custom-pagination :deep(.ant-pagination-prev) {
    min-width: 40px;
    height: 40px;
    line-height: 40px;
    margin: 0 0;
    border: 1px solid #bdbdbd;
    background-color: #ffffff;
    border-radius: 5px 0 0 5px;
  }

  .custom-pagination :deep(.ant-pagination-disabled) {
    span {
      color: #2f353a !important;
    }
  }

  .custom-pagination :deep(.ant-pagination-next) {
    min-width: 40px;
    height: 40px;
    line-height: 40px;
    margin: 0 0;
    border: 1px solid #bdbdbd;
    background-color: #ffffff;
    border-radius: 0 5px 5px 0;
    span {
      color: #2f353a !important;
    }
  }

  .custom-pagination :deep(.ant-pagination-prev .anticon),
  .custom-pagination :deep(.ant-pagination-next .anticon) {
    color: #000000;
  }

  .custom-pagination :deep(.ant-pagination-prev .anticon) {
    color: #000000;
    border-radius: 0 5px 5px 0;
  }

  .custom-pagination :deep(.ant-pagination-jump-prev),
  .custom-pagination :deep(.ant-pagination-jump-next) {
    border-radius: 4px;
    min-width: 40px;
    height: 40px;
    line-height: 40px;
    margin-inline-end: 0;

    span {
      border: 1px solid #bdbdbd;
    }
  }
</style>
