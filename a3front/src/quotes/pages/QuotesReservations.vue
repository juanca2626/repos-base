<script lang="ts" setup>
  import ButtonOutlineContainer from '@/quotes/components/global/ButtonOutlineContainer.vue';
  import ModalComponent from '@/quotes/components/global/ModalComponent.vue';
  import FileSingleUpload from '@/components/global/FileSingleUpload.vue';
  import { computed, reactive, ref, watchEffect, onMounted } from 'vue';
  import { debounce } from 'lodash-es';
  import dayjs from 'dayjs';
  import type { Country, State } from '@/quotes/interfaces';
  import { useQuote } from '@/quotes/composables/useQuote';
  import useQuoteDocTypes from '@/quotes/composables/useQuoteDocTypes';
  import useCountries from '@/quotes/composables/useCountries';
  import useStates from '@/quotes/composables/useStates';
  import useOrigins from '@/quotes/composables/useOrigins';
  import useArlines from '@/quotes/composables/useArlines';
  import type { FormInstance } from 'ant-design-vue';

  import { useI18n } from 'vue-i18n';

  const { t } = useI18n();

  const { page, quote, save_reservation, downloadPassengerExcel, uploadPassengerExcel } =
    useQuote();
  const { docTypes } = useQuoteDocTypes();
  const { countries, getPhoneCode } = useCountries();
  const { origins, getOrigins } = useOrigins();
  const { airlines, getAirlines } = useArlines();

  const { states, getStates } = useStates();

  const docTypesFilters = computed(() => docTypes.value.filter((t) => [1, 2, 3].includes(t.id)));

  const isPassportExpired = (passenger: any) => {
    if (passenger.passport_expiration) {
      const expiration = dayjs(passenger.passport_expiration);
      const travelDate = dayjs(quote.value?.date_in);

      if (!expiration.isValid() || !travelDate.isValid()) return false;

      // Retorna true si faltan menos de 6 meses para que expire
      // respecto a la fecha de ingreso al país.
      return expiration.diff(travelDate, 'month') < 6;
    }

    return false;
  };

  onMounted(async () => {
    // DATALAYER para detalle de quotes..
    const services = quote.value.categories[0].services;
    let total_amount = 0;

    const items = services
      .filter((service) => service.type === 'service' || service.type === 'group_header')
      .map((service) => {
        let item = {};

        if (service.type === 'service') {
          item = {
            item_id: service.service.service.id,
            item_sku: service.service.service.aurora_code,
            item_name: service.service.service.name.toUpperCase(),
            price: service.service.import.total_amount,
            item_brand: '',
            item_category: 'service',
            item_category2: 'quote_product',
            item_list_id: '',
            item_list_name: '',
          };

          total_amount += parseFloat(service.service.import.total_amount);
        }

        if (service.type === 'group_header') {
          item = {
            item_id: service.service.hotel.id,
            item_sku: service.service.hotel.channel[0].code,
            item_name: service.service.hotel.name.toUpperCase(),
            price: service.service.import,
            item_brand: '',
            item_category: 'hotel',
            item_category2: 'quote_product',
            item_list_id: '',
            item_list_name: '',
          };

          total_amount += parseFloat(service.service.import);
        }

        return item;
      });

    dataLayer.push({
      event: 'begin_checkout',
      funnel_type: 'quotes-funnel',
      currency: 'USD',
      value: total_amount,
      package_id: quote.value.id,
      package_code: quote.value.id,
      package_name: quote.value.name.toUpperCase(),
      items: items,
    });

    /*
    window.dataLayer.push({
      'content-name': '/quotes',
      'content-view-name': 'quotes-reservation',
      event: 'content-view',
    });
    */
  });

  // const { showIsLoading, closeIsLoading } = useLoader();
  const dateFormat = 'DD/MM/YYYY';

  const formRefFlight = ref<FormInstance>();

  const formRefInput = ref(null);

  interface phoneCode {
    label: string;
    code: string;
  }

  const state = reactive({
    //   currentPassenger : [],
    //   currentFlights : [true, false, false],
    showModalIflight: false,
  });

  const currentPassenger = ref<boolean[]>([]);
  const currentFlights = ref<boolean[]>([]);
  const listPhoneCode = ref<phoneCode[]>([]);

  // const flights = ref([])

  const form_flights = reactive({
    flight: {
      destiny: '',
      origin: '',
      date_flight: '', //dayjs(quote.value.date_in)
      passengers: [],
      disabled: false,
    },
  });

  const types_room = [
    {
      label: 'SGL',
      code: 'SGL',
    },
    {
      label: 'DBL',
      code: 'DBL',
    },
    {
      label: 'TPL',
      code: 'TPL',
    },
  ];

  const add_flight = async () => {
    formRefFlight.value
      .validate()
      .then(async () => {
        quote.value.flights.push({
          flag_type: '',
          origin: form_flights.flight.origin,
          destiny: form_flights.flight.destiny,
          date: form_flights.flight.date_flight,
          number_flight: '',
          departure: '',
          arrival: '',
          hour_range: '',
          pnr: '',
          airline: '',
          passengers: form_flights.flight.passengers,
          adult: '',
          child: '',
          code_flight: '',
        });

        // currentFlights.value.push(false);
        // select_search_destiny.data = [];
        form_flights.flight.origin = '';
        form_flights.flight.destiny = '';
        form_flights.flight.date_flight = '';
        form_flights.flight.passengers = [];

        await getOrigins('');
        select_search_destiny.data = origins.value.map((row) => ({
          label: row.label,
          value: row.code,
        }));

        toggleModalFlight();
        changeDataFlight();
      })
      .catch((error) => {
        if (error.errorFields && error.errorFields.length > 0) {
          const formu_name = error.errorFields[0].name[0];
          const item = error.errorFields[0].name[1];

          if (formu_name == 'passengers') {
            currentPassenger.value[item] = !currentPassenger.value[item];
          }

          if (formu_name == 'flights') {
            currentFlights.value[item] = !currentPassenger.value[item];
          }
        } else {
          console.log(error);
        }
      });
  };

  const add_item = (item: number) => {
    quote.value.flights.push({
      date: dataFlights.value[item].date,
      origin: '',
      destiny: '',
      number_flight: '',
      departure: '',
      arrival: '',
      hour_range: '',
      pnr: '',
      airline: '',
      passengers: [],
      adult: '',
      child: '',
      code_flight: '',
    });
    changeDataFlight();
  };

  const changeDataFlight = () => {
    currentFlights.value = [];
    dataFlights.value = [];
    let indexFlights = 0;
    quote.value.flights.forEach((element, index) => {
      let result = dataFlights.value.filter(
        (e) => e.date_unique === element.date.format('YYYY-MM-DD')
      );

      element['index'] = index;

      if (result.length > 0) {
        dataFlights.value.forEach((element2, indexSub) => {
          if (element2.date_unique === element.date.format('YYYY-MM-DD')) {
            // add_item(index);
            dataFlights.value[indexSub].items.push(element);
          }
        });
      } else {
        currentFlights.value.push(false);
        dataFlights.value.push({
          flag_type: element.flag_type,
          date: element.date,
          date_unique: element.date.format('YYYY-MM-DD'),
          index_header: indexFlights,
          passenger_options: JSON.parse(JSON.stringify(passenger_options.value)),
          items: [element],
        });
        indexFlights++;
      }
    });

    toggleDownFlights(dataFlights.value.length - 1);
  };

  const toggleDownPassenger = (item: number) => {
    currentPassenger.value[item] = !currentPassenger.value[item];
  };

  const toggleDownFlights = (item: number) => {
    console.log(item);
    currentFlights.value[item] = !currentFlights.value[item];
  };

  const toggleModalFlight = () => {
    form_flights.flight.destiny = '';
    form_flights.flight.origin = '';
    form_flights.flight.date_flight = '';
    form_flights.flight.passengers = [];
    form_flights.flight.disabled = true;

    state.showModalIflight = !state.showModalIflight;
  };

  const changeDate = () => {
    if (form_flights.flight.date_flight) {
      form_flights.flight.disabled = false;

      dataFlights.value.forEach((rows) => {
        if (rows.date_unique == form_flights.flight.date_flight.format('YYYY-MM-DD')) {
          let selectedOptions = [];
          rows.items.forEach((fli) => {
            fli.passengers.forEach((passenger) => {
              selectedOptions.push(passenger);
            });
          });

          passenger_options.value.forEach((option, op_index) => {
            if (selectedOptions.includes(option.value)) {
              passenger_options.value[op_index].disabled = true;
            } else {
              passenger_options.value[op_index].disabled = false;
            }
          });
        } else {
          passenger_options.value.forEach((option, op_index) => {
            passenger_options.value[op_index].disabled = false;
          });
        }
      });
    } else {
      form_flights.flight.disabled = true;
    }
  };

  const changePage = async (newView: string) => {
    page.value = newView;
  };

  // const passengers = computed(() => {
  //   return quote.value.passengers
  // })

  const passenger_options = computed(() => {
    return quote.value.passengers.map((row, index) => ({
      label:
        'Pasajero ' +
        (index + 1) +
        ' ' +
        row.type +
        (row.type == 'CHD' ? ' (' + row.age_child.age + 'y)' : ''),
      value: row.id,
      type: row.type,
      disabled: false,
      age_child: row.type == 'CHD' ? row.age_child.age : 0,
    }));
  });

  // console.log(passenger_options);
  const dataFlights = ref([]);
  watchEffect(() => {
    // let currentPassenger: Array<boolean> = []
    quote.value.passengers.forEach((element) => {
      currentPassenger.value.push(false);
      element.birthday_selected =
        element.birthday && element.birthday != '0000-00-00' ? dayjs(element.birthday) : null;
    });
    currentPassenger.value[0] = true;
    console.log('CODES', getPhoneCode());
    listPhoneCode.value = getPhoneCode();
  });

  const passengerChange = (value, index_header: number, index_chl: number) => {
    let optionSelected = dataFlights.value[index_header].items[index_chl].passengers;

    dataFlights.value.forEach((rows, index) => {
      let selectedOptions = [];
      rows.items.forEach((fli) => {
        fli.passengers.forEach((passenger) => {
          selectedOptions.push(passenger);
        });
      });

      rows.passenger_options.forEach((option, op_index) => {
        if (selectedOptions.includes(option.value) && !optionSelected.includes(option.value)) {
          dataFlights.value[index].passenger_options[op_index].disabled = true;
        } else {
          dataFlights.value[index].passenger_options[op_index].disabled = false;
        }
      });
    });
  };

  const updateCodeFlight = (value, index: number) => {
    dataFlights.value[index].items.forEach((element) => {
      quote.value.flights[element.index].code_flight = value.target.value;
    });
  };

  const countryChange = (value: { option: Country }, index: number) => {
    quote.value.passengers[index].country_iso = value.option.iso;
    quote.value.passengers[index].city_ifx_iso = null;
    quote.value.passengers[index].phone_code = value.option.phone_code;
    getStates(value.option.iso);
  };

  const stateChange = (value: { option: State }, index: number) => {
    quote.value.passengers[index].city_ifx_iso = value.option.iso;
  };

  const setPhoneCode = (value: { option: State }, index: number) => {
    quote.value.passengers[index].phone_code = value.option.code;
  };

  const hoursChange = (value, index: number) => {
    quote.value.flights[index].arrival = value[0].format('HH:mm');
    quote.value.flights[index].departure = value[1].format('HH:mm');
  };

  const birthdayChange = (value, index: number) => {
    quote.value.passengers[index].birthday = value.format('YYYY-MM-DD');
  };

  const select_search_destiny = reactive({
    data: [],
    value: [],
    fetching: false,
  });

  const serach_destiny = debounce(async (value: string) => {
    select_search_destiny.data = [];
    select_search_destiny.fetching = true;

    await getOrigins(value.toUpperCase());

    const results = origins.value.map((row) => ({
      label: row.label,
      value: row.code,
    }));

    select_search_destiny.data = results;
    select_search_destiny.fetching = false;
  }, 300);

  const select_search_airline = reactive({
    data: [],
    value: [],
    fetching: false,
  });

  const serach_airline = debounce(async (value: string) => {
    select_search_airline.data = [];
    select_search_airline.fetching = true;

    await getAirlines(value.toUpperCase());

    const results = airlines.value.map((row) => ({
      label: row.label,
      value: row.code,
    }));

    select_search_airline.data = results;
    select_search_airline.fetching = false;
  }, 300);

  const handleFileUpload = async ($event: Event) => {
    const target = $event.target as HTMLInputElement;
    if (target && target.files) {
      const results = await uploadPassengerExcel(target.files[0]);

      results.data.forEach(async (element, index) => {
        let birthday = element.birthday ? dayjs(element.birthday, 'DD/MM/YYYY') : '';

        quote.value.passengers[index].first_name = element.first_name;
        quote.value.passengers[index].last_name = element.last_name;
        quote.value.passengers[index].doctype_iso = element.doctype_iso;
        quote.value.passengers[index].document_number = element.document_number;
        quote.value.passengers[index].country_iso = element.country_iso;
        quote.value.passengers[index].gender = element.gender;
        quote.value.passengers[index].birthday = birthday ? birthday.format('YYYY-MM-DD') : '';
        quote.value.passengers[index].birthday_selected = birthday ? birthday : null;
        quote.value.passengers[index].email = element.email;
        quote.value.passengers[index].phone_code = element.phone_code
          ? element.phone_code.toString()
          : null;
        quote.value.passengers[index].phone = element.phone;
        quote.value.passengers[index].tiphab = element.tiphab;
        quote.value.passengers[index].medical_restrictions = element.medical_restrictions;
        quote.value.passengers[index].dietary_restrictions = element.dietary_restrictions;

        quote.value.passengers[index].city_ifx_iso = null;
        await getStates(element.country_iso);
      });
      formRefInput.value.value = '';
    }
  };

  const formRef = ref<FormInstance>();

  const onSubmit = () => {
    formRef.value
      .validate()
      .then(async () => {
        const result = await save_reservation();

        console.log('RESULT', result);

        if (result != false) {
          changePage('reservations-confirmation');
        }

        // changePage('reservations-confirmation');
        window.scrollTo(0, 0);
      })
      .catch((error) => {
        if (error.errorFields && error.errorFields.length > 0) {
          const formu_name = error.errorFields[0].name[0];
          const item = error.errorFields[0].name[1];

          if (formu_name == 'passengers') {
            currentPassenger.value[item] = true;
          }

          if (formu_name == 'flights') {
            currentFlights.value[item] = true;
          }
        } else {
          console.log(error);
        }
      });
  };

  const delete_flight = (index: number) => {
    quote.value.flights.splice(index, 1);
    changeDataFlight();
  };

  const ini = async () => {
    await getOrigins('');
    select_search_destiny.data = origins.value.map((row) => ({
      label: row.label,
      value: row.code,
    }));

    await getAirlines('');
    select_search_airline.data = airlines.value.map((row) => ({
      label: row.label,
      value: row.code,
    }));
  };

  ini();

  const responseFilesFrom = (value, index: number) => {
    quote.value.passengers[index].document_url = value;
  };
