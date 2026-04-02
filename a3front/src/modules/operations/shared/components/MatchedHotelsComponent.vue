<template>
  <div v-if="hasHotels" style="text-align: left">
    <!-- Un solo hotel -->
    <div v-if="matchedHotels!.length === 1" style="margin-bottom: 12px">
      <div style="font-size: 12px">{{ matchedHotels![0].name }}</div>
    </div>

    <!-- Dos o más hoteles -->
    <div v-else>
      <div v-for="(hotel, index) in matchedHotels" :key="hotel.id" style="margin-bottom: 12px">
        <div
          v-if="index === 0"
          style="display: flex; align-items: center; gap: 8px; font-size: 12px"
        >
          <font-awesome-icon :icon="['fas', 'arrow-right-to-bracket']" class="in_hotel_icon" />
          <div>{{ hotel.name }}</div>
        </div>

        <div
          v-else-if="index === 1"
          style="display: flex; align-items: center; gap: 8px; font-size: 12px"
        >
          <font-awesome-icon :icon="['fas', 'arrow-right-from-bracket']" class="in_hotel_icon" />
          <div>{{ hotel.name }}</div>
        </div>

        <div v-else style="font-size: 12px">
          {{ hotel.name }}
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
  import { computed } from 'vue';

  interface Hotel {
    id: number;
    name: string;
  }

  const props = defineProps<{
    matchedHotels: Hotel[] | null;
  }>();

  const hotels = computed(() => props.matchedHotels ?? []);
  const hasHotels = computed(() => hotels.value.length > 0);
</script>

<style scoped lang="scss">
  .in_hotel_icon {
    font-size: 12px;
    color: #1284ed;
  }
</style>
