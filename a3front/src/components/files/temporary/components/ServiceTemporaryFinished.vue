<template>
  <div class="files-edit">
    <a-row :gutter="16">
      <a-col :span="24">
        <div class="title-finished">Servicio temporal creado</div>
      </a-col>
      <a-col :span="24" class="text-center mt-5">
        <IconCircleCheck class="finished-icon" color="#1ED790" width="75.83px" height="75.83px" />
      </a-col>
      <a-col :span="24" class="text-center mt-5">
        <div class="box-detail-finished">
          <a-row :gutter="16" align="middle" justify="space-between">
            <a-col :span="8">
              <div class="label-service">Detalle de servicio temporal creado</div>
            </a-col>
            <a-col :span="16">
              <a-input
                v-if="serviceSelected.itinerary"
                class="base-input"
                v-model:value="serviceSelected.itinerary.name"
                placeholder=""
                name="description"
                size="large"
                width="210"
                disabled
              />
            </a-col>
          </a-row>
          <a-row :gutter="16" align="middle" justify="start">
            <a-col :span="24" class="mt-5 text-left">
              <span class="label-detail">Servicio creado a partir de:</span>
              {{ serviceSelected.itinerary?.name_original }}
            </a-col>
          </a-row>
          <a-row :gutter="24" align="middle" justify="start" class="mt-4">
            <a-col :span="10" class="text-left">
              <div>
                <span class="label-detail">Proveedor(es):</span>
                <div v-for="supplier in serviceSuppliers" :key="supplier">
                  <font-awesome-icon :icon="['fas', 'circle']" size="2xs" />
                  {{ supplier.codigo }} - {{ supplier.razon }}
                </div>
              </div>
              <div class="mt-2">
                <span class="label-detail" style="margin-top: 5px">Fecha de creación:</span>
                {{ serviceSelected.itinerary?.created_date_at || '-' }}
                <a-divider
                  type="vertical"
                  style="height: 15px; width: 2px; background-color: #eb5757"
                />
                {{ serviceSelected.itinerary?.created_time_at || '-' }}
              </div>
              <div class="mt-1">
                <span class="label-detail mr-1">Detalles del servicio:</span>
                <span class="link-info">Más información del servicio</span>
              </div>
            </a-col>
            <a-col :span="7" class="text-left">
              <div>
                <div class="label-service-detail">
                  <IconCirclePlus color="#EB5757" width="1.2em" height="1.2em" />
                  Servicios agregados
                </div>
                <div
                  class="service-master-name"
                  v-for="(service, index) in serviceNewTemporary"
                  :key="index"
                >
                  {{ service.name }}
                </div>
                <div v-if="serviceNewTemporary.length === 0">Ninguno</div>
              </div>
              <div class="mt-3">
                <div class="label-service-detail">
                  <font-awesome-icon :icon="['far', 'trash-can']" />
                  Servicios eliminados
                </div>
                <div
                  class="service-master-name"
                  v-for="(service, index) in serviceDeletedTemporary"
                  :key="index"
                >
                  {{ service.name }}
                </div>
                <div v-if="serviceDeletedTemporary.length === 0">Ninguno</div>
              </div>
            </a-col>
            <a-col :span="7" class="text-left">
              <div class="label-service-detail">
                <font-awesome-icon :icon="['fas', 'dollar-sign']" />
                Tarifas:
              </div>
              <div class="mt-1">
                <span class="label-detail">Plan tarifario:</span>
                Regular Rate FITS
              </div>
              <a-row :gutter="24" align="middle" justify="start" class="mt-1">
                <a-col :span="12" class="text-left">
                  <div class="label-detail">Tarifa:</div>
                </a-col>
                <a-col :span="12" class="text-right">
                  <div class="total-rate">
                    USD {{ serviceSelected.itinerary?.total_amount || 0 }}
                  </div>
                </a-col>
              </a-row>
              <a-row
                :gutter="24"
                align="middle"
                justify="start"
                class="mt-1"
                v-if="serviceSelected.itinerary?.total_amount_penalties > 0"
              >
                <a-col :span="12" class="text-left">
                  <div class="label-detail-penalty">Total penalidad:</div>
                </a-col>
                <a-col :span="12" class="text-right">
                  <div class="total-penalty">
                    USD
                    {{ serviceSelected.itinerary?.total_amount_penalties || 0 }}
                  </div>
                </a-col>
              </a-row>
            </a-col>
          </a-row>
          <a-row
            :gutter="24"
            align="middle"
            justify="start"
            class="mt-4"
            v-if="serviceReplacedTemporary.length > 0"
          >
            <a-col :span="24" class="text-left">
              <div class="label-service-replace">
                <font-awesome-icon :icon="['fas', 'repeat']" />
                Servicios reemplazados
              </div>
            </a-col>
            <a-col
              :span="24"
              class="text-left"
              v-for="(service, index) in serviceReplacedTemporary"
              :key="index"
            >
              <div class="box-service-master-replace">
                <div class="service-origin">{{ service.service.name }}</div>
                <div class="icon">
                  <font-awesome-icon :icon="['fas', 'right-long']" />
                </div>
                <div class="service-replace">{{ service.service_chance.name }}</div>
              </div>
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
  </div>
