<template>
  <div>
    <a-config-provider :locale="customLocale">
      <a-pagination
        class="base-pagination"
        v-model:current="currentPage"
        v-model:pageSize="pageSize"
        :disabled="isDisabled"
        :total="total"
        :show-size-changer="showSizeChanger"
        :show-quick-jumper="showQuickJumper"
        @change="handleChange"
      >
        <template #itemRender="{ type, originalElement }">
          <a v-if="type === 'prev'">
            <font-awesome-icon :icon="['fas', 'chevron-left']" />
          </a>
          <a v-else-if="type === 'next'">
            <font-awesome-icon :icon="['fas', 'chevron-right']" />
          </a>
          <component :is="originalElement" v-else></component>
        </template>
      </a-pagination>
    </a-config-provider>
  </div>
</template>

<script lang="ts" setup>
  import { computed, ref, watch } from 'vue';
  import esES from 'ant-design-vue/es/locale/es_ES';

  const props = defineProps<{
    current: number;
    pageSize: number;
    total: number;
    disabled?: boolean;
    showSizeChanger?: boolean;
    showQuickJumper?: boolean;
  }>();

  const emit = defineEmits(['update:current', 'update:pageSize', 'change']);

  const currentPage = ref(props.current);
  const pageSize = ref(props.pageSize);

  const isDisabled = computed(() => props.disabled ?? false);

  const customLocale = {
    ...esES,
    Pagination: {
      ...esES.Pagination,
      jump_to: 'Ir a la página',
      items_per_page: '',
    },
  };

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

<style scoped lang="scss">
  .base-pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    justify-items: center;
    gap: 10px;
    margin-top: 40px;

    .page {
      font-weight: 400;
      font-size: 18px;
      line-height: 30px;
      text-align: center;
      letter-spacing: -0.015em;
      color: var(--files-black-4);
      width: 29px;

      cursor: pointer;
    }

    &-pages {
      display: flex;
      justify-content: center;
      align-items: center;
      justify-items: center;
      gap: 9px;
      margin: 10px;

      max-width: 240px;
      height: 33px;

      transition: 0.3s ease all;
    }

    .page:hover {
      color: var(--files-main-color);
      font-weight: 800;
    }

    .page--previous,
    .page--next {
      color: var(--files-main-color);
      width: 13px;
    }
  }
</style>
