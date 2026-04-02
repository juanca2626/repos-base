<template>
  <div class="content-read-card content-read-card-by-day">
    <ContentCardHeader :title="title" :status="status" />
    <div class="content-read-by-day-timeline">
      <div v-for="day in days" :key="day.dayNumber" class="content-read-by-day-item">
        <div class="content-read-by-day-item-header">
          <IconCalendarCheck :height="20" :width="20" class="content-read-by-day-icon" />
          <span class="content-read-by-day-label">Día {{ day.dayNumber }}</span>
        </div>
        <div v-if="day.html" class="content-read-by-day-content">{{ stripHtml(day.html) }}</div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
  import ContentCardHeader from './ContentCardHeader.vue';
  import IconCalendarCheck from '@/modules/negotiations/products/configuration/icons/IconCalendarCheck.vue';

  export interface TextTypeDayItem {
    dayNumber: number;
    html: string;
  }

  interface Props {
    title: string;
    status?: string;
    days: TextTypeDayItem[];
  }

  withDefaults(defineProps<Props>(), {
    status: undefined,
    days: () => [],
  });

  const stripHtml = (html: string): string => {
    const div = document.createElement('div');
    div.innerHTML = html;
    return (div.textContent ?? '').trim();
  };
</script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .content-read-card {
    background-color: $color-white;
    border-radius: 8px;
    border: 0.5px solid #e7e7e7;
    padding: 16px;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
  }

  .content-read-by-day-timeline {
    position: relative;
    padding-left: 24px;
    border-left: 3px solid #fff2dd;
    margin-left: 8px;
  }

  .content-read-by-day-item {
    display: flex;
    gap: 10px;
    align-items: flex-start;

    margin-bottom: 20px;

    &:last-child {
      margin-bottom: 0;
    }
  }

  .content-read-by-day-item-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
    flex-shrink: 0;
    white-space: nowrap;
  }

  .content-read-by-day-icon {
    color: #2f353a;
    flex-shrink: 0;
  }

  .content-read-by-day-label {
    font-size: 16px;
    font-weight: 600;
    color: #2f353a;
    flex-shrink: 0;
  }

  .content-read-by-day-content {
    font-size: 14px;
    color: $color-black-2;
    line-height: 1.5;
  }
</style>
