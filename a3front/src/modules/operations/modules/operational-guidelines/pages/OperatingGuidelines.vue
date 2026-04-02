<template>
  <div class="module-operations" style="padding-bottom: 10px">
    <a-title-section title="Pautas Operativas" icon="calendar" />

    <a-card :style="{ boxShadow: 'none' }">
      <a-form layout="vertical" :model="formSearch">
        <a-row>
          <a-col :span="7">
            <a-form-item label="Escoger:">
              <a-radio-group v-model:value="formSearch.type">
                <a-radio value="C" :disabled="loading">Cliente</a-radio>
                <a-radio value="M" :disabled="loading">Mercado</a-radio>
                <a-radio value="S" :disabled="loading">Serie</a-radio>
              </a-radio-group>
            </a-form-item>
          </a-col>
          <a-col :span="7">
            <!-- Condicional para mostrar select de Cliente -->
            <a-form-item
              v-if="['C', 'M', 'S'].includes(formSearch.type)"
              :label="`Escoger ${getLabel(formSearch.type)}:`"
            >
              <!-- <a-select
                show-search
                :placeholder="getPlaceholder(formSearch.type)"
                v-model:value="selectedOwner"
                :options="ownersOptions ?? []"
                :loading="loading"
                :disabled="loading"
                label-in-value
              ></a-select> -->

              <a-select
                show-search
                :placeholder="getPlaceholder(formSearch.type)"
                v-model:value="selectedOwner"
                v-model:search-value="searchQuery"
                @search="(value: string) => (searchQuery = value)"
                :options="ownersOptions"
                :filter-option="false"
                label-in-value
              >
                <template v-if="dataStore.loading" #notFoundContent>
                  <div
                    style="
                      display: flex;
                      justify-content: center;
                      align-items: center;
                      height: 100%;
                    "
                  >
                    <a-spin size="small" />
                  </div>
                </template>
                <template #suffixIcon>
                  <SearchOutlined />
                </template>
              </a-select>
            </a-form-item>
          </a-col>
          <a-col class="bottom-aligned" :span="5">
            <!-- <a-form-item>
              <a-button
                type="link"
                :style="{ color: '#bd0d12', paddingBottom: '0', height: 'auto' }"
                size="large"
              >
                <svg
                  class="colorPrimary_SVG"
                  xmlns="http://www.w3.org/2000/svg"
                  viewBox="0 0 576 512"
                  width="24"
                  height="24"
                >
                  <path
                    d="M234.7 42.7L197 56.8c-3 1.1-5 4-5 7.2s2 6.1 5 7.2l37.7 14.1L248.8 123c1.1 3 4 5 7.2 5s6.1-2 7.2-5l14.1-37.7L315 71.2c3-1.1 5-4 5-7.2s-2-6.1-5-7.2L277.3 42.7 263.2 5c-1.1-3-4-5-7.2-5s-6.1 2-7.2 5L234.7 42.7zM46.1 395.4c-18.7 18.7-18.7 49.1 0 67.9l34.6 34.6c18.7 18.7 49.1 18.7 67.9 0L529.9 116.5c18.7-18.7 18.7-49.1 0-67.9L495.3 14.1c-18.7-18.7-49.1-18.7-67.9 0L46.1 395.4zM484.6 82.6l-105 105-23.3-23.3 105-105 23.3 23.3zM7.5 117.2C3 118.9 0 123.2 0 128s3 9.1 7.5 10.8L64 160l21.2 56.5c1.7 4.5 6 7.5 10.8 7.5s9.1-3 10.8-7.5L128 160l56.5-21.2c4.5-1.7 7.5-6 7.5-10.8s-3-9.1-7.5-10.8L128 96 106.8 39.5C105.1 35 100.8 32 96 32s-9.1 3-10.8 7.5L64 96 7.5 117.2zm352 256c-4.5 1.7-7.5 6-7.5 10.8s3 9.1 7.5 10.8L416 416l21.2 56.5c1.7 4.5 6 7.5 10.8 7.5s9.1-3 10.8-7.5L480 416l56.5-21.2c4.5-1.7 7.5-6 7.5-10.8s-3-9.1-7.5-10.8L480 352l-21.2-56.5c-1.7-4.5-6-7.5-10.8-7.5s-9.1 3-10.8 7.5L416 352l-56.5 21.2z"
                  />
                </svg>
                Limpiar filtros
              </a-button>
            </a-form-item> -->
          </a-col>
          <a-col class="bottom-aligned" :span="5">
            <a-form-item>
              <a-button
                :icon="h(SearchOutlined)"
                block="true"
                danger
                size="large"
                :style="{ color: '#bd0d12' }"
                @click="handleSearch"
                :disabled="loading"
                >Buscar</a-button
              >
            </a-form-item>
          </a-col>
        </a-row>
      </a-form>
    </a-card>

    <a-card v-if="owner" :style="{ backgroundColor: '#f9f9f9', border: 'none', boxShadow: 'none' }">
      <a-row>
        <a-col :span="19">
          <a-flex justify="space-between" align="center">
            <a-typography-title
              :level="5"
              :style="{ marginTop: '9px', marginBottom: '0px', fontWeight: '700' }"
            >
              <span :style="{ color: '#bd0d12' }">[{{ owner?.code }}]</span>
              {{ owner?.name }}
              <!-- 
              <a-tag :style="{ color: '#2E2B9E', backgroundColor: '#EDEDFF' }">Vegetariano</a-tag>
              <a-tag :style="{ color: '#2E2B9E', backgroundColor: '#EDEDFF' }">LGTB</a-tag> -->
            </a-typography-title>
          </a-flex>
        </a-col>

        <a-col :span="5">
          <a-flex justify="flex-end" align="center">
            <a-button
              :icon="h(PlusSquareOutlined)"
              type="primary"
              size="large"
              block="true"
              :btn="{ title: '+ Crear Bloqueo', action: 'showDrawer' }"
              @click="handlerShowDrawer(true, false)"
              @handlerShowDrawer="handlerShowDrawer($event)"
            >
              Crear nueva pauta
            </a-button>

            <GuidelineManagementComponent
              :showDrawer="showDrawer"
              @handlerShowDrawer="handlerShowDrawer($event)"
            />
          </a-flex>
        </a-col>
      </a-row>
      <template v-if="owner.type === 'C'">
        <a-divider />
        <a-row :style="{ marginBottom: '20px' }" v-if="owner?.executives.length > 0">
          <a-col>
            <LabelComponent
              :source="'fontAwesome'"
              :type="'text'"
              :icon="'user'"
              :title="'Ejecutivas'"
              :direction="{ direction: 'horizontal', size: 20 }"
              :content="owner.executives"
            />
          </a-col>
        </a-row>
        <a-row :style="{ marginBottom: '20px' }">
          <a-col :span="6" v-if="owner?.emergency_contact">
            <LabelComponent
              :source="'fontAwesome'"
              :type="'text'"
              :icon="'user'"
              :title="'Contacto de emergencia'"
              :direction="{ direction: 'vertical', size: 0 }"
              :content="[{ value: owner?.emergency_contact }]"
            />
          </a-col>
          <a-col :span="6" v-if="owner.phone">
            <LabelComponent
              :source="'antDesign'"
              :type="'link'"
              :icon="'phone'"
              :title="'Teléfono de emergencia'"
              :direction="{ direction: 'vertical', size: 0 }"
              :content="[{ value: owner?.phone, link: 'tel:' + owner.phone }]"
            />
          </a-col>
          <a-col :span="6" v-if="owner.email">
            <LabelComponent
              :source="'antDesign'"
              :type="'link'"
              :icon="'email'"
              :title="'Correo de emergencia'"
              :direction="{ direction: 'vertical', size: 0 }"
              :content="[{ value: owner.email, link: 'mailto:' + owner.email }]"
            />
          </a-col>
          <a-col :span="6">
            <LabelComponent
              :type="'link'"
              :source="'antDesign'"
              :icon="'link'"
              :title="'Logo especial'"
              :direction="{ direction: 'vertical', size: 0 }"
              :content="[{ value: 'Ver logo especial', link: owner?.logo }]"
            />
          </a-col>
        </a-row>
        <a-row :style="{ marginBottom: '20px' }">
          <a-col :span="24" v-if="owner.comercial_contact.length > 0">
            <LabelComponent
              :source="'antDesign'"
              :type="'link'"
              :icon="'email'"
              :title="'Contactos'"
              :direction="{ direction: 'vertical', size: 0 }"
              :content="
                owner.comercial_contact.map((contact: any) => ({
                  value: contact.email,
                  link: 'mailto:' + contact.email,
                  alt: contact.fullname,
                }))
              "
            />
          </a-col>
        </a-row>
      </template>
    </a-card>

    <a-row style="margin: 30px 20px 0px 20px">
      <a-col :span="19">
        <a-typography-title
          :level="5"
          :style="{ marginTop: '5px', marginBottom: '0px', fontWeight: '700' }"
        >
          Pautas operativas
        </a-typography-title>
      </a-col>
      <a-col :span="5">
        <a-form-item>
          <a-select
            placeholder="Elegir sede"
            v-model:value="selectedFilter"
            :disabled="!operationalGuidelines"
          >
            <a-select-option value="ALL">Todos</a-select-option>
            <a-select-option value="AQP">Arequipa</a-select-option>
            <a-select-option value="CUS">Cusco</a-select-option>
            <a-select-option value="LIM">Lima</a-select-option>
            <a-select-option value="PUN">Puno</a-select-option>
          </a-select>
        </a-form-item>
      </a-col>
    </a-row>

    <a-row style="margin: 0px 20px 10px 20px" v-if="!operationalGuidelines">
      <a-col :span="24">
        <a-typography-title :level="5" :style="{ fontWeight: '700' }">
          No existen pautas operativas.
        </a-typography-title>
      </a-col>
    </a-row>

    <a-row style="margin: 0px 20px 10px 20px" v-if="operationalGuidelines">
      <a-col :span="24">
        <a-space direction="vertical" :style="{ width: '100%' }">
          <!-- 
            <a-collapse
              v-model:activeKey="activeKey"
              collapsible="header"
              expandIconPosition="right"
            >
              <a-collapse-panel key="1" header="Todas las sedes" :update-date="updateDates[1]">
                <a-table :columns="columns" :data-source="allSedesData" :pagination="false">
                  <template #bodyCell="{ column, record }">
                    <template v-if="column.dataIndex === 'actions'">
                      <a-space>
                        <font-awesome-icon icon="pen-to-square" />
                        <font-awesome-icon icon="trash-can" />
                      </a-space>
                    </template>
                  </template>
                </a-table>
              </a-collapse-panel>
            </a-collapse> 
            -->

          <!-- 
            <a-collapse v-model:activeKey="activeKey" collapsible="icon">
              <a-collapse-panel key="2" header="Lima" :update-date="updateDates[2]">
                <a-alert
                  message="Si requieres una configuración de transporte distinta a la mostrada, contáctate con neg@limatours.com.pe"
                  type="warning"
                  show-icon
                  :style="{ marginBottom: '15px' }"
                />
                <a-table :columns="columnsLima" :data-source="limaData" :pagination="false">
                  <template #bodyCell="{ column, record }">
                    <template v-if="column.dataIndex === 'actions'">
                      <a-space>
                        <font-awesome-icon icon="pen-to-square" />
                        <font-awesome-icon icon="trash-can" />
                      </a-space>
                    </template>
                    <template
                      v-if="column.dataIndex === 'observaciones' && record.tipo === 'Guías'"
                    >
                      <a-space>
                        <a-tag class="aTag-gui aTag-gui-prefered">
                          <strong>Preferente</strong><br />Nombre Apellido
                        </a-tag>
                        <a-tag class="aTag-gui aTag-gui-blocked">
                          <strong>Bloqueado</strong><br />Nombre Apellido
                        </a-tag>
                        <a-tag class="aTag-gui aTag-gui-blocked">
                          <strong>Bloqueado</strong><br />Nombre Apellido
                        </a-tag>
                      </a-space>
                    </template>
                    <template
                      v-if="column.dataIndex === 'observaciones' && record.tipo === 'Transporte'"
                    >
                      <a-space>
                        <a-tag class="aTag-trp"><strong>TAUT</strong><br />1 PAX</a-tag>
                        <a-tag class="aTag-trp"><strong>TH01</strong><br />2-4 PAXS</a-tag>
                        <a-tag class="aTag-trp"><strong>TSPC</strong><br />5-9 PAXS</a-tag>
                        <a-tag class="aTag-trp"><strong>TSPL</strong><br />10-14 PAXS</a-tag>
                        <a-tag class="aTag-trp"><strong>TMN8</strong><br />15-22 PAXS</a-tag>
                        <a-tag class="aTag-trp"><strong>TBUS</strong><br />23-42 PAXS</a-tag>
                      </a-space>
                    </template>
                  </template>
                </a-table>
              </a-collapse-panel>
            </a-collapse> 
            -->

          <!-- <pre>
            {{ data }}
            </pre> -->

          <!-- Primer Collapse -->
          <template v-for="(v, i) in operationalGuidelines?.headquarters" :key="i">
            <a-collapse
              v-model:activeKey="activeKey"
              collapsible="header"
              expandIconPosition="right"
              v-if="
                v.guidelines.length > 0 &&
                (selectedFilter === 'ALL' || selectedFilter === v.headquarter.code)
              "
              :key="i"
            >
              <a-collapse-panel :key="i">
                <template #header>
                  <div class="collapse-header">
                    {{ v.headquarter.description }}
                    <!-- <span class="update-date">17/10/2022</span> -->
                  </div>
                </template>
                <a-table :columns="columns" :data-source="data[i]" :pagination="false">
                  <template #bodyCell="{ column, record }">
                    <!-- 
                    <pre>
                    {{ column }}
                    </pre>
                     -->
                    <!-- 
                     <pre>
                    {{ record }}
                    </pre> 
                    -->
                    <template
                      v-if="
                        column.dataIndex === 'observaciones' &&
                        (record.code === 'REP' || record.code === 'GUI' || record.code === 'ESC')
                      "
                    >
                      <!-- <a-space> -->
                      <a-tag
                        v-for="(v2, i2) in record.observaciones"
                        :key="i2"
                        class="aTag-gui"
                        :class="
                          v2.category === 'preferred' ? 'aTag-gui-prefered' : 'aTag-gui-blocked'
                        "
                        style="margin: 4px 8px 4px 0"
                      >
                        <strong v-if="v2.category === 'preferred'">Preferente</strong
                        ><strong v-if="v2.category === 'blocked'">Bloqueado</strong><br />
                        {{ `(${v2.code}) - ${v2.fullname}` }}
                      </a-tag>
                      <!-- </a-space> -->
                    </template>

                    <!--
                    <template
                      v-if="column.dataIndex === 'observaciones' && record.tipo === 'Transporte'"
                    >
                      <a-space>
                        <a-tag class="aTag-trp"><strong>TAUT</strong><br />1 PAX</a-tag>
                        <a-tag class="aTag-trp"><strong>TH01</strong><br />2-4 PAXS</a-tag>
                        <a-tag class="aTag-trp"><strong>TSPC</strong><br />5-9 PAXS</a-tag>
                        <a-tag class="aTag-trp"><strong>TSPL</strong><br />10-14 PAXS</a-tag>
                        <a-tag class="aTag-trp"><strong>TMN8</strong><br />15-22 PAXS</a-tag>
                        <a-tag class="aTag-trp"><strong>TBUS</strong><br />23-42 PAXS</a-tag>
                      </a-space>
                    </template>
                    -->

                    <template v-if="column.dataIndex === 'actions'">
                      <a-space>
                        <!-- <font-awesome-icon
                            icon="pen-to-square"
                            @click="editGuideline(record.headquarter_id, record.guideline_id)"
                          /> -->

                        <font-awesome-icon
                          :icon="['far', 'pen-to-square']"
                          size="lg"
                          :style="{ outline: 'none' }"
                          @click="editGuideline(record.headquarter_id, record.guideline_id)"
                        />

                        <a-popconfirm
                          title="¿Seguro que quieres eliminar esta pauta operativa?"
                          ok-text="Si"
                          cancel-text="No"
                          @confirm="deleteGuideline(record.headquarter_id, record.guideline_id)"
                        >
                          <font-awesome-icon
                            :icon="['far', 'trash-can']"
                            size="lg"
                            :style="{ outline: 'none' }"
                          />
                        </a-popconfirm>
                      </a-space>
                    </template>
                  </template>
                </a-table>
              </a-collapse-panel>
            </a-collapse>
          </template>

          <!-- Segundo Collapse -->
          <!--
            <a-collapse
              v-model:activeKey="activeKey"
              collapsible="header"
              expandIconPosition="right"
            >
              <a-collapse-panel key="2">
                <template #header>
                  <div class="collapse-header">
                    Lima
                    <span class="update-date">{{ updateDates[2] }}</span>
                  </div>
                </template>
                <a-alert
                  message="Si requieres una configuración de transporte distinta a la mostrada, contáctate con neg@limatours.com.pe"
                  type="warning"
                  show-icon
                  :style="{ marginBottom: '15px' }"
                />
                <a-table :columns="columns" :data-source="limaData" :pagination="false">
                  <template #bodyCell="{ column, record }">
                    <template v-if="column.dataIndex === 'actions'">
                      <a-space>
                        <font-awesome-icon icon="pen-to-square" />
                        <font-awesome-icon icon="trash-can" />
                      </a-space>
                    </template>
                    <template
                      v-if="column.dataIndex === 'observaciones' && record.tipo === 'Guías'"
                    >
                      <a-space>
                        <a-tag class="aTag-gui aTag-gui-prefered">
                          <strong>Preferente</strong><br />Nombre Apellido
                        </a-tag>
                        <a-tag class="aTag-gui aTag-gui-blocked">
                          <strong>Bloqueado</strong><br />Nombre Apellido
                        </a-tag>
                        <a-tag class="aTag-gui aTag-gui-blocked">
                          <strong>Bloqueado</strong><br />Nombre Apellido
                        </a-tag>
                      </a-space>
                    </template>
                    <template
                      v-if="column.dataIndex === 'observaciones' && record.tipo === 'Transporte'"
                    >
                      <a-space>
                        <a-tag class="aTag-trp"><strong>TAUT</strong><br />1 PAX</a-tag>
                        <a-tag class="aTag-trp"><strong>TH01</strong><br />2-4 PAXS</a-tag>
                        <a-tag class="aTag-trp"><strong>TSPC</strong><br />5-9 PAXS</a-tag>
                        <a-tag class="aTag-trp"><strong>TSPL</strong><br />10-14 PAXS</a-tag>
                        <a-tag class="aTag-trp"><strong>TMN8</strong><br />15-22 PAXS</a-tag>
                        <a-tag class="aTag-trp"><strong>TBUS</strong><br />23-42 PAXS</a-tag>
                      </a-space>
                    </template>
                  </template>
                </a-table>
              </a-collapse-panel>
            </a-collapse>
            -->
        </a-space>
      </a-col>
    </a-row>
  </div>
