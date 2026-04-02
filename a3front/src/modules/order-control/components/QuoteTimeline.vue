<template>
  <div class="quote-timeline">
    <!-- Header -->
    <div class="quote-timeline__summary">
      <div class="quote-timeline__badge">
        <div class="badge-icon">
          <div class="badge-box"></div>
          <div class="badge-dot"></div>
        </div>
        <div class="badge-text">{{ totalQuotes }} cotizaciones</div>
      </div>
    </div>

    <!-- Días con cotizaciones -->
    <div v-for="(group, index) in groupedQuotes" :key="index" class="quote-timeline__day-group">
      <div class="quote-timeline__date">{{ group.date }}</div>
      <div class="quote-timeline__items">
        <div v-for="quote in group.quotes" :key="quote.id" class="quote-timeline__item">
          <div class="quote-id">#{{ quote.id }}</div>
          <div class="quote-name">
            <template v-if="quote.name">{{ quote.name }}</template>
          </div>

          <div v-if="quote.isLatest" class="quote-label label-latest">Última cotización</div>
          <div v-if="quote.file" class="quote-label label-file">File: {{ quote.file }}</div>

          <div class="quote-timeline__right">
            <div class="status-dot"></div>
            <div class="quote-user">
              <template v-if="quote.executive">{{ quote.executive }}</template>
            </div>
            <div class="quote-time">{{ quote.time }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
  defineProps<{
    totalQuotes: number;
    groupedQuotes: Array<{
      date: string;
      quotes: Array<{
        id: string | number;
        name?: string;
        executive?: string;
        isLatest?: boolean;
        file?: string;
        type: string;
        time: string;
      }>;
    }>;
  }>();
</script>

<style scoped lang="scss">
  .quote-timeline {
    width: 100%;
    padding: 8px 12px;
    background: #fafafa;
    border-radius: 0 0 6px 6px;
    display: flex;
    flex-direction: column;
    gap: 16px;

    &__summary {
      display: flex;
      align-items: center;
      gap: 18px;
    }

    &__badge {
      background: #f0f0f0;
      padding: 5px 10px;
      border-radius: 6px;
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .badge-icon {
      position: relative;
      width: 12px;
      height: 12px;

      .badge-box {
        width: 8px;
        height: 10px;
        position: absolute;
        left: 2px;
        top: 1px;
        outline: 1px solid #575757;
        outline-offset: -0.5px;
      }

      .badge-dot {
        width: 3px;
        height: 3px;
        position: absolute;
        left: 7px;
        top: 1px;
        outline: 1px solid #575757;
        outline-offset: -0.5px;
      }
    }

    .badge-text {
      font-size: 10px;
      font-weight: 700;
      font-family: Montserrat, sans-serif;
      color: #575757;
      line-height: 17px;
      letter-spacing: 0.15px;
    }

    &__day-group {
      display: flex;
      flex-direction: column;
      gap: 12px;
    }

    &__date {
      font-size: 14px;
      font-weight: 500;
      color: #737373;
      font-family: Montserrat;
      width: 80px;
    }

    &__items {
      flex: 1;
      display: flex;
      flex-direction: column;
      gap: 0;
    }

    &__item {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 15px 20px;
      border-bottom: 1px solid #e9e9e9;

      &:first-child {
        border-top-left-radius: 6px;
        border-top-right-radius: 6px;
      }

      .quote-id,
      .quote-name {
        font-size: 14px;
        font-family: Montserrat;
        line-height: 21px;
        letter-spacing: 0.21px;
        color: #0d0d0d;
      }

      .quote-id {
        font-weight: 600;
      }

      .quote-name {
        font-weight: 400;
      }

      .quote-label {
        padding: 1px 6px;
        font-size: 12px;
        font-family: Montserrat;
        border-radius: 6px;
        line-height: 19px;
        display: flex;
        align-items: center;
      }

      .label-latest {
        color: #55a3ff;
        border: 1px solid #bad8fc;
        background: #fafafa;
      }

      .label-file {
        color: #28a745;
        border: 1px solid #1ed790;
        background: #fafafa;
      }

      .quote-timeline__right {
        margin-left: auto;
        display: flex;
        align-items: center;
        gap: 8px;

        .status-dot {
          width: 12px;
          height: 12px;
          background: #c4c4c4;
          border-radius: 50%;
        }

        .quote-user,
        .quote-time {
          font-size: 12px;
          font-family: Montserrat;
          font-weight: 500;
          color: #737373;
        }

        .quote-time {
          color: #bbbdbf;
          width: 32px;
          text-align: right;
        }
      }
    }
  }
</style>
