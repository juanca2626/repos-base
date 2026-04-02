<template>
  <div v-if="statisticsStore?.isLoading" class="container-body is-loading">
    <a-spin :tip="`${t('global.label.loading_stats')}...`" />
  </div>
  <div v-else class="group-stats-wrap">
    <div class="group-stats">
      <div class="toggle-stats" @click="toggleStats = !toggleStats" v-if="false">
        <font-awesome-icon v-if="toggleStats" icon="fa-solid fa-chevron-down" size="lg" />
        <font-awesome-icon v-else icon="fa-solid fa-chevron-up" size="lg" />
      </div>

      <CardStatsB size="sm" v-if="toggleStats" @onClick="fetchFilterNextDays(45)">
        <template #card-icon>
          <font-awesome-icon icon="fa-solid fa-box-archive" size="1x" />
        </template>
        <template #card-title>{{ padString(stats.filesTotal) }} Files</template>
      </CardStatsB>

      <CardStatsA v-else @onClick="fetchFilterNextDays(45)">
        <template #card-number>{{ padString(stats.filesTotal) }}</template>
        <!-- <template #badge-number>30</template> -->

        <template #badge-title-1>
          <span style="cursor: pointer" @click="fetchRevisionStages(2)">OPE</span>
        </template>
        <template #badge-number-1>
          <span
            style="cursor: pointer; display: flex; align-items: center"
            @click="fetchRevisionStages(2)"
          >
            {{ padString(stats.filesTotalOpe) }}
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="16"
              height="16"
              fill="none"
              stroke="currentColor"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              class="feather feather-arrow-right"
              viewBox="0 0 24 24"
            >
              <path d="M5 12h14M12 5l7 7-7 7" />
            </svg>
          </span>
        </template>

        <template #badge-title-2>
          <span style="cursor: pointer" @click="fetchRevisionStages(1)">DTR</span>
        </template>
        <template #badge-number-2>
          <span
            style="cursor: pointer; display: flex; align-items: center"
            @click="fetchRevisionStages(1)"
          >
            {{ padString(stats.filesTotalDtr) }}
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="16"
              height="16"
              fill="none"
              stroke="currentColor"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              class="feather feather-arrow-right"
              viewBox="0 0 24 24"
            >
              <path d="M5 12h14M12 5l7 7-7 7" />
            </svg>
          </span>
        </template>
      </CardStatsA>

      <CardStatsB
        status-class="isDanger"
        :active-class="filesStore.filterNextDays === 7 ? 'isDanger--active' : ''"
        :size="toggleStats ? 'sm' : 'lg'"
        @onClick="fetchFilterNextDays(7)"
      >
        <template #card-title v-if="toggleStats"
          >{{ padString(stats.filesWeekly) }} {{ t('global.label.files') }}
        </template>
        <template #card-title v-else>07 {{ t('global.label.days') }}</template>

        <template #card-button-label
          >{{ padString(stats.filesWeekly) }} {{ t('global.label.files') }}
        </template>
      </CardStatsB>

      <CardStatsB
        status-class="isWarning"
        :active-class="filesStore.filterNextDays === 15 ? 'isWarning--active' : ''"
        :size="toggleStats ? 'sm' : 'lg'"
        @onClick="fetchFilterNextDays(15)"
      >
        <template #card-title v-if="toggleStats"
          >{{ padString(stats.filesQuarter) }} {{ t('global.label.files') }}
        </template>
        <template #card-title v-else>15 {{ t('global.label.days') }}</template>

        <template #card-button-label
          >{{ padString(stats.filesQuarter) }} {{ t('global.label.files') }}
        </template>
      </CardStatsB>

      <CardStatsB
        status-class="isSuccess"
        :active-class="filesStore.filterNextDays === 30 ? 'isSuccess--active' : ''"
        :size="toggleStats ? 'sm' : 'lg'"
        @onClick="fetchFilterNextDays(30)"
      >
        <template #card-title v-if="toggleStats"
          >{{ padString(stats.filesMonth) }} {{ t('global.label.files') }}
        </template>
        <template #card-title v-else>30 {{ t('global.label.days') }}</template>

        <template #card-button-label
          >{{ padString(stats.filesMonth) }} {{ t('global.label.files') }}
        </template>
      </CardStatsB>
    </div>

    <div class="btn-group" style="margin-top: 20px">
      <a-dropdown>
        <a-button
          style="height: auto"
          type="primary"
          class="px-5 py-2 text-600 btn-create-file"
          size="large"
          @click.prevent
          overlayClassName="btn-create-file-item"
          >{{ t('files.button.new') }}
        </a-button>
        <template #overlay>
          <a-menu class="file-dropdown-menu">
            <a-menu-item key="1" class="file-menu-item" @click="showModalCreateFile">
              <div class="menu-item-content">
                <font-awesome-icon :icon="['fas', 'file-medical']" class="menu-icon" />
                <span class="menu-text">{{ t('global.label.from') }} 0</span>
              </div>
            </a-menu-item>
            <a-menu-item key="2" class="file-menu-item" @click="showModalCloneFile">
              <div class="menu-item-content">
                <font-awesome-icon :icon="['fas', 'clone']" class="menu-icon" />
                <span class="menu-text">{{ t('global.label.clone') }}</span>
              </div>
            </a-menu-item>
          </a-menu>
        </template>
      </a-dropdown>
    </div>
  </div>

  <CreateFileModal
    v-if="modalIsOpen"
    :is-open="modalIsOpen"
    @update:is-open="modalIsOpen = $event"
  />
  <CloneFileModal
    v-if="modalCloneIsOpen"
    :is-open="modalCloneIsOpen"
    @update:is-open="modalCloneIsOpen = $event"
    :showFileSelect="true"
    :setFileId="''"
  />
