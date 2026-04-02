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
    :row-selection="rowSelection"
    :row-class-name="getRowClassName"
    :expandedRowKeys="expandedRowKeys"
    @expand="onExpand"
  >
    <template #title>
      <a-flex align="center" justify="space-between">
        <p>Lista de todos los servicios</p>
        <div>
          <a-tag color="default" class="bg-transparent">
            <template #icon>
              <font-awesome-icon :icon="['fas', 'robot']" />
            </template>
            Asignación automática
          </a-tag>
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
            <a-button
              danger
              type="primary"
              @click="validateNest(selectedRows)"
              :disabled="selectedRows.length <= 1"
            >
              Anidar
            </a-button>

            &nbsp;

            <a-button danger type="primary" @click="dataStore.handleOrderServices(selectedRows)">
              Enviar OS
            </a-button>

            &nbsp;

            <span>
              Tienes <strong>{{ selectedRows.length }}</strong> ítem(s) seleccionado(s)
            </span>
          </div>
          <div>
            <a-space>
              <a-button
                type="link"
                class="link-secondary"
                @click="validateTrpMultiple(selectedRows)"
              >
                <span class="mx-1 text-underline">Asignar traslado</span>
              </a-button>
              <a-button
                type="link"
                class="link-secondary"
                @click="
                  modalStore.showModal('guiAssignment', 'Asignar guía', { data: selectedRows }, 824)
                "
              >
                <span class="mx-1 text-underline">Asignar guía</span>
              </a-button>
            </a-space>
          </div>
        </a-flex>
      </div>
    </template>
    <template #bodyCell="{ column, record }">
      <template v-if="column.key === 'datetime_start'">
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
            <a-tag
              color="default"
              style="font-weight: bold; font-size: 15px"
              @click="() => console.log(record)"
            >
              {{ record.service.city_in.code }}
            </a-tag>
            <span style="display: block; font-weight: bold">
              {{ formatDate(record.datetime_start) }}
            </span>
            <span style="display: block">
              {{ formatTime(record.datetime_start) }}
            </span>
            <FlightPopover :data="record" />
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
        <a-popover v-if="record.files[0].file.vip" title="Motivo VIP">
          <template #content>
            <a-row style="width: 250px">
              <a-col :span="24">
                <p>{{ record.files[0].file.vip }}</p>
              </a-col>
            </a-row>
          </template>
          <StarFilled :style="{ color: '#ffcc00', fill: '#ffcc00' }" />
        </a-popover>
        <StarOutlined v-else :style="{ fill: '#BABCBD ' }" />
      </template>
      <template v-else-if="column.key === 'file'">
        <template v-if="record.files.length === 1">
          <span style="display: block">
            <a
              href="javascript:void(0)"
              style="color: #1284ed; font-weight: bold; text-decoration: underline"
              @click="formStore.updateFileNumber(record.files[0].file.file_number)"
            >
              #{{ record.files[0].file.file_number }}
            </a>
            -
            {{ record.files[0].file.description }}
          </span>
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
            style="color: #1284ed; font-weight: bold; text-decoration: underline"
            @click="
              toggleExpand(record);
              setValue('isExpanded', true);
            "
          >
            {{ record.files.length }} FILES ANIDADOS
          </a>
        </template>
      </template>
      <template v-else-if="column.key === 'lang'">
        <span v-for="lang in record.lang" :key="lang" style="display: block">{{ lang }}</span>
      </template>
      <template v-else-if="column.key === 'pax'">
        <!-- {{ record.files[0].partial_paxs }} -->
        <template v-if="record.partial_paxs === record.total_paxs">
          {{ padWithZero(record.total_paxs) }}
        </template>
        <template v-else> {{ record.partial_paxs }}/{{ record.total_paxs }} </template>
      </template>
      <template v-else-if="column.key === 'hotel'">
        <div v-if="record.files.length === 1">
          <MatchedHotelsComponent :matched-hotels="record.matched_hotels" />
        </div>
        <div v-else>
          <div style="font-size: 12px; margin-bottom: 12px">
            {{ padWithZero(uniqueHotelCount(record.matched_hotels)) }}
            hotel{{ uniqueHotelCount(record.matched_hotels) > 1 ? 'es' : '' }}
          </div>
        </div>
      </template>

      <template v-else-if="column.key === 'trp'">
        <template v-if="record.trp.length > 0">
          <div
            v-for="(v, i) in record.trp"
            style="
              display: flex;
              flex-direction: column;
              gap: 4px;
              margin: 4px 0;
              align-items: center;
            "
          >
            <div style="display: flex; align-items: center; justify-content: center">
              <a-tag
                :color="formatProvider(v)"
                style="font-size: 14px; margin-right: 8px"
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
                  style="margin-left: 4px; position: relative; top: 1px"
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

            <div style="display: flex; justify-content: center">
              <template v-if="record.required_vehicle_type">
                <a-tag
                  :color="formatProvider(v)"
                  style="font-size: 12px; font-weight: 400"
                  @mouseenter="v.showUpdateVehicleType = true"
                  @mouseleave="v.showUpdateVehicleType = false"
                >
                  Tipo:
                  {{ v.vehicle_type }}
                  <a
                    href="javascript:void(0)"
                    title="Actualizar tipo de vehículo"
                    style="margin-left: 4px; position: relative; top: 1px"
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
          </div>
        </template>
        <template v-else>
          <div v-if="record.requires_carrier">
            <a-button
              type="link"
              @click="validateTrpSingle(record)"
              style="display: flex; justify-content: center; align-items: center; width: 100%"
              ><span style="text-decoration: underline; color: #1284ed">Asignar</span>
            </a-button>
            <a-tag
              v-if="record.required_vehicle_type"
              style="font-size: 12px; font-weight: 400; margin: 4px 0"
            >
              Tipo: {{ record.required_vehicle_type }}
            </a-tag>
          </div>
          <div v-else>
            <span class="text-disabled">Asignar</span>
          </div>
        </template>
      </template>
      <template v-else-if="column.key === 'gui'">
        <div v-if="record.requires_guide">
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
        </div>
        <div v-else>
          <span class="text-disabled">Asignar</span>
        </div>
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
          <template v-if="!record.related_service_id">
            <a-popover
              :trigger="record.total_paxs <= 1 ? 'hover' : ''"
              :overlayStyle="{ maxWidth: '250px' }"
            >
              <template #content>
                <p v-if="record.total_paxs <= 1">
                  No se puede crear adicionales a un servicio con pasajero: {{ record.total_paxs }}
                </p>
              </template>

              <a-button
                type="link"
                style="
                  padding: 0;
                  width: 28px;
                  height: 28px;
                  display: flex;
                  align-items: center;
                  justify-content: center;
                "
                @click="openDrawer('Añadir proveedores al servicio', record)"
                :disabled="
                  record.related_service_id || record.files.length > 1 || record.total_paxs <= 1
                "
              >
                <PlusSquareOutlined
                  :style="{
                    color:
                      record.related_service_id || record.files.length > 1 || record.total_paxs <= 1
                        ? '#E7E7E7'
                        : undefined,
                  }"
                />
              </a-button>
            </a-popover>
          </template>

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
                <a-menu-item
                  @click="
                    modalStore.showModal('guiAssignment', 'Asignar guía', { data: record }, 824)
                  "
                >
                  Asignar guía
                </a-menu-item>
                <a-menu-item @click="validateTrpSingle(record)">Asignar traslado</a-menu-item>
                <a-menu-divider />
                <a-menu-item
                  @click="modalStore.showModal('createReturn', 'Crear retorno', { data: record })"
                >
                  Crear retorno
                </a-menu-item>
                <a-menu-divider />
                <a-menu-item>Ver pautas</a-menu-item>
              </a-menu>
            </template>
          </a-dropdown>
        </a-space>
      </template>
      <template v-else>
        {{ record[column.key] }}
      </template>
    </template>

    <!-- Detalle de cada unidad -->
    <template #expandedRowRender="{ record }">
      <Suspense>
        <template #default>
          <ExpandedComponent
            :record="record"
            :files="record.files"
            @closeExpand="toggleExpand(record)"
          />
        </template>
        <template #fallback>
          <div>Cargando contenido...</div>
        </template>
      </Suspense>
      <!-- Pestañas generadas dinámicamente -->
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

    <template #content v-else-if="modalStore.currentProcess === 'createReturn'">
      <CreateReturnComponent :data="modalStore.modalData.data" />
    </template>

    <template #content v-else-if="modalStore.currentProcess === 'informationService'">
      <TabsComponent :data="modalStore.modalData.data" />
    </template>

    <template #content v-else-if="modalStore.currentProcess === 'removeAssignment'">
      <RemoveAssignmentComponent :data="modalStore.modalData.data" />
    </template>

    <template #content v-else-if="modalStore.currentProcess === 'updateVehicleType'">
      <UpdateVehicleTypeComponent :data="modalStore.modalData.data" />
    </template>
  </ReusableModal>

  <ADrawer :onSave="handleSave">
    <a-row :gutter="[8, 16]">
      <a-col :span="24">
        <a-flex justify="center">
          <a-typography-title :level="5" :style="{ color: '#1284ED' }">
            <a-badge
              count="1"
              :number-style="{
                backgroundColor: '#1284ED',
              }"
            />
            Datos del servicio
          </a-typography-title>
        </a-flex>
      </a-col>
      <a-col :span="24">
        <a-alert
          message="Al añadir proveedores, se generará una nueva línea de servicio. Deberás asignar los proveedores correspondientes"
          type="warning"
          show-icon
        />
      </a-col>
      <template v-for="(node, index) in items" :key="index">
        <a-col :span="24" v-if="index === 0">
          <a-typography-text strong>Servicio seleccionado:</a-typography-text>

          <table style="width: 100%">
            <thead style="background-color: #2f353a; color: #ffffff">
              <tr>
                <th style="padding: 10px; text-align: left; width: 50%">Servicio</th>
                <th style="padding: 10px; width: 50%">Cantidad PAX</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td style="padding: 10px">{{ item.service_long_description }}</td>
                <td style="padding: 5px; text-align: center">
                  <template v-if="!node.partial_paxs">
                    {{ item.partial_paxs }}
                  </template>
                  <template v-else>{{ node.partial_paxs }}/{{ totalPaxs }}</template>
                </td>
              </tr>
            </tbody>
          </table>
        </a-col>

        <a-col v-else :span="24" class="container" :class="{ inactive: !node.editable }">
          <a-flex justify="space-between" align="center">
            <div class="header-item">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" style="width: 12px">
                <path
                  d="M32 448c-17.7 0-32 14.3-32 32s14.3 32 32 32l96 0c53 0 96-43 96-96l0-306.7 73.4 73.4c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3l-128-128c-12.5-12.5-32.8-12.5-45.3 0l-128 128c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L160 109.3 160 416c0 17.7-14.3 32-32 32l-96 0z"
                />
              </svg>
              <a-typography-text strong>Añadir servicio</a-typography-text>
            </div>
            <a-space v-if="node.editable">
              <CheckCircleOutlined
                style="color: #07df81; font-size: 20px; cursor: pointer"
                @click="updateAdditional(index)"
              />
              <CloseCircleOutlined
                v-if="items.length > 1 && index > 1"
                style="color: #bd0d12; font-size: 20px; cursor: pointer"
                @click="removeAdditional(index)"
              />
            </a-space>
            <a-space v-else>
              <FormOutlined
                style="color: #bd0d12; font-size: 20px; cursor: pointer"
                @click="updateAdditional(index)"
              />
              <DeleteOutlined
                style="color: #bd0d12; font-size: 20px; cursor: pointer"
                @click="removeAdditional(index)"
              />
            </a-space>
          </a-flex>
          <a-typography-text v-if="node.editable">
            Añadir proveedor(es) al nuevo servicio:
          </a-typography-text>
          <a-typography-text v-else>
            Cantidad de pasajeros: <strong>{{ node.partial_paxs }}/{{ totalPaxs }}</strong>
          </a-typography-text>
          <a-space>
            <a-checkbox
              v-model:checked="node.trp"
              class="custom-checkbox"
              :disabled="!node.editable || !addServiceStore.is_required_carrier"
            >
              Transporte
            </a-checkbox>

            <a-checkbox
              v-model:checked="node.gui"
              class="custom-checkbox"
              :disabled="!node.editable || !addServiceStore.is_required_guide"
            >
              Guía
            </a-checkbox>
          </a-space>
        </a-col>
      </template>

      <a-col :span="24" v-if="isAllEditableFalse && !isPaxCountEqual">
        <a-flex justify="flex-end" align="center">
          <a-typography-text style="cursor: pointer; color: #bd0d12" strong @click="addAdditional">
            <font-awesome-icon :icon="['fas', 'plus']" /> Añadir otro servicio
          </a-typography-text>
        </a-flex>
      </a-col>
    </a-row>
  </ADrawer>