</template>

<script setup lang="ts">
  import { onMounted, computed, h, ref, watch } from 'vue';
  import { storeToRefs } from 'pinia';
  import { SearchOutlined, PlusSquareOutlined } from '@ant-design/icons-vue';
  import ATitleSection from '@/components/backend/ATitleSection.vue';
  import LabelComponent from '@operations/modules/operational-guidelines/components/LabelComponent.vue';
  import GuidelineManagementComponent from '@operations/modules/operational-guidelines/components/GuidelineManagementComponent.vue';

  import { useDataStore } from '@operations/modules/operational-guidelines/store/data.store';
  import { useDrawerStore } from '@operations/modules/operational-guidelines/store/drawer.store';
  import { useOptionsStore } from '@operations/modules/operational-guidelines/store/options.store';
  import { useFormStore } from '@operations/modules/operational-guidelines/store/form.store';
  import { useDebouncedSearch } from '@/hooks/useDebouncedSearch';

  // drawer.store.ts
  const drawerStore = useDrawerStore();
  const { handlerShowDrawer } = useDrawerStore();
  const { showDrawer } = storeToRefs(drawerStore);

  //data.store.ts
  const dataStore = useDataStore();
  const { owner, operationalGuidelines } = storeToRefs(dataStore);

  //options.store.ts
  const optionsStore = useOptionsStore();
  const { ownersOptions } = storeToRefs(optionsStore);

  //form.store.ts
  const formStore = useFormStore();
  const { handleSearch, editGuideline, deleteGuideline } = formStore;
  const { formSearch, selectedOwner, loading } = storeToRefs(formStore);

  const searchOwners = (query: string) => {
    const type = formSearch.value.type;
    dataStore.getOwners(type, query);
  };
  const { searchQuery } = useDebouncedSearch(searchOwners, 500);

  // Funciones para obtener la etiqueta y el placeholder dinámicamente
  const getLabel = (type: string): string => {
    switch (type) {
      case 'C':
        return 'cliente';
      case 'M':
        return 'mercado';
      case 'S':
        return 'serie';
      default:
        return '';
    }
  };

  const getPlaceholder = (type: string): string => {
    switch (type) {
      case 'C':
        return 'Nombre del cliente';
      case 'M':
        return 'Nombre del mercado';
      case 'S':
        return 'Nombre de la serie';
      default:
        return '';
    }
  };

  // Formulario de búsqueda
  // const formSearch = reactive({
  //   type: 'C',
  //   owner: { _id: '', code: '', name: '' } as Owner,
  // });

  // Método para manejar la búsqueda
  // const handleSearch = async () => {
  //   //TODO: Revisar
  //   if (formSearch.owner._id || formSearch.owner.code) {
  //     // Llama a la función getOperationalGuidelines con el ID del owner seleccionado
  //     await getOperationalGuidelines(formSearch.owner.code);
  //   } else {
  //     console.error('Seleccione un cliente o mercado antes de buscar.');
  //     // Puedes agregar una notificación al usuario aquí si es necesario
  //   }
  // };

  // const activeKey = ref(['0']);
  const activeKey = ref([]);
  const selectedFilter = ref('ALL'); // Por defecto, selecciona 'ALL'

  // const activeKey = computed(() => {
  //   if (!operationalGuidelines.value) return [];
  //   return operationalGuidelines.value.headquarters.map((_, index) => index.toString());
  // });

  watch(
    operationalGuidelines,
    (newVal) => {
      if (newVal) {
        activeKey.value = newVal.headquarters.map((_, index) => index.toString());
      }
    },
    { immediate: true }
  );

  const columns = [
    { title: 'Tipo de pauta', dataIndex: 'tipo', key: 'tipo' },
    { title: 'Detalle', dataIndex: 'detalle', key: 'detalle' },
    { title: 'Observaciones', dataIndex: 'observaciones', key: 'observaciones' },
    { title: 'Acciones', dataIndex: 'actions', key: 'actions' },
  ];

  // Función para construir la variable `data` a partir de `operationalGuidelines`.
  // Computed para adaptar la data en un array de arrays
  const data = computed(() => {
    if (!operationalGuidelines.value) return [];

    // Crear un array para cada headquarters
    return operationalGuidelines.value.headquarters.map((hq: unknown) => {
      return hq.guidelines
        .map((v: unknown) => {
          // console.log('🚀 ~ .map ~ v:', v);
          // console.log('🚀 ~ .map ~ i:', i);

          // Verificar si el guideline es de tipo REP/GUI/ESC

          const { guideline, options, observations } = v;

          if (guideline.code === 'REP' || guideline.code === 'GUI' || guideline.code === 'ESC') {
            // Agrupar todos los entities dentro de un solo array y agregar la categoría a cada uno
            const observaciones = options.flatMap((option: unknown) =>
              option.entities.map((entity: unknown) => ({
                ...entity,
                category: option.category, // Agregar la categoría (blocked o preferred)
              }))
            );

            return {
              headquarter_id: hq.headquarter._id, // Agregar headquarter_id
              guideline_id: guideline._id, // Agregar guideline_id
              code: guideline.code,
              tipo: guideline.description, // Asignar la descripción de la guideline
              detalle: 'Preferentes y bloqueados', // Mostrar detalle específico para REP
              observaciones: observaciones.length ? observaciones : [], // Asignar el array de entities o vacío si no hay
            };
          }

          // Procesar normalmente otros guidelines
          return options.map((option: unknown) => {
            // Buscar el valor correspondiente en options basado en la categoría
            const matchingGuidelineOption = guideline.options.find(
              (opt: unknown) => opt.category === option.category
            );

            // Determinar el detalle basado en los valores coincidentes
            let detalle = option.values
              .map((value: unknown) => {
                const matchingValue = matchingGuidelineOption?.values.find((v1: unknown) => {
                  if (typeof value === 'boolean') return true;
                  return v1.value === value;
                });
                return matchingValue ? matchingValue.description : value;
              })
              .join(', ');

            return {
              headquarter_id: hq.headquarter._id, // Agregar headquarter_id
              guideline_id: guideline._id, // Agregar guideline_idcode: guideline.code,
              tipo: guideline.description, // Asignar la descripción de la guideline
              detalle: detalle || 'Sin detalle', // Mostrar detalle o un mensaje predeterminado
              observaciones: observations || '', // Asignar observaciones si existen
            };
          });
        })
        .flat(); // Aplanar para tener un array por headquarters
    });
  });

  onMounted(async () => {
    await dataStore.syncData();
  });