</template>

<script setup>
  import { onBeforeMount, ref, computed } from 'vue';
  import CardStatsA from '@/components/files/reusables/CardStatsA.vue';
  import CardStatsB from '@/components/files/reusables/CardStatsB.vue';
  import CreateFileModal from '@/components/files/create/CreateFileModal.vue';
  import CloneFileModal from '@/components/files/clone/CloneFileModal.vue';

  import { useFilesStore, useStatisticsStore, useVipsStore } from '@store/files';

  import { useI18n } from 'vue-i18n';

  const modalIsOpen = ref(false);
  const modalCloneIsOpen = ref(false);

  const { t } = useI18n({
    useScope: 'global',
  });

  const statisticsStore = useStatisticsStore();
  const filesStore = useFilesStore();
  const vipsStore = useVipsStore();

  const toggleStats = ref(false);

  const padString = (str, targetLength = 2, padString = '0') =>
    String(str).padStart(targetLength, padString);

  const stats = computed(() => {
    const {
      filesTotal = 0,
      filesTotalOpe = 0,
      filesTotalDtr = 0,
      filesWeekly = 0,
      filesQuarter = 0,
      filesMonth = 0,
    } = statisticsStore.getStatistics;

    return {
      filesTotal,
      filesTotalOpe,
      filesTotalDtr,
      filesWeekly,
      filesQuarter,
      filesMonth,
    };
  });

  const showModalCreateFile = () => {
    modalIsOpen.value = true;
  };

  const showModalCloneFile = () => {
    modalCloneIsOpen.value = true;
  };

  const fetchFilterNextDays = async (filterNextDays) => {
    filesStore.revisionStages = null;
    filesStore.filterNextDays = filterNextDays;
    await vipsStore.fetchAll();
    await filesStore.fetchAll({ currentPage: 1 });
  };

  const fetchRevisionStages = async (revisionStages) => {
    filesStore.filterNextDays = null;
    filesStore.revisionStages = revisionStages;
    await vipsStore.fetchAll();
    await filesStore.fetchAll({ currentPage: 1 });
  };

  onBeforeMount(async () => {
    await statisticsStore.fetchAll();
  });
</script>
<style scoped lang="scss">
  .file-dropdown-menu {
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    padding: 4px 0;

    :deep(.ant-dropdown-menu-item) {
      padding: 10px; /* Ajusta el padding de los items */
      .menu-item-content {
        display: flex;
        align-items: center;
        gap: 10px;

        svg {
          color: #eb5757;
          margin-left: 5px;
        }

        /* Estilos para el texto del item */
        span {
          font-family: 'Montserrat', sans-serif;
          font-weight: 500;
          font-size: 16px;
          color: #eb5757;
          margin-right: 15px;
        }
      }

      /* Ajustes cuando un item está activo (hover o selección) */
      &:hover {
        background-color: #ffffff; /* Color de fondo del ítem cuando está activo */
      }

      /* Ajustes para ítems seleccionados */
      &.ant-dropdown-menu-item-selected {
        background-color: #ffffff; /* Color de fondo del ítem seleccionado */
      }
    }
  }
</style>