</script>

<template>
  <div class="banner">
    <img src="../../images/quotes/banner-reservations.jpg" />
  </div>

  <div class="container-hotel-info">
    <div class="title-page">
      <h1>2. {{ t('quote.label.register_passeger') }}</h1>

      <div class="actions">
        <ButtonOutlineContainer
          icon="arrow-left"
          :text="t('quote.label.go_back')"
          @click="changePage('details-price')"
        />
      </div>
    </div>

    <div class="alert-time">
      <svg
        xmlns="http://www.w3.org/2000/svg"
        width="24"
        height="24"
        viewBox="0 0 24 24"
        fill="none"
      >
        <g clip-path="url(#clip0_9571_748)">
          <path
            d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z"
            stroke="#5C5AB4"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
          />
          <path
            d="M12 16V12"
            stroke="#5C5AB4"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
          />
          <path
            d="M12 8H12.01"
            stroke="#5C5AB4"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
          />
        </g>
        <defs>
          <clipPath id="clip0_9571_748">
            <rect width="24" height="24" fill="white" />
          </clipPath>
        </defs>
      </svg>
      <b>{{ t('quote.label.save_time') }}</b>
      {{ t('quote.label.fill_information_passengers') }}
    </div>

    <div class="title-page btnActions">
      <div class="actions">
        <div>
          <svg
            xmlns="http://www.w3.org/2000/svg"
            width="29"
            height="29"
            viewBox="0 0 29 29"
            fill="none"
          >
            <path
              d="M21.75 9.6665C23.752 9.6665 25.375 8.04354 25.375 6.0415C25.375 4.03947 23.752 2.4165 21.75 2.4165C19.748 2.4165 18.125 4.03947 18.125 6.0415C18.125 8.04354 19.748 9.6665 21.75 9.6665Z"
              stroke="#E0453D"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
            <path
              d="M7.25 18.125C9.25203 18.125 10.875 16.502 10.875 14.5C10.875 12.498 9.25203 10.875 7.25 10.875C5.24797 10.875 3.625 12.498 3.625 14.5C3.625 16.502 5.24797 18.125 7.25 18.125Z"
              stroke="#E0453D"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
            <path
              d="M21.75 26.5835C23.752 26.5835 25.375 24.9605 25.375 22.9585C25.375 20.9565 23.752 19.3335 21.75 19.3335C19.748 19.3335 18.125 20.9565 18.125 22.9585C18.125 24.9605 19.748 26.5835 21.75 26.5835Z"
              stroke="#E0453D"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
            <path
              d="M10.3796 16.3247L18.6326 21.1339"
              stroke="#E0453D"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
            <path
              d="M18.6205 7.86621L10.3796 12.6754"
              stroke="#E0453D"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
          </svg>
        </div>

        <div class="btn-download outline button-component" @click="downloadPassengerExcel">
          {{ t('quote.label.download_format') }}
          <svg
            xmlns="http://www.w3.org/2000/svg"
            width="25"
            height="21"
            viewBox="0 0 25 21"
            fill="none"
          >
            <g clip-path="url(#clip0_6745_40199)">
              <path
                d="M8.65381 15.3633L12.5 19.1728L16.3461 15.3633"
                stroke="#EB5757"
                stroke-width="2.3"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
              <path
                d="M12.5 10.6011V19.1725"
                stroke="#EB5757"
                stroke-width="2.3"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
              <path
                d="M21.0384 16.4011C21.8744 15.8188 22.5013 14.9879 22.8281 14.0288C23.155 13.0698 23.1648 12.0325 22.8561 11.0676C22.5475 10.1026 21.9364 9.26021 21.1116 8.66257C20.2868 8.06492 19.2912 7.74315 18.2692 7.74394H17.0577C16.7685 6.62763 16.2274 5.59084 15.4751 4.71163C14.7228 3.83243 13.779 3.13371 12.7146 2.66808C11.6502 2.20246 10.4931 1.98206 9.33029 2.02348C8.16748 2.0649 7.02931 2.36705 6.00147 2.9072C4.97362 3.44735 4.08288 4.21141 3.39631 5.14187C2.70973 6.07233 2.24522 7.14493 2.03772 8.27894C1.83023 9.41294 1.88518 10.5788 2.19842 11.6887C2.51166 12.7986 3.07503 13.8237 3.84613 14.6868"
                stroke="#EB5757"
                stroke-width="2.3"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
            </g>
            <defs>
              <clipPath id="clip0_6745_40199">
                <rect width="25" height="20" fill="white" transform="translate(0 0.125)" />
              </clipPath>
            </defs>
          </svg>
        </div>

        <div class="btn-passengers">
          <label for="file">
            {{ t('quote.label.import_passenger') }}
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="23"
              height="24"
              viewBox="0 0 23 24"
              fill="none"
            >
              <path
                d="M20.125 14.5V18.3333C20.125 18.8417 19.9231 19.3292 19.5636 19.6886C19.2042 20.0481 18.7167 20.25 18.2083 20.25H4.79167C4.28334 20.25 3.79582 20.0481 3.43638 19.6886C3.07693 19.3292 2.875 18.8417 2.875 18.3333V14.5"
                stroke="#575757"
                stroke-width="2.3"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
              <path
                d="M16.2917 7.79167L11.5 3L6.70837 7.79167"
                stroke="#575757"
                stroke-width="2.3"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
              <path
                d="M11.5 3V14.5"
                stroke="#575757"
                stroke-width="2.3"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
            </svg>
            <input
              type="file"
              id="file"
              ref="formRefInput"
              v-on:change="handleFileUpload($event)"
            />
          </label>
        </div>
      </div>
    </div>

    <div class="promotional-code">
      <div>
        {{ t('quote.label.code_reference_pre') }}
        <b>{{ t('quote.label.code_reference') }}</b>
        {{ t('quote.label.code_reference_post') }}
      </div>

      <a-input
        class="ant-input"
        v-model:value="quote.reference_code"
        :placeholder="t('quote.label.write_here')"
      />
    </div>

    <a-form ref="formRef" autocomplete="off" name="dynamic_form_nest_item" :model="quote">
      <div class="content-passengers">
        <h2>{{ t('quote.label.data_passenger') }}:</h2>

        <div class="data-passenger">
          <template v-for="(passenger, index) in quote.passengers" :key="index">
            <div class="title" @click="toggleDownPassenger(index)">
              <div>
                <template v-if="passenger.type == 'ADL'">
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="21"
                    height="21"
                    viewBox="0 0 21 21"
                    fill="none"
                  >
                    <path
                      d="M10.5 10.5C12.9165 10.5 14.875 8.5415 14.875 6.125C14.875 3.7085 12.9165 1.75 10.5 1.75C8.0835 1.75 6.125 3.7085 6.125 6.125C6.125 8.5415 8.0835 10.5 10.5 10.5ZM13.5625 11.5938H12.9917C12.2329 11.9424 11.3887 12.1406 10.5 12.1406C9.61133 12.1406 8.77051 11.9424 8.0083 11.5938H7.4375C4.90137 11.5938 2.84375 13.6514 2.84375 16.1875V17.6094C2.84375 18.5151 3.57861 19.25 4.48438 19.25H16.5156C17.4214 19.25 18.1562 18.5151 18.1562 17.6094V16.1875C18.1562 13.6514 16.0986 11.5938 13.5625 11.5938Z"
                      fill="#3D3D3D"
                    />
                  </svg>
                </template>
                <template v-if="passenger.type == 'CHD'">
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="21"
                    height="21"
                    viewBox="0 0 21 21"
                    fill="none"
                  >
                    <path
                      d="M6.30306 0.835049C5.86167 0.115216 4.84998 -0.0869837 4.19375 0.454912C2.02584 2.24641 0.646973 4.91949 0.646973 7.91205H10.6467L6.30306 0.835049ZM20.0214 4.02981H18.1465C16.7676 4.02981 15.6465 5.19044 15.6465 6.61797V9.20613H0.646973C0.646973 11.2524 1.54538 13.1046 3.00237 14.4916C1.65085 14.8516 0.646973 16.1092 0.646973 17.6177C0.646973 19.4051 2.04537 20.8529 3.77188 20.8529C5.4984 20.8529 6.89679 19.4051 6.89679 17.6177C6.89679 17.2577 6.82648 16.9221 6.72492 16.5986C7.56865 16.8372 8.46706 16.9706 9.39672 16.9706C10.3264 16.9706 11.2287 16.8372 12.0685 16.5986C11.9631 16.9221 11.8966 17.2577 11.8966 17.6177C11.8966 19.4051 13.295 20.8529 15.0216 20.8529C16.7481 20.8529 18.1465 19.4051 18.1465 17.6177C18.1465 16.1092 17.1426 14.8516 15.7911 14.4916C17.2481 13.1046 18.1465 11.2524 18.1465 9.20613V6.61797H20.0214C20.3652 6.61797 20.6464 6.3268 20.6464 5.97093V4.67685C20.6464 4.32098 20.3652 4.02981 20.0214 4.02981ZM3.77188 18.9117C3.0844 18.9117 2.52192 18.3294 2.52192 17.6177C2.52192 16.9059 3.0844 16.3236 3.77188 16.3236C4.45936 16.3236 5.02185 16.9059 5.02185 17.6177C5.02185 18.3294 4.45936 18.9117 3.77188 18.9117ZM16.2715 17.6177C16.2715 18.3294 15.709 18.9117 15.0216 18.9117C14.3341 18.9117 13.7716 18.3294 13.7716 17.6177C13.7716 16.9059 14.3341 16.3236 15.0216 16.3236C15.709 16.3236 16.2715 16.9059 16.2715 17.6177Z"
                      fill="#3D3D3D"
                    />
                  </svg>
                </template>
                {{ t('quote.label.passenger') }} {{ index + 1 }}
                {{ passenger.type }}
              </div>

              <div>
                <span class="arrow" :class="{ close: currentPassenger[index] === false }">
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="28"
                    height="28"
                    viewBox="0 0 28 28"
                    fill="none"
                  >
                    <path
                      d="M21 17.5L14 10.5L7 17.5"
                      stroke="#3D3D3D"
                      stroke-width="3"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                    />
                  </svg>
                </span>
              </div>
            </div>

            <div
              class="infoGeneral"
              :class="[{ active: currentPassenger[index] }, { inactive: !currentPassenger[index] }]"
            >
              <div class="row">
                <div class="item">
                  <label>{{ t('quote.label.name') }} <span>*</span></label>
                  <a-form-item
                    :name="['passengers', index, 'first_name']"
                    :rules="{
                      required: index == 0,
                      message: t('quote.label.rule_name'),
                    }"
                  >
                    <!-- <input class="ant-input" placeholder="Escribe aqui..." v-model="passenger.first_name"> -->
                    <a-input
                      class="ant-input"
                      autocomplete="off"
                      v-model:value="passenger.first_name"
                      :placeholder="t('quote.label.name')"
                    />
                  </a-form-item>
                </div>

                <div class="item">
                  <label>{{ t('quote.label.lastName') }} <span>*</span></label>
                  <a-form-item
                    :name="['passengers', index, 'last_name']"
                    :rules="{
                      required: index == 0,
                      message: t('quote.label.rule_lastname'),
                    }"
                  >
                    <!-- <input class="ant-input" placeholder="Escribe aqui..." v-model="passenger.last_name"> -->
                    <a-input
                      class="ant-input"
                      autocomplete="off"
                      v-model:value="passenger.last_name"
                      :placeholder="t('quote.label.lastName')"
                    />
                  </a-form-item>
                </div>
              </div>
              <div class="row">
                <div class="item">
                  <label>{{ t('quote.label.type_doc') }} </label>
                  <a-select
                    v-model:value="passenger.doctype_iso"
                    :options="docTypesFilters"
                    :field-names="{ label: 'label', value: 'code' }"
                    label-in-value
                  ></a-select>
                </div>

                <div class="item">
                  <label>{{ t('quote.label.nunber_doc') }} </label>
                  <input
                    class="ant-input"
                    autocomplete="off"
                    :placeholder="t('quote.label.write_here')"
                    v-model="passenger.document_number"
                  />
                </div>
              </div>

              <div
                class="row"
                v-if="passenger.doctype_iso && passenger.doctype_iso?.value === 'PAS'"
              >
                <div class="item">
                  <label>{{ t('quote.label.passport_expiration') }}</label>
                  <a-date-picker
                    autocomplete="off"
                    v-model:value="passenger.passport_expiration"
                    :format="dateFormat"
                    :placeholder="t('quote.label.select_date')"
                  />
                </div>

                <div class="item">
                  <div v-if="isPassportExpired(passenger)" class="passport-warning">
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      width="20"
                      height="20"
                      viewBox="0 0 24 24"
                      fill="none"
                    >
                      <path
                        d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"
                        stroke="#D97706"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                      />
                      <line
                        x1="12"
                        y1="9"
                        x2="12"
                        y2="13"
                        stroke="#D97706"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                      />
                      <line
                        x1="12"
                        y1="17"
                        x2="12.01"
                        y2="17"
                        stroke="#D97706"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                      />
                    </svg>
                    <span>{{ t('quote.label.passport_renewal_warning') }}</span>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="item">
                  <label>{{ t('quote.label.country') }} </label>
                  <a-select
                    showSearch
                    optionFilterProp="label"
                    v-model:value="passenger.country_iso"
                    :options="countries"
                    :field-names="{ label: 'label', value: 'code' }"
                    label-in-value
                    @change="(value: Country) => countryChange(value, index)"
                  ></a-select>
                </div>

                <div class="item">
                  <label>{{ t('quote.label.city') }} </label>
                  <a-select
                    showSearch
                    optionFilterProp="label"
                    v-model:value="passenger.city_ifx_iso"
                    :options="states"
                    :field-names="{ label: 'label', value: 'code' }"
                    label-in-value
                    @change="(value: State) => stateChange(value, index)"
                  ></a-select>
                </div>
              </div>
              <div class="row">
                <div class="item">
                  <label>{{ t('quote.label.gender') }} </label>
                  <a-select ref="select" v-model:value="passenger.gender">
                    <a-select-option value="M">{{ t('quote.label.male') }}</a-select-option>
                    <a-select-option value="F">{{ t('quote.label.female') }}</a-select-option>
                  </a-select>
                </div>

                <div class="item">
                  <label>{{ t('quote.label.birthdate') }} </label>
                  <a-form-item
                    :name="['passengers', index, 'birthday_selected']"
                    :rules="{
                      required: passenger.type == 'CHD',
                      message: t('quote.label.select_date'),
                    }"
                  >
                    <a-date-picker
                      autocomplete="off"
                      v-model:value="passenger.birthday_selected"
                      id="start-date"
                      :format="dateFormat"
                      @change="(value) => birthdayChange(value, index)"
                    />
                  </a-form-item>
                </div>
              </div>
              <div class="row">
                <div class="item">
                  <label
                    >{{ t('quote.label.email') }}
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      width="25"
                      height="24"
                      viewBox="0 0 25 24"
                      fill="none"
                    >
                      <g clip-path="url(#clip0_9572_2109)">
                        <path
                          d="M12.8857 22C18.4086 22 22.8857 17.5228 22.8857 12C22.8857 6.47715 18.4086 2 12.8857 2C7.36289 2 2.88574 6.47715 2.88574 12C2.88574 17.5228 7.36289 22 12.8857 22Z"
                          stroke="#FF3B3B"
                          stroke-width="2"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                        />
                        <path
                          d="M12.8857 8V12"
                          stroke="#FF3B3B"
                          stroke-width="2"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                        />
                        <path
                          d="M12.8857 16H12.8957"
                          stroke="#FF3B3B"
                          stroke-width="2"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                        />
                      </g>
                      <defs>
                        <clipPath id="clip0_9572_2109">
                          <rect
                            width="24"
                            height="24"
                            fill="white"
                            transform="translate(0.885742)"
                          />
                        </clipPath>
                      </defs>
                    </svg>
                  </label>
                  <input
                    class="ant-input"
                    :placeholder="t('quote.label.write_here')"
                    type="email"
                    v-model="passenger.email"
                  />
                </div>

                <div class="item telefono">
                  <label
                    >{{ t('quote.label.num_phone') }}
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      width="25"
                      height="24"
                      viewBox="0 0 25 24"
                      fill="none"
                    >
                      <g clip-path="url(#clip0_9572_2109)">
                        <path
                          d="M12.8857 22C18.4086 22 22.8857 17.5228 22.8857 12C22.8857 6.47715 18.4086 2 12.8857 2C7.36289 2 2.88574 6.47715 2.88574 12C2.88574 17.5228 7.36289 22 12.8857 22Z"
                          stroke="#FF3B3B"
                          stroke-width="2"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                        />
                        <path
                          d="M12.8857 8V12"
                          stroke="#FF3B3B"
                          stroke-width="2"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                        />
                        <path
                          d="M12.8857 16H12.8957"
                          stroke="#FF3B3B"
                          stroke-width="2"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                        />
                      </g>
                      <defs>
                        <clipPath id="clip0_9572_2109">
                          <rect
                            width="24"
                            height="24"
                            fill="white"
                            transform="translate(0.885742)"
                          />
                        </clipPath>
                      </defs>
                    </svg>
                  </label>
                  <div>
                    <a-select
                      showSearch
                      optionFilterProp="label"
                      v-model:value="passenger.phone_code"
                      :options="listPhoneCode"
                      :field-names="{ label: 'label', value: 'code' }"
                      label-in-value
                      @change="(value: State) => setPhoneCode(value, index)"
                    ></a-select>
                    <input class="ant-input" placeholder="000 000 000" v-model="passenger.phone" />
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="item">
                  <label>{{ t('quote.label.attach_doc') }}</label>

                  <FileSingleUpload
                    class="upload"
                    v-bind:index="index"
                    v-bind:folder="'passengers'"
                    v-bind:document="passenger.document_url"
                    @onResponseFiles="(value) => responseFilesFrom(value, index)"
                  />
                </div>
                <div class="item">
                  <label>{{ t('quote.label.arrangement') }} </label>

                  <a-select
                    optionFilterProp="label"
                    v-model:value="passenger.tiphab"
                    :options="types_room"
                    :field-names="{ label: 'label', value: 'code' }"
                    label-in-value
                  ></a-select>
                </div>
              </div>
              <div class="row">
                <div class="item aligntop">
                  <label>{{ t('quote.label.medical_restrictions') }} </label>
                  <textarea
                    name=""
                    class="ant-input"
                    :placeholder="t('quote.label.specify_medical')"
                    v-model="passenger.medical_restrictions"
                  ></textarea>
                </div>

                <div class="item aligntop">
                  <label>{{ t('quote.label.dietary_restrictions') }} </label>
                  <textarea
                    name=""
                    class="ant-input"
                    :placeholder="t('quote.label.specify_restrictions')"
                    v-model="passenger.dietary_restrictions"
                  ></textarea>
                </div>
              </div>

              <!-- <p><CheckBoxComponent @checked="selected as boolean"/> Asignar pago de huella de carbono</p> -->

              <div class="alert">
                <p>
                  <span
                    ><svg
                      xmlns="http://www.w3.org/2000/svg"
                      width="25"
                      height="24"
                      viewBox="0 0 25 24"
                      fill="none"
                    >
                      <g clip-path="url(#clip0_9572_2109)">
                        <path
                          d="M12.8857 22C18.4086 22 22.8857 17.5228 22.8857 12C22.8857 6.47715 18.4086 2 12.8857 2C7.36289 2 2.88574 6.47715 2.88574 12C2.88574 17.5228 7.36289 22 12.8857 22Z"
                          stroke="#FF3B3B"
                          stroke-width="2"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                        />
                        <path
                          d="M12.8857 8V12"
                          stroke="#FF3B3B"
                          stroke-width="2"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                        />
                        <path
                          d="M12.8857 16H12.8957"
                          stroke="#FF3B3B"
                          stroke-width="2"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                        />
                      </g>
                      <defs>
                        <clipPath id="clip0_9572_2109">
                          <rect
                            width="24"
                            height="24"
                            fill="white"
                            transform="translate(0.885742)"
                          />
                        </clipPath>
                      </defs>
                    </svg>
                    {{ t('quote.label.required_communications') }}

                    <a :href="t('quote.label.link_communications')" target="_blank">{{
                      t('quote.label.click_here')
                    }}</a>
                  </span>
                </p>
                <p>
                  <span>* {{ t('quote.label.required_field') }}</span>
                </p>
              </div>

              <!-- <div class="btns">
							
						<div class="cancelar">Cancelar</div>
						<div class="guardar">Guardar</div>

					</div> -->
            </div>
          </template>
        </div>
      </div>

      <div class="content-passengers flight">
        <h2 class="flightTitle">
          {{ t('quote.label.flight_details') }}:
          <div class="add-flight" @click="toggleModalFlight">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="24"
              height="24"
              viewBox="0 0 24 24"
              fill="none"
            >
              <path
                d="M12 5V19"
                stroke="#EB5757"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
              <path
                d="M5 12H19"
                stroke="#EB5757"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
            </svg>
            {{ t('quote.label.add_flights') }}
          </div>
        </h2>

        <!-- {{  dataFlights  }} -->
        <div class="data-passenger">
          <template v-for="flight in dataFlights" :key="flight.index_header">
            <div class="title" @click="toggleDownFlights(flight.index_header)">
              <div>
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  width="30"
                  height="31"
                  viewBox="0 0 30 31"
                  fill="none"
                >
                  <path
                    d="M26.875 23H3.125C2.77969 23 2.5 23.2797 2.5 23.625V24.875C2.5 25.2203 2.77969 25.5 3.125 25.5H26.875C27.2203 25.5 27.5 25.2203 27.5 24.875V23.625C27.5 23.2797 27.2203 23 26.875 23ZM4.25039 13.5336L7.7168 16.6586C8.00122 16.9155 8.34203 17.1018 8.71172 17.2028L19.9461 20.2633C20.9805 20.545 22.0773 20.6039 23.1102 20.3164C24.2691 19.9934 24.807 19.4879 24.9559 18.9215C25.1055 18.3551 24.8883 17.6438 24.043 16.7746C23.2898 16.0004 22.3105 15.4938 21.2762 15.2121L17.4672 14.1746L13.5469 6.6805C13.4879 6.45355 13.3145 6.2762 13.0914 6.21526L10.5488 5.52269C10.1363 5.41019 9.73242 5.7305 9.73984 6.16487L11.6117 12.5793L7.61953 11.4918L6.5418 8.84026C6.46641 8.64925 6.30703 8.5055 6.11133 8.45237L4.55938 8.02933C4.15547 7.91917 3.75742 8.22464 3.75 8.64964L3.75898 12.6254C3.76641 12.9735 3.99453 13.3028 4.25039 13.5336Z"
                    fill="#212529"
                  />
                </svg>
                {{ t('quote.label.flight') }}
                {{ flight.date.format('DD/MM/YYYY') }}
                <!-- <span>{{ flight.passengers.length }} PAX</span> -->
              </div>

              <div>
                <span class="arrow" :class="{ close: currentFlights[0] === false }">
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="28"
                    height="28"
                    viewBox="0 0 28 28"
                    fill="none"
                  >
                    <path
                      d="M21 17.5L14 10.5L7 17.5"
                      stroke="#3D3D3D"
                      stroke-width="3"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                    />
                  </svg>
                </span>
              </div>
            </div>

            <div
              class="infoGeneral"
              :class="[
                { active: currentFlights[flight.index_header] },
                { inactive: !currentFlights[flight.index_header] },
              ]"
            >
              <div class="content_add_item_flight">
                <a-form-item
                  :name="['flights', flight.items[0].index, 'code_flight']"
                  :rules="{
                    required: true,
                    message: t('quote.label.flight_type'),
                  }"
                >
                  <a-radio-group
                    v-model:value="quote.flights[flight.items[0].index].code_flight"
                    @change="(value) => updateCodeFlight(value, flight.index_header)"
                  >
                    <a-radio value="AEIFLT">{{ t('quote.label.domestic') }}</a-radio>
                    <a-radio value="AECFLT">{{ t('quote.label.international_flight') }}</a-radio>
                  </a-radio-group>
                </a-form-item>

                <div class="add_item_flight">
                  <div @click="add_item(flight.index_header)">
                    <svg
                      width="31"
                      height="32"
                      viewBox="0 0 31 32"
                      fill="none"
                      xmlns="http://www.w3.org/2000/svg"
                    >
                      <path
                        d="M24.4444 4.5H6.55556C5.14416 4.5 4 5.64416 4 7.05556V24.9444C4 26.3558 5.14416 27.5 6.55556 27.5H24.4444C25.8558 27.5 27 26.3558 27 24.9444V7.05556C27 5.64416 25.8558 4.5 24.4444 4.5Z"
                        stroke="#C4C4C4"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                      />
                      <path
                        d="M15.5 10.8887V21.1109"
                        stroke="#C4C4C4"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                      />
                      <path
                        d="M10.3889 16H20.6111"
                        stroke="#C4C4C4"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                      />
                    </svg>
                    {{ t('quote.label.add_flight_day') }}
                    {{ flight.date.format('DD/MM') }}
                  </div>

                  <!--<input type="button" :value="t('quote.label.add_item_flight')" @click="add_item(flight.index_header)">-->
                </div>
              </div>
              <!-- quote.flights -->
              <div class="add_items" v-for="(item, p) in flight.items" :key="item.index">
                <div class="bg_add_items">
                  <div class="row flight">
                    <div class="item">
                      <label>{{ t('quote.label.airline') }}</label>
                      <a-form-item
                        :name="['flights', item.index, 'airline']"
                        :rules="{
                          required: true,
                          message: t('quote.label.select_airline'),
                        }"
                      >
                        <a-select
                          v-model:value="quote.flights[item.index].airline"
                          showSearch
                          label-in-value
                          :placeholder="t('quote.label.select_users')"
                          style="width: 100%"
                          :filter-option="false"
                          :not-found-content="select_search_airline.fetching ? undefined : null"
                          :options="select_search_airline.data"
                          @search="serach_airline"
                        >
                          <template v-if="select_search_airline.fetching" #notFoundContent>
                            <a-spin size="small" />
                          </template>
                        </a-select>
                      </a-form-item>
                    </div>

                    <div class="item par">
                      <label>{{ t('quote.label.num_flight') }}</label>
                      <a-form-item
                        :name="['flights', item.index, 'number_flight']"
                        :rules="{
                          required: true,
                          message: t('quote.label.select_num_flight'),
                        }"
                      >
                        <a-input
                          class="ant-input"
                          v-model:value="quote.flights[item.index].number_flight"
                          :placeholder="t('quote.label.num_flight')"
                        />
                      </a-form-item>
                    </div>

                    <div class="item">
                      <label>{{ t('quote.label.passengers') }}</label>
                      <a-form-item
                        :name="['flights', item.index, 'passengers']"
                        :rules="{
                          required: true,
                          message: t('quote.label.select_passengers'),
                        }"
                      >
                        <a-select
                          v-model:value="quote.flights[item.index].passengers"
                          mode="multiple"
                          style="width: 100%"
                          :max-tag-count="1"
                          :options="flight.passenger_options"
                          @focus="(value) => passengerChange(value, flight.index_header, p)"
                        >
                        </a-select>
                      </a-form-item>
                    </div>
                  </div>

                  <div class="row flight">
                    <div class="item">
                      <label>{{ t('quote.label.origin') }} </label>
                      <a-form-item
                        :name="['flights', item.index, 'origin']"
                        :rules="{
                          required: true,
                          message: t('quote.label.select_origin'),
                        }"
                      >
                        <a-select
                          v-model:value="quote.flights[item.index].origin"
                          showSearch
                          label-in-value
                          :placeholder="t('quote.label.select_users')"
                          style="width: 100%"
                          :filter-option="false"
                          :not-found-content="select_search_destiny.fetching ? undefined : null"
                          :options="select_search_destiny.data"
                          @search="serach_destiny"
                        >
                          <template v-if="select_search_destiny.fetching" #notFoundContent>
                            <a-spin size="small" />
                          </template>
                        </a-select>
                      </a-form-item>
                    </div>

                    <div class="item par">
                      <label>{{ t('quote.label.destination') }}</label>
                      <a-form-item
                        :name="['flights', item.index, 'destiny']"
                        :rules="{
                          required: true,
                          message: t('quote.label.select_destination'),
                        }"
                      >
                        <a-select
                          v-model:value="quote.flights[item.index].destiny"
                          showSearch
                          label-in-value
                          :placeholder="t('quote.label.select_users')"
                          style="width: 100%"
                          :filter-option="false"
                          :not-found-content="select_search_destiny.fetching ? undefined : null"
                          :options="select_search_destiny.data"
                          @search="serach_destiny"
                        >
                          <template v-if="select_search_destiny.fetching" #notFoundContent>
                            <a-spin size="small" />
                          </template>
                        </a-select>
                      </a-form-item>
                    </div>

                    <div class="item">
                      <label>{{ t('quote.label.schedules') }}</label>
                      <a-form-item
                        :name="['flights', item.index, 'hour_range']"
                        :rules="{
                          required: true,
                          message: t('quote.label.select_schedules'),
                        }"
                      >
                        <a-time-range-picker
                          format="HH:mm"
                          v-model:value="quote.flights[item.index].hour_range"
                          @change="(value) => hoursChange(value, item.index)"
                          :placeholder="[t('quote.label.hour_start'), t('quote.label.hour_end')]"
                        />
                      </a-form-item>
                    </div>
                  </div>

                  <div class="row flight">
                    <div class="item">
                      <label
                        >PNR
                        <svg
                          xmlns="http://www.w3.org/2000/svg"
                          width="16"
                          height="16"
                          viewBox="0 0 16 16"
                          fill="none"
                        >
                          <g clip-path="url(#clip0_9572_1014)">
                            <path
                              d="M7.99992 14.6668C11.6818 14.6668 14.6666 11.6821 14.6666 8.00016C14.6666 4.31826 11.6818 1.3335 7.99992 1.3335C4.31802 1.3335 1.33325 4.31826 1.33325 8.00016C1.33325 11.6821 4.31802 14.6668 7.99992 14.6668Z"
                              stroke="black"
                              stroke-width="1.5"
                              stroke-linecap="round"
                              stroke-linejoin="round"
                            />
                            <path
                              d="M6.06006 5.99989C6.21679 5.55434 6.52616 5.17863 6.93336 4.93931C7.34056 4.7 7.81932 4.61252 8.28484 4.69237C8.75036 4.77222 9.1726 5.01424 9.47678 5.37558C9.78095 5.73691 9.94743 6.19424 9.94672 6.66656C9.94672 7.99989 7.94673 8.66656 7.94673 8.66656"
                              stroke="black"
                              stroke-width="1.5"
                              stroke-linecap="round"
                              stroke-linejoin="round"
                            />
                            <path
                              d="M8 11.3335H8.00667"
                              stroke="black"
                              stroke-width="1.5"
                              stroke-linecap="round"
                              stroke-linejoin="round"
                            />
                          </g>
                          <defs>
                            <clipPath id="clip0_9572_1014">
                              <rect width="16" height="16" fill="white" />
                            </clipPath>
                          </defs>
                        </svg>
                      </label>
                      <input
                        class="ant-input pnr"
                        :placeholder="t('quote.label.write_here')"
                        v-model="quote.flights[item.index].pnr"
                      />
                    </div>
                    <div class="item"></div>
                    <div class="item"></div>
                  </div>
                </div>

                <div class="btns">
                  <div class="guardar" @click="delete_flight(item.index)">
                    {{ t('quote.label.delete') }}
                  </div>
                </div>
              </div>
            </div>
          </template>
        </div>
      </div>

      <div class="content-extra-boton">
        <p>
          <svg
            xmlns="http://www.w3.org/2000/svg"
            width="20"
            height="20"
            viewBox="0 0 20 20"
            fill="none"
          >
            <path
              d="M13.3333 2.5L17.5 6.66667L6.66667 17.5H2.5V13.3333L13.3333 2.5Z"
              stroke="#EB5757"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
          </svg>
          {{ t('quote.label.account_executive') }}:
        </p>

        <textarea name="" :placeholder="t('quote.label.escreva_nota')"></textarea>

        <div class="btns">
          <div class="guardar" @click="onSubmit" v-if="!quote.file.file_code">
            {{ t('quote.label.reserve') }}
          </div>
        </div>
      </div>
    </a-form>
  </div>

  <ModalComponent
    :modal-active="state.showModalIflight"
    class="modal-add-flight"
    @close="toggleModalFlight"
  >
    <template #body>
      <div class="data-passenger">
        <div class="content-passengers">
          <h2>
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="31"
              height="31"
              viewBox="0 0 31 31"
              fill="none"
            >
              <path
                d="M12.9167 3.875H3.875V12.9167H12.9167V3.875Z"
                stroke="#EB5757"
                stroke-width="3"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
              <path
                d="M27.1249 3.875H18.0833V12.9167H27.1249V3.875Z"
                stroke="#EB5757"
                stroke-width="3"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
              <path
                d="M27.1249 18.0833H18.0833V27.1249H27.1249V18.0833Z"
                stroke="#EB5757"
                stroke-width="3"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
              <path
                d="M12.9167 18.0833H3.875V27.1249H12.9167V18.0833Z"
                stroke="#EB5757"
                stroke-width="3"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
            </svg>
            {{ t('quote.label.add_flight') }}
          </h2>

          <div class="infoGeneral">
            <a-form ref="formRefFlight" name="form_fligtht" :model="form_flights">
              <div class="row">
                <div class="item">
                  <label>{{ t('quote.label.origin') }}</label>
                  <a-form-item
                    :name="['flight', 'origin']"
                    :rules="[
                      {
                        required: true,
                        message: t('quote.label.select_origin'),
                      },
                    ]"
                  >
                    <a-select
                      v-model:value="form_flights.flight.origin"
                      showSearch
                      label-in-value
                      :placeholder="t('quote.label.select_origin')"
                      style="width: 100%"
                      :filter-option="false"
                      :not-found-content="select_search_destiny.fetching ? undefined : null"
                      :options="select_search_destiny.data"
                      @search="serach_destiny"
                    >
                      <template v-if="select_search_destiny.fetching" #notFoundContent>
                        <a-spin size="small" />
                      </template>
                    </a-select>
                  </a-form-item>
                </div>

                <div class="item">
                  <label>{{ t('quote.label.destination') }}</label>
                  <a-form-item
                    :name="['flight', 'destiny']"
                    :rules="[
                      {
                        required: true,
                        message: t('quote.label.select_destination'),
                      },
                    ]"
                  >
                    <a-select
                      v-model:value="form_flights.flight.destiny"
                      showSearch
                      label-in-value
                      :placeholder="t('quote.label.select_destination')"
                      style="width: 100%"
                      :filter-option="false"
                      :not-found-content="select_search_destiny.fetching ? undefined : null"
                      :options="select_search_destiny.data"
                      @search="serach_destiny"
                    >
                      <template v-if="select_search_destiny.fetching" #notFoundContent>
                        <a-spin size="small" />
                      </template>
                    </a-select>
                  </a-form-item>
                </div>
              </div>

              <div class="row">
                <div class="item">
                  <label>{{ t('quote.label.date') }}</label>
                  <a-form-item
                    :name="['flight', 'date_flight']"
                    :rules="[{ required: true, message: t('quote.label.select_date') }]"
                  >
                    <a-date-picker
                      v-model:value="form_flights.flight.date_flight"
                      id="date_flight"
                      :format="dateFormat"
                      @change="changeDate"
                    />
                  </a-form-item>
                </div>

                <div class="item">
                  <label>{{ t('quote.label.passengers') }}</label>
                  <a-form-item
                    :name="['flight', 'passengers']"
                    :rules="[
                      {
                        required: true,
                        message: t('quote.label.select_passengers'),
                      },
                    ]"
                  >
                    <a-select
                      v-model:value="form_flights.flight.passengers"
                      mode="multiple"
                      style="width: 100%"
                      :max-tag-count="1"
                      :options="passenger_options"
                      :disabled="form_flights.flight.disabled"
                    >
                    </a-select>
                  </a-form-item>
                </div>
              </div>
            </a-form>
          </div>
        </div>
      </div>
    </template>
    <template #footer>
      <div class="footer">
        <button :disabled="false" class="cancel" @click="toggleModalFlight">
          {{ t('quote.label.cancel') }}
        </button>
        <button :disabled="false" class="ok" @click="add_flight">
          {{ t('quote.label.add_flight') }}
        </button>
      </div>
    </template>
  </ModalComponent>

  <div class="footerHotel">
    <div class="container-hotel-info">
      <div>{{ t('quote.label.tell_something') }}</div>
      <div class="btn">{{ t('quote.label.write_us') }}</div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
  .add_items {
    margin-bottom: 50px;

    &:last-child {
      //margin-bottom: 0;
    }

    & > div.bg_add_items {
      background: #fafafa;
      padding: 40px 40px 10px;
      margin-bottom: 20px;
    }

    .ant-form-item {
      width: 90% !important;
    }

    .row .item {
      input {
        width: 90% !important;
      }

      &.par {
        .ant-form-item {
          width: 100% !important;
        }

        input {
          width: 100% !important;
        }
      }
    }
  }

  .content_add_item_flight {
    display: flex;
    margin: 0 0 50px 0;
    gap: 15px;

    .ant-row {
      width: auto;
      margin: 0;
    }
  }

  .add_item_flight {
    display: flex;
    align-items: center;
    color: #c4c4c4;
    font-size: 16px;

    & > div {
      display: flex;
      gap: 5px;
      align-items: center;
      cursor: pointer;
    }

    &:hover {
      color: #eb5757;

      svg path {
        stroke: #eb5757;
      }
    }
  }

  .upload-list-inline {
    width: 100%;

    :deep(.ant-upload.ant-upload-select) {
      display: block;

      .ant-btn {
        width: 100%;
        background: none;
        border: 1px solid #eb5757;
        font-size: 14px;
        color: #eb5757;

        .anticon {
          vertical-align: middle;

          svg {
            width: 25px;
            height: 22px;
          }
        }

        &:hover {
          background: rgba(255, 225, 225, 0.4);
        }
      }
    }
  }

  .ant-row,
  .ant-picker {
    width: 100%;
  }

  .active {
    display: block !important;
  }

  .inactive {
    display: none !important;
  }

  .content-extra-boton {
    border-top: 2px solid #c4c4c4;
    margin-top: 30px;
    padding: 30px 0 100px;

    p {
      color: #eb5757;
      /* Tag/Body 10, 12, 14/Body R XSmall SemiBold */
      font-family: Montserrat;
      font-size: 12px;
      font-style: normal;
      font-weight: 600;
      line-height: 19px; /* 158.333% */
      letter-spacing: 0.18px;

      svg {
        display: inline-block;
        margin-right: 2px;
        vertical-align: top;
      }
    }

    textarea {
      border: 1px solid #c4c4c4;
      width: 100%;
      font-size: 16px;
      border-radius: 4px;
      padding: 8px 18px;
    }

    .btns {
      margin-top: 30px;

      .guardar {
        width: 224px;
        justify-content: center;
      }
    }
  }

  .modal-add-flight {
    :deep(.modal-inner) {
      max-width: 750px;
    }

    .content-passengers {
      padding: 20px;
      border: 0;

      h2 {
        svg {
          display: inline-block;
          vertical-align: top;
        }
      }

      .infoGeneral {
        margin-bottom: 20px;

        .row {
          .item {
            flex-direction: column;
            gap: 8px;
          }
        }
      }
    }
  }

  .contentAddFlight {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 35px;
    align-self: stretch;
  }

  .add-flight {
    border-radius: 6px;
    border: 1px solid #eb5757;
    color: #eb5757;
    /* Text 16, 18, 24/CTA - Text SemiBold */
    font-family: Montserrat;
    font-size: 16px;
    font-style: normal;
    font-weight: 600;
    line-height: 23px; /* 143.75% */
    letter-spacing: -0.24px;
    gap: 8px;
    display: flex;
    padding: 14px 16px;
    cursor: pointer;
    justify-content: center;
    align-items: center;
    align-self: stretch;

    &:hover {
      background: rgba(255, 225, 225, 0.4);
    }
  }

  .btns {
    display: flex;
    justify-content: flex-end;
    align-items: flex-end;
    gap: 36px;
    align-self: stretch;

    .cancelar {
      display: flex;
      padding: 14px 32px;
      align-items: center;
      border-radius: 6px;
      background: #fafafa;
      cursor: pointer;
    }

    .guardar {
      display: flex;
      padding: 14px 32px;
      align-items: center;
      border-radius: 6px;
      color: #fff;
      cursor: pointer;
      background: #eb5757;
    }
  }

  .data-passenger {
    width: 100%;

    .title {
      cursor: pointer;
      background: #fafafa;
      display: flex;
      height: 69px;
      padding: 12px 20px 12px 20px;
      justify-content: space-between;
      align-items: center;
      align-self: stretch;
      border-radius: 5px;
      margin-bottom: 15px;

      svg {
        display: inline-block;
        vertical-align: middle;
        margin-right: 4px;
      }

      span {
        color: #575757;
        font-size: 12px;
        margin-left: 25px;
      }

      &:hover {
        background: #e9e9e9;
      }

      &:last-child {
        margin-bottom: 0;
      }
    }

    .infoGeneral {
      display: flex;
      padding: 26px 45px 20px;
      flex-direction: column;
      align-items: flex-start;
      gap: 28px;
      align-self: stretch;

      .alert {
        flex-direction: column;
        align-items: baseline;
        display: flex;
        margin-left: -47px;
        margin-right: -50px;
        width: 100%;

        p {
          color: #eb5757;
          font-size: 14px;

          svg {
            display: inline-block;
            vertical-align: middle;
          }
        }
      }

      p {
        display: flex;
        gap: 5px;
      }

      .row {
        display: flex;
        align-items: flex-start;
        align-self: stretch;
        gap: 110px;
        justify-content: space-between;
        margin-bottom: 24px;
        width: 100%;

        .ant-form-item {
          margin-bottom: 0;
          width: 100%;
        }

        &.flight {
          gap: 25px;

          .item {
            gap: 5px;

            &.par {
              .ant-form-item {
                width: 90%;
              }

              input {
                width: 100%;
              }
            }

            label {
              font-size: 14px;
              //	    				width: 220px;
              width: 42%;
            }
          }
        }

        .item {
          display: flex;
          align-items: center;
          justify-content: space-between;
          width: 100%;
          gap: 25px;
          /*gap: 105px;
				flex: 1 0 0;*/

          &.telefono {
            div {
              display: flex;
              gap: 12px;
              align-items: flex-start;
              width: 100%;
            }
          }

          label {
            display: flex;
            align-items: center;
            gap: 5px;
            width: 230px;
            font-size: 12px;

            span {
              color: #eb5757;
            }
          }

          input,
          .upload,
          textarea {
            font-size: 14px;
            height: 45px;
            width: 100%;
            border: 1px solid #d9d9d9;
            padding: 5px 10px;
            border-radius: 5px;
          }

          .upload {
            border: 0;
            padding: 0;
            height: auto;
          }

          :deep(.upload label) {
            border-color: #eb5757;
            width: 100%;
            height: 45px;
            padding: 0;
            text-align: center;
            max-width: 100%;
            border-radius: 5px;
            color: #eb5757;
          }

          :deep(.upload label span) {
            font-size: 14px;
            line-height: 40px;
            font-family: 'Montserrat', sans-serif;
          }

          :deep(.upload input[type='button']) {
            background: #eb5757;
            border-color: #eb5757;
            color: #fff;
            border-radius: 5px;
            padding: 5px 10px;
            margin-top: 10px;
            cursor: pointer;
          }

          .ant-select {
            width: 100%;
            height: 45px;
            font-size: 14px;

            :deep(.ant-select-selector) {
              height: 45px !important;
              font-size: 14px;
            }
          }

          &.aligntop {
            align-items: flex-start;
          }

          textarea.ant-input {
            height: 100px;
            min-height: 100px;
            font-size: 14px;
          }
        }
      }
    }
  }

  .arrow {
    cursor: pointer;
    align-items: center;
    display: flex;

    &.close {
      transform: rotate(180deg);
    }
  }

  .footerHotel {
    background:
      url(/images/limaFooter.jpg),
      lightgray 50% / cover no-repeat;
    background-size: cover;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-shrink: 0;
    color: #fff;
    /* Body P Large */
    font-family: Montserrat;
    font-size: 28px;
    font-style: normal;
    font-weight: 400;
    line-height: 40px;
    letter-spacing: -0.42px;
    height: 500px;

    .container-hotel-info {
      display: flex;
      align-items: center;
      gap: 34px 20px;
      flex-wrap: wrap;
    }

    div {
      align-items: center;
      padding: 80px 0 80px 0;
      justify-content: space-between;

      & > div:first-child {
      }
    }

    .btn {
      padding: 0;
      align-items: center;
      font-size: 16px;
      border-radius: 6px;
      background: #eb5757;
      text-align: center;
      cursor: pointer;
      width: 192px;
      height: 52px;
    }
  }

  .content-passengers {
    display: flex;
    padding: 40px 32px;
    flex-direction: column;
    align-items: flex-start;
    gap: 30px;
    align-self: stretch;
    border-radius: 6px;
    border: 1px solid #e9e9e9;
    margin-bottom: 30px;

    &.flight {
      padding: 40px 32px 0;
    }

    h2 {
      color: #3d3d3d;
      font-family: Montserrat;
      font-size: 20px;
      font-style: normal;
      font-weight: 700;
      line-height: 28px;
      letter-spacing: -0.2px;

      &.flightTitle {
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
      }
    }
  }

  .container-hotel-info {
    width: 80vw;
    margin: 0 auto;
  }

  .promotional-code {
    border-bottom: 2px solid #c4c4c4;
    padding: 0 0 46px 0;
    display: flex;
    align-items: center;
    gap: 15px;
    align-self: stretch;
    justify-content: space-between;
    margin: 48px 0 30px;

    input {
      max-width: 66%;
      height: 47px;
    }
  }

  .title-page {
    display: flex;
    justify-content: space-between;
    align-items: center;
    align-self: stretch;
    padding: 120px 0 5px;

    &.btnActions {
      padding: 30px 0 5px;
    }

    h1 {
      color: #eb5757;
      font-family: Montserrat;
      font-size: 36px !important;
      font-style: normal;
      font-weight: 700;
      line-height: 44px;
      letter-spacing: -0.36px;
    }

    .actions {
      display: flex;
      gap: 15px;
      align-items: center;

      div {
        display: flex;
        align-items: center;
      }
    }
  }

  .btn-download {
    display: flex;
    height: 45px;
    padding: 12px 24px;
    align-items: center;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    line-height: 17px; /* 170% */
    letter-spacing: 0.15px;
    flex-shrink: 0;
    gap: 8px;

    &:hover {
      background: rgba(255, 225, 225, 0.4);
    }
  }

  .btn-passengers {
    background: #fafafa;
    padding: 14px 32px;
    font-family: Montserrat;
    font-size: 14px;
    font-style: normal;
    font-weight: 600;
    cursor: pointer;
    line-height: 17px; /* 170% */
    letter-spacing: 0.15px;
    gap: 8px;
    color: #575757;
    position: relative;
    border-radius: 6px;

    &:hover {
      background: #e9e9e9;
    }

    #file {
      visibility: hidden;
      position: absolute;
      left: 0;
      top: 0;
      right: 0;
      bottom: 0;
    }

    label {
      display: flex;
      align-items: center;
      gap: 10px;
      cursor: pointer;
    }
  }

  .alert-time {
    border-radius: 4px;
    border: 0.5px solid #2e2b9e;
    background: #ededff;
    display: flex;
    padding: 16px;
    align-items: flex-start;
    gap: 10px;
    align-self: stretch;
    color: #2e2b9e;
    font-family: Montserrat;
    font-size: 16px;
    font-style: normal;
    font-weight: 400;
    line-height: 23px; /* 143.75% */
    letter-spacing: -0.24px;

    b {
      font-weight: 600;
    }
  }

  :deep(.ant-picker) {
    min-height: 45px;
  }

  .passport-warning {
    display: flex;
    align-items: center;
    gap: 10px;
    background-color: #fff7ed;
    border: 1px solid #f59e0b;
    border-radius: 6px;
    padding: 10px 14px;
    color: #92400e;
    font-size: 13px;
    font-weight: 500;
    width: 100%;

    svg {
      flex-shrink: 0;
    }
  }

  @media only screen and (max-width: 1800px) {
    .container-hotel-info {
      width: 85vw;
    }
  }

  @media only screen and (max-width: 1600px) {
    .container-hotel-info {
      width: 90vw;
    }
  }

  @media only screen and (max-width: 1450px) {
    .container-hotel-info {
      width: 95vw;
    }
  }
</style>
