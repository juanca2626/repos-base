<template>
  <div>
    <a-modal
      class="file-modal"
      :open="isOpen"
      @cancel="handleCancel"
      :footer="null"
      :keyboard="!isSaving"
      :maskClosable="!isSaving"
      :focusTriggerAfterClose="false"
      width="1028px"
    >
      <template #title>
        <div class="service-tags">
          <div class="tag-modal tag-type-service">
            {{ serviceMasterReplace?.id }} - {{ serviceMasterReplace?.name }}
          </div>
        </div>
        <a-row>
          <a-col :span="24">
            <div class="title-service-master">
              <font-awesome-icon :icon="['fas', 'repeat']" size="lg" />
              Reemplazar servicio maestro
            </div>
          </a-col>
        </a-row>
        <a-row class="mt-4">
          <a-col
            class="mb-2"
            :span="24"
            v-for="(service, index) in serviceEditSelected.itinerary?.services"
            :key="index"
            v-show="!service.isDeleted && service?._id == serviceMasterReplace?._id"
          >
            <div
              class="box-service-master-replace"
              :class="{
                'selected-service': service?._id == serviceMasterReplace?._id,
              }"
            >
              <div class="box-service-master-row">
                <div class="title-service">{{ service?.name }}</div>
                <div class="more-info" @click="toggleInfoReplace(index)">
                  <IconArrowUpCircle
                    v-if="!infoVisibleReplace[index]"
                    color="#EB5757"
                    width="1.4em"
                    height="1.4em"
                  />
                  <IconDownUpCircle
                    v-if="infoVisibleReplace[index]"
                    color="#9574AF"
                    width="1.4em"
                    height="1.4em"
                  />
                  <span
                    :class="
                      !infoVisibleReplace[index] ? 'more-info-link-up' : 'more-info-link-down'
                    "
                  >
                    Más información del compuesto
                  </span>
                </div>
                <div class="total-price">$ {{ service?.amount_cost }}</div>
              </div>
              <!-- Información adicional desplegable -->
              <div v-if="infoVisibleReplace[index]" class="additional-info">
                <a-row :gutter="16" class="mt-2">
                  <a-col :span="12">
                    <div class="additional-info-header">
                      <div class="operation-title">Operatividad</div>
                      <div class="type-service">Tipo de servicio</div>
                      <div class="category-service">Categoría</div>
                    </div>
                    <div class="additional-info-body">
                      04.00 am: Recojo de hotel en Cusco <br />
                      05.00 am: Recojo de hotel en el Valle (si es en Urubamba)<br />
                      06.10 am: Tren de Ollanta al km: 104<br />
                      ? Visita a Chacha bamba (1er complejo arqueologico)<br />
                      ? Arribo a Wiaynahuayna 2700msnm. Lugar del almuerzo (En este lugar nuestro
                      grupo podra degustar del snack que se le envie por parte)<br />
                      ? Inicio Box Lunch (Sector Wiaynahuayna)<br />
                      ? Llegada a Intipunku (Lugar desde se puede apreciar MachuPicchu si el cielo
                      esta despejado) 2730msnm.<br />
                      ? Entrada a MachuPicchu + inicio tour<br />
                      ? Estacion de bus, para tomar el bus de bajada a MachuPicchu hacia Aguas
                      Calientes<br />
                      ? Arribo al pobledo de Aguas Calientes 2040 msnm para ser acomodado en su
                      hotel.<br />
                      Fin del tour guiado en MachiPicchu.
                    </div>
                  </a-col>
                  <a-col :span="12">
                    <div class="additional-note">
                      <div class="additional-note-header">Notas</div>
                      <div class="additional-note-body">
                        Servicio sujeto a disponibilidad de camino Inca y de ingreso a MachuPichu
                        para el mismo día a las 14:00 hrs.<br />
                        No disponibleen el mes de febrero<br />
                        Tipo de servicio intenso<br />
                        No incluye trenes ni bus de bajada<br />
                      </div>
                    </div>
                  </a-col>
                </a-row>
              </div>
            </div>
          </a-col>
        </a-row>
      </template>
      <!-- Contenido del modal -->
      <div class="modal-content">
        <div v-if="masterServiceStore.getSelectedMasterServices.length > 0">
          <a-row :gutter="16" align="middle">
            <a-col :span="24">
              <a-divider style="height: 1px; background-color: #c4c4c4" />
            </a-col>
          </a-row>
          <a-row :gutter="16" align="middle">
            <a-col :span="24">
              <p class="fw-bold">Ha seleccionado</p>
            </a-col>
          </a-row>
          <a-row :gutter="16" align="middle">
            <a-col
              :span="7"
              v-for="(service, index) in masterServiceStore.getSelectedMasterServices"
              :key="index"
            >
              <div class="box-selected-service-master my-1">
                <font-awesome-icon :icon="['fas', 'file-import']" size="lg" />
                <div class="mx-1 service-master-name">
                  {{ service.name }}
                </div>
                <a-divider
                  style="height: 40px; width: 3px; background-color: #eb5757"
                  type="vertical"
                />
                <div class="mx-1 service-master-price">
                  $ {{ parseFloat(service.amount_cost).toFixed(2) }}
                </div>
                <div class="mx-1 service-master-delete" @click="handleDeleteServiceMaster(service)">
                  <font-awesome-icon :icon="['fas', 'x']" />
                </div>
              </div>
            </a-col>
          </a-row>
          <a-row :gutter="16" justify="middle" class="mt-4">
            <a-col :span="14" align="end">
              <span class="label-total-service">Total de servicios pagar:</span>
              <span class="label-total-price">$ {{ masterServiceStore.totalAmountCost }}</span>
            </a-col>
            <a-col :span="5">
              <a-button danger class="btn-cancel-add" @click="handleCancelServiceMaster">
                Cancelar
              </a-button>
            </a-col>
            <a-col :span="5">
              <a-button danger class="btn-add-service-master" @click="handleReplaceServiceMaster">
                Agregar servicio
              </a-button>
            </a-col>
          </a-row>
        </div>
        <a-row :gutter="16" align="middle">
          <a-col :span="24">
            <a-divider style="height: 1px; background-color: #c4c4c4" />
          </a-col>
        </a-row>
        <a-row :gutter="16" align="middle">
          <a-col :span="24">
            <div class="box-title"><span>Resultados de servicios</span></div>
          </a-col>
        </a-row>
        <a-row :gutter="16" align="middle" class="mt-5">
          <a-col :span="24" v-for="(service, index) in listMasterServices" :key="index">
            <div class="box-service-master">
              <div class="box-service-master-row">
                <div class="mx-2 icon-service">
                  <font-awesome-icon :icon="['fas', 'bus']" size="lg" />
                </div>
                <div class="mx-2 service-master-name">
                  {{ service?.name }}
                </div>
                <div class="mx-2 service-master-price">
                  $ {{ parseFloat(service.amount_cost).toFixed(2) }}
                </div>
                <div class="mx-2 service-selection">
                  <a-radio
                    v-model:checked="serviceMasterSelected[index]"
                    @change="toggleServiceSelection(service, index)"
                    size="lg"
                  ></a-radio>
                </div>
              </div>
              <div class="box-service-master-options">
                <div class="category-service-master">Todas las categorías</div>
                <div class="more-info-service" @click="toggleInfo(index)">
                  Más información del servicio
                </div>
              </div>
              <!-- Información adicional desplegable -->
              <div v-if="infoVisible[index]" class="additional-info">
                <a-row :gutter="16" class="mt-2">
                  <a-col :span="12">
                    <div class="additional-info-header">
                      <div class="operation-title">Operatividad</div>
                      <div class="type-service">Tipo de servicio</div>
                      <div class="category-service">Categoría</div>
                    </div>
                    <div class="additional-info-body">
                      04.00 am: Recojo de hotel en Cusco <br />
                      05.00 am: Recojo de hotel en el Valle (si es en Urubamba)<br />
                      06.10 am: Tren de Ollanta al km: 104<br />
                      ? Visita a Chacha bamba (1er complejo arqueologico)<br />
                      ? Arribo a Wiaynahuayna 2700msnm. Lugar del almuerzo (En este lugar nuestro
                      grupo podra degustar del snack que se le envie por parte)<br />
                      ? Inicio Box Lunch (Sector Wiaynahuayna)<br />
                      ? Llegada a Intipunku (Lugar desde se puede apreciar MachuPicchu si el cielo
                      esta despejado) 2730msnm.<br />
                      ? Entrada a MachuPicchu + inicio tour<br />
                      ? Estacion de bus, para tomar el bus de bajada a MachuPicchu hacia Aguas
                      Calientes<br />
                      ? Arribo al pobledo de Aguas Calientes 2040 msnm para ser acomodado en su
                      hotel.<br />
                      Fin del tour guiado en MachiPicchu.
                    </div>
                  </a-col>
                  <a-col :span="12">
                    <div class="additional-note">
                      <div class="additional-note-header">Notas</div>
                      <div class="additional-note-body">
                        Servicio sujeto a disponibilidad de camino Inca y de ingreso a MachuPichu
                        para el mismo día a las 14:00 hrs.<br />
                        No disponibleen el mes de febrero<br />
                        Tipo de servicio intenso<br />
                        No incluye trenes ni bus de bajada<br />
                      </div>
                    </div>
                  </a-col>
                </a-row>
              </div>
            </div>
          </a-col>
        </a-row>
        <a-row :gutter="16" align="middle" class="mt-5" v-if="listMasterServices.length > 0">
          <a-col :span="24" align="center">
            <BasePagination
              v-model:current="pagination.current"
              v-model:pageSize="pagination.pageSize"
              :total="pagination.total"
              :disabled="listMasterServices?.length === 0"
              :show-quick-jumper="true"
              :show-size-changer="true"
              @change="onChange"
            />
          </a-col>
        </a-row>
        <a-row :gutter="16" align="middle" justify="center" class="mt-4" v-if="loading">
          <a-col :span="24" class="text-center">
            <a-spin size="large" />
            <p>Buscando servicios...</p>
          </a-col>
        </a-row>
        <a-empty
          v-if="!loading && listMasterServices.length === 0"
          description="No se encontraron resultados"
        />
      </div>
    </a-modal>
  </div>
