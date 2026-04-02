<template>
  <a-spin :spinning="loading" class="spinner">
    <template v-if="!loading">
      <a-tabs v-model:activeKey="activeKey" class="info-tabs">
        <a-tab-pane
          :tab="renderTabTitle('Información general', generalNotesCount, 'general')"
          key="general"
        >
          <template v-if="dataStore.notes.forFile.length > 0">
            <div class="info-header">
              <span class="code">C1A3 - USA</span>
              <span class="person">
                <font-awesome-icon :icon="['fas', 'user']" class="icon" />
                <strong>KAM:</strong> Nombre de la KAM
              </span>
              <span class="person">
                <font-awesome-icon :icon="['fas', 'user']" class="icon" />
                <strong>Ejecutiva:</strong> {{ executive_code }}
              </span>
            </div>

            <NoteItem
              v-for="(note, index) in dataStore.notes.forFile"
              :key="index"
              :title="note.classification_name"
              :date="note.date!"
              :content="note.description"
              :isNew="true"
            />
          </template>

          <template v-else>
            <div class="no-notes">No se encontraron notas.</div>
          </template>
        </a-tab-pane>

        <a-tab-pane
          :tab="renderTabTitle('Información del servicio', serviceNotesCount, 'service')"
          key="service"
        >
          <template v-if="dataStore.notes.forService.length > 0">
            <div class="info-header">
              <span class="code">C1A3 - USA</span>
              <span class="person">
                <font-awesome-icon :icon="['fas', 'user']" class="icon" />
                <strong>KAM:</strong> Nombre de la KAM
              </span>
              <span class="person">
                <font-awesome-icon :icon="['fas', 'user']" class="icon" />
                <strong>Ejecutiva:</strong> {{ executive_code }}
              </span>
            </div>

            <NoteItem
              v-for="(note, index) in dataStore.notes.forService"
              :key="index"
              :title="note.classification_name"
              :date="note.date"
              :content="note.description"
              :isNew="true"
            />
          </template>

          <template v-else>
            <div class="no-notes">No se encontraron notas.</div>
          </template>
        </a-tab-pane>
      </a-tabs>
    </template>
  </a-spin>
</template>

<script setup lang="ts">
  import { computed, ref, h, watch } from 'vue';
  import { Badge } from 'ant-design-vue';
  import NoteItem from './NoteItem.vue';
  import { useDataStore } from '../../store/data.store';
  import { useBooleans } from '@/composables/useBooleans';

  const { useBoolean } = useBooleans();

  const isExpanded = useBoolean('isExpanded');

  const dataStore = useDataStore();

  interface Props {
    data: any;
  }

  const props = defineProps<Props>();
  const data = props.data;

  const equivalence_id = computed(() => data.equivalence_id);

  // Usar `computed` dinámico según isExpanded
  const file = computed(() => {
    return isExpanded.value ? data.file?.file_number : data.files?.[0]?.file?.file_number;
  });

  const executive_code = computed(() => {
    return isExpanded.value ? data.file?.executive_code : data.files?.[0]?.file?.executive_code;
  });

  const loading = ref(false);
  const activeKey = ref<'general' | 'service'>('general');

  watch(
    () => file.value,
    async (newFile) => {
      if (newFile) {
        loading.value = true;
        await dataStore.getNotes(newFile, equivalence_id.value);
        loading.value = false;
      }
    },
    { immediate: true }
  );

  const generalNotesCount = computed(() => dataStore.notes.forFile?.length || 0);
  const serviceNotesCount = computed(() => dataStore.notes.forService?.length || 0);

  const renderTabTitle = (label: string, count: number, tabKey: string) => {
    return h('span', [
      label,
      h(Badge, {
        count: `${count.toString().padStart(2, '0')} nota(s)`,
        color: activeKey.value === tabKey ? '#1677ff' : '#bfbfbf',
        style: {
          marginLeft: '8px',
          fontWeight: 'normal',
        },
      }),
    ]);
  };
</script>

<style scoped>
  .info-header {
    display: flex;
    align-items: center;
    gap: 24px;
    margin: 16px 0;
    flex-wrap: wrap;
  }

  .code {
    color: #7e8285;
    font-size: 14px;
    font-weight: 600;
  }

  .person {
    color: #2f353a;
    font-size: 14px;
  }

  .person strong {
    font-weight: 600;
    margin-right: 4px;
  }

  .icon {
    margin-right: 4px;
  }

  .spinner {
    min-height: 200px;
    display: flex;
    justify-content: center;
    align-items: center;
  }

  .no-notes {
    text-align: center;
    color: #999;
    padding: 32px 0;
    font-style: italic;
  }

  ::v-deep(.ant-tabs-nav-list) {
    width: 100%;
    display: flex;
  }

  ::v-deep(.ant-tabs-tab) {
    flex: 1;
    justify-content: center;
    text-align: center;
  }

  ::v-deep(.ant-tabs-nav) {
    overflow: visible !important;
  }

  ::v-deep(.ant-tabs-nav-operations) {
    display: none !important;
  }
</style>
