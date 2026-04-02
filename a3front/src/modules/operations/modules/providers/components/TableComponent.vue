<template>
  <!-- TODO: Validar table para TRP / GUI -->
  <template v-if="providerStore.getType === 'TRP'">
    <a-table
      rowKey="id"
      expand-icon-as-cell="false"
      expand-icon-column-index="{-1}"
      table-layout="fixed"
      :loading="loading"
      :columns="filteredColumns"
      :data-source="data"
      :pagination="false"
      :row-selection="providerStore.getContract === 'F' ? rowSelection : null"
      :row-class-name="getRowClassName"
      :expandedRowKeys="expandedRowKeys"
      @expand="onExpand"
    >
      <template #title>
        <a-flex align="center" justify="space-between">
          <p>Lista de todos los servicios</p>
          <div>
            <template v-if="providerStore.getContract === 'F'">
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
                Sin confirmar
              </a-tag>
              <a-tag color="error" class="bg-transparent">
                <template #icon>
                  <font-awesome-icon :icon="['fas', 'circle']" />
                </template>
                Rechazado
              </a-tag>
              <a-tag color="yellow" class="bg-transparent">
                <template #icon>
                  <font-awesome-icon :icon="['fas', 'circle']" />
                </template>
                Sin reporte
              </a-tag>

              <!-- 
              <a-tag color="geekblue" class="bg-transparent">
                <template #icon>
                  <font-awesome-icon :icon="['fas', 'circle']" />
                </template>
                Completado
              </a-tag> 
              -->
            </template>

            <template v-else-if="providerStore.getContract === 'P'">
              <a-tag color="yellow" class="bg-transparent">
                <template #icon>
                  <font-awesome-icon :icon="['fas', 'circle']" />
                </template>
                Sin reporte
              </a-tag>
            </template>
          </div>
        </a-flex>
        <div class="table-actions" v-if="selectedRows.length > 0">
          <a-flex gap="middle" align="center" justify="space-between">
            <div>
              <a-button
                danger
                type="primary"
                @click="dataStore.confirmAssignments(selectedRows, true)"
              >
                Confirmar
              </a-button>
              &nbsp;
              <a-button
                danger
                type="primary"
                @click="dataStore.confirmAssignments(selectedRows, false)"
              >
                Cancelar
              </a-button>
              &nbsp;
              <span>
                Tienes <strong>{{ selectedRows.length }}</strong> ítem(s) seleccionado(s)
              </span>
            </div>
            <div>
              <!--
            <a-space>
              <a-button
                type="link"
                class="link-secondary"
                @click="
                  modalStore.showModal(
                    'trpAssignment',
                    'Asignar traslado',
                    { data: selectedRows },
                    824
                  )
                "
              >
                <i class="icon-trash-2"></i>
                <span class="mx-1">Asignar traslado</span>
              </a-button>
              <a-button
                type="link"
                class="link-secondary"
                @click="
                  modalStore.showModal('guiAssignment', 'Asignar guía', { data: selectedRows }, 824)
                "
              >
                <i class="icon-trash-2"></i>
                <span class="mx-1">Asignar guía</span>
              </a-button>
            </a-space>
            --></div>
          </a-flex>
        </div>
      </template>
      <template #bodyCell="{ column, record }">
        <template v-if="column.key === 'datetime_start'">
          <a-row justify="center" align="middle">
            <a-col :span="24">
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
        <template v-else-if="column.key === 'file'">
          <!-- <template v-if="record.files.length === 1">
            <span style="display: block">
              <a
                href="javascript:void(0)"
                style="color: #2f353a; font-weight: bold"
                @click="formStore.updateFileNumber(record.files[0].file.file_number)"
              >
                #{{ record.files[0].file.file_number }}
              </a>
              <span style="display: block">{{ record.files[0].file.description }}</span>
            </span>
          </template> -->
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
              @click="() => toggleExpand(record)"
            >
              {{ record.files.length }} FILES ANIDADOS
            </a>
          </template>
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
          <a-popover v-if="record.files[0].is_vip" title="Motivo VIP">
            <template #content>
              <a-row style="width: 250px">
                <a-col :span="24">
                  <p>Texto con el motivo que la especialista ha especificado</p>
                </a-col>
              </a-row>
            </template>
            <StarFilled :style="{ color: '#ffcc00', fill: '#ffcc00' }" />
          </a-popover>
          <StarOutlined v-else :style="{ fill: '#BABCBD ' }" />
        </template>

        <template v-else-if="column.key === 'lang'">
          <!-- {{ record.files[0].lang }} -->
          <span v-for="lang in record.lang" :key="lang" style="display: block">{{ lang }}</span>
        </template>
        <template v-else-if="column.key === 'pax'">
          <!-- {{ record.files[0].partial_paxs }} -->
          <template v-if="record.partial_paxs === record.total_paxs">
            {{ record.total_paxs }}
          </template>
          <template v-else> {{ record.partial_paxs }}/{{ record.total_paxs }} </template>
        </template>
        <template v-else-if="column.key === 'hotel'">
          <span v-for="route in record.routes" :key="route" style="display: block">
            <template v-if="route.pickup_point.type === 'HOTEL'">
              {{ route.pickup_point.provider.fullname }}
            </template>
          </span>
        </template>
        <template v-else-if="column.key === 'gui'">
          <template v-if="record.gui.length > 0">
            <div v-for="(v, i) in record.gui" style="display: block; margin: 5px 0">
              <UserOutlined /> {{ toTitleCase(v.provider.fullname) }}
            </div>
          </template>
        </template>
        <template v-else-if="column.key === 'trp'">
          <template v-if="record.assignment.driver">
            <a-tag color="default" style="font-size: 13px">
              <template #icon>
                <PhoneOutlined />
              </template>
              {{ record.assignment.driver.phone }}
            </a-tag>
            <span style="display: block; font-size: 13px">
              {{ record.assignment.driver.fullname }}
            </span>
          </template>

          <template v-if="record.assignment.vehicle">
            <span style="display: block; font-size: 13px">
              {{ record.assignment.vehicle.type }}
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
              {{ record.assignment.vehicle.plate }}
            </span>
          </template>
        </template>
        <template v-else-if="column.key === 'assignment'">
          <!-- {{ record.assignment.confirmation.status }} -->
          <!-- <a-tag :color="formatProvider(record.provider)" style="font-size: 14px; margin: 4px 0">
          <WarningOutlined v-if="formatProviderTxt(record.provider) === 'Sin confirmar'" />
          <CheckCircleOutlined v-if="formatProviderTxt(record.provider) === 'Confirmado'" />
          {{ formatProviderTxt(record.provider) }}
        </a-tag> -->

          <a-tag :color="formatProvider(record.assignment)" style="font-size: 14px; margin: 4px 0">
            <template v-if="record?.assignment?.confirmation?.status === 'Pending'">
              <font-awesome-icon :icon="['fas', 'clock']" />
              Sin confirmar
            </template>
            <template v-else-if="record?.assignment?.confirmation?.status === 'Confirmed'">
              <font-awesome-icon :icon="['fas', 'circle-check']" />
              Confirmado
            </template>
            <template v-else-if="record?.assignment?.confirmation?.status === 'NoReport'">
              <font-awesome-icon :icon="['fas', 'circle-exclamation']" />
              Sin reporte
            </template>
            <template v-else>
              <font-awesome-icon :icon="['fas', 'circle-xmark']" />
              Rechazado
            </template>
          </a-tag>
        </template>
        <template v-else-if="column.key === 'actions'">
          <a-space class="actions" style="gap: 4px">
            <!-- Ver información general y del servicio -->
            <!-- 
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
            <i class="icon-info" style="margin: 0"></i>
          </a-button> 
          -->

            <!-- Servicios adicionales de programación -->
            <!-- 
          <a-button type="link" style="padding: 0" @click="">
            <font-awesome-icon :icon="['fas', 'user-group']" />
          </a-button> 
          -->

            <a-button type="link" style="padding: 0" @click="openDrawer(record)">
              <font-awesome-icon :icon="['far', 'clipboard']" />
            </a-button>

            <!-- Acciones... -->
            <a-dropdown
              overlayClassName="custom-dropdown-backend"
              :disabled="record?.assignment?.confirmation?.status === 'Canceled'"
            >
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
                  <a-menu-item @click=""> Hoja master </a-menu-item>
                  <a-menu-item
                    v-if="
                      providerStore.getType === 'TRP' &&
                      record?.assignment?.confirmation?.status === 'Confirmed'
                    "
                    @click="
                      modalStore.showModal('unitAssignment', 'Asignación de unidad', {
                        data: record,
                      })
                    "
                  >
                    Asignación de unidad
                  </a-menu-item>

                  <a-menu-item @click=""> Lista de pasajeros </a-menu-item>
                </a-menu>
              </template>
            </a-dropdown>
          </a-space>

          <!-- 
        <a-button shape="circle" :icon="h(InfoCircleOutlined)" />
        <InfoCircleOutlined />
        <PlusSquareOutlined /> -->

          <!-- <a-space class="actions">
          <svg
            width="20"
            height="20"
            viewBox="0 0 20 20"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
            <g clip-path="url(#clip0_12212_11185)">
              <path
                d="M9.99996 13.3334V10.0001M9.99996 6.66675H10.0083M18.3333 10.0001C18.3333 14.6025 14.6023 18.3334 9.99996 18.3334C5.39759 18.3334 1.66663 14.6025 1.66663 10.0001C1.66663 5.39771 5.39759 1.66675 9.99996 1.66675C14.6023 1.66675 18.3333 5.39771 18.3333 10.0001Z"
                stroke="#2F353A"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
            </g>
            <defs>
              <clipPath id="clip0_12212_11185">
                <rect width="20" height="20" fill="white" />
              </clipPath>
            </defs>
          </svg>

          <a-button type="link">
            <svg
              width="20"
              height="20"
              viewBox="0 0 20 20"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M10 6.66667V13.3333M6.66667 10H13.3333M4.16667 2.5H15.8333C16.7538 2.5 17.5 3.24619 17.5 4.16667V15.8333C17.5 16.7538 16.7538 17.5 15.8333 17.5H4.16667C3.24619 17.5 2.5 16.7538 2.5 15.8333V4.16667C2.5 3.24619 3.24619 2.5 4.16667 2.5Z"
                stroke="#2F353A"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
            </svg>
          </a-button>
          <svg
            width="28"
            height="28"
            viewBox="0 0 28 28"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
            <rect x="0.5" y="0.5" width="27" height="27" rx="1.5" fill="#E4E5E6" />
            <rect x="0.5" y="0.5" width="27" height="27" rx="1.5" stroke="#E4E5E6" />
            <path
              d="M19.7388 13.6464L19.3853 14L19.7388 14.3536L22.8588 17.4736C23.0796 17.6944 23.0824 18.0727 22.8566 18.307C22.6292 18.532 22.2649 18.5313 22.0384 18.3048L18.1493 14.4156C17.922 14.1884 17.922 13.8225 18.1493 13.5952L22.0384 9.70605C22.2657 9.47882 22.6316 9.47882 22.8588 9.70605C23.0861 9.93329 23.0861 10.2992 22.8588 10.5264L19.7388 13.6464ZM17.4707 20H5.55404C5.23435 20 4.9707 19.7364 4.9707 19.4167C4.9707 19.097 5.23435 18.8333 5.55404 18.8333H17.4707C17.7904 18.8333 18.054 19.097 18.054 19.4167C18.054 19.7364 17.7904 20 17.4707 20ZM14.2207 14.5833H5.55404C5.23435 14.5833 4.9707 14.3197 4.9707 14C4.9707 13.6803 5.23435 13.4167 5.55404 13.4167H14.2207C14.5404 13.4167 14.804 13.6803 14.804 14C14.804 14.3197 14.5404 14.5833 14.2207 14.5833ZM5.55404 9.16667C5.23435 9.16667 4.9707 8.90302 4.9707 8.58333C4.9707 8.26364 5.23435 8 5.55404 8H17.4707C17.7904 8 18.054 8.26364 18.054 8.58333C18.054 8.90302 17.7904 9.16667 17.4707 9.16667H5.55404Z"
              fill="#2F353A"
              stroke="#2F353A"
            />
          </svg>
        </a-space> -->

          <!-- <a-space class="actions">
          <a-popconfirm
            title="¿Estás seguro de eliminar esta unidad?"
            ok-text="Sí"
            cancel-text="No"
          >
            <a-button type="link">
              <i class="icon-trash-2"></i>
            </a-button>
          </a-popconfirm>
          <a-button type="link">
            <i class="icon-edit"></i>
          </a-button>
          <a-dropdown overlayClassName="custom-dropdown-backend">
            <a-button type="link">
              <i class="icon-indent-right"></i>
            </a-button>
            <template #overlay>
              <a-menu>
                <a-menu-item v-if="!record.status">
                  <i class="icon-plus-square"></i>
                  Agregar configuración
                </a-menu-item>
                <a-menu-item>
                  <template v-if="!record.status">
                    <font-awesome-icon :icon="['far', 'circle-check']" />
                  </template>
                  <template v-else>
                    <i class="icon-x-circle"></i>
                  </template>
                  {{ record.status ? 'Desactivar unidad' : 'Activar unidad' }}
                </a-menu-item>
              </a-menu>
            </template>
          </a-dropdown>
        </a-space> -->
        </template>
        <template v-else>
          {{ record[column.key] }}
        </template>
      </template>

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
  </template>

  <template v-if="providerStore.getType === 'GUI'">
    <a-table
      rowKey="id"
      expand-icon-as-cell="false"
      expand-icon-column-index="{-1}"
      table-layout="fixed"
      :columns="columns"
      :data-source="data"
      :row-class-name="getRowClassName"
      :row-selection="providerStore.getContract === 'F' ? rowSelection : null"
      :loading="loading"
      :pagination="false"
    >
      <template #title>
        <a-flex align="center" justify="space-between">
          <p>Lista de todos los servicios</p>
          <div>
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
              Sin confirmar
            </a-tag>
            <a-tag color="error" class="bg-transparent">
              <template #icon>
                <font-awesome-icon :icon="['fas', 'circle']" />
              </template>
              Rechazado
            </a-tag>
            <!-- 
          <a-tag color="yellow" class="bg-transparent">
            <template #icon>
              <font-awesome-icon :icon="['fas', 'circle']" />
            </template>
            Sin reporte
          </a-tag> 
          -->
            <!-- 
          <a-tag color="geekblue" class="bg-transparent">
            <template #icon>
              <font-awesome-icon :icon="['fas', 'circle']" />
            </template>
            Completado
          </a-tag> 
          --></div>
        </a-flex>
        <div class="table-actions" v-if="selectedRows.length > 0">
          <a-flex gap="middle" align="center" justify="space-between" v-if="isConfirmed">
            <div>
              <a-button
                danger
                type="primary"
                @click="dataStore.confirmAssignments(selectedRows, true)"
              >
                Confirmar
              </a-button>
              &nbsp;
              <a-button
                danger
                type="primary"
                @click="dataStore.confirmAssignments(selectedRows, false)"
              >
                Cancelar
              </a-button>
              &nbsp;
              <span>
                Tienes <strong>{{ selectedRows.length }}</strong> ítem(s) seleccionado(s)
              </span>
            </div>
            <div>
              <!--
            <a-space>
              <a-button
                type="link"
                class="link-secondary"
                @click="
                  modalStore.showModal(
                    'trpAssignment',
                    'Asignar traslado',
                    { data: selectedRows },
                    824
                  )
                "
              >
                <i class="icon-trash-2"></i>
                <span class="mx-1">Asignar traslado</span>
              </a-button>
              <a-button
                type="link"
                class="link-secondary"
                @click="
                  modalStore.showModal('guiAssignment', 'Asignar guía', { data: selectedRows }, 824)
                "
              >
                <i class="icon-trash-2"></i>
                <span class="mx-1">Asignar guía</span>
              </a-button>
            </a-space>
            --></div>
          </a-flex>
        </div>
      </template>
      <template #bodyCell="{ column, record }">
        <template v-if="column.key === 'datetime_start'">
          <a-row justify="center" align="middle">
            <a-col :span="24">
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
        <template v-else-if="column.key === 'file'">
          <template v-if="record.files.length === 1">
            <span style="display: block">
              <a
                href="javascript:void(0)"
                style="color: #2f353a; font-weight: bold"
                @click="formStore.updateFileNumber(record.files[0].file.file_number)"
              >
                #{{ record.files[0].file.file_number }}
              </a>
              <span style="display: block">{{ record.files[0].file.description }}</span>
            </span>
          </template>
        </template>
        <template v-else-if="column.key === 'type'">
          {{ record.category === 'PRI' ? 'Privado' : 'Compartido' }}
        </template>
        <template v-else-if="column.key === 'client'">
          {{ record.client.name }}
        </template>
        <template v-else-if="column.key === 'vip'">
          <a-popover v-if="record.files[0].is_vip" title="Motivo VIP">
            <template #content>
              <a-row style="width: 250px">
                <a-col :span="24">
                  <p>Texto con el motivo que la especialista ha especificado</p>
                </a-col>
              </a-row>
            </template>
            <StarFilled :style="{ color: '#ffcc00', fill: '#ffcc00' }" />
          </a-popover>
          <StarOutlined v-else :style="{ fill: '#BABCBD ' }" />
        </template>

        <template v-else-if="column.key === 'lang'">
          <!-- {{ record.files[0].lang }} -->
          <span v-for="lang in record.lang" :key="lang" style="display: block">{{ lang }}</span>
        </template>
        <template v-else-if="column.key === 'pax'">
          <!-- {{ record.files[0].partial_paxs }} -->
          <template v-if="record.partial_paxs === record.total_paxs">
            {{ record.total_paxs }}
          </template>
          <template v-else> {{ record.partial_paxs }}/{{ record.total_paxs }} </template>
        </template>
        <template v-else-if="column.key === 'hotel'">
          <span v-for="route in record.routes" :key="route" style="display: block">
            <template v-if="route.pickup_point.type === 'HOTEL'">
              {{ route.pickup_point.provider.fullname }}
            </template>
          </span>
        </template>
        <template v-else-if="column.key === 'gui'">
          <template v-if="record.gui.length > 0">
            <div v-for="(v, i) in record.gui" style="display: block; margin: 5px 0">
              <UserOutlined /> {{ toTitleCase(v.provider.fullname) }}
            </div>
          </template>
        </template>
        <template v-else-if="column.key === 'trp'">
          <!-- Caso: status Confirmed y existe record.trp con longitud > 0 -->
          <template
            v-if="
              record?.assignment?.confirmation.status === 'Confirmed' &&
              Array.isArray(record.trp) &&
              record.trp.length > 0
            "
          >
            <div v-for="(v, i) in record.trp" :key="i" style="display: block; margin: 5px 0">
              <template v-if="v.driver">
                <a-tag color="default" style="font-size: 13px">
                  <template #icon><PhoneOutlined /></template>
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
            </div>
          </template>
          <!-- Caso contrario -->
          <template v-else>-</template>
        </template>

        <template v-else-if="column.key === 'assignment'">
          <!-- {{ record.assignment.confirmation.status }} -->
          <!-- <a-tag :color="formatProvider(record.provider)" style="font-size: 14px; margin: 4px 0">
          <WarningOutlined v-if="formatProviderTxt(record.provider) === 'Sin confirmar'" />
          <CheckCircleOutlined v-if="formatProviderTxt(record.provider) === 'Confirmado'" />
          {{ formatProviderTxt(record.provider) }}
        </a-tag> -->

          <a-tag :color="formatProvider(record.assignment)" style="font-size: 14px; margin: 4px 0">
            <!-- <template #icon v-if="v.sent_by === 'BOT'">
            <font-awesome-icon :icon="['fas', 'robot']" />
          </template> -->

            <template v-if="record?.assignment?.confirmation.status === 'Pending'">
              <font-awesome-icon :icon="['fas', 'clock']" />
              Sin confirmar
            </template>
            <template v-else-if="record?.assignment?.confirmation.status === 'Confirmed'">
              <font-awesome-icon :icon="['fas', 'circle-check']" />
              Confirmado
            </template>
            <template v-else-if="record?.assignment?.confirmation.status === 'NoReport'">
              <font-awesome-icon :icon="['fas', 'circle-exclamation']" />
              Sin reporte
            </template>
            <template v-else-if="record?.assignment?.confirmation.status === 'Cancelled'">
              <font-awesome-icon :icon="['fas', 'circle-xmark']" />
              Rechazado
            </template>
          </a-tag>
        </template>
        <template v-else-if="column.key === 'actions'">
          <a-space class="actions" style="gap: 4px">
            <!-- Ver información general y del servicio -->
            <!-- 
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
            <i class="icon-info" style="margin: 0"></i>
          </a-button> 
          -->

            <!-- Servicios adicionales de programación -->
            <!-- 
          <a-button type="link" style="padding: 0" @click="">
            <font-awesome-icon :icon="['fas', 'user-group']" />
          </a-button> 
          -->

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
                  <a-menu-item @click=""> Hoja master </a-menu-item>
                  <a-menu-item
                    v-if="
                      record?.assignment?.confirmation.status === 'Confirmed' &&
                      providerStore.getType !== 'GUI'
                    "
                    @click="
                      modalStore.showModal('unitAssignment', 'Asignación de unidad', {
                        data: record,
                      })
                    "
                  >
                    Asignación de unidad
                  </a-menu-item>
                  <a-menu-item @click=""> Lista de pasajeros </a-menu-item>
                </a-menu>
              </template>
            </a-dropdown>
          </a-space>
        </template>
        <template v-else>
          {{ record[column.key] }}
        </template>
      </template>
    </a-table>
  </template>

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
    <!-- 
    <template #content v-if="modalStore.currentProcess === 'guiAssignment'">
      <GuiAssignmentComponent :data="modalStore.modalData.data" />
    </template>

    <template #content v-else-if="modalStore.currentProcess === 'trpAssignment'">
      <TrpAssignmentComponent :data="modalStore.modalData.data" />
    </template>

    <template #content v-else-if="modalStore.currentProcess === 'informationService'">
      <InformationServiceComponent :data="modalStore.modalData.data" />
    </template>
    -->
    <template #content v-if="modalStore.currentProcess === 'unitAssignment'">
      <UnitAssignmentComponent :data="modalStore.modalData.data" />
    </template>
  </ReusableModal>

  <CreateReportComponent />
