<template>
  <!-- Pantalla inicial -->
  <div v-if="pantallaInicial" class="full-width-container">
    <div
      style="
        background-color: white;
        border-radius: 10px;
        border: 1px solid #f0f0f0;
        margin-top: 16px;
        width: 100%;
        padding: 24px;
        margin-left: auto;
        margin-right: auto;
      "
    >
      <!-- Encabezado -->
      <a-row justify="space-between" align="middle" style="margin-bottom: 16px">
        <a-col><h4 style="font-weight: bold; font-size: 22px">Listado de reclamos</h4></a-col>
        <a-col>
          <a-button
            type="default"
            style="color: #eb5757; border-color: #eb5757"
            @click="iniciarReclamo"
          >
            <plus-outlined />
            Crear reclamo
          </a-button>
        </a-col>
      </a-row>
      <!-- Cuerpo gris en lista de reclamos-->
      <div style="background-color: #fafafa; padding: 16px; border-radius: 8px">
        <!-- Filtros -->
        <a-row justify="space-between" align="middle" style="margin-bottom: 16px">
          <a-col :span="7">
            <a-input
              v-model:value="filtroPalabraClave"
              placeholder="Filtrar por palabras clave"
              allow-clear
              :prefix="h(SearchOutlined)"
            />
          </a-col>
          <a-col :span="4">
            <a-select
              v-model:value="filtroEstado"
              placeholder="Estados"
              allow-clear
              style="width: 100%"
            >
              <a-select-option value="pendiente">Pendiente</a-select-option>
              <a-select-option value="cerrado">Cerrado</a-select-option>
            </a-select>
          </a-col>
          <a-col :span="4">
            <a-select
              v-model:value="filtroTipoFecha"
              placeholder="Filtrar por"
              allow-clear
              style="width: 100%"
            >
              <a-select-option value="recepcion">Fecha Recepción</a-select-option>
              <a-select-option value="ingreso">Fecha Ingreso</a-select-option>
            </a-select>
          </a-col>
          <a-col :span="6">
            <a-range-picker
              v-model:value="filtroRangoFechas"
              format="DD/MM/YYYY"
              :placeholder="['dd/mm/aaaa', 'dd/mm/aaaa']"
              style="width: 100%"
            />
          </a-col>
          <a-col>
            <a-button
              type="default"
              :icon="h(DownloadOutlined)"
              style="color: #eb5757; border-color: #eb5757"
              title="Descargar"
            />
          </a-col>
          <a-col>
            <a-button
              type="default"
              :icon="h(SearchOutlined)"
              style="color: #eb5757; border-color: #eb5757"
              title="Buscar"
            />
          </a-col>
        </a-row>
        <!-- Borrar filtros -->
        <a-row>
          <a-col>
            <a-button
              type="link"
              style="color: #eb5757; display: flex; align-items: center; gap: 6px"
              @click="borrarFiltros"
            >
              <template #icon>
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  height="1em"
                  viewBox="0 0 640 512"
                  fill="#eb5757"
                >
                  <path
                    d="M497.9 142.1l-90-90c-12.5-12.5-32.8-12.5-45.3 0l-297 297c-12.5 12.5-12.5 32.8 0 45.3l90 90c12.5 12.5 32.8 12.5 45.3 0l297-297c12.5-12.5 12.5-32.8 0-45.3zM124.1 387.9L160 352h80l-80 80-35.9-35.9zM240 336h-80l240-240 80 80L240 336z"
                  />
                </svg>
              </template>
              Borrar filtros
            </a-button>
          </a-col>
        </a-row>
      </div>
    </div>
    <!-- Tabla de reclamos -->
    <div
      style="
        background-color: white;
        border-radius: 10px;
        border: 1px solid #f0f0f0;
        margin-top: 16px;
        width: 100%;
        padding: 24px;
        margin-left: auto;
        margin-right: auto;
      "
    >
      <a-table
        :columns="columns"
        :data-source="mockData"
        :pagination="false"
        row-key="codigoReclamo"
        size="middle"
        scroll="{ x: 'max-content' }"
      >
        <template #bodyCell="{ column, record }">
          <template v-if="column.dataIndex === 'estado'">
            <span :style="getEstadoStyle(record.estado)">● {{ record.estado }}</span>
          </template>

          <template v-else-if="column.dataIndex === 'kamEc'">
            <div style="display: flex; flex-direction: column; align-items: center">
              <span>{{ record.kamEc1 }}</span>
              <span style="background-color: #c4c4c4; padding: 2px 6px; border-radius: 4px">
                {{ record.kamEc2 }}
              </span>
            </div>
          </template>

          <template v-else-if="column.dataIndex === 'tipo'">
            <a-tag
              :style="{
                backgroundColor:
                  record.tipo === 'Infundado'
                    ? '#5C5AB4'
                    : record.tipo === 'Objetivo'
                      ? '#FF3B3B'
                      : '#d9d9d9',
                color: 'white',
                borderRadius: '12px',
                fontWeight: '600',
                fontSize: '14px',
                padding: '2px 12px',
                textAlign: 'center',
              }"
            >
              {{ record.tipo }}
            </a-tag>
          </template>
          <!-- Renderizar días + horas para Días gestión-->
          <template v-else-if="column.dataIndex === 'diasGestion'">
            <div style="display: flex; flex-direction: column; align-items: center">
              <span style="font-size: 14px">{{ getDiasHoras(record.fechaRecepcion).dias }}</span>
              <span style="font-size: 12px; color: #666">{{
                getDiasHoras(record.fechaRecepcion).horas
              }}</span>
            </div>
          </template>
          <!-- -->
          <template v-else-if="column.dataIndex === 'opciones'">
            <div style="display: flex">
              <div style="display: flex">
                <a-button type="text" style="color: #eb5757; padding: 0 4px" title="Ver">
                  <eye-outlined />
                </a-button>
                <a-button type="text" style="color: #eb5757; padding: 0 4px" title="Editar">
                  <edit-outlined />
                </a-button>
                <a-button type="text" style="color: #eb5757; padding: 0 4px" title="Eliminar">
                  <delete-outlined />
                </a-button>
              </div>
            </div>
          </template>
          <template v-else>
            {{ record[column.dataIndex] }}
          </template>
        </template>
      </a-table>
      <!-- Paginación -->
      <div
        class="row g-0 row-pagination"
        style="margin-top: 16px; display: flex; justify-content: center"
      >
        <a-pagination
          v-model:current="currentPageValue"
          v-model:pageSize="currentPageSize"
          :disabled="false"
          :total="total"
          :pageSizeOptions="DEFAULT_PAGE_SIZE_OPTIONS"
          show-size-changer
          :show-quick-jumper="false"
          @change="onChange"
        >
          <template #buildOptionText="{ value }">
            <span>{{ value }}</span>
          </template>
        </a-pagination>
        <!-- Botón Descargar Listado -->
        <a-button
          type="default"
          style="color: #eb5757; border-color: #eb5757; font-weight: 600; margin-left: 25px"
          @click="descargarExcel"
        >
          <DownloadOutlined style="margin-right: 6px" />
          Descargar Listado
        </a-button>

        <!-- -->
      </div>
    </div>
  </div>
  <!-- Pantalla del formulario -->
  <div v-else class="claim-form">
    <!--<h3>Customer Service - Reclamo</h3>-->
    <div style="margin-top: 38px; padding: 0 35px">
      <a-steps v-model:current="currentStep" size="small" class="custom-steps" :items="stepItems" />
    </div>
    <!-- -->
    <div class="steps-container">
      <a-row justify="center">
        <a-col :span="24">
          <a-form layout="vertical">
            <!-- Paso 0: Creación -->
            <template v-if="currentStep === 0">
              <!-- Título del formulario -->
              <a-row style="margin-bottom: 20px">
                <a-col :span="24"
                  ><div style="font-weight: bold; font-size: 22px">Nuevo reclamo:</div></a-col
                >
              </a-row>
              <!--  -->
              <a-row :gutter="14" class="row-spacing">
                <!-- Columna para NÚMERO DE FILE -->
                <a-col :span="12">
                  <div style="display: flex; align-items: center; gap: 10px">
                    <label style="font-weight: 600; min-width: 160px">Número de file:</label>
                    <a-input
                      v-model:value="form.nroFile"
                      placeholder="Ingrese el número de file"
                      style="flex: 1"
                    />
                  </div>
                </a-col>
                <!-- Columna para ESPECIALISTA -->
                <a-col :span="12">
                  <div style="display: flex; align-items: center; gap: 10px">
                    <label style="font-weight: 600; min-width: 150px">Especialista:</label>
                    <a-input
                      v-model:value="form.especialista"
                      placeholder="Ingrese el especialista"
                      style="flex: 1"
                    />
                  </div>
                </a-col>
              </a-row>

              <a-row :gutter="14" class="row-spacing">
                <!-- Columna para NOMBRE RESERVA -->
                <a-col :span="12">
                  <div style="display: flex; align-items: center; gap: 10px">
                    <label style="font-weight: 600; min-width: 160px">Nombre de reserva:</label>
                    <a-input
                      v-model:value="form.nombreReserva"
                      placeholder="Ingrese el nombre de la reserva"
                      style="flex: 1"
                    />
                  </div>
                </a-col>
                <!-- Columna para CLIENTE -->
                <a-col :span="12">
                  <div style="display: flex; align-items: center; gap: 10px">
                    <label style="font-weight: 600; min-width: 150px">Cliente:</label>
                    <a-input
                      v-model:value="form.cliente"
                      placeholder="Ingrese el cliente"
                      style="flex: 1"
                    />
                  </div>
                </a-col>
              </a-row>

              <a-row :gutter="14" class="row-spacing">
                <!-- Columna para AREA -->
                <a-col :span="12">
                  <div style="display: flex; align-items: center; gap: 10px">
                    <label style="font-weight: 600; min-width: 160px">Área:</label>
                    <a-input
                      v-model:value="form.area"
                      placeholder="Ingrese el área"
                      style="flex: 1"
                    />
                  </div>
                </a-col>
                <!-- Columna para FECHA DE LLEGADA -->
                <a-col :span="12">
                  <div style="display: flex; align-items: center; gap: 10px">
                    <label style="font-weight: 600; min-width: 150px">Fecha de llegada:</label>
                    <a-date-picker
                      v-model:value="form.fechaLlegada"
                      format="DD/MM/YYYY"
                      placeholder="dd/mm/aaaa"
                      style="width: 100%"
                    />
                  </div>
                </a-col>
              </a-row>
              <!-- Editor de Comentario -->
              <a-col :span="24" class="row-tight">
                <div style="display: flex; align-items: flex-start; gap: 10px">
                  <label class="label-horizontal" style="min-width: 160px">Comentario:</label>
                  <div style="flex: 1; min-width: 0">
                    <quill-editor
                      v-model:content="form.comentario"
                      contentType="html"
                      theme="snow"
                      :toolbar="toolbarOptions"
                      :style="{ height: '160px' }"
                      class="custom-editor"
                      @update:content="(val) => (form.comentario = limitTextLength(val, 2000))"
                    />
                    <div style="text-align: right; font-size: 12px; color: #999; margin-top: 4px">
                      {{ getPlainTextLength(form.comentario) }}/2000
                    </div>
                  </div>
                </div>
              </a-col>
              <!-- Subir archivos adjuntos -->
              <a-row :gutter="14" class="row-tight" style="align-items: center">
                <a-col :span="24">
                  <div style="display: flex; align-items: center; gap: 10px">
                    <label class="label-horizontal" style="min-width: 160px; font-weight: 600">
                      Adjuntar archivos:
                    </label>
                    <div style="flex: 1; display: flex; justify-content: flex-start">
                      <!-- se debe cambiar la URL por una nueva URL válida, esta que coloque de Mocky solo es para pruebas; no guarda archivos-->
                      <a-upload
                        multiple
                        v-model:file-list="fileListComentario"
                        action="https://www.mocky.io/v3/5185415ba171ea3a00704eed"
                        :before-upload="beforeUpload"
                        @change="handleChangeComentario"
                        :accept="'.png,.pdf'"
                        :max-count="5"
                      >
                        <a-button
                          type="default"
                          style="color: #eb5757; border-color: #eb5757; background-color: white"
                        >
                          <upload-outlined />
                          Subir archivos adjuntos
                        </a-button>
                      </a-upload>
                    </div>
                  </div>
                </a-col>
              </a-row>
              <!-- Resumen Comentario -->
              <a-col :span="24" class="row-tight" style="margin-top: 24px">
                <div style="display: flex; align-items: center; gap: 10px">
                  <label class="label-horizontal" style="min-width: 150px"
                    >Resumen comentario:</label
                  >
                  <a-input
                    v-model:value="form.resumenComentario"
                    :maxlength="200"
                    placeholder="Ingrese un resumen del comentario"
                    style="flex: 1"
                  />
                </div>
                <div style="text-align: right; font-size: 12px; color: #999; margin-top: 2px">
                  {{ form.resumenComentario.length }}/200
                </div>
              </a-col>
              <!-- Botones cancelar y guardar-->
              <a-row justify="end" class="row-buttons">
                <a-col :span="24" style="text-align: center; margin-top: 12px">
                  <a-button type="default" @click="goToList" style="margin-right: 10px"
                    >Cancelar</a-button
                  >
                  <a-button type="primary" @click="save">Guardar</a-button>
                </a-col>
              </a-row>
            </template>
            <!-- Paso 1: En proceso -->
            <template v-if="currentStep === 1">
              <a-row :gutter="[16, 16]" align="middle" style="margin-bottom: 12px">
                <!-- Columna izquierda: Icono + File -->
                <a-col :span="12">
                  <div style="display: flex; align-items: center; gap: 5px">
                    <!-- Icono tag a la izquierda de Fila -->
                    <span
                      v-html="tagIcon"
                      style="width: 20px; height: 20px; display: inline-flex; align-items: center"
                    ></span>
                    <span style="font-size: 24px; font-weight: bold"> File: </span>
                    <!-- Espacio reservado para 6 dígitos  del número de file -->
                    <span
                      style="
                        font-size: 20px;
                        font-weight: bold;
                        display: inline-block;
                        min-width: 72px;
                      "
                      >{{ form.nroFile }}</span
                    >
                  </div>
                </a-col>

                <a-col :span="12">
                  <div style="display: flex; justify-content: flex-end; gap: 8px">
                    <a-button
                      type="default"
                      style="color: #eb5757; border-color: #eb5757; background-color: white"
                      @click="abrirModalProductoNoConforme"
                    >
                      Producto no conforme
                    </a-button>
                    <a-popover
                      placement="bottomRight"
                      trigger="click"
                      v-model:visible="popoverVisible"
                      :autoAdjustOverflow="false"
                      :overlayStyle="{ maxHeight: '500px', overflow: 'hidden' }"
                    >
                      <template #content>
                        <div style="width: 400px; padding: 10px">
                          <a-tabs v-model:activeKey="activeTab" size="small">
                            <!-- Pestaña AÑADIR -->
                            <a-tab-pane key="añadir" tab="Añadir">
                              <div style="max-height: 300px; overflow-y: auto; padding-right: 8px">
                                <div
                                  v-for="(item, index) in codigosServicioTemporales"
                                  :key="index"
                                  style="margin-bottom: 12px"
                                >
                                  <!-- Fila: Código de servicio + ícono eliminar -->
                                  <div
                                    style="
                                      display: flex;
                                      justify-content: space-between;
                                      align-items: center;
                                    "
                                  >
                                    <label style="font-weight: 600; margin-bottom: 4px"
                                      >Código de servicio</label
                                    >
                                    <delete-outlined
                                      style="font-size: 18px; color: #eb5757; cursor: pointer"
                                      @click="codigosServicioTemporales.splice(index, 1)"
                                    />
                                  </div>
                                  <!-- Inputs -->
                                  <a-input
                                    v-model:value="item.codigo"
                                    placeholder="Escribe el código"
                                    style="margin-bottom: 6px"
                                  />
                                  <a-input
                                    v-model:value="item.motivo"
                                    placeholder="Escribe aquí el motivo"
                                  />
                                </div>
                              </div>
                              <!-- Botón Añadir código -->
                              <div style="display: flex; align-items: center; margin-top: 8px">
                                <a-button
                                  type="default"
                                  shape="circle"
                                  style="margin-right: 8px; color: #eb5757; border-color: #eb5757"
                                  @click="
                                    codigosServicioTemporales.push({ codigo: '', motivo: '' })
                                  "
                                >
                                  <plus-outlined />
                                </a-button>
                                <span style="font-weight: 600; color: #eb5757">Añadir código</span>
                              </div>
                            </a-tab-pane>
                            <!-- Pestaña LISTA -->
                            <a-tab-pane :key="'lista'" :tab="`Códigos (${codigosServicio.length})`">
                              <a-list
                                size="small"
                                :data-source="codigosServicio"
                                bordered
                                :renderItem="renderItem"
                                style="max-height: 200px; overflow-y: auto"
                              />
                            </a-tab-pane>
                          </a-tabs>
                          <!-- Botones cancelar y eliminar del código de servicio-->
                          <div style="display: flex; margin-top: 16px; margin-left: 178px">
                            <a-button
                              type="default"
                              @click="popoverVisible = false"
                              style="margin-right: 10px"
                            >
                              Cancelar
                            </a-button>
                            <a-button type="primary" @click="guardarTodosLosCodigos">
                              Guardar
                            </a-button>
                          </div>
                        </div>
                      </template>
                      <!-- BOTÓN TRIGGER -->
                      <a-button
                        type="default"
                        style="color: #eb5757; border-color: #eb5757; background-color: white"
                      >
                        + Código servicio
                      </a-button>
                    </a-popover>

                    <a-button
                      style="color: #eb5757; border-color: #eb5757; background-color: white"
                      @click="handleDelete"
                    >
                      <delete-outlined />Eliminar
                    </a-button>
                  </div>
                </a-col>
              </a-row>
              <!-- Panel informativo -->
              <a-col :span="24" style="margin-bottom: 24px">
                <div
                  style="
                    border: 1px solid #f0f0f0;
                    padding: 16px;
                    border-radius: 8px;
                    background: white;
                  "
                >
                  <a-row :gutter="[16, 16]">
                    <a-col :span="4">
                      <div>
                        <span style="font-weight: normal">Especialista:</span>
                        <div>
                          <strong style="display: inline-block; min-width: 120px">{{
                            form.especialista
                          }}</strong>
                        </div>
                      </div>
                    </a-col>
                    <a-col :span="6">
                      <div>
                        <span style="font-weight: normal">Nombre reserva:</span>
                        <div>
                          <strong style="display: inline-block; min-width: 120px">{{
                            form.nombreReserva
                          }}</strong>
                        </div>
                      </div>
                    </a-col>
                    <a-col :span="5">
                      <div>
                        <span style="font-weight: normal">Cliente:</span>
                        <div>
                          <strong style="display: inline-block; min-width: 120px">{{
                            form.cliente
                          }}</strong>
                        </div>
                      </div>
                    </a-col>
                    <a-col :span="4">
                      <div>
                        <span style="font-weight: normal">Área:</span>
                        <div>
                          <strong style="display: inline-block; min-width: 120px">{{
                            form.area
                          }}</strong>
                        </div>
                      </div>
                    </a-col>
                    <a-col :span="5">
                      <div>
                        <span style="font-weight: normal">Fecha de llegada:</span>
                        <div>
                          <strong style="display: inline-block; min-width: 120px">{{
                            form.fechaLlegada?.format?.('DD/MM/YYYY') || form.fechaLlegada
                          }}</strong>
                        </div>
                      </div>
                    </a-col>
                  </a-row>
                </div>
              </a-col>
              <!-- Resumen Comentario -->
              <a-col :span="24" class="row-tight">
                <div style="display: flex; align-items: center; gap: 10px">
                  <label class="label-horizontal" style="min-width: 160px"
                    >Resumen comentario:</label
                  >
                  <a-input
                    v-model:value="form.resumenComentario"
                    :maxlength="200"
                    placeholder="Ingrese un resumen del comentario"
                    style="flex: 1"
                  />
                </div>
                <div style="text-align: right; font-size: 12px; color: #999; margin-top: 2px">
                  {{ form.resumenComentario.length }}/200
                </div>
              </a-col>
              <!-- Editor de Respuesta-->
              <a-col :span="24" class="row-tight">
                <div style="display: flex; align-items: flex-start; gap: 10px">
                  <label class="label-horizontal" style="min-width: 160px">Respuesta:</label>
                  <div style="flex: 1; min-width: 0">
                    <quill-editor
                      v-model:content="form.respuesta"
                      contentType="html"
                      theme="snow"
                      :toolbar="toolbarOptions"
                      :style="{ height: '160px' }"
                      class="custom-editor"
                      @update:content="(val) => (form.respuesta = limitTextLength(val, 2000))"
                    />
                    <div style="text-align: right; font-size: 12px; color: #999; margin-top: 4px">
                      {{ getTextLength(form.respuesta) }}/2000
                    </div>
                  </div>
                </div>
              </a-col>
              <!-- Subir archivos adjuntos -->
              <a-row :gutter="14" class="row-tight" style="align-items: center">
                <a-col :span="24">
                  <div style="display: flex; align-items: center; gap: 10px">
                    <label class="label-horizontal" style="min-width: 160px; font-weight: 600">
                      Adjuntar archivos:
                    </label>
                    <div style="flex: 1; display: flex; justify-content: flex-start">
                      <!-- se debe cambiar la URL por una nueva URL válida, esta que coloque de Mocky solo es para pruebas; no guarda archivos-->
                      <a-upload
                        multiple
                        v-model:file-list="fileListRespuesta"
                        action="https://www.mocky.io/v3/5185415ba171ea3a00704eed"
                        :before-upload="beforeUpload"
                        @change="handleChangeRespuesta"
                        :accept="'.png,.pdf'"
                        :max-count="5"
                      >
                        <a-button
                          type="default"
                          style="color: #eb5757; border-color: #eb5757; background-color: white"
                        >
                          <upload-outlined />
                          Subir archivos adjuntos
                        </a-button>
                      </a-upload>
                    </div>
                  </div>
                </a-col>
              </a-row>
              <!-- Resumen Respuesta -->
              <a-col :span="24" class="row-tight" style="margin-top: 26px">
                <div style="display: flex; align-items: center; gap: 10px">
                  <label class="label-horizontal" style="min-width: 160px"
                    >Resumen respuesta:</label
                  >
                  <a-input
                    v-model:value="form.resumenRespuesta"
                    :maxlength="200"
                    placeholder="Ingrese un resumen de la respuesta"
                    style="flex: 1"
                  />
                </div>
                <div style="text-align: right; font-size: 12px; color: #999; margin-top: 2px">
                  {{ form.resumenRespuesta.length }}/200
                </div>
              </a-col>
              <!-- Botones cancelar y guardar-->
              <a-row justify="end" class="row-buttons">
                <a-col :span="24" style="text-align: center; margin-top: 12px">
                  <a-button type="default" @click="goToList" style="margin-right: 10px"
                    >Cancelar</a-button
                  >
                  <a-button type="primary" @click="save">Guardar</a-button>
                </a-col>
              </a-row>
            </template>
            <!-- Paso 2: Cierre -->
            <template v-if="currentStep === 2">
              <a-row :gutter="[16, 16]" align="middle" style="margin-bottom: 12px">
                <!-- Columna izquierda: Icono + File -->
                <a-col :span="12">
                  <div style="display: flex; align-items: center; gap: 5px">
                    <!-- Icono tag a la izquierda de Fila -->
                    <span
                      v-html="tagIcon"
                      style="width: 20px; height: 20px; display: inline-flex; align-items: center"
                    ></span>
                    <span style="font-size: 24px; font-weight: bold"> File: </span>
                    <!-- Espacio reservado para 6 dígitos  del número de file -->
                    <span
                      style="
                        font-size: 20px;
                        font-weight: bold;
                        display: inline-block;
                        min-width: 72px;
                      "
                      >{{ form.nroFile }}</span
                    >
                  </div>
                </a-col>
                <a-col :span="12">
                  <div style="display: flex; justify-content: flex-end; gap: 8px">
                    <a-button
                      type="default"
                      style="color: #eb5757; border-color: #eb5757; background-color: white"
                      @click="abrirModalProductoNoConforme"
                    >
                      Producto no conforme
                    </a-button>
                    <a-popover
                      placement="bottomRight"
                      trigger="click"
                      v-model:visible="popoverVisible"
                      :autoAdjustOverflow="false"
                      :overlayStyle="{ maxHeight: '500px', overflow: 'hidden' }"
                    >
                      <template #content>
                        <div style="width: 400px; padding: 10px">
                          <a-tabs v-model:activeKey="activeTab" size="small">
                            <!-- Pestaña AÑADIR -->
                            <a-tab-pane key="añadir" tab="Añadir">
                              <div style="max-height: 300px; overflow-y: auto; padding-right: 8px">
                                <div
                                  v-for="(item, index) in codigosServicioTemporales"
                                  :key="index"
                                  style="margin-bottom: 12px"
                                >
                                  <!-- Fila: Código de servicio + ícono eliminar -->
                                  <div
                                    style="
                                      display: flex;
                                      justify-content: space-between;
                                      align-items: center;
                                    "
                                  >
                                    <label style="font-weight: 600; margin-bottom: 4px"
                                      >Código de servicio</label
                                    >
                                    <delete-outlined
                                      style="font-size: 18px; color: #eb5757; cursor: pointer"
                                      @click="codigosServicioTemporales.splice(index, 1)"
                                    />
                                  </div>

                                  <!-- Inputs -->
                                  <a-input
                                    v-model:value="item.codigo"
                                    placeholder="Escribe el código"
                                    style="margin-bottom: 6px"
                                  />
                                  <a-input
                                    v-model:value="item.motivo"
                                    placeholder="Escribe aquí el motivo"
                                  />
                                </div>
                              </div>
                              <!-- Botón Añadir código -->
                              <div style="display: flex; align-items: center; margin-top: 8px">
                                <a-button
                                  type="default"
                                  shape="circle"
                                  style="margin-right: 8px; color: #eb5757; border-color: #eb5757"
                                  @click="
                                    codigosServicioTemporales.push({ codigo: '', motivo: '' })
                                  "
                                >
                                  <plus-outlined />
                                </a-button>
                                <span style="font-weight: 600; color: #eb5757">Añadir código</span>
                              </div>
                            </a-tab-pane>
                            <!-- Pestaña LISTA -->
                            <a-tab-pane :key="'lista'" :tab="`Códigos (${codigosServicio.length})`">
                              <a-list
                                size="small"
                                :data-source="codigosServicio"
                                bordered
                                :renderItem="renderItem"
                                style="max-height: 200px; overflow-y: auto"
                              />
                            </a-tab-pane>
                          </a-tabs>
                          <!-- Botones cancelar y eliminar del código de servicio-->
                          <div style="display: flex; margin-top: 16px; margin-left: 178px">
                            <a-button
                              type="default"
                              @click="popoverVisible = false"
                              style="margin-right: 10px"
                            >
                              Cancelar
                            </a-button>
                            <a-button type="primary" @click="guardarTodosLosCodigos">
                              Guardar
                            </a-button>
                          </div>
                        </div>
                      </template>
                      <!-- BOTÓN TRIGGER -->
                      <a-button
                        type="default"
                        style="color: #eb5757; border-color: #eb5757; background-color: white"
                      >
                        + Código servicio
                      </a-button>
                    </a-popover>

                    <a-button
                      style="color: #eb5757; border-color: #eb5757; background-color: white"
                      @click="handleDelete"
                    >
                      <delete-outlined />Eliminar
                    </a-button>
                  </div>
                </a-col>
              </a-row>
              <!-- Panel informativo -->
              <a-col :span="24" style="margin-bottom: 24px">
                <div
                  style="
                    border: 1px solid #f0f0f0;
                    padding: 16px;
                    border-radius: 8px;
                    background: white;
                  "
                >
                  <a-row :gutter="[16, 16]">
                    <a-col :span="4">
                      <div>
                        <span style="font-weight: normal">Especialista:</span>
                        <div>
                          <strong style="display: inline-block; min-width: 120px">{{
                            form.especialista
                          }}</strong>
                        </div>
                      </div>
                    </a-col>
                    <a-col :span="6">
                      <div>
                        <span style="font-weight: normal">Nombre reserva:</span>
                        <div>
                          <strong style="display: inline-block; min-width: 120px">{{
                            form.nombreReserva
                          }}</strong>
                        </div>
                      </div>
                    </a-col>
                    <a-col :span="5">
                      <div>
                        <span style="font-weight: normal">Cliente:</span>
                        <div>
                          <strong style="display: inline-block; min-width: 120px">{{
                            form.cliente
                          }}</strong>
                        </div>
                      </div>
                    </a-col>
                    <a-col :span="4">
                      <div>
                        <span style="font-weight: normal">Área:</span>
                        <div>
                          <strong style="display: inline-block; min-width: 120px">{{
                            form.area
                          }}</strong>
                        </div>
                      </div>
                    </a-col>
                    <a-col :span="5">
                      <div>
                        <span style="font-weight: normal">Fecha de llegada:</span>
                        <div>
                          <strong style="display: inline-block; min-width: 120px">{{
                            form.fechaLlegada?.format?.('DD/MM/YYYY') || form.fechaLlegada
                          }}</strong>
                        </div>
                      </div>
                    </a-col>
                  </a-row>
                </div>
              </a-col>
              <!-- Checkbox: Ignorar notificaciones por correo -->
              <a-row style="margin-bottom: 16px">
                <a-col :span="24">
                  <a-checkbox v-model:checked="form.ignorarCorreo">
                    Ignorar notificaciones por correo
                  </a-checkbox>
                </a-col>
              </a-row>
              <a-collapse
                v-model:activeKey="activeKey"
                expandIconPosition="end"
                bordered
                :style="{
                  border: '1px solid #f0f0f0',
                  borderRadius: '8px',
                  backgroundColor: 'white',
                }"
              >
                <!-- Panel: Cierre del reclamo -->
                <a-collapse-panel
                  :header="renderCollapseHeader('Cierre del reclamo', iconHTML)"
                  key="1"
                  class="custom-panel"
                >
                  <!-- RESUMEN COMENTARIO -->
                  <a-col :span="24" class="row-tight">
                    <div style="display: flex; align-items: center; gap: 10px">
                      <label class="label-horizontal" style="min-width: 175px"
                        >Resumen comentario:</label
                      >
                      <a-input
                        v-model:value="form.resumenComentario"
                        :maxlength="200"
                        placeholder="Ingrese un resumen del comentario"
                        style="flex: 1"
                      />
                    </div>
                    <div style="text-align: right; font-size: 12px; color: #999; margin-top: 2px">
                      {{ form.resumenComentario.length }}/200
                    </div>
                  </a-col>
                  <!-- Resumen Respuesta -->
                  <a-col :span="24" class="row-tight">
                    <div style="display: flex; align-items: center; gap: 10px">
                      <label class="label-horizontal" style="min-width: 175px"
                        >Resumen respuesta:</label
                      >
                      <a-input
                        v-model:value="form.resumenRespuesta"
                        :maxlength="200"
                        placeholder="Ingrese un resumen de la respuesta"
                        style="flex: 1"
                      />
                    </div>
                    <div style="text-align: right; font-size: 12px; color: #999; margin-top: 2px">
                      {{ form.resumenRespuesta.length }}/200
                    </div>
                  </a-col>
                  <!-- Fila: Fecha de cierre + Checkboxes -->
                  <a-row class="row-spacing" align="middle">
                    <a-col :span="24">
                      <div
                        style="
                          display: flex;
                          align-items: center;
                          justify-content: space-between;
                          gap: 24px;
                        "
                      >
                        <!-- Fecha de cierre (lado izquierdo, espacio reservado siempre) -->
                        <div style="display: flex; align-items: center; gap: 10px; width: 50%">
                          <label style="font-weight: 600; min-width: 175px" v-show="form.cerrado">
                            Fecha de cierre:
                          </label>
                          <a-date-picker
                            v-model:value="form.fechaCierre"
                            format="DD/MM/YYYY"
                            placeholder="dd/mm/aaaa"
                            :style="{ flex: 1, visibility: form.cerrado ? 'visible' : 'hidden' }"
                          />
                        </div>
                        <!-- Checkboxes (lado derecho) -->
                        <div
                          style="
                            display: flex;
                            align-items: center;
                            gap: 24px;
                            justify-content: flex-end;
                            width: 50%;
                          "
                        >
                          <a-checkbox v-model:checked="form.infundado"
                            >Reclamo infundado</a-checkbox
                          >
                          <a-checkbox v-model:checked="form.cerrado">Reclamo cerrado</a-checkbox>
                        </div>
                      </div>
                    </a-col>
                  </a-row>
                </a-collapse-panel>
                <!-- Panel: Información adicional -->
                <a-collapse-panel key="2" class="custom-panel">
                  <template #header>
                    <span style="font-weight: bold">
                      <InfoCircleOutlined style="margin-right: 8px" />
                      Información adicional
                    </span>
                  </template>
                  <a-row :gutter="14" class="row-spacing">
                    <a-col :span="12">
                      <div style="display: flex; align-items: center; gap: 10px">
                        <label style="font-weight: 600; min-width: 175px"
                          >Monto devolución total:</label
                        >
                        <a-input-number
                          :value="montoDevolucionTotal"
                          style="flex: 1; background-color: white"
                          disabled
                        />
                      </div>
                    </a-col>
                  </a-row>

                  <a-row :gutter="14" class="row-spacing">
                    <a-col :span="12">
                      <div style="display: flex; align-items: center; gap: 10px">
                        <label style="font-weight: 600; min-width: 175px"
                          >Monto compensación:</label
                        >
                        <a-input-number v-model:value="form.montoCompensacion" style="flex: 1" />
                      </div>
                    </a-col>
                    <a-col :span="12">
                      <div style="display: flex; align-items: center; gap: 10px">
                        <label style="font-weight: 600; min-width: 140px; max-width: 180px"
                          >Observaciones compensación:</label
                        >
                        <a-input v-model:value="form.observacionesCompensacion" style="flex: 1" />
                      </div>
                    </a-col>
                  </a-row>

                  <a-row :gutter="14" class="row-spacing">
                    <a-col :span="12">
                      <div style="display: flex; align-items: center; gap: 10px">
                        <label style="font-weight: 600; min-width: 175px">Monto reembolso:</label>
                        <a-input-number v-model:value="form.montoReembolso" style="flex: 1" />
                      </div>
                    </a-col>
                    <a-col :span="12">
                      <div style="display: flex; align-items: center; gap: 10px">
                        <label style="font-weight: 600; min-width: 140px; max-width: 180px"
                          >Observaciones reembolso:</label
                        >
                        <a-input v-model:value="form.observacionesReembolso" style="flex: 1" />
                      </div>
                    </a-col>
                  </a-row>
                  <!-- Cuadro de texto para Conclusiones -->
                  <a-col :span="24" class="row-tight">
                    <div style="display: flex; align-items: center; gap: 10px">
                      <label class="label-horizontal" style="min-width: 175px">Conclusiones:</label>
                      <a-input
                        v-model:value="form.conclusiones"
                        :maxlength="200"
                        placeholder="Ingrese las conclusiones"
                        style="flex: 1"
                      />
                    </div>
                    <div style="text-align: right; font-size: 12px; color: #999; margin-top: 2px">
                      {{ form.conclusiones.length }}/200
                    </div>
                  </a-col>
                </a-collapse-panel>
                <!-- Panel: Réplicas -->
                <a-collapse-panel key="3" class="custom-panel">
                  <template #header>
                    <span style="font-weight: bold">
                      <MessageOutlined style="margin-right: 8px" />
                      Réplicas
                    </span>
                  </template>
                  <div
                    v-for="(replica, index) in replicas"
                    :key="index"
                    style="margin-bottom: 24px"
                  >
                    <a-row :gutter="14" class="row-spacing">
                      <!-- Columna izquierda: "Réplica" -->
                      <a-col :span="12">
                        <div style="display: flex; align-items: center; gap: 10px">
                          <label style="font-weight: 600; min-width: 175px">Réplica:</label>
                          <a-date-picker
                            v-model:value="replica.fecha"
                            format="DD/MM/YYYY"
                            placeholder="dd/mm/aaaa"
                            style="flex: 1"
                          />
                        </div>
                      </a-col>
                      <a-col
                        :span="12"
                        style="
                          display: flex;
                          align-items: center;
                          gap: 12px;
                          justify-content: flex-end;
                        "
                      >
                        <edit-outlined
                          style="font-size: 18px; color: #eb5757; cursor: pointer"
                          title="Editar"
                        />
                        <delete-outlined
                          style="font-size: 18px; color: #eb5757; cursor: pointer"
                          @click="eliminarReplica(index)"
                          title="Eliminar"
                        />
                      </a-col>
                    </a-row>

                    <a-row :gutter="14" class="row-tight">
                      <a-col :span="24">
                        <div style="display: flex; align-items: flex-start; gap: 10px">
                          <label class="label-horizontal" style="min-width: 175px"></label>
                          <div style="flex: 1; min-width: 0">
                            <quill-editor
                              v-model:content="replica.comentario"
                              contentType="html"
                              theme="snow"
                              :toolbar="toolbarOptions"
                              :style="{ height: '120px' }"
                              class="custom-editor"
                              @update:content="
                                (val) => (replica.comentario = limitTextLength(val, 1000))
                              "
                            />
                            <div
                              style="
                                text-align: right;
                                font-size: 12px;
                                color: #999;
                                margin-top: 4px;
                              "
                            >
                              {{ getPlainTextLength(replica.comentario) }}/1000
                            </div>
                          </div>
                        </div>
                      </a-col>
                    </a-row>
                  </div>
                  <!-- Botón para añadir nueva réplica -->
                  <a-row>
                    <a-col :span="24" style="text-align: left">
                      <a-button
                        type="default"
                        shape="circle"
                        style="margin-right: 8px; color: #eb5757; border-color: #eb5757"
                        @click="agregarReplica"
                      >
                        <template #icon>
                          <span style="font-size: 18px; line-height: 1">+</span>
                        </template>
                      </a-button>
                      <span style="font-weight: 600; color: #eb5757">Añadir réplica</span>
                    </a-col>
                  </a-row>
                </a-collapse-panel>
              </a-collapse>
              <!--  -->
              <!-- Botones cancelar y guardar-->
              <a-row justify="end" class="row-buttons">
                <a-col :span="24" style="text-align: center; margin-top: 12px">
                  <a-button type="default" @click="goToList" style="margin-right: 10px"
                    >Cancelar</a-button
                  >
                  <a-button type="primary" @click="save">Guardar</a-button>
                </a-col>
              </a-row>
            </template>
          </a-form>
        </a-col>
      </a-row>
      <input type="file" ref="fileInput" style="display: none" @change="handleFileUpload" />
    </div>
  </div>
  <!-- MODAL: Producto no conforme (es para la ventana flotante)-->
  <a-modal
    v-model:visible="modalProductoVisible"
    centered
    width="650px"
    :footer="null"
    class="producto-no-conforme-modal"
    closable
  >
    <!-- Pestañas al inicio -->
    <a-tabs v-model:activeKey="activeTabProducto" size="small">
      <!-- TAB AGREGAR -->
      <a-tab-pane key="agregar" tab="Agregar">
        <!-- Barra interna -->
        <div
          style="
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
          "
        >
          <div style="display: flex; align-items: center; gap: 8px">
            <!-- Ícono de + en cuadrado rojo -->
            <div
              style="
                width: 24px;
                height: 24px;
                border: 1px solid #eb5757;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 4px;
              "
            >
              <span style="color: #eb5757; font-weight: bold; font-size: 16px">+</span>
            </div>
            <span style="font-weight: 600; font-size: 14px">Agregar producto no conforme</span>
          </div>
          <div
            style="
              background: #f5f5f5;
              padding: 2px 8px;
              border-radius: 4px;
              font-size: 12px;
              display: flex;
              align-items: center;
              gap: 4px;
            "
          >
            <span
              v-html="tagIcon"
              style="width: 20px; height: 20px; display: inline-flex; align-items: center"
            ></span>
            <span style="font-size: 24px; font-weight: bold"> File: </span>
            <!-- Espacio reservado para 6 dígitos  del número de file -->
            <span style="display: inline-block; min-width: 80px; text-align: center">
              {{ form.nroFile }}
            </span>
          </div>
        </div>

        <!-- Formulario -->
        <div style="max-height: 450px; overflow-y: auto; padding-right: 8px">
          <a-form layout="vertical">
            <a-form-item label="Pasajero">
              <a-select placeholder="Selecciona" />
            </a-form-item>

            <a-row :gutter="12">
              <a-col :span="8">
                <a-form-item label="Tipo de servicio">
                  <a-select placeholder="Selecciona" />
                </a-form-item>
              </a-col>
              <a-col :span="8">
                <a-form-item label="Servicio">
                  <a-select placeholder="Selecciona" />
                </a-form-item>
              </a-col>
              <a-col :span="8">
                <a-form-item label="Fecha de incidente">
                  <a-date-picker
                    v-model:value="form.fechaIncidente"
                    format="DD/MM/YYYY"
                    valueFormat="DD/MM/YYYY"
                    placeholder="dd/mm/aaaa"
                    style="width: 100%"
                  />
                </a-form-item>
              </a-col>
            </a-row>

            <a-form-item label="Categoría">
              <a-select placeholder="Selecciona" />
            </a-form-item>

            <a-form-item label="Subcategoría">
              <a-select placeholder="Selecciona" />
            </a-form-item>

            <a-form-item label="Falta">
              <div style="display: flex; align-items: center; gap: 16px">
                <a-select placeholder="Selecciona" style="flex: 1" />
                <a-checkbox>Infundado</a-checkbox>
                <a-checkbox>Subjetivo</a-checkbox>
              </div>
            </a-form-item>

            <a-form-item label="Comentario">
              <quill-editor
                v-model:content="comentarioProducto"
                contentType="html"
                theme="snow"
                :toolbar="toolbarOptions"
                :style="{ height: '120px' }"
                class="custom-editor"
                @update:content="(val) => (comentarioProducto = limitTextLength(val, 1000))"
              />
              <div style="text-align: right; font-size: 12px; color: #999; margin-top: 4px">
                {{ getPlainTextLength(comentarioProducto) }}/1000
              </div>
            </a-form-item>
          </a-form>
        </div>

        <div style="display: flex; justify-content: center; gap: 12px; margin-top: 12px">
          <a-button @click="modalProductoVisible = false">Cancelar</a-button>
          <a-button type="primary" @click="guardarProductoNoConforme">Agregar</a-button>
        </div>
      </a-tab-pane>

      <!-- Pestaña PRODUCTOS -->
      <a-tab-pane :key="'productos'" :tab="`Productos (${productosNoConformes.length})`">
        <!-- Encabezado -->
        <div
          style="
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
          "
        >
          <div style="display: flex; align-items: center; gap: 8px">
            <!-- Ícono tres puntitos con líneas -->
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="20"
              height="20"
              viewBox="0 0 24 24"
              fill="none"
              stroke="#eb5757"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            >
              <line x1="8" y1="6" x2="21" y2="6"></line>
              <line x1="8" y1="12" x2="21" y2="12"></line>
              <line x1="8" y1="18" x2="21" y2="18"></line>
              <circle cx="3" cy="6" r="1"></circle>
              <circle cx="3" cy="12" r="1"></circle>
              <circle cx="3" cy="18" r="1"></circle>
            </svg>
            <span style="font-weight: 600; font-size: 16px">Productos no conforme</span>
          </div>
          <div
            style="
              background: #f5f5f5;
              padding: 2px 8px;
              border-radius: 4px;
              font-size: 12px;
              display: flex;
              align-items: center;
              gap: 4px;
            "
          ></div>
        </div>

        <!-- Lista -->
        <div style="max-height: 400px; overflow-y: auto; padding-right: 8px">
          <a-list :data-source="productosNoConformes" :renderItem="renderProductoNoConforme" />
        </div>
      </a-tab-pane>
    </a-tabs>
  </a-modal>
  <a-modal
    v-model:visible="detalleProductoVisible"
    title="Detalle de producto no conforme"
    footer="{null}"
    width="500px"
  >
    <div v-if="productoSeleccionado">
      <p><strong>Código:</strong> {{ productoSeleccionado.codigo }}</p>
      <p><strong>Categoría:</strong> {{ productoSeleccionado.categoria }}</p>
      <p><strong>Fecha:</strong> {{ productoSeleccionado.fecha }}</p>
      <!-- campos que se quiere mostrar -->
    </div>
  </a-modal>
