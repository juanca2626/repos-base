<template>
  <div class="files-edit">
    <a-row :gutter="24" type="flex" align="middle" justify="space-between" class="mb-5">
      <a-col :span="24">
        <div class="title">
          <font-awesome-icon :icon="['far', 'message']" class="text-danger" />
          Comunicación al proveedor
        </div>
      </a-col>
      <a-col :span="24" class="mt-5">
        <div class="descripcion">
          Estás a un paso de <strong>modificar</strong> las reserva del servicio y generar
          <strong>nuevas reservas:</strong>
        </div>
      </a-col>
      <a-col :span="24" class="mt-5">
        <div class="box-communications">
          <CommunicationServiceDetails :service="serviceSelected" />

          <CommunicationSection
            v-show="getCommunicationsTemporary.reservations.length > 0"
            title="Servicios con comunicación de reserva a proveedores"
            :items="getCommunicationsTemporary.reservations"
            type="reservations"
            v-model="getCommunicationsTemporary.reservations"
            @showModalEmails="showModalEmails"
            @showCommunicationFrom="showCommunicationFrom"
          />

          <CommunicationSection
            v-show="getCommunicationsTemporary.cancellation.length > 0"
            title="Servicios con comunicación de anulación a proveedores"
            :items="getCommunicationsTemporary.cancellation"
            type="cancellation"
            v-model="getCommunicationsTemporary.cancellation"
            @showModalEmails="showModalEmails"
            @showCommunicationFrom="showCommunicationFrom"
          />

          <CommunicationSectionModification
            v-show="getCommunicationsTemporary.modification.length > 0"
            title="Servicios con comunicación para modificación"
            :items="getCommunicationsTemporary.modification"
            type="modification"
            v-model="getCommunicationsTemporary.modification"
            @showModalEmails="showModalEmails"
            @showCommunicationFrom="showCommunicationFrom"
          />
        </div>
      </a-col>
      <a-col :span="24" style="text-align: center; margin-top: 50px">
        <div class="actions">
          <a-button
            class="btn-back"
            :disabled="fileServiceStore.isLoading"
            type="default"
            size="large"
            @click="goToPreviousStep"
          >
            Atrás
          </a-button>
          <a-button
            :loading="fileServiceStore.isLoading"
            type="primary"
            class="btn-danger btn-temporary"
            default
            html-type="submit"
            size="large"
            @click="handleFormSubmit"
          >
            Reservar
          </a-button>
        </div>
      </a-col>
    </a-row>
    <ModalViewCommunication :modal="modalViewCommunication" @close="closeModal" />
    <ModalAddEmails
      :modal="modalAddEmails"
      :emails="selectedSupplierEmails"
      @deleteEmail="handleDeleteEmails"
      @save="handleSaveEmails"
      @close="closeModal"
    />
  </div>
