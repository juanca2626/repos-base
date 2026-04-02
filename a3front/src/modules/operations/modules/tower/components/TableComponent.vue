<template>
  <a-table
    rowKey="id"
    expand-icon-as-cell="false"
    expand-icon-column-index="{-1}"
    table-layout="fixed"
    :loading="loading"
    :columns="columns"
    :data-source="data"
    :pagination="false"
    :row-class-name="getRowClassName"
    :expandedRowKeys="expandedRowKeys"
    @expand="onExpand"
  >
    <template #title>
      <a-flex align="center" justify="space-between">
        <p></p>
        <div>
          <a-tag color="geekblue" class="bg-transparent">
            <template #icon>
              <font-awesome-icon :icon="['fas', 'circle']" />
            </template>
            Sin orden de servicio
          </a-tag>
          <!--  style="color: #00a15b" -->
          <a-tag color="success" class="bg-transparent">
            <template #icon>
              <font-awesome-icon :icon="['fas', 'circle']" />
            </template>
            Confirmado
          </a-tag>
          <a-tag color="warning" class="bg-transparent">
            <template #icon>
              <font-awesome-icon :icon="['fas', 'circle']" />
            </template>
            Pendiente de confirmación
          </a-tag>
          <a-tag color="error" class="bg-transparent">
            <template #icon>
              <font-awesome-icon :icon="['fas', 'circle']" />
            </template>
            Rechazado
          </a-tag>
        </div>
      </a-flex>
      <div class="table-actions" v-if="selectedRows.length > 0">
        <a-flex gap="middle" align="center" justify="space-between">
          <div>
            <a-button danger type="primary">Anidar</a-button>
            <span>
              Tienes <strong>{{ selectedRows.length }}</strong> ítem(s) seleccionado(s)
            </span>
          </div>
          <div>
            <a-space>
              <a-button type="link" class="link-secondary" @click="">
                <i class="icon-trash-2"></i>
                <span class="mx-1">Asignar traslado</span>
              </a-button>
              <a-button type="link" class="link-secondary" @click="">
                <i class="icon-trash-2"></i>
                <span class="mx-1">Asignar guía</span>
              </a-button>
            </a-space>
          </div>
        </a-flex>
      </div>
    </template>

    <template #bodyCell="{ column, record }">
      <template v-if="column.key === 'monitoring'">
        <MonitoringTag
          :initialServiceState="getServiceState(record)"
          @toggle-expand="toggleExpand(record, true)"
        />
      </template>
      <template v-if="column.key === 'city_datetime_start'">
        <a-row justify="center" align="middle">
          <a-col :span="!record.related_service_id ? 0 : 4">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 384 512"
              style="fill: #7e8285; width: 12px"
            >
              <path
                d="M32 448c-17.7 0-32 14.3-32 32s14.3 32 32 32l96 0c53 0 96-43 96-96l0-306.7 73.4 73.4c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3l-128-128c-12.5-12.5-32.8-12.5-45.3 0l-128 128c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L160 109.3 160 416c0 17.7-14.3 32-32 32l-96 0z"
              />
            </svg>
          </a-col>
          <a-col :span="!record.related_service_id ? 24 : 20">
            <a-tag color="default" style="font-weight: bold; font-size: 15px">
              {{ record.service.city_in.code }}
            </a-tag>
            <span style="display: block; font-weight: bold">
              {{ formatDate(record.datetime_start) }}
            </span>
            <span style="display: block">
              {{ formatTime(record.datetime_start) }}
            </span>
            <!-- <FlightPopover :record="record" /> -->
          </a-col>
        </a-row>
      </template>
      <template v-else-if="column.key === 'service'">
        {{ record.service.long_description || record.service.short_description }}
      </template>
      <template v-else-if="column.key === 'type'">
        {{ record.category === 'PRI' ? 'Privado' : 'Compartido' }}
      </template>
      <template v-else-if="column.key === 'client'">
        <template v-if="record.files.length === 1">
          {{ record.client.name }}
        </template>
        <template v-else>-</template>
      </template>
      <template v-else-if="column.key === 'vip'">
        <a-popover v-if="record.files[0].is_vip && record.files[0].vip" title="Motivo VIP">
          <template #content>
            <a-row style="width: 250px">
              <a-col :span="24">
                <p>{{ record.files[0].vip }}</p>
              </a-col>
            </a-row>
          </template>
          <StarFilled :style="{ color: '#ffcc00', fill: '#ffcc00' }" />
        </a-popover>
        <StarOutlined v-else :style="{ fill: '#BABCBD ' }" />
      </template>
      <template v-else-if="column.key === 'file'">
        <template v-if="record.files.length === 1">
          <!-- <span style="display: block"> -->
          <a
            href="javascript:void(0)"
            style="color: #1284ed; font-weight: bold; text-decoration: underline; display: block"
            @click="formStore.updateFileNumber(record.files[0].file.file_number)"
          >
            #{{ record.files[0].file.file_number }}
          </a>
          {{ record.files[0].file.description }}
          <!-- </span> -->
          <!-- <a-progress
            :percent="record.files[0].progress_percentage"
            size="small"
            strokeLinecap="square"
            :strokeColor="colorPer(record.files[0].progress_percentage)"
          /> -->
        </template>
        <template v-else>
          <a
            href="javascript:void(0)"
            style="color: #1284ed; font-weight: bold; text-decoration: underline; display: block"
            @click="() => toggleExpand(record)"
          >
            {{ record.files.length }} FILES <DownOutlined />
          </a>
          ANIDADOS
        </template>
      </template>
      <template v-else-if="column.key === 'pax'">
        <template v-if="record.partial_paxs === record.total_paxs">
          {{ record.total_paxs.toString().padStart(2, '0') }}
        </template>
        <template v-else>
          {{ record.partial_paxs.toString().padStart(2, '0') }}/{{
            record.total_paxs.toString().padStart(2, '0')
          }}
        </template>
      </template>

      <template v-else-if="column.key === 'lang'">
        <span v-for="lang in record.lang" :key="lang" style="display: block">{{ lang }}</span>
      </template>
      <template v-else-if="column.key === 'vuelo_in'">
        <div v-if="record.matched_flights.length">
          <div
            v-for="(flight, index) in record.matched_flights[0].flights"
            :key="index"
            style="margin-bottom: 8px; text-align: left"
          >
            <a-popover placement="bottom">
              <template #content>
                Actualizado hace <span class="flight-strong">3h 11m</span>
              </template>
              <a-tag>{{ record.matched_flights[0].city_in_name }}</a-tag>
            </a-popover>
            <div class="text_air_number">{{ flight.airline_number }}</div>
            <div>{{ dayjs(flight.departure_time, 'HH:mm:ss').format('HH:mm') }}</div>
          </div>
        </div>
      </template>

      <template v-else-if="column.key === 'vuelo_out'">
        <div v-if="record.matched_flights.length">
          <div
            v-for="flight in record.matched_flights[0].flights"
            :key="flight.id"
            style="margin-bottom: 8px; text-align: left"
          >
            <a-popover placement="bottom">
              <template #content>
                Actualizado hace <span class="flight-strong">3h 11m</span>
              </template>
              <a-tag>{{ record.matched_flights[0].city_out_name }}</a-tag>
            </a-popover>
            <div class="text_air_number">{{ flight.airline_number }}</div>
            <div>{{ dayjs(flight.arrival_time, 'HH:mm:ss').format('HH:mm') }}</div>
          </div>
        </div>
      </template>
      <template v-else-if="column.key === 'hotel'">
        <div v-if="record?.matched_hotels?.length" style="text-align: left">
          <div v-for="hotel in record.matched_hotels" :key="hotel.id" style="margin-bottom: 12px">
            <div style="display: flex; align-items: center; gap: 8px; font-size: 12px">
              <font-awesome-icon :icon="['fas', 'arrow-right-to-bracket']" class="in_hotel_icon" />
              <div style="font-size: 12px">{{ hotel.name }}</div>
            </div>
            <div>{{ hotel.city_in_name }}</div>
          </div>
        </div>
      </template>
      <template v-else-if="column.key === 'trp'">
        <template v-if="record.trp.length > 0">
          <div v-for="(v, i) in record.trp">
            <a-tag
              :color="formatProvider(v)"
              style="font-size: 14px; margin: 4px 0"
              @mouseenter="v.showDelete = true"
              @mouseleave="v.showDelete = false"
            >
              <template #icon v-if="v.sent_by === 'BOT'">
                <font-awesome-icon :icon="['fas', 'robot']" />
              </template>

              <a-tooltip>
                <template #title>{{ v.provider.fullname }}</template>
                {{ v.provider.code }}
              </a-tooltip>

              <a
                href="javascript:void(0)"
                title="Eliminar asignación"
                style="margin: 0 -2px 0 4px; position: relative; top: 1px"
                v-if="v.showDelete"
                @click="
                  modalStore.showModal('removeAssignment', 'Eliminar asignación de proveedor', {
                    data: v,
                  })
                "
              >
                <CloseCircleOutlined :style="{ color: `${formatProviderTest(v)}` }" />
              </a>
            </a-tag>

            <template v-if="!record.required_vehicle_type">
              <a-tooltip placement="bottom">
                <template #title>No disponible</template>
                <a-tag
                  style="font-size: 12px; font-weight: 400; margin: 4px 0"
                  :color="formatProvider(v)"
                >
                  Tipo: ND
                </a-tag>
              </a-tooltip>
            </template>
            <template v-else>
              <a-tag
                :color="formatProvider(v)"
                style="font-size: 12px; font-weight: 400; margin: 4px 0"
                @mouseenter="v.showUpdateVehicleType = true"
                @mouseleave="v.showUpdateVehicleType = false"
              >
                Tipo:
                {{ v.vehicle_type }}
                <a
                  href="javascript:void(0)"
                  title="Eliminar asignación"
                  style="margin: 0 -2px 0 4px; position: relative; top: 1px"
                  v-if="v.showUpdateVehicleType"
                  @click="
                    modalStore.showModal('updateVehicleType', 'Actualizar tipo de vehículo', {
                      data: { operationalService: record, assignment: v },
                    })
                  "
                >
                  <SwapOutlined :style="{ color: `${formatProviderTest(v)}` }" />
                </a>
              </a-tag>
            </template>
          </div>
        </template>
        <template v-else>
          <a-button
            type="link"
            @click="
              modalStore.showModal('trpAssignment', 'Asignar traslado', { data: record }, 824)
            "
            style="display: flex; justify-content: center; align-items: center; width: 100%"
            ><span style="text-decoration: underline; color: #1284ed">Asignar</span>
          </a-button>
          <a-tag style="font-size: 12px; font-weight: 400; margin: 4px 0">
            Tipo: {{ record.required_vehicle_type }}
          </a-tag>
        </template>
      </template>
      <template v-else-if="column.key === 'chofer_placa'">
        <template v-for="(v, i) in record.trp">
          <template v-if="v.driver">
            <a-tag color="default" style="font-size: 13px">
              <template #icon>
                <PhoneOutlined />
              </template>
              {{ v.driver.phone }}
            </a-tag>
            <span style="display: block; font-size: 13px">
              {{ v.driver.fullname }}
            </span>
          </template>

          <template v-if="v.vehicle">
            <span style="display: block; font-size: 13px">
              {{ v.vehicle.type }}
            </span>
            <span style="display: block; font-size: 13px; font-weight: bold">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 512 512"
                style="fill: #1284ed; width: 13px"
              >
                <path
                  d="M165.4 96l181.2 0c13.6 0 25.7 8.6 30.2 21.4L402.9 192l-293.8 0 26.1-74.6c4.5-12.8 16.6-21.4 30.2-21.4zm-90.6 .3L39.6 196.8C16.4 206.4 0 229.3 0 256l0 80c0 23.7 12.9 44.4 32 55.4L32 448c0 17.7 14.3 32 32 32l32 0c17.7 0 32-14.3 32-32l0-48 256 0 0 48c0 17.7 14.3 32 32 32l32 0c17.7 0 32-14.3 32-32l0-56.6c19.1-11.1 32-31.7 32-55.4l0-80c0-26.7-16.4-49.6-39.6-59.2L437.2 96.3C423.7 57.8 387.4 32 346.6 32L165.4 32c-40.8 0-77.1 25.8-90.6 64.3zM208 272l96 0c8.8 0 16 7.2 16 16l0 32c0 8.8-7.2 16-16 16l-96 0c-8.8 0-16-7.2-16-16l0-32c0-8.8 7.2-16 16-16zM48 280c0-13.3 10.7-24 24-24l32 0c13.3 0 24 10.7 24 24s-10.7 24-24 24l-32 0c-13.3 0-24-10.7-24-24zm360-24l32 0c13.3 0 24 10.7 24 24s-10.7 24-24 24l-32 0c-13.3 0-24-10.7-24-24s10.7-24 24-24z"
                />
              </svg>
              {{ v.vehicle.plate }}
            </span>
          </template>
        </template>
      </template>
      <template v-else-if="column.key === 'gui'">
        <template v-if="record.gui.length > 0">
          <div v-for="(v, i) in record.gui">
            <a-tag
              :color="formatProvider(v)"
              style="font-size: 14px; margin: 4px 0"
              @mouseenter="v.showDelete = true"
              @mouseleave="v.showDelete = false"
            >
              <template #icon v-if="v.sent_by === 'BOT'">
                <font-awesome-icon :icon="['fas', 'robot']" />
              </template>

              <a-tooltip>
                <template #title>{{ v.provider.fullname }}</template>
                {{ v.provider.code }}
              </a-tooltip>

              <a
                href="javascript:void(0)"
                title="Eliminar asignación"
                style="margin: 0 -2px 0 4px; position: relative; top: 1px"
                v-if="v.showDelete"
                @click="
                  modalStore.showModal('removeAssignment', 'Eliminar asignación de proveedor', {
                    data: v,
                  })
                "
              >
                <CloseCircleOutlined :style="{ color: `${formatProviderTest(v)}` }" />
              </a>
            </a-tag>
          </div>
        </template>
        <template v-else>
          <a-button
            type="link"
            @click="modalStore.showModal('guiAssignment', 'Asignar guía', { data: record }, 824)"
            style="display: flex; justify-content: center; align-items: center; width: 100%"
          >
            <span style="text-decoration: underline; color: #1284ed">Asignar</span>
          </a-button>
        </template>
      </template>
      <template v-else-if="column.key === 'masi'">
        <CheckCircleOutlined style="font-size: 20px" />
        <!-- <CheckCircleFilled /> -->
      </template>
      <template v-else-if="column.key === 'actions'">
        <a-space class="actions" style="gap: 4px">
          <!-- Ver información general y del servicio -->
          <a-button
            type="link"
            style="padding: 0"
            @click="
              modalStore.showModal(
                'informationService',
                'Ver información general y del servicio',
                { data: record },
                824
              )
            "
          >
            <ExclamationCircleOutlined />
          </a-button>

          <!-- Servicios adicionales de programación -->
          <!-- <a-button
            type="link"
            style="padding: 0"
            @click="openDrawer('Añadir proveedores al servicio', record)"
            :disabled="record.related_service_id"
          >
            <i
              class="icon-plus-square"
              style="margin: 0"
              :style="record.related_service_id ? { color: '#E7E7E7' } : {}"
            ></i>
          </a-button> -->

          <!-- Acciones... -->
          <a-dropdown overlayClassName="custom-dropdown-backend">
            <a-button type="link" style="padding: 0">
              <svg
                width="28"
                height="28"
                viewBox="0 0 28 28"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
                style="width: 23px"
              >
                <rect x="0.5" y="0.5" width="27" height="27" rx="1.5" fill="#E4E5E6" />
                <rect x="0.5" y="0.5" width="27" height="27" rx="1.5" stroke="#E4E5E6" />
                <path
                  d="M19.7388 13.6464L19.3853 14L19.7388 14.3536L22.8588 17.4736C23.0796 17.6944 23.0824 18.0727 22.8566 18.307C22.6292 18.532 22.2649 18.5313 22.0384 18.3048L18.1493 14.4156C17.922 14.1884 17.922 13.8225 18.1493 13.5952L22.0384 9.70605C22.2657 9.47882 22.6316 9.47882 22.8588 9.70605C23.0861 9.93329 23.0861 10.2992 22.8588 10.5264L19.7388 13.6464ZM17.4707 20H5.55404C5.23435 20 4.9707 19.7364 4.9707 19.4167C4.9707 19.097 5.23435 18.8333 5.55404 18.8333H17.4707C17.7904 18.8333 18.054 19.097 18.054 19.4167C18.054 19.7364 17.7904 20 17.4707 20ZM14.2207 14.5833H5.55404C5.23435 14.5833 4.9707 14.3197 4.9707 14C4.9707 13.6803 5.23435 13.4167 5.55404 13.4167H14.2207C14.5404 13.4167 14.804 13.6803 14.804 14C14.804 14.3197 14.5404 14.5833 14.2207 14.5833ZM5.55404 9.16667C5.23435 9.16667 4.9707 8.90302 4.9707 8.58333C4.9707 8.26364 5.23435 8 5.55404 8H17.4707C17.7904 8 18.054 8.26364 18.054 8.58333C18.054 8.90302 17.7904 9.16667 17.4707 9.16667H5.55404Z"
                  fill="#2F353A"
                  stroke="#2F353A"
                />
              </svg>
            </a-button>
            <template #overlay>
              <a-menu>
                <a-menu-item>
                  <font-awesome-icon :icon="['fas', 'circle-plus']" class="icon_sub_action" />
                  Crear adicional
                </a-menu-item>
                <a-menu-item>
                  <font-awesome-icon :icon="['fas', 'user-plus']" class="icon_sub_action" />
                  Añadir TRP/GUI
                </a-menu-item>
                <a-menu-item>
                  <font-awesome-icon :icon="['fas', 'file-lines']" class="icon_sub_action" />
                  Servicios programados
                </a-menu-item>
                <a-menu-item>
                  <font-awesome-icon :icon="['fas', 'users']" class="icon_sub_action" />
                  Lista de pasajeros
                </a-menu-item>
                <a-menu-item>
                  <font-awesome-icon :icon="['fas', 'bed']" class="icon_sub_action" />
                  Rooming list
                </a-menu-item>
                <a-menu-item>
                  <font-awesome-icon :icon="['fas', 'ticket']" class="icon_sub_action" />
                  Tickets
                </a-menu-item>
                <a-menu-item>
                  <font-awesome-icon :icon="['fas', 'exclamation']" class="icon_sub_action" />
                  Incidencias
                </a-menu-item>
                <a-menu-item>
                  <font-awesome-icon :icon="['fas', 'clock-rotate-left']" class="icon_sub_action" />
                  Historial
                </a-menu-item>
                <a-menu-item>
                  <font-awesome-icon :icon="['fas', 'trash-can']" class="icon_sub_action" />
                  Eliminar servicio
                </a-menu-item>
              </a-menu>
            </template>
          </a-dropdown>
        </a-space>
      </template>
      <template v-else>
        <!-- {{ record[column.key] }} -->
      </template>
    </template>

    <template #expandedRowRender="{ record }">
      <Suspense>
        <template #default>
          <template v-if="expandedFromMonitoring[record.id]">
            <MonitoringExpanded
              :record="record"
              :serviceState="getServiceState(record)"
              @update-service-state="updateServiceState"
            />
          </template>
          <template v-else>
            <ExpandedComponent
              :record="record"
              :files="record.files"
              @closeExpand="toggleExpand(record)"
            />
          </template>
        </template>
        <template #fallback>
          <div>Cargando contenido...</div>
        </template>
      </Suspense>
    </template>
  </a-table>

  <CustomPagination
    v-model:current="pagination.current"
    v-model:pageSize="pagination.pageSize"
    :total="pagination.total"
    :disabled="data?.length === 0"
    @change="onChange"
  />

  <ReusableModal
    :isVisible="modalStore.isModalVisible"
    :title="modalStore.modalTitle"
    :loading="modalStore.loading"
    :width="modalStore.modalWidth"
  >
    <template #content v-if="modalStore.currentProcess === 'trpAssignment'">
      <TrpAssignmentComponent :data="modalStore.modalData.data" />
    </template>

    <template #content v-else-if="modalStore.currentProcess === 'guiAssignment'">
      <GuiAssignmentComponent :data="modalStore.modalData.data" />
    </template>

    <template #content v-else-if="modalStore.currentProcess === 'informationService'">
      <TabsComponent :data="modalStore.modalData.data" />
    </template>
  </ReusableModal>
