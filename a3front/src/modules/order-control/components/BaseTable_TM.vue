<template>
  <div class="base-table container-fluid p-0">
    <div class="files-edit files-edit__border">
      <a-row style="padding: 0 0 20px 0">
        <a-col :span="24">
          <a-flex justify="space-between" align="center">
            <h3 class="files-edit__filelabel" style="text-transform: capitalize; margin: 0">
              <font-awesome-icon
                :icon="['fas', 'arrow-left']"
                style="color: rgb(235, 87, 87); cursor: pointer"
                @click="goBack"
              />
              Gestionar plantillas
            </h3>
            <base-button
              danger
              type="outline-main"
              size="large"
              @click="showTemplateManagementModal()"
              style="border-color: #eb5757; color: #eb5757"
            >
              <div style="display: flex; gap: 4px; align-items: center">
                <font-awesome-icon
                  :icon="['far', 'square-plus']"
                  style="font-size: 20px; vertical-align: middle"
                />
                <span>Crear Plantilla</span>
              </div>
            </base-button>
          </a-flex>
        </a-col>
      </a-row>
    </div>

    <!-- Comentario -->
    <div class="files-edit files-edit__border">
      <a-skeleton active v-if="templateStore.isLoading" />
      <template v-else>
        <a-tabs v-model:activeKey="activeKey">
          <a-tab-pane key="1" :class="{ 'block-style': !flagEditable }">
            <template #tab>
              <font-awesome-icon :icon="['fas', 'user']" style="margin-right: 4px" />
              <span style="text-transform: capitalize">Mis plantillas</span>
            </template>
            <div v-if="parseInt(activeKey) === 1">
              <div class="row g-0 row-header">
                <div class="col-small" v-for="column in config.columns01" :key="column.id">
                  <span v-if="!column?.isFiltered">{{ column.title }}</span>
                  <span v-else class="row-header-sort">
                    <span
                      v-if="isDesc && isSelectedFilterBy(column.fieldName)"
                      @click="onFilterBy({ filterBy: column.fieldName, filterByType: 'asc' })"
                    >
                      {{ column.title }}
                      <font-awesome-icon icon="fa-solid fa-arrow-up-short-wide" />
                    </span>
                    <span
                      v-else
                      @click="onFilterBy({ filterBy: column.fieldName, filterByType: 'desc' })"
                    >
                      {{ column.title }}
                      <font-awesome-icon icon="fa-solid fa-arrow-down-wide-short" />
                    </span>
                  </span>
                </div>
              </div>

              <div v-if="isLoading" class="container-body is-loading">
                <a-spin :tip="t('files.label.loading')" />
              </div>
              <div v-else-if="data?.length === 0" class="container-body is-loading">
                <a-empty>
                  <template #description>
                    <span> No se encontraron resultados para su búsqueda. </span>
                  </template>
                </a-empty>
              </div>

              <div v-else class="container-body">
                <div
                  v-bind:class="['row g-0 row-body']"
                  v-for="template in data"
                  :key="template._id"
                >
                  <div class="col-small">
                    {{ template.name }}
                  </div>
                  <div class="col-small">
                    {{ formatDate(template.updatedAt).date }}
                    <span style="display: block; color: #979797; font-size: 12px">
                      {{ formatDate(template.updatedAt).time }}
                    </span>
                  </div>
                  <div class="col-small">
                    <ToggleStatus
                      :status="template.status"
                      @update:status="handleStatusChange(template, $event)"
                    />
                  </div>
                  <div class="col-small">
                    <LanguageTag v-for="lang in template.languages" :key="lang" :lang="lang" />
                  </div>
                  <div class="col-small col-body-options">
                    <font-awesome-icon
                      :icon="['far', 'pen-to-square']"
                      @click="showTemplateEditModal(template)"
                    />
                    <font-awesome-icon
                      :icon="['fas', 'trash-can']"
                      @click="showTemplateDeleteModal(template._id)"
                    />
                    <font-awesome-icon
                      :icon="['far', 'clone']"
                      @click="showTemplateCloneModal(template)"
                    />
                  </div>
                </div>
              </div>

              <div class="row g-0 row-pagination">
                <a-pagination
                  v-model:current="currentPageValue"
                  v-model:pageSize="currentPageSize"
                  :disabled="data?.length === 0"
                  :pageSizeOptions="DEFAULT_PAGE_SIZE_OPTIONS"
                  :total="total"
                  show-size-changer
                  show-quick-jumper-off
                  @change="onChange"
                  @showSizeChange="onShowSizeChange"
                >
                  <template #buildOptionText="props">
                    <span>{{ props.value }}</span>
                  </template>
                </a-pagination>
              </div>
            </div>
          </a-tab-pane>
          <a-tab-pane key="2" :class="{ 'block-style': !flagEditable }">
            <template #tab>
              <font-awesome-icon :icon="['fas', 'chart-pie']" style="margin-right: 4px" />
              <span style="text-transform: capitalize">Todas las plantillas</span>
            </template>
            <div v-if="parseInt(activeKey) === 2">
              <div class="row g-0 row-header">
                <div class="col-small" v-for="column in config.columns02" :key="column.id">
                  <span v-if="!column?.isFiltered">{{ column.title }}</span>
                  <span v-else class="row-header-sort">
                    <span
                      v-if="isDesc && isSelectedFilterBy(column.fieldName)"
                      @click="onFilterBy({ filterBy: column.fieldName, filterByType: 'asc' })"
                    >
                      {{ column.title }}
                      <font-awesome-icon icon="fa-solid fa-arrow-up-short-wide" />
                    </span>
                    <span
                      v-else
                      @click="onFilterBy({ filterBy: column.fieldName, filterByType: 'desc' })"
                    >
                      {{ column.title }}
                      <font-awesome-icon icon="fa-solid fa-arrow-down-wide-short" />
                    </span>
                  </span>
                </div>
              </div>

              <div v-if="isLoading" class="container-body is-loading">
                <a-spin :tip="t('files.label.loading')" />
              </div>
              <div v-else-if="data?.length === 0" class="container-body is-loading">
                <a-empty>
                  <template #description>
                    <span>{{ t('global.label.empty') }}</span>
                  </template>
                </a-empty>
              </div>

              <div v-else class="container-body">
                <div
                  v-bind:class="['row g-0 row-body']"
                  v-for="template in data"
                  :key="template._id"
                >
                  <div class="col-small">
                    {{ template.name }}
                  </div>
                  <div class="col-small">
                    {{ formatDate(template.updatedAt).date }}
                    <span style="display: block; color: #979797; font-size: 12px">
                      {{ formatDate(template.updatedAt).time }}
                    </span>
                  </div>
                  <div class="col-small">
                    <ToggleStatus
                      :status="template.status"
                      :disabled="true"
                      @update:status="handleStatusChange(template, $event)"
                    />
                  </div>
                  <div class="col-small">
                    <LanguageTag v-for="lang in template.languages" :key="lang" :lang="lang" />
                  </div>
                  <div class="col-small">
                    <a-popover title="">
                      <template #content>
                        <p style="margin-bottom: 0; padding-bottom: 0">
                          <!-- <strong>Cliente:</strong><br /> -->
                          {{ template.user?.fullname }}
                        </p>
                      </template>
                      {{ template.user?.code }}
                    </a-popover>
                  </div>
                  <div class="col-small col-body-options">
                    <!-- 
                    <font-awesome-icon
                      :icon="['far', 'pen-to-square']"
                      @click="showTemplateEditModal(template)"
                    />
                    <font-awesome-icon
                      :icon="['fas', 'trash-can']"
                      @click="showTemplateDeleteModal(template._id)"
                    /> 
                    -->
                    <font-awesome-icon
                      :icon="['far', 'clone']"
                      @click="showTemplateCloneModal(template)"
                    />
                  </div>
                </div>
              </div>

              <div class="row g-0 row-pagination">
                <a-pagination
                  v-model:current="currentPageValue"
                  v-model:pageSize="currentPageSize"
                  :disabled="data?.length === 0"
                  :pageSizeOptions="DEFAULT_PAGE_SIZE_OPTIONS"
                  :total="total"
                  show-size-changer
                  show-quick-jumper-off
                  @change="onChange"
                  @showSizeChange="onShowSizeChange"
                >
                  <template #buildOptionText="props">
                    <span>{{ props.value }}</span>
                  </template>
                </a-pagination>
              </div>
            </div>
          </a-tab-pane>
        </a-tabs>
      </template>
    </div>

    <BaseModal
      width="600px"
      ref="templateManagementModalRef"
      :open="modalTemplateManagement"
      :title="selectedTemplate ? 'Editar plantilla' : 'Crear plantilla'"
      :titleIcon="selectedTemplate ? 'pen-to-square' : 'plus'"
      titleAlign="left"
      okText="Guardar"
      cancelText="Cancelar"
      showFooter
      :validateForm="true"
      :okLoading="templateStore.isLoading"
      @ok="handleSaveTemplate"
      @cancel="handleCancelTemplateManagement"
    >
      <a-form
        ref="templateFormRef"
        layout="vertical"
        :model="formState"
        :rules="rules"
        style="padding: 20px; background-color: #f8f8f8; border-radius: 10px"
      >
        <base-input
          v-model:value="formState.name"
          placeholder="Escribe aquí..."
          label="Nombre de la plantilla:"
          name="name"
          size="small"
        />

        <base-select-multiple
          name="languages"
          label="Seleccionar idioma(s)"
          placeholder="Selecciona"
          :showSearch="false"
          :allowClear="true"
          :options="generalStore.getLanguages"
          :filter-option="true"
          :loading="generalStore.isLoading"
          :multiple="true"
          :comma="true"
          :maxTagCount="5"
          v-model:value="formState.languages"
        />

        <a-tabs v-model:activeKey="modalActiveKey" v-if="selectedLanguagesDetailed?.length > 0">
          <a-tab-pane v-for="lang in selectedLanguagesDetailed" :key="lang.value">
            <template #tab>
              <span>{{ lang.label }}</span>
            </template>

            <a-form-item :name="['contents', lang.value, 'subject']" :rules="[]">
              <a-input
                v-model:value="contentByLanguage(lang.value).subject"
                :placeholder="`Asunto en ${lang.label}`"
              />
            </a-form-item>

            <a-form-item :name="['contents', lang.value, 'content']" :rules="[]">
              <QuillEditor
                style="background-color: #ffffff"
                theme="snow"
                v-model:content="contentByLanguage(lang.value).content"
                :placeholder="`Contenido en ${lang.label}`"
                contentType="html"
              />
            </a-form-item>
          </a-tab-pane>
        </a-tabs>
      </a-form>
    </BaseModal>

    <BaseModal
      width="400px"
      ref="templateDeleteModalRef"
      :open="modalTemplateDelete"
      title="Eliminar plantilla"
      okText="Si, continuar"
      cancelText="Cancelar"
      showFooter
      :validateForm="true"
      :okLoading="templateStore.isLoading"
      @ok="handleDeleteTemplate"
      @cancel="handleCancelDeleteTemplate"
    >
      <a-form layout="vertical">
        Estás a un paso de eliminar esta plantilla. ¿Estás seguro?
      </a-form>
    </BaseModal>

    <BaseModal
      width="400px"
      ref="templateCloneModalRef"
      :open="modalTemplateClone"
      title="Duplicar plantilla"
      okText="Si, continuar"
      cancelText="Cancelar"
      showFooter
      :validateForm="true"
      :okLoading="templateStore.isLoading"
      @ok="handleCloneTemplate"
      @cancel="handleCancelCloneTemplate"
    >
      <a-form layout="vertical" :model="cloneFormState" :rules="[]" ref="cloneFormRef">
        <a-typography-text>
          Estás a un paso de duplicar esta plantilla. ¿Estás seguro?
        </a-typography-text>
        <hr style="border: none; margin-top: 10px; margin-bottom: 15px; border-top: 15px" />
        <base-input
          v-model:value="cloneFormState.name"
          placeholder="Escribe aquí..."
          label="Nuevo nombre de la plantilla:"
          name="name"
          size="small"
        />
      </a-form>
    </BaseModal>
  </div>
