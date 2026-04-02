<template>
  <div class="bg-pink-stick p-5 my-5">
    <a-row class="subtitle" type="flex" justify="space-between" align="middle">
      <a-col>
        <a-row type="flex" justify="start" align="middle" style="gap: 5px">
          <a-col>
            <span>
              <font-awesome-icon
                :icon="
                  filesStore.showServiceIcon(
                    filesStore.getFileItinerary.service_category_id,
                    filesStore.getFileItinerary.service_sub_category_id,
                    filesStore.getFileItinerary.service_type_id
                  )
                "
                style="font-size: 1.2rem"
              />
            </span>
          </a-col>
          <a-col flex="auto">
            <a-tooltip>
              <template #title v-if="filesStore.getFileItinerary.name.length > 70">
                {{ filesStore.getFileItinerary.name }}
              </template>
              <span class="text-700" style="font-size: 18px">
                {{ filesStore.getFileItinerary.name }}
              </span>
            </a-tooltip>
          </a-col>
          <!-- a-col>
            <span class="mx-2">
              <a-tag color="#c63838">
                {{ filesStore.getFileItinerary.category }}
              </a-tag>
            </span>
          </a-col -->
        </a-row>
      </a-col>
      <a-col>
        <a-row type="flex" justify="end" align="middle" style="gap: 5px">
          <a-col>
            <font-awesome-icon :icon="['far', 'calendar']" />
          </a-col>
          <a-col>
            <b class="text-700" style="font-size: 18px">{{
              formatDate(filesStore.getFileItinerary.date_in, 'DD/MM/YYYY')
            }}</b>
          </a-col>
        </a-row>
      </a-col>
    </a-row>
    <a-row type="flex" align="middle" justify="space-between" class="mb-2 mt-3">
      <a-col class="d-flex" style="gap: 5px">
        <b class="text-capitalize">{{ t('global.label.passengers') }}:</b>
        <span style="font-size: 14px" class="text-700">
          <i class="bi bi-person-fill text-700" style="font-size: 17px"></i>
          {{ filesStore.getFileItinerary.adults }}
        </span>
        <span style="font-size: 14px" class="text-700">
          <i class="bi bi-person-arms-up text-700" style="font-size: 17px"></i>
          {{ filesStore.getFileItinerary.children }}
        </span>
      </a-col>
      <a-col class="d-flex cursor-pointer" v-on:click="showInformation()" style="gap: 5px">
        <b
          class="d-flex text-danger"
          style="font-size: 14px; border-bottom: 1px solid; padding-bottom: 1px; gap: 4px"
        >
          <span class="text-capitalize">{{ t('global.label.more') }}</span>
          <span class="text-lowercase"
            >{{ t('global.label.information') }} {{ t('global.label.of') }}
            {{ t('global.label.service') }}</span
          ></b
        >
      </a-col>
      <a-col class="d-flex" style="gap: 5px">
        <b style="font-size: 16px" class="text-700">{{ t('global.label.sale') }}:</b>
        <b class="text-danger" style="font-size: 16px">
          <!-- $ {{ formatNumber({ number: filesStore.getFileItinerary.penality, digits: 2 }) }} -->
          $ {{ formatNumber({ number: filesStore.getFileItinerary.total_amount, digits: 2 }) }}
        </b>
      </a-col>
      <a-col>
        <a-row type="flex" align="middle" justify="start" style="gap: 5px">
          <template v-if="filesStore.getFileItinerary.penality > 0">
            <a-col>
              <svg
                width="32"
                height="32"
                viewBox="0 0 32 32"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M13.7191 5.34315L2.42572 23.6073C2.19288 23.9979 2.06967 24.4408 2.06837 24.8919C2.06707 25.343 2.18771 25.7865 2.41829 26.1784C2.64887 26.5703 2.98135 26.8969 3.38266 27.1256C3.78397 27.3544 4.24012 27.4774 4.70572 27.4823H27.2924C27.758 27.4774 28.2141 27.3544 28.6154 27.1256C29.0167 26.8969 29.3492 26.5703 29.5798 26.1784C29.8104 25.7865 29.931 25.343 29.9297 24.8919C29.9284 24.4408 29.8052 23.9979 29.5724 23.6073L18.2791 5.34315C18.0414 4.96354 17.7067 4.64968 17.3073 4.43186C16.9079 4.21404 16.4574 4.09961 15.9991 4.09961C15.5407 4.09961 15.0902 4.21404 14.6908 4.43186C14.2914 4.64968 13.9567 4.96354 13.7191 5.34315V5.34315Z"
                  stroke="#FFCC00"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
                <path
                  d="M16 22.3164H16.0133"
                  stroke="#FFCC00"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
                <path
                  d="M16 11.9824V17.1491"
                  stroke="#FFCC00"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
              </svg>
            </a-col>
            <a-col>
              <b style="font-size: 12px" class="text-600 text-dark-warning"
                >Penalidad por cancelación:</b
              >
            </a-col>
            <a-col>
              <b class="text-warning" style="font-size: 24px"
                >$0.00
                <!-- {{
                  formatNumber({
                    number:
                      filesStore.getFileItinerary.penality * filesStore.getFileItinerary.adults,
                    digits: 2,
                  })
                }} -->
              </b>
            </a-col>
          </template>
          <template v-else>
            <a-col>
              <font-awesome-icon :icon="['fas', 'circle-check']" size="lg" class="text-success" />
            </a-col>
            <template v-if="filesStore.getFileItinerary.penality > 0">
              <a-col>
                <b style="font-size: 12px" class="text-600 text-success"
                  >Penalidad por cancelación:</b
                >
              </a-col>
              <a-col>
                <b class="text-success"
                  >$
                  {{
                    formatNumber({
                      number: filesStore.getFileItinerary.penality,
                      digits: 2,
                    })
                  }}</b
                >
              </a-col>
            </template>
            <template v-else>
              <a-col>
                <b style="font-size: 11px" class="text-600 text-success text-uppercase">{{
                  t('files.label.no_penalty')
                }}</b>
              </a-col>
            </template>
          </template>
        </a-row>
      </a-col>
    </a-row>
  </div>
  <div class="bg-gray p-5 my-5" v-if="filesStore.getFileItinerary.penality > 0">
    <a-row type="flex" justify="space-between" class="my-3">
      <a-col class="mx-3">
        <a-row type="flex" justify="space-between" align="middle" style="gap: 4px">
          <a-col class="text-info">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 512 512"
              width="24"
              height="24"
              class="d-flex"
            >
              <path
                d="M256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM216 336h24V272H216c-13.3 0-24-10.7-24-24s10.7-24 24-24h48c13.3 0 24 10.7 24 24v88h8c13.3 0 24 10.7 24 24s-10.7 24-24 24H216c-13.3 0-24-10.7-24-24s10.7-24 24-24zm40-208a32 32 0 1 1 0 64 32 32 0 1 1 0-64z"
              />
            </svg>
          </a-col>
          <a-col flex="auto" class="text-info">
            <span class="cursor-pointer" v-on:click="showPoliciesCancellation()">{{
              t('global.label.cancellation_policies')
            }}</span>
          </a-col>
          <a-col class="text-warning">
            <span>{{ t('global.label.total_cost_price_with_penalty') }}:</span>
          </a-col>
        </a-row>

        <div class="p-4 border bg-white my-4">
          <a-form layout="vertical">
            <a-form-item label="¿Quién asume la penalidad?">
              <a-select
                size="large"
                placeholder="Selecciona"
                :default-active-first-option="false"
                :not-found-content="null"
                :options="filesStore.getAsumedBy"
                v-model:value="asumed_by"
                showSearch
                :filter-option="false"
                :disabled="filesStore.isLoading || filesStore.isLoadingAsync"
              >
              </a-select>
            </a-form-item>

            <a-form-item
              label="Seleccione la especialista que asume la penalidad"
              v-if="asumed_by == 13"
            >
              <a-select
                size="large"
                placeholder="Selecciona"
                :default-active-first-option="false"
                :not-found-content="null"
                :options="executivesStore.getExecutives"
                v-model:value="executive_id"
                :field-names="{ label: 'name', value: 'id' }"
                showSearch
                :filter-option="false"
                @search="searchExecutives"
              >
              </a-select>
            </a-form-item>

            <a-form-item label="Seleccione el file que asume la penalidad" v-if="asumed_by == 12">
              <a-select
                size="large"
                placeholder="Selecciona"
                :default-active-first-option="false"
                :not-found-content="null"
                :options="filesStore.getFiles"
                v-model:value="file_id"
                showSearch
                :field-names="{ label: 'description', value: 'id' }"
                :filter-option="false"
                @search="searchFiles"
              >
              </a-select>
            </a-form-item>

            <a-form-item label="Motivo">
              <a-textarea :rows="4" placeholder="Ingrese un motivo" v-model:value="motive" />
            </a-form-item>
          </a-form>
        </div>
      </a-col>
      <a-col flex="auto" class="mx-3">
        <a-row type="flex" justify="start" align="middle">
          <a-col class="text-warning">
            <svg
              class="feather feather-check-circle"
              xmlns="http://www.w3.org/2000/svg"
              width="24"
              height="24"
              fill="none"
              stroke="currentColor"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              viewBox="0 0 24 24"
            >
              <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
              <path d="M22 4 12 14.01l-3-3" />
            </svg>
          </a-col>
          <a-col class="text-warning ms-2">
            <h5 class="m-0 text-700" style="font-size: 24px !important">
              $
              {{
                formatNumber({
                  number: filesStore.getFileItinerary.penality * filesStore.getFileItinerary.adults,
                  digits: 2,
                })
              }}
            </h5>
          </a-col>
          <a-col class="text-warning ms-2">
            <small class="text-500" style="font-size: 12px"
              >Debe pagar la penalidad si cambia o anula el servicio</small
            >
          </a-col>
        </a-row>
        <div class="p-4 border bg-white my-4">
          <p>Precio neto por tipo de pasajero</p>
          <a-row type="flex" justify="space-between">
            <a-col class="text-left">
              <small class="d-flex text-gray mb-2"> Costo neto </small>
              <p class="mb-1 text-warning">
                <a-row type="flex" align="middle" style="gap: 4px">
                  <a-col>
                    <span class="text-800">
                      $
                      {{
                        formatNumber({
                          number: filesStore.getFileItinerary.penality_cost,
                          digits: 2,
                        })
                      }}
                    </span>
                  </a-col>
                  <a-col>
                    <FontAwesomeIcon
                      icon="arrow-right-long"
                      class="text-dark-gray"
                    ></FontAwesomeIcon>
                  </a-col>
                  <a-col>
                    <a-tag class="text-400 text-gray">
                      $
                      {{
                        formatNumber({
                          number: filesStore.getFileItinerary.penality,
                          digits: 2,
                        })
                      }}
                    </a-tag>
                  </a-col>
                </a-row>
              </p>
              <p class="mb-1 text-warning" v-if="filesStore.getFileItinerary.children > 0">
                <a-row type="flex" align="middle" style="gap: 4px">
                  <a-col>
                    <span class="text-800">
                      $
                      {{
                        formatNumber({
                          number: filesStore.getFileItinerary.penality_cost,
                          digits: 2,
                        })
                      }}
                    </span>
                  </a-col>
                  <a-col>
                    <FontAwesomeIcon
                      icon="arrow-right-long"
                      class="text-dark-gray"
                    ></FontAwesomeIcon>
                  </a-col>
                  <a-col>
                    <a-tag class="text-400 text-gray">
                      $
                      {{
                        formatNumber({
                          number: filesStore.getFileItinerary.penality,
                          digits: 2,
                        })
                      }}
                    </a-tag>
                  </a-col>
                </a-row>
              </p>
            </a-col>
            <a-col class="text-center">
              <small class="d-flex text-gray mb-2"> {{ t('global.label.pax_type') }} </small>
              <p class="mb-1">
                <span class="text-700 me-1">ADL</span>
                <i class="bi bi-person-fill"></i>
              </p>
              <p class="mb-1" v-if="filesStore.getFileItinerary.children > 0">
                <span class="text-700 me-1">CHD</span>
                <i class="bi bi-person-arms-up"></i>
              </p>
            </a-col>
            <a-col class="text-center">
              <small class="d-flex text-gray mb-2"> Cantidad </small>
              <p class="mb-1 text-dark">
                <span class="text-400">{{
                  textPad({ text: filesStore.getFileItinerary.adults, start: '0', length: 2 })
                }}</span>
              </p>
              <p class="mb-1 text-dark" v-if="filesStore.getFileItinerary.children > 0">
                <span class="text-400">{{
                  textPad({ text: filesStore.getFileItinerary.children, start: '0', length: 2 })
                }}</span>
              </p>
            </a-col>
            <a-col class="text-left">
              <small class="d-flex text-gray mb-2"> Total </small>
              <p class="mb-1 text-dark">
                <span class="text-400"
                  ><b class="text-gray">$</b>
                  {{
                    formatNumber({
                      number:
                        filesStore.getFileItinerary.penality_cost *
                        filesStore.getFileItinerary.adults,
                      digits: 2,
                    })
                  }}</span
                >
              </p>
              <p class="mb-1 text-dark" v-if="filesStore.getFileItinerary.children > 0">
                <span class="text-400"
                  ><b class="text-gray">$</b>
                  {{
                    formatNumber({
                      number:
                        filesStore.getFileItinerary.penality_cost *
                        filesStore.getFileItinerary.adults,
                      digits: 2,
                    })
                  }}</span
                >
              </p>
            </a-col>
          </a-row>
          <!-- a-row
            type="flex"
            justify="space-between"
          >
            <a-col flex="auto" class="text-left">
              <p class="mb-1 text-warning">
                <span class="text-800">$ {{ filesStore.getFileItinerary.total_amount }}</span>
              </p>
            </a-col>
          </a-row -->
        </div>
      </a-col>
    </a-row>
  </div>

  <a-modal v-model:visible="modalInformation" :width="800">
    <template #title>
      <div class="text-left px-4 pt-4">
        <h6 class="mb-0" style="font-size: 18px !important">{{ service.name }}</h6>
        <a-tag
          color="#EB5757"
          style="
            position: absolute;
            top: 0px;
            right: 60px;
            border-top-left-radius: 0;
            border-top-right-radius: 0;
            padding: 7px 15px;
            font-size: 18px;
            font-weight: 500;
          "
        >
          {{ service.service_type.name }}
        </a-tag>
      </div>
    </template>
    <div class="px-2">
      <a-row :gutter="24" type="flex" justify="space-between" align="top">
        <a-col :span="15">
          <p class="text-700">Operatividad</p>
          <p class="mb-0">Sistema horario de 24 horas</p>
          <p>
            {{ service.operations.turns[0].departure_time }}
            {{ service.operations.turns[0].shifts_available }}
          </p>
        </a-col>
        <a-col :span="9">
          <template v-if="service.inclusions.length > 0">
            <p>
              <b>Incluye</b>
            </p>
            <p>
              <template v-for="inclusion in service.inclusions">
                <a-tag v-for="item in inclusion.include" class="mb-2">{{ item.name }}</a-tag>
              </template>
            </p>
          </template>
          <p>
            <b>Disponibilidad</b>
          </p>
          <a-row type="flex" justify="space-between" align="top" style="gap: 5px">
            <a-col>
              <svg
                width="24"
                height="25"
                viewBox="0 0 24 25"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M22 11.2049V12.1249C21.9988 14.2813 21.3005 16.3795 20.0093 18.1067C18.7182 19.8338 16.9033 21.0973 14.8354 21.7088C12.7674 22.3202 10.5573 22.2468 8.53447 21.4994C6.51168 20.7521 4.78465 19.371 3.61096 17.5619C2.43727 15.7529 1.87979 13.6129 2.02168 11.4612C2.16356 9.30943 2.99721 7.26119 4.39828 5.62194C5.79935 3.98268 7.69279 2.84025 9.79619 2.36501C11.8996 1.88977 14.1003 2.1072 16.07 2.98486"
                  stroke="#1ED790"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
                <path
                  d="M22 4.125L12 14.135L9 11.135"
                  stroke="#1ED790"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
              </svg>
            </a-col>
            <a-col>
              <p class="mb-1">Dias:</p>
              <template v-if="Object.values(service.operations.days).length == 7">
                Todos los días
              </template>
              <template v-else>
                <p class="m-0" v-for="(day, d) in service.operations.days">
                  {{ d }}
                </p>
              </template>
            </a-col>
            <a-col>
              <p class="mb-1">Horario</p>
              <template v-if="Object.values(service.operations.days).length == 7">
                <p class="text-danger text-400 mb-0">
                  {{ service.operations.schedule[0]['friday'] }}.
                </p>
              </template>
              <template v-else>
                <p class="m-0" v-for="(day, d) in service.operations.schedule">{{ d }}.</p>
              </template>
            </a-col>
          </a-row>
        </a-col>
      </a-row>
    </div>
    <template #footer></template>
  </a-modal>

  <a-modal v-model:visible="modalPoliciesCancellation" :width="500">
    <template #title>
      <div class="text-left">
        <b class="text-700" style="font-size: 12px">Política de cancelación</b>
      </div>
    </template>
    <div id="files-layout" style="margin: -20px; margin-top: 0">
      <div class="files-edit m-0 p-0">
        <div class="bg-pink-stick">
          <template v-for="(_service, s) in filesStore.getFileItinerary.services">
            <template v-for="(_composition, c) in _service.compositions">
              <p class="m-0" style="font-size: 12px">
                Composición: <b>{{ _composition.name }} ({{ _composition.code }})</b>
              </p>
              <p class="m-0" style="font-size: 12px">
                {{ _composition.penality.message }}
              </p>
            </template>
          </template>
        </div>
      </div>
    </div>
    <template #footer></template>
  </a-modal>