</template>
<script setup lang="ts">
  import IconCircleCheck from '@/components/icons/IconCircleCheck.vue';
  import { onMounted, ref } from 'vue';
  import IconCirclePlus from '@/components/icons/IconCirclePlus.vue';
  import { useFilesStore, useSupplierStore } from '@/stores/files';
  import type { Service } from '@/components/files/temporary/interfaces/service.interface';
  import { useRouter } from 'vue-router';

  const router = useRouter();
  const serviceSelected = ref<Service>({
    itinerary: {
      name: '',
      services_new: [],
      services_new_replaced: [],
      services_deleted: [],
    },
  });

  const serviceNewTemporary = ref([]);
  const serviceDeletedTemporary = ref([]);
  const serviceReplacedTemporary = ref([]);

  const serviceSuppliers = ref([]);
  const filesStore = useFilesStore();
  const supplierStore = useSupplierStore();

  const handleClose = () => {
    localStorage.removeItem('stepsData');
    localStorage.removeItem('currentStep');
    filesStore.setServiceEdit(null);
    router
      .push({ name: 'files-edit', params: { id: router.currentRoute.value.params.id } })
      .then(() => {
        window.location.reload();
      });
  };

  const fetchSuppliersByCodes = async (codes) => {
    try {
      await supplierStore.fetchSuppliersByCodes(codes);
      const fetchedSuppliers = supplierStore.getSuppliers; // Obtenemos los proveedores desde el store
      // Convertimos la respuesta en un objeto indexado por 'codigo' para buscar fácilmente
      const suppliersData = fetchedSuppliers.reduce((acc, item) => {
        acc[item.codigo] = item;
        return acc;
      }, {});

      // Creamos la lista final de proveedores verificando cada código
      serviceSuppliers.value = codes.map((codigo) => {
        if (suppliersData[codigo]) {
          // Si encontramos el proveedor, devolvemos sus datos específicos
          const { codigo, razon, lintlx, contacts } = suppliersData[codigo];
          return { codigo, razon, lintlx, contacts };
        } else {
          // Si no se encuentra, devolvemos una estructura predeterminada
          return {
            codigo,
            razon: '',
            lintlx: '',
            contacts: [],
          };
        }
      });
    } catch (error) {
      console.error('Error al traer datos de proveedores:', error);
    }
  };

  onMounted(async () => {
    serviceSelected.value = filesStore.getServiceEdit;
    serviceNewTemporary.value = filesStore.getServiceTemporaryNew;
    serviceDeletedTemporary.value = filesStore.getServiceTemporaryDeleted;
    serviceReplacedTemporary.value = filesStore.getServiceTemporaryReplaced;

    serviceReplacedTemporary.value.forEach((tempService) => {
      // Buscar el servicio por service_id en serviceSelected.value.itinerary.services
      const foundService = serviceSelected.value.itinerary.services.find(
        (s) => s.id === tempService.service_id
      );

      if (foundService) {
        // Agregar el servicio encontrado dentro del objeto de serviceReplacedTemporary
        tempService.service = foundService;
      } else {
        console.warn(`Service with id ${tempService.service_id} not found.`);
      }
    });

    // Validamos que `serviceSelected` tenga servicios
    if (
      serviceSelected.value &&
      serviceSelected.value.itinerary &&
      Array.isArray(serviceSelected.value.itinerary.services)
    ) {
      // Creamos un Set para asegurar que los codes sean únicos
      const uniqueSuppliersSet = new Set<string>();
      // Recorremos los servicios y obtenemos los `code_request_book`
      serviceSelected.value.itinerary.services.forEach((service) => {
        // Si el servicio tiene composiciones, recorremos las composiciones
        if (Array.isArray(service.compositions)) {
          service.compositions.forEach((comp) => {
            // Verificamos que el supplier tenga code_request_book y lo añadimos al Set
            if (comp.supplier && comp.supplier.code_request_book) {
              uniqueSuppliersSet.add(comp.supplier.code_request_book);
            }
          });
        }
      });

      fetchSuppliersByCodes(Array.from(uniqueSuppliersSet));
      // Convertimos el Set en un array y lo asignamos a serviceSuppliers.value
    }
  });
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

  .label-service-replace {
    font-size: 12px;
    font-weight: 600;
    line-height: 19px;
    letter-spacing: 0.015em;
    text-align: left;
    color: #eb5757;
  }

  .link-info {
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
