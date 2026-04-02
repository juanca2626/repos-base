<template>
  <template v-if="loadingSimulation">
    <a-alert type="warning" class="text-dark-warning">
      <template #message>
        <a-row type="flex" style="gap: 10px; flex-flow: nowrap" align="middle" justify="start">
          <a-col>
            <LoadingMaca size="small" />
          </a-col>
          <a-col>
            <font-awesome-icon :icon="['fas', 'gears']" spin-pulse size="xl" />
          </a-col>
          <a-col flex="auto">
            <p class="mb-0 text-600">Simulación de reserva</p>
            <p class="mb-0">
              Aurora BOT está trabajando en la simulación de costos, en un momento podrás visualizar
              la información.
            </p>
            <a-progress :percent="showPercentItineraries" status="active" class="pe-4 mb-0" />
          </a-col>
        </a-row>
      </template>
    </a-alert>
  </template>
  <div class="mt-3" v-if="!loadingSimulation">
    <template v-if="new_passengers_total == 0">
      <a-alert type="info" class="mb-3">
        <template #description>
          <a-row type="flex" justify="start" align="top" style="gap: 10px">
            <a-col>
              <i class="bi bi-exclamation-circle" style="font-size: 18px"></i>
            </a-col>
            <a-col>
              <p class="text-700 mb-1 p-0">Error en la simulación</p>
              La simulación no encontró hoteles disponibles en las fechas actuales. Por lo tanto no
              se puede completar la simulación.
            </a-col>
          </a-row>
        </template>
      </a-alert>
    </template>

    <template v-if="new_passengers_total > 0">
      <div class="bg-light py-2" style="padding-left: 50px; padding-right: 50px">
        <h6 class="py-3 text-700 mt-3 mb-0">Simulador de costos</h6>
        <span class="line-dashed size-1"></span>
        <div class="my-5">
          <a-row :gutter="16">
            <a-col :span="9">
              <h6 class="my-3 text-700">
                Precio actual del file<template v-if="filesStore.getFile.status == 'xl'">
                  anulado</template
                >
              </h6>
              <h6><small class="text-400 mb-0">Precio neto por pasajero</small></h6>
              <div class="m-2">
                <template v-for="(item, i) in old_prices" :key="`item-new-${i}`">
                  <a-row
                    type="flex"
                    justify="space-between"
                    align="middle"
                    class="my-2"
                    v-if="item.flag_show"
                  >
                    <a-col>
                      <h5 class="text-700 mb-0" :style="{ color: item.color }">
                        ${{ formatNumber({ number: item.price / item.paxs, digits: 2 }) }}
                      </h5>
                    </a-col>
                    <a-col>
                      <h6 class="text-gray text-700 mb-0">{{ item.description }}</h6>
                    </a-col>
                    <a-col>
                      <h6 class="text-gray text-700 mb-0">1 pax</h6>
                    </a-col>
                  </a-row>
                </template>
                <a-divider style="margin: 0 auto" />
                <a-row type="flex" justify="space-between" align="middle" class="my-2">
                  <a-col>
                    <p class="text-danger text-700 mb-0">
                      ${{ formatNumber({ number: old_price_total, digits: 2 }) }}
                    </p>
                  </a-col>
                  <a-col>
                    <p class="text-700 mb-0">Total</p>
                  </a-col>
                  <a-col>
                    <p class="text-danger text-700 mb-0">{{ old_passengers_total }} pax</p>
                  </a-col>
                </a-row>
              </div>
            </a-col>
            <a-col flex="auto" class="pt-5 mt-5 px-5">
              <a-row type="flex" justify="space-between" align="middle" class="pt-5">
                <a-col>
                  <span class="circle bg-orange"></span>
                </a-col>
                <a-col flex="auto">
                  <span class="line-dashed size-2 mx-1"></span>
                </a-col>
                <a-col>
                  <span class="circle bg-green"></span>
                </a-col>
              </a-row>
            </a-col>
            <a-col :span="9">
              <h6 class="my-3 text-700">
                Precio modificado
                <template v-if="filesStore.getFile.status == 'xl'">de activación</template
                ><template v-else>del file</template>
              </h6>
              <h6><small class="text-400 mb-0">Precio neto por pasajero</small></h6>
              <div class="m-2">
                <template v-for="(item, i) in new_prices" :key="`item-modify-${i}`">
                  <a-row
                    type="flex"
                    justify="space-between"
                    align="middle"
                    class="my-2"
                    v-if="item.flag_show"
                  >
                    <a-col>
                      <h5 class="text-700 mb-0" :style="{ color: item.color }">
                        ${{ formatNumber({ number: item.price / item.paxs, digits: 2 }) }}
                      </h5>
                    </a-col>
                    <a-col>
                      <h6 class="text-gray text-700 mb-0">{{ item.description }}</h6>
                    </a-col>
                    <a-col>
                      <h6 class="text-gray text-700 mb-0">1 pax</h6>
                    </a-col>
                  </a-row>
                </template>
                <a-divider style="margin: 0 auto" />
                <a-row type="flex" justify="space-between" align="middle" class="my-2">
                  <a-col>
                    <p class="text-danger text-700 mb-0">
                      ${{ formatNumber({ number: new_price_total, digits: 2 }) }}
                    </p>
                  </a-col>
                  <a-col>
                    <p class="text-700 mb-0">Total</p>
                  </a-col>
                  <a-col>
                    <p class="text-danger text-700 mb-0">{{ new_passengers_total }} pax</p>
                  </a-col>
                </a-row>
              </div>
            </a-col>
          </a-row>
        </div>
      </div>

      <div class="mt-3">
        <a-alert type="warning">
          <template #message>
            <div class="text-warning">Hoteles disponibles</div>
          </template>
          <template #description>
            Según la tarifa actual del file (standard). Si desea verificar otras tarifas diríjase a
            cotizar
          </template>
        </a-alert>
      </div>
    </template>

    <div
      class="mt-3 text-right"
      v-if="new_passengers_total == 0 && quantity_adults == 0 && quantity_children == 0"
    >
      <a-button
        type="default"
        class="mx-2 px-4 text-600"
        v-on:click="sendToBoard()"
        danger
        :loading="filesStore.isLoading || filesStore.isLoadingAsync"
        size="large"
      >
        Ir a cotizar
      </a-button>
    </div>

    <div class="mt-3" v-if="new_passengers_total > 0">
      <a-collapse
        class="collapse-paxs w-100 bg-white"
        :bordered="false"
        accordion
        expandIconPosition="end"
      >
        <template #expandIcon="{ isActive }">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            width="28"
            height="28"
            viewBox="0 0 28 28"
            fill="none"
            :class="{ 'rotate-180': isActive }"
          >
            <path
              d="M21 17.5L14 10.5L7 17.5"
              stroke="#3D3D3D"
              stroke-width="3"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
          </svg>
        </template>
        <template v-for="(_city, index) in cities" :key="`city-${index}`">
          <a-collapse-panel :style="customStyle">
            <template #header>
              <h6>
                <span>
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="16"
                    height="16"
                    fill="currentColor"
                    class="bi bi-bag-check-fill"
                    viewBox="0 0 16 16"
                  >
                    <path
                      fill-rule="evenodd"
                      d="M10.5 3.5a2.5 2.5 0 0 0-5 0V4h5zm1 0V4H15v10a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V4h3.5v-.5a3.5 3.5 0 1 1 7 0m-.646 5.354a.5.5 0 0 0-.708-.708L7.5 10.793 6.354 9.646a.5.5 0 1 0-.708.708l1.5 1.5a.5.5 0 0 0 .708 0z"
                    />
                  </svg>
                </span>
                {{ _city.iso }}
                <small class="text-gray">disponibilidad de hoteles - Tarifa Standard</small>
              </h6>
            </template>
            <a-flex align="middle" wrap="nowrap" style="overflow-x: auto">
              <template v-for="(_hotel, h) in _city.hotels" :key="`hotel-${h}`">
                <a-col>
                  <hotel-card>
                    <template #name>{{ _hotel.name }}</template>
                    <template #category>{{ _hotel.class }}</template>
                    <template #date_in>{{ dayjs(_hotel.date_in).format('DD/MM/YYYY') }}</template>
                    <template #date_out>{{ dayjs(_hotel.date_out).format('DD/MM/YYYY') }}</template>
                    <template #nights>{{ checkDates(_hotel.date_in, _hotel.date_out) }}</template>
                    <template #quantity_rooms>{{ _hotel.quantity_rooms }}</template>
                    <template #type_room>{{ _hotel.rooms[0].room_type }}</template>
                  </hotel-card>
                </a-col>
              </template>
              <template v-for="(_service, s) in _city.services" :key="`service-${s}`">
                <a-col>
                  <service-card :service="_service">
                    <template #date_in>{{
                      dayjs(_service.date_reserve).format('DD/MM/YYYY')
                    }}</template>
                    <template #name>{{ _service.name }}</template>
                    <template #old_price
                      >{{ _service.currency.iso }}
                      {{ formatNumber({ number: _service.old_price, digits: 2 }) }}</template
                    >
                    <template #new_price
                      >{{ _service.currency.iso }}
                      {{ formatNumber({ number: _service.total_amount, digits: 2 }) }}</template
                    >
                  </service-card>
                </a-col>
              </template>
            </a-flex>
          </a-collapse-panel>
        </template>
      </a-collapse>
    </div>
  </div>
