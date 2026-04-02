<template>
  <div class="files-edit">
    <a-row :gutter="16">
      <a-col :span="24">
        <div class="title-finished">{{ serviceSelected.name || '' }} agregado</div>
      </a-col>
      <a-col :span="24" class="text-center mt-5">
        <IconCircleCheck class="finished-icon" color="#1ED790" width="75.83px" height="75.83px" />
      </a-col>
      <a-col :span="24" class="text-center mt-5">
        <div class="box-detail-finished">
          <a-row :gutter="16" align="middle" justify="space-between">
            <a-col :span="24">
              <div class="label-service">Detalle del regalo agregado</div>
            </a-col>
          </a-row>
          <a-row :gutter="24" align="top" justify="start" class="mt-4">
            <a-col :span="12" class="text-left">
              <div>
                <span class="label-detail">Proveedor del regalo:</span>
                <span class="label-detail-name">{{ serviceSuppliers.supplier_name || '-' }}</span>
              </div>
              <div class="mt-2">
                <span class="label-detail">Fecha de entrega:</span>
                <span class="label-detail-name">{{ formatDate(serviceSelected.date_from) }}</span>
              </div>
              <div class="mt-1" v-if="serviceRate.passengers && serviceRate.passengers.length > 0">
                <span class="label-detail mr-1">Pasajeros involucrados:</span>
                <div v-for="passenger in serviceRate.passengers || []" :key="passenger.id">
                  <font-awesome-icon
                    :icon="['fas', 'user']"
                    style="font-size: 0.8rem"
                    v-if="passenger.type === 'ADL'"
                    class="mx-2"
                  />
                  <font-awesome-icon
                    :icon="['fas', 'child']"
                    style="font-size: 0.8rem"
                    v-if="passenger.type === 'CHD'"
                    class="mx-2"
                  />
                  <span class="label-detail">{{ passenger.label }}</span>
                </div>
              </div>
              <div class="mt-1">
                <span class="label-detail mr-1">Detalles del servicio:</span>
                <span class="link-info" @click="showModalMoreInfo"
                  >Más información del servicio</span
                >
              </div>
            </a-col>
            <a-col :span="12" class="text-left">
              <div class="label-service-detail">
                <font-awesome-icon :icon="['fas', 'dollar-sign']" />
                Tarifas:
              </div>
              <div class="mt-1">
                <span class="label-detail-rate"
                  >Total tarifa
                  <span v-if="serviceRate.typeCost === 'person'">por persona</span>:</span
                >
                <font-awesome-icon :icon="['fas', 'user']" style="color: #1ed790 !important" />
                <span class="label-detail-rate-price">
                  USD {{ formatCurrency(serviceRate?.priceSale) }}
                </span>
              </div>
            </a-col>
          </a-row>
          <a-row :gutter="24" align="top" justify="start" class="mt-4">
            <a-col :span="24" class="text-left">
              <span class="label-title-description">Descripción del regalo:</span>
              <span class="label-service-description text-justify mx-2">
                {{ serviceRate.description || '-' }}
              </span>
            </a-col>
          </a-row>
        </div>
        <a-row :gutter="24" align="middle" justify="space-between" class="mt-5">
          <a-col :span="24" class="text-center">
            <a-button
              type="primary"
              class="btn-danger btn-close mx-2 px-4 text-600"
              default
              html-type="submit"
              size="large"
              @click="handleClose"
            >
              Cerrar
            </a-button>
          </a-col>
        </a-row>
      </a-col>
    </a-row>
    <ServiceMaskMoreInfoComponent
      :is-open="modalIsOpenInformation"
      @update:is-open="modalIsOpenInformation = $event"
    />
  </div>
</template>

<script setup lang="ts">
  import IconCircleCheck from '@/components/icons/IconCircleCheck.vue';
  import { onMounted, ref } from 'vue';
  import { useServiceMaskStore } from '@/components/files/services-masks/store/serviceMaskStore';
  import { useRouter } from 'vue-router';
  import dayjs from 'dayjs';
  import ServiceMaskMoreInfoComponent from '@/components/files/services-masks/components/ServiceMaskMoreInfoComponent.vue';

  const router = useRouter();
  const serviceMaskStore = useServiceMaskStore();
  const modalIsOpenInformation = ref(false);

  // Referencias reactivas
  const serviceSelected = ref<Record<string, any>>({});
  const serviceSuppliers = ref<Record<string, any>>({});
  const serviceRate = ref<Record<string, any>>({});

  // Manejo del botón cerrar
  const handleClose = () => {
    localStorage.removeItem('stepsData');
    localStorage.removeItem('currentStep');
    serviceMaskStore.clearServiceMask();
    serviceMaskStore.clearServiceMaskRate();
    serviceMaskStore.clearServiceMaskSupplier();
    serviceMaskStore.clearFileSelected();
    router
      .push({ name: 'files-edit', params: { id: router.currentRoute.value.params.id } })
      .then(() => window.location.reload());
  };

  // Carga inicial
  onMounted(() => {
    serviceSelected.value = serviceMaskStore.getServiceMask || {};
    serviceSuppliers.value = serviceMaskStore.getServiceMaskSupplier || {};
    serviceRate.value = serviceMaskStore.getServiceMaskRate || {};
  });

  function formatDate(date: string): string {
    return dayjs(date).format('DD - MMMM - YYYY');
  }

  // Función para formatear moneda
  function formatCurrency(value: any): string {
    console.log('Valor de priceSale:', value); // Depuración
    const numericValue = parseFloat(value); // Convertir a número
    if (isNaN(numericValue)) {
      return '0.00'; // Valor predeterminado
    }
    return numericValue.toFixed(2); // Formatear como número decimal
  }

  const showModalMoreInfo = () => {
    modalIsOpenInformation.value = true;
  };
