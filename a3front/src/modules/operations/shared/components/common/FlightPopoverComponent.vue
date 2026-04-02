<template>
  <a-popover placement="bottom" @openChange="(val: boolean) => val && showData()">
    <template #content>
      <template v-for="(flight, index) in flights" :key="index">
        <a-row class="flight-row">
          <a-col :span="24">
            <a-flex justify="space-between" align="center">
              <!-- Origen -->
              <a-typography-text class="city-label">
                <font-awesome-icon :icon="['fas', resolveFlightIcon(flight)]" />
                {{ flight.city_in_name }}
                <span class="city-code">({{ flight.city_in_iso }})</span>
              </a-typography-text>

              <!-- Destino -->
              <a-typography-text class="city-label">
                <font-awesome-icon :icon="['fas', 'arrow-right']" class="icon-arrow" />
                {{ flight.city_out_name }}
                <span class="city-code">({{ flight.city_out_iso }})</span>
              </a-typography-text>
            </a-flex>

            <a-typography-text class="flight-detail">
              Vuelo
              <span class="flight-strong"> {{ flight.flights[0].airline_number }}</span> -
              {{ flight.flights[0].airline_name }}
            </a-typography-text>

            <a-typography-text class="flight-detail">
              PNR: {{ flight.flights[0].pnr }}
            </a-typography-text>
          </a-col>
        </a-row>

        <a-row>
          <a-col :span="10">
            <a-typography-text class="section-label">Salida:</a-typography-text>
            <a-typography-text class="typography-text">
              <span class="time-main">
                {{ dayjs(flight.flights[0].departure_time, 'HH:mm:ss').format('HH:mm') }}
              </span>
              <span class="time-unit">HRS</span>
            </a-typography-text>
            <a-typography-text type="secondary" class="date-text">
              {{ dayjs(flight.date_out).format('DD MMM YYYY') }}
            </a-typography-text>
          </a-col>

          <a-col :span="4" />

          <a-col :span="10">
            <a-typography-text class="section-label">Llegada:</a-typography-text>
            <a-typography-text class="typography-text">
              <span class="time-main">
                {{ dayjs(flight.flights[0].arrival_time, 'HH:mm:ss').format('HH:mm') }}
              </span>
              <span class="time-unit">HRS</span>
            </a-typography-text>
            <a-typography-text type="secondary" class="date-text">
              {{ dayjs(flight.date_in).format('DD MMM YYYY') }}
            </a-typography-text>
          </a-col>
        </a-row>

        <a-divider class="divider" />
      </template>

      <a-row>
        <a-col :span="24">
          <a-typography-text type="secondary" class="updated-text">
            Actualizado hace <span class="flight-strong">3h 11m</span>
          </a-typography-text>
        </a-col>
      </a-row>
    </template>

    <a-button v-for="(flight, index) in flights" :key="index" type="link" class="popover-button">
      <font-awesome-icon :icon="['fas', resolveFlightIcon(flight)]" />
      {{ flight.flights[0].airline_code }}{{ flight.flights[0].airline_number }}
    </a-button>
  </a-popover>
</template>

<script setup lang="ts">
  import { computed, toRaw } from 'vue';
  import dayjs from 'dayjs';

  interface Props {
    data: any;
  }

  const props = defineProps<Props>();
  const flights = computed(() => props.data?.matched_flights || []);

  const resolveFlightIcon = (flight: any): 'plane-arrival' | 'plane-departure' | 'ban' => {
    if (flight?.type === 'in') return 'plane-arrival'; // avión hacia abajo
    if (flight?.type === 'out') return 'plane-departure'; // avión hacia arriba

    return 'ban'; // ícono de acceso denegado o bloqueado
  };

  const showData = () => {
    const rawFlights = toRaw(flights.value);
    console.log(rawFlights);
    // console.table(
    //   rawFlights.map((f: any) => ({
    //     number_flight: `${f.flights?.[0]?.airline_code ?? ''}${f.flights?.[0]?.airline_number ?? ''}`,
    //     country_in_name: f.country_in_name,
    //     country_out_name: f.country_out_name,
    //     hour_out: f.flights?.[0]?.departure_time ?? '',
    //     hour_in: f.flights?.[0]?.arrival_time ?? '',
    //     type: f.type,
    //     f: f,
    //   }))
    // );
  };
</script>

<style scoped lang="scss">
  .flight-row {
    margin-bottom: 7px;
    width: auto;
  }

  .city-label {
    font-size: 14px;
    color: #1284ed;
    fill: #1284ed;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    white-space: nowrap;
  }

  .city-code {
    color: #babcbd;
  }

  .flight-detail {
    display: block;
    color: #7e8285;
    font-size: 12px;
  }

  .flight-strong {
    font-weight: 700;
  }

  .section-label {
    display: block;
    font-size: 10px;
    font-weight: 400;
    color: #7e8285;
  }

  .typography-text {
    display: block;
    margin: -4px 0 -8px 0;
  }

  .time-main {
    font-size: 18px;
    font-weight: 700;
    color: #2f353a;
  }

  .time-unit {
    font-size: 10px;
    font-weight: 400;
    color: #7e8285;
    margin-left: 4px;
  }

  .icon-arrow {
    margin-left: 2px;
    margin-right: 2px;
    color: #a1a2a2;
    font-size: 16px;
  }

  .date-text {
    font-size: 12px;
    font-weight: 500;
  }

  .divider {
    margin: 10px 0 5px 0;
  }

  .updated-text {
    font-size: 10px;
  }

  .popover-button {
    padding: 0;
    color: #1284ed;

    &:hover {
      color: #1284ed;
    }
  }
</style>
