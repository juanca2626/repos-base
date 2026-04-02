<template>
  <a-form-item label="Files disponibles:" v-if="fileOptions.length" required>
    <a-select
      v-model:value="selected"
      mode="multiple"
      label-in-value
      placeholder="Seleccionar file(s)"
      style="width: 100%"
      @change="onChange"
    >
      <template #dropdownRender>
        <div class="custom-dropdown">
          <a-checkbox
            v-for="option in fileOptions"
            :key="option.value._id"
            :checked="selected.some((item) => item.value._id === option.value._id)"
            @change="(e: Event) => toggleSelection(option, (e.target as HTMLInputElement).checked)"
          >
            {{ option.label }}
          </a-checkbox>
        </div>
      </template>
    </a-select>
  </a-form-item>
</template>

<script setup lang="ts">
  import { ref, computed, onMounted } from 'vue';
  import { useFileStore } from '../store/file.store';
  import { useReportStore } from '@/modules/operations/modules/providers/store/report.store';
  import type { File } from '../interfaces/file';

  type FileOption = {
    label: string;
    value: File;
  };

  const emit = defineEmits<(e: 'update:selected', value: File[]) => void>();

  const selected = ref<FileOption[]>([]);
  const fileStore = useFileStore();
  const reportStore = useReportStore();

  const fileOptions = computed<FileOption[]>(() =>
    fileStore.files.map((file) => ({
      label: `#${file.file_number}`,
      value: file,
    }))
  );

  // ✅ Preselección automática desde el store
  onMounted(() => {
    if (reportStore.files.length) {
      selected.value = fileOptions.value.filter((option) =>
        reportStore.files.some((f: File) => f._id === option.value._id)
      );
      emitSelected();
    }
  });

  // ✅ Manejo de checkbox individual
  const toggleSelection = (option: FileOption, isChecked: boolean): void => {
    selected.value = isChecked
      ? [...selected.value, option]
      : selected.value.filter((item) => item.value._id !== option.value._id);

    emitSelected();
  };

  // ✅ Manejo de cambio desde el <a-select>
  const onChange = (newValues: FileOption[]): void => {
    selected.value = newValues;
    emitSelected();
  };

  // ✅ Emitir al padre + actualizar el store
  const emitSelected = (): void => {
    const selectedFiles = selected.value.map((item) => item.value);
    emit('update:selected', selectedFiles);
    reportStore.setReportData({ files: selectedFiles });
  };
</script>

<style scoped>
  .custom-dropdown {
    display: flex;
    flex-direction: column;
    padding: 8px;
  }
</style>
