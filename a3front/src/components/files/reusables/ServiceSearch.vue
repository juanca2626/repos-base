<template>
  <div id="quotes-layout" class="spin-white">
    <a-spin :spinning="filesStore.isLoadingAsync" size="large">
      <template #indicator>
        <LoadingMaca v-if="filesStore.isLoadingAsync" />
      </template>
      <add-service-component
        :defaultOpen="true"
        :defaultCategory="'tours'"
        :showHeaderIcon="false"
        :showCheckbox="true"
        :showHotels="false"
        :showExtensions="false"
        :isFile="true"
      />
    </a-spin>
  </div>

  <div class="services-items" v-if="filesStore.getFileItinerariesServicesReplace.length > 0">
    <p class="text-selected text-700">Has seleccionado</p>
    <template v-for="(_service, s) in filesStore.getFileItinerariesServicesReplace">
      <div v-if="_service.entity === 'service-equivalence'">
        <a-row
          align="middle"
          justify="space-between"
          style="border: 1px dashed #ddd; gap: 8px"
          class="item-selected item-selected-default d-flex justify-content-between align-items-center rounded mb-3"
          :class="{
            'temporary-mask-item': _service?.service_mask === 1,
          }"
        >
          <a-col>
            <template v-if="_service.service_mask === 1">
              <font-awesome-icon style="color: #979797" :icon="['fas', 'gift']" size="lg" />
            </template>
            <template v-else>
              <font-awesome-icon class="text-danger" :icon="['fas', 'circle-check']" size="lg" />
            </template>
          </a-col>
          <a-col flex="auto">
            <a-tooltip placement="top">
              <template #title>
                <small> {{ _service.name }} </small>
              </template>
              <p class="item-selected-title mb-0 mx-1 truncate">
                <b>{{ _service.code }}</b> {{ _service.name }}
              </p>
            </a-tooltip>
          </a-col>
          <a-col>
            <a-divider
              type="vertical"
              style="height: 46px; width: 2px; background-color: #eb5757"
            />
          </a-col>
          <a-col>
            <a-row type="flex" align="middle" justify="start">
              <a-col :span="24">
                <div class="item-selected-span">TARIFA</div>
              </a-col>
              <a-col :span="24">
                <div class="item-price-status">
                  <div class="item-selected-price">
                    <font-awesome-icon class="pr-1" icon="fa-solid fa-dollar-sign" />
                    <div class="mx-2">{{ _service.price * _service.quantity }}</div>
                    <a-tooltip
                      v-if="_service.service_mask === 1"
                      overlayClassName="tooltip-mask"
                      :overlayStyle="{ maxWidth: '340px', padding: '15px' }"
                      color="#fff"
                      placement="top"
                    >
                      <template #title>
                        <div class="tooltip-mask-content">
                          <div class="tooltip-title-mask">Máscara de servicio</div>
                          <div class="tooltip-subtitle-mask">
                            Tarifas en $00.00, al agregar el servicio sigue los pasos para ingresar
                            el servicio
                          </div>
                          <div class="tooltip-options-mask">
                            <div class="tooltip-option-mask">
                              <div class="option">1</div>
                              <div class="step">Paso 1</div>
                              <div class="step-text">Ingresar proveedor</div>
                            </div>
                            <div class="tooltip-option-mask">
                              <div class="option">2</div>
                              <div class="step">Paso 2</div>
                              <div class="step-text">Tarifas e información de regalo</div>
                            </div>
                            <div class="tooltip-option-mask">
                              <div class="option">3</div>
                              <div class="step">Paso 3</div>
                              <div class="step-text">Máscara completa</div>
                            </div>
                          </div>
                        </div>
                      </template>
                      <IconCircleExclamation
                        class="me-2"
                        color="#EB5757"
                        width="1.1rem"
                        height="1.1rem"
                      />
                    </a-tooltip>
                  </div>
                  <!--                        <a-tag-->
                  <!--                          color="#28A745"-->
                  <!--                          class="tag-status"-->
                  <!--                          v-if="_service.service_mask !== 1"-->
                  <!--                        >-->
                  <!--                          OK-->
                  <!--                        </a-tag>-->
                  <!--                        <a-tag color="#D80404" class="tag-status mx-2" v-else>RQ</a-tag>-->
                </div>
              </a-col>
            </a-row>
          </a-col>
          <a-col>
            <span class="ml-2 text-dark-gray text-right cursor-pointer" @click="removeItem(s)">
              <IconXClose color="#C4C4C4" width="1.3rem" height="1.3rem" />
            </span>
          </a-col>
        </a-row>
      </div>
      <div v-if="_service.entity === 'service-temporary'">
        <a-row
          type="flex"
          align="middle"
          justify="space-between"
          class="item-selected temporary-service-item item-selected-default d-flex justify-content-between align-items-center rounded"
        >
          <a-col :span="4">
            <font-awesome-icon class="ms-2" :icon="['fas', 'business-time']" />
            <font-awesome-icon
              class="item-selected-icon mx-1"
              icon="fa-solid fa-file-import"
              style="color: #979797"
            />
          </a-col>
          <a-col :span="10">
            <a-tooltip placement="top" color="#fff">
              <template #title v-if="_service.name.length > 18">
                <span style="color: #0d0d0d"> {{ _service.name }}</span>
              </template>
              <p class="item-selected-title mb-0 mx-1">
                {{ truncateString(_service.name, 18) }}
              </p>
            </a-tooltip>
          </a-col>
          <a-col :span="2">
            <a-divider
              type="vertical"
              style="height: 46px; width: 2px; background-color: #eb5757"
            />
          </a-col>
          <a-col :span="6">
            <a-row type="flex" align="middle" justify="start">
              <a-col :span="24">
                <div class="item-selected-span">Tarifa</div>
              </a-col>
              <a-col :span="24">
                <div class="item-price-status">
                  <div class="item-selected-price">
                    <font-awesome-icon class="pr-1" icon="fa-solid fa-dollar-sign" />
                    <b class="mx-2">{{ _service.price * _service.quantity }}</b>
                  </div>
                  <!--                    <a-tag class="ant-tag-ok" v-if="_service.status == 1">OK</a-tag>-->
                </div>
              </a-col>
            </a-row>
          </a-col>
          <a-col :span="2">
            <span class="ml-2 text-dark-gray cursor-pointer" @click="removeItem(s)">
              <IconXClose color="#C4C4C4" width="1.3rem" height="1.3rem" />
            </span>
          </a-col>
        </a-row>
      </div>
    </template>

    <!-- a-alert type="info">
      <template #description>
        <a-row type="flex" justify="start" align="top" style="gap: 10px">
          <a-col>
            <i class="bi bi-exclamation-circle" style="font-size: 18px"></i>
          </a-col>
          <a-col>
            <p class="mb-1">Agregar servicios</p>
            Al presionar agregar se añadirán al file los servicios seleccionados que no tienen
            comunicación e irá al paso 2 para servicios con comunicación.
          </a-col>
        </a-row>
      </template>
    </a-alert -->
    <a-row type="flex" align="middle" justify="end" class="price-total" style="gap: 7px">
      <a-col>
        <span class="price-total-info">Total de servicios pagar:</span>
      </a-col>
      <a-col>
        <b class="title text-danger">
          <font-awesome-icon class="pr-1" icon="fa-solid fa-dollar-sign" />
          {{ formatNumber({ number: totalSum, digits: 2 }) }}
        </b>
      </a-col>
      <a-col>
        <a-button
          type="default"
          class="mx-2 px-4 text-600 btn-back"
          default
          size="large"
          v-on:click="cancel()"
          :loading="filesStore.isLoadingAsync"
        >
          {{ t('global.button.cancel') }}
        </a-button>
      </a-col>
      <a-col>
        <a-button
          type="default"
          class="mx-2 px-4 text-600 btn-add"
          default
          v-on:click="nextStep()"
          :loading="filesStore.isLoadingAsync"
          size="large"
        >
          Agregar servicios
        </a-button>
      </a-col>
    </a-row>
    <hr class="my-5" />
  </div>

  <template v-if="!filesStore.isLoadingAsync && filesStore.getFlagSearchServices">
    <template v-if="filteredServices.length > 0">
      <div
        class="box-hotels p-4 mt-3"
        :class="{
          'temporary-service-item': _service?.entity && _service?.entity === 'service-temporary',
          'temporary-mask-item': !_service?.entity && _service?.service_mask === 1,
        }"
        v-for="(_service, s) in filteredServices"
        :key="s"
      >
        <div v-if="!_service?.entity">
          <a-row
            type="flex"
            justify="space-between"
            align="top"
            v-for="(_rate, r) in _service.rate.rate_plans"
            :key="r"
          >
            <a-col>
              <a-row type="flex" justify="start" align="middle" style="gap: 5px">
                <a-col>
                  <font-awesome-icon
                    :icon="
                      filesStore.showServiceIcon(
                        _service.category.service_category_id,
                        4,
                        _service.service_type.id
                      )
                    "
                    class="text-dark"
                    size="xl"
                  />
                </a-col>
                <a-col>
                  <font-awesome-icon
                    v-if="_service.service_mask === 1"
                    :icon="['fas', 'gift']"
                    class="mx-2"
                    size="xl"
                  />
                  <span class="truncate text-500" style="font-size: 15px">
                    <a-tooltip>
                      <template #title v-if="_service.descriptions.description.length > 90">{{
                        _service.descriptions.description
                      }}</template>
                      {{ truncateString(_service.descriptions.description, 90) }}
                    </a-tooltip>
                  </span>
                </a-col>
              </a-row>
              <a-tag class="text-white bg-danger mt-2" :bordered="false">
                {{ _service.code }}
              </a-tag>
              <small
                class="cursor-pointer more-info-service text-uppercase"
                @click="showInformation(_service)"
              >
                {{ t('global.label.more') }} {{ t('global.label.information') }}
                {{ t('global.label.of') }} {{ t('global.label.service') }}
              </small>
            </a-col>
            <a-col>
              <a-row type="flex" style="gap: 10px" justify="space-between" align="middle">
                <a-col v-if="_service.service_mask === 1">
                  <a-tooltip
                    overlayClassName="tooltip-mask"
                    :overlayStyle="{ maxWidth: '340px', padding: '15px' }"
                    color="#fff"
                    placement="top"
                  >
                    <template #title>
                      <div class="tooltip-mask-content">
                        <div class="tooltip-title-mask">Máscara de servicio</div>
                        <div class="tooltip-subtitle-mask">
                          Tarifas en $00.00, al agregar el servicio sigue los pasos para ingresar el
                          servicio
                        </div>
                        <div class="tooltip-options-mask">
                          <div class="tooltip-option-mask">
                            <div class="option">1</div>
                            <div class="step">Paso 1</div>
                            <div class="step-text">Ingresar proveedor</div>
                          </div>
                          <div class="tooltip-option-mask">
                            <div class="option">2</div>
                            <div class="step">Paso 2</div>
                            <div class="step-text">Tarifas e información de regalo</div>
                          </div>
                          <div class="tooltip-option-mask">
                            <div class="option">3</div>
                            <div class="step">Paso 3</div>
                            <div class="step-text">Máscara completa</div>
                          </div>
                        </div>
                      </div>
                    </template>
                    <IconCircleInformation
                      class="me-2"
                      color="#EB5757"
                      width="1.4rem"
                      height="1.4rem"
                    />
                  </a-tooltip>
                </a-col>
                <a-col v-if="!_service.service_mask">
                  <a href="javascript:;" v-on:click="showInformationRate(_rate)">
                    <IconCircleInformation color="#EB5757" width="1.4rem" height="1.4rem" />
                  </a>
                </a-col>
                <a-col>
                  <b class="text-uppercase">{{ _service.rate.name }}</b>
                </a-col>
                <a-col>
                  <b class="mx-1 text-danger" style="font-size: 16px"
                    >$.
                    {{ roundLito({ num: parseFloat(_rate.price_adult) }) }}
                  </b>
                  <span class="mx-1">
                    <a-tag
                      class="bg-success text-white"
                      :bordered="false"
                      v-if="_service.rate.on_request == 0"
                      >OK</a-tag
                    >
                    <a-tag class="bg-danger text-white" :bordered="false" v-else>RQ</a-tag>
                  </span>
                </a-col>
                <a-col>
                  <span class="cursor-pointer" v-on:click="toggleRate(_rate, _service)">
                    <template v-if="items.indexOf(_rate.id) > -1">
                      <i class="bi bi-check-square-fill text-danger" style="font-size: 1.5rem"></i>
                    </template>
                    <template v-else>
                      <i
                        class="bi bi-square text-danger text-dark-light"
                        style="font-size: 1.5rem"
                      ></i>
                    </template>
                  </span>
                </a-col>
              </a-row>
            </a-col>
          </a-row>
        </div>
        <div v-else>
          <a-row type="flex" justify="space-between" align="top">
            <a-col>
              <div class="h6 text-700 p-0" style="max-width: 600px">
                <font-awesome-icon :icon="['fas', 'stopwatch']" style="font-size: 1.3rem" />
                <i class="bi bi-building-fill mx-2" style="font-size: 1.3rem"></i>
                <span class="truncate">
                  <a-tooltip>
                    <template #title v-if="_service.name.length > 60">{{ _service.name }}</template>
                    {{ truncateString(_service.name, 60) }}
                  </a-tooltip>
                </span>
              </div>
              <!--              <a-tag class="text-white bg-danger mt-2" :bordered="false">-->
              <!--                {{ _service.category.category }}-->
              <!--              </a-tag>-->
              <span
                class="cursor-pointer more-info-service"
                @click="showModalInfoTemporary(_service)"
              >
                Más información del servicio
              </span>
            </a-col>
            <a-col></a-col>
            <a-col>
              <a-row type="flex" justify="space-between" align="middle">
                <a-col>
                  <i class="bi bi-info-circle me-2" style="font-size: 1.3rem"></i>
                </a-col>
                <a-col>
                  <span class="me-3"> <b>Tarifa:</b> </span>
                </a-col>
                <a-col>
                  <b class="mx-2 text-danger"
                    >$. {{ parseFloat(_service.total_amount).toFixed(2) }}</b
                  >
                  <span class="mx-1">
                    <a-tag class="bg-success text-white" v-if="_service.status === 0">OK</a-tag>
                    <a-tag class="bg-danger text-white" v-else>RQ</a-tag>
                  </span>
                </a-col>
                <a-col>
                  <span class="cursor-pointer" v-on:click="toggleServiceOptions(_service)">
                    <template v-if="items.indexOf(_service.id) > -1">
                      <i class="bi bi-check-square-fill text-danger" style="font-size: 1.5rem"></i>
                    </template>
                    <template v-else>
                      <i
                        class="bi bi-square text-danger text-dark-light"
                        style="font-size: 1.5rem"
                      ></i>
                    </template>
                  </span>
                </a-col>
              </a-row>
            </a-col>
          </a-row>
        </div>
      </div>
    </template>
    <template v-else>
      <a-alert type="warning">
        <template #description>
          <a-row type="flex" justify="start" align="top" style="gap: 10px">
            <a-col>
              <i class="bi bi-exclamation-circle" style="font-size: 18px"></i>
            </a-col>
            <a-col>
              <p class="mb-1">Búsqueda de servicios</p>
              No se encontró servicios con los filtros seleccionados. Por favor, intente con otros
              filtros.
            </a-col>
          </a-row>
        </template>
      </a-alert>
    </template>

    <BasePagination
      v-if="filesStore.getPages > 1"
      v-model:current="currentPage"
      v-model:pageSize="perPage"
      :total="filesStore.getTotalServices"
      :show-quick-jumper="true"
      :show-size-changer="true"
      @change="onChange"
    />

    <div class="my-3" v-if="step > 0">
      <a-row type="flex" justify="end" align="middle">
        <a-col>
          <a-button
            type="default"
            class="mx-2 px-4 text-600"
            v-on:click="returnToProgram()"
            default
            :disabled="filesStore.isLoading || filesStore.isLoadingAsync"
            size="large"
          >
            {{ t('global.button.cancel') }}
          </a-button>
          <a-button
            v-if="items.length > 0"
            type="primary"
            class="mx-2 px-4 text-600"
            v-on:click="nextStep()"
            default
            :disabled="filesStore.isLoading || filesStore.isLoadingAsync"
            size="large"
          >
            {{ t('global.button.continue') }}
          </a-button>
        </a-col>
      </a-row>
    </div>
  </template>
  <InformationServiceModalComponent
    :is-open="modalIsOpenInformation"
    :data="modalInformation"
    @update:is-open="modalIsOpenInformation = $event"
  />

  <a-modal v-model:visible="modalInformationService" :width="800">
    <template #title>
      <div id="files-layout">
        <div id="files-edit">
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
        </div>
      </div>
    </template>
    <div id="files-layout">
      <div id="files-edit" class="px-2">
        <a-row :gutter="24" type="flex" justify="space-between" align="top">
          <a-col :span="15">
            <template v-for="itinerary in service.descriptions.itinerary">
              <div v-html="itinerary.description" class="mb-3"></div>
            </template>
            <p class="text-700">Operatividad</p>
            <p class="mb-0">Sistema horario de 24 horas</p>
            <p>
              {{ service.operations.turns[0].departure_time }}
              {{ service.operations.turns[0].shifts_available }}
            </p>
          </a-col>
          <a-col :span="9">
            <div class="bg-light" v-if="service.galleries.length > 0">
              <a-carousel arrows :autoplay="true" class="mb-3">
                <template #prevArrow>
                  <div class="custom-slick-arrow" style="left: 10px; z-index: 1">
                    <left-circle-outlined />
                  </div>
                </template>
                <template #nextArrow>
                  <div class="custom-slick-arrow" style="right: 10px">
                    <right-circle-outlined />
                  </div>
                </template>
                <div
                  v-for="(image, i) in service.galleries.slice(0, 4)"
                  class="w-100 object-cover aspect-square"
                  :key="i"
                >
                  <img :src="image" class="w-100 object-cover h-full" />
                </div>
              </a-carousel>
            </div>
            <template v-if="service.inclusions.length > 0">
              <p>
                <b>Incluye</b>
              </p>
              <p>
                <template v-for="inclusion in service.inclusions">
                  <a-tag v-for="item in inclusion.include" class="mb-2">
                    <a-tooltip>
                      {{ truncateString(item.name, 40) }}
                      <template #title v-if="item.name.length > 40">{{ item.name }}</template>
                    </a-tooltip>
                  </a-tag>
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
    </div>
    <template #footer></template>
  </a-modal>

  <a-modal v-model:visible="modalInformationRate" :footer="null" :width="600">
    <template #title>
      <div>
        <h6 class="mb-0" style="font-size: 18px !important">Detalles de políticas del servicio</h6>
      </div>
    </template>
    <div id="files-layout">
      <div class="files-edit p-0 m-0">
        <div class="files-edit__service-detail-card">
          <p class="text-700">Política de cancelaciones</p>
          <p class="mb-0">
            {{ rate }}
            {{ rate.political.cancellation.penalties[0].message }}
          </p>
        </div>
      </div>
    </div>
    <template #footer></template>
  </a-modal>