</script>
<style scoped lang="scss">
  .title-finished {
    font-size: 36px;
    font-weight: 700;
    line-height: 50px;
    letter-spacing: -0.015em;
    text-align: center;
    color: #eb5757;
    width: 100%;
  }

  .finished-icon {
    justify-content: center;
  }

  .label-title-description {
    font-size: 12px;
    font-weight: 600;
    line-height: 19px;
    letter-spacing: 0.015em;
    text-align: left;
    color: #3d3d3d;
  }

  .label-service-description {
    font-size: 12px;
    font-weight: 400;
    line-height: 19px;
    letter-spacing: 0.015em;
    text-align: left;
    color: #3d3d3d;
  }

  .link-info {
    font-family: Montserrat, serif;
    font-size: 12px;
    font-weight: 500;
    margin-left: 10px;
    color: #eb5757;
    cursor: pointer;
    text-decoration: underline;
    text-underline-position: under;
    text-underline-offset: 1px;
  }

  .flex-row-service {
    display: flex;
    align-items: center;
    gap: 30px;
  }

  .box-detail-finished {
    padding: 40px;
    border: 1px solid #e9e9e9;
    border-radius: 6px;
    background-color: #fafafa;

    .label-service {
      font-size: 18px;
      font-weight: 700;
      line-height: 25px;
      letter-spacing: -0.01em;
      text-align: left;
      color: #eb5757;
    }

    .label-detail {
      font-family: Montserrat, serif;
      font-size: 12px;
      font-weight: 600;
      line-height: 19px;
      letter-spacing: 0.015em;
      text-align: left;
    }

    .label-detail-rate {
      font-family: Montserrat, serif;
      font-size: 16px;
      font-weight: 600;
      line-height: 19px;
      letter-spacing: 0.015em;
      text-align: left;
      margin-right: 10px;
    }

    .label-detail-rate-price {
      font-family: Montserrat, serif;
      font-size: 18px;
      font-weight: 600;
      line-height: 19px;
      letter-spacing: 0.015em;
      text-align: left;
      margin-left: 10px;
      color: #28a745;
    }

    .label-detail-name {
      font-family: Montserrat, serif;
      font-size: 12px;
      font-weight: 400;
      line-height: 19px;
      letter-spacing: 0.015em;
      text-align: left;
      margin-left: 10px;
    }

    .total-rate {
      font-family: Montserrat, serif;
      font-size: 12px;
      font-weight: 600;
      line-height: 19px;
      letter-spacing: 0.015em;
      text-align: left;
      color: #3d3d3d;
    }

    .label-detail-penalty {
      font-family: Montserrat, serif;
      font-size: 12px;
      font-weight: 600;
      line-height: 19px;
      letter-spacing: 0.015em;
      text-align: left;
      color: #e4b804;
    }

    .total-penalty {
      font-family: Montserrat, serif;
      font-size: 12px;
      font-weight: 400;
      line-height: 19px;
      text-align: left;
      color: #e4b804;
    }

    .label-service-detail {
      font-size: 12px;
      font-weight: 600;
      line-height: 19px;
      letter-spacing: 0.015em;
      text-align: left;
      color: #eb5757;
      margin-bottom: 5px;

      svg {
        margin-right: 0 !important;
      }
    }
  }

  .box-service-master-replace {
    display: flex;
    gap: 15px;
    flex-direction: row;
    align-items: center;
    justify-content: left;
    align-content: center;
    flex-wrap: nowrap;
    margin-top: 15px;

    .service-origin {
      font-size: 12px;
      font-weight: 400;
      line-height: 19px;
      letter-spacing: 0.015em;
      text-align: left;
      color: #737373;
      padding: 3px 6px 3px 6px;
      border-radius: 6px;
      border: 1px solid #e9e9e9;
    }

    .icon {
      color: #c4c4c4;
    }

    .service-replace {
      font-size: 12px;
      font-weight: 600;
      line-height: 19px;
      letter-spacing: 0.015em;
      text-align: left;
      color: #3d3d3d;
      padding: 3px 6px 3px 6px;
    }
  }

  .service-master-name {
    margin-left: 15px;
    margin-bottom: 5px;
  }

  .btn-close {
    font-family: Montserrat, serif;
    background-color: #eb5757;
    border-radius: 6px;
  }
</style>
