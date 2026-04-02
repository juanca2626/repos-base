<script lang="ts" setup>
  import { computed, reactive, toRef, ref } from 'vue';
  import type { Service } from '@/quotes/interfaces/services';
  import { useQuoteServices } from '@/quotes/composables/useQuoteServices';
  import { useI18n } from 'vue-i18n';
  import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'; // Ensure FontAwesomeIcon is imported

  useI18n();
  const { services } = useQuoteServices();

  const showServicesSearch = ref(services.value);
  console.log('hola: ', showServicesSearch.value);

  // Props
  interface Props {
    service: Service;
  }

  const props = defineProps<Props>();

  // Service
  const service = toRef(props, 'service');

  const name = computed(() => {
    const translation = service.value.service_translations[0];
    return translation.name ? translation.name : 'No name available';
  });

  const price = computed(() => {
    return service.value.service_rate[0]?.service_rate_plans[0]?.price_adult ?? 'N/A';
  });

  const value = ref<number>(1);
  const radioStyle = reactive({
    display: 'flex',
    height: '30px',
    lineHeight: '30px',
  });
</script>
<template>
  <div class="services-items py-4">
    <div class="list-header">
      <p class="">Resultados de los servicios</p>
      <a-button class="btn btn-secondary btn-sm"> Crear servicio desde 0 </a-button>
    </div>
    <div class="list-container">
      <div class="list-item">
        <a-row class="list-item-box">
          <a-col :span="1">
            <font-awesome-icon icon="fa-solid fa-bus" class="list-item-icon" />
          </a-col>
          <a-col :span="15">
            <p class="list-item-title">{{ name }}</p>
            <div>
              <a-tag class="list-item-category">Categoria</a-tag>
              <a href="">
                Más información del servicio
                <font-awesome-icon icon="fa-regular fa-circle-question" />
                <i class="bi bi-question-circle"></i>
              </a>
            </div>
          </a-col>
          <a-col :span="7">
            <div>
              <a-radio-group v-model:value="value">
                <a-radio :style="radioStyle" :value="1" class="">
                  <span> tarifa</span>
                  <span>
                    <font-awesome-icon class="pr-1" icon="fa-solid fa-dollar-sign" />
                    {{ price }}
                  </span>
                  <a-tag class="ant-tag-ok">dfd</a-tag>
                </a-radio>

                <a-radio :style="radioStyle" :value="2">
                  <span> Tipo de tarifa </span>
                  <span>
                    <font-awesome-icon class="pr-1" icon="fa-solid fa-dollar-sign" />
                    100
                  </span>
                  <a-tag class="ant-tag-rq"> RQ </a-tag>
                </a-radio>
              </a-radio-group>
            </div>
          </a-col>
          <a-col :span="1">
            <!--CheckBoxComponent :showCheckbox="true"/-->
          </a-col>
        </a-row>
      </div>
    </div>
  </div>
</template>
