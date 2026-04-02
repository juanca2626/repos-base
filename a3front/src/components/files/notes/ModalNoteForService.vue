<template>
  <a-modal
    v-model:open="props.openModal"
    width="50%"
    @cancel="closeModal"
    :maskClosable="false"
    :footer="null"
  >
    <a-form direction="vertical" style="width: 100%">
      <a-row>
        <a-col>
          <!-- {{ formNotes.services }} -->
          <div v-for="service in formNotes.services" :key="service.service_id">
            <a-descriptions :title="t('global.message.service_details')" bordered>
              <a-descriptions-item :label="t('global.label.service')" :span="3">{{
                service.service_name || service.itinerary_name
              }}</a-descriptions-item>
              <a-descriptions-item :label="t('global.column.date')" :span="3">{{
                formatDate(service.service_date_in || service.itinerary_date_in)
              }}</a-descriptions-item>
              <a-descriptions-item :label="t('global.label.city')" :span="3">{{
                service.service_city_in_name || service.itinerary_city_in_name
              }}</a-descriptions-item>
            </a-descriptions>
          </div>
        </a-col>
      </a-row>
      <a-row class="mt-4">
        <a-col>
          <a-form-item :label="t('global.message.note_type')" style="font-size: 16px">
            <a-radio-group v-model:value="formNotes.type_note" disabled>
              <a-radio value="INFORMATIVE" style="font-size: 11px">{{
                t('global.label.informative')
              }}</a-radio>
              <a-radio value="REQUIREMENT" style="font-size: 11px">{{
                t('global.label.requirement')
              }}</a-radio>
            </a-radio-group>
          </a-form-item>
        </a-col>
      </a-row>
      <a-form-item :label="t('global.label.classification')">
        <a-select
          :placeholder="t('global.label.selected')"
          label-in-value
          v-model:value="formNotes.classification_code"
          style="width: 100%"
          @change="changeClasification"
        >
          <!-- <a-select-option value="" selected hidden>Selecciona</a-select-option> -->
          <a-select-option
            v-for="item in serviceNotesStore?.classifications"
            :key="item.code.trim()"
            :value="item.code.trim()"
          >
            {{ item.description }}
          </a-select-option>
        </a-select>
      </a-form-item>
      <a-form-item>
        <a-textarea
          style="width: 100%"
          :placeholder="t('global.message.writer_notificacion_ope')"
          :rows="3"
          show-count
          :maxlength="500"
          v-model:value="formNotes.description"
        />
      </a-form-item>
      <div :span="24" style="text-align: right; margin-bottom: 0">
        <a-button style="margin-right: 10px" @click="closeModal" :disabled="loadingNote">{{
          t('global.label.cancel')
        }}</a-button>
        <a-button type="primary" @click="handleServiceLogSave" :disabled="loadingNote">{{
          t('global.label.save')
        }}</a-button>
      </div>

      <!-- <div>
        {{ formNotes }}
      </div> -->
    </a-form>
  </a-modal>
