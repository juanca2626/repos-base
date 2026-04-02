<template>
  <div class="files-edit">
    <a-spin
      size="large"
      :spinning="
        isTranslating ||
        filesStore.isLoading ||
        filesStore.isLoadingAsync ||
        fileServiceStore.isLoading
      "
    >
      <div class="d-flex justify-content-between align-items-center pb-5">
        <div class="title">
          <font-awesome-icon
            :icon="['fas', 'stopwatch']"
            class="text-danger"
            style="padding-right: 15px"
          />
          Crear servicio temporal
        </div>
        <div class="actions">
          <a-button
            class="btn-default btn-back"
            type="default"
            v-on:click="returnToProgram()"
            :loading="filesStore.isLoadingAsync"
            size="large"
          >
            Ir al programa
          </a-button>
          <a-button
            danger
            class="text-600 btn-temporary"
            type="default"
            v-on:click="returnToReplaceService()"
            :loading="filesStore.isLoadingAsync"
            size="large"
          >
            Volver a modificar servicio
          </a-button>
        </div>
      </div>
      <a-row>
        <a-col :span="24">
          <div class="files-edit__fileinfo-center">
            <span class="files-edit__filealert">
              <a-alert class="px-4" type="warning" closable>
                <template #message>
                  <WarningOutlined :style="{ fontSize: '18px', color: '#FFCC00' }" />
                  <span class="text-warning">
                    El servicio temporal se crea a partir del servicio seleccionado. No se modifica
                    o reemplaza el servicio original
                  </span>
                </template>
              </a-alert>
            </span>
          </div>
        </a-col>
      </a-row>
      <a-row>
        <a-col :span="24" class="mt-5">
          <a-card class="files-edit__service-detail-card">
            <!-- Encabezado -->
            <div class="header-card">
              <a-row justify="space-around" align="middle">
                <a-col span="1" class="text-center" v-if="serviceSelected.itinerary">
                  <font-awesome-icon
                    :icon="
                      filesStore.showServiceIcon(
                        serviceSelected.itinerary?.service_category_id,
                        serviceSelected.itinerary?.service_sub_category_id,
                        serviceSelected.itinerary?.service_type_id
                      )
                    "
                    style="font-size: 1.2rem"
                  />
                </a-col>
                <a-col span="23">
                  <div class="success-text-service">
                    <font-awesome-icon :icon="['far', 'circle-check']" size="lg" />
                    {{ serviceSelected.itinerary?.name }}
                  </div>
                </a-col>
              </a-row>
              <a-row justify="space-around" align="middle" class="mt-4">
                <a-col span="1"></a-col>
                <a-col span="10">
                  <div class="flex-row-service">
                    <div class="date-service">
                      <icon-calendar-ligth />
                      <span class="label-date">{{
                        formatDate(serviceSelected.itinerary?.date_in)
                      }}</span>
                    </div>
                    <div class="pax-service">
                      <span class="label-pax">pasajeros:</span>
                      <font-awesome-icon :icon="['fas', 'user']" />
                      <span class="label-adult">{{ serviceSelected.itinerary?.adults }}</span>
                      <font-awesome-icon :icon="['fas', 'child-reaching']" />
                      <span class="label-child">{{ serviceSelected.itinerary?.children }}</span>
                    </div>
                  </div>
                </a-col>
                <a-col span="13">
                  <div class="total-price-service">
                    <span class="label-price">Precio de venta:</span>
                    <span class="total-price">
                      $
                      {{
                        formatNumber({
                          number: serviceSelected.itinerary?.total_amount,
                          digits: 2,
                        }) || 0
                      }}
                    </span>
                    <span
                      class="label-price-penalty"
                      v-if="serviceSelected.itinerary?.total_amount_penalties > 0"
                    >
                      <i class="bi bi-exclamation-triangle" style="font-size: 1.4rem"></i>
                      Penalidad por cancelación
                    </span>
                    <span
                      class="total-price-penalty"
                      v-if="serviceSelected.itinerary?.total_amount_penalties > 0"
                    >
                      $ {{ serviceSelected.itinerary?.total_amount_penalties.toFixed(2) || 0 }}
                    </span>
                  </div>
                </a-col>
              </a-row>
              <a-row>
                <a-col :span="24">
                  <a-divider style="height: 1px; background-color: #bbbdbf" />
                </a-col>
              </a-row>
            </div>
            <!-- Header Servicios Maestros -->
            <a-row justify="space-between" align="middle">
              <a-col :span="12">
                <span class="label-maestro">
                  <IconGitPullRequest color="#212529" width="1.3em" height="1.3em" />
                  Servicios Maestros
                </span>
              </a-col>
              <a-col :span="12" style="text-align: right">
                <a-button type="link" class="btn-add-maestro" @click="showModalAddService()">
                  <span class="add-maestro">
                    <IconCirclePlus color="#EB5757" width="1.4em" height="1.4em" />
                    Agregar servicio maestro
                  </span>
                </a-button>
              </a-col>
            </a-row>
            <!-- Listado de Servicios Maestros -->
            <div class="mt-5">
              <a-row
                justify="space-between"
                align="middle"
                class="mt-1"
                v-for="(service, index) in serviceSelected.itinerary?.services"
                :key="index"
                v-show="!service.isDeleted"
              >
                <a-col :span="12" style="text-align: left">
                  <span class="col-name-service">{{ service?.name }}</span>
                </a-col>
                <a-col :span="6" style="text-align: center">
                  <span class="col-more-info-service" @click="showModalInfoMaster(service)">
                    Más información del compuesto
                  </span>
                </a-col>
                <a-col :span="2" style="text-align: right">
                  <span class="col-price"
                    >$ {{ formatNumber({ number: service?.amount_cost, digits: 0 }) }}</span
                  >
                </a-col>
                <a-col :span="2" style="text-align: right" v-if="service?.totalPenalties > 0">
                  <span class="col-price-penalty">
                    <i class="bi bi-exclamation-triangle" style="font-size: 1.1rem"></i>
                    $ {{ service?.totalPenalties.toFixed(2) }}
                  </span>
                </a-col>
                <a-col :span="2" style="text-align: right">
                  <span class="col-actions">
                    <font-awesome-icon
                      :icon="['fas', 'repeat']"
                      size="xl"
                      @click="showModalReplaceService(service)"
                    />
                    <font-awesome-icon
                      :icon="['fas', 'trash-can']"
                      size="xl"
                      @click="deleteMasterService(service)"
                    />
                  </span>
                </a-col>
              </a-row>
            </div>
          </a-card>
        </a-col>
      </a-row>
      <a-form
        class="files-edit__form-service-temporary"
        :model="formState"
        ref="formRefServiceTemporary"
        layout="vertical"
        :rules="rules"
      >
        <a-row>
          <a-col :span="24">
            <a-card class="files-edit__new-service-temporary">
              <div class="header-card">
                <a-row class="mb-4">
                  <a-col span="24">
                    <a-divider
                      orientation="left"
                      orientation-margin="0px"
                      style="margin-bottom: 5px"
                    >
                      <span class="title-new-temporary">Nombre del servicio temporal</span>
                    </a-divider>
                    <span class="sub-title-new-temporary">
                      La selección de los datos conformarán el nombre del servicio
                    </span>
                  </a-col>
                </a-row>
              </div>
              <a-row :gutter="16">
                <a-col span="8">
                  <a-form-item name="serviceTime" label="Tiempo" :rules="rules.serviceTime">
                    <a-select
                      style="width: 100%"
                      name="serviceSubCategory"
                      label="Tiempo"
                      placeholder="Selecciona..."
                      :field-names="{ label: 'label', value: 'id' }"
                      size="large"
                      :showSearch="true"
                      :filter-option="false"
                      :allowClear="true"
                      :options="servicesSubCategoriesOptions"
                      v-model:value="formState.serviceTime"
                      :loading="loadingResources"
                    >
                    </a-select>
                  </a-form-item>
                </a-col>
                <a-col span="8">
                  <a-form-item name="serviceType" label="Tipo" :rules="rules.serviceType">
                    <a-select
                      style="width: 100%"
                      name="serviceCategory"
                      label="Tipo"
                      placeholder="Selecciona..."
                      size="large"
                      :showSearch="true"
                      :filter-option="false"
                      :allowClear="true"
                      :options="servicesCategoriesOptions"
                      v-model:value="formState.serviceType"
                      :loading="loadingResources"
                    >
                    </a-select>
                  </a-form-item>
                </a-col>
                <a-col span="8">
                  <a-form-item
                    name="servicePrivacy"
                    label="Privacidad"
                    :rules="rules.servicePrivacy"
                  >
                    <a-select
                      style="width: 100%"
                      name="fileId"
                      label="Privacidad"
                      placeholder="Selecciona..."
                      size="large"
                      :showSearch="true"
                      :filter-option="false"
                      :allowClear="true"
                      :options="servicesTypesOptions"
                      v-model:value="formState.servicePrivacy"
                      @search="handleSearchFiles"
                      :loading="loadingResources"
                    >
                    </a-select>
                  </a-form-item>
                </a-col>
              </a-row>
              <a-row justify="center" align="middle">
                <a-col span="4" class="label-complement">
                  <span class="label-required">*</span> Nombre:
                </a-col>
                <a-col span="20">
                  <a-form-item
                    label=""
                    name="serviceSupplementaryText"
                    :rules="rules.serviceSupplementary"
                  >
                    <a-input
                      class="base-input"
                      v-model:value="formState.serviceSupplementaryText"
                      placeholder="Escribe aquí..."
                      name="description"
                      size="small"
                    />
                  </a-form-item>
                </a-col>
              </a-row>
            </a-card>
          </a-col>
        </a-row>
        <a-row>
          <a-col :span="24">
            <a-card class="files-edit__new-service-temporary">
              <div class="header-card">
                <a-row class="mb-4">
                  <a-col span="24">
                    <a-divider
                      orientation="left"
                      orientation-margin="0px"
                      style="margin-bottom: 5px"
                    >
                      <span class="title-new-temporary">Información adicional</span>
                    </a-divider>
                    <span class="sub-title-new-temporary">
                      La selección de los datos conformarán el nombre del servicio
                    </span>
                  </a-col>
                </a-row>
              </div>
              <a-row :gutter="16" class="mb-5">
                <a-col span="4">
                  <a-card class="language-card">
                    <a-card-grid class="language-card__grid">
                      <div class="label-lang-file">Idioma del file</div>
                      <div class="text-lang-file">{{ fileLanguageSelected?.label }}</div>
                    </a-card-grid>
                  </a-card>
                </a-col>
                <a-col span="20">
                  <a-form-item
                    label="Idiomas en los que desea ingresar la información del servicio"
                  >
                    <a-checkbox-group
                      name="checkbox-group"
                      size="large"
                      v-model:value="formState.languages"
                      :options="formattedLanguagesOptions"
                    />
                  </a-form-item>
                </a-col>
              </a-row>
              <a-row :gutter="24">
                <a-col span="24">
                  <a-tabs v-model:activeKey="activeKey">
                    <a-tab-pane key="1" tab="Skeleton">
                      <span class="files-edit__filealert mt-4 mb-4">
                        <a-alert class="px-4" type="info" closable>
                          <template #message>
                            <font-awesome-icon
                              :icon="['far', 'circle-check']"
                              size="lg"
                              :style="{ fontSize: '18px', color: '#5C5AB4' }"
                            />
                            <span style="color: #2e2b9e">
                              Selecciona y escribe en la parte del Skeleton, original, del idioma
                              que necesite alguna modificación
                            </span>
                          </template>
                        </a-alert>
                      </span>
                      <div v-for="(language, index) in formState.languages" :key="index">
                        <a-row>
                          <a-col :span="3">
                            <span class="label-required" v-if="language === 'es'">*</span>
                            {{ `${getLanguageLabel(language)}` }}
                          </a-col>
                          <a-col :span="21">
                            <a-form-item :name="`languageTextsSkeleton_${language}`">
                              <a-textarea
                                :rows="4"
                                v-model:value="formState.languageTextsSkeleton[language]"
                                :disabled="language !== 'es'"
                              />
                              <small
                                class="translation-loading"
                                v-if="isTranslating && language !== 'es'"
                              >
                                Traduciendo...
                              </small>
                            </a-form-item>
                          </a-col>
                        </a-row>
                      </div>
                    </a-tab-pane>
                    <a-tab-pane key="2" tab="Itinerario">
                      <span class="files-edit__filealert mt-4 mb-4">
                        <a-alert class="px-4" type="info" closable>
                          <template #message>
                            <font-awesome-icon
                              :icon="['far', 'circle-check']"
                              size="lg"
                              :style="{ fontSize: '18px', color: '#5C5AB4' }"
                            />
                            <span style="color: #2e2b9e">
                              Selecciona y escribe en la parte del Itinerario, original, del idioma
                              que necesite alguna modificación
                            </span>
                          </template>
                        </a-alert>
                      </span>
                      <div v-for="(language, index) in formState.languages" :key="index">
                        <a-row>
                          <a-col :span="3">
                            <span class="label-required" v-if="language === 'es'">*</span>
                            {{ `${getLanguageLabel(language)}` }}
                          </a-col>
                          <a-col :span="21">
                            <a-form-item :name="`languageTextsItinerary_${language}`">
                              <a-textarea
                                :rows="4"
                                v-model:value="formState.languageTextsItinerary[language]"
                                :disabled="language !== 'es'"
                              />
                              <small
                                class="translation-loading"
                                v-if="isTranslating && language !== 'es'"
                              >
                                Traduciendo...
                              </small>
                            </a-form-item>
                          </a-col>
                        </a-row>
                      </div>
                    </a-tab-pane>
                  </a-tabs>
                </a-col>
              </a-row>
            </a-card>
          </a-col>
        </a-row>
        <a-row>
          <a-col :span="24" style="text-align: right; margin-top: 50px">
            <div class="actions">
              <a-button
                class="btn-back text-600"
                type="default"
                :loading="
                  filesStore.isLoading || filesStore.isLoadingAsync || fileServiceStore.isLoading
                "
                size="large"
              >
                Atras
              </a-button>
              <a-button
                type="primary"
                class="btn-danger btn-continue mx-2 px-4 text-600"
                default
                html-type="submit"
                :loading="
                  isTranslating ||
                  filesStore.isLoading ||
                  filesStore.isLoadingAsync ||
                  fileServiceStore.isLoading
                "
                size="large"
                @click="handleFormSubmit"
              >
                Continuar
              </a-button>
            </div>
          </a-col>
        </a-row>
      </a-form>
    </a-spin>
  </div>
  <ServiceTemporaryModalConfirm
    :is-open="modalIsOpenConfirmation"
    @update:is-open="modalIsOpenConfirmation = $event"
    @serviceSaved="handleServiceSaved"
  />
  <ServiceTemporaryModalInformationMaster
    :is-open="modalIsOpenInfoMaster"
    @update:is-open="modalIsOpenInfoMaster = $event"
  />
  <ServiceTemporaryAddService
    :is-open="modalIsOpenAddService"
    @update:is-open="modalIsOpenAddService = $event"
  />
  <ServiceTemporaryReplaceService
    :is-open="modalIsOpenReplaceService"
    @update:is-open="modalIsOpenReplaceService = $event"
  />
