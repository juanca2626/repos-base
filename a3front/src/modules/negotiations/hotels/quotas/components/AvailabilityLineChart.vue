<template>
  <div class="availability-line-chart">
    <svg viewBox="0 0 1200 120" preserveAspectRatio="none" class="chart-svg">
      <!-- Grid Y -->
      <line
        v-for="(value, index) in yValues"
        :key="`grid-y-${index}`"
        :x1="paddingLeft"
        :y1="getYPosition(value)"
        :x2="chartWidth + paddingLeft"
        :y2="getYPosition(value)"
        stroke="#E5E5E5"
        stroke-width="1"
      />

      <!-- Labels Y -->
      <text
        v-for="(value, index) in yValues"
        :key="`y-label-${index}`"
        :x="paddingLeft - 8"
        :y="getYPosition(value) + 4"
        text-anchor="end"
        class="axis-label"
      >
        {{ value }}
      </text>

      <!-- Labels X -->
      <text
        v-for="(label, index) in xLabelsComputed"
        :key="`x-label-${index}`"
        :x="getXPosition(index) + paddingLeft"
        :y="chartHeight + paddingTop + xAxisLabelPaddingTop"
        text-anchor="middle"
        class="axis-label"
      >
        {{ label }}
      </text>

      <!-- Series -->
      <g
        v-for="(_, seriesIndex) in normalizedData"
        :key="`series-${seriesIndex}`"
        v-show="chartStore.isLineVisible(seriesIndex)"
      >
        <!-- Líneas (segmentadas) -->
        <polyline
          v-for="(segment, segmentIndex) in getLineSegments(seriesIndex)"
          :key="`line-${seriesIndex}-${segmentIndex}`"
          :points="segment"
          fill="none"
          :stroke="getLineColor(seriesIndex)"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
        />

        <!-- Puntos -->
        <circle
          v-for="(point, i) in getSeriesDataPoints(seriesIndex)"
          :key="`point-${seriesIndex}-${i}`"
          :cx="point.x + paddingLeft"
          :cy="point.y + paddingTop"
          r="3"
          :fill="getLineColor(seriesIndex)"
          style="cursor: pointer"
          @click="
            handlePointClick(
              seriesIndex,
              point.originalIndex,
              point.x + paddingLeft,
              point.y + paddingTop
            )
          "
        />
      </g>
    </svg>

    <ChartPointPopup
      v-if="popupVisible && popupData"
      :visible="popupVisible"
      :date="popupData.date"
      :category-name="popupData.categoryName"
      :category-color="popupData.categoryColor"
      :total-hotels="popupData.totalHotels"
      :hotel-details="popupData.hotelDetails"
      :chart-type="chartStore.chartType"
      :x="popupData.x"
      :y="popupData.y"
    />
  </div>
</template>