</template>

<script setup lang="ts">
  import { computed, defineAsyncComponent, onMounted, ref, watch } from 'vue';
  import { storeToRefs } from 'pinia';
  import dayjs from 'dayjs';
  import {
    StarOutlined,
    StarFilled,
    DownOutlined,
    CheckCircleOutlined,
    ExclamationCircleOutlined,
    CloseCircleOutlined,
  } from '@ant-design/icons-vue';

  /** GLOBAL **/
  import CustomPagination from '@/components/global/CustomPaginationComponent.vue';
  import MonitoringTag from '@operations/shared/components/common/MonitoringTag.vue';

  /** STORES **/
  import { useTableStore } from '@operations/modules/tower/store/table.store';
  import { useColumnStore } from '@operations/shared/stores/column.store';

  import { useFormStore } from '@operations/modules/tower/store/form.store';
  import ReusableModal from '@operations/shared/components/ReusableModalComponent.vue';
  import { useModalStore } from '@operations/shared/stores/modal.store';
  import TrpAssignmentComponent from '../../service-management/components/TrpAssignmentComponent.vue';
  import GuiAssignmentComponent from '../../service-management/components/GuiAssignmentComponent.vue';
  import TabsComponent from '../../service-management/components/InfoService/TabsComponent.vue';

  // import { useADrawerStore } from '@operations/shared/stores/drawer.store';
  // import { useAdditionalServiceStore } from '@operations/modules/service-management/store/additional-service.store';
  // import { additionalServices } from '@operations/modules/service-management/api/serviceManagementApi';

  /*******/
  const ExpandedComponent = defineAsyncComponent(
    () => import('@operations/shared/components/common/Expanded.vue')
  );
  const MonitoringExpanded = defineAsyncComponent(
    () => import('@operations/modules/tower/components/MonitoringExpanded.vue')
  );
  const expandedRows = ref(new Set<number>());
  const expandedRowKeys = computed(() => Array.from(expandedRows.value));

  // Método que puede ejecutarse cuando la tabla emite un evento de expansión
  const onExpand = async (expanded: any, record: any) => {
    console.log('🚀 ~ onExpand ~ expanded:', expanded);
    console.log('🚀 ~ onExpand ~ record:', record);
    if (expanded) {
      await toggleExpand(record);
    } else {
      expandedRows.value.delete(record.id);
    }
  };

  // Función para alternar la expansión de filas
  const expandedFromMonitoring = ref<{ [key: number]: boolean }>({});

  const toggleExpand = (record: any, fromMonitoring = false) => {
    if (expandedRows.value.has(record.id)) {
      expandedRows.value.delete(record.id);
      expandedFromMonitoring.value[record.id] = false;
    } else {
      expandedRows.value.clear();
      expandedRows.value.add(record.id);
      expandedFromMonitoring.value[record.id] = fromMonitoring;
    }
  };

  /*******/

  // const aDrawerStore = useADrawerStore();

  // const addServiceStore = useAdditionalServiceStore();
  // const aDrawerStore = useADrawerStore();
  const formStore = useFormStore();

  // const { additionals } = storeToRefs(dataStore);
  // const item = computed(() => addServiceStore.item);

  // const totalPaxs = ref(0); // Total de pasajeros
  // const totalPaxs = computed(() => item.value?.total_paxs || 0);

  // const items = ref(additionals);

  // const distributePaxs = (restart = false) => {
  //   const totalNodes = items.value.length;
  //   const basePaxPerNode = Math.floor(totalPaxs.value / totalNodes);
  //   let remaining = totalPaxs.value;

  //   if (restart) {
  //     // Reiniciar el cálculo solo para un nodo
  //     items.value[0].partial_paxs = totalPaxs.value;
  //     for (let i = 1; i < totalNodes; i++) {
  //       items.value[i].partial_paxs = 0;
  //     }
  //     return;
  //   }

  //   // Distribuir el número base en todos los nodos
  //   items.value.forEach((node) => {
  //     node.partial_paxs = basePaxPerNode;
  //     remaining -= basePaxPerNode;
  //   });

  //   // Distribuir el resto comenzando desde el primer nodo
  //   for (let i = 0; remaining > 0; i = (i + 1) % totalNodes) {
  //     items.value[i].partial_paxs += 1;
  //     remaining -= 1;
  //   }
  // };

  // Método específico del padre
  // const handleSave = async () => {
  //   try {
  //     console.log('Lógica de guardado personalizada desde el padre');
  //     // Aquí implementas tu lógica específica
  //     await someApiCallOrLogic(); // Ejemplo de llamada a una API o lógica de negocio
  //     console.log('Guardado exitoso');
  //   } catch (error) {
  //     console.error('Error al guardar:', error);
  //     throw error; // Propaga el error para que el hijo lo maneje si es necesario
  //   }
  // };

  // const someApiCallOrLogic = async () => {
  //   try {
  //     // Crear el payload asegurándose de que 'items' sea un JSON válido
  //     const data = {
  //       original_id: item.value.id,
  //       items: items.value, // items ya es un objeto reactivo, lo enviamos directamente
  //     };

  //     // Llamada a la API
  //     const response = await additionalServices(data);
  //     console.log('Respuesta de la API:', response);
  //     formStore.fetchServicesWithParams();
  //   } catch (error) {
  //     console.error('Error en la llamada a la API:', error);
  //     throw error; // Propaga el error para manejo en otro nivel
  //   }
  // };

  // const openDrawer = (title: string, newItem: any) => {
  //   addServiceStore.setItem(newItem); // Configura el item específico
  //   aDrawerStore.showDrawer(title, { width: 600 }); // Configuración general del drawer
  // };

  // const activeKey = ref(['0']);
  // const checkedTRP = ref(false);
  // const checkedGUI = ref(false);

  // Accedemos al store de Pinia
  const tableStore = useTableStore();
  const { lastUpdated } = storeToRefs(tableStore);
  const columnStore = useColumnStore();
  const modalStore = useModalStore();
  // const addItemStore = useAdditionalItemStore();

  // const currentProcess = ref<string | null>(null);

  // const openModal = (process: string, data: any) => {
  //   // Define el título según el proceso
  //   const titles: Record<string, string> = {
  //     guideAssignment: 'Asignar guía',
  //     otroProceso: 'Otro proceso',
  //   };
  //   currentProcess.value = process;
  //   modalStore.showModal(titles[process], { data }, 824, process);
  // };

  // const onModalConfirm = async () => {
  //   // modalStore.handleModalOk(confirmDelete);
  //   if (currentProcess.value === 'guideAssignment') {
  //     await dataStore.handleGuideAssignment();
  //   } else {
  //     console.log('No se ha detectado un proceso actualizado');
  //   }
  // };

  // const { services } = storeToRefs(dataStore);

  const props = defineProps({
    data: {
      type: Array,
      required: true,
    },
    loading: {
      type: Boolean,
      required: true,
    },
    pagination: {
      type: Object,
      required: true,
    },
  });

  console.log(props.data);

  //TODO: Eliminar
  // const openDeleteModal = (item: any) => {
  //   modalStore.showModal('Eliminar ítem', { item });
  // };

  // const confirmDelete = async () => {
  // await someDeleteFunction(modalStore.modalData.item); // Implementación específica de la eliminación
  // Puedes añadir lógica adicional aquí si es necesario
  // };

  //TODO: Eliminar

  // Destructuramos las propiedades del store
  const { pagination } = storeToRefs(tableStore);
  const { onChange } = tableStore;

  // Obtenemos las columnas desde el store
  const columns = columnStore.getColumnsByType('tower');

  // Función para manejar los cambios en la tabla (paginación, filtros, orden)
  // const handleTableChange = (pagination: any) => {
  //   onChange(pagination.current, pagination.pageSize);
  // };

  // Ejecutar la función fetchData cuando el componente se monte
  onMounted(() => {
    // fetchData(pagination.value.current, pagination.value.pageSize); // Inicializar la tabla con la página 1
    // fetchDataGuides(); // Inicializar la tabla con la página 1
  });

  const formatDate = (date: string): string => {
    return dayjs(date).format('DD/MM');
  };

  const formatTime = (dateStart: string): string => {
    return dayjs(dateStart).format('HH:mm');
  };

  // const test = (v: any) => {
  //   console.log('click modal', v);
  //   modalStore.showModal('removeAssignment', 'Eliminar asignación de proveedor', { data: v });
  // };

  // const isEmptyObject = (obj: any) => {
  //   return Object.keys(obj).length === 0 && obj.constructor === Object;
  // };

  const formatProvider = (providerAssignmentInfo: any): string | undefined => {
    const { provider, service_orders, confirmation } = providerAssignmentInfo;
    const contract = provider.contract;

    //TODO: Implementar validación para tipo de proveedor (GUI / TRP)
    // No existe información de orden de servicio
    if (service_orders.length === 0) {
      return 'geekblue';
    }

    // Es un tipo de proveedor PLANTA (no requiere confirmación)
    if (contract === 'P') return 'success';
    else {
      if (!confirmation) return 'warning';
      // O es un tipo de proveedor FREELANCE (requiere confirmación)
      if (confirmation.status === 'Canceled') return 'error';
      else if (confirmation.status === 'Confirmed') return 'success';
      else return 'warning';
    }
  };

  const formatProviderTest = (providerAssignmentInfo: any): string | undefined => {
    const { provider, service_orders, confirmation } = providerAssignmentInfo;
    const contract = provider.contract;

    //TODO: Implementar validación para tipo de proveedor (GUI / TRP)
    // No existe información de orden de servicio
    if (service_orders.length === 0) {
      return '#1d39c4';
    }

    // Es un tipo de proveedor PLANTA (no requiere confirmación)
    if (contract === 'P') return '#52c41a';
    else {
      if (!confirmation) return '#faad14';
      // O es un tipo de proveedor FREELANCE (requiere confirmación)
      if (confirmation.status === 'Canceled') return '#ff4d4f';
      else if (confirmation.status === 'Confirmed') return '#52c41a';
      else return '#faad14';
    }
  };

  // const colorPer = (percent: number) => {
  //   if (percent < 50) return '#D80404';
  //   else if (percent >= 50 && percent <= 99) return '#F99500';
  //   else return '#07DF81';
  // };

  // const randomNumber = computed(() => {
  //   return Math.floor(Math.random() * 100) + 1;
  // });

  //! Selección de filas
  // const expandedRows = ref(new Set<number>());
  // const expandedRowKeys = computed(() => Array.from(expandedRows.value));
  // const selectedRowKeys = ref<number[]>([]);
  const selectedRows = ref<[]>([]);
  // const activeKey = ref(0);
  // const showModalMultipleRemove = ref(false);

  // const rowSelection = {
  //   onChange: (keys: number[], rows: []) => {
  //     selectedRowKeys.value = keys;
  //     selectedRows.value = rows;
  //     console.log(`Selected row keys: ${keys}`, 'Selected rows: ', rows);
  //   },
  // };

  // const rowSelectionGuides = {
  //   onChange: (keys: number[], rows: []) => {
  //     selectedRowKeys.value = keys;
  //     selectedRows.value = rows;
  //     console.log(`Selected row keys: ${keys}`, 'Selected rows: ', rows);
  //   },
  // };

  // const selectedRowKeys = ref([]);
  // const selectedRows = ref([]);
  // const onSelectRow = (id: any, event: any) => {
  //   if (event.target.checked) {
  //     selectedRowKeys.value.push(id);
  //   } else {
  //     selectedRowKeys.value = selectedRowKeys.value.filter((key) => key !== id);
  //   }
  //   console.log('Selected Row Keys:', selectedRowKeys.value);
  // };

  // Método para asignar la clase de la fila seleccionada o expandida
  const getRowClassName = (record: any) => {
    console.log('🚀 ~ getRowClassName ~ record:', record);
    // Si la fila está expandida, aplicar la clase 'expanded-row'
    // if (expandedRowKeys.value.includes(record.id)) {
    //   // Aplicar clase específica solo para la fila expandida
    //   return 'expanded-row';
    // }

    // Puedes aplicar la lógica de filas seleccionadas u otras condiciones aquí si es necesario.
    return '';
  };

  // const handleModalOk = async ({ process, data }: { process: string; data: any }) => {
  //   console.log('🚀 ~ handleModalOk ~ process:', currentProcess.value);
  //   console.log('🚀 ~ handleModalOk ~ data:', data);
  //   try {
  //     if (process === 'guideAssignment') {
  //       await dataStore.handleGuideAssignment(data);
  //     } else {
  //       console.warn(`No action found for process: ${process}`);
  //     }
  //     notification.success({
  //       message: 'Acción completada',
  //       description: 'Los cambios se guardaron correctamente.',
  //     });
  //   } catch (error) {
  //     console.error('Error en handleModalOk:', error);
  //     notification.error({ message: 'Error', description: 'No se pudo completar la acción.' });
  //   }
  // };

  // 🔹 Estado reactivo para cada fila del servicio
  const serviceStates = ref<{ [key: number]: string }>({});

  // Función para actualizar el estado del servicio desde MonitoringExpanded
  const updateServiceState = (recordId: number, newState: string) => {
    serviceStates.value[recordId] = newState;
  };

  // Función para obtener el estado inicial (si no existe, se usa "stateless")
  const getServiceState = (record: any) => {
    return serviceStates.value[record.id] || record.monitoring?.event || 'stateless';
  };

  watch(lastUpdated, (newVal, oldVal) => {
    if (newVal !== oldVal) {
      formStore.fetchServicesWithParams();
    }
  });

  onMounted(() => {
    console.log('Programación de servicios: listado principal.');
    formStore.fetchServicesWithParams();
  });
