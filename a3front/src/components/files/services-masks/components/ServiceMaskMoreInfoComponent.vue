<template>
  <div>
    <a-modal
      class="file-modal"
      :open="isOpen"
      @cancel="handleCancel"
      :footer="null"
      :focusTriggerAfterClose="false"
      width="920px"
    >
      <template #title>
        <div class="title-service-master">
          {{ serviceSelected?.name }}
        </div>
        <div class="service-tags">
          <div class="tag-modal tag-type-service">Misceláneo</div>
        </div>
      </template>

      <!-- Contenido del modal -->
      <div class="modal-content">
        <a-row :gutter="16" class="mt-2">
          <a-col :span="24">
            <div class="additional-info-header">
              <div class="operation-title">
                <font-awesome-icon :icon="['far', 'bookmark']" />
                <span class="mx-2">Itinerario</span>
              </div>
            </div>
            <div class="additional-info-body">
              {{ serviceRate?.languageTextsItinerary.es }}
            </div>
          </a-col>
          <a-col :span="24" class="mt-5">
            <div class="additional-info-header">
              <div class="operation-title">
                <font-awesome-icon :icon="['far', 'bookmark']" />
                <span class="mx-2">Skeleton</span>
              </div>
            </div>
            <div class="additional-info-body">
              {{ serviceRate?.languageTextsSkeleton.es }}
            </div>
          </a-col>
        </a-row>
      </div>
    </a-modal>
  </div>
</template>

<script setup lang="ts">
  import { onMounted, ref } from 'vue';
  import { useServiceMaskStore } from '@/components/files/services-masks/store/serviceMaskStore';

  defineProps({
    isOpen: {
      type: Boolean,
      required: true,
    },
  });

  const emit = defineEmits(['update:isOpen', 'submit']);
  const serviceSelected = ref<Record<string, any>>({});
  const serviceSuppliers = ref<Record<string, any>>({});
  const serviceRate = ref<Record<string, any>>({});
  const serviceMaskStore = useServiceMaskStore();

  const handleCancel = () => {
    emit('update:isOpen', false);
  };

  onMounted(() => {
    serviceSelected.value = serviceMaskStore.getServiceMask || {};
    serviceSuppliers.value = serviceMaskStore.getServiceMaskSupplier || {};
    serviceRate.value = serviceMaskStore.getServiceMaskRate || {};
  });
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
      margin-top: 50px;
      margin-left: 20px;
    }

    .service-tags {
      display: flex;
      gap: 35px;
      justify-content: flex-start;
      margin-left: 20px;

      .tag-modal {
        font-family: 'Montserrat', sans-serif;
        color: #ffffff;
        padding: 2px 40px;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 500;
        margin-top: 5px;
      }

      .tag-type-service {
        background-color: #4ba3b2;
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
        font-size: 18px;
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
      margin-left: 20px;
      text-align: justify;
      margin-top: 10px;
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
