<template>
  <div class="transport-configuration-general p-5">
    <div class="header-container">
      <div class="title-text">Configuración de unidades</div>
      <button class="config-button" @click="openNewConfig">
        <PlusIcon />
        <span>Configuración</span>
      </button>
    </div>
    <div class="content-container">
      <div class="filters-row">
        <a-select
          placeholder="Filtrar por"
          class="filter-select"
          :options="filterOptions"
          :bordered="false"
        >
          <template #suffixIcon><ChevronDownIcon /></template>
        </a-select>

        <a-input placeholder="Buscar por código o tipo de unidad" class="search-input">
          <template #suffix><SearchIcon /></template>
        </a-input>
      </div>

      <div class="city-tabs-container">
        <div class="nav-arrow left" @click="scrollTabs('left')">
          <ArrowLeftGreyIcon />
        </div>
        <div class="city-tabs-scroll" ref="tabsScrollRef">
          <div
            v-for="city in configuredCities"
            :key="city.id"
            class="city-tab"
            :class="{ active: selectedCityId === city.id }"
            @click="selectedCityId = city.id"
          >
            {{ city.name }}
          </div>
        </div>
        <div class="nav-actions">
          <div v-if="isOverflowing" class="more-dots"><MoreDotsIcon /></div>
          <div class="nav-arrow right" @click="scrollTabs('right')">
            <ArrowRightRedIcon />
          </div>
        </div>
      </div>

      <div class="table-wrapper">
        <a-table
          :columns="columns"
          :data-source="displayData"
          :loading="loading"
          :pagination="false"
          :row-key="(record: any) => record.id"
          class="transport-units-table"
          :row-class-name="getRowClass"
        >
          <template #emptyText>
            <div class="custom-empty-state">No se han configurado unidades</div>
          </template>

          <template #bodyCell="{ column, record }">
            <template v-if="record.isHeader">
              <div v-if="column.key === 'capacity'" class="group-header-content">
                <div class="header-main-info">
                  <div
                    class="chevron-wrapper"
                    :class="{ collapsed: collapsedGroups.includes(record.groupId) }"
                    @click="toggleGroup(record.groupId)"
                  >
                    <GroupChevronIcon />
                  </div>
                  <BuildingIcon />
                  <span class="header-title"
                    >{{ record.segmentation }} - {{ record.activity }}</span
                  >
                </div>
                <div class="header-validity-col">
                  <span class="header-validity"
                    >Vigencia: {{ record.since }} - {{ record.until }}</span
                  >
                </div>
                <div class="header-actions-col">
                  <a-tooltip
                    placement="top"
                    title="Agregar unidades"
                    overlay-class-name="white-tooltip"
                    :overlay-inner-style="{
                      background: '#FFFFFF',
                      color: '#2F353A',
                      width: '126px',
                      height: '34px',
                      borderRadius: '8px',
                      display: 'flex',
                      alignItems: 'center',
                      justifyContent: 'center',
                      padding: '8px 12px',
                      fontSize: '12px',
                      fontWeight: '500',
                      boxShadow: '0px 2px 8px rgba(0, 0, 0, 0.15)',
                      marginLeft: '-80px',
                    }"
                  >
                    <CirclePlusIcon class="action-icon" @click="openNewConfigInStep2(record)" />
                  </a-tooltip>

                  <template v-if="record.product > 1">
                    <MinusCircleGreyIcon class="action-icon" @click="deactivateConfig(record)" />
                  </template>
                  <template v-else>
                    <a-dropdown
                      :trigger="['click']"
                      placement="bottomRight"
                      overlay-class-name="custom-header-dropdown"
                    >
                      <VerticalDotsIcon class="action-icon" />
                      <template #overlay>
                        <a-menu class="header-action-menu">
                          <a-menu-item key="edit" @click.stop="editHeaderConfig(record)">
                            <span class="menu-item-text">Editar configuración</span>
                          </a-menu-item>
                          <a-menu-item key="delete" @click.stop="deleteHeaderConfig(record)">
                            <span class="menu-item-text">Eliminar configuración</span>
                          </a-menu-item>
                        </a-menu>
                      </template>
                    </a-dropdown>
                  </template>
                </div>
              </div>
            </template>

            <template v-else-if="!collapsedGroups.includes(record.groupId)">
              <template v-if="column.key === 'capacity'">
                <span>{{ record.minCapacity }} - {{ record.maxCapacity }} pax</span>
              </template>
              <template v-else-if="column.key === 'codeName'">
                <span>{{ getTransportCodeLabel(record.code) }}</span>
              </template>
              <template v-else-if="column.key === 'usage'">
                <span>{{ getUsageLabel(record.usage) }}</span>
              </template>
              <template v-else-if="column.key === 'representative'">
                <span>{{
                  record.includeRepresentative
                    ? `Incluye (${record.representativeQty})`
                    : 'No incluye'
                }}</span>
              </template>
              <template v-else-if="column.key === 'acciones'">
                <div class="row-actions">
                  <template v-if="record.product > 1">
                    <MinusCircleBlueIcon class="action-icon-blue" @click="deactivateUnit(record)" />
                  </template>
                  <template v-else>
                    <TrashIcon class="action-icon-blue" @click="deleteUnit(record)" />
                    <SquareEditIcon class="action-icon-blue" @click="editUnitRow(record)" />
                  </template>
                </div>
              </template>
            </template>
          </template>
        </a-table>
      </div>
    </div>

    <TransportConfigurationDrawer
      v-model:open="openDrawer"
      :initial-data="editingData"
      :initial-step="initialDrawerStep"
    />

    <GenericModal
      v-model:open="modalState.open"
      :title="modalState.title"
      :body="modalState.body"
      :width="modalState.width"
      :height="modalState.height"
      :border-radius="modalState.borderRadius"
      :custom-padding="modalState.padding"
      :custom-class="modalState.class"
      @confirm="modalState.onConfirm"
    />
  </div>