</template>

<script setup lang="ts">
  import { computed, defineAsyncComponent, onMounted, ref, toRaw, watch } from 'vue';
  import { storeToRefs } from 'pinia';
  // import { notification } from 'ant-design-vue';
  import { StarOutlined, StarFilled, PhoneOutlined, UserOutlined } from '@ant-design/icons-vue';
  import dayjs from 'dayjs';

  import { toTitleCase } from '@operations/shared/utils/stringUtils';

  import CustomPagination from '@/components/global/CustomPaginationComponent.vue';
  import ReusableModal from '@operations/shared/components/ReusableModalComponent.vue';

  import { useTableStore } from '@operations/modules/providers/store/table.store';
  import { useColumnStore } from '@operations/shared/stores/column.store';
  import { useDataStore } from '@operations/modules/providers/store/data.store';

  import { useFormStore } from '@operations/modules/providers/store/form.store';
  import { useReportStore } from '@operations/modules/providers/store/report.store';
  import { useModalStore } from '@operations/shared/stores/modal.store';
  import { useDrawerStore } from '@/composables/useDrawerStore';
  import { useFileStore } from '../store/file.store';

  import CreateReportComponent from '@/modules/operations/modules/providers/components/CreateReportComponent.vue';

  // import ADrawer from '@operations/shared/components/ADrawerComponent.vue';
  // import { useADrawerStore } from '@operations/shared/stores/drawer.store';
  // import { useAdditionalServiceStore } from '@operations/modules/service-management/store/additional-service.store';
  // import { additionalServices } from '@operations/modules/service-management/api/serviceManagementApi';

  // import GuiAssignmentComponent from '@operations/modules/service-management/components/GuiAssignmentComponent.vue';
  // import TrpAssignmentComponent from '@operations/modules/service-management/components/TrpAssignmentComponent.vue';
  // import InformationServiceComponent from '@operations/modules/service-management/components/InformationServiceComponent.vue';

  import UnitAssignmentComponent from '@operations/modules/providers/components/UnitAssignmentComponent.vue';

  import { useProviderStore } from '../store/providerStore';
  import FlightPopover from '@/modules/operations/shared/components/common/FlightPopoverComponent.vue';
  import { useBooleans } from '@/composables/useBooleans';

  // const aDrawerStore = useADrawerStore();

  const dataStore = useDataStore();
  const reportStore = useReportStore();
  const fileStore = useFileStore();
  const drawerStore = useDrawerStore();

  // const addServiceStore = useAdditionalServiceStore();
  // const aDrawerStore = useADrawerStore();
  const formStore = useFormStore();
  const providerStore = useProviderStore();

  const { useBoolean, setValue } = useBooleans();

  const isConfirmed = useBoolean('isConfirmed');

  // const { additionals } = storeToRefs(dataStore);

  // const item = computed(() => addServiceStore.item);

  // const totalPaxs = ref(0); // Total de pasajeros
  // const totalPaxs = computed(() => item.value?.total_paxs || 0);

  // const items = ref([
  //   {
  //     original: true,
  //     editable: false,
  //     partial_paxs: totalPaxs.value,
  //   },
  //   {
  //     editable: true,
  //     trp: false,
  //     gui: false,
  //     partial_paxs: 0, // Los nodos adicionales empiezan en 0
  //   },
  // ]);

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

  // const addAdditional = () => {
  //   items.value.push({ editable: true, trp: false, gui: false, partial_paxs: 0 });
  // };

  // const removeAdditional = (index: number) => {
  //   if (index === 0) return; // Evitar eliminar el nodo principal
  //   items.value.splice(index, 1);
  //   distributePaxs(); // Recalcular distribución
  // };

  // const updateAdditional = (index: number) => {
  //   const node = items.value[index];

  //   if (!node.trp && !node.gui) {
  //     notification.warning({
  //       message: 'Selección requerida',
  //       description: 'Debes seleccionar al menos un proveedor antes de guardar.',
  //       placement: 'topRight',
  //     });
  //     return;
  //   }
  //   node.editable = !node.editable;
  //   if (node.editable) {
  //     node.partial_paxs = 0;
  //     distributePaxs(true);
  //   } else distributePaxs();
  // };

  // const isAllEditableFalse = computed(() => {
  //   return items.value.every((item) => item.editable === false);
  // });

  // Método específico del padre
  // const handleSave = async () => {
  //   try {
  //     console.log('Lógica de guardado personalizada desde el padre');
  //     // Aquí implementas tu lógica específica
  //     // await someApiCallOrLogic(); // Ejemplo de llamada a una API o lógica de negocio
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
  const columnType = providerStore.getType === 'TRP' ? 'providerTRP' : 'providerGUI';
  const columns = columnStore.getColumnsByType(columnType);

  const filteredColumns = columns.filter((col) => {
    if (col.dataIndex === 'trp') {
      return providerStore.getType === 'TRP';
    }
    if (col.dataIndex === 'gui') {
      return providerStore.getType === 'GUI';
    }
    // Para el resto de columnas, siempre se muestran
    return col.dataIndex !== 'trp' && col.dataIndex !== 'gui';
  });

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

  // const isEmptyObject = (obj: any) => {
  //   return Object.keys(obj).length === 0 && obj.constructor === Object;
  // };

  // const formatProviderTxt = (providerAssignmentInfo: any): string | undefined => {
  //   const { provider, confirmation } = providerAssignmentInfo;
  //   const contract = provider.contract;
  //   if (contract === 'P') return 'Confirmado';
  //   else {
  //     if (confirmation.is_confirmed === 0) return 'Cancelado';
  //     else if (confirmation.is_confirmed === 1) return 'Confirmado';
  //     else return 'Sin confirmar';
  //   }
  // };

  const formatProvider = (assignment: any): string | undefined => {
    const confirmation = assignment?.confirmation;

    if (!confirmation || !confirmation.status) return 'error';

    switch (confirmation.status) {
      case 'Pending':
        return 'orange';
      case 'Confirmed':
        return 'success';
      case 'NoReport':
        return 'yellow';
      case 'Canceled':
        return 'error';
    }
  };

  // const colorPer = (percent: number) => {
  //   if (percent < 50) return '#D80404';
  //   else if (percent >= 50 && percent <= 99) return '#F99500';
  //   else return '#07DF81';
  // };

  //! Selección de filas
  const expandedRows = ref(new Set<number>());
  const expandedRowKeys = computed(() => Array.from(expandedRows.value));
  const selectedRowKeys = ref<number[]>([]);
  const selectedRows = ref<[]>([]);
  // const activeKey = ref(0);
  // const showModalMultipleRemove = ref(false);

  const rowSelection = {
    onChange: (keys: number[], rows: []) => {
      setValue('isConfirmed', true);
      selectedRowKeys.value = keys;
      selectedRows.value = rows;
      console.log(`Selected row keys: ${keys}`, 'Selected rows: ', rows);
    },

    getCheckboxProps: (record: any) => ({
      disabled: record?.assignment?.confirmation?.status !== 'Pending',
    }),
  };

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

  /******/
  const ExpandedComponent = defineAsyncComponent(
    () => import('@operations/modules/providers/components/Expanded.vue')
  );
  // const expandedRows = ref(new Set<number>());
  // const expandedRowKeys = computed(() => Array.from(expandedRows.value));

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
  /******/

  watch(lastUpdated, (newVal, oldVal) => {
    if (newVal !== oldVal) {
      formStore.fetchServicesWithParams();
    }
  });

  onMounted(() => {
    console.log('Servicios programados: listado principal.');
    formStore.fetchServicesWithParams();
  });

  const openDrawer = (record: any) => {
    const rawFiles = toRaw(record.files);

    const mappedFiles = rawFiles.map((item: any) => {
      const f = item.file;
      return {
        _id: f._id,
        file_number: f.file_number,
      };
    });

    fileStore.setFiles(mappedFiles);

    reportStore.setReportData({
      operational_service_id: record.assignment.operational_service_id,
    });
    drawerStore.openDrawer();

    // historyIncidents.setIncidents(record.history_incidents);
  };
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
</style>
