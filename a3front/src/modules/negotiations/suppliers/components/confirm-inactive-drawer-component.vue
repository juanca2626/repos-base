<template>
  <a-drawer
    :open="open"
    :width="424"
    :maskClosable="false"
    :keyboard="false"
    @close="handleClose"
    class="custom-filter-form-drawer"
  >
    <template #title>
      <div>
        <span class="custom-title"> Confirmar inactivación </span>
      </div>
    </template>

    <p class="text-info">¿Confirmar que desea desactivar al proveedor {{ businessName }}?</p>

    <template #footer>
      <a-row :gutter="12">
        <a-col :span="12">
          <a-button class="btn-cancel w-100" :disabled="isLoading" @click="handleClose">
            Cancelar
          </a-button>
        </a-col>
        <a-col :span="12">
          <a-button
            class="btn-apply-filters w-100"
            :loading="isLoading"
            :disabled="isLoading"
            @click="handleConfirm"
          >
            Aplicar cambios
          </a-button>
        </a-col>
      </a-row>
    </template>
  </a-drawer>
</template>

<script setup lang="ts">
  import { ref, watch } from 'vue';

  const props = defineProps<{
    open: boolean;
    businessName: string | null;
  }>();

  const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'confirm'): void;
  }>();

  const isLoading = ref(false);

  const handleConfirm = () => {
    isLoading.value = true;
    emit('confirm');
  };

  const handleClose = () => {
    isLoading.value = false; // 🔄 reset manual cuando se cierre por el botón de X
    emit('close');
  };

  // ✅ Observa cambios de `open` para resetear loading cuando el padre cierre el drawer
  watch(
    () => props.open,
    (val) => {
      if (!val) {
        isLoading.value = false;
      }
    }
  );
</script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';
  @import '@/scss/components/negotiations/_supplierList.scss';

  .custom-title {
    font-size: 24px;
    font-weight: 600;
  }

  .btn-apply-filters {
    background-color: $color-black;
    color: $color-white;
    font-size: 16px;
    font-weight: 600;
    height: 48px;

    &:hover {
      background: $color-black-2;
      border: 1px solid $color-black;
      color: $color-white;
    }

    &:disabled {
      opacity: 0.7;
      cursor: not-allowed;
    }
  }

  .btn-cancel {
    background-color: #ffffff;
    color: #000000;
    font-size: 16px;
    font-weight: 600;
    height: 48px;
    border: 1px solid #d9d9d9;

    &:hover {
      background: #f5f5f5;
      border: 1px solid #d9d9d9;
      color: #000000;
    }

    &:disabled {
      opacity: 0.7;
      cursor: not-allowed;
    }
  }

  .text-info {
    margin-top: 10px;
    line-height: 24px;
    font-size: 16px;
  }
</style>