</template>

<script setup lang="ts">
  import { computed, defineAsyncComponent, onMounted, ref, watch } from 'vue';
  import { storeToRefs } from 'pinia';
  import { notification } from 'ant-design-vue';
  import CustomPagination from '@/components/global/CustomPaginationComponent.vue';
  import FlightPopover from '@operations/shared/components/common/FlightPopoverComponent.vue';
  import dayjs from 'dayjs';

  import {
    StarOutlined,
    StarFilled,
    CheckCircleOutlined,
    CloseCircleOutlined,
    DeleteOutlined,
    FormOutlined,
    SwapOutlined,
    ExclamationCircleOutlined,
    PlusSquareOutlined,
  } from '@ant-design/icons-vue';

  import { useTableStore } from '@operations/modules/service-management/store/table.store';
  import { useColumnStore } from '@operations/shared/stores/column.store';
  import { useDataStore } from '@operations/modules/service-management/store/data.store';

  import { useFormStore } from '@operations/modules/service-management/store/form.store';

  import ReusableModal from '@operations/shared/components/ReusableModalComponent.vue';
  import { useModalStore } from '@operations/shared/stores/modal.store';

  import ADrawer from '@operations/shared/components/ADrawerComponent.vue';
  import { useADrawerStore } from '@operations/shared/stores/drawer.store';
  import { useAdditionalServiceStore } from '@operations/modules/service-management/store/additional-service.store';
  import { createAdditionals } from '@operations/modules/service-management/api/serviceManagementApi';

  import GuiAssignmentComponent from '@operations/modules/service-management/components/GuiAssignmentComponent.vue';
  import TrpAssignmentComponent from '@operations/modules/service-management/components/TrpAssignmentComponent.vue';
  import CreateReturnComponent from '@operations/modules/service-management/components/CreateReturnComponent.vue';

  import RemoveAssignmentComponent from '@operations/modules/service-management/components/RemoveAssignmentComponent.vue';
  import UpdateVehicleTypeComponent from '@operations/modules/service-management/components/UpdateVehicleTypeComponent.vue';
  import TabsComponent from './InfoService/TabsComponent.vue';
  import MatchedHotelsComponent from '@/modules/operations/shared/components/MatchedHotelsComponent.vue';
  import { padWithZero } from '@/modules/operations/shared/utils/padWithZero';
  import { useBooleans } from '@/composables/useBooleans';

  // import { useFilterStore } from '@operations/modules/service-management/store/filter.store';

  /*******/

  type SelectedRow = {
    service: {
      _id: string;
      city_in: { _id: string };
    };
    category: string;
    datetime_start: string;
    [key: string]: any;
  };

  const ExpandedComponent = defineAsyncComponent(
    () => import('@operations/shared/components/common/Expanded.vue')
  );
  const expandedRows = ref(new Set<number>());
  const expandedRowKeys = computed(() => Array.from(expandedRows.value));

  const { useBoolean, setValue } = useBooleans();

  const isDeselect = useBoolean('isDeselect');

  // Método que puede ejecutarse cuando la tabla emite un evento de expansión
  const onExpand = async (expanded: boolean, record: any) => {
    console.log('🚀 ~ onExpand ~ expanded:', expanded);
    console.log('🚀 ~ onExpand ~ record:', record);
    if (expanded) {
      await toggleExpand(record);
    } else {
      expandedRows.value.delete(record.id);
    }
  };

  // Función para alternar la expansión de filas
  const toggleExpand = async (record: any) => {
    // console.log('🚀 ~ toggleExpand ~ record:', record);
    if (expandedRows.value.has(record.id)) {
      expandedRows.value.delete(record.id);
    } else {
      expandedRows.value.clear();
      expandedRows.value.add(record.id);
      // activeKey.value = 0;
      // if (record.locations.length > 0) {
      // await handleTabChangeUnit(0, record);
      // }
    }
  };
  /*******/

  // const aDrawerStore = useADrawerStore();

  const dataStore = useDataStore();
  const addServiceStore = useAdditionalServiceStore();
  const aDrawerStore = useADrawerStore();
  const formStore = useFormStore();
  // const filterStore = useFilterStore();

  // const { lastUpdated } = storeToRefs(filterStore);

  const item = computed(() => addServiceStore.item);

  // const totalPaxs = ref(0); // Total de pasajeros
  const totalPaxs = computed(() => item.value?.total_paxs || 0);

  const isPaxCountEqual = computed(() => item.value.total_paxs === items.value.length);

  const items = ref<any[]>([]);

  const distributePaxs = (restart = false) => {
    const totalNodes = items.value.length;
    const basePaxPerNode = Math.floor(totalPaxs.value / totalNodes);
    let remaining = totalPaxs.value;

    if (restart) {
      // Reiniciar el cálculo solo para un nodo
      items.value[0].partial_paxs = totalPaxs.value;
      for (let i = 1; i < totalNodes; i++) {
        items.value[i].partial_paxs = 0;
      }
      return;
    }

    // Distribuir el número base en todos los nodos
    items.value.forEach((node) => {
      node.partial_paxs = basePaxPerNode;
      remaining -= basePaxPerNode;
    });

    // Distribuir el resto comenzando desde el primer nodo
    for (let i = 0; remaining > 0; i = (i + 1) % totalNodes) {
      items.value[i].partial_paxs += 1;
      remaining -= 1;
    }
  };

  const addAdditional = () => {
    if (!isPaxCountEqual.value) {
      items.value.push({
        editable: true,
        trp: false,
        gui: false,
        partial_paxs: 0,
      });
    }
  };

  const validationNotEmptyTrpGuide = (): boolean => {
    for (const item of items.value) {
      if (!('_id' in item)) {
        const hasTrp = item.trp === true;
        const hasGui = item.gui === true;

        if (!hasTrp && !hasGui) {
          notification.warning({
            message: 'Validación requerida',
            description:
              'Debes seleccionar al menos un proveedor antes de guardar la configuración.',
            placement: 'topRight',
          });
          return false;
        }
      }
    }
    return true;
  };

  const removeAdditional = (index: number) => {
    if (index === 0) return; // Evitar eliminar el nodo principal
    items.value.splice(index, 1);
    distributePaxs(); // Recalcular distribución
  };

  const updateAdditional = (index: number) => {
    if (!validationNotEmptyTrpGuide()) return;

    const node = items.value[index];

    node.editable = !node.editable;
    if (node.editable) {
      node.partial_paxs = 0;
      distributePaxs(true);
    } else distributePaxs();
  };

  const isAllEditableFalse = computed(() => {
    return items.value.every((item) => item.editable === false);
  });

  // Método específico del padre
  const handleSave = async () => {
    try {
      if (!validateProcess(items.value)) {
        notification.warning({
          message: 'Validación requerida',
          description: 'Es necesario confirmar servicio',
          placement: 'topRight',
        });
        return;
      }
      aDrawerStore.loading = true;
      await handleCreateAdditionals();
    } catch (error) {
      console.error('Error al guardar:', error);
      throw error; // Propaga el error para que el hijo lo maneje si es necesario
    } finally {
      // aDrawerStore.handleDrawerClose();
    }
  };

  const validateProcess = (array: any[]): boolean => {
    for (const item of array) {
      if (item.editable === true) {
        return false;
      }

      if (!('_id' in item)) {
        if (!item.trp && !item.gui) {
          return false;
        }
      }
    }
    return true;
  };

  const handleCreateAdditionals = async () => {
    try {
      const data = {
        original_id: item.value.id,
        items: items.value,
        headquarter: item.value.service.city_in.code,
        service_type: item.value.service.type,
        client_code: item.value.client.code,
        date_in: item.value.datetime_start,
      };

      // Llamada a la API
      await createAdditionals(data);
      aDrawerStore.handleDrawerClose();
      formStore.fetchServicesWithParams();
    } catch (error) {
      console.error('Error en la llamada a la API:', error);
      throw error; // Propaga el error para manejo en otro nivel
    }
  };

  const openDrawer = async (title: string, service: any) => {
    aDrawerStore.showDrawer(title, { width: 600 }); // Mostrar primero
    items.value = []; // Limpiar datos anteriores
    const clonedAdditionals = await addServiceStore.setItem(service); // Obtener nuevos
    items.value = clonedAdditionals;

    console.log(items.value);

    // ✅ Solo ejecutar si todos tienen _id
    const allHaveId = items.value.every((item) => '_id' in item);

    if (allHaveId && items.value.length > 1) {
      items.value.forEach((item) => {
        if (item.editable) item.editable = false;
      });
      distributePaxs(); // Una sola vez fuera del loop
    }
  };

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

  defineProps({
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
  const columns = columnStore.getColumnsByType();

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

  // const formatTime = (dateStart: string, dateEnd: string): string => {
  //   const formattedDateStart = dayjs(dateStart).format('HH:mm');
  //   const formattedDateEnd = dayjs(dateEnd).format('HH:mm');
  //   if (formattedDateStart === formattedDateEnd) return formattedDateStart;
  //   else return `${formattedDateStart} - ${formattedDateEnd}`;
  // };

  // const isEmptyObject = (obj: any) => {
  //   return Object.keys(obj).length === 0 && obj.constructor === Object;
  // };

  const formatProvider = (providerAssignmentInfo: any): string | undefined => {
    const { provider, service_orders, confirmation } = providerAssignmentInfo;
    const contract = provider.contract;

    // No existe información de orden de servicio
    if (service_orders.length === 0) {
      return 'geekblue';
    }

    // Si hay órdenes de servicio, el color base es warning
    if (service_orders.length > 0) {
      // Es un tipo de proveedor PLANTA (no requiere confirmación)
      if (contract === 'P') {
        return 'success';
      }

      // Para otros tipos de proveedores, verificar confirmación
      if (!confirmation) {
        return 'warning';
      }

      // Verificar estado de confirmación
      switch (confirmation.status) {
        case 'Canceled':
          return 'error';
        case 'Confirmed':
          return 'success';
        default:
          return 'warning';
      }
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
  const selectedRowKeys = ref<number[]>([]);
  const selectedRows = ref<any[]>([]);

  // const activeKey = ref(0);
  // const showModalMultipleRemove = ref(false);

  const rowSelection = computed(() => ({
    selectedRowKeys: selectedRowKeys.value,
    onChange: (keys: number[], rows: any[]) => {
      selectedRowKeys.value = keys;
      selectedRows.value = rows;
    },
  }));

  // Obtener zonas válidas
  const getValidZone = async (cityCodes: string[]) => {
    await dataStore.getZones();

    return dataStore.zones.find((zone) =>
      cityCodes.every((code) => zone.headquarter === code || zone.zones.includes(code))
    );
  };

  // Mostrar notificación
  const showNotification = (message: string, description: string) => {
    notification.warning({
      message,
      description,
      duration: 4,
    });
  };

  // Función para abrir modal de asignación TRP
  const openTrpModal = (data: any[]) => {
    modalStore.showModal('trpAssignment', 'Asignar traslado', { data }, 824);
  };

  // Validación y asignación TRP para un solo registro
  const validateTrpSingle = async (record: any): Promise<void> => {
    // Verificar si el servicio requiere transportista

    const requires_carrier = record.requires_carrier === true;

    if (!requires_carrier) {
      showNotification(
        'Notificación',
        'El servicio seleccionado no requiere un vehículo. No se puede asignar un TRP.'
      );
      return;
    }

    const cityCode = record.service?.city_in?.code || '';
    const validZone = await getValidZone([cityCode]);

    if (!validZone) {
      showNotification(
        'Zona no válida',
        'No se encontró una zona válida para el servicio seleccionado.'
      );
      return;
    }

    // Preparar datos para el modal
    const parseData = [
      {
        ...record,
        headquarter: validZone.headquarter,
      },
    ];

    openTrpModal(parseData);
  };

  // Validación y asignación TRP para múltiples registros
  const validateTrpMultiple = async (selectedRows: any[]): Promise<void> => {
    // Filtrar servicios que requieren transportista
    const servicesRequiringCarrier = selectedRows.filter((row) => row.requires_carrier === true);

    if (servicesRequiringCarrier.length === 0) {
      showNotification(
        'No hay coincidencias',
        'Todos los servicios seleccionados no requieren un vehículo. No se puede asignar un TRP.'
      );
      return;
    }

    // Extraer códigos de ciudad únicos
    const cityCodes = [
      ...new Set(servicesRequiringCarrier.map((row) => row.service?.city_in?.code || '')),
    ].filter(Boolean);

    // Validar que todos los servicios están en la misma zona
    const validZone = await getValidZone(cityCodes);

    if (!validZone) {
      showNotification(
        'Zonas incompatibles',
        'Los servicios seleccionados no están todos en la misma sede o zona.'
      );
      return;
    }

    // Mapear servicios con sede válida
    const parseData = servicesRequiringCarrier.map((service) => ({
      ...service,
      headquarter: validZone.headquarter,
    }));

    openTrpModal(parseData);
  };

  const uniqueHotelCount = (hotels: any[] = []) => {
    const uniqueNames = new Set(hotels.map((h) => h.name));
    return uniqueNames.size;
  };

  // Método para reiniciar la selección
  // const resetSelection = () => {
  //   selectedRowKeys.value = [];
  //   selectedRows.value = [];
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
    // Si la fila está expandida, aplicar la clase 'expanded-row'
    if (expandedRowKeys.value.includes(record.id)) {
      return 'expanded-row'; // Aplicar clase específica solo para la fila expandida
    }

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

  // watch(lastUpdated, (newVal, oldVal) => {
  //   if (newVal !== oldVal) {
  //     formStore.fetchServicesWithParams();
  //   }
  // });

  // Estado reactivo para manejar elementos con showDelete
  // const showDeleteState = reactive<Record<number, boolean>>({});

  // Función para verificar si el cursor está sobre el `a-tag` o el `a-popconfirm`
  // const checkMouseLeave = (key: any) => {
  //   setTimeout(() => {
  //     const tagElement = document.querySelector(`[data-tag-key="${key}"]`);
  //     const popconfirmElement = document.querySelector(`[data-popconfirm-key="${key}"]`);

  //     const isOverTag = tagElement?.matches(':hover');
  //     const isOverPopconfirm = popconfirmElement?.matches(':hover');

  //     if (!isOverTag && !isOverPopconfirm) {
  //       showDeleteState[key] = false; // Oculta el botón 'X' si no está sobre ningún elemento
  //     }
  //   }, 100); // Ajusta el tiempo para capturar bien los movimientos
  // };

  const getMatchingRows = (selectedRows: SelectedRow[]) => {
    const data_trp = {
      headquarter: (selectedRows[0].service.city_in as any).code,
      client_code: selectedRows[0].client.code,
      date_in: selectedRows[0].datetime_start,
      service_type: (selectedRows[0].service as any).type,
    };

    const grouped = new Map<string, SelectedRow[]>();

    selectedRows.forEach((row) => {
      const key = `${row.service?._id}|${row.service?.city_in?._id}|${row.category}|${row.datetime_start}`;
      if (!grouped.has(key)) {
        grouped.set(key, []);
      }
      grouped.get(key)?.push(row);
    });

    const allGroups = Array.from(grouped.values());
    const allValid = allGroups.every((group) => group.length >= 2);

    const matchedRows = allGroups.flat();

    if (matchedRows.every((row) => row.requires_carrier === false)) {
      notification.warning({
        message: 'Notificación',
        description: 'No hay ningún servicio que requiera un vehículo. No se puede asignar un TRP.',
        duration: 4,
      });
      return;
    }

    if (!allValid) {
      notification.warning({
        message: 'No hay coincidencias',
        description:
          'Debe seleccionar al menos 2 filas con el mismo servicio, ciudad, tipo y hora. Todas las filas seleccionadas deben coincidir.',
        duration: 4,
      });
      return;
    }

    // Si todas las filas están en grupos válidos (con mínimo 2 elementos)
    dataStore.handleNestServices(matchedRows, data_trp);
  };

  const validateNest = (selectedRows: any) => getMatchingRows(selectedRows);

  watch(lastUpdated, (newVal, oldVal) => {
    if (newVal !== oldVal) {
      formStore.fetchServicesWithParams();
    }
  });

  onMounted(() => {
    console.log('Programación de servicios: listado principal.');
    formStore.fetchServicesWithParams();
  });

  const clearSelection = () => {
    selectedRowKeys.value = [];
    selectedRows.value = [];
  };

  // Watcher que ejecuta deselección cuando isDeselect es true
  watch(isDeselect, (newValue) => {
    if (newValue) {
      clearSelection();
      setTimeout(() => {
        setValue('isDeselect', false);
      }, 500);
    }
  });
</script>

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

  .text-underline {
    text-decoration: underline;
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

  .text-disabled {
    color: #b0b0b0;
    user-select: none;
  }
</style>