</template>

<script setup>
  import { onBeforeMount, ref, computed } from 'vue';
  import { useFilesStore } from '@store/files';
  import { truncateString, formatNumber, roundLito } from '@/utils/files.js';
  import AddServiceComponent from '@/quotes/components/AddServiceComponent.vue';
  import useQuoteDestinations from '@/quotes/composables/useQuoteDestinations';
  import useQuoteLanguages from '@/quotes/composables/useQuoteLanguages';
  import useQuoteDocTypes from '@/quotes/composables/useQuoteDocTypes';
  import useCountries from '@/quotes/composables/useCountries';
  import { useQuoteServices } from '@/quotes/composables/useQuoteServices';
  import BasePagination from '@/components/files/reusables/BasePagination.vue';
  import { v4 as uuidv4 } from 'uuid';
  import { useI18n } from 'vue-i18n';
  import InformationServiceModalComponent from '@/components/files/service/components/informationServiceModalComponent.vue';
  import IconCircleExclamation from '@/components/icons/IconCircleExclamation.vue';
  import IconXClose from '@/components/icons/IconXClose.vue';
  import { notification } from 'ant-design-vue';
  import { useServiceMaskStore } from '@/components/files/services-masks/store/serviceMaskStore';
  import { useRouter } from 'vue-router';
  import IconCircleInformation from '@/components/icons/IconCircleInformation.vue';
  import { LeftCircleOutlined, RightCircleOutlined } from '@ant-design/icons-vue';
  import LoadingMaca from '@/components/global/LoadingMaca.vue';

  const { t } = useI18n({
    useScope: 'global',
  });

  const emit = defineEmits(['onReturnToPogram', 'onNextStep']);
  const filesStore = useFilesStore();
  const modalIsOpenInformation = ref(false);
  const modalInformation = ref(null);
  const modalInformationService = ref(false);
  const service = ref({});
  const modalInformationRate = ref(false);
  const rate = ref({});
  const serviceMaskStore = useServiceMaskStore();
  const router = useRouter();

  const returnToProgram = () => {
    emit('onReturnToProgram');
  };

  defineProps({
    showHeader: {
      type: Boolean,
      default: () => true,
    },
  });

  const currentPage = ref(1);
  const perPage = ref(10);

  const onChange = () => {
    filesStore.changePageServices(currentPage.value, perPage.value);
  };
  // Lógica relacionada con la obtención de datos para los componentes
  // const { getQuote } = useQuote();
  const {
    getServicesCategories,
    getServicesExperiences,
    getServicesDurations,
    getServiceTypeMeals,
    getServicesDestinations,
  } = useQuoteServices();
  const { getDestinations } = useQuoteDestinations();
  const { getLanguages } = useQuoteLanguages();
  const { getDoctypes } = useQuoteDocTypes();
  const { getCountries } = useCountries();

  // Función para obtener los componentes necesarios
  const loadingResources = ref(false);
  const items = ref([]);

  const getComponents = async () => {
    loadingResources.value = true;
    const resources = [];

    resources.push(getServicesCategories());
    resources.push(getServicesExperiences());
    resources.push(getServicesDurations());
    resources.push(getServiceTypeMeals());
    resources.push(getServicesDestinations());
    resources.push(getDestinations());
    resources.push(getLanguages());
    resources.push(getDoctypes());
    // resources.push(getQuote());
    resources.push(getCountries());

    await Promise.all(resources).then(() => (loadingResources.value = false));
  };

  onBeforeMount(async () => {
    filesStore.clearSearchServices();
    filesStore.clearFileItineraryServiceReplace();

    await getComponents();
  });

  const filteredServices = computed(() => {
    return filesStore.getServices;

    /*
    const targetItinerary = filesStore.getFileItinerary;

    return filesStore.getServices.filter((service) => {
      const isSameAsTarget = service.code === targetItinerary.object_code &&
        service.date_reserve === targetItinerary.date_in;

      const alreadyExists = filesStore.getFileItineraries.some(
        (itinerary) =>
          itinerary.date_in === service.date_reserve && itinerary.object_code === service.code
      );

      // Retornamos solo los servicios que NO cumplen ambas condiciones al mismo tiempo
      return !(isSameAsTarget || alreadyExists);
    });
    */
  });

  const toggleServiceOptions = (service) => {
    let index = items.value.indexOf(service.id);
    if (index > -1) {
      items.value.splice(index, 1);
      filesStore.removeFileItineraryServiceReplace(index);
    } else {
      const ident = uuidv4().replace(/-/g, '');
      service.ident = ident;

      let params = {
        service: service,
        entity: 'service-temporary',
        quantity: 1,
        adults: filesStore.getDefaultAdults,
        children: filesStore.getDefaultChildren,
        search_parameters_services: filesStore.getSearchParametersServices,
        price: parseFloat(service.total_amount * filesStore.getDefaultAdults),
        passengers: filesStore.getSearchPassengers,
      };

      items.value.push(service.id);
      filesStore.putFileItinerariesServiceReplace(params);
    }
  };
  const toggleRate = (rate, service) => {
    let index = items.value.indexOf(rate.id);

    if (index > -1) {
      items.value.splice(index, 1);
      filesStore.removeFileItineraryServiceReplace(index);
    } else {
      // Identificador Service..
      const ident = uuidv4().replace(/-/g, '');
      service.ident = ident;

      if (hasServiceMask()) {
        notifyServiceMaskWarning();
        return;
      }

      let params = {
        service: service,
        entity: 'service-equivalence',
        rate: rate,
        quantity: 1,
        adults: filesStore.getDefaultAdults,
        children: filesStore.getDefaultChildren,
        token_search: filesStore.getTokenSearchServices,
        search_parameters_services: filesStore.getSearchParametersServices,
        price: parseFloat(rate.price_adult * filesStore.getDefaultAdults),
        passengers: filesStore.getSearchPassengers,
      };

      items.value.push(rate.id);
      filesStore.putFileItinerariesServiceReplace(params);
    }
  };

  const hasServiceMask = () => {
    return filesStore.getFileItinerariesServicesReplace.some((item) => item.service_mask === 1);
  };

  const getServiceMask = () => {
    return filesStore.getFileItinerariesServicesReplace.filter((item) => item.service_mask === 1);
  };

  const notifyServiceMaskWarning = () => {
    notification.warning({
      message: 'Máscara de servicio',
      description: 'Solo se puede agregar una máscara de servicio',
    });
  };

  const totalSum = computed(() => {
    return filesStore.getFileItinerariesServicesReplace.reduce((total, service) => {
      return total + service.price * service.quantity;
    }, 0);
  });

  const removeItem = (_index) => {
    items.value.splice(_index, 1);
    filesStore.removeFileItineraryServiceReplace(_index);
  };

  const cancel = () => {
    emit('onReturnToProgram');
  };

  const showModalInfoTemporary = (data) => {
    if (data.entity === 'service' || data.entity === 'service-temporary') {
      modalIsOpenInformation.value = true;
      data.service_itinerary = data.details[0].itinerary;
      modalInformation.value = data;
    }
  };

  const showInformation = async (_service) => {
    service.value = _service;
    modalInformationService.value = true;
  };

  const showInformationRate = async (_rate) => {
    rate.value = _rate;
    modalInformationRate.value = true;
  };

  const nextStep = () => {
    if (hasServiceMask()) {
      serviceMaskStore.clearServiceMask();
      serviceMaskStore.clearServiceMaskRate();
      serviceMaskStore.clearServiceMaskSupplier();
      serviceMaskStore.clearFileSelected();

      serviceMaskStore.setFile({
        file: filesStore.getFile,
        passengers: filesStore.getFilePassengers,
      });

      const serviceMask = getServiceMask();
      serviceMaskStore.setServiceMask(serviceMask[0]);
      const fileId = router.currentRoute.value.params.id; // Obtenemos el :id desde la URL
      router.push({
        name: 'files-add-miscellaneous-modifiable',
        params: {
          id: fileId, // Usamos el id actual
          service_id: serviceMask[0].service_id, // Usamos el service_id actual
        },
      });
    } else {
      emit('onNextStep');
    }
  };
