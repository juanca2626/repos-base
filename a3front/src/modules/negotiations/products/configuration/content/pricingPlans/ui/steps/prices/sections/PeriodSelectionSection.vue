<template>
  <a-spin :spinning="isLoadingRateVariations">
    <div>
      <div class="prices-section-title">
        <span class="title-main"
          >Seleccione la fecha para configurar las tarifas correspondientes</span
        >
        <span class="title-hint"> Configuradas en el paso 1 </span>
      </div>

      <div class="variation-progress">
        <div class="progress-left"></div>

        <div class="progress-right">
          <div class="legend-item completed">
            <span class="dot"></span>
            <span>{{ completedCount }} completas</span>
          </div>

          <div class="legend-item in-progress">
            <span class="dot"></span>
            <span>{{ inProgressCount }} en progreso</span>
          </div>

          <div class="legend-item not-started">
            <span class="dot"></span>
            <span>{{ notStartedCount }} pendientes</span>
          </div>
        </div>
      </div>

      <div class="schedules-wrapper">
        <button v-if="canGoPrevious" class="chevron-button chevron-left" @click="goToPreviousPage">
          <IconChevronLeft />
        </button>

        <div class="schedules-container" :style="{ '--items-per-page': ITEMS_PER_PAGE }">
          <div
            v-for="card in visibleCards"
            :key="card.id"
            :data-variation-id="card.id"
            class="schedule-card"
            :class="{ selected: card.id === selectedId }"
            @click="select(card.id)"
          >
            <div class="schedule-title-container">
              <div class="title-left">
                <span class="schedule-title">
                  {{ card.label }} <TrainIcon v-if="isTrainService" />
                  <span class="number-of-frequencies" v-if="isTrainService"
                    >{{ card.frequencies.length }} f.</span
                  >
                </span>

                <div class="schedule-time-container">
                  <CalendarOutlined class="clock-icon" />
                  <span class="schedule-time-title" :title="card.dateDisplay">
                    {{ truncateDateDisplay(card.dateDisplay) }}
                  </span>
                </div>

                <div class="status-row">
                  <span class="status-dot" :class="card.status"></span>
                  <span class="status-text">
                    {{ mapVariationStatus(card.status).label }}
                  </span>
                </div>
              </div>

              <a-radio
                class="schedule-radio"
                :checked="card.id === selectedId"
                @change="select(card.id)"
              />
            </div>
          </div>
        </div>

        <button v-if="canGoNext" class="chevron-button chevron-right" @click="goToNextPage">
          <IconChevronRight />
        </button>
      </div>
    </div>
  </a-spin>
</template>

<script setup lang="ts">
  import { ref, computed, watch, nextTick } from 'vue';
  import { CalendarOutlined } from '@ant-design/icons-vue';
  import { mapVariationStatus } from '@/modules/negotiations/products/configuration/content/pricingPlans/application/helpers/mapVariationStatus';
  import {
    IconChevronLeft,
    IconChevronRight,
    TrainIcon,
  } from '@/modules/negotiations/products/configuration/content/pricingPlans/icons';

  import type { RateVariation } from '@/modules/negotiations/products/configuration/content/pricingPlans/domain/models/RateVariation';

  interface Props {
    model: any;
    cards: RateVariation[];
    selectedId: string | null;
    onSelect: (id: string) => void;
    isTrainService: boolean;
    completedCount: number;
    inProgressCount: number;
    notStartedCount: number;
  }

  const props = defineProps<Props>();

  const ITEMS_PER_PAGE = 4;
  const MAX_LENGTH = 16;
  const currentPage = ref(0);

  const visibleCards = computed(() => {
    const start = currentPage.value * ITEMS_PER_PAGE;
    return props.cards.slice(start, start + ITEMS_PER_PAGE);
  });

  const canGoNext = computed(() => (currentPage.value + 1) * ITEMS_PER_PAGE < props.cards.length);

  const canGoPrevious = computed(() => currentPage.value > 0);

  const isLoadingRateVariations = computed(() => props.model.isLoadingRateVariations);

  const goToNextPage = () => currentPage.value++;
  const goToPreviousPage = () => currentPage.value--;

  const truncateDateDisplay = (value: string) => {
    return value.length > MAX_LENGTH ? `${value.slice(0, MAX_LENGTH)}...` : value;
  };

  const select = (id: string) => {
    props.onSelect(id);
  };

  const selectedIndex = computed(() => props.cards.findIndex((c) => c.id === props.selectedId));

  watch(
    () => props.selectedId,
    () => {
      if (selectedIndex.value === -1) return;

      const page = Math.floor(selectedIndex.value / ITEMS_PER_PAGE);

      if (currentPage.value !== page) {
        currentPage.value = page;
      }
    },
    { immediate: true }
  );

  watch(
    () => props.selectedId,
    async (id) => {
      await nextTick();

      const el = document.querySelector(`[data-variation-id="${id}"]`);
      if (el) {
        el.scrollIntoView({
          behavior: 'smooth',
          inline: 'center',
          block: 'nearest',
        });
      }
    }
  );
</script>
<style scoped lang="scss">
  @import '@/modules/negotiations/products/configuration/content/pricingPlans/ui/styles/prices-period-selector.scss';

  :deep(.ant-radio-wrapper .ant-radio-checked .ant-radio-inner) {
    background-color: white !important;
  }

  .schedule-title-container {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
  }

  .title-left {
    display: flex;
    flex-direction: column;
    gap: 4px;
  }

  .schedule-title {
    display: inline-flex;
    align-items: center;
    gap: 6px;
  }

  .schedule-title :deep(svg) {
    display: block;
    flex-shrink: 0;
  }

  .status-row {
    display: flex;
    align-items: center;
    gap: 6px;
  }

  .status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
  }

  .status-dot.COMPLETED {
    background-color: #52c41a;
  }

  .status-dot.IN_PROGRESS {
    background-color: #faad14;
  }

  .status-dot.NOT_STARTED {
    background-color: #d9d9d9;
  }

  .status-text {
    font-size: 12px;
    color: #8c8c8c;
    font-weight: 500;
  }

  .schedule-card {
    transition: all 0.25s ease;
    border: 1px solid #e5e7eb;
  }

  .variation-progress {
    font-size: 13px;
    margin-bottom: 8px;
    color: #8c8c8c;
  }

  .all-completed {
    color: #52c41a;
    font-weight: 500;
  }

  .schedule-card.selected {
    animation: pulse 0.3s ease;
  }

  .variation-progress {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
  }

  .progress-left {
    font-size: 13px;
    color: #8c8c8c;
  }

  .all-completed {
    color: #52c41a;
    font-weight: 500;
  }

  .progress-right {
    display: flex;
    gap: 16px;
    align-items: center;
  }

  .legend-item {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    font-weight: 500;
  }

  .legend-item .dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
  }

  .legend-item.completed .dot {
    background-color: #52c41a;
  }

  .legend-item.in-progress .dot {
    background-color: #faad14;
  }

  .legend-item.not-started .dot {
    background-color: #d9d9d9;
  }

  @keyframes pulse {
    0% {
      transform: scale(0.98);
    }
    100% {
      transform: scale(1);
    }
  }
</style>