</template>
<script setup lang="ts">
  import { computed, onMounted, reactive, ref, watch } from 'vue';
  import { useFilesStore } from '@/stores/files';
  import { useLanguagesStore } from '@/stores/global/index.js';
  import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
  import { WarningOutlined } from '@ant-design/icons-vue';
  import IconCalendarLigth from '@/quotes/components/icons/IconCalendarLight.vue';
  import ServiceTemporaryModalConfirm from '@/components/files/temporary/components/ServiceTemporaryModalConfirm.vue';
  import type {
    Service,
    Services,
  } from '@/components/files/temporary/interfaces/service.interface';
  import ServiceTemporaryModalInformationMaster from '@/components/files/temporary/components/ServiceTemporaryModalInformationMaster.vue';
  import ServiceTemporaryAddService from '@/components/files/temporary/components/ServiceTemporaryAddService.vue';
  import ServiceTemporaryReplaceService from '@/components/files/temporary/components/ServiceTemporaryModalReplaceService.vue';
  import { type FormInstance, notification } from 'ant-design-vue';
  import { useRouter } from 'vue-router';
  import { useQuoteServices } from '@/quotes/composables/useQuoteServices';
  import translationsApi from '@/utils/translationsApi';
  import { debounce } from 'lodash';
  import { useFileServiceStore } from '@/components/files/temporary/store/useFileServiceStore';
  import IconGitPullRequest from '@/components/icons/IconGitPullRequest.vue';
  import IconCirclePlus from '@/components/icons/IconCirclePlus.vue';
  import { formatNumber } from '@/utils/files.js';

  const loadingResources = ref(false);
  const activeKey = ref('1');
  const modalIsOpenConfirmation = ref(false);
  const modalIsOpenInfoMaster = ref(false);
  const modalIsOpenAddService = ref(false);
  const modalIsOpenReplaceService = ref(false);
  const serviceSelected = ref<Service>({});
  const languagesOptions = ref([]);
  const fileLanguageIso = ref('es');
  const filesStore = useFilesStore();
  const languagesStore = useLanguagesStore();
  const router = useRouter();
  const isTranslating = ref(false); // Estado para indicar que la traducción está en progreso
  const formRefServiceTemporary = ref<FormInstance | null>(null);
  const fileServiceStore = useFileServiceStore();

  const {
    servicesTypes,
    serviceCategories,
    serviceSubCategories,
    getServicesTypeCategories,
    getServicesCategories,
    getServicesSubCategories,
  } = useQuoteServices();

  const servicesTypesOptions = computed(() => servicesTypes.value);
  const servicesCategoriesOptions = computed(() => serviceCategories.value);
  const servicesSubCategoriesOptions = ref([]);
  const emit = defineEmits(['nextStep']);
  const fileLanguageSelected = ref({
    value: 'es',
    label: 'ESPAÑOL',
  });

  const formState = reactive({
    serviceTime: null,
    serviceType: null,
    servicePrivacy: null,
    serviceSupplementaryText: '',
    languages: ['es'],
    languageTextsName: {},
    languageTextsSkeleton: {},
    languageTextsItinerary: {},
    skeleton: [],
    itineraries: [],
  });

  const rules = {
    serviceTime: [{ required: true, message: 'Debe seleccionar una opción' }],
    serviceType: [{ required: true, message: 'Debe seleccionar una opción' }],
    servicePrivacy: [{ required: true, message: 'Debe seleccionar una opción' }],
    serviceSupplementary: [{ required: true, message: 'Debe ingresar un nombre' }],
    languageTextsSkeleton_es: [
      {
        required: true,
        message: 'Debe ingresar el texto en español',
        validator: (_rule: Rule) => {
          if (!formState.languageTextsSkeleton['es']) {
            return Promise.reject('Debe ingresar el texto en español');
          }
          return Promise.resolve();
        },
      },
    ],
    languageTextsItinerary_es: [
      {
        required: true,
        message: 'Debe ingresar el itinerario en español',
        validator: (_rule: Rule) => {
          if (!formState.languageTextsItinerary['es']) {
            return Promise.reject('Debe ingresar el itinerario en español');
          }
          return Promise.resolve();
        },
      },
    ],
  };

  const formattedLanguagesOptions = computed(() =>
    languagesOptions.value.map((lang) => ({
      ...lang,
      disabled: lang.value === 'es',
    }))
  );

  const getComponents = async () => {
    loadingResources.value = true;
    try {
      const resources = [];
      resources.push(getServicesCategories());
      resources.push(getServicesTypeCategories());
      resources.push(
        getServicesSubCategories(serviceSelected.value.itinerary?.service_category_id)
      );
      await Promise.all(resources);
    } catch (error) {
      console.error('Error al cargar los componentes:', error);
      notification.error({
        message: 'Error',
        description: 'Hubo un problema al cargar los datos necesarios para el servicio.',
      });
    } finally {
      loadingResources.value = false;
    }
  };

  const goToNextStep = () => {
    console.log('Go to next step');
    emit('complete');
    emit('nextStep');
  };

  const validateCommunications = async () => {
    try {
      let hasCommunications = false;
      const partialPayload = {
        services: {
          new: filesStore.getServiceTemporaryNew,
          delete: filesStore.getServiceTemporaryDeleted.map((service) => service.id),
          update: filesStore.getServiceTemporaryReplaced,
        },
      };
      // console.log('Payload enviado desde la aplicación:', JSON.stringify(partialPayload, null, 2));
      if (
        partialPayload.services.new.length === 0 &&
        partialPayload.services.delete.length === 0 &&
        partialPayload.services.update.length === 0
      ) {
        hasCommunications = false;
      } else {
        const response = await fileServiceStore.getCommunicationTemporaryService(
          router.currentRoute.value.params.id,
          router.currentRoute.value.params.service_id,
          partialPayload
        );
        console.log('Respuesta de la API:', response);
        if (fileServiceStore.isSuccess) {
          const { reservations, cancellation, modification } = response.data;
          if (
            (reservations && reservations.length > 0) ||
            (cancellation && cancellation.length > 0) ||
            (modification && modification.length > 0)
          ) {
            filesStore.setServiceTemporaryCommunications(response.data);
            hasCommunications = true;
          }
        }
      }

      return hasCommunications;
    } catch (error) {
      notification.error({
        message: 'Error',
        description: error.message || 'Ocurrió un error al guardar el servicio temporal',
      });
      console.error('Error:', error);
    } finally {
    }
  };

  const handleFormSubmit = async () => {
    try {
      await formRefServiceTemporary.value?.validate();

      filesStore.updateNameServiceEdit(formState.serviceSupplementaryText);
      filesStore.updateCategoryServiceEdit(formState.serviceType);
      filesStore.updateSubCategoryServiceEdit(formState.serviceTime);
      filesStore.updateTypeServiceEdit(formState.servicePrivacy);

      filesStore.updateTextServiceEdit(
        formattedLanguagesOptions,
        formState.languageTextsSkeleton,
        formState.languageTextsItinerary
      );

      const hasCommunications = await validateCommunications();
      if (hasCommunications && hasCommunications !== undefined) {
        goToNextStep();
      }

      if (!hasCommunications && hasCommunications !== undefined) {
        showModalConfirm();
      }
    } catch (errorInfo) {
      console.log(errorInfo);
      notification.error({
        message: 'Error de validación',
        description: 'Por favor, complete todos los campos requeridos',
      });
    }
  };

  const showModalConfirm = () => {
    modalIsOpenConfirmation.value = true;
  };

  const showModalInfoMaster = (masterService: Services) => {
    filesStore.setServiceMasterReplace(masterService);
    modalIsOpenInfoMaster.value = true;
  };

  const showModalAddService = () => {
    modalIsOpenAddService.value = true;
  };

  const showModalReplaceService = (masterService: Services) => {
    filesStore.setServiceMasterReplace(masterService);
    modalIsOpenReplaceService.value = true;
  };

  function formatDate(dateString: string | null): string {
    if (!dateString) return '';
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('es-ES', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
    }).format(date);
  }

  watch(
    () => languagesStore.getLanguages,
    (newLanguages) => {
      languagesOptions.value = newLanguages;
    },
    { immediate: true }
  );

  watch(
    () => languagesStore.getAllLanguages,
    (newLanguages) => {
      // Busca el idioma cuyo valor coincide con fileLanguageIso
      fileLanguageSelected.value = newLanguages.find(
        (lang) => lang.value === fileLanguageIso.value
      );
    },
    { immediate: true }
  );

  watch(
    () => formState.languages,
    async (newLanguages) => {
      // Marcar el callback del `watch` como `async`
      const currentLanguagesSkeleton = { ...formState.languageTextsSkeleton };
      const currentLanguagesItinerary = { ...formState.languageTextsItinerary };

      // Inicializar nuevos idiomas si no existen
      newLanguages.forEach((language) => {
        if (!currentLanguagesSkeleton[language]) {
          currentLanguagesSkeleton[language] = ''; // Inicializa el texto si no existe
        }
        if (!currentLanguagesItinerary[language]) {
          currentLanguagesItinerary[language] = ''; // Inicializa el texto si no existe
        }
      });

      // Eliminar idiomas que ya no están seleccionados
      Object.keys(currentLanguagesSkeleton).forEach((key) => {
        if (!newLanguages.includes(key)) {
          delete currentLanguagesSkeleton[key];
          delete currentLanguagesItinerary[key]; // Eliminar de ambos si ya no está seleccionado
        }
      });

      // Asignar los idiomas actuales
      formState.languageTextsSkeleton = currentLanguagesSkeleton;
      formState.languageTextsItinerary = currentLanguagesItinerary;

      // Llamar a la API solo para los idiomas seleccionados que no sean "es"
      const targetLangs = newLanguages.filter((lang) => lang !== 'es');

      if (targetLangs.length > 0) {
        const spanishTextSkeleton = formState.languageTextsSkeleton['es'];
        const spanishTextItinerary = formState.languageTextsItinerary['es'];

        // Traducción de Skeleton
        if (spanishTextSkeleton) {
          try {
            const translationsSkeleton = await translateText(spanishTextSkeleton, targetLangs);
            if (translationsSkeleton) {
              translationsSkeleton.forEach((translation) => {
                const lang = translation.language;
                formState.languageTextsSkeleton[lang] = translation.translatedText || '';
              });
            }
          } catch (error) {
            console.error('Error en la traducción del Skeleton:', error);
          }
        }

        // Traducción de Itinerary
        if (spanishTextItinerary) {
          try {
            const translationsItinerary = await translateText(spanishTextItinerary, targetLangs);
            if (translationsItinerary) {
              translationsItinerary.forEach((translation) => {
                const lang = translation.language;
                formState.languageTextsItinerary[lang] = translation.translatedText || '';
              });
            }
          } catch (error) {
            console.error('Error en la traducción del Itinerary:', error);
          }
        }
      }
    },
    { immediate: true }
  );

  watch(
    () => servicesCategoriesOptions.value, // Observa cuando cambian las opciones
    (newOptions) => {
      if (newOptions.length > 0 && formState.serviceType) {
        const selectedOption = newOptions.find((option) => option.id == formState.serviceType);
        if (selectedOption) {
          formState.serviceType = selectedOption.id;
        } else {
          formState.serviceType = null;
        }
      }
    },
    { immediate: true }
  );

  watch(
    () => servicesTypesOptions.value, // Observa cuando cambian las opciones
    (newOptions) => {
      if (newOptions.length > 0 && formState.servicePrivacy) {
        const selectedOption = newOptions.find((option) => option.id == formState.servicePrivacy);
        if (selectedOption) {
          formState.servicePrivacy = selectedOption.id;
        } else {
          formState.servicePrivacy = null;
        }
      }
    },
    { immediate: true }
  );

  async function translateText(text: string, targetLangs: string[]): Promise<any> {
    try {
      isTranslating.value = true;
      const response = await translationsApi.post('default/LambdaTranslate', {
        text,
        sourceLang: 'es',
        targetLangs,
      });

      if (!response.data.success) {
        throw new Error(response.data.error || 'Error en la traducción');
      }

      return response.data.translations; // Devolvemos el arreglo de traducciones
    } catch (error) {
      console.error('Error en la API de traducción:', error);
      return null;
    } finally {
      isTranslating.value = false; // Finaliza el estado de carga
    }
  }

  function capitalizeFirstLetter(text: string): string {
    if (!text) return text;
    return text.charAt(0).toUpperCase() + text.slice(1).toLowerCase();
  }

  function getLanguageLabel(language: string): string {
    const option = languagesOptions.value.find((opt) => opt.value === language);
    return option ? capitalizeFirstLetter(option.label) : capitalizeFirstLetter(language);
  }

  const returnToReplaceService = () => {
    const currentId = router.currentRoute.value.params.id; // Obtenemos el :id desde la URL
    const currentServiceId = router.currentRoute.value.params.service_id; // Obtenemos el :service_id desde la URL
    localStorage.removeItem('stepsData');
    localStorage.removeItem('currentStep');
    filesStore.setServiceEdit(null);
    router
      .push({
        name: 'files-replace-service',
        params: {
          id: currentId, // Usamos el id actual
          service_id: currentServiceId, // Usamos el service_id actual
        },
      })
      .then(() => {
        window.location.reload();
      });
  };

  const returnToProgram = () => {
    localStorage.removeItem('stepsData');
    localStorage.removeItem('currentStep');
    filesStore.setServiceEdit(null);
    router
      .push({ name: 'files-edit', params: { id: router.currentRoute.value.params.id } })
      .then(() => {
        window.location.reload();
      });
  };

  const deleteMasterService = (service: Service) => {
    const response = filesStore.removeServiceFromEdit(service._id);
    if (response) {
      notification.success({
        message: 'Éxito',
        description: response.message,
      });
    } else {
      notification.error({
        message: 'Error',
        description: response.message,
      });
    }
  };

  const debouncedTranslateSkeleton = debounce(async (text: string, targetLangs: string[]) => {
    if (text && targetLangs.length > 0) {
      try {
        const translationsSkeleton = await translateText(text, targetLangs);
        if (translationsSkeleton) {
          translationsSkeleton.forEach((translation) => {
            const lang = translation.language;
            formState.languageTextsSkeleton[lang] = translation.translatedText || '';
          });
        }
      } catch (error) {
        console.error('Error en la traducción del Skeleton:', error);
      }
    }
  }, 1000);

  const debouncedTranslateItinerary = debounce(async (text: string, targetLangs: string[]) => {
    if (text && targetLangs.length > 0) {
      try {
        const translationsItinerary = await translateText(text, targetLangs);
        if (translationsItinerary) {
          translationsItinerary.forEach((translation) => {
            const lang = translation.language;
            formState.languageTextsItinerary[lang] = translation.translatedText || '';
          });
        }
      } catch (error) {
        console.error('Error en la traducción del Itinerary:', error);
      }
    }
  }, 1000);

  // Watch para enviar la traducción cada vez que cambie el texto en español
  watch(
    () => formState.languageTextsSkeleton['es'], // Observa el campo en español para Skeleton
    (newText) => {
      const targetLangs = formState.languages.filter((lang) => lang !== 'es'); // Filtrar idiomas seleccionados
      debouncedTranslateSkeleton(newText, targetLangs); // Llama a la función debounced
    }
  );

  watch(
    () => formState.languageTextsItinerary['es'], // Observa el campo en español para Itinerary
    (newText) => {
      const targetLangs = formState.languages.filter((lang) => lang !== 'es'); // Filtrar idiomas seleccionados
      debouncedTranslateItinerary(newText, targetLangs); // Llama a la función debounced
    }
  );

  watch(
    () => formState.serviceType,
    async (newServiceType) => {
      if (newServiceType) {
        loadingResources.value = true; // Muestra el loading mientras se espera la respuesta
        try {
          // Llama a la API para obtener las subcategorías basado en el serviceType seleccionado
          await getServicesSubCategories(newServiceType);
          servicesSubCategoriesOptions.value = serviceSubCategories.value;
          formState.serviceTime = servicesSubCategoriesOptions.value.find(
            (option) => option.id == serviceSelected.value.itinerary.service_sub_category_id
          )?.id;
        } catch (error) {
          console.error('Error al obtener las subcategorías', error);
        } finally {
          loadingResources.value = false; // Oculta el loading al finalizar la llamada
        }
      } else {
        servicesSubCategoriesOptions.value = []; // Limpia las opciones si no hay serviceType seleccionado
      }
    }
  );

  // Watch para el texto en español del skeleton
  watch(
    () => formState.languageTextsSkeleton['es'],
    (newValue) => {
      if (newValue) {
        // Si se actualiza el texto en español, actualizar el campo de validación
        formRefServiceTemporary.value?.validate('languageTextsSkeleton_es');
      }
    }
  );

  // Watch para el texto en español del itinerario
  watch(
    () => formState.languageTextsItinerary['es'],
    (newValue) => {
      if (newValue) {
        // Si se actualiza el texto en español, actualizar el campo de validación
        formRefServiceTemporary.value?.validate('languageTextsItinerary_es');
      }
    }
  );

  const handleServiceSaved = () => {
    // Emitir el evento 'goToFinalStep' para que el componente padre lo escuche
    emit('goToFinalStep');
  };

  onMounted(async () => {
    serviceSelected.value = filesStore.getServiceEdit;
    fileLanguageIso.value = filesStore.getLang || 'es';
    if (serviceSelected.value.itinerary?.name) {
      formState.serviceSupplementaryText = serviceSelected.value.itinerary.name;
    }

    if (serviceSelected.value.itinerary) {
      formState.servicePrivacy = serviceSelected.value.itinerary.service_type_id;
      formState.serviceType = serviceSelected.value.itinerary.service_category_id;
    }

    const serviceDetails = serviceSelected.value?.itinerary?.details || [];
    if (serviceDetails.length > 0) {
      formState.languages = [];
      // Si existen datos en details, recorrer cada detalle y llenar formState
      serviceDetails.forEach((detail) => {
        const languageIso = detail.language_iso;
        formState.languages.push(languageIso);
        formState.languageTextsSkeleton[languageIso] = detail.skeleton || '';
        formState.languageTextsItinerary[languageIso] = detail.itinerary || '';
      });
    } else {
      if (serviceSelected.value.itinerary?.service_summary) {
        formState.languageTextsSkeleton['es'] = serviceSelected.value.itinerary.service_summary;
      }
      if (serviceSelected.value.itinerary?.service_itinerary) {
        formState.languageTextsItinerary['es'] = serviceSelected.value.itinerary.service_itinerary;
      }
    }

    // Carga las categorías y otros datos necesarios
    await getComponents();

    loadingResources.value = false;
    filesStore.finished();
  });