</template>
<script setup lang="ts">
  import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
  import { defineEmits, onMounted, ref } from 'vue';
  import { useFilesStore } from '@/stores/files';
  import type { Service } from '@/components/files/temporary/interfaces/service.interface';
  import { notification } from 'ant-design-vue';
  import CommunicationServiceDetails from '@/components/files/temporary/components/communications/CommunicationServiceDetails.vue';
  import CommunicationSection from '@/components/files/temporary/components/communications/CommunicationSection.vue';
  import ModalViewCommunication from '@/components/files/temporary/components/communications/ModalViewCommunication.vue';
  import ModalAddEmails from '@/components/files/temporary/components/communications/ModalAddEmails.vue';
  import CommunicationSectionModification from '@/components/files/temporary/components/communications/CommunicationSectionModification.vue';
  import { useFileServiceStore } from '@/components/files/temporary/store/useFileServiceStore';
  import { useRouter } from 'vue-router';

  const fileServiceStore = useFileServiceStore();
  const router = useRouter();
  const emit = defineEmits(['nextStep', 'backStep']);
  const filesStore = useFilesStore();

  const getCommunicationsTemporary = ref({
    reservations: [],
    cancellation: [],
    modification: [],
  });

  const selectedSupplierEmails = ref([]);
  const addSupplierEmails = ref({
    emails: [] as string[],
    type: '',
    index: 0,
    typeFrom: null | String,
    indexFrom: null | Number,
  });

  const modalViewCommunication = ref({
    open: false,
    html: '',
  });

  const modalAddEmails = ref({
    open: false,
  });

  const serviceSelected = ref<Service>({
    itinerary: {
      name: '',
      total_amount: 0,
      services_new: [],
      services_new_replaced: [],
      services_deleted: [],
    },
  });

  const showCommunicationFrom = (component, type) => {
    console.log(component, type);
    const parser = new DOMParser();
    const doc = parser.parseFromString(component.html, 'text/html');
    const bodyElement = doc.body;

    modalViewCommunication.value.html = bodyElement.innerHTML;
    modalViewCommunication.value.open = true;
  };

  const showModalEmails = async (_emails, index, type, typeFrom = null, indexFrom = null) => {
    addSupplierEmails.value.type = type;
    addSupplierEmails.value.index = index;
    addSupplierEmails.value.typeFrom = typeFrom;
    addSupplierEmails.value.indexFrom = indexFrom;
    console.log('type email:', type);
    if (type == 'reservations') {
      selectedSupplierEmails.value =
        getCommunicationsTemporary.value.reservations[index].supplier_emails;
    }

    if (type == 'cancellation') {
      selectedSupplierEmails.value =
        getCommunicationsTemporary.value.cancellation[index].supplier_emails;
    }

    if (type == 'modification') {
      if (typeFrom == 'reservations' || typeFrom == 'cancellation') {
        selectedSupplierEmails.value =
          getCommunicationsTemporary.value.modification[index][typeFrom][indexFrom].supplier_emails;
      } else {
        selectedSupplierEmails.value =
          getCommunicationsTemporary.value.modification[index].supplier_emails;
      }
    }

    modalAddEmails.value.open = true;
  };

  const closeModal = () => {
    modalAddEmails.value.open = false;
    modalViewCommunication.value.open = false;
  };

  const handleFormSubmit = async () => {
    try {
      const itinerary = filesStore.serviceEdit.itinerary;
      if (!itinerary || !itinerary.id || !itinerary.services || itinerary.services.length === 0) {
        throw new Error('Itinerario o servicios no están correctamente definidos');
      }

      fileServiceStore.setServiceTemporaryCommunications(
        filesStore.getServiceTemporaryCommunications
      );

      await fileServiceStore.sendFileService(
        router.currentRoute.value.params.id,
        router.currentRoute.value.params.service_id,
        itinerary
      );
      if (fileServiceStore.isSuccess) {
        const currentDateTime = fileServiceStore.getCurrentDateTime();
        filesStore.serviceEdit.itinerary.created_date_at = currentDateTime.date;
        filesStore.serviceEdit.itinerary.created_time_at = currentDateTime.time;
        notification.success({
          message: 'Éxito',
          description: 'El servicio temporal se ha creado correctamente.',
        });
        emit('goToFinalStep');
      }
    } catch (error) {
      notification.error({
        message: 'Error',
        description: error.message || 'Ocurrió un error al guardar el servicio temporal',
      });
    } finally {
      fileServiceStore.isLoading = false;
    }
  };

  // Función para retroceder al paso anterior
  const goToPreviousStep = () => {
    emit('backStep');
  };

  const isValidEmail = (email: string) => {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
  };

  const handleDeleteEmails = (type = '', indexEmail) => {
    console.log('type: ', type);
    console.log('indexEmail: ', indexEmail);
    filesStore.setDeleteEmailSuppliersServiceTemporaryCommunications(
      addSupplierEmails.value.type,
      addSupplierEmails.value.index,
      indexEmail
    );
  };

  const handleSaveEmails = (emailInput) => {
    addSupplierEmails.value.emails = emailInput;
    // Paso 1: Eliminar espacios en blanco y filtrar correos inválidos
    const trimmedEmails = addSupplierEmails.value.emails.map((email) => email.trim());
    const invalidEmails = trimmedEmails.filter((email) => !isValidEmail(email));

    // Obtenemos los correos previamente guardados para la validación de duplicados
    const savedEmails = filesStore.getEmailsServiceTemporaryCommunications(
      addSupplierEmails.value.type,
      addSupplierEmails.value.index,
      addSupplierEmails.value.typeFrom,
      addSupplierEmails.value.indexFrom
    );

    // Paso 2: Eliminar duplicados en el nuevo conjunto de correos ingresados
    const uniqueEmails = Array.from(new Set(trimmedEmails));

    // Paso 3: Filtrar correos duplicados ya existentes en savedEmails
    const nonDuplicateEmails = uniqueEmails.filter((email) => !savedEmails.includes(email));

    // Paso 4: Validación de correos inválidos y duplicados
    if (invalidEmails.length > 0) {
      // Notificación de error si existen correos inválidos
      notification.error({
        message: 'Error',
        description: `Los siguientes correos son inválidos: ${invalidEmails.join(', ')}`,
      });
    } else if (nonDuplicateEmails.length === 0 && emailInput.length === 0) {
      // Notificación de advertencia si todos los correos son duplicados
      notification.warning({
        message: 'Advertencia',
        description: 'Debe ingresar al menos un correo válido.',
      });
    } else if (nonDuplicateEmails.length === 0 && emailInput.length > 0) {
      // Notificación de advertencia si todos los correos son duplicados
      notification.warning({
        message: 'Advertencia',
        description: 'Todos los correos ingresados ya existen en la lista.',
      });
    } else {
      // Guardar solo los correos válidos y únicos que no están en savedEmails
      filesStore.setEmailsSuppliersServiceTemporaryCommunications(
        nonDuplicateEmails,
        addSupplierEmails.value.type,
        addSupplierEmails.value.index,
        addSupplierEmails.value.typeFrom,
        addSupplierEmails.value.indexFrom
      );

      // Limpiar el estado de los correos después de guardarlos
      addSupplierEmails.value.emails = [];
      addSupplierEmails.value.type = '';
      addSupplierEmails.value.index = 0;
      addSupplierEmails.value.typeFrom = null;
      addSupplierEmails.value.indexFrom = null;

      // Notificación de éxito al guardar
      notification.success({
        message: 'Éxito',
        description: 'Se guardaron los correos correctamente.',
      });

      closeModal();
    }
  };

  onMounted(async () => {
    getCommunicationsTemporary.value = filesStore.getServiceTemporaryCommunications;
    serviceSelected.value = filesStore.getServiceEdit;
  });
</script>
<style scoped lang="scss">
  .box-communications {
    padding: 40px;
    border: 1px solid #e9e9e9;
    border-radius: 6px;
    margin-bottom: 20px;
  }

  .actions {
    display: flex;
    gap: 25px;
    flex-wrap: nowrap;
    flex-direction: row;
    align-content: center;
    justify-content: center;
    align-items: center;
  }

  .btn-temporary {
    width: auto;
    height: 54px !important;
    padding: 12px 24px 12px 24px;
    display: flex;
    align-items: center;
    flex-wrap: nowrap;
    justify-content: center;
    font-size: 17px;
    font-weight: 500 !important;
    color: #ffffff !important;
    background-color: #eb5757 !important;
    border: 1px solid #eb5757 !important;

    svg {
      margin-right: 10px;
    }

    &:hover {
      color: #ffffff !important;
      background-color: #c63838 !important;
      border: 1px solid #c63838 !important;
    }
  }

  .btn-back {
    width: auto;
    height: 54px !important;
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