</template>
<script setup>
  import { ref, computed, watch, h } from 'vue';
  import { useClaimsStore } from '@/modules/claims/stores/claims-store.js';
  import { QuillEditor } from '@vueup/vue-quill';
  import { defineEmits } from 'vue';
  import '@vueup/vue-quill/dist/vue-quill.snow.css';
  import { UploadOutlined, PlusOutlined, EyeOutlined } from '@ant-design/icons-vue';
  import {
    DeleteOutlined,
    EditOutlined,
    SearchOutlined,
    DownloadOutlined,
    MessageOutlined,
    InfoCircleOutlined,
  } from '@ant-design/icons-vue';
  import { Upload, message, Modal } from 'ant-design-vue';

  function getDiasHoras(fechaRecepcion) {
    if (!fechaRecepcion) return { dias: '', horas: '' };
    const [day, month, year] = fechaRecepcion.split('/'); // formato DD/MM/YYYY
    const fechaInicio = new Date(`${year}-${month}-${day}`);
    const fechaActual = new Date();
    const diffMs = fechaActual - fechaInicio;
    const diffHoras = Math.floor(diffMs / (1000 * 60 * 60));
    const diffDias = Math.floor(diffHoras / 24);
    return {
      dias: diffDias, // aquí irá por ejemplo 7
      horas: `(${diffHoras} horas)`, // aqui irá por ejemplo (187 horas)
    };
  }

  const pantallaInicial = ref(true);
  const iniciarReclamo = () => {
    pantallaInicial.value = false;
    currentStep.value = 0; // o 1 si quieres iniciar desde el paso 1 directamente
  };
  const goToList = () => {
    pantallaInicial.value = true;
    currentStep.value = 0;
  };
  const fileListComentario = ref([]);
  const fileListRespuesta = ref([]);
  const handleChangeComentario = ({ file, fileList }) => {
    if (file.status !== 'uploading') {
      console.log('Archivo comentario:', file);
    }
    fileListComentario.value = [...fileList];
  };
  const handleChangeRespuesta = ({ file, fileList }) => {
    if (file.status !== 'uploading') {
      console.log('Archivo respuesta:', file);
    }
    fileListRespuesta.value = [...fileList];
  };
  const beforeUpload = (file) => {
    const isAllowed = file.type === 'image/png' || file.type === 'application/pdf';
    if (!isAllowed) {
      message.error(`${file.name} no es un archivo permitido (PNG, PDF)`);
    }
    return isAllowed || Upload.LIST_IGNORE;
  };

  const claimsStore = useClaimsStore();
  function getPlainTextLength(html) {
    const div = document.createElement('div');
    div.innerHTML = html || '';
    return div.textContent?.length || 0;
  }
  function limitTextLength(html, maxLength) {
    const div = document.createElement('div');
    div.innerHTML = html || '';
    const text = div.textContent || '';
    if (text.length <= maxLength) return html;
    // Corta el texto si supera el límite
    return text.slice(0, maxLength);
  }
  function getTextLength(html) {
    const div = document.createElement('div');
    div.innerHTML = html || '';
    return div.textContent?.length || 0;
  }
  const form = ref({
    nroFile: '',
    especialista: '',
    nombreReserva: '',
    cliente: '',
    area: '',
    comentario: '',
    respuesta: '',
    montoCompensacion: 0,
    montoReembolso: 0,
    infundado: false,
    cerrado: false,
    fechaLlegada: null,
    fechaCierre: null,
    fechaIncidente: null,
    resumenComentario: '',
    resumenRespuesta: '',
    conclusiones: '',
    ignorarCorreo: false,
  });
  // para calcular el monto total
  const montoDevolucionTotal = computed(() => {
    return (form.value.montoCompensacion || 0) + (form.value.montoReembolso || 0);
  });
  const tagIcon = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="black" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
  <path d="M20.59 13.41L13.41 20.59a2 2 0 0 1-2.83 0L3 13V3h10l7.59 7.59a2 2 0 0 1 0 2.82z" />
  <circle cx="7.5" cy="7.5" r="1.5" /></svg>`;
  // Opciones de la barra de herramientas
  const toolbarOptions = [
    [{ font: ['sans-serif', 'serif', 'monospace', 'cursive', 'fantasy'] }], // Fuente
    [{ header: [1, 2, 3, false] }], // Tamaño de encabezado
    ['bold', 'italic', 'underline', 'strike'], // Estilos de texto
    [{ align: [] }], // Alineación
    [{ list: 'ordered' }, { list: 'bullet' }], // Listas numeradas y con viñetas
    ['image'],
  ];
  const emit = defineEmits(['goToList']);
  // Función para manejar la subida de archivos
  const handleFileUpload = () => {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = '*/*'; // Permitir todos los archivos
    input.click();
  };
  const currentStep = ref(0); // 0: Creación, 1: En proceso, 2: Cierre
  const stepItems = computed(() => [
    {
      title: currentStep.value > 0 ? 'Finalizado' : 'Creación',
      description: 'Información general',
    },
    {
      title:
        currentStep.value > 1
          ? 'Finalizado'
          : currentStep.value === 1
            ? 'En proceso'
            : 'En proceso',
      description: 'Respuesta',
    },
    {
      title: currentStep.value === 2 && form.value.cerrado ? 'Finalizado' : 'Cierre',
      description: 'Reclamo completo',
    },
  ]);
  const save = async () => {
    if (!form.value.cerrado) {
      form.value.fechaCierre = null;
    }
    console.log('Guardando reclamo...', form.value);
    await claimsStore.saveClaim(form.value);
    // Avanza el paso si corresponde
    if (currentStep.value === 0) {
      currentStep.value = 1;
    } else if (currentStep.value === 1 && form.value.cerrado) {
      currentStep.value = 2;
    }
  };
  watch(currentStep, () => {
    setTimeout(() => {
      const toolbar = document.querySelector('.ql-toolbar');
      if (toolbar) {
        const titles = {
          'ql-bold': 'Negrita',
          'ql-italic': 'Cursiva',
          'ql-underline': 'Subrayado',
          'ql-strike': 'Tachado',
          'ql-list': 'Lista',
          'ql-header': 'Encabezado',
          'ql-align': 'Alinear',
          'ql-image': 'Insertar imagen',
          'ql-font': 'Fuente',
        };
        Object.keys(titles).forEach((className) => {
          const btns = toolbar.querySelectorAll(`.${className}`);
          btns.forEach((btn) => {
            if (className === 'ql-list') {
              const value = btn.getAttribute('value');
              if (value === 'ordered') {
                btn.setAttribute('title', 'Lista numerada');
              } else if (value === 'bullet') {
                btn.setAttribute('title', 'Lista con viñetas');
              } else {
                btn.setAttribute('title', 'Lista');
              }
            } else {
              btn.setAttribute('title', titles[className]);
            }
          });
        });
      }
    }, 100); // Pequeño delay para que el DOM se actualice
  });
  const handleDelete = () => {
    Modal.confirm({
      title: '¿Estás seguro de que deseas eliminar este file?',
      okText: 'Sí, eliminar',
      cancelText: 'Cancelar',
      okType: 'danger', // Botón rojo
      centered: true, // Centra el modal
      onOk() {
        claimsStore.deleteClaim(form.value.nroFile);
        goToList(); // Regresa al listado después de eliminar
      },
    });
  };
  const modalCodigoVisible = ref(false);
  const codigosServicioTemporales = ref([{ codigo: '', motivo: '' }]);
  const popoverVisible = ref(false);
  const activeTab = ref('añadir');
  const codigosServicio = ref([]);
  // Al guardar (puedes hacer un save a tu store aquí)
  const guardarTodosLosCodigos = () => {
    console.log('Códigos guardados:', codigosServicio.value);
    modalCodigoVisible.value = false;
  };
  // Render function para lista de códigos
  const renderItem = ({ item }) => {
    return h('a-list-item', {}, [
      h('div', [
        h('strong', item.codigo),
        h('div', { style: 'font-size: 12px; color: #888' }, item.motivo),
      ]),
    ]);
  };
  const modalProductoVisible = ref(false);
  const comentarioProducto = ref('');
  const abrirModalProductoNoConforme = () => {
    modalProductoVisible.value = true;
  };
  const guardarProductoNoConforme = () => {
    const fecha = new Date();
    const fechaStr = fecha.toLocaleDateString('es-PE'); // Ej: 13/08/2025
    const horaStr = fecha.toLocaleTimeString('es-PE', { hour12: false }); // Ej: 10:44:02

    productosNoConformes.value.push({
      codigo: 'P-' + (productosNoConformes.value.length + 1),
      categoria: 'Categoría ejemplo',
      fecha: fechaStr,
      hora: horaStr,
    });
    modalProductoVisible.value = false;
  };
  const activeKey = ref([]); // cerrados por defecto los desglosables en el paso 3
  const renderCollapseHeader = (title, iconHTML) => {
    return h('div', {
      class: 'custom-header-content',
      style: {
        display: 'flex',
        alignItems: 'center',
        gap: '8px',
      },
      innerHTML: `${iconHTML}<span style="font-weight: 600">${title}</span>`,
    });
  };
  const iconHTML = `
  <span style="display: inline-block; width: 16px; height: 16px; position: relative;">
    ${Array.from({ length: 8 })
      .map((_, i) => {
        const angle = i * 45;
        return `<span style="
        background-color: #1890ff;
        width: 2px;
        height: 6px;
        border-radius: 1px;
        position: absolute;
        top: 2px;
        left: 7px;
        transform: rotate(${angle}deg) translateY(-6px);
        transform-origin: center;
      "></span>`;
      })
      .join('')}
  </span>
