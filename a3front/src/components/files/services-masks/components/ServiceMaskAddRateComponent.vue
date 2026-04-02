<template>
  <div class="files-edit">
    <a-spin
      size="large"
      :spinning="isTranslating || filesStore.isLoading || filesStore.isLoadingAsync"
    >
      <div class="d-flex justify-content-between align-items-center pb-5">
        <div class="title">
          <font-awesome-icon
            :icon="['fas', 'cube']"
            class="text-danger"
            style="padding-right: 15px"
          />
          Máscara de servicio para regalo
        </div>
        <div class="actions">
          <a-button
            class="btn-default btn-back"
            type="default"
            v-on:click="returnToProgram()"
            :loading="
              filesStore.isLoadingAsync || filesStore.isLoading || serviceMaskStore.isLoading
            "
            size="large"
          >
            Ir al programa
          </a-button>
          <a-button
            danger
            class="text-600 btn-temporary"
            type="default"
            v-on:click="returnToReplaceService()"
            :loading="
              filesStore.isLoadingAsync || filesStore.isLoading || serviceMaskStore.isLoading
            "
            size="large"
          >
            Volver a modificar servicio
          </a-button>
        </div>
      </div>
      <a-form layout="vertical" :model="formState" :rules="rules" ref="formRefServiceMask">
        <a-row>
          <a-col :span="24" class="mt-5">
            <a-card class="files-edit__service-detail-card">
              <a-row :gutter="16" align="middle" justify="space-between">
                <a-col :span="17">
                  <div class="col-date">
                    <font-awesome-icon :icon="['fas', 'cube']" size="xl" />
                    <font-awesome-icon :icon="['fas', 'gift']" size="xl" style="color: #979797" />
                    <div class="service-mask-name">{{ serviceMask?.name }}</div>
                    <div>
                      <BaseDatePicker
                        name="dateInit"
                        v-model:value="formState.dateInit"
                        :show-time="false"
                        format="DD/MM/YYYY"
                        style="width: 100%"
                        :rules="rules.dateInit"
                        :disabledDate="disabledDate"
                      />
                    </div>
                  </div>
                </a-col>
                <a-col :span="7">
                  <div class="col-price">
                    <div class="price-label">Precio de venta:</div>
                    <div class="price-value">
                      <font-awesome-icon :icon="['fas', 'dollar-sign']" style="color: #eb5757" />
                      <div class="mx-2 price">{{ formState.priceSale }}</div>
                    </div>
                  </div>
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
                      <span class="title-new-mask-service">Costo del servicio</span>
                    </a-divider>
                  </a-col>
                </a-row>
              </div>
              <a-row :gutter="16" align="middle" justify="space-between">
                <a-col span="8">
                  <a-row :gutter="16" align="middle" justify="start">
                    <a-col :span="7" class="text-left"> El costo es:</a-col>
                    <a-col :span="17">
                      <a-form-item label="" name="typeCost">
                        <a-radio-group v-model:value="formState.typeCost">
                          <a-radio value="total">Total</a-radio>
                          <a-radio value="person">Por persona</a-radio>
                        </a-radio-group>
                      </a-form-item>
                    </a-col>
                  </a-row>
                </a-col>
                <a-col span="16">
                  <a-row :gutter="16" align="middle" justify="start">
                    <a-col :span="8">
                      Ingresa el costo del servicio $ <span class="label-required">*</span>
                    </a-col>
                    <a-col :span="16">
                      <a-form-item label="" name="priceCost" :rules="rules.priceCost">
                        <a-input-number
                          v-model:value="formState.priceCost"
                          class="base-input"
                          placeholder="Ingresa el costo del servicio"
                          style="width: 100%"
                          size="small"
                        />
                      </a-form-item>
                    </a-col>
                  </a-row>
                </a-col>
              </a-row>
              <div class="header-card mt-5">
                <a-row class="mb-4">
                  <a-col span="24">
                    <a-divider
                      orientation="left"
                      orientation-margin="0px"
                      style="margin-bottom: 5px"
                    >
                      <span class="title-new-mask-service">Descripcion del servicio</span>
                    </a-divider>
                  </a-col>
                </a-row>
              </div>
              <a-row :gutter="16" align="top" justify="space-between">
                <a-col :span="24">
                  <a-row :gutter="16" align="top" justify="start">
                    <a-col :span="8" class="text-right">
                      Descripcion del servicio <span class="label-required">*</span>
                    </a-col>
                    <a-col :span="16">
                      <a-form-item label="" name="description">
                        <a-textarea
                          :rows="4"
                          placeholder="Tamaño basado en líneas de contenido"
                          v-model:value="formState.description"
                          :rules="rules.description"
                        />
                      </a-form-item>
                    </a-col>
                  </a-row>
                </a-col>
              </a-row>
              <a-row :gutter="16" align="center" justify="center">
                <a-col :span="24" class="text-center mt-5">
                  <a-form-item label="" name="additional">
                    <a-checkbox-group
                      size="large"
                      v-model:value="formState.additional"
                      @change="handleAdditionalsChange"
                    >
                      <a-checkbox value="skeleton">Agregar skeleton</a-checkbox>
                      <a-checkbox value="itinerary">Agregar itinerario</a-checkbox>
                      <a-checkbox value="passengerAssignment">
                        Agregar asignación de pasajeros
                      </a-checkbox>
                    </a-checkbox-group>
                  </a-form-item>
                </a-col>
              </a-row>
              <div
                class="header-card mt-5"
                v-if="
                  formState.additional.includes('skeleton') ||
                  formState.additional.includes('itinerary') ||
                  formState.additional.includes('passengerAssignment')
                "
              >
                <a-row class="mb-4">
                  <a-col span="24">
                    <a-divider
                      orientation="left"
                      orientation-margin="0px"
                      style="margin-bottom: 5px"
                    >
                      <span class="title-new-mask-service">Adicionales</span>
                    </a-divider>
                  </a-col>
                </a-row>
              </div>

              <!-- Adicionales -->
              <a-row
                justify="space-between"
                v-if="
                  formState.additional.includes('skeleton') ||
                  formState.additional.includes('itinerary')
                "
              >
                <a-col :span="24" class="mb-4">
                  <a-collapse
                    class="mb-3"
                    :activeKey="activeAdditionalKeys"
                    @change="handleCollapseChange"
                    style="width: 100%"
                  >
                    <a-collapse-panel key="skeleton_itinerary" :show-arrow="false">
                      <template #header>
                        <div class="collapse-header-title">
                          <font-awesome-icon
                            :icon="['fas', 'square-check']"
                            style="color: #eb5757"
                            size="xl"
                            class="mx-2"
                            @click="handleSkeletonItineraryUncheck(activeAdditionalKeys)"
                          />
                          Agregar Skeleton / Itinerario
                        </div>
                      </template>
                      <template #extra>
                        <div>
                          <font-awesome-icon :icon="['fas', 'chevron-down']" />
                        </div>
                      </template>
                      <a-row :gutter="16" class="mb-5">
                        <a-col span="4">
                          <a-card class="language-card">
                            <a-card-grid class="language-card__grid">
                              <div class="label-lang-file">Idioma del file</div>
                              <div class="text-lang-file">
                                {{ fileLanguageSelected?.label }}
                              </div>
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
                            <a-tab-pane
                              key="1"
                              tab="Skeleton"
                              v-if="formState.additional.includes('skeleton')"
                            >
                              <a-row :gutter="16" align="middle" justify="space-between">
                                <a-col :span="24">
                                  <div class="info-service-mask mb-4">
                                    Escribe el texto del servicio que deseas reemplazar en el
                                    Skeleton
                                  </div>
                                </a-col>
                              </a-row>
                              <div v-for="(language, index) in formState.languages" :key="index">
                                <a-row class="mb-4">
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
                            <a-tab-pane
                              key="2"
                              tab="Itinerario"
                              v-if="formState.additional.includes('itinerary')"
                            >
                              <a-row :gutter="16" align="middle" justify="space-between">
                                <a-col :span="24">
                                  <div class="info-service-mask mb-4">
                                    Escribe el texto del servicio que deseas reemplazar en el
                                    Itinerario
                                  </div>
                                </a-col>
                              </a-row>
                              <div v-for="(language, index) in formState.languages" :key="index">
                                <a-row class="mb-4">
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
                    </a-collapse-panel>
                  </a-collapse>
                </a-col>
              </a-row>
              <a-row
                justify="space-between"
                v-if="formState.additional.includes('passengerAssignment')"
              >
                <a-col :span="24" class="mb-4">
                  <a-collapse
                    class="mb-3"
                    :activeKey="activeAdditionalKeys"
                    @change="handleCollapseChange"
                    style="width: 100%"
                  >
                    <a-collapse-panel key="passengerAssignment" :show-arrow="false">
                      <template #header>
                        <div class="collapse-header-title">
                          <font-awesome-icon
                            :icon="['fas', 'square-check']"
                            style="color: #eb5757"
                            size="xl"
                            class="mx-2"
                            @click="handlePassengerAssignmentUncheck(activeAdditionalKeys)"
                          />
                          Agregar asignacion de pasajeros
                        </div>
                      </template>
                      <template #extra>
                        <div>
                          <font-awesome-icon :icon="['fas', 'chevron-down']" />
                        </div>
                      </template>
                      <a-row :gutter="24" align="middle" justify="space-between">
                        <a-col span="4">
                          <div>Seleciona pasajeros</div>
                        </a-col>
                        <a-col span="20">
                          <base-select-multiple
                            style="width: 100%"
                            name="passengers"
                            label=""
                            placeholder="Selecciona un pasajero"
                            :allowClear="true"
                            size="large"
                            :fieldNames="{ label: 'label', value: 'id' }"
                            :options="passengersOptions"
                            :maxTagCount="10"
                            :multiple="true"
                            :comma="false"
                            v-model:value="formState.passengers"
                            :rules="rules.passengers"
                          />
                        </a-col>
                      </a-row>
                    </a-collapse-panel>
                  </a-collapse>
                </a-col>
              </a-row>
            </a-card>
          </a-col>
          <a-col :span="24" style="text-align: right; margin-top: 50px">
            <div class="actions">
              <a-button
                class="btn-back"
                type="default"
                :loading="
                  filesStore.isLoading || filesStore.isLoadingAsync || serviceMaskStore.loading
                "
                size="large"
                @click="goToPreviousStep()"
              >
                Atras
              </a-button>
              <a-button
                type="primary"
                class="btn-temporary"
                default
                html-type="submit"
                :loading="
                  isTranslating ||
                  filesStore.isLoading ||
                  filesStore.isLoadingAsync ||
                  serviceMaskStore.loading
                "
                size="large"
                @click="handleFormSubmit"
              >
                Crear regalo
              </a-button>
            </div>
          </a-col>
        </a-row>
      </a-form>
    </a-spin>
  </div>