</template>

<script setup lang="ts">
  import { onMounted, reactive, ref, watch } from 'vue';
  import type { Service } from '@/components/files/temporary/interfaces/service.interface';
  import { useFilesStore } from '@/stores/files';
  import { useMasterServiceStore } from '@/components/files/temporary/store/masterServiceStore';
  import BasePagination from '@/components/files/reusables/BasePagination.vue';
  import type { PaginationInterface } from '@/modules/negotiations/interfaces/pagination.interface';
  import { notification } from 'ant-design-vue';
  import IconArrowUpCircle from '@/components/icons/IconArrowUpCircle.vue';
  import IconDownUpCircle from '@/components/icons/IconDownUpCircle.vue';

  const serviceEditSelected = ref<Record<number, boolean>>({});
  const serviceMasterSelected = ref<boolean[]>([]); // Mantengo la variable
  const listMasterServices = ref([]);
  const filesStore = useFilesStore();
  const serviceMasterReplace = ref(filesStore.getServiceMasterReplace);
  const masterServiceStore = useMasterServiceStore();
  const loading = ref(false);
  const infoVisible = ref<{ [key: number]: boolean }>({});
  const infoVisibleReplace = ref<{ [key: number]: boolean }>({});
  const pagination = ref<PaginationInterface>({
    current: 1,
    pageSize: 10,
    total: 0,
  });

  defineProps({
    isOpen: {
      type: Boolean,
      required: true,
    },
  });

  const formState = reactive({
    type_service: null,
    filterSearch: '',
  });

  const resetFormState = () => {
    Object.assign(formState, {
      type_service: null,
      filterSearch: '',
    });
  };

  const emit = defineEmits(['update:isOpen', 'submit']);

  const handleSearchMaster = async (page: number = 1, pageSize: number = 10) => {
    try {
      loading.value = true;

      const currentPage = isNaN(page) ? 1 : page;
      const currentPageSize = isNaN(pageSize) ? 10 : pageSize;

      const response = await filesStore.searchMasterServices({
        type_service: formState.type_service,
        filter: formState.filterSearch,
        page: currentPage,
        pageSize: currentPageSize,
      });
      if (response && response.success && response.data.data.length > 0) {
        listMasterServices.value = response.data.data;
        pagination.value = {
          current: response.data.pagination.current_page,
          pageSize: response.data.pagination.per_page,
          total: response.data.pagination.total,
        };
      }
      loading.value = false;
    } catch (error) {
      loading.value = false;
      console.error('Error al crear el file:', error);
    }
  };

  const toggleServiceSelection = (service: Service, index: number) => {
    // Desmarcar todos los elementos en serviceMasterSelected
    serviceMasterSelected.value = serviceMasterSelected.value.map(() => false);

    // Marcar solo el índice actual
    serviceMasterSelected.value[index] = true;

    // Actualizar el store con el servicio seleccionado
    masterServiceStore.clearSelectedMasterServices(); // Limpiar selecciones anteriores
    masterServiceStore.addService(service); // Agregar el servicio seleccionado
  };

  const handleCancel = () => {
    listMasterServices.value = [];
    serviceMasterSelected.value = [];
    loading.value = false;
    masterServiceStore.clearSelectedMasterServices();
    resetFormState();
    emit('update:isOpen', false);
  };

  const handleCancelServiceMaster = () => {
    masterServiceStore.clearSelectedMasterServices();
    serviceMasterSelected.value = [];
    resetFormState();
  };

  const handleDeleteServiceMaster = (service: Service) => {
    masterServiceStore.removeService(service._id);

    const serviceIndex = listMasterServices.value.findIndex((item) => item._id === service._id);

    // Deseleccionar el checkbox en `serviceMasterSelected`
    if (serviceIndex !== -1) {
      serviceMasterSelected.value[serviceIndex] = false;
    }
  };

  const handleReplaceServiceMaster = () => {
    const selectedServices = masterServiceStore.getSelectedMasterServices;
    if (selectedServices.length === 0 || !serviceMasterReplace.value) {
      notification.error({
        message: 'Error',
        description: 'No se seleccionó un nuevo servicio o no hay servicio para reemplazar.',
      });
      return;
    }
    const newService = selectedServices[0]; // Solo reemplazamos con el primer servicio seleccionado

    // Caso 1: Si el servicio actual es nuevo (isNew = true)
    if (serviceMasterReplace.value.isNew) {
      //Actualizar todos los servicios que apuntan a este como replacedBy
      const itineraryServices = filesStore.getServiceEdit.itinerary.services;
      itineraryServices.forEach((service) => {
        // Si algún servicio tiene reemplazado por el que estamos reemplazando, actualizarlo
        if (service.isReplaced && service.replacedBy == serviceMasterReplace.value._id) {
          service.replacedBy = newService._id;
        }
      });

      // Eliminar completamente el servicio actual
      filesStore.removeServiceFromEdit(serviceMasterReplace.value._id);

      // Agregar el nuevo servicio como nuevo al itinerario
      filesStore.addSelectedMasterServicesToItinerary();
    } else {
      // Caso 2: Si el servicio actual no es nuevo (isNew = false)
      // Marcar el servicio actual como reemplazado y eliminado
      serviceMasterReplace.value.isDeleted = true;
      serviceMasterReplace.value.isReplaced = true;
      serviceMasterReplace.value.replacedBy = newService._id; // ID del nuevo servicio

      // Añadir el nuevo servicio en la posición del servicio original
      const serviceIndex = filesStore.getServiceEdit.itinerary.services.findIndex(
        (service) => service._id === serviceMasterReplace.value._id
      );

      if (serviceIndex !== -1) {
        filesStore.getServiceEdit.itinerary.services.splice(serviceIndex, 1, newService);

        // Añadir el servicio original al final con `isDeleted = true`
        filesStore.getServiceEdit.itinerary.services.push(serviceMasterReplace.value);
      }

      // Agregar el nuevo servicio al itinerario
      // newService.isNew = true; // Marcar el nuevo servicio como "nuevo"
      // filesStore.addSelectedMasterServicesToItinerary();
    }

    // Limpiar la selección de servicios en el store
    masterServiceStore.clearSelectedMasterServices();

    // Limpiar la selección de radio y cerrar el modal
    serviceMasterSelected.value = [];
    resetFormState();
    emit('update:isOpen', false);
  };

  const onChange = (page: number, perSize: number) => {
    handleSearchMaster(page, perSize);
  };

  watch(
    () => filesStore.getServiceMasterReplace,
    (newService) => {
      serviceMasterReplace.value = newService;
      handleSearchMaster();
    }
  );

  const toggleInfo = (index: number) => {
    infoVisible.value[index] = !infoVisible.value[index];
  };

  const toggleInfoReplace = (index: number) => {
    infoVisibleReplace.value[index] = !infoVisibleReplace.value[index];
  };

  onMounted(() => {
    serviceEditSelected.value = filesStore.getServiceEdit;
  });