</template>

<script setup>
  import { onBeforeMount, ref, computed } from 'vue';
  import { checkDates, formatNumber } from '@/utils/files.js';
  import { useFilesStore } from '@store/files';
  import { useLanguagesStore } from '@store/global';
  import HotelCard from '@/components/files/simulation/HotelCard.vue';
  import ServiceCard from '@/components/files/simulation/ServiceCard.vue';
  import dayjs from 'dayjs';
  import { v4 as uuidv4 } from 'uuid';
  import LoadingMaca from '@/components/global/LoadingMaca.vue';

  const props = defineProps({
    quantity_adults: {
      type: Number,
      default: 0,
    },
    quantity_children: {
      type: Number,
      default: 0,
    },
    count_sgl: {
      type: Number,
      default: 0,
    },
    count_dbl: {
      type: Number,
      default: 0,
    },
    count_tpl: {
      type: Number,
      default: 0,
    },
  });

  const old_prices = ref([
    {
      price: 0,
      description: 'Simple',
      rooms: 0,
      paxs: 0,
      color: '#ff7a45',
      flag_show: false,
      items: [],
    },
    {
      price: 0,
      description: 'Doble',
      rooms: 0,
      paxs: 0,
      color: '#ff7a45',
      flag_show: false,
      items: [],
    },
    {
      price: 0,
      description: 'Triple',
      rooms: 0,
      paxs: 0,
      color: '#ff7a45',
      flag_show: false,
      items: [],
    },
  ]);

  const new_prices = ref([
    {
      price: 0,
      occupation: 1,
      description: 'Simple',
      rooms: 0,
      paxs: 0,
      color: '#1ed790',
      flag_show: false,
      items: [],
    },
    {
      price: 0,
      occupation: 2,
      description: 'Doble',
      rooms: 0,
      paxs: 0,
      color: '#1ed790',
      flag_show: false,
      items: [],
    },
    {
      price: 0,
      occupation: 3,
      description: 'Triple',
      rooms: 0,
      paxs: 0,
      color: '#1ed790',
      flag_show: false,
      items: [],
    },
  ]);

  const old_price_total = computed(() => {
    let total = 0;
    for (const old_price of old_prices.value) {
      if (old_price.flag_show) {
        total += parseFloat(old_price.price);
      }
    }
    return total;
  });

  const old_passengers_total = computed(() => {
    let total = 0;
    for (const old_price of old_prices.value) {
      if (old_price.flag_show) {
        total += parseInt(old_price.paxs);
      }
    }
    return total;
  });

  const new_price_total = computed(() => {
    let total = 0;
    for (const new_price of new_prices.value) {
      if (new_price.flag_show) {
        let occupation = 1;

        if (new_price.occupation === 1) {
          occupation = props.count_sgl;
        }
        if (new_price.occupation === 2) {
          occupation = props.count_dbl;
        }
        if (new_price.occupation === 3) {
          occupation = props.count_tpl;
        }

        total += parseFloat(new_price.price) * occupation;
      }
    }
    return total;
  });

  const new_passengers_total = computed(() => {
    let total = 0;
    for (const new_price of new_prices.value) {
      if (new_price.flag_show) {
        total += parseInt(new_price.paxs) * props.quantity_adults;
      }
    }

    if (new_passengers_total === 0) {
      filesStore.clearSimulations();
    }

    return total;
  });

  const cities = ref([]);
  const loadingSimulation = ref(true);

  const filesStore = useFilesStore();
  const languagesStore = useLanguagesStore();

  const customStyle =
    'background: #f7f7f7;border-radius: 4px;margin-bottom: 24px;border: 0;overflow: hidden';

  const sendToBoard = async () => {
    let new_date = dayjs(filesStore.getFile.dateIn).format('YYYY-MM-DD');
    let params = {
      force: true,
      date_init: new_date,
      activate: true,
    };

    await filesStore.sendQuote(filesStore.getFile.id, params);

    const error = filesStore.getError;

    if (error != '' && error != null) {
      notification.error({
        message: 'Error al activar',
        description: `${error.message}`,
      });

      filesStore.finished();
      return;
    }

    localStorage.setItem('a3_file_id', filesStore.getFile.id);
    window.location.href = `${window.url_app}quotes`;
  };

  const processRoomType = async (index, count, adults, children, itinerary, passengers) => {
    let quantityValidated = 0;
    let reservation = {};

    const rooms = [
      {
        adults,
        ages_child: [{ age: 5, child: 1 }],
        child: children,
        room: count,
      },
    ];

    const params = {
      client_id: filesStore.getFile.clientId,
      destiny: { code: '', label: '' },
      typeclass_id: 'all',
      hotels_search_code: itinerary.object_code,
      date_from: itinerary.date_in,
      date_to: itinerary.date_out,
      quantity_rooms: 1,
      quantity_persons_rooms: rooms,
      lang: languagesStore.getLanguage,
      simulation: true,
    };

    await filesStore.fetchHotels(params);

    for (const hotel of filesStore.getAllHotels) {
      if (quantityValidated >= count) break;

      hotel.date_in = itinerary.date_in;
      hotel.date_out = itinerary.date_out;
      hotel.quantity_rooms = itinerary.rooms.length;

      cities.value[index].hotels.push(hotel);

      const room = hotel.rooms[0];
      const occupation = room.occupation;
      const paxs = parseInt(adults) + parseInt(children); // `children` debe estar definido arriba
      const priceInfo = new_prices.value[occupation - 1];

      priceInfo.flag_show = true;
      priceInfo.paxs = Math.max(priceInfo.paxs, paxs);
      priceInfo.price += parseFloat(parseFloat(room.best_price / parseInt(adults))) / count;
      priceInfo.items.push(room);

      const rate = room.rates[0];
      const reservationParams = {
        token_search: filesStore.getTokenSearchHotels,
        search_parameters: filesStore.getSearchParametersHotels,
        hotel,
        room,
        rate,
        hotel_name: hotel.name,
        occupation: room.occupation,
        top: false,
        quantity: hotel.quantity_rooms,
        passengers,
      };

      reservation = filesStore.putFileItinerariesReplace(reservationParams, true);

      console.log('NEW HOTEL: ', reservation);

      filesStore.addSimulation({
        params,
        search: hotel,
        hotel: reservation,
        type: 'hotel',
      });

      quantityValidated++;
    }
  };

  const itemsProcessed = ref(0);

  const showPercentItineraries = computed(() => {
    const count_success = itemsProcessed.value;
    const total = filesStore.getFileItineraries.length;
    if (total === 0) return 0;
    const percent = parseFloat((count_success / total) * 100);
    return parseFloat(percent).toFixed(2);
  });

  onBeforeMount(async () => {
    loadingSimulation.value = true;
    filesStore.clearSimulations();

    console.log(props);
    // ----------------------------------------------------------

    for (const itinerary of filesStore.getFileItineraries) {
      let index = -1;
      let passengers = [];
      let reservation = {};

      console.log('ITINERARY ORIGINAL: ', itinerary);

      if (itinerary.entity == 'hotel' || itinerary.entity == 'service') {
        if (itinerary.entity == 'hotel') {
          for (const room of itinerary.rooms) {
            let paxs = parseInt(room.total_adults) + parseInt(room.total_children);

            if (room.total_rooms > 0) {
              let occupation = Math.ceil(paxs / room.total_rooms);

              const price = room.units
                .flatMap((unit) => unit.nights.map((night) => night.price_adult_sale))
                .reduce((total, price) => parseFloat(total) + parseFloat(price), 0);

              old_prices.value[occupation - 1].flag_show = true;
              old_prices.value[occupation - 1].paxs =
                old_prices.value[occupation - 1].paxs < paxs
                  ? paxs
                  : old_prices.value[occupation - 1].paxs;
              old_prices.value[occupation - 1].price += price;
              old_prices.value[occupation - 1].items.push(room);

              passengers = room.units.map((unit) => {
                unit.accommodations.map((accommodation) => accommodation.file_passenger_id);
              });
            }
          }
        }

        if (itinerary.entity == 'service') {
          let price = parseFloat(itinerary.total_amount / itinerary.adults);

          old_prices.value.forEach((old_price) => {
            old_price.price += parseFloat(price);
            old_price.items.push(itinerary);
          });

          passengers = itinerary.accommodations
            ? itinerary.accommodations.map((accommodation) => accommodation.file_passenger_id)
            : [];
        }

        if (passengers.length > 0) {
          passengers = filesStore.getFilePassengers.map((pax) => pax.id);
        }

        cities.value.forEach((city, c) => {
          if (city.iso === itinerary.city_in_iso) {
            index = c;
          }
        });

        if (index === -1) {
          cities.value.push({
            iso: itinerary.city_in_iso,
            hotels: [],
            services: [],
          });

          index = cities.value.length - 1;
        }
      }

      console.log('OLD PRICES: ', old_prices.value);

      let adults = props.quantity_adults > 0 ? props.quantity_adults : itinerary.adults;
      let children = props.quantity_children > 0 ? props.quantity_children : itinerary.children;

      if (itinerary.entity === 'hotel') {
        const roomTypes = [
          { type: 'sgl', count: props.count_sgl, adults: 1, children: 0 },
          { type: 'dbl', count: props.count_dbl / 2, adults: 2, children: 0 },
          { type: 'tpl', count: props.count_tpl / 3, adults: 3, children: 0 },
        ];

        for (const roomType of roomTypes) {
          if (roomType.count > 0) {
            await processRoomType(
              index,
              roomType.count,
              roomType.adults,
              roomType.children,
              itinerary,
              passengers
            );
          }
        }
      }

      if (itinerary.entity == 'service') {
        const params = {
          lang: languagesStore.getLanguage,
          client_id: `${filesStore.getFile.clientId}`,
          origin: '',
          destiny: '',
          date: `${itinerary.date_in}`,
          quantity_persons: {
            adults: `${adults}`,
            child: `${children}`,
            age_childs: [
              {
                age: 1,
              },
            ],
          },
          type: 'all',
          category: 'all',
          experience: 'all',
          classification: 'all',
          filter: `${itinerary.object_code}`,
          limit: 1,
          page: 1,
          simulation: true,
        };

        await filesStore.fetchServices(params);

        for (let service of filesStore.getServices) {
          service.old_price = itinerary.total_amount / itinerary.adults;
          service.search_parameters_services = params;
          service.price = service.total_amount / itinerary.adults;
          cities.value[index].services.push(service);

          new_prices.value.forEach((new_price) => {
            new_price.price += parseFloat(service.price);
            new_price.items.push(service);
          });

          // Identificador Service..
          const ident = uuidv4().replace(/-/g, '');
          service.ident = ident;

          let params_reservation = {
            service: service,
            rate: service.rate,
            quantity: 1,
            adults: adults,
            children: children,
            token_search: filesStore.getTokenSearchServices,
            search_parameters_services: filesStore.getSearchParametersServices,
            price: service.price,
            passengers: passengers,
          };

          reservation = filesStore.putFileItinerariesServiceReplace(params_reservation, true);

          console.log('NEW SERVICE: ', reservation);

          filesStore.addSimulation({
            params: params,
            search: service,
            service: reservation,
            type: 'service',
          });
        }
      }

      console.log('NEW PRICES: ', new_prices.value);

      itemsProcessed.value++;
    }

    loadingSimulation.value = false;

    if (new_passengers_total == 0) {
      filesStore.clearSimulations();
    }
  });
</script>

<style scoped>
  .price-text {
    font-size: 24px;
    font-weight: bold;
  }

  .total {
    color: #ff4d4f;
  }

  .pax-total {
    color: #52c41a;
    text-align: right;
  }

  .price-row {
    text-align: center;
  }

  .text-right {
    text-align: right;
  }
</style>
