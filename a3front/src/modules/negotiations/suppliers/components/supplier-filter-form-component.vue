<template>
  <a-drawer
    :open="showDrawerForm"
    :width="424"
    :maskClosable="false"
    :keyboard="false"
    @close="handleClose"
    class="custom-filter-form-drawer"
  >
    <template #title>
      <div>
        <span class="custom-title"> Filtros </span>
      </div>
    </template>

    <div>
      <slot name="body" />
    </div>

    <template #footer>
      <a-row :gutter="10">
        <a-col :span="12">
          <a-button type="link" class="btn-clean-filters w-100" @click="emit('cleanFilters')">
            Limpiar filtros
          </a-button>
        </a-col>
        <a-col :span="12">
          <a-button class="btn-apply-filters w-100" @click="emit('applyFilters')">
            Aplicar filtros
          </a-button>
        </a-col>
      </a-row>
    </template>
  </a-drawer>
</template>
<script setup lang="ts">
  defineProps({
    showDrawerForm: {
      type: Boolean,
      required: true,
    },
  });

  const emit = defineEmits(['closeDrawerForm', 'cleanFilters', 'applyFilters']);

  const handleClose = () => {
    emit('closeDrawerForm', false);
  };
</script>

<style lang="scss">
  @import '@/scss/components/negotiations/_supplierList.scss';
</style>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .custom-title {
    font-size: 24px;
    font-weight: 600;
  }

  .btn-clean-filters {
    color: $color-black;
    font-size: 16px;
    font-weight: 600;
    height: 48px;

    &:hover {
      color: $color-black-2;
    }
  }

  .btn-apply-filters {
    background-color: $color-black;
    color: $color-white;
    font-size: 16px;
    font-weight: 600;
    height: 48px;
    width: 152px;

    &:hover {
      background: $color-black-2;
      border: 1px solid $color-black;
      color: $color-white;
    }
  }
</style>
