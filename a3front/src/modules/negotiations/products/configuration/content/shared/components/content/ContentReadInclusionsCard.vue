<template>
  <div class="content-read-card">
    <ContentCardHeader :title="title" />
    <div class="content-read-list">
      <div v-for="(item, idx) in items" :key="idx" class="content-read-list-row">
        <span class="content-read-list-name">{{ item.label }}</span>
        <div class="content-read-list-meta">
          <span class="content-read-visibility">
            <EyeInvisibleOutlined v-if="!item.visibleCliente" class="content-read-meta-icon" />
            <EyeOutlined v-else class="content-read-meta-icon" />
            {{ item.visibleCliente ? 'Visible' : 'Oculto' }}
          </span>
          <span
            class="content-read-pill"
            :class="
              item.incluye ? 'content-read-pill--included' : 'content-read-pill--not-included'
            "
          >
            <span class="content-read-pill-dot" />
            {{ item.incluye ? 'Incluido' : 'No incluido' }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
  import { EyeOutlined, EyeInvisibleOutlined } from '@ant-design/icons-vue';
  import ContentCardHeader from './ContentCardHeader.vue';

  export interface ContentReadInclusionItem {
    label: string;
    visibleCliente: boolean;
    incluye: boolean;
  }

  interface Props {
    title?: string;
    items: ContentReadInclusionItem[];
  }

  withDefaults(defineProps<Props>(), {
    title: 'Inclusiones',
  });
</script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .content-read-card {
    background-color: $color-white;
    border-radius: 8px;
    border: 0.5px solid #e7e7e7;
    padding: 16px;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
    display: flex;
    flex-direction: column;
  }

  .content-read-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
    padding: 0 20px;
    flex: 1;
  }

  .content-read-list-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
    padding: 4px 0;
    min-height: 44px;
  }

  .content-read-list-name {
    font-size: 14px;
    color: #7e8285;
    font-weight: 600;
    min-height: 22px;
    line-height: 22px;
    display: inline-flex;
    align-items: center;
  }

  .content-read-list-meta {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-shrink: 0;
  }

  .content-read-visibility {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    color: #babcbd;
  }

  .content-read-meta-icon {
    font-size: 15px;
    color: #babcbd;
    line-height: 1;
    display: inline-flex;
    align-items: center;
  }

  .content-read-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 10px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 500;

    &--included {
      background-color: #dfffe9;
      color: #00a15b;
    }

    &--not-included {
      background-color: #f9f9f9;
      color: #575b5f;
    }
  }

  .content-read-pill-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background-color: currentColor;
  }
</style>