</script>

<style lang="scss" scoped>
  .colorPrimary_SVG {
    fill: #bd0d12;
    margin-right: 5px;
  }

  .ant-card {
    border: 1px solid var(--White-5, #e7e7e7);
    background: var(--White, #fff);
    margin: 20px;
    padding: 15px 15px 0px 15px;
  }

  .bottom-aligned {
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
  }

  ::v-deep(.ant-collapse) {
    border-color: #1284ed;
    .ant-collapse-item {
      border-color: #1284ed;
      .ant-collapse-header {
        // border-radius: 8px;
        // border-color: #1284ed;
        background-color: #f1f8ff;
        border-bottom: 1px solid #1284ed;
        border-radius: 8px 8px 0 0;
        color: #1284ed;
        .ant-collapse-expand-icon {
          position: absolute;
          right: 5px;
        }
        .ant-collapse-header-text {
          font-weight: 600;
          font-size: 16px;
          margin-left: 50px;
        }
        .ant-collapse-header-text::before {
          content: '';
          position: absolute;
          left: 20px;
          top: 50%;
          transform: translateY(-50%);
          width: 20px; // Ajusta según el tamaño del ícono
          height: 20px; // Ajusta según el tamaño del ícono
          background: no-repeat center/contain;
          background-image: url('data:image/svg+xml;charset=UTF-8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="%231284ed" d="M384 48c8.8 0 16 7.2 16 16l0 384c0 8.8-7.2 16-16 16L96 464c-8.8 0-16-7.2-16-16L80 64c0-8.8 7.2-16 16-16l288 0zM96 0C60.7 0 32 28.7 32 64l0 384c0 35.3 28.7 64 64 64l288 0c35.3 0 64-28.7 64-64l0-384c0-35.3-28.7-64-64-64L96 0zM240 256a64 64 0 1 0 0-128 64 64 0 1 0 0 128zm-32 32c-44.2 0-80 35.8-80 80c0 8.8 7.2 16 16 16l192 0c8.8 0 16-7.2 16-16c0-44.2-35.8-80-80-80l-64 0zM512 80c0-8.8-7.2-16-16-16s-16 7.2-16 16l0 64c0 8.8 7.2 16 16 16s16-7.2 16-16l0-64zM496 192c-8.8 0-16 7.2-16 16l0 64c0 8.8 7.2 16 16 16s16-7.2 16-16l0-64c0-8.8-7.2-16-16-16zm16 144c0-8.8-7.2-16-16-16s-16 7.2-16 16l0 64c0 8.8 7.2 16 16 16s16-7.2 16-16l0-64z"/></svg>');
        }
        /*
        .ant-collapse-header-text::after {
          // content: 'Actualizado el 15/10/2023';
          content: attr(data-update-date);
          color: #bdbdbd;
          font-size: 12px;
          margin-left: 20px;
          padding-left: 20px;
          background-image: url('data:image/svg+xml;charset=UTF-8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="%23bdbdbd" d="M384 48c8.8 0 16 7.2 16 16l0 384c0 8.8-7.2 16-16 16L96 464c-8.8 0-16-7.2-16-16L80 64c0-8.8 7.2-16 16-16l288 0zM96 0C60.7 0 32 28.7 32 64l0 384c0 35.3 28.7 64 64 64l288 0c35.3 0 64-28.7 64-64l0-384c0-35.3-28.7-64-64-64L96 0zM240 256a64 64 0 1 0 0-128 64 64 0 1 0 0 128zm-32 32c-44.2 0-80 35.8-80 80c0 8.8 7.2 16 16 16l192 0c8.8 0 16-7.2 16-16c0-44.2-35.8-80-80-80l-64 0zM512 80c0-8.8-7.2-16-16-16s-16 7.2-16 16l0 64c0 8.8 7.2 16 16 16s16-7.2 16-16l0-64zM496 192c-8.8 0-16 7.2-16 16l0 64c0 8.8 7.2 16 16 16s16-7.2 16-16l0-64c0-8.8-7.2-16-16-16zm16 144c0-8.8-7.2-16-16-16s-16 7.2-16 16l0 64c0 8.8 7.2 16 16 16s16-7.2 16-16l0-64z"/></svg>');
          background-repeat: no-repeat;
          background-position-x: 0px;
        }
        */
      }
      .ant-collapse-content-box {
        background-color: white;
        border-radius: 8px;
        border-color: #1284ed;
      }
    }
  }

  ::v-deep(.ant-table) {
    .ant-table-container {
      .ant-table-content {
        table thead tr th {
          &:last-child {
            text-align: center;
          }
          &.ant-table-cell:nth-of-type(1) {
            width: 10%;
          }
          &.ant-table-cell:nth-of-type(2) {
            width: 15%;
          }
          &.ant-table-cell:nth-of-type(3) {
            width: 80%;
          }
          &.ant-table-cell:nth-of-type(4) {
            width: 5%;
          }
        }
        table tbody.ant-table-tbody tr td {
          .ant-tag {
            height: auto;
          }
          .ant-tag.aTag-gui {
            text-align: left;
            &-prefered {
              color: #00a15b;
              background-color: #dfffe9;
            }
            &-blocked {
              color: #d80404;
              background-color: #fff2f2;
            }
          }

          .ant-tag.aTag-trp {
            color: #2e2b9e;
            background-color: #ededff;
          }
          &:last-child {
            text-align: center;
            svg {
              cursor: pointer;
            }
          }
        }
      }
    }
  }

  ::v-deep(.collapse-header) {
    display: flex;
    justify-content: space-between;
    align-items: center;
    // background-color: red !important;
  }

  ::v-deep(.update-date) {
    font-size: 12px;
    color: #bdbdbd;
    padding-left: 20px;
    margin-left: 20px;
    background-image: url('data:image/svg+xml;charset=UTF-8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="%23bdbdbd" d="M152 24c0-13.3-10.7-24-24-24s-24 10.7-24 24l0 40L64 64C28.7 64 0 92.7 0 128l0 16 0 48L0 448c0 35.3 28.7 64 64 64l320 0c35.3 0 64-28.7 64-64l0-256 0-48 0-16c0-35.3-28.7-64-64-64l-40 0 0-40c0-13.3-10.7-24-24-24s-24 10.7-24 24l0 40L152 64l0-40zM48 192l352 0 0 256c0 8.8-7.2 16-16 16L64 464c-8.8 0-16-7.2-16-16l0-256z"/></svg>');
    background-repeat: no-repeat;
    background-position-x: 0px;
    // background-repeat: no-repeat;
    // background-position: left;
  }
</style>
