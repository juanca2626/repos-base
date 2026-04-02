<template>
  <a-row type="flex" justify="space-between" align="middle" class="bg-light header-bar">
    <a-col>
      <h1 class="page-title">{{ template.name }}</h1>
      <p class="mb-0">{{ template.newType }} | {{ template.duration }}</p>
    </a-col>
  </a-row>

  <div class="filters-section m-4">
    <div class="search-container">
      <a-row type="flex" justify="space-between" align="middle">
        <a-col :span="18">
          <a-form :model="filters" layout="horizontal">
            <a-row :grutter="24" type="flex" justify="start" align="bottom" style="gap: 7px">
              <a-col :span="3">
                <a-form-item label="Cantidad:" name="value" class="mb-0">
                  <a-input
                    autocomplete="off"
                    type="number"
                    v-model:value="filters.quantity"
                    placeholder="3"
                    min="1"
                  />
                </a-form-item>
              </a-col>
              <a-col v-if="filters.quantity > 0">
                <a-button type="primary" :disabled="isLoading" @click="handleSearch">
                  <SearchOutlined /> Calcular costos para {{ filters.quantity }} pax<template
                    v-if="filters.quantity > 1"
                    >s</template
                  >
                </a-button>
              </a-col>
            </a-row>
          </a-form>
        </a-col>
        <a-col v-if="cash.length > 0">
          <a-button type="primary" size="large" :disabled="isLoading" @click="handleAdd">
            <PlusOutlined /> Servicios Adicionales
          </a-button>
        </a-col>
      </a-row>
    </div>
  </div>

  <div class="content-page">
    <h1 class="page-title mb-2">SERVICIOS:</h1>
    <backend-table-component
      :loading="isLoading"
      :items="cash"
      :columns="columns"
      :options="tableOptions"
      size="small"
    >
    </backend-table-component>

    <template v-if="extraCash.length > 0">
      <h1 class="page-title mb-2 mt-4">ADICIONALES:</h1>
      <backend-table-component
        :loading="isLoading"
        :items="extraCash"
        :columns="columns"
        :options="tableOptions"
        size="small"
      >
      </backend-table-component>
    </template>
  </div>

  <add-service-modal
    :visible="showModal"
    :locked="true"
    @handleOk="handleOk"
    @handleCancel="handleCancel"
  />
</template>

<script setup lang="ts">
  import { ref, onMounted, computed } from 'vue';
  import { useTemplates, useExtraServices } from '@/composables/adventure';
  import { useRoute } from 'vue-router';
  import BackendTableComponent from '@/components/global/BackendTableComponent.vue';
  import AddServiceModal from '@/components/adventure/components/AddServiceModal.vue';
  import { PlusOutlined, SearchOutlined } from '@ant-design/icons-vue';

  const route = useRoute();

  const {
    isLoading,
    fetchTemplate,
    template,
    cash,
    extraCash,
    fetchTemplateCashService,
    filtersCash,
  } = useTemplates();

  const { saveTemplateExtraService, error } = useExtraServices();

  const columns = computed(() => [
    {
      title: 'Servicio / PAX',
      dataIndex: 'name',
      key: 'name',
      customRender: (value: any, record: any) => {
        if (record?.days && record?.days.length > 0) {
          return `<p class="d-block mb-0">${value}</p><b class="d-block mb-0"><span>${record.days.length} DÍA${record.days.length > 1 ? 'S' : ''}</span></b>`;
        } else {
          return `<p class="d-block mb-0">${value}</p>`;
        }
      },
    },
    ...Array.from({ length: filtersCash.value.quantity }, (_, i) => ({
      title: i + 1,
      dataIndex: `costs`,
      key: `costs`,
      width: 50,
      align: 'center',
      customRender: (value: any) => {
        return value[i + 1];
      },
    })),
  ]);

  const filters = ref({
    quantity: filtersCash.value.quantity,
  });

  const tableOptions = {
    showActions: false,
    pagination: false,
    rowKey: '_id',
    bordered: true,
  };

  const showModal = ref(false);

  onMounted(async () => {
    const id = route.params.id;

    if (!template.value._id || template.value._id !== id) {
      await fetchTemplate(id);
    }

    await handleSearch();
  });

  const handleSearch = async () => {
    filtersCash.value.quantity = filters.value.quantity;
    await fetchTemplateCashService(route.params.id);
  };

  const handleAdd = () => {
    showModal.value = true;
  };

  const handleOk = async (_extraService: any) => {
    console.log('ExtraService: ', template.value._id, _extraService);
    await saveTemplateExtraService(template.value._id);

    if (!error.value) {
      showModal.value = false;
    }
  };

  const handleCancel = () => {
    console.log('Cancel..');
    showModal.value = false;
  };
</script>

<style scoped>
  @import '../../scss/views/adventure.scss';
</style>