</template>
<script setup lang="ts">
  import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
  import { useFilesStore } from '@/stores/files';
  import { useLanguagesStore } from '@/stores/global/index.js';
  import { computed, defineEmits, onMounted, reactive, ref, watch } from 'vue';
  import { useRouter } from 'vue-router';
  import BaseDatePicker from '@/components/files/reusables/BaseDatePicker.vue';
  import { type FormInstance, notification } from 'ant-design-vue';
  import translationsApi from '@/utils/translationsApi';
  import BaseSelectMultiple from '@/components/files/reusables/BaseSelectMultiple.vue';
  import { useServiceMaskStore } from '@/components/files/services-masks/store/serviceMaskStore';
  import dayjs from 'dayjs';
  import type { Rule } from 'ant-design-vue/es/form';
  import { debounce } from 'lodash';

  const filesStore = useFilesStore();
  const languagesStore = useLanguagesStore();
  const serviceMaskStore = useServiceMaskStore();
  const isTranslating = ref(false); // Estado para indicar que la traducción está en progreso
  const router = useRouter();
  const emit = defineEmits(['nextStep', 'backStep']);

  const formRefServiceMask = ref<FormInstance | null>(null);
  const formState = reactive({
    typeCost: 'person',
    dateInit: null,
    priceCost: 0,
    priceSale: 0,
    description: '',
    languages: ['es'],
    languageTextsName: {},
    languageTextsSkeleton: {},
    languageTextsItinerary: {},
    passengers: [],
    additional: [],
    createdAtTime: null,
  });

  const validatePrice = (_: unknown, value: number) => {
    if (typeof value == 'number' && !isNaN(value)) {
      if (value >= 1) {
        return Promise.resolve();
      } else {
        return Promise.reject('El precio costo debe ser mayor a 0');
      }
    } else {
      return Promise.reject('El precio costo debe ser un número');
    }
  };

  const rules = {
    dateInit: [{ required: true, message: 'Debe seleccionar la fecha' }],
    priceCost: [
      {
        required: true,
        message: 'Debe ingresar el precio costo',
      },
      {
        validator: validatePrice,
        trigger: 'change',
      },
    ],
    description: [
      {
        required: true,
        message: 'Debe ingresar una descripción',
      },
    ],
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
    passengers: [
      {
        required: true,
        message: 'Debe seleccionar al menos un pasajero',
        trigger: 'change',
      },
    ],
  };

  const activeAdditionalKeys = ref([]); // Claves activas en el a-collapse
  const activeKey = ref('1');
  const fileLanguageIso = ref('es');
  const languagesOptions = ref([]);
  const passengersOptions = ref([]);
  const fileLanguageSelected = ref({
    value: 'es',
    label: 'ESPAÑOL',
  });
  const serviceMask = ref({});
  const file = ref({});
  const markup = ref(0);

  const handleAdditionalsChange = (selectedOptions) => {
    if (selectedOptions.includes('skeleton') || selectedOptions.includes('itinerary')) {
      activeAdditionalKeys.value = ['skeleton_itinerary'];
      if (selectedOptions.includes('skeleton')) {
        activeKey.value = '1';
      } else if (selectedOptions.includes('itinerary')) {
        activeKey.value = '2';
      }
    } else {
      activeAdditionalKeys.value = [];
    }
  };

  const handleCollapseChange = (activeKeys) => {
    activeAdditionalKeys.value = activeKeys;
  };

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

  function getLanguageLabel(language: string): string {
    const option = languagesOptions.value.find((opt) => opt.value === language);
    return option ? capitalizeFirstLetter(option.label) : capitalizeFirstLetter(language);
  }

  function capitalizeFirstLetter(text: string): string {
    if (!text) return text;
    return text.charAt(0).toUpperCase() + text.slice(1).toLowerCase();
  }

  const formattedLanguagesOptions = computed(() =>
    languagesOptions.value.map((lang) => ({
      ...lang,
      disabled: lang.value === 'es',
    }))
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

  const handleFormSubmit = async () => {
    try {
      await formRefServiceMask.value?.validate();
      serviceMaskStore.setServiceMaskRate(formState);
      serviceMaskStore.updateTextServiceEdit(
        formattedLanguagesOptions,
        formState.languageTextsSkeleton,
        formState.languageTextsItinerary
      );
      await serviceMaskStore.saveServiceMask();
      if (serviceMaskStore.getIsCreatedServiceMask) {
        emit('goToFinalStep');
      }
    } catch (errorInfo) {
      console.log(errorInfo);
      notification.error({
        message: 'Error de validación',
        description: 'Por favor, complete todos los campos requeridos',
      });
    }
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

  const disabledDate = (current) => {
    return current && current < dayjs().startOf('day');
  };

  const goToPreviousStep = () => {
    emit('backStep');
  };

  const handleSkeletonItineraryUncheck = () => {
    activeAdditionalKeys.value = activeAdditionalKeys.value.filter(
      (key) => key !== 'skeleton' && key !== 'itinerary' && key !== 'skeleton_itinerary'
    );
    formState.additional = activeAdditionalKeys.value;
  };

  const handlePassengerAssignmentUncheck = () => {
    activeAdditionalKeys.value = activeAdditionalKeys.value.filter(
      (key) => key !== 'passengerAssignment'
    );
    formState.additional = activeAdditionalKeys.value;
  };

  watch(
    () => formState.priceCost,
    (newPriceCost) => {
      if (newPriceCost && !isNaN(newPriceCost)) {
        // Calcula el precio de venta con el markup
        formState.priceSale = (newPriceCost * (1 + markup.value / 100)).toFixed(2);
      } else {
        formState.priceSale = 0; // Reinicia el precio de venta si el costo no es válido
      }
    },
    { immediate: true } // Ejecuta al inicio para sincronizar valores iniciales
  );

  // Sincroniza las claves activas del a-collapse con las opciones seleccionadas
  watch(
    () => formState.additional,
    (newAdditional) => {
      if (newAdditional.includes('skeleton') || newAdditional.includes('itinerary')) {
        activeAdditionalKeys.value.push('skeleton_itinerary');
      }

      if (newAdditional.includes('passengerAssignment')) {
        activeAdditionalKeys.value.push('passengerAssignment');
      }
    },
    { immediate: true }
  );

  watch(
    () => languagesStore.getLanguages,
    (newLanguages) => {
      languagesOptions.value = newLanguages;
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
    () => languagesStore.getAllLanguages,
    (newLanguages) => {
      // Busca el idioma cuyo valor coincide con fileLanguageIso
      fileLanguageSelected.value = newLanguages.find(
        (lang) => lang.value === fileLanguageIso.value
      );
    },
    { immediate: true }
  );

  onMounted(async () => {
    serviceMask.value = serviceMaskStore.getServiceMask;
    file.value = serviceMaskStore.getFile;
    markup.value = file.value.file.markupClient;
    fileLanguageIso.value = file.value.file.lang || 'es';
    passengersOptions.value = serviceMaskStore.getFile.passengers;
    const serviceRate = serviceMaskStore.getServiceMaskRate;
    if (Object.keys(serviceRate).length > 0) {
      formState.dateInit = dayjs(serviceRate.dateInit);
      formState.description = serviceRate?.description || '';
      formState.priceCost = serviceRate?.priceCost || 0;
      formState.priceSale = serviceRate?.priceSale || 0;
      formState.typeCost = serviceRate?.typeCost;
      formState.additional = serviceRate?.additional || [];
      formState.passengers = serviceRate?.passengers || [];
      const serviceDetails = serviceRate?.details || [];
      activeAdditionalKeys.value = formState.additional;
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
        formState.languageTextsSkeleton['es'] = '';
        formState.languageTextsItinerary['es'] = '';
      }
    }
  });
</script>
<style scoped lang="scss">
  :deep(.ant-spin-container.ant-spin-blur:after) {
    background: rgb(0 0 0 / 1%) !important;
    opacity: 1 !important;
    z-index: 25;
  }

  .collapse-header-title {
    font-family: Montserrat, sans-serif;
    font-weight: 500;
    font-size: 16px;
    color: #979797;
  }

  .actions {
    display: flex;
    justify-content: flex-end;
    gap: 25px;
  }

  .col-date {
    display: flex;
    flex-direction: row;
    flex-wrap: nowrap;
    align-content: center;
    align-items: center;
    justify-content: flex-start;
    gap: 12px;

    .service-mask-name {
      font-family: Montserrat, sans-serif;
      font-size: 18px;
      font-weight: 700;
      color: #575757;
    }
  }

  .col-price {
    display: flex;
    flex-direction: row;
    flex-wrap: nowrap;
    align-content: center;
    align-items: center;
    justify-content: flex-end;
    gap: 12px;

    .price-label {
      font-family: Montserrat, sans-serif;
      font-size: 16px;
      font-weight: 700;
      color: #575757;
    }

    .price-value {
      display: flex;
      flex-direction: row;
      flex-wrap: nowrap;
      font-family: Montserrat, sans-serif;
      font-size: 16px;
      font-weight: 700;
      color: #575757;
      align-items: center;

      .price {
        display: flex;
        width: auto;
        height: 45px;
        background-color: #fafafa;
        padding: 4px 10px;
        align-items: center;
      }
    }
  }

  .btn-temporary {
    width: auto;
    height: 54px;
    padding: 12px 24px 12px 24px;
    display: flex;
    align-items: center;
    flex-wrap: nowrap;
    justify-content: center;
    font-size: 14px;
    font-weight: 600 !important;
    background-color: #eb5757 !important;
    color: #ffffff !important;

    svg {
      margin-right: 10px;
    }

    &:hover {
      color: #ffffff !important;
      background-color: #c63838 !important;
    }
  }

  .info-service-mask {
    font-family: Montserrat, sans-serif;
    font-size: 14px;
    font-weight: 400;
    color: #575757;
    background-color: #fafafa;
    height: 51px;
    padding: 15px 20px;
  }

  .ant-form-item {
    margin-bottom: 0;
  }

  .btn-back {
    width: auto;
    height: 54px;
    padding: 12px 24px 12px 24px;
    display: flex;
    align-items: center;
    flex-wrap: nowrap;
    justify-content: center;
    font-size: 14px;
    font-weight: 600 !important;
    background-color: #fafafa !important;
    color: #575757 !important;
    border: 1px solid #fafafa !important;

    &:hover {
      color: #575757 !important;
      background-color: #e9e9e9 !important;
      border: 1px solid #e9e9e9 !important;
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

    display: flex;
    align-items: center;
    height: 45px;
    font-size: 14px;
  }

  .label-required {
    display: inline-block;
    margin-inline-end: 1px;
    color: #ff4d4f;
    font-size: 16px;
    line-height: 1;
  }
</style>