`;
  const replicas = ref([{ fecha: null, comentario: '' }]);
  const agregarReplica = () => {
    replicas.value.push({ fecha: null, comentario: '' });
  };
  const eliminarReplica = (index) => {
    replicas.value.splice(index, 1);
  };
  const columns = [
    { title: '# Reclamo', dataIndex: 'codigoReclamo', key: 'codigoReclamo', align: 'center' },
    { title: 'N° File', dataIndex: 'nroFile', key: 'nroFile', align: 'center' },
    { title: 'Usuario', dataIndex: 'usuario', key: 'usuario', align: 'center' },
    { title: 'Cliente', dataIndex: 'cliente', key: 'cliente', align: 'center' },
    { title: 'Nombre del file', dataIndex: 'nombreFile', key: 'nombreFile', align: 'center' },
    {
      title: () => h('div', { style: 'text-align: center' }, [h('div', 'KAM'), h('div', 'EC')]),
      dataIndex: 'kamEc',
      key: 'kamEc',
      align: 'center',
    },
    {
      title: 'Fecha recepción',
      dataIndex: 'fechaRecepcion',
      key: 'fechaRecepcion',
      align: 'center',
    },
    {
      title: 'Fecha resolución',
      dataIndex: 'fechaResolucion',
      key: 'fechaResolucion',
      align: 'center',
    },
    { title: 'Días gestión', dataIndex: 'diasGestion', key: 'diasGestion', align: 'center' },
    { title: 'Tipo', dataIndex: 'tipo', key: 'tipo', align: 'center' },
    { title: 'Estado', dataIndex: 'estado', key: 'estado', align: 'center' },
    { title: 'Opciones', dataIndex: 'opciones', key: 'opciones', align: 'center' },
  ];
  const mockData = [
    {
      codigoReclamo: 'Q00001',
      nroFile: '123456',
      usuario: 'jrojas',
      cliente: 'LIMATOURS',
      nombreFile: 'JOSÉ PÉREZ',
      kamEc1: 'AEC',
      kamEc2: 'LZZ',
      fechaRecepcion: '19/07/2025',
      fechaResolucion: '23/07/2025',
      diasGestion: 2,
      tipo: 'Infundado',
      estado: 'Cerrado',
    },
    {
      codigoReclamo: 'Q00002',
      nroFile: '654321',
      usuario: 'mmendoza',
      cliente: 'TUI IBERIA',
      nombreFile: 'MARÍA LOPEZ',
      kamEc1: 'AWC',
      kamEc2: 'ÑZZ',
      fechaRecepcion: '30/05/2025',
      fechaResolucion: '31/05/2025',
      diasGestion: 1,
      tipo: 'Objetivo',
      estado: 'Creado',
    },
    {
      codigoReclamo: 'Q00003',
      nroFile: '654321',
      usuario: 'Pmendoza',
      cliente: 'TUI ROMA',
      nombreFile: 'MARÍA LOPEZ',
      kamEc1: 'AWC',
      kamEc2: 'ÑAZ',
      fechaRecepcion: '15/07/2025',
      fechaResolucion: '',
      diasGestion: 1,
      tipo: 'Objetivo',
      estado: 'En proceso',
    },
  ];
  /* para producto no conforme*/
  const activeTabProducto = ref('agregar');
  const productosNoConformes = ref([
    { codigo: '315080 - SAMAY CASA', categoria: 'Tours', fecha: '17/11/2022' },
    { codigo: '315081 - OTRO HOTEL', categoria: 'Hoteles', fecha: '18/11/2022' },
  ]);

  const renderProductoNoConforme = ({ item }) => {
    return h(
      'a-list-item',
      {
        style: `
        display:flex;
        justify-content:space-between;
        align-items:center;
        background:#E9E9E9;
        border-radius:6px;
        padding:12px;
        margin-bottom:8px;
      `,
      },
      [
        // Información izquierda
        h('div', [
          h('strong', item.codigo),
          h('span', { style: 'font-size: 12px; color: #555;' }, item.categoria),
          h('span', { style: 'font-size: 12px; color: #555;' }, item.fecha),
          h('div', { style: 'font-size: 11px; color: black;' }, item.hora),
        ]),
        // Fecha + hora + acciones
        h('div', { style: 'display:flex; align-items:center; gap:12px;' }, [
          // Fecha y hora
          h('div', { style: 'text-align:right;' }, [
            h('div', { style: 'color:#eb5757; font-weight:600; font-size:12px;' }, item.fecha),
            h('div', { style: 'font-size:10px; color:#000' }, '10:44:02'), // hora en negro
          ]),
          // Ícono eliminar
          h(DeleteOutlined, {
            style: 'color:#eb5757; font-size:16px; cursor:pointer;',
            title: 'Eliminar',
            onClick: () => eliminarProducto(item),
          }),
          // Ícono ver
          h(EyeOutlined, {
            style: 'color:#eb5757; font-size:16px; cursor:pointer;',
            title: 'Ver detalles',
            onClick: () => verDetallesProducto(item),
          }),
        ]),
      ]
    );
  };

  // Estado para modal de detalle
  const detalleProductoVisible = ref(false);
  const productoSeleccionado = ref(null);

  const verDetallesProducto = (producto) => {
    productoSeleccionado.value = producto;
    detalleProductoVisible.value = true;
  };

  // Función para eliminar producto
  const eliminarProducto = (producto) => {
    productosNoConformes.value = productosNoConformes.value.filter((p) => p !== producto);
  };

  /* */

  const DEFAULT_PAGE_SIZE_OPTIONS = [10, 20];
  const INIT_CURRENT_PAGE_VALUE = 1;
  const INIT_PAGE_SIZE = 10;
  const currentPageValue = ref(INIT_CURRENT_PAGE_VALUE);
  const currentPageSize = ref(INIT_PAGE_SIZE);
  const total = ref(0); // Total de registros devueltos por backend
  const onChange = (page, perSize) => {
    console.log('Página seleccionada:', page, 'Tamaño de página:', perSize);
    const storedClientCode = Cookies.get('client_code_limatour');
    emit('onChange', {
      currentPage: page,
      perPage: perSize,
      filter: formFilter.filter.trim(),
      clientCode: storedClientCode,
      dateFrom: formFilter.dateRange.length ? formFilter.dateRange[0] : null,
      dateTo: formFilter.dateRange.length ? formFilter.dateRange[1] : null,
      searchOption: props.searchOption,
    });
  };
  const filtroPalabraClave = ref('');
  const filtroEstado = ref(null);
  const filtroTipoFecha = ref(null);
  const filtroRangoFechas = ref([]);
  const borrarFiltros = () => {
    filtroPalabraClave.value = '';
    filtroEstado.value = null;
    filtroTipoFecha.value = null;
    filtroRangoFechas.value = [];
  };
  const getEstadoStyle = (estado) => {
    const colorMap = {
      Cerrado: '#1ED790', // verde
      'En proceso': '#55A3FF', // azul
      Creado: '#FFC107', // mostaza
    };
    return {
      color: colorMap[estado] || '#d9d9d9',
      fontWeight: 'bold',
      fontSize: '14px',
      display: 'inline-flex',
      alignItems: 'center',
      gap: '6px',
    };
  };
</script>

<style scoped>
  .claim-form {
    /* Da el ancho para los contenedores de cada uno de los pasos 0,1 y 2 */
    margin: auto;
    max-width: 1380px;
  }
  .row-spacing {
    margin-bottom: 30px; /* espacio vertical entre filas de los campos a llenar */
  }
  .label-horizontal {
    font-weight: 600;
    min-width: 140px;
    display: inline-block;
  }
  .row-tight {
    margin-bottom: 12px;
  }
  ::v-deep(.ql-snow) {
    /* Eliminar bordes globales para comentario y respuesta*/
    border: none;
  }
  ::v-deep(.ql-toolbar) {
    /* Barra de herramientas para comentario y respuesta*/
    border: 1px solid #d9d9d9;
    border-radius: 6px 6px 0 0;
  }
  /* Área de contenido para comentario y respuesta*/
  ::v-deep(.ql-container) {
    border: 1px solid #d9d9d9;
    border-top: none;
    border-radius: 0 0 6px 6px;
  }
  /* Títulos de pasos personalizados */
  ::v-deep(.custom-steps .ant-steps-item-title) {
    font-weight: 500;
    font-size: 18px;
    color: rgba(0, 0, 0, 0.88);
  }
  ::v-deep(.custom-steps .ant-steps-item-description) {
    /* Descripciones más grandes de los pasos*/
    font-size: 13px;
    color: #999;
  }
  .steps-container {
    border: 1px solid #f0f0f0;
    border-radius: 8px;
    padding: 30px 24px 24px 24px; /* 30: para darle más espacio a la parte superior del contenedor y donde empieza las oraciones*/
    background-color: #fff;
    margin-top: 38px; /*para darle más espacio a la parte inferior de creación, en proceso y cierre*/
  }
  .row-buttons {
    margin-top: 8px; /* menos espacio arriba */
    margin-bottom: 7px; /* evita espacio extra abajo */
  }
  /* Cambia el ícono de calendario de los date-pickers a rojo */
  ::v-deep(.ant-picker .ant-picker-suffix .anticon-calendar) {
    color: #eb5757;
  }
  /* Aplica fondo gris claro solo a los inputs de resumen */
  ::v-deep(input[placeholder='Ingrese un resumen del comentario']),
  ::v-deep(input[placeholder='Ingrese un resumen de la respuesta']) {
    background-color: #e9e9e9 !important;
  }
  * {
    font-family: 'Montserrat', sans-serif;
  }
  ::v-deep(.custom-header > .ant-collapse-header) {
    background-color: #fafafa !important;
  }
  /* Aplica fondo completo a los headers de cada panel */
  ::v-deep(.custom-panel > .ant-collapse-header) {
    background-color: #fafafa !important;
    border-radius: 6px 6px 0 0;
    padding: 12px 16px !important;
  }
  /* borde inferior de cada uno de los 3 paneles colapsables */
  ::v-deep(.ant-collapse-content) {
    border-bottom: #f0f0f0 !important;
  }
  /* Quita el scoped si estás teniendo problemas */
  ::v-deep(.ant-table-tbody > tr > td) {
    border-bottom: none !important;
  }
  /* Cabecera sin fondo gris en la tabla de listado de reclamos */
  ::v-deep(.ant-table-thead > tr > th) {
    background-color: white !important;
    border-right: none !important;
    border-bottom: none !important; /* elimina línea inferior del header */
  }
  ::v-deep(.ant-table-thead > tr > th::before) {
    background-color: transparent !important;
    width: 0 !important;
    content: '' !important;
  }

  /* Aplica fondo gris claro a las filas del cuerpo de la tabla */
  ::v-deep(.ant-table-tbody > tr) {
    background-color: #fafafa;
    border-radius: 8px; /* esquinas redondeadas */
    overflow: hidden;
  }
  /* Elimina la línea superior entre filas del cuerpo */
  ::v-deep(.ant-table-tbody > tr > td) {
    border-top: none !important;
    border-bottom: 12px solid #ffffff !important; /* Línea blanca de 6px en la tabla de lista de reclamos */
    background-color: #fafafa !important;
  }
</style>
