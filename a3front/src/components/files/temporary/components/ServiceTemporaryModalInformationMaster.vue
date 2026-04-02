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
      width="920px"
    >
      <template #title>
        <div class="service-tags">
          <div class="tag-modal tag-type-service">Tipo de servicio</div>
          <div class="tag-modal tag-category-service">Premium</div>
        </div>
        <div class="title-service-master">
          {{ serviceMasterReplace?.id }} - {{ serviceMasterReplace?.name }}
        </div>
      </template>

      <!-- Contenido del modal -->
      <div class="modal-content">
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
              ? Arribo a Wiaynahuayna 2700msnm. Lugar del almuerzo (En este lugar nuestro grupo
              podra degustar del snack que se le envie por parte)<br />
              ? Inicio Box Lunch (Sector Wiaynahuayna)<br />
              ? Llegada a Intipunku (Lugar desde se puede apreciar MachuPicchu si el cielo esta
              despejado) 2730msnm.<br />
              ? Entrada a MachuPicchu + inicio tour<br />
              ? Estacion de bus, para tomar el bus de bajada a MachuPicchu hacia Aguas Calientes<br />
              ? Arribo al pobledo de Aguas Calientes 2040 msnm para ser acomodado en su hotel.<br />
              Fin del tour guiado en MachiPicchu.
            </div>
          </a-col>
          <a-col :span="12">
            <div class="additional-note">
              <div class="additional-note-header">Notas</div>
              <div class="additional-note-body">
                Servicio sujeto a disponibilidad de camino Inca y de ingreso a MachuPichu para el
                mismo día a las 14:00 hrs.<br />
                No disponibleen el mes de febrero<br />
                Tipo de servicio intenso<br />
                No incluye trenes ni bus de bajada<br />
              </div>
            </div>
          </a-col>
        </a-row>
      </div>
    </a-modal>
  </div>
</template>

<script setup lang="ts">
  import { ref, watch } from 'vue';
  import { useFilesStore } from '@/stores/files';

  defineProps({
    isOpen: {
      type: Boolean,
      required: true,
    },
  });

  const emit = defineEmits(['update:isOpen', 'submit']);
  const filesStore = useFilesStore();
  const serviceMasterReplace = ref(filesStore.getServiceMasterReplace);

  const handleCancel = () => {
    emit('update:isOpen', false);
  };

  watch(
    () => filesStore.getServiceMasterReplace,
    (newService) => {
      serviceMasterReplace.value = newService;
    }
  );
</script>

<style scoped lang="scss">
  .file-modal {
    .title-service-master {
      font-family: 'Montserrat', sans-serif;
      font-weight: 600;
      font-size: 22px;
      letter-spacing: -0.01em;
      color: #212529;
      text-align: left;
      margin-left: 20px;
    }

    .service-tags {
      display: flex;
      gap: 35px;
      top: -20px;
      position: relative;
      justify-content: flex-end;
      margin-right: 45px;

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

      .tag-category-service {
        background-color: #ffb001;
      }
    }

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
      font-size: 14px;
      font-weight: 400;
    }

    .additional-note {
      background-color: #fafafa;
      padding: 15px 20px;
      border-radius: 6px;

      .additional-note-header {
        font-size: 14px;
        font-weight: 600;
        text-underline-position: from-font;
        text-decoration-skip-ink: none;
        margin-bottom: 10px;
      }
    }

    .modal-content {
      padding: 20px;
    }
  }
</style>