</script>

<style scoped lang="scss">
  .col-name-service {
    padding-left: 40px;
  }

  .label-maestro {
    display: flex;
    align-items: center;

    svg {
      margin-right: 10px;
    }
  }

  .add-maestro {
    display: flex;
    align-items: center;

    svg {
      margin-right: 5px;
    }
  }

  .base-input {
    &-w210 {
      min-width: 210px;
    }

    &-w340 {
      min-width: 340px;
    }

    &-w660 {
      min-width: 660px;
    }

    height: 45px;
    font-size: 14px;
  }

  .translation-loading {
    color: #1466b8;
  }

  .label-required {
    display: inline-block;
    margin-inline-end: 1px;
    color: #ff4d4f;
    font-size: 16px;
    line-height: 1;
  }

  :deep(.ant-spin-container.ant-spin-blur:after) {
    background: rgb(0 0 0 / 1%) !important;
    opacity: 1 !important;
    z-index: 25;
  }

  .actions {
    display: flex;
    justify-content: flex-end;
    gap: 25px;
  }

  .btn-temporary {
    width: auto;
    height: 45px;
    padding: 12px 24px 12px 24px;
    display: flex;
    align-items: center;
    flex-wrap: nowrap;
    justify-content: center;
    font-size: 14px;
    font-weight: 600 !important;
    color: #eb5757 !important;
    border: 1px solid #eb5757 !important;

    svg {
      margin-right: 10px;
    }

    &:hover {
      color: #eb5757 !important;
      background-color: #fff6f6 !important;
      border: 1px solid #eb5757 !important;
    }
  }

  .btn-back {
    width: auto;
    height: 45px;
    padding: 12px 24px 12px 24px;
    display: flex;
    align-items: center;
    flex-wrap: nowrap;
    justify-content: center;
    font-size: 14px;
    font-weight: 600 !important;
    color: #575757 !important;
    border: 1px solid #fafafa !important;

    &:hover {
      color: #575757 !important;
      background-color: #e9e9e9 !important;
      border: 1px solid #e9e9e9 !important;
    }
  }
</style>