</script>

<style scoped lang="scss">
  .file-modal {
    .fw-bold {
      font-weight: bold;
      font-size: 16px;
    }

    .title-service-master {
      font-family: 'Montserrat', sans-serif;
      font-weight: 700;
      font-size: 24px;
      letter-spacing: -0.01em;
      color: #212529;
      text-align: left;
      display: flex;
      align-items: center;

      svg {
        color: #eb5757;
        margin-right: 10px;
      }
    }

    .service-tags {
      display: flex;
      gap: 35px;
      top: -20px;
      position: relative;
      justify-content: flex-end;
      margin-right: 20px;

      .tag-modal {
        color: #fff;
        padding: 10px 18px;
        border-bottom-left-radius: 5px;
        border-bottom-right-radius: 5px;
        font-size: 14px;
        font-weight: 500;
        margin-bottom: 5px;
      }

      .tag-type-service {
        background-color: #ff4d4f;
      }
    }

    .box-selected-service-master {
      display: flex;
      background-color: #fafafa;
      border-radius: 8px;
      padding: 8px;
      flex-wrap: nowrap;
      align-content: center;
      justify-content: center;
      align-items: center;

      .service-master-name {
        font-size: 16px;
        font-weight: 500;
        color: #2e2e2e;
      }

      .service-master-price {
        font-size: 14px;
        font-weight: 600;
        color: #2e2e2e;
        width: 35%;
      }

      .service-master-delete {
        font-size: 14px;
        font-weight: 600;
        color: #c4c4c4;
        width: 15%;
        cursor: pointer;
        text-align: center;
        margin-left: 10px;

        svg {
          margin-right: 0;
          color: #c4c4c4;
        }
      }

      svg {
        margin-right: 10px;
      }
    }

    .btn-add-service-master {
      font-family: 'Montserrat', sans-serif;
      background-color: #eb5757;
      border-color: #eb5757;
      color: #ffffff;
      height: 54px;
      font-size: 16px;
      width: 100%;
    }

    .btn-cancel-add {
      font-family: 'Montserrat', sans-serif;
      background-color: #fafafa;
      border-color: #fafafa;
      color: #575757;
      font-weight: 500;
      font-size: 16px;
      height: 54px;
      width: 100%;
    }

    .label-total-service {
      font-family: 'Montserrat', sans-serif;
      font-weight: 700;
      font-size: 18px;
      color: #2e2e2e;
    }

    .label-total-price {
      font-family: 'Montserrat', sans-serif;
      font-weight: 700;
      font-size: 24px;
      color: #eb5757;
      margin-left: 10px;
    }

    .box-title {
      padding: 10px;
      background-color: #fafafa;

      span {
        font-family: 'Montserrat', sans-serif;
        font-weight: 700;
        font-size: 18px;
        color: #3d3d3d;
        margin-left: 10px;
      }
    }

    .box-service-master {
      background-color: #fafafa;
      border-radius: 8px;
      padding: 18px;
      margin-bottom: 25px;
      border-bottom: 1px solid #e9e9e9;

      .icon-service {
        width: 5%;
        text-align: center;
      }

      .service-master-name {
        font-family: 'Montserrat', sans-serif;
        font-size: 14px;
        font-weight: 400;
        color: #2e2e2e;
        width: 75%;
      }

      .service-master-price {
        font-family: 'Montserrat', sans-serif;
        font-size: 16px;
        font-weight: 700;
        width: 10%;
        color: #eb5757;
      }

      .service-selection {
        font-family: Montserrat, serif;
        font-size: 14px;
        font-weight: 400;
        color: #2e2e2e;
        width: 10%;
      }

      .box-service-master-row {
        display: flex;
        flex-wrap: nowrap;
        align-content: center;
        justify-content: center;
        align-items: center;
      }

      .box-service-master-options {
        font-family: Montserrat, serif;
        display: flex;
        flex-direction: row;
        align-items: center;
        gap: 15px;
        justify-content: flex-start;
        flex-wrap: nowrap;
        align-content: center;
        margin-left: 6%;
        margin-top: 10px;

        .category-service-master {
          font-family: Montserrat, serif;
          text-align: center;
          width: auto;
          padding: 2px 15px;
          height: auto;
          font-size: 12px;
          font-weight: 700;
          color: #ffffff;
          background-color: #e0453d;
          border-radius: 6px;
        }

        .more-info-service {
          font-family: Montserrat, serif;
          font-size: 12px;
          font-weight: 500;
          color: #eb5757;
          text-decoration: underline;
          text-underline-position: under;
          text-underline-offset: 1px;
          cursor: pointer;
        }
      }

      .additional-info {
        background-color: #ffffff;
        padding: 20px 25px;
        margin-top: 30px;
        border-radius: 6px;
        font-size: 14px;
        border: 1px solid #cfd1d5;
        margin-bottom: 10px;

        .additional-info-header {
          display: flex;
          flex-direction: row;
          align-items: center;
          gap: 15px;
          justify-content: flex-start;
          flex-wrap: nowrap;
          align-content: center;

          .operation-title {
            font-family: Montserrat, serif;
            font-size: 16px;
            font-weight: 700;
            line-height: 31px;
            letter-spacing: -0.01em;
            text-align: left;
            text-underline-position: from-font;
            text-decoration-skip-ink: none;
          }

          .type-service {
            text-align: center;
            width: auto;
            padding: 2px 15px;
            height: auto;
            font-size: 11px;
            font-weight: 700;
            color: #ffffff;
            background-color: #e0453d;
            border-radius: 6px;
          }

          .category-service {
            text-align: center;
            width: auto;
            padding: 2px 15px;
            height: auto;
            font-size: 11px;
            font-weight: 700;
            color: #ffffff;
            background-color: #ffc107;
            border-radius: 6px;
          }
        }

        .additional-info-body,
        .additional-note-body {
          font-family: Montserrat, serif !important;
          font-size: 12px;
          font-weight: 400;
        }

        .additional-note {
          background-color: #fafafa;
          padding: 15px 20px;
          border-radius: 6px;

          .additional-note-header {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 10px;
          }
        }
      }
    }

    .box-service-master-replace {
      font-family: Montserrat, serif;
      font-weight: 500;
      background-color: #e9e9e9;
      border-radius: 8px;
      padding: 10px 20px;
      color: #c4c4c4;

      .box-service-master-row {
        display: flex;
        flex-wrap: nowrap;
        align-content: center;
        justify-content: center;
        align-items: center;
      }

      .box-service-master-options {
        display: flex;
        flex-direction: row;
        align-items: center;
        gap: 15px;
        justify-content: flex-start;
        flex-wrap: nowrap;
        align-content: center;
        margin-left: 6%;
        margin-top: 10px;
      }

      .additional-info {
        background-color: #ffffff;
        padding: 20px 25px;
        margin-top: 30px;
        border-radius: 6px;
        font-size: 14px;
        border: 1px solid #cfd1d5;
        margin-bottom: 10px;

        .additional-info-header {
          display: flex;
          flex-direction: row;
          align-items: center;
          gap: 15px;
          justify-content: flex-start;
          flex-wrap: nowrap;
          align-content: center;

          .operation-title {
            font-family: Montserrat, serif;
            font-size: 16px;
            font-weight: 700;
            line-height: 31px;
            letter-spacing: -0.01em;
            text-align: left;
            text-underline-position: from-font;
            text-decoration-skip-ink: none;
          }

          .type-service {
            text-align: center;
            width: auto;
            padding: 2px 15px;
            height: auto;
            font-size: 11px;
            font-weight: 700;
            color: #ffffff;
            background-color: #e0453d;
            border-radius: 6px;
          }

          .category-service {
            text-align: center;
            width: auto;
            padding: 2px 15px;
            height: auto;
            font-size: 11px;
            font-weight: 700;
            color: #ffffff;
            background-color: #ffc107;
            border-radius: 6px;
          }
        }

        .additional-info-body,
        .additional-note-body {
          font-family: Montserrat, serif !important;
          font-size: 12px;
          font-weight: 400;
          text-align: left;
        }

        .additional-note {
          background-color: #fafafa;
          padding: 15px 20px;
          border-radius: 6px;

          .additional-note-header {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 10px;
          }
        }
      }

      &.selected-service {
        background-color: #fff2f2 !important;
        color: #3d3d3d !important;

        .more-info {
          display: flex;
          flex-direction: row;
          align-items: center;
          justify-content: center;
          gap: 5px;
          font-weight: 500;
          font-size: 12px;
        }

        .total-price {
          font-weight: 600;
        }
      }

      .more-info-link-up {
        color: #eb5757 !important;
        text-decoration: underline;
        text-underline-position: under;
        text-underline-offset: 1px;
      }

      .more-info-link-down {
        color: #9574af !important;
        text-decoration: underline;
        text-underline-position: under;
        text-underline-offset: 1px;
      }

      .title-service {
        font-size: 14px;
        width: 40%;
        text-align: left;
      }

      .more-info {
        cursor: pointer;
        width: 40%;
        font-weight: 500;
        font-size: 12px;
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: center;
        gap: 5px;
      }

      .more-info-link-up {
        color: #eb5757 !important;
        text-decoration: underline;
        text-underline-position: under;
        text-underline-offset: 1px;
      }

      .more-info-link-down {
        color: #9574af !important;
        text-decoration: underline;
        text-underline-position: under;
        text-underline-offset: 1px;
      }

      .total-price {
        width: 20%;
        font-size: 16px;
        text-align: right;
      }
    }
  }
</style>
