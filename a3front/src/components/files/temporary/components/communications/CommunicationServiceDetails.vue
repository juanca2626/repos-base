<template>
  <div class="service-detail">
    <div class="box-title-service">
      <font-awesome-icon :icon="['fas', 'bus']" />
      {{ service.itinerary?.name }}
    </div>
    <a-row :gutter="24" align="middle" justify="space-between" class="mt-4">
      <a-col span="10">
        <div class="flex-row-service">
          <div class="date-service">
            <IconCalendarLight color="#979797" />
            <span class="label-date">{{ formatDate(service.itinerary?.date_in) }}</span>
          </div>
          <div class="pax-service">
            <span class="label-pax">Pasajeros:</span>
            <font-awesome-icon :icon="['fas', 'user']" />
            <span class="label-adult">{{ service.itinerary?.adults }}</span>
            <font-awesome-icon :icon="['fas', 'child-reaching']" />
            <span class="label-child">{{ service.itinerary?.children }}</span>
          </div>
        </div>
      </a-col>
      <a-col span="14">
        <div class="total-price-service">
          <span class="label-price">Costo</span>
          <span class="total-price"> $ {{ service.itinerary?.total_amount.toFixed(2) }} </span>
        </div>
      </a-col>
    </a-row>
    <a-row :gutter="24" align="middle" justify="space-between" class="mt-1">
      <a-col :span="24">
        <a-divider style="height: 1px; background-color: #bbbdbf" />
      </a-col>
    </a-row>
  </div>
</template>

<script setup lang="ts">
  import { defineProps } from 'vue';
  import IconCalendarLight from '@/quotes/components/icons/IconCalendarLight.vue';

  defineProps({
    service: {
      type: Object,
      required: true,
    },
  });

  function formatDate(dateString: string | null): string {
    if (!dateString) return '';
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('es-ES', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
    }).format(date);
  }
</script>

<style scoped lang="scss">
  .service-detail {
    .box-title-service {
      font-weight: bold;
      font-size: 18px;
      color: #575757;
    }

    .flex-row-service {
      display: flex;
      align-items: center;
      gap: 50px;

      .date-service,
      .pax-service {
        display: flex;
        align-items: center;
        gap: 5px;
        color: #979797;
      }

      .label-date,
      .label-pax {
        font-weight: bold;
        color: #979797;
      }
    }

    .total-price-service {
      display: flex;
      justify-content: right;
      align-items: center;
      gap: 10px;

      .label-price {
        color: #979797;
        font-size: 16px;
        font-family: 'Montserrat', sans-serif !important;
        font-weight: 700;
      }

      .total-price {
        color: #eb5757;
        font-size: 24px;
        font-family: 'Montserrat', sans-serif !important;
        font-weight: 700;
      }

      .label-price-penalty {
        color: #e4b804;
        font-size: 14px;
        font-family: 'Montserrat', sans-serif !important;
        font-weight: 700;
      }

      .total-price-penalty {
        color: #e4b804;
        font-size: 24px;
        font-family: 'Montserrat', sans-serif !important;
        font-weight: 700;
      }
    }
  }
</style>
