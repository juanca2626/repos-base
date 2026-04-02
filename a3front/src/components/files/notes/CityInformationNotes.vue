<template>
  <a-card
    class="reservation-card override-card"
    :class="{ border: toogleBool, 'override-card-hover': !toogleBool }"
  >
    <template #title>
      <div class="reservation-header">
        <!-- Sección izquierda del header -->
        <div class="header-section">
          <div class="location-title">{{ props.city }}</div>
          <span class="label-count-notes">{{ countNotes || 0 }} notes</span>
        </div>

        <!-- Sección derecha del header -->
        <div class="header-section">
          <font-awesome-icon
            :icon="['fas', toogleBool ? 'chevron-up' : 'chevron-down']"
            class="action-icon"
            @click="toggleActive"
          />
        </div>
      </div>
    </template>
    <!-- <hr class="custom-divider" v-if="toogleBool"> -->
    <!-- Contenido de la card -->
    <div v-for="(dateData, date) in props.item" v-if="toogleBool">
      <div v-for="(item, key) in dateData" :key="key">
        <hr class="custom-divider" />
        <div class="reservation-content" v-if="toogleBool">
          <!-- Información del alojamiento -->
          <CityInformationNotesItem
            :item="item"
            :date="date"
            :dateIn="props.dateIn"
            :fileId="props.fileId"
            :fileNumber="props.fileNumber"
            :revisionStages="props.revisionStages"
          />
        </div>
      </div>
    </div>
  </a-card>
</template>
<script setup>
  import { ref, computed } from 'vue';
  import CityInformationNotesItem from '@/components/files/notes/CityInformationNotesItem.vue';

  const toogleBool = ref(false);

  const props = defineProps({
    item: {
      required: true,
      type: Array,
      default: [],
    },
    city: {
      required: true,
      default: '',
    },
    dateIn: {
      required: true,
      default: '',
    },
    fileNumber: {
      required: true,
    },
    fileId: {
      required: true,
    },
    revisionStages: {
      required: true,
    },
  });

  const countNotes = computed(() => {
    let count = 0;
    Object.keys(props.item).forEach((e) => {
      count += props.item[e].length;
    });
    return count;
  });

  // const positionDate = (key) => {
  //   let position = Object.keys(props.item).findIndex((date) => date === key);
  //   return position + 1;
  // };

  const toggleActive = () => {
    toogleBool.value = !toogleBool.value;
  };
</script>
<style scoped>
  /* Estilos del card */
  .reservation-card {
    border-radius: 8px;
    background-color: #fafafa;
    border: 1px solid #e9e9e9 !important;
    /* width: 100% !important; */
  }

  /* Header styles */
  .reservation-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
    padding: 0px 28px;
  }

  .header-section {
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .location-title {
    color: #3d3d3d;
    font-weight: 700;
    font-size: 18px;
  }

  .label-count-notes {
    color: #979797;
    font-size: 12px;
    font-weight: 600;
  }

  .date-group,
  .nights-info {
    display: flex;
    align-items: center;
    gap: 12px;
    color: #979797;
    font-weight: 600;
    font-size: 12px;
  }

  .date-group div {
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .action-icon {
    cursor: pointer;
    color: #3d3d3d;
    opacity: 0.8;
    transition: opacity 0.2s;
    font-size: 18px;
  }

  .action-icon:hover {
    opacity: 1;
  }

  /* Content styles */
  :where(.files-layout) .ant-card-head {
    all: unset;
  }

  /* Estilos específicos para tu card */
  .reservation-card.override-card :deep(.ant-card-head) {
    background: linear-gradient(135deg, #fafafa, #fafafa) !important;
    border-radius: 8px 8px 8px 8px !important;
    padding: 0 16px !important;
    border: 1px solid #e9e9e9 !important;
  }

  .reservation-card.override-card-hover :deep(.ant-card-head):hover {
    background: linear-gradient(135deg, #e9e9e9, #e9e9e9) !important;
  }

  .reservation-card.override-card.border :deep(.ant-card-head) {
    border: 0 !important;
  }

  .reservation-card.override-card :deep(.ant-card-head-title) {
    color: #979797 !important;
    padding: 12px 0 !important;
  }

  .reservation-card.override-card :deep(.ant-card-body) {
    padding-top: 0px !important;
    padding-left: 0 !important;
    padding-right: 0 !important;
    padding-bottom: 0 !important;
  }

  .reservation-card .day-info {
    color: #979797 !important;
    font-weight: 500;
    size: 12px;
  }

  .custom-divider {
    border: 0;
    height: 1px;
    background: #e9e9e9;
    margin: 0px 0;
    width: 100%;
  }
</style>