</template>

<script setup lang="ts">
  import { ref, onMounted, onUnmounted, nextTick, computed, watch } from 'vue';
  import dayjs from 'dayjs';
  import {
    PlusIcon,
    ChevronDownIcon,
    SearchIcon,
    ArrowLeftGreyIcon,
    ArrowRightRedIcon,
    MoreDotsIcon,
    BuildingIcon,
    CirclePlusIcon,
    VerticalDotsIcon,
    SquareEditIcon,
    TrashIcon,
    GroupChevronIcon,
    MinusCircleGreyIcon,
    MinusCircleBlueIcon,
  } from '../icons';
  import { useTransportConfiguration } from '../composables/useTransportConfiguration';
  import TransportConfigurationDrawer from '../components/TransportConfigurationDrawer.vue';
  import GenericModal from '../components/GenericModal.vue';

  const {
    loading,
    fetchUnits,
    filterOptions,
    configuredCities,
    selectedCityId,
    transportCodeOptions,
    transportUsageOptions,
    rawGroups,
  } = useTransportConfiguration();

  const getTransportCodeLabel = (id: number) => {
    return transportCodeOptions.value.find((o: any) => o.value === id)?.label || id.toString();
  };

  const getUsageLabel = (id: number) => {
    return transportUsageOptions.value.find((o: any) => o.value === id)?.label || 'Sin asignar';
  };

  const openDrawer = ref(false);
  const editingData = ref<any>(null);
  const initialDrawerStep = ref(1);
  const tabsScrollRef = ref<HTMLElement | null>(null);
  const isOverflowing = ref(false);
  const collapsedGroups = ref<string[]>([]);

  const modalState = ref({
    open: false,
    title: '',
    body: '',
    width: 634,
    height: 342,
    padding: '40px 24px 32px 24px',
    class: 'deactivate-variant',
    borderRadius: 8,
    onConfirm: () => {},
  });

  const showModal = (config: any) => {
    modalState.value = {
      ...modalState.value,
      ...config,
      open: true,
    };
  };

  const prepareDrawer = (config: { record?: any; step?: number; units?: any[] }) => {
    const { record, step = 1, units } = config;
    const currentCity = configuredCities.value.find((c: any) => c.id === selectedCityId.value);

    const header = record?.isHeader
      ? record
      : displayData.value.find((h: any) => h.isHeader && h.groupId === record.groupId);

    editingData.value = {
      groupId: record?.groupId || null,
      city: currentCity ? currentCity.name.toLowerCase() : null,
      segmentation: header?.segmentation || null,
      activity: header?.activity || null,
      dateFrom: header?.since ? dayjs(header.since, 'DD/MM/YYYY') : null,
      dateTo: header?.until ? dayjs(header.until, 'DD/MM/YYYY') : null,
      requiresDetail: header?.requiresDetail || 0,
      activityDetail: header?.activityDetail || '',
      client: header?.client || null,
      units:
        units ||
        (record?.isHeader
          ? rawGroups.value.find((g: any) => g.groupId === record.groupId)?.items
          : []) ||
        [],
    };

    initialDrawerStep.value = step;
    openDrawer.value = true;
  };

  const toggleGroup = (groupId: string) => {
    const index = collapsedGroups.value.indexOf(groupId);
    index > -1 ? collapsedGroups.value.splice(index, 1) : collapsedGroups.value.push(groupId);
  };

  const openNewConfig = () => {
    editingData.value = null;
    initialDrawerStep.value = 1;
    openDrawer.value = true;
  };

  const editHeaderConfig = (record: any) => prepareDrawer({ record, step: 1 });

  const editUnitRow = (record: any) => {
    const group = rawGroups.value.find((g: any) => g.groupId === record.groupId);
    const singleUnit = group?.items.find((u: any) => u.id === record.id);
    prepareDrawer({
      record,
      step: 2,
      units: singleUnit ? [{ ...singleUnit, isEditing: true }] : [],
    });
  };

  const openNewConfigInStep2 = (record: any) => {
    prepareDrawer({
      record,
      step: 2,
      units: [
        {
          code: null,
          units: 1,
          usage: null,
          minCapacity: '',
          maxCapacity: '',
          includeRepresentative: false,
          representativeQty: '',
          isEditing: true,
        },
      ],
    });
  };

  const deleteHeaderConfig = (record: any) => {
    showModal({
      title: '¿Estás seguro de eliminar la configuración?',
      body: `La configuración <b>${record.segmentation} - ${record.activity}</b> tiene agregada 4 unidades vehiculares. <b>¿Desea continuar?</b>`,
      onConfirm: () => {
        console.log('Delete header', record);
        modalState.value.open = false;
      },
    });
  };

  const deactivateConfig = (record: any) => {
    const productCount = record.product || 0;
    const products = record.productsItem ? record.productsItem.join(', ') : '';
    showModal({
      title: '¿Estás seguro de desactivar la configuración?',
      body: `La configuración <b>${record.segmentation} - ${record.activity}</b> está enlazada a ${productCount} productos: <b>${products}</b>, está seguro de querer desactivarla. <b>¿Desea continuar?</b>`,
      height: 306,
      onConfirm: () => {
        modalState.value.open = false;
      },
    });
  };

  const deactivateUnit = (record: any) => {
    const label = getTransportCodeLabel(record.code);
    const productCount = record.productsItem?.length || 0;
    const products = record.productsItem ? record.productsItem.join(', ') : '';
    showModal({
      title: '¿Estás seguro de desactivar la unidad configurada?',
      body: `La unidad <b>${label}</b> está enlazada a ${productCount} tipos de productos: <b>${products}</b>. Está seguro de querer desactivarla. <b>¿Desea continuar?</b>`,
      onConfirm: () => {
        modalState.value.open = false;
      },
    });
  };

  const deleteUnit = (record: any) => {
    showModal({
      body: `¿Está seguro de eliminar la unidad ${getTransportCodeLabel(record.code)} con capacidad de ${record.minCapacity} - ${record.maxCapacity} pax?`,
      height: 218,
      borderRadius: 16,
      onConfirm: () => {
        modalState.value.open = false;
      },
    });
  };

  watch(openDrawer, (val) => {
    if (!val) editingData.value = null;
  });

  const displayData = computed(() => {
    const result: any[] = [];
    rawGroups.value.forEach((group: any) => {
      result.push({
        id: `h-${group.groupId}`,
        groupId: group.groupId,
        isHeader: true,
        segmentation: group.segmentation,
        activity: group.activity,
        since: group.since,
        until: group.until,
        product: group.product,
        productsItem: group.productsItem,
      });
      if (!collapsedGroups.value.includes(group.groupId)) {
        group.items.forEach((item: any) =>
          result.push({
            ...item,
            groupId: group.groupId,
            isHeader: false,
            product: group.product,
            productsItem: item.productsItem,
          })
        );
      }
    });
    return result;
  });

  const getRowClass = (record: any) => (record.isHeader ? 'header-row-style' : 'data-row-style');

  const columns = computed(() => [
    {
      title: 'Capacidad pax',
      dataIndex: 'capacity',
      key: 'capacity',
      width: '20%',
      align: 'end',
      customCell: (record: any) => (record.isHeader ? { colSpan: 6 } : { colSpan: 1 }),
      class: 'no-padding-cell',
    },
    {
      title: 'Cód. / Nombre',
      dataIndex: 'codeName',
      key: 'codeName',
      width: '25%',
      align: 'center',
      customCell: (record: any) => (record.isHeader ? { colSpan: 0 } : { colSpan: 1 }),
    },
    {
      title: 'Cant. Unidades',
      dataIndex: 'units',
      key: 'units',
      width: '15%',
      align: 'center',
      customCell: (record: any) => (record.isHeader ? { colSpan: 0 } : { colSpan: 1 }),
    },
    {
      title: 'Uso Unidades',
      dataIndex: 'usage',
      key: 'usage',
      width: '15%',
      align: 'center',
      customCell: (record: any) => (record.isHeader ? { colSpan: 0 } : { colSpan: 1 }),
    },
    {
      title: 'Representate',
      dataIndex: 'representative',
      key: 'representative',
      width: '15%',
      align: 'center',
      customCell: (record: any) => (record.isHeader ? { colSpan: 0 } : { colSpan: 1 }),
    },
    {
      title: 'Acciones',
      key: 'acciones',
      width: '10%',
      align: 'center',
      customCell: (record: any) => (record.isHeader ? { colSpan: 0 } : { colSpan: 1 }),
    },
  ]);

  const checkOverflow = () => {
    if (tabsScrollRef.value)
      isOverflowing.value = tabsScrollRef.value.scrollWidth > tabsScrollRef.value.clientWidth;
  };

  const scrollTabs = (direction: 'left' | 'right') => {
    if (!tabsScrollRef.value) return;
    tabsScrollRef.value.scrollBy({ left: direction === 'left' ? -200 : 200, behavior: 'smooth' });
  };

  onMounted(async () => {
    fetchUnits();
    await nextTick();
    checkOverflow();
    window.addEventListener('resize', checkOverflow);
  });
  onUnmounted(() => window.removeEventListener('resize', checkOverflow));
</script>

<style lang="scss">
  @import '../styles/TransportConfigurationGeneral.scss';
</style>