<script setup lang="ts">
  import { computed, ref, onMounted, onUnmounted } from 'vue';
  import moment from 'moment';
  import ChartPointPopup from './ChartPointPopup.vue';
  import { useHotelAvailabilityChartStore } from '@/modules/negotiations/hotels/quotas/store/hotel-availability-chart.store';

  /* ------------------ Props ------------------ */
  interface Props {
    data?: number[] | (number | null)[][];
    colors?: string[];
    xLabels?: string[];
  }

  const props = withDefaults(defineProps<Props>(), {
    data: () => [],
    colors: () => [],
    xLabels: () => [],
  });

  const chartStore = useHotelAvailabilityChartStore();

  /* ------------------ Popup ------------------ */
  const popupVisible = ref(false);
  const popupData = ref<{
    date: string;
    categoryName: string;
    categoryColor: string;
    totalHotels: number;
    hotelDetails: any[];
    x: number;
    y: number;
  } | null>(null);

  /* ------------------ Layout ------------------ */
  const viewBoxWidth = 1200;
  const viewBoxHeight = 120;

  const paddingLeft = 20;
  const paddingTop = 20;
  const paddingRight = 15;
  const paddingBottom = 40;
  const xAxisLabelPaddingTop = 35;

  const chartWidth = computed(() => viewBoxWidth - paddingLeft - paddingRight);
  const chartHeight = computed(() => viewBoxHeight - paddingTop - paddingBottom);

  /* ------------------ Data ------------------ */
  const normalizedData = computed<(number | null)[][]>(() => {
    if (!props.data || !props.data.length) return [];

    if (typeof props.data[0] === 'number') {
      return [props.data as number[]];
    }

    return props.data as (number | null)[][];
  });

  const xLabelsComputed = computed(() => {
    return props.xLabels?.length ? props.xLabels : [];
  });

  const maxValue = computed(() => {
    const values = normalizedData.value.flat().filter((v): v is number => v !== null);

    if (!values.length) return 0;

    const max = Math.max(...values);
    return max > 0 ? Math.ceil(max / 5) * 5 : 0;
  });

  const yValues = computed(() => {
    const max = maxValue.value;
    return [0, Math.floor(max / 2), max];
  });

  /* ------------------ Helpers ------------------ */
  const getXPosition = (index: number) => {
    if (xLabelsComputed.value.length <= 1) return 0;
    const step = chartWidth.value / (xLabelsComputed.value.length - 1);
    return index * step;
  };

  const getYPosition = (value: number) => {
    if (!maxValue.value) return chartHeight.value;
    return paddingTop + chartHeight.value - (value / maxValue.value) * chartHeight.value;
  };

  const getLineColor = (seriesIndex: number) =>
    props.colors[seriesIndex % props.colors.length] || '#246337';

  /* ------------------ Líneas segmentadas ------------------ */
  const getLineSegments = (seriesIndex: number): string[] => {
    const series = normalizedData.value[seriesIndex];
    if (!series || !maxValue.value) return [];

    const segments: string[] = [];
    let current: string[] = [];

    series.forEach((value, index) => {
      if (value === null) {
        if (current.length > 1) segments.push(current.join(' '));
        current = [];
        return;
      }

      const x = getXPosition(index) + paddingLeft;
      const y = chartHeight.value - (value / maxValue.value) * chartHeight.value + paddingTop;

      current.push(`${x},${y}`);
    });

    if (current.length > 1) segments.push(current.join(' '));

    return segments;
  };

  /* ------------------ Puntos ------------------ */
  const getSeriesDataPoints = (seriesIndex: number) => {
    const series = normalizedData.value[seriesIndex];
    if (!series || !maxValue.value) return [];

    return series
      .map((value, index) => {
        if (value === null) return null;

        return {
          x: getXPosition(index),
          y: chartHeight.value - (value / maxValue.value) * chartHeight.value,
          originalIndex: index,
        };
      })
      .filter(Boolean) as { x: number; y: number; originalIndex: number }[];
  };

  /* ------------------ Click real (TU LÓGICA) ------------------ */
  const handlePointClick = (seriesIndex: number, pointIndex: number, x: number, y: number) => {
    const rawData = chartStore.rawChartData;
    if (!rawData || !rawData.length) return;

    const typeClassIds = Array.from(new Set(rawData.map((i) => i.typeclass_id))).sort(
      (a, b) => a - b
    );
    const typeClassId = typeClassIds[seriesIndex];

    const dateLabels = chartStore.chartXLabels;
    if (!dateLabels || pointIndex >= dateLabels.length) return;

    const dateLabel = dateLabels[pointIndex];
    const chartType = chartStore.chartType;

    let matchingItems = rawData.filter((i) => i.typeclass_id === typeClassId);

    if (chartType === 'day') {
      matchingItems = matchingItems.filter(
        (i) => moment(i.date, 'YYYY-MM-DD').format('DD') === dateLabel
      );
    } else {
      matchingItems = matchingItems.filter(
        (i) => moment(i.date, 'YYYY-MM').format('MMM') === dateLabel
      );
    }

    if (!matchingItems.length) return;

    const totalHotels = matchingItems.reduce((s, i) => s + i.score_del_periodo, 0);
    const hotelDetails = matchingItems.flatMap((i) => i.detalle || []);
    const firstItem = matchingItems[0];

    const chartElement = document.querySelector('.availability-line-chart');
    const svg = chartElement?.querySelector('svg');
    if (!svg) return;

    const rect = svg.getBoundingClientRect();

    popupData.value = {
      date: firstItem.date,
      categoryName: firstItem.typeclass_name,
      categoryColor: firstItem.typeclass_color,
      totalHotels,
      hotelDetails,
      x: rect.left + x,
      y: rect.top + y,
    };

    popupVisible.value = true;
  };

  /* ------------------ Outside click ------------------ */
  const handleClickOutside = (e: MouseEvent) => {
    const t = e.target as HTMLElement;
    if (!t.closest('.chart-point-popup') && !t.closest('circle')) {
      popupVisible.value = false;
    }
  };

  onMounted(() => document.addEventListener('click', handleClickOutside));
  onUnmounted(() => document.removeEventListener('click', handleClickOutside));
</script>

<style scoped lang="scss">
  .availability-line-chart {
    width: 100%;
    height: 100%;
    position: relative;
  }

  .chart-svg {
    width: 100%;
    height: 100%;
  }

  .axis-label {
    font-size: 14px;
    fill: #2f353a;
  }

  // Media query para pantallas menores a 1280px
  @media (max-width: 1280px) {
    .axis-label {
      font-size: 12px;
    }
  }
</style>
