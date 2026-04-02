<template>
  <a-collapse
    collapsible="header"
    expandIconPosition="right"
    :expandIcon="expandIcon"
    class="note-collapse"
    :bordered="false"
  >
    <a-collapse-panel>
      <template #header>
        <div class="collapse-header">
          {{ title }}
          <span class="update-date">Creado: {{ formatDate }} </span>
          <!-- <a-tag class="tagStyle"> Nuevo </a-tag> -->
        </div>
      </template>

      <!-- ✅ Padding separado en contenedor interno -->
      <div class="collapse-inner">
        {{ content }}
      </div>
    </a-collapse-panel>
  </a-collapse>
</template>

<script setup lang="ts">
  import { computed, h } from 'vue';
  import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
  import dayjs from 'dayjs';

  interface Props {
    title: string;
    date: string;
    content: string;
  }

  const props = defineProps<Props>();

  const formatDate = computed(() => {
    return props.date && dayjs(props.date).isValid()
      ? dayjs(props.date).format('DD/MM/YYYY')
      : 'Fecha no disponible';
  });

  const expandIcon = ({ isActive }: { isActive: boolean }) =>
    h(FontAwesomeIcon, {
      icon: ['fas', isActive ? 'minus' : 'plus'],
      style: { fontSize: '14px', color: '#595959' },
    });
</script>

<style scoped lang="scss">
  .note-collapse {
    border: 1px solid #1284ed;
    border-radius: 8px;
    overflow: hidden;
    background-color: #f1f8ff;
    margin-bottom: 12px;

    ::v-deep(.ant-collapse-item) {
      border: none;

      .ant-collapse-header {
        background-color: #f1f8ff;
        color: #1284ed;
        font-weight: 600;
        font-size: 16px;
        padding: 12px 16px;
        border-radius: 8px 8px 0 0;

        .ant-collapse-expand-icon {
          position: absolute;
          right: 12px;
        }

        .ant-collapse-header-text {
          margin-left: 30px;
        }
      }

      .ant-collapse-content {
        border-top: none;
        background-color: white;
        border-radius: 0 0 8px 8px;
        /* ❌ No usar padding aquí */
      }
    }
  }

  .collapse-inner {
    padding: 10px 14px; // ✅ Padding sin interferir con la animación
  }

  .collapse-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-right: 32px;
  }

  .update-date {
    font-size: 12px;
    color: #bdbdbd;
    margin-left: 20px;
    margin-right: 10px;
    padding-left: 20px;
    background-repeat: no-repeat;
    background-position-x: 0px;
    background-image: url('data:image/svg+xml;charset=UTF-8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="%23bdbdbd" d="M152 24c0-13.3-10.7-24-24-24s-24 10.7-24 24l0 40L64 64C28.7 64 0 92.7 0 128l0 16 0 48L0 448c0 35.3 28.7 64 64 64l320 0c35.3 0 64-28.7 64-64l0-256 0-48 0-16c0-35.3-28.7-64-64-64l-40 0 0-40c0-13.3-10.7-24-24-24s-24 10.7-24 24l0 40L152 64l0-40zM48 192l352 0 0 256c0 8.8-7.2 16-16 16L64 464c-8.8 0-16-7.2-16-16l0-256z"/></svg>');
  }

  .tagStyle {
    background-color: #e0edf5;
    color: #5c5ab4;
    font-weight: bold;
    border-radius: 5px;
    font-size: 14px;
    padding: 4px 8px;
  }
</style>