</template>

<script setup>
  import { onBeforeMount, ref, watch } from 'vue';
  import { useFilesStore, useExecutivesStore } from '@store/files';
  import { formatDate, formatNumber, textPad } from '@/utils/files.js';
  import { debounce } from 'lodash-es';
  import { useI18n } from 'vue-i18n';
  import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

  const emit = defineEmits(['onChangeAsumed']);

  const { t } = useI18n({
    useScope: 'global',
  });

  const filesStore = useFilesStore();
  const executivesStore = useExecutivesStore();

  defineProps({
    type: {
      type: String,
      default: () => '',
    },
    editable: {
      type: Boolean,
      default: () => true,
    },
  });

  const flag_validate = ref(false);
  const executive_id = ref('');
  const file_id = ref('');
  const asumed_by = ref('');
  const motive = ref('');

  const searchExecutives = debounce(async (value) => {
    if (value != '' || (value == '' && executivesStore.getExecutives.length == 0)) {
      await executivesStore.fetchAll(value);
    }
  }, 300);

  const searchFiles = debounce(async (value) => {
    if (value != '') {
      await filesStore.fetchAll({ filter: value });
    }
  }, 300);

  // Función para emitir cambios de estado
  const emitChange = () => {
    emit('onChangeAsumed', {
      executive_id: executive_id.value,
      file_id: file_id.value,
      flag_validate: flag_validate.value,
      asumed_by: asumed_by.value,
      motive: motive.value,
    });
  };

  // Observa cambios en las referencias
  watch([flag_validate, asumed_by, motive], ([newFlag, newAsumed, newMotive]) => {
    console.log('Cambio detectado:', { newFlag, newAsumed, newMotive });
    emitChange();
  });

  const modalInformation = ref(false);
  const modalPoliciesCancellation = ref(false);
  const service = ref({});

  const showPoliciesCancellation = () => {
    modalPoliciesCancellation.value = true;
  };

  onBeforeMount(async () => {
    filesStore.initedAsync();
    await executivesStore.fetchAll('');
    await filesStore.fetchAsumedBy();

    if (!(filesStore.getFileItinerary?.penality > 0)) {
      await filesStore.calculatePenality('itinerary', [filesStore.getFileItinerary.id]);
    }

    if (filesStore.getFileItinerary.penality > 0) {
      flag_validate.value = true;
    }

    filesStore.finished();
  });

  const showInformation = async () => {
    await filesStore.findServiceInformation(
      filesStore.getFileItinerary.object_id,
      filesStore.getFileItinerary.date_in,
      filesStore.getFileItinerary.adults + filesStore.getFileItinerary.children
    );
    service.value = filesStore.getServiceInformation;

    setTimeout(() => {
      modalInformation.value = true;
    }, 100);
  };
</script>