</script>

<!-- <style scoped lang="scss"> -->

<style scoped lang="scss">
  // ADDITIONAL
  .container {
    padding: 20px !important;
    background: #ebf5ff;
    border-radius: 4px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    &.inactive {
      background-color: #f9f9f9;
    }
  }

  .header-item {
    display: flex;
    align-items: center;
    gap: 3px;
    flex: 1;
  }

  .icon {
    width: 14px;
    height: 16px;
    display: flex;
    justify-content: center;
    align-items: center;
    padding-right: 1.17px;
  }

  .icon-bg {
    width: 12.83px;
    height: 16px;
    background: #2f353a;
  }

  .header-text {
    color: #2f353a;
    font-size: 14px;
    font-family: Montserrat, sans-serif;
    font-weight: 600;
    line-height: 20px;
  }

  .action-part-1 {
    width: 9.9px;
    height: 4.24px;
    position: absolute;
    top: 12.5px;
    left: 7px;
    transform: rotate(-45deg);
    border: 2px solid #babcbd;
  }

  .action-part-2 {
    width: 20px;
    height: 20px;
    position: absolute;
    top: 2px;
    left: 2px;
    border: 2px solid #babcbd;
  }

  .action-inner {
    width: 20px;
    height: 20px;
    border: 2px solid #babcbd;
  }

  .content {
    display: flex;
    flex-direction: column;
    gap: 8px;
  }

  .content-text {
    width: 446px;
    color: #2f353a;
    font-size: 14px;
    font-family: Montserrat, sans-serif;
    font-weight: 500;
    line-height: 20px;
  }

  .options {
    display: flex;
    gap: 12px;
  }

  .option {
    display: flex;
    align-items: center;
    gap: 10px;
    height: 24px;
    flex: 1;
  }

  .checkbox {
    width: 24px;
    height: 24px;
    background: white;
    border-radius: 2px;
    border: 1px solid #7e8285;
    display: flex;
    justify-content: center;
    align-items: center;
  }

  .checkbox-inner {
    width: 24px;
    height: 24px;
    border-radius: 2px;
    border: 1px solid #7e8285;
  }

  .option-text {
    color: #2f353a;
    font-size: 16px;
    font-family: Montserrat, sans-serif;
    font-weight: 400;
    line-height: 24px;
  }

  //TODO: CUSTOM CHECKBOX
  ::v-deep(.custom-checkbox .ant-checkbox-inner) {
    width: 24px;
    height: 24px;
  }

  .custom-checkbox .ant-checkbox-inner::after {
    width: 15px; /* Tamaño del check */
    height: 15px;
    transform: scale(1.2); /* Ajusta el tamaño del icono de check */
  }

  ::v-deep(.custom-checkbox .ant-checkbox) {
    line-height: 2; /* Ajusta el espaciado */
  }

  ::v-deep(.custom-checkbox) {
    font-size: 16px; /* Cambia el tamaño de la fuente del texto */
  }

  ::v-deep(.disabled-row) {
    background-color: #f5f5f5; /* Color gris claro */
    color: #b0b0b0; /* Texto gris */
    cursor: not-allowed;
    pointer-events: none; /* Evita interacciones */
  }

  .text_air_number {
    font-weight: 500;
  }

  .in_hotel_icon {
    font-size: 12px;
    color: #1284ed;
  }

  a-menu-item {
    display: flex;
    align-items: center;
  }

  .icon_sub_action {
    color: #bcbaba;
    width: 20px;
    text-align: center;
    margin-right: 8px;
    font-size: 16px;
  }

  .flight-strong {
    font-weight: 700;
  }
</style>