</script>
<style scoped lang="scss">
  .tooltip-mask .ant-tooltip-content {
    font-family: Montserrat, sans-serif;
    width: 317px !important;
  }

  .tag-status {
    font-family: Montserrat, serif;
    font-size: 10px;
    font-weight: 700;
  }

  .item-price-status,
  .item-selected-price {
    display: flex;
    align-items: center;
    justify-content: flex-start;
  }

  .item-selected-span {
    font-family: Montserrat, sans-serif;
    font-size: 12px;
    font-weight: 600;
    color: #4f4b4b;
    margin-bottom: 5px;
  }

  .tooltip-title-mask {
    font-family: Montserrat, sans-serif;
    color: #575757;
    text-align: center;
    font-size: 14px;
    font-weight: 700;
    margin-bottom: 10px;
  }

  .tooltip-subtitle-mask {
    font-family: Montserrat, sans-serif;
    color: #575757;
    text-align: justify;
    font-size: 10px;
    font-weight: 400;
    margin-bottom: 10px;
  }

  .tooltip-mask-content {
    padding: 15px;
  }

  .tooltip-options-mask {
    font-family: Montserrat, sans-serif;
    color: #575757;
    display: flex;
    flex-direction: column;

    .option {
      font-family: Montserrat, sans-serif;
      width: 22px;
      height: 22px;
      background-color: #55a3ff;
      border-radius: 50%;
      display: flex;
      justify-content: center;
      align-items: center;
      color: #ffffff;
    }

    .step {
      font-family: Montserrat, sans-serif;
      font-size: 12px;
      font-weight: 600;
      color: #4f4b4b;
    }

    .step-text {
      font-family: Montserrat, sans-serif;
      font-size: 10px;
      font-weight: 400;
      color: #575757;
    }

    .tooltip-option-mask {
      display: flex;
      flex-direction: row;
      align-content: center;
      align-items: center;
      gap: 10px;
      margin-bottom: 5px;
      margin-left: 20px;
    }
  }

  .temporary-service-item {
    background-color: #e9e9e9 !important;
  }

  .temporary-mask-item {
    background-color: #ededff !important;
  }

  .ant-alert-info {
    flex-direction: row;
    justify-content: center;
    gap: 10px;
    align-items: flex-start;
  }

  .temporary-service-item-selected {
    background-color: #e2e8f0 !important;
  }

  .more-info-service {
    font-family: Montserrat, serif;
    font-weight: 500;
    color: #eb5757;
    text-decoration: underline;
    text-underline-position: under;
    text-underline-offset: 1px;
    cursor: pointer;
  }

  .btn-add {
    width: auto;
    height: 54px;
    padding: 12px 24px 12px 24px;
    display: flex;
    align-items: center;
    flex-wrap: nowrap;
    justify-content: center;
    font-size: 17px;
    font-weight: 500 !important;
    background-color: #eb5757 !important;
    color: #fff6f6 !important;
    border: 1px solid #eb5757 !important;

    svg {
      margin-right: 10px;
    }

    &:hover {
      color: #fff6f6 !important;
      background-color: #c63838 !important;
      border: 1px solid #eb5757 !important;
    }
  }

  .btn-back {
    width: auto;
    height: 54px;
    padding: 12px 24px 12px 24px;
    display: flex;
    align-items: center;
    flex-wrap: nowrap;
    justify-content: center;
    font-size: 17px;
    font-weight: 500 !important;
    color: #575757 !important;
    background-color: #fafafa !important;

    border: 1px solid #fafafa !important;

    &:hover {
      color: #575757 !important;
      background-color: #e9e9e9 !important;
      border: 1px solid #e9e9e9 !important;
    }
  }
</style>

<style scoped>
  /* For demo */
  :deep(.slick-slide) {
    text-align: center;
    height: 160px;
    line-height: 160px;
    background: #364d79;
    overflow: hidden;
  }

  :deep(.slick-arrow.custom-slick-arrow) {
    width: 25px;
    height: 25px;
    font-size: 25px;
    color: #fff;
    background-color: rgba(31, 45, 61, 0.11);
    transition: ease all 0.3s;
    opacity: 0.3;
    z-index: 1;
  }
  :deep(.slick-arrow.custom-slick-arrow:before) {
    display: none;
  }
  :deep(.slick-arrow.custom-slick-arrow:hover) {
    color: #fff;
    opacity: 0.5;
  }

  :deep(.slick-slide h3) {
    color: #fff;
  }
</style>