</template>
<script setup>
  import { ref, reactive, watch, computed } from 'vue';
  // import dayjs from 'dayjs';
  import { getUserId, getUserName, getUserCode } from '@/utils/auth';
  import { notification } from 'ant-design-vue';
  import { useServiceNotesStore, useFilesStore } from '../../../stores/files';
  import { useSocketsStore } from '@/stores/global';
  import { useI18n } from 'vue-i18n';
  const { t } = useI18n({
    useScope: 'global',
  });

  const serviceNotesStore = useServiceNotesStore();
  const filesStore = useFilesStore();
  const socketsStore = useSocketsStore();
  const file_id = filesStore.getFile.id;
  const file_number = filesStore.getFile.fileNumber;
  const loadingNote = ref(false);
  const messages = computed(() => {
    return {
      update_note: t('files.message.update_note'),
      updated_correctly: t('global.message.updated_correctly'),
    };
  });

  const props = defineProps({
    openModal: {
      type: Boolean,
      required: true,
      default: false,
    },
    id: {
      type: Number,
      default: 0,
    },
    type: {
      type: String,
    },
    entity: {
      type: String,
    },
  });

  const formNotes = reactive({
    id: 0,
    type_note: 'REQUIREMENT',
    record_type: '',
    assignment_mode: '',
    description: '',
    dates: [],
    classification_code: '',
    classification_name: '',
    created_by: getUserId(),
    created_by_code: getUserCode(),
    created_by_name: getUserName(),
    services: [],
  });
  const emit = defineEmits(['close-modal']);

  const closeModal = () => {
    emit('close-modal', false);
  };

  const changeClasification = (value) => {
    const searchClassification = serviceNotesStore.classifications.find((e) => {
      return e.code.trim() == value.key;
    });
    formNotes.classification_code = value.key;
    formNotes.classification_name = searchClassification.description;
  };

  const handleServiceLogSave = async () => {
    // AQUI EL GUARDAR
    if (formNotes.id != 0 && formNotes.description != '') {
      let arrEnviar = { ...formNotes };
      arrEnviar.dates = JSON.stringify(arrEnviar.dates);
      loadingNote.value = true;
      serviceNotesStore
        .update({
          note_id: arrEnviar.id,
          itinerary_id: arrEnviar.services[0].service_id,
          file_id: file_id,
          data: arrEnviar,
        })
        .then(() => {
          loadingNote.value = false;
          const message = messages.value.update_note;
          const description = messages.value.updated_correctly;

          sendSocketMessage(true, 'update_note_itinerary', message, description, {
            itinerary_id: arrEnviar.services[0].service_id,
            id: arrEnviar.id,
            type_note: arrEnviar.type_note,
            record_type: arrEnviar.record_type,
            entity: 'note',
          });

          closeModal();
        })
        .catch((err) => {
          console.log(err);
          loadingNote.value = false;
        });
    } else {
      notification['warning']({
        message: `Advertencia`,
        description: 'Todos los campos son requeridos',
        duration: 5,
      });
    }
  };

  const requirement_information = computed(() => {
    return serviceNotesStore.getAllRequirementFileNote || [];
  });

  const information_by_city = computed(() => {
    const for_service = serviceNotesStore.getAllFileNote.for_service || [];
    const arrInformation = Object.values(for_service) // Obtener los valores de las ciudades
      .flatMap((cityDates) => Object.values(cityDates)) // Obtener los arrays de fechas
      .flat();

    return arrInformation;
  });

  const sendSocketMessage = (success, type, message, description, data = {}) => {
    socketsStore.send({
      success,
      type: type,
      file_number: file_number,
      file_id: file_id,
      itinerary_id: data.itinerary_id,
      user_code: getUserCode(),
      user_id: getUserId(),
      message: message,
      description: description,
      id: data?.id || 0,
      type_note: data?.type_note || '',
      record_type: data?.record_type || '',
    });
  };

  watch(
    () => ({ id: props.id, modal: props.openModal }),
    ({ id }) => {
      if (id) {
        if (props.type === 'INFORMATIVE') {
          const search = information_by_city.value.find(
            (e) => e.id === id && e.entity === props.entity
          );
          if (search) {
            formNotes.id = id;
            formNotes.type_note = search.type_note;
            formNotes.record_type = search.record_type;
            formNotes.assignment_mode = search.assignment_mode;
            formNotes.description = search.description;
            formNotes.dates = [search.date];
            formNotes.classification_code = search.classification_code;
            formNotes.classification_name = search.classification_name;
            formNotes.services = search.services;
          }
        } else {
          // console.log('ESCUCHANDO EL WATCH MODAL FOR SERVICE', id, modal);
          // console.log(requirement_information.value);
          const search = requirement_information.value.find(
            (e) => e.id === id && e.entity === props.entity
          );
          // console.log(search);
          if (search) {
            formNotes.id = id;
            formNotes.type_note = search.type_note;
            formNotes.record_type = search.record_type;
            formNotes.assignment_mode = search.assignment_mode;
            formNotes.description = search.description;
            formNotes.dates = search.dates;
            formNotes.classification_code = search.classification_code;
            formNotes.classification_name = search.classification_name;
            formNotes.services = search.services;
          }
        }
      }
    }
  );

  const formatDate = (dateString) => {
    if (!dateString) return '';

    // Dividir la fecha por guiones
    const [year, month, day] = dateString.split('-');

    // Devolver en nuevo formato
    return `${day}/${month}/${year}`;
  };

  // onMounted(() => {
  //     // Elimina los botones predeterminados después de que el componente se monte
  //     setTimeout(() => {
  //         const defaultButtons = document.querySelectorAll('.ant-form-item:has(.ant-btn-default)');
  //         defaultButtons.forEach(btn => btn.remove());
  //     }, 100);
  // });
</script>
<style scoped>
  :deep(.ant-footer .ant-btn-default) {
    display: none !important;
  }
</style>