</template>

<script setup>
  import { computed, reactive, ref, watch } from 'vue';
  import { useRouter } from 'vue-router';
  import BaseButton from '@/components/files/reusables/BaseButton.vue';
  import BaseModal from '@ordercontrol/components/BaseModal.vue';

  import { QuillEditor } from '@vueup/vue-quill';
  import '@vueup/vue-quill/dist/vue-quill.snow.css';

  import { useTemplateStore } from '@ordercontrol/store/template.store';
  import { useGeneralStore } from '@ordercontrol/store/general.store';

  import { notification } from 'ant-design-vue';

  import ToggleStatus from '@ordercontrol/shared/ToggleStatus.vue';
  import LanguageTag from '@ordercontrol/shared/LanguageTag.vue';

  import BaseInput from '@/components/files/reusables/BaseInput.vue';
  import BaseSelectMultiple from '@/components/files/reusables/BaseSelectMultiple.vue';

  const router = useRouter();

  const templateStore = useTemplateStore();
  const generalStore = useGeneralStore();
  const activeKey = ref('1');
  const modalActiveKey = ref(null);

  const DEFAULT_FILTER_BY = null;
  const selectedFilter = ref(DEFAULT_FILTER_BY);

  const INIT_CURRENT_PAGE_VALUE = 1;
  const INIT_PAGE_SIZE = 9;
  const DEFAULT_PAGE_SIZE_OPTIONS = [6, 9, 18];

  const templateManagementModalRef = ref(null);
  const templateDeleteModalRef = ref(null);
  const templateCloneModalRef = ref(null);
  const templateFormRef = ref(null);

  const modalTemplateManagement = ref(false);
  const modalTemplateDelete = ref(false);
  const modalTemplateClone = ref(false);
  const selectedTemplateId = ref(null);
  const selectedTemplate = ref(null);

  const initialFormState = {
    name: '',
    languages: [],
    contents: [],
  };

  const formState = reactive({ ...initialFormState });

  const cloneFormRef = ref(null);
  const templateToClone = ref({ id: null, name: '' });
  const cloneFormState = reactive({
    name: '',
  });

  const rules = {
    // name: [{ required: true, message: 'El nombre de la plantilla es requerido' }],
    // languages: [{ required: true, message: 'Debe seleccionar al menos un idioma', type: 'array' }],
  };

  const DEFAULT_FORM_FILTER = {
    filter: '',
    executiveCode: null,
    clientId: null,
    dateRange: [],
    flag_stella: false,
  };

  const formFilter = reactive(DEFAULT_FORM_FILTER);

  const props = defineProps({
    config: {
      type: Object,
      default: () => ({}),
    },
    total: {
      type: Number,
      default: 0,
    },
    isLoading: {
      type: Boolean,
      default: false,
    },
    data: {
      type: Array,
      default: () => [],
    },
    currentPage: {
      type: Number,
      default: 0,
    },
    defaultPerPage: {
      type: Number,
      default: 0,
    },
    perPage: {
      type: Number,
      default: 0,
    },
  });

  const emit = defineEmits([
    'onChange',
    'onShowSizeChange',
    'onFilterBy',
    'onFilter',
    'onRefresh',
    'handleRefreshCache',
  ]);

  const currentPageValue = ref(INIT_CURRENT_PAGE_VALUE);
  const currentPageSize = ref(INIT_PAGE_SIZE);

  const handleSaveTemplate = async () => {
    try {
      await templateFormRef.value.validate();
      const form = { ...formState };
      let success = false;
      if (selectedTemplate.value) {
        // Update
        success = await templateStore.update(selectedTemplate.value._id, form);
      } else {
        // Create
        success = await templateStore.create(form);
      }
      console.log('🚀 ~ handleSaveTemplate ~ success:', success);

      if (success) {
        notification.success({
          message: `Plantilla ${selectedTemplate.value ? 'actualizada' : 'creada'}`,
          description: `La plantilla se ha ${
            selectedTemplate.value ? 'actualizado' : 'creado'
          } correctamente.`,
        });
        activeKey.value = '1'; // Set activeKey to '1' for "Mis plantillas"
        handleCancelTemplateManagement(); // Close and reset
      }
    } catch (error) {
      console.error('Error saving template:', error);
    }
  };

  const handleCancelTemplateManagement = () => {
    modalTemplateManagement.value = false;
    selectedTemplate.value = null;
    Object.assign(formState, initialFormState, { languages: [], contents: [] });
    templateFormRef.value?.clearValidate();
  };

  const handleStatusChange = async (template, newStatus) => {
    // Guardamos el estado original en caso de que la actualización falle.
    const originalStatus = template.status;

    // Actualizamos la UI de forma optimista para una respuesta rápida.
    template.status = newStatus;

    const success = await templateStore.update(template._id, { status: newStatus });
    console.log('🚀 ~ handleStatusChange ~ success:', success);

    if (success) {
      notification.success({
        message: 'Estado actualizado',
        description: `El estado de la plantilla "${template.name}" se ha actualizado correctamente.`,
      });
    } else {
      // Si la API falla, revertimos el cambio en la UI.
      template.status = originalStatus;
      notification.error({
        message: 'Error al actualizar',
        description: 'No se pudo actualizar el estado de la plantilla.',
      });
    }
  };

  const handleDeleteTemplate = async () => {
    if (!selectedTemplateId.value) return;
    const success = await templateStore.softDelete(selectedTemplateId.value);
    if (success) {
      notification.success({
        message: 'Plantilla eliminada',
        description: 'La plantilla se ha eliminado correctamente.',
      });
    }
    // La lista se refresca automáticamente desde el store
    modalTemplateDelete.value = false;
    selectedTemplateId.value = null;
  };

  const handleCloneTemplate = async () => {
    try {
      await cloneFormRef.value.validate();
      const payload = { name: cloneFormState.name };
      const clonedTemplate = await templateStore.clone(templateToClone.value.id, payload);

      if (clonedTemplate) {
        notification.success({
          message: 'Plantilla clonada',
          description: `La plantilla ha sido clonada como "${cloneFormState.name}".`,
        });
        handleCancelCloneTemplate();
        activeKey.value = '1';
      }
    } catch (error) {
      // Validation error is handled by the form automatically
      // API error is handled by the store, but we can log it or show a notification here if needed.
      console.error('Error cloning template:', error);
      notification.error({
        message: 'Error al clonar',
        description:
          error?.response?.data?.message || 'Ocurrió un error al intentar clonar la plantilla.',
      });
    }
  };

  const handleCancelDeleteTemplate = () => {
    modalTemplateDelete.value = false;
    selectedTemplateId.value = null;
  };

  watch(formFilter, async (newFormFilter) => {
    const form = { ...newFormFilter };
    emit('onFilter', { form });
  });

  watch(
    () => props.perPage,
    (newPerPage) => {
      if (newPerPage) {
        currentPageSize.value = newPerPage;
      }
    }
  );

  watch(
    () => props.currentPage,
    (newCurrentPage) => {
      if (newCurrentPage) {
        currentPageValue.value = newCurrentPage;
      }
    }
  );

  const onChange = (page, perSize) => {
    templateStore.fetchAll({
      currentPage: page,
      perPage: perSize,
      isMyTemplates: activeKey.value === '1',
      filterBy_: selectedFilter.value?.filterBy,
      filterByType_: selectedFilter.value?.filterByType,
    });
  };

  const onShowSizeChange = (current, size) => {
    templateStore.fetchAll({
      currentPage: current,
      perPage: size,
      isMyTemplates: activeKey.value === '1',
      filterBy_: selectedFilter.value?.filterBy,
      filterByType_: selectedFilter.value?.filterByType,
    });
  };

  const isDesc = computed(() => selectedFilter?.value?.filterByType === 'desc');

  const isSelectedFilterBy = (fieldName) => selectedFilter?.value?.filterBy === fieldName;

  const onFilterBy = ({ filterBy, filterByType }) => {
    selectedFilter.value = { filterBy, filterByType };
    templateStore.fetchAll({
      currentPage: INIT_CURRENT_PAGE_VALUE,
      perPage: currentPageSize.value,
      isMyTemplates: activeKey.value === '1',
      filterBy_: filterBy,
      filterByType_: filterByType,
    });
  };

  const showTemplateManagementModal = async () => {
    handleCancelTemplateManagement(); // Reset state before showing
    if (generalStore.getLanguages.length === 0) {
      await generalStore.fetchAllLanguages();
    }
    modalTemplateManagement.value = true;
  };

  const showTemplateEditModal = async (template) => {
    if (generalStore.getLanguages.length === 0) {
      await generalStore.fetchAllLanguages();
    }
    selectedTemplate.value = template;
    Object.assign(formState, JSON.parse(JSON.stringify(template)));
    modalTemplateManagement.value = true;
  };

  const showTemplateDeleteModal = (id) => {
    modalTemplateDelete.value = true;
    selectedTemplateId.value = id;
  };

  const showTemplateCloneModal = (template) => {
    templateToClone.value = { id: template._id, name: template.name };
    cloneFormState.name = `Copia de ${template.name}`;
    modalTemplateClone.value = true;
  };

  const handleCancelCloneTemplate = () => {
    modalTemplateClone.value = false;
    templateToClone.value = { id: null, name: '' };
    cloneFormState.name = '';
  };

  const goBack = () => {
    router.back(); // retrocede a la ruta anterior
  };

  const selectedLanguagesDetailed = computed(() =>
    formState.languages
      ?.map((code) => generalStore.getLanguages.find((lang) => lang.value === code))
      .filter(Boolean)
  );

  const contentByLanguage = (langCode) =>
    formState.contents.find((item) => item.language === langCode);

  watch(
    () => formState.languages,
    (newLangs) => {
      // Remueve idiomas no seleccionados
      formState.contents = formState.contents.filter((entry) => newLangs.includes(entry.language));

      // Agrega nuevos idiomas
      newLangs.forEach((lang) => {
        const exists = formState.contents.find((entry) => entry.language === lang);
        if (!exists) {
          formState.contents.push({ language: lang, subject: '', content: '' });
        }
      });

      // Activar primer tab automáticamente
      if (newLangs.length > 0) {
        modalActiveKey.value = newLangs[0];
      }
    },
    { immediate: true }
  );

  watch(activeKey, async (newTabKey) => {
    await templateStore.fetchAll({
      currentPage: INIT_CURRENT_PAGE_VALUE,
      perPage: currentPageSize.value,
      isMyTemplates: newTabKey === '1',
      filterBy_: selectedFilter.value?.filterBy,
      filterByType_: selectedFilter.value?.filterByType,
    });
  });

  // onBeforeMount(async () => {
  //   await generalStore.fetchAllLanguages();
  // });
  const formatDate = (isoString) => {
    const date = new Date(isoString);
    const formattedDate = date.toLocaleDateString('es-ES'); // ej: "29/07/2025"
    const formattedTime = date.toLocaleTimeString('es-ES', {
      hour: '2-digit',
      minute: '2-digit',
    }); // ej: "20:05"
    return { date: formattedDate, time: formattedTime };
  };
</script>

<style scoped lang="scss">
  .base-table {
    .row {
      display: flex;
      align-items: center;
      border-radius: 6px;
      margin-bottom: 15px;
      text-align: center;
      font-size: 0.875rem;
    }

    .row-header {
      background-color: var(--files-background-1);
      color: var(--files-black-4);
      min-height: 50px;
      font-weight: 700;
    }

    .row-header-sort {
      cursor: pointer;
    }

    .container-body {
      min-height: 370px;
      // min-height: 465px;
      &.is-loading {
        background: #fafafa;
        display: flex;
        justify-content: center;
        align-items: center;
      }
    }

    .row-body {
      background-color: var(--files-gray-1);
      border: 1px solid var(--files-main-colorgray-1);
      min-height: 40px;
      font-weight: 400;
    }

    .col-small {
      flex: 1 0 0%;

      &-break {
        word-break: break-all;
        text-align: left;

        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
      }

      &-200 {
        width: 200px;
      }
    }

    .col-body-options {
      display: flex;
      justify-content: center;
      align-items: center;
      color: var(--files-main-color);
      gap: 8px;

      svg {
        cursor: pointer;
        padding: 1px;
        width: 16px;
        height: 16px;
      }

      svg:focus {
        outline: none;
      }
    }
  }

  .box-title {
    font-weight: 500;
    font-size: 12px;
    line-height: 18px;
    color: #2f353a;
  }

  .box-description {
    font-weight: 400;
    font-size: 12px;
    line-height: 18px;
  }

  .form-filter {
    display: flex;
    // gap: 0.35rem;
    gap: 1rem;
  }

  .form-vip {
    display: flex;
    flex-direction: column;
    gap: 1.75rem;
    margin-bottom: 1.625rem;
  }

  .group-vip {
    cursor: pointer;
    transition: 0.3s ease all;
    color: #c4c4c4;

    .is-vip {
      color: var(--files-exclusives);
    }

    &:hover {
      color: var(--files-exclusives);
    }
  }

  .row-pagination {
    display: flex;
    justify-content: center;
    gap: 60px;
    padding-top: 20px;
  }

  .text-uppercase {
    text-transform: uppercase;
  }

  .vip-content-click {
    font-family: var(--files-font-basic);
    width: 410px;
    height: 260px;
    // height: 337px;
    display: flex;
    flex-direction: column;
    gap: 4px;
    padding: 3px;

    &-title {
      font-weight: 700;
      font-size: 1rem;
      line-height: 23px;
      padding: 20px 12px 23px;
      text-align: center;
      letter-spacing: -0.015em;
      color: #3d3d3d;
      border-bottom: 1px solid #e9e9e9;
      margin-bottom: 1.8125rem;
    }
  }

  .quotation-content-click {
    font-family: var(--files-font-basic);
    width: 320px;
    height: 220px;
    display: flex;
    flex-direction: column;
    gap: 4px;
    padding: 3px;

    &-title {
      font-weight: 900;
      font-size: 1.2rem;
      line-height: 1.2;
    }

    &-subtitle {
      font-weight: 400;
      font-size: 0.8rem;

      span {
        text-decoration: underline;
        font-weight: 500;
      }
    }

    &-buttons {
      display: flex;
      gap: 5px;
      padding-bottom: 10px;
    }

    &-table {
      width: 310px;
      box-shadow:
        0 3px 6px -2px rgb(0 0 0 / 20%),
        0 6px 12px rgb(0 0 0 / 10%);
      margin-top: 5px;

      & :deep(.ant-table-cell) {
        font-size: 0.7rem;
      }
    }
  }

  .details-content-click {
    font-family: var(--files-font-basic);
    width: 275px;
    height: 220px;
    display: flex;
    flex-direction: column;
    gap: 4px;
    padding: 3px;

    font-size: 0.875rem;
    line-height: 1.3125;
    letter-spacing: 0.015em;
    color: var(--files-black-2);

    &-title {
      font-weight: 700;
      text-align: center;
      text-transform: uppercase;
    }

    &-subtitle {
      font-weight: 600;
    }

    &-description {
      font-weight: 400;
    }
  }

  .pending-tasks-content-click {
    font-family: var(--files-font-basic);
    width: 320px;
    height: 202px;
    display: flex;
    flex-direction: column;
    gap: 4px;
    padding: 3px;

    font-size: 0.875rem;
    line-height: 1.3125;
    letter-spacing: 0.015em;

    color: var(--files-black-2);

    &-body {
      overflow-y: auto;
    }

    &-footer {
      height: 100px;
      overflow-y: auto;
      display: flex;
      justify-content: end;
    }

    &-title {
      color: var(--files-black-4);
      font-weight: 700;
      text-align: center;
    }

    &-subtitle {
      font-weight: 600;
    }

    &-description {
      font-weight: 400;
      font-size: 0.75rem;
      line-height: 1.1875;
    }
  }

  .actions-content-click {
    font-family: var(--files-font-basic);
    width: 185px;
    height: auto;
    display: flex;
    flex-direction: column;
    padding: 3px;

    font-size: 0.875rem;
    line-height: 1.3125;
    letter-spacing: 0.015em;

    &-title {
      color: var(--files-black-4);
      font-weight: 700;
      text-align: center;
    }

    &-content {
      display: flex;
      flex-direction: column;
    }
  }

  .status-row {
    &-error {
      width: 7px;
      height: 7px;
      background: #c21d3b;
      box-shadow: 0px 0px 0px 2px rgba(194, 29, 59, 0.24);
      border-radius: 100px;
    }

    &-success {
      width: 7px;
      height: 7px;
      background: #1ed790;
      box-shadow: 0px 0px 0px 2px rgba(30, 215, 144, 0.25);
      border-radius: 100px;
    }

    &-trends {
      width: 7px;
      height: 7px;
      background: #9574af;
      box-shadow: 0px 0px 0px 2px #e3d7f2;
      border-radius: 100px;
    }

    &-trends {
      width: 7px;
      height: 7px;
      background: #9574af;
      box-shadow: 0px 0px 0px 2px #e3d7f2;
      border-radius: 100px;
    }
  }

  .is-col-hidden {
    display: none;
  }
</style>
