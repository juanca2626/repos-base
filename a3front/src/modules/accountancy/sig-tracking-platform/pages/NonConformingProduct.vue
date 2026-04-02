<template>
  <div class="nonconforming-product-container">
    <a-card v-if="typeView === 'L'" class="filters-card" :bordered="false">
      <!-- Fila 1: Filtros básicos en línea -->
      <div class="filter-section">
        <div class="filter-group">
          <span class="filter-label">Filtrar por:</span>
          <a-select v-model:value="tipfec" placeholder="Tipo fecha" style="width: 150px">
            <a-select-option value="FECHA_APERTURA">F. APERTURA</a-select-option>
            <a-select-option value="FECING">F. PROBLEMA</a-select-option>
            <a-select-option value="CHECKIN">CHECK IN</a-select-option>
          </a-select>
        </div>

        <div class="filter-group">
          <span class="filter-label">Fechas:</span>
          <a-range-picker
            v-model:value="dateRange"
            format="DD-MM-YYYY"
            :placeholder="['Desde', 'Hasta']"
            style="width: 240px"
            @change="handleDateRangeChange"
          />
        </div>

        <div class="filter-group">
          <a-button type="primary" @click="search" :loading="loading">
            <SearchOutlined />
            Buscar
          </a-button>
          <a-button @click="eraser" style="margin-left: 8px" :disabled="loading">
            <ClearOutlined />
            Limpiar
          </a-button>
        </div>
      </div>

      <!-- Fila 2: Filtros adicionales -->
      <div class="filter-section">
        <div class="filter-group">
          <span class="filter-label">Campos para exportar:</span>
          <a-select
            v-model:value="field"
            placeholder="Seleccionar campos"
            style="width: 200px"
            @change="addFields"
            :allow-clear="true"
            :showSearch="true"
            :filter-option="true"
          >
            <a-select-option value="ALL">TODOS</a-select-option>
            <a-select-option v-for="(label, key) in sortedFields" :key="key" :value="key">
              {{ label }}
            </a-select-option>
          </a-select>
        </div>

        <div class="filter-group">
          <span class="filter-label">Tipo:</span>
          <a-select
            v-model:value="typeUser"
            placeholder="Seleccionar tipo"
            style="width: 200px"
            @change="filterCustomers"
          >
            <a-select-option v-for="c in allTypeservices" :key="c.codgru" :value="c.codgru">
              {{ c.descri }}
            </a-select-option>
            <a-select-option value="C">CLIENTE</a-select-option>
          </a-select>
        </div>
      </div>

      <!-- Fila 3: Filtro de cliente condicional -->
      <div class="filter-section">
        <div class="filter-group">
          <span class="filter-label">Estado:</span>
          <a-select
            v-model:value="state"
            placeholder="Seleccionar estado"
            style="width: 180px"
            @change="addStates"
          >
            <a-select-option value="ALL">TODOS</a-select-option>
            <a-select-option value="P-2">EN PROCESO</a-select-option>
            <a-select-option
              v-for="s in status"
              :key="`${s.codigo}-${s.tipniv}`"
              :value="`${s.codigo}-${s.tipniv}`"
            >
              {{ s.desc }}
            </a-select-option>
            <a-select-option value="X-0">ELIMINADO</a-select-option>
          </a-select>
        </div>

        <div class="filter-group" v-if="showCustomerFilter">
          <span class="filter-label">Filtrar cliente:</span>
          <a-select
            v-model:value="selectedCustomer"
            placeholder="Filtra por nombre o razón social"
            style="width: 230px"
            :allow-clear="true"
            :showSearch="true"
            :filter-option="true"
          >
            <a-select-option v-for="c in allCustomers" :key="c.codigo" :value="c.codigo">
              {{ c.razon }} {{ c.vouch1 }} {{ c.vouch2 }}
            </a-select-option>
          </a-select>
        </div>
      </div>

      <!-- Fila 4: Botones de exportación -->
      <div class="filter-section">
        <div class="filter-group">
          <span class="filter-label">Exportar:</span>
          <a-button
            type="primary"
            @click="exportExcel"
            :disabled="products.length === 0 || loading"
          >
            <DownloadOutlined />
            Excel
          </a-button>
          <a-button
            @click="exportExcelDesglosed"
            :disabled="products.length === 0 || loading"
            style="margin-left: 8px"
          >
            <DownloadOutlined />
            Excel Desglosado
          </a-button>
        </div>
      </div>

      <!-- Etiquetas de filtros aplicados -->
      <div class="tags-section" v-if="statesSelected.length > 0">
        <span class="tags-label">Estados filtrados:</span>
        <a-tag
          v-for="(s, k) in statesSelected"
          :key="k"
          closable
          color="blue"
          @close="removeStates(k)"
          class="filter-tag"
        >
          {{ s.desc }}
        </a-tag>
      </div>

      <div class="tags-section" v-if="fieldsSelected.length > 0">
        <span class="tags-label">Campos a exportar:</span>
        <a-tag
          v-for="(s, k) in fieldsSelected"
          :key="k"
          closable
          color="green"
          @close="removeFields(k)"
          class="filter-tag"
        >
          {{ s.desc }}
        </a-tag>
      </div>
    </a-card>

    <a-card v-if="typeView === 'L'" style="margin-top: 16px">
      <template #title>
        <a-row>
          <a-col :span="12">
            <a-button type="primary" danger @click="changeView('F')">
              Agregar Producto NC
            </a-button>
          </a-col>
          <a-col :span="12">
            <a-input
              v-model:value="filterProducts"
              placeholder="Búsqueda sobre el resultado..."
              style="width: 100%"
              @pressEnter="search"
            >
              <template #prefix><SearchOutlined /></template>
            </a-input>
          </a-col>
        </a-row>
      </template>

      <a-table
        :columns="columns"
        :data-source="filteredProducts"
        :loading="loading"
        :pagination="{ pageSize: 20 }"
        size="small"
        class="responsive-table"
        :scroll="{ x: tableScrollX }"
      >
        <template #bodyCell="{ column, record }">
          <template v-if="column.key === 'actions'">
            <a-dropdown :trigger="['click']">
              <a-button size="small">
                <SettingOutlined />
              </a-button>
              <template #overlay>
                <a-menu>
                  <a-menu-item
                    v-if="
                      record.descri_estado !== 'EN PROCESO' && record.descri_estado !== 'ELIMINADO'
                    "
                    @click="openProduct(record)"
                  >
                    <FolderOpenOutlined /> Re-abrir
                  </a-menu-item>
                  <a-menu-item @click="editProduct(record)">
                    <EyeOutlined /> Ver Detalle
                  </a-menu-item>
                  <a-menu-item
                    v-if="record.descri_estado === 'EN PROCESO'"
                    @click="resendMail(record)"
                  >
                    <MailOutlined /> Re-enviar correo de notificación
                  </a-menu-item>
                  <a-menu-item danger @click="deleteProduct(record)">
                    <DeleteOutlined /> Eliminar
                  </a-menu-item>
                </a-menu>
              </template>
            </a-dropdown>
          </template>

          <template v-else-if="column.key === 'comentario'">
            <a-button
              type="link"
              size="small"
              @click="showDetailModal(record)"
              style="background: #f3f3f3"
            >
              Ver
            </a-button>
          </template>

          <template v-else-if="column.key === 'fecha_cierre'">
            {{ record.descri_estado !== 'EN PROCESO' ? record.fecha_cierre : '' }}
          </template>

          <template v-else-if="column.dataIndex && record[column.dataIndex]">
            <div class="multiline-cell">
              {{ record[column.dataIndex] }}
            </div>
          </template>

          <template v-else> - </template>
        </template>
      </a-table>
    </a-card>

    <a-row v-if="typeView === 'F'" :gutter="16">
      <a-col :span="16">
        <a-card title="Producto No Conforme">
          <a-form :model="product" layout="vertical">
            <a-row :gutter="16">
              <a-col :span="12">
                <a-form-item :label="labels.nrofile">
                  <a-input
                    v-model:value="nrofile"
                    :disabled="blocked === 1"
                    @change="searchDetail"
                  />
                </a-form-item>
              </a-col>

              <a-col :span="12">
                <a-form-item :label="labels.qrReserva">
                  <a-input
                    v-model:value="product.operad"
                    :disabled="product.nroref || blocked === 1"
                  />
                </a-form-item>
              </a-col>

              <a-col :span="12">
                <a-form-item :label="labels.nombreReserva">
                  <a-input
                    v-model:value="product.descri"
                    :disabled="product.nroref || blocked === 1"
                  />
                </a-form-item>
              </a-col>

              <a-col :span="12">
                <a-form-item :label="labels.pasajero">
                  <a-select
                    v-if="blocked === 0"
                    v-model:value="selectedPassenger"
                    show-search
                    placeholder="Filtra por nombre"
                    :filter-option="false"
                    @search="filterPassengers"
                  >
                    <a-select-option v-for="p in allPassengers" :key="p.nrosec" :value="p.nrosec">
                      {{ p.nrodoc }} - {{ p.nombre }}
                    </a-select-option>
                  </a-select>
                  <a-input v-else v-model:value="product.nombre" disabled />
                </a-form-item>
              </a-col>

              <a-col :span="12">
                <a-form-item :label="labels.cliente">
                  <a-input
                    v-model:value="product.razon"
                    :disabled="product.nroref || blocked === 1"
                  />
                </a-form-item>
              </a-col>

              <a-col :span="12">
                <a-form-item :label="labels.area">
                  <a-input
                    v-model:value="product.codsec"
                    :disabled="product.nroref || blocked === 1"
                  />
                </a-form-item>
              </a-col>

              <a-col :span="12">
                <a-form-item :label="labels.tipoServicio">
                  <a-select
                    v-if="blocked === 0"
                    v-model:value="selectedTypeService"
                    placeholder="Selecciona un tipo"
                    @change="resetService"
                  >
                    <a-select-option v-for="t in allTypeservices" :key="t.codgru" :value="t.codgru">
                      {{ t.descri }}
                    </a-select-option>
                  </a-select>
                  <a-input v-else v-model:value="product.servicio" disabled />
                </a-form-item>
              </a-col>

              <a-col :span="12">
                <a-form-item :label="labels.proveedor">
                  <a-select
                    v-if="blocked === 0"
                    v-model:value="selectedService"
                    show-search
                    placeholder="Filtra por código o descripción"
                    :filter-option="false"
                    @change="searchFechasDisponibles"
                    @search="handleServiceSearch"
                  >
                    <a-select-option v-for="s in allServices" :key="s.prefac" :value="s.prefac">
                      {{ s.prefac }} - {{ s.proveedor }}
                    </a-select-option>
                  </a-select>
                  <a-input v-else v-model:value="product.proveedor_servicio" disabled />
                </a-form-item>
              </a-col>

              <a-col :span="12">
                <a-form-item :label="labels.fechaIncidente">
                  <a-select
                    v-if="fechasDisponibles.length > 0 && !manualDateMode"
                    v-model:value="fechaDisponible"
                    @click="showDateSelector"
                    @change="handleDateSelectChange"
                    placeholder="Seleccione de la lista"
                    style="width: 100%"
                  >
                    <a-select-option v-for="t in fechasDisponibles" :key="t.fecin" :value="t.fecin">
                      {{ t.fecin }}
                    </a-select-option>

                    <a-select-option
                      disabled
                      key="separator"
                      class="ant-select-item-option-disabled"
                    >
                      ──────────────
                    </a-select-option>
                    <a-select-option value="MANUAL_ENTRY" style="font-weight: bold; color: #a10e19">
                      📅 Otra fecha (Calendario)
                    </a-select-option>
                  </a-select>

                  <div v-else style="display: flex; gap: 8px">
                    <a-date-picker
                      v-model:value="fechaDisponibleCalendario"
                      format="DD-MM-YYYY"
                      placeholder="Seleccione fecha"
                      style="width: 100%"
                      @change="handleDatePickerChange"
                    />

                    <a-tooltip title="Volver a la lista de fechas sugeridas">
                      <a-button v-if="fechasDisponibles.length > 0" @click="returnToDateList">
                        <template #icon><OrderedListOutlined /></template>
                      </a-button>
                    </a-tooltip>
                  </div>
                </a-form-item>
              </a-col>

              <a-col :span="24" v-if="selectedTypeService === '0008'">
                <a-row :gutter="16">
                  <a-col :span="12">
                    <a-form-item label="CÓDIGO DE RESERVA DEL PROVEEDOR">
                      <a-input
                        v-model:value="codigoReservaProveedor"
                        :disabled="blocked === 1"
                        placeholder="Ingrese código de reserva"
                      />
                    </a-form-item>
                  </a-col>
                  <a-col :span="12">
                    <a-form-item label="NÚMERO DE FRECUENCIA">
                      <a-input
                        v-model:value="numeroFrecuencia"
                        :disabled="blocked === 1"
                        placeholder="Ingrese número de frecuencia"
                      />
                    </a-form-item>
                  </a-col>
                </a-row>
              </a-col>

              <a-col :span="24" v-if="canEditCategory">
                <a-form-item :label="labels.categoria">
                  <a-select
                    v-model:value="selectedType"
                    show-search
                    placeholder="Filtra por categoría"
                    :filter-option="filterOption"
                    @change="filterSubcategories"
                  >
                    <a-select-option
                      v-for="t in allTypes"
                      :key="t.codigo"
                      :value="t.codigo"
                      :label="t.descnc"
                    >
                      {{ t.descnc }}
                    </a-select-option>
                  </a-select>

                  <div
                    v-if="blocked === 1 && product.descri_tipo"
                    style="margin-top: 8px; color: #666"
                  >
                    SELECCIÓN ANTERIOR: <b>{{ product.descri_tipo }}</b>
                  </div>
                </a-form-item>
              </a-col>

              <a-col :span="24" v-if="selectedType && canEditCategory">
                <a-form-item :label="labels.subcategoria">
                  <a-alert
                    v-if="allSubcategories.length === 0"
                    message="Si no se muestra la opción de elegir subcategorías es porque la categoría escogida no tiene elementos agregados."
                    type="warning"
                    show-icon
                  />
                  <a-select
                    v-else
                    v-model:value="selectedSubcategory"
                    show-search
                    placeholder="Filtra por subcategoría"
                    @change="showFalta"
                  >
                    <a-select-option
                      v-for="(s, index) in allSubcategories"
                      :key="s.subcat"
                      :value="s.subcat"
                    >
                      {{ s.subcat }}
                    </a-select-option>
                  </a-select>
                </a-form-item>
              </a-col>

              <!--              <a-col :span="24" v-if="allStatus.length > 0 && view_status === 1">-->
              <a-col :span="24" v-if="allStatus.length > 0">
                <a-form-item :label="labels.estado">
                  <a-select
                    v-model:value="selectedStatus"
                    placeholder="Selecciona un estado"
                    @change="handleStatusChange"
                  >
                    <a-select-option
                      v-for="s in allStatus"
                      :key="`${s.codigo}-${s.tipniv}`"
                      :value="`${s.codigo}-${s.tipniv}`"
                    >
                      {{ s.desc }}
                    </a-select-option>
                  </a-select>
                </a-form-item>
              </a-col>

              <a-col :span="24" v-if="['LTM', 'CNS', 'SZY'].includes(userSelected)">
                <a-row :gutter="16">
                  <a-col :span="8" v-if="falta">
                    <a-form-item label="FALTA">
                      <a-input v-model:value="falta" disabled />
                    </a-form-item>
                  </a-col>
                  <a-col :span="16" v-if="sancionProveedor">
                    <a-form-item label="SANCIÓN AL PROVEEDOR">
                      <a-input v-model:value="sancionProveedor" disabled />
                    </a-form-item>
                  </a-col>
                </a-row>
              </a-col>

              <a-col
                :span="24"
                v-if="['LTM', 'CNS', 'SZY'].includes(userSelected)"
                style="padding-bottom: 24px"
              >
                <a-checkbox v-model:checked="infundado" @change="updateOptionProduct('INFUNDADO')">
                  INFUNDADO
                </a-checkbox>
                <a-checkbox v-model:checked="subjetivo" @change="updateOptionProduct('SUBJETIVO')">
                  SUBJETIVO
                </a-checkbox>
                <a-checkbox v-model:checked="asociado" @change="updateOptionProduct('ASOCIADO')">
                  ASOCIADO A RECLAMO
                </a-checkbox>
              </a-col>

              <a-col :span="24">
                <a-form-item :label="labels.comentario">
                  <!-- Versión editable cuando view_comment es 1 -->
                  <div v-if="view_comment === 1">
                    <CloudinaryQuillEditor
                      ref="commentEditorRef"
                      v-model:modelValue="comment"
                      :placeholder="'Ingrese el comentario...'"
                      @image-upload-start="handleImageUploadStart"
                      @image-upload-complete="handleImageUploadComplete"
                    />
                    <a-button type="primary" @click="disableCommentEdit" style="margin-top: 8px">
                      Finalizar edición
                    </a-button>

                    <!-- Indicador de upload -->
                    <div v-if="imageUploading" style="margin-top: 8px">
                      <a-alert message="Subiendo imágenes..." type="info" show-icon />
                    </div>
                  </div>

                  <!-- Versión de solo lectura cuando view_comment es 0 -->
                  <div v-else>
                    <a-space style="margin-bottom: 8px">
                      <a-typography-text type="secondary">
                        <a @click="enableCommentEdit">[EDITAR]</a>
                      </a-typography-text>
                    </a-space>
                    <div
                      v-if="comment"
                      style="
                        padding: 12px;
                        background: #f5f5f5;
                        border-radius: 4px;
                        border: 1px solid #d9d9d9;
                        min-height: 40px;
                      "
                    >
                      <div v-html="comment"></div>
                    </div>
                    <div
                      v-else
                      style="
                        padding: 12px;
                        background: #f5f5f5;
                        border-radius: 4px;
                        border: 1px dashed #d9d9d9;
                        color: #999;
                        text-align: center;
                      "
                    >
                      Sin comentario
                    </div>
                  </div>
                </a-form-item>
              </a-col>

              <a-col :span="24">
                <a-form-item :label="labels.respuesta">
                  <!-- Versión editable cuando view_response es 1 -->
                  <div v-if="view_response === 1">
                    <CloudinaryQuillEditor
                      ref="responseEditorRef"
                      v-model:modelValue="response"
                      :placeholder="'Ingrese la respuesta...'"
                      @image-upload-start="handleImageUploadStart"
                      @image-upload-complete="handleImageUploadComplete"
                    />
                    <a-button type="primary" @click="disableResponseEdit" style="margin-top: 8px">
                      Finalizar edición
                    </a-button>

                    <!-- Indicador de upload -->
                    <div v-if="imageUploading" style="margin-top: 8px">
                      <a-alert message="Subiendo imágenes..." type="info" show-icon />
                    </div>
                  </div>

                  <!-- Versión de solo lectura cuando view_response es 0 -->
                  <div v-else>
                    <a-space style="margin-bottom: 8px">
                      <a-typography-text type="secondary">
                        <a @click="enableResponseEdit" style="margin-right: 8px">[EDITAR]</a>
                      </a-typography-text>
                    </a-space>
                    <div
                      v-if="response"
                      style="
                        padding: 12px;
                        background: #f5f5f5;
                        border-radius: 4px;
                        border: 1px solid #d9d9d9;
                        min-height: 40px;
                      "
                    >
                      <div v-html="response"></div>
                    </div>
                    <div
                      v-else
                      style="
                        padding: 12px;
                        background: #f5f5f5;
                        border-radius: 4px;
                        border: 1px dashed #d9d9d9;
                        color: #999;
                        text-align: center;
                      "
                    >
                      Sin respuesta
                    </div>
                  </div>
                </a-form-item>
              </a-col>

              <transition name="fade">
                <div v-if="simulationResult" id="sim-report" class="simulation-report-card">
                  <div class="report-header">
                    <h3><span class="icon">🧪</span> Resultado de la Simulación</h3>
                    <button class="close-btn" @click="simulationResult = null">×</button>
                  </div>

                  <div class="report-body">
                    <div class="status-msg">
                      {{ simulationResult.message }}
                    </div>

                    <div class="email-section">
                      <h4>📧 Destinatarios (Para):</h4>
                      <div v-if="simulationResult.recipients?.to?.length" class="email-list">
                        <span
                          v-for="(email, index) in simulationResult.recipients.to"
                          :key="'to-' + index"
                          class="email-tag to"
                        >
                          {{ email }}
                        </span>
                      </div>
                      <div v-else class="empty-msg">No hay destinatarios principales.</div>
                    </div>

                    <div class="email-section" v-if="simulationResult.recipients?.bcc?.length">
                      <h4>🕵️ Copias Ocultas (BCC):</h4>
                      <div class="email-list">
                        <span
                          v-for="(email, index) in simulationResult.recipients.bcc"
                          :key="'bcc-' + index"
                          class="email-tag bcc"
                        >
                          {{ email }}
                        </span>
                      </div>
                    </div>

                    <div class="debug-section" v-if="simulationResult.debug_info">
                      <h4>⚙️ Lógica Detectada:</h4>
                      <ul>
                        <li>
                          <strong>Ciudad detectada:</strong>
                          {{ simulationResult.debug_info.ciudad_detectada }}
                        </li>
                        <li>
                          <strong>Tipo de Servicio:</strong>
                          {{ simulationResult.debug_info.tipser }}
                        </li>
                        <li>
                          <strong>Proveedor:</strong> {{ simulationResult.debug_info.proveedor }}
                        </li>
                      </ul>
                    </div>
                  </div>
                </div>
              </transition>

              <template v-if="['LTM', 'CNS', 'LSC', 'DEV'].includes(userSelected)">
                <a-col :span="12">
                  <a-form-item :label="labels.montoTotal">
                    <a-input-number
                      v-model:value="totalCompensacion"
                      :min="0"
                      style="width: 100%"
                      disabled
                    />
                  </a-form-item>
                </a-col>

                <a-col :span="12">
                  <a-form-item :label="labels.asumidoPor">
                    <a-select v-model:value="asumidoPor" placeholder="Seleccione">
                      <a-select-option value="">----</a-select-option>
                      <a-select-option value="0001">RENTABILIDAD DEL FILE</a-select-option>
                      <a-select-option value="0002">ESPECIALISTA</a-select-option>
                      <a-select-option value="0003">OPE LIMA</a-select-option>
                      <a-select-option value="0004">OPE CUSCO</a-select-option>
                      <a-select-option value="0005">OPE AREQUIPA</a-select-option>
                      <a-select-option value="0006">OPE PUNO</a-select-option>
                      <a-select-option value="0007">PROVEEDOR</a-select-option>
                      <a-select-option value="0008">OTROS</a-select-option>
                      <a-select-option value="0009">GUIA PLANTA</a-select-option>
                      <a-select-option value="0010">GUIA FREELANCE</a-select-option>
                      <a-select-option value="0011">REPS</a-select-option>
                    </a-select>
                  </a-form-item>
                </a-col>

                <a-col :span="12">
                  <a-form-item :label="labels.montoCompensacion">
                    <a-input-number
                      v-model:value="montoCompensacion"
                      :min="0"
                      style="width: 100%"
                      @change="updateTotalAmount"
                    />
                  </a-form-item>
                </a-col>

                <a-col :span="12">
                  <a-form-item :label="labels.observacionesCompensacion">
                    <a-input v-model:value="observacionesCompensacion" />
                  </a-form-item>
                </a-col>

                <a-col :span="12">
                  <a-form-item :label="labels.montoReembolso">
                    <a-input-number
                      v-model:value="montoReembolso"
                      :min="0"
                      style="width: 100%"
                      @change="updateTotalAmount"
                    />
                  </a-form-item>
                </a-col>

                <a-col :span="12">
                  <a-form-item :label="labels.observacionesReembolso">
                    <a-input v-model:value="observacionesReembolso" />
                  </a-form-item>
                </a-col>
              </template>

              <a-col :span="24" v-if="flag_autorizado && product.codref">
                <a-row :gutter="16">
                  <a-col :span="12">
                    <a-form-item label="ACCIÓN / SANCIÓN">
                      <a-input
                        v-model:value="accionSancion"
                        @blur="updateAction"
                        placeholder="Ingrese acción/sanción"
                      />
                    </a-form-item>
                  </a-col>
                  <a-col :span="12">
                    <a-form-item label="AUTORIZADO POR">
                      <a-input
                        v-model:value="autorizado"
                        @blur="updateAutorized"
                        placeholder="Ingrese autorizado por"
                      />
                    </a-form-item>
                  </a-col>
                </a-row>
              </a-col>

              <a-col :span="24" v-if="historial.length > 0">
                <a-divider />
                <h4>HISTORIAL DE RESPUESTAS</h4>
                <div class="historial-container">
                  <div v-for="(item, index) in historial" :key="index" class="historial-card">
                    <div class="historial-header">
                      <span class="historial-number">Respuesta {{ index + 1 }}</span>
                      <a-tag color="orange">Historial</a-tag>
                    </div>
                    <div class="historial-content" v-html="item.texto"></div>
                  </div>
                </div>
              </a-col>

              <a-col :span="24" v-if="tracings.length > 0">
                <a-divider />
                <a-alert
                  v-for="tracing in tracings"
                  :key="tracing.nrolin"
                  :message="`COMENTARIO #${tracing.nrolin}`"
                  :description="tracing.coment"
                  type="warning"
                  style="margin-bottom: 8px"
                />
              </a-col>
            </a-row>
          </a-form>
        </a-card>
      </a-col>

      <a-col :span="8" style="position: fixed; right: 5%">
        <a-card title="">
          <a-space direction="vertical" style="width: 100%">
            <a-button block @click="changeView('L')"> Ir al Listado </a-button>

            <a-button
              v-if="product.codref === 0 && !cmd_simular"
              type="primary"
              danger
              block
              @click="save"
              :loading="loading"
            >
              Guardar
            </a-button>

            <a-button
              v-if="product.codref === 0 && cmd_simular"
              type="primary"
              danger
              block
              @click="saveSimulator"
              :loading="loading"
            >
              ✨ Simular
            </a-button>

            <a-button
              v-if="
                product.codref > 0 &&
                ((editable === 1 && product.descri_estado !== 'CERRADO') ||
                  ['LTM', 'CNS', 'SZY'].includes(userSelected))
              "
              type="primary"
              danger
              block
              :loading="loading"
              @click="update"
            >
              Guardar
            </a-button>

            <a-button
              v-if="product.codref > 0 && view_response === 1"
              type="primary"
              danger
              block
              :disabled="loading"
              @click="close"
            >
              Cerrar
            </a-button>

            <a-button
              v-if="product.codref > 0 && product.descri_estado !== 'ELIMINADO'"
              danger
              block
              :disabled="loading"
              @click="deleteProduct(product)"
            >
              Eliminar
            </a-button>

            <a-checkbox
              v-if="product.codref > 0 && ['LTM', 'CNS', 'SZY', 'DEV'].includes(userSelected)"
              v-model:checked="flagNotify"
            >
              IGNORAR LAS NOTIFICACIONES POR CORREO
            </a-checkbox>
          </a-space>

          <!-- SECCIÓN DE SEGUIMIENTOS RELACIONADOS - FALTABA -->
          <div
            v-if="related.length > 0"
            style="margin-top: 20px; max-height: 260px; overflow-y: scroll"
          >
            <p style="margin-bottom: 10px">
              <ExclamationCircleOutlined style="color: #faad14" />
              <strong style="margin-left: 8px">FILE CON SEGUIMIENTOS RELACIONADOS:</strong>
            </p>
            <div
              v-for="item in related"
              :key="item.nrocom"
              style="margin-bottom: 12px; padding: 8px; background: #fffbf0; border-radius: 4px"
            >
              <div>
                <strong>N° {{ item.nrocom }}</strong> | Proveedor: {{ item.proveedor_servicio }}
              </div>
              <div>Motivo: {{ item.descri_tipo }}</div>
              <div>Compensación: US$. {{ item.montot }}</div>
              <div>Estado: {{ item.descri_estado }}</div>
            </div>
          </div>

          <!-- SECCIÓN DE RECLAMOS RELACIONADOS - FALTABA -->
          <div v-if="claimsRelated.length > 0" style="margin-top: 20px">
            <p style="margin-bottom: 10px">
              <ExclamationCircleOutlined style="color: #ff4d4f" />
              <strong style="margin-left: 8px">FILE CON RECLAMOS RELACIONADOS:</strong>
            </p>
            <div
              v-for="item in claimsRelated"
              :key="item.nrocom"
              style="margin-bottom: 12px; padding: 8px; background: #fff2f0; border-radius: 4px"
            >
              <div>
                <strong>N° {{ item.nrocom }}</strong>
              </div>
              <div>Estado: {{ item.estado_reclamo === 'CE' ? 'CERRADO' : 'PENDIENTE' }}</div>
              <div>
                Monto total devolución: US$. {{ item.tipo_monto ? item.comree : item.montot }}
              </div>
            </div>
          </div>
        </a-card>
      </a-col>
    </a-row>

    <a-modal
      v-model:visible="detailModalVisible"
      :title="'NRO DE SEGUIMIENTO: ' + selectedProduct?.nrocom"
      width="800px"
      :footer="null"
    >
      <a-descriptions bordered :column="1">
        <a-descriptions-item label="Comentario">
          <div v-html="selectedProduct?.coment"></div>
        </a-descriptions-item>
        <a-descriptions-item label="Respuesta">
          <div v-html="selectedProduct?.respue"></div>
        </a-descriptions-item>
      </a-descriptions>
    </a-modal>
  </div>
</template>

<script setup>
  import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
  import { debounce } from 'lodash-es';
  import CloudinaryQuillEditor from '../../components/CloudinaryQuillEditor.vue';
  import { message, Modal } from 'ant-design-vue';
  import {
    SearchOutlined,
    ClearOutlined,
    DownloadOutlined,
    SettingOutlined,
    FolderOpenOutlined,
    EyeOutlined,
    MailOutlined,
    DeleteOutlined,
    ExclamationCircleOutlined,
    OrderedListOutlined,
  } from '@ant-design/icons-vue';
  import moment from 'moment';
  import dayjs from 'dayjs';
  import { nonConformingProductsApi, exportsApi } from '../../services/api';

  const manualDateMode = ref(false);

  // QuillEditor
  const commentEditorRef = ref(null);
  const responseEditorRef = ref(null);
  // Estados para el upload de imágenes
  const imageUploading = ref(false);
  const allFilesCompleted = ref(true);

  const tableScrollX = ref(null);
  const updateTableScroll = () => {
    const screenWidth = window.innerWidth;

    if (screenWidth >= 1600) {
      // Pantallas grandes - sin scroll
      tableScrollX.value = null;
    } else if (screenWidth >= 1200) {
      // Pantallas medianas - scroll mínimo
      tableScrollX.value = 1400;
    } else if (screenWidth >= 992) {
      // Tablets grandes
      tableScrollX.value = 1200;
    } else if (screenWidth >= 768) {
      // Tablets
      tableScrollX.value = 1000;
    } else {
      // Móviles - scroll necesario
      tableScrollX.value = 800;
    }
  };

  const labels = {
    nrofile: 'NÚMERO DE FILE',
    qrReserva: 'QR RESERVA',
    nombreReserva: 'NOMBRE RESERVA',
    pasajero: 'PASAJERO',
    cliente: 'CLIENTE',
    area: 'ÁREA',
    tipoServicio: 'TIPO DE SERVICIO',
    proveedor: 'PROVEEDOR',
    fechaIncidente: 'FECHA DEL INCIDENTE',
    categoria: 'CATEGORÍA',
    subcategoria: 'SUBCATEGORÍA',
    estado: 'ESTADO',
    comentario: 'COMENTARIO',
    respuesta: 'RESPUESTA',
    montoTotal: 'MONTO TOTAL',
    asumidoPor: 'ASUMIDO POR',
    montoCompensacion: 'MONTO COMPENSACIÓN',
    observacionesCompensacion: 'OBSERVACIONES COMPENSACIÓN',
    montoReembolso: 'MONTO REEMBOLSO',
    observacionesReembolso: 'OBSERVACIONES REEMBOLSO',
  };

  // Estado de la vista
  const typeView = ref('L'); // 'L' = Lista, 'F' = Formulario
  const loading = ref(false);

  // Filtros
  const tipfec = ref('FECHA_APERTURA');
  const dateRange = ref([dayjs(), dayjs().add(1, 'day')]);
  const typeUser = ref('C');
  const state = ref('ALL');
  const field = ref('ALL');

  const sortedFields = computed(() => {
    const sorted = {};
    Object.keys(fields.value)
      .sort((a, b) => fields.value[a].localeCompare(fields.value[b]))
      .forEach((key) => {
        sorted[key] = fields.value[key];
      });
    return sorted;
  });

  const selectedCustomer = ref(null);
  const filterProducts = ref(null);

  // Datos
  const products = ref([]);
  const allTypeservices = ref([]);
  const allCustomers = ref([]);
  const allPassengers = ref([]);
  const allServices = ref([]);
  const allTypes = ref([]);
  const allSubcategories = ref([]);
  const allStatus = ref([]);
  const status = ref([]);
  const fields = ref({});
  const statesSelected = ref([]);
  const fieldsSelected = ref([]);

  // Campos adicionales del formulario
  const codigoReservaProveedor = ref('');
  const numeroFrecuencia = ref('');

  const accionSancion = ref('');
  const autorizado = ref('');
  const sancionProveedor = ref('');

  // Producto actual
  const product = ref({
    codref: 0,
    nroref: '',
    operad: '',
    descri: '',
    nombre: '',
    razon: '',
    codsec: '',
    servicio: '',
    proveedor_servicio: '',
    descri_estado: '',
  });

  const nrofile = ref('');
  const blocked = ref(0);
  const editable = ref(1);

  // Selecciones
  const selectedPassenger = ref('');
  const selectedTypeService = ref('');
  const selectedService = ref('');
  const selectedType = ref('');
  const selectedSubcategory = ref('');
  const selectedStatus = ref('');
  const fechaDisponible = ref('');

  // Formulario
  const comment = ref('');
  const response = ref('');
  const prevResponse = ref('');
  const infundado = ref(false);
  const subjetivo = ref(false);
  const asociado = ref(false);
  const flagNotify = ref(false);

  const simulationResult = ref(null);
  const cmd_simular = ref(false);
  watch(comment, (newValue) => {
    const textContent = newValue.replace(/<[^>]*>/g, '').trim();
    if (textContent === '!simular') {
      cmd_simular.value = true;
      console.log('Comando activado');
    }
  });

  // Compensación
  const totalCompensacion = ref(0);
  const montoCompensacion = ref(0);
  const montoReembolso = ref(0);
  const observacionesCompensacion = ref('');
  const observacionesReembolso = ref('');
  const asumidoPor = ref('');

  // Historial y seguimientos
  const historial = ref([]);
  const tracings = ref([]);
  const related = ref([]);
  const claimsRelated = ref([]);

  // Usuario
  const userSelected = ref('');
  let user_ = ref('');

  // Modal
  const detailModalVisible = ref(false);
  const selectedProduct = ref(null);

  const columns = [
    {
      title: 'N° SEG',
      dataIndex: 'nrocom',
      key: 'nrocom',
      width: 70,
      align: 'center',
      fixed: 'left',
    },
    {
      title: 'N° FILE',
      dataIndex: 'nroref',
      key: 'nroref',
      width: 80,
      align: 'center',
    },
    {
      title: 'NOMBRE FILE',
      dataIndex: 'nombre_file',
      key: 'nombre_file',
      width: 150,
      minWidth: 120,
    },
    {
      title: 'USU.',
      dataIndex: 'codusu',
      key: 'codusu',
      width: 60,
      align: 'center',
    },
    {
      title: 'QR RES.',
      dataIndex: 'operad',
      key: 'operad',
      width: 90,
      align: 'center',
    },
    {
      title: 'QR VTA.',
      dataIndex: 'codope',
      key: 'codope',
      width: 90,
      align: 'center',
    },
    {
      title: 'ÁREA',
      dataIndex: 'area',
      key: 'area',
      width: 80,
      align: 'center',
    },
    {
      title: 'SERVICIO',
      dataIndex: 'servicio',
      key: 'servicio',
      width: 130,
      minWidth: 100,
    },
    {
      title: 'PROVEEDOR',
      dataIndex: 'proveedor_servicio',
      key: 'proveedor_servicio',
      width: 160,
      minWidth: 120,
    },
    {
      title: 'F. PROBLEMA',
      dataIndex: 'fecing',
      key: 'fecing',
      width: 100,
      align: 'center',
    },
    {
      title: 'F. APERTURA',
      dataIndex: 'fecha_apertura',
      key: 'fecha_apertura',
      width: 100,
      align: 'center',
    },
    {
      title: 'F. CIERRE',
      key: 'fecha_cierre',
      align: 'center',
      width: 90,
      customRender: ({ text, record }) => {
        return record.descri_estado !== 'EN PROCESO' ? text : '';
      },
    },
    {
      title: 'TIPO',
      dataIndex: 'descri_tipo',
      key: 'descri_tipo',
      width: 120,
      minWidth: 100,
    },
    {
      title: 'COMENT. / RPTA.',
      key: 'comentario',
      width: 100,
      align: 'center',
    },
    {
      title: 'ESTADO',
      dataIndex: 'descri_estado',
      key: 'descri_estado',
      width: 100,
      align: 'center',
    },
    {
      title: 'Acciones',
      key: 'actions',
      width: 80,
      align: 'center',
      fixed: 'right',
    },
  ];

  const checkAllImagesUploaded = async () => {
    if (commentEditorRef.value) {
      const commentFiles = commentEditorRef.value.getAllFiles();
      if (commentFiles.total !== commentFiles.completed) return false;
    }

    if (responseEditorRef.value) {
      const responseFiles = responseEditorRef.value.getAllFiles();
      if (responseFiles.total !== responseFiles.completed) return false;
    }

    return true;
  };

  const handleDateRangeChange = (dates) => {
    if (dates && dates.length === 2) {
      dateRange.value = dates;
    }
  };

  const filterOption = (input, option) => {
    return option.label.toLowerCase().includes(input.toLowerCase());
  };

  // Productos filtrados
  const filteredProducts = computed(() => {
    // if (!filterProducts.value) return products.value;
    //
    // const searchTerm = filterProducts.value.toLowerCase();
    // return products.value.filter((p) => {
    //   return Object.values(p).some((val) => String(val).toLowerCase().includes(searchTerm));
    // });
    return products.value;
  });

  // Variables adicionales necesarias
  const fechasDisponibles = ref([]);
  const flagFechasDisponibles = ref(0);
  const fechaDisponibleCalendario = ref(null);

  const flag_autorizado = ref(false);
  const falta = ref('');

  // Nuevos metodos
  const searchHistorial = async (nrocom) => {
    try {
      const response = await nonConformingProductsApi.get('/search-historial', {
        params: { nrocom: nrocom },
      });
      historial.value = response.data.data || [];
    } catch (error) {
      console.error('Error al buscar historial:', error);
      historial.value = [];
    }
  };

  const searchTracings = async (nrocom) => {
    try {
      const response = await nonConformingProductsApi.get('/search-tracings', {
        params: { nrocom: nrocom },
      });
      tracings.value = response.data.data || [];
    } catch (error) {
      console.error('Error al buscar seguimientos:', error);
      tracings.value = [];
    }
  };

  const searchRelated = async () => {
    try {
      const params = {
        nrofile: nrofile.value,
      };

      // Solo agregar nrocom si existe y es mayor a 0 (para excluir el actual)
      if (product.value.nrocom && product.value.nrocom > 0) {
        params.nrocom = product.value.nrocom;
      }

      const response = await nonConformingProductsApi.get('/search-related', { params });
      related.value = response.data.data.results || [];
    } catch (error) {
      console.error('Error al buscar relacionados:', error);
      related.value = [];
    }
  };

  const searchClaimsRelated = async () => {
    try {
      const response = await nonConformingProductsApi.get('/search-claims-related', {
        params: {
          nrofile: nrofile.value,
        },
      });
      claimsRelated.value = response.data.data.results || [];
    } catch (error) {
      console.error('Error al buscar reclamos relacionados:', error);
      claimsRelated.value = [];
    }
  };

  const showFalta = async (subcategoryIndex) => {
    if (allSubcategories.value[subcategoryIndex]) {
      const subcat = allSubcategories.value[subcategoryIndex];
      falta.value = subcat.descri_falta || '';
      if (falta.value) {
        await searchSancionProveedor(falta.value);
      }
    }
  };

  const updateAutorized = async () => {
    try {
      await nonConformingProductsApi.put('/update-authorized', {
        codref: product.value.codref,
        autorizado: autorizado.value,
      });
      // message.success('Autorizado por actualizado correctamente');
      console.log('Autorizado por actualizado correctamente');
    } catch (error) {
      // message.error('Error al actualizar autorización: ' + error.message);
      console.log('Error al actualizar autorización: ' + error.message);
    }
  };

  const updateAction = async () => {
    try {
      await nonConformingProductsApi.put('/update-action', {
        codref: product.value.codref,
        accion: accionSancion.value,
      });
      // message.success('Acción/sanción actualizada correctamente');
      console.log('Acción/sanción actualizada correctamente');
    } catch (error) {
      // message.error('Error al actualizar acción: ' + error.message);
      console.log('Error al actualizar acción: ' + error.message);
    }
  };

  const searchSancionProveedor = async (faltaText) => {
    try {
      const response = await nonConformingProductsApi.get('/search-sancion-proveedor', {
        params: {
          nrocom: product.value.nrocom,
          codsvs: selectedTypeService.value,
          prefac: selectedService.value,
          falta: faltaText,
        },
      });
      sancionProveedor.value = response.data.data.sancion;
    } catch (error) {
      console.error('Error al buscar sanción:', error);
    }
  };

  // Métodos
  const search = async () => {
    loading.value = true;

    // Validación de fechas CON CONVERSIÓN
    const date1 = dateRange.value[0] ? dateRange.value[0].format('YYYY-MM-DD') : '';
    const date2 = dateRange.value[1] ? dateRange.value[1].format('YYYY-MM-DD') : '';

    if (date1 && date2) {
      const dayjsDate1 = dayjs(date1, 'YYYY-MM-DD');
      const dayjsDate2 = dayjs(date2, 'YYYY-MM-DD');

      if (dayjsDate1.isAfter(dayjsDate2)) {
        message.warning('Fechas incorrectas');
        loading.value = false;
        return;
      }
    }

    try {
      const params = {
        tipfec: tipfec.value,
        fecini: date1,
        fecfin: date2,
        state: statesSelected.value.length > 0 ? statesSelected.value : 'ALL',
        nrocom: nrofile.value,
        tipser: typeUser.value || '',
        customer: selectedCustomer.value || '',
        filter: filterProducts.value || '',
      };
      const response = await nonConformingProductsApi.get('/search', {
        params: params,
      });

      const returns = response.data.data;

      products.value = returns.results;
      status.value = returns.status;
      fields.value = returns.fields;

      if (returns.results.length === 0) {
        loading.value = false;
        return;
      }

      // Procesar cada producto como en el original
      let processedCount = 0;

      for (let i = 0; i < returns.results.length; i++) {
        const item = returns.results[i];

        // 1. Establecer estado "EN PROCESO" para P-2
        if (item.estado + '-' + item.tipniv === 'P-2') {
          products.value[i].descri_estado = 'EN PROCESO';
        }

        // Actualizar variables globales (como en el original)
        autorizado.value = products.value[i].autorizado;
        accionSancion.value = products.value[i].accion_sancion;
        sancionProveedor.value = products.value[i].sancion_proveedor;

        processedCount++;

        // Último elemento procesado - finalizar loading
        if (processedCount === returns.results.length) {
          loading.value = false;
        }
      }
    } catch (error) {
      console.error('Error en búsqueda principal:', error);
      message.error('Error al buscar productos: ' + (error.response?.data?.error || error.message));
      loading.value = false;
    }
  };

  const eraser = () => {
    dateRange.value = [dayjs().subtract(1, 'day'), dayjs()];
    typeUser.value = '';
    state.value = 'ALL';
    selectedCustomer.value = '';
    filterProducts.value = '';
    statesSelected.value = [];
    fieldsSelected.value = [];
    products.value = [];
  };

  const changeView = (view, fromEdit = false) => {
    typeView.value = view;
    if (view === 'F') {
      if (!fromEdit) {
        resetForm(); // Solo resetear cuando es AGREGAR nuevo
        blocked.value = 0;
      }
      editable.value = 1;
    } else if (view === 'L') {
      flag_autorizado.value = false;
      resetFormFields();
      setTimeout(() => search(), 100);
    }
  };

  const resetFormFields = () => {
    // Limpiar solo campos del formulario, NO los filtros de búsqueda
    product.value = {
      codref: 0,
      nroref: '',
      operad: '',
      descri: '',
      nombre: '',
      razon: '',
      codsec: '',
      servicio: '',
      proveedor_servicio: '',
      descri_estado: '',
    };

    nrofile.value = '';
    asumidoPor.value = '';
    montoCompensacion.value = 0;
    montoReembolso.value = 0;
    totalCompensacion.value = 0;
    observacionesCompensacion.value = '';
    observacionesReembolso.value = '';

    // Limpiar selecciones del formulario
    selectedPassenger.value = '';
    selectedTypeService.value = '';
    selectedService.value = '';
    selectedType.value = '';
    selectedSubcategory.value = '';
    selectedStatus.value = '';
    fechaDisponible.value = null;

    // Limpiar campos de entrada del formulario
    comment.value = '';
    response.value = '';
    prevResponse.value = '';

    view_comment.value = 1; // Comentario editable por defecto
    view_response.value = 0;

    infundado.value = false;
    subjetivo.value = false;
    asociado.value = false;
    flagNotify.value = false;

    // Limpiar campos adicionales
    codigoReservaProveedor.value = '';
    numeroFrecuencia.value = '';
    falta.value = '';
    sancionProveedor.value = '';
    accionSancion.value = '';
    autorizado.value = '';

    // Limpiar datos relacionados
    historial.value = [];
    tracings.value = [];
    related.value = [];
    claimsRelated.value = [];

    blocked.value = 0;
  };

  const resetForm = () => {
    nrofile.value = '';
    filterProducts.value = '';
    typeUser.value = '';
    state.value = 'ALL';
    selectedCustomer.value = '';
    statesSelected.value = [];
    fieldsSelected.value = [];
    products.value = [];

    // Limpiar campos del formulario
    resetFormFields();

    blocked.value = 0;
    editable.value = 1;
  };

  const showCustomerFilter = computed(() => {
    return typeUser.value === 'C' || allTypeservices.value.some((s) => s.codgru === typeUser.value);
  });

  const canEditCategory = computed(() => {
    return !product.value.codref || ['LTM', 'CNS', 'SZY'].includes(userSelected.value);
  });

  const searchDetail = debounce(async () => {
    if (!nrofile.value) return;

    try {
      const response = await nonConformingProductsApi.get(`/search-detail/${nrofile.value}`);
      product.value = { ...product.value, ...response.data.data };
      await searchRelated();
      await searchClaimsRelated();
      filterPassengers();
    } catch (error) {
      message.error('Error al buscar File, no encontrado');
      console.log(error.message);
    }
  }, 350);

  const filterCustomers = async (searchText) => {
    selectedCustomer.value = '';
    try {
      const response = await nonConformingProductsApi.get('/search-users', {
        params: { tipo_servicio: typeUser.value, term: searchText },
      });
      allCustomers.value = response.data.data;
    } catch (error) {
      message.error('Error al buscar clientes: ' + error.message);
    }
  };

  const filterPassengers = async (searchText) => {
    try {
      const response = await nonConformingProductsApi.get('/search-passengers', {
        params: { nroref: nrofile.value, term: searchText },
      });
      allPassengers.value = response.data.data;
    } catch (error) {
      message.error('Error al buscar pasajeros: ' + error.message);
    }
  };

  const filterServices = async (searchText) => {
    try {
      const response = await nonConformingProductsApi.get('/search-services', {
        params: {
          nroref: nrofile.value,
          term: searchText,
          tipo_servicio: selectedTypeService.value,
          module: '',
        },
      });
      allServices.value = response.data.data;
    } catch (error) {
      message.error('Error al buscar servicios: ' + error.message);
    }
  };

  let searchTimeout = null;
  const handleServiceSearch = (val) => {
    if (!val || val.trim() === '') return;
    if (searchTimeout) {
      clearTimeout(searchTimeout);
    }
    searchTimeout = setTimeout(() => {
      filterServices(val);
    }, 400);
  };

  const filterTypes = async () => {
    try {
      const response = await nonConformingProductsApi.get('/search-types', {
        params: { tipo_servicio: selectedTypeService.value, usuario: user_ },
      });
      allTypes.value = response.data.data;
    } catch (error) {
      message.error('Error al buscar tipos: ' + error.message);
    }
  };

  const filterSubcategories = async () => {
    try {
      const response = await nonConformingProductsApi.get('/search-sub-categories', {
        params: { service: selectedTypeService.value, category: selectedType.value },
      });
      allSubcategories.value = response.data.data;
    } catch (error) {
      message.error('Error al buscar tipos: ' + error.message);
    }
  };

  const searchFechasDisponibles = async (searchText) => {
    if (!searchText || searchText.length < 2) return;

    fechaDisponible.value = '';
    manualDateMode.value = false;

    let params_ = {
      nrofile: nrofile.value,
      prefac: editable.value ? searchText : selectedService.value,
    };

    try {
      const response = await nonConformingProductsApi.get('/search-fechas-disponibles', {
        params: params_,
      });

      fechasDisponibles.value = response.data.data || [];
      flagFechasDisponibles.value = fechasDisponibles.value.length > 0 ? 1 : 0;
      // Si no hay fechas disponibles, resetear la fecha seleccionada
      if (fechasDisponibles.value.length === 0) {
        fechaDisponible.value = '';
        manualDateMode.value = true;
      }
    } catch (error) {
      message.error('Error al FechasDisponibles: ' + error.message);
      fechasDisponibles.value = [];
      flagFechasDisponibles.value = 0;
      manualDateMode.value = true;
    }
  };

  const handleDateSelectChange = (value) => {
    if (value === 'MANUAL_ENTRY') {
      manualDateMode.value = true;
      fechaDisponible.value = ''; // Limpiamos la selección del select
      // Opcional: Podrías inicializar el calendario con la fecha de hoy
      // fechaDisponibleCalendario.value = dayjs();
    } else {
      fechaDisponible.value = value;
    }
  };

  const returnToDateList = () => {
    manualDateMode.value = false;
    fechaDisponible.value = ''; // Limpiar para obligar a seleccionar de nuevo
  };

  const resetService = () => {
    selectedService.value = '';
    allServices.value = [];
    flagFechasDisponibles.value = 0;
    fechasDisponibles.value = [];
    fechaDisponible.value = '';
    fechaDisponibleCalendario.value = null;
    filterServices();
    filterTypes();
  };

  const showDateSelector = () => {
    flagFechasDisponibles.value = 1;
  };

  const handleDatePickerChange = (date, dateString) => {
    fechaDisponibleCalendario.value = date;
    fechaDisponible.value = dateString;
  };

  const view_response = ref(0);
  const view_comment = ref(1);

  const enableCommentEdit = () => {
    view_comment.value = 1;
  };

  const disableCommentEdit = () => {
    view_comment.value = 0;
  };

  const enableResponseEdit = () => {
    view_response.value = 1;
  };

  const disableResponseEdit = () => {
    view_response.value = 0;
  };

  const view_status = ref(1);

  // Método para cambiar visibilidad según estado
  const changeStatus = () => {
    if (!selectedStatus.value) return;

    view_status.value = 0;

    // Buscar el estado seleccionado en allStatus
    const statusObj = allStatus.value.find(
      (s) => `${s.codigo}-${s.tipniv}` === selectedStatus.value
    );

    if (!statusObj) return;

    if (statusObj.desc === 'CERRADO' || statusObj.desc === 'ELIMINADO') {
      view_response.value = 0; // OCULTO
    } else {
      if (statusObj.desc === 'EN PROCESO') {
        view_status.value = 1;
      }
      view_response.value = 1; // VISIBLE
    }
  };

  // Actualizar el método cuando cambia el estado
  const handleStatusChange = () => {
    changeStatus();
  };

  const updateTotalAmount = () => {
    totalCompensacion.value = (montoCompensacion.value || 0) + (montoReembolso.value || 0);
  };

  const formatDateForAPI = (dateValue) => {
    if (!dateValue) return '';

    // Si es un string en formato DD-MM-YYYY, dejarlo igual
    if (typeof dateValue === 'string' && dateValue.match(/^\d{2}-\d{2}-\d{4}$/)) {
      return dateValue;
    }

    // Si es un objeto DayJS (del datepicker)
    if (dateValue && dateValue.format) {
      return dateValue.format('DD-MM-YYYY');
    }

    // Si es un Date object o ISO string
    if (dateValue instanceof Date || (typeof dateValue === 'string' && dateValue.includes('T'))) {
      const date = new Date(dateValue);
      const day = date.getDate().toString().padStart(2, '0');
      const month = (date.getMonth() + 1).toString().padStart(2, '0');
      const year = date.getFullYear();
      return `${day}-${month}-${year}`;
    }

    return '';
  };

  const save = async () => {
    loading.value = true;

    try {
      // Verificar que todas las imágenes estén subidas
      const allUploaded = await checkAllImagesUploaded();
      if (!allUploaded) {
        message.warning('Espere a que terminen de subir todas las imágenes');
        loading.value = false;
        return;
      }

      // Limitar longitud del texto (como en Angular)
      if (comment.value && comment.value.length >= 30000) {
        message.error(
          'No está permitido la cantidad de texto ingresado en el comentario. Por favor, trate de acortar el contenido para continuar.'
        );
        loading.value = false;
        return;
      }

      if (response.value && response.value.length >= 30000) {
        message.error(
          'No está permitido la cantidad de texto ingresado en la respuesta. Por favor, trate de acortar el contenido para continuar.'
        );
        loading.value = false;
        return;
      }

      const fechaIncidente =
        fechasDisponibles.value.length > 0 && !manualDateMode.value
          ? fechaDisponible.value
          : formatDateForAPI(fechaDisponibleCalendario.value);

      if (!fechaIncidente) {
        message.error('Ingrese una fecha de incidente');
        return;
      }

      const data = {
        usuario: user_,
        nroref: nrofile.value,
        codcli: product.value.codcli,
        passenger: selectedPassenger.value,
        type: selectedType.value,
        type_service: selectedTypeService.value,
        subcategory: selectedSubcategory.value,
        service: selectedService.value,
        comment: comment.value,
        response: response.value,
        date: fechaIncidente,
        estado: selectedStatus.value || 'P-2',
        infundado: infundado.value ? 1 : 0,
        subjetivo: subjetivo.value ? 1 : 0,
        asociado: asociado.value ? 1 : 0,
        flag_notify: flagNotify.value ? 1 : 0,
        asumido_por: asumidoPor.value,
        total_compensacion: totalCompensacion.value,
        monto_compensacion: montoCompensacion.value,
        monto_reembolso: montoReembolso.value,
        observaciones_compensacion: observacionesCompensacion.value,
        observaciones_reembolso: observacionesReembolso.value,
        corepr: codigoReservaProveedor.value,
        numfre: numeroFrecuencia.value,
        falta: falta.value,
        sancion: sancionProveedor.value,
        accion_sancion: accionSancion.value,
        autorizado_por: autorizado.value,
      };

      const response_ = await nonConformingProductsApi.post('/save', data);

      if (response_.data.data.type === 'success') {
        message.success('Producto guardado correctamente');
        changeView('L');
      } else {
        message.error('Error al guardar el producto');
      }
    } catch (error) {
      // MISMA LÓGICA DE MANEJO DE ERRORES
      if (error.response?.data?.error) {
        message.error(error.response.data.error);
      } else if (error.response?.data) {
        message.error(JSON.stringify(error.response.data));
      } else if (error.message) {
        // MISMA LÓGICA DE MANEJO DE ERRORES
        if (error.response?.data?.error) {
          message.error(error.response.data.error);
        } else if (error.response?.data) {
          message.error(JSON.stringify(error.response.data));
        } else if (error.message) {
          message.error('Error: ' + error.message);
        } else {
          message.error('Error desconocido');
        }
      } else {
        message.error('Error desconocido');
      }
    } finally {
      loading.value = false;
    }
  };

  const saveSimulator = async () => {
    loading.value = true;

    try {
      // Limitar longitud del texto (como en Angular)
      if (comment.value && comment.value.length >= 30000) {
        message.error(
          'No está permitido la cantidad de texto ingresado en el comentario. Por favor, trate de acortar el contenido para continuar.'
        );
        loading.value = false;
        return;
      }

      if (response.value && response.value.length >= 30000) {
        message.error(
          'No está permitido la cantidad de texto ingresado en la respuesta. Por favor, trate de acortar el contenido para continuar.'
        );
        loading.value = false;
        return;
      }

      const fechaIncidente =
        fechasDisponibles.value.length > 0 && !manualDateMode.value
          ? fechaDisponible.value
          : formatDateForAPI(fechaDisponibleCalendario.value);

      if (!fechaIncidente) {
        message.error('Ingrese una fecha de incidente');
        return;
      }

      const data = {
        usuario: user_,
        nroref: nrofile.value,
        codcli: product.value.codcli,
        passenger: selectedPassenger.value,
        type: selectedType.value,
        type_service: selectedTypeService.value,
        subcategory: selectedSubcategory.value,
        service: selectedService.value,
        comment: comment.value,
        response: response.value,
        date: fechaIncidente,
        estado: selectedStatus.value || 'P-2',
        infundado: infundado.value ? 1 : 0,
        subjetivo: subjetivo.value ? 1 : 0,
        asociado: asociado.value ? 1 : 0,
        flag_notify: flagNotify.value ? 1 : 0,
        asumido_por: asumidoPor.value,
        total_compensacion: totalCompensacion.value,
        monto_compensacion: montoCompensacion.value,
        monto_reembolso: montoReembolso.value,
        observaciones_compensacion: observacionesCompensacion.value,
        observaciones_reembolso: observacionesReembolso.value,
        corepr: codigoReservaProveedor.value,
        numfre: numeroFrecuencia.value,
        falta: falta.value,
        sancion: sancionProveedor.value,
        accion_sancion: accionSancion.value,
        autorizado_por: autorizado.value,
      };

      const response_ = await nonConformingProductsApi.post('/save-simulator', data);

      if (response_.data.data.type === 'success') {
        message.success('Simulación generada correctamente');
        simulationResult.value = response_.data.data;
        console.log(response_.data.data);
      } else {
        message.error('Error al guardar el producto');
      }
    } catch (error) {
      // MISMA LÓGICA DE MANEJO DE ERRORES
      if (error.response?.data?.error) {
        message.error(error.response.data.error);
      } else if (error.response?.data) {
        message.error(JSON.stringify(error.response.data));
      } else if (error.message) {
        // MISMA LÓGICA DE MANEJO DE ERRORES
        if (error.response?.data?.error) {
          message.error(error.response.data.error);
        } else if (error.response?.data) {
          message.error(JSON.stringify(error.response.data));
        } else if (error.message) {
          message.error('Error: ' + error.message);
        } else {
          message.error('Error desconocido');
        }
      } else {
        message.error('Error desconocido');
      }
    } finally {
      loading.value = false;
    }
  };

  const update = async () => {
    loading.value = true;
    try {
      // Verificar que todas las imágenes estén subidas
      const allUploaded = await checkAllImagesUploaded();
      if (!allUploaded) {
        message.warning('Espere a que terminen de subir todas las imágenes');
        loading.value = false;
        return;
      }

      // Limitar longitud del texto
      if (comment.value && comment.value.length >= 30000) {
        message.error(
          'No está permitido la cantidad de texto ingresado en el comentario. Por favor, trate de acortar el contenido para continuar.'
        );
        loading.value = false;
        return;
      }

      if (response.value && response.value.length >= 30000) {
        message.error(
          'No está permitido la cantidad de texto ingresado en la respuesta. Por favor, trate de acortar el contenido para continuar.'
        );
        loading.value = false;
        return;
      }

      const fechaIncidente =
        fechasDisponibles.value.length > 0 && !manualDateMode.value
          ? fechaDisponible.value
          : formatDateForAPI(fechaDisponibleCalendario.value);

      if (!fechaIncidente) {
        message.error('Ingrese una fecha de incidente');
        return;
      }

      const data = {
        usuario: user_,
        codref: product.value.codref,
        nroord: product.value.itecom,
        nroref: nrofile.value,
        passenger: selectedPassenger.value,
        type: selectedType.value,
        type_service: selectedTypeService.value,
        subcategory: selectedSubcategory.value,
        service: selectedService.value,
        comment: comment.value,
        prev_respue: prevResponse.value,
        respue: response.value,
        date: fechaIncidente,
        estado: selectedStatus.value,
        infundado: infundado.value ? 1 : 0,
        subjetivo: subjetivo.value ? 1 : 0,
        asociado: asociado.value ? 1 : 0,
        asumido_por: asumidoPor.value,
        total_compensacion: totalCompensacion.value,
        monto_compensacion: montoCompensacion.value,
        monto_reembolso: montoReembolso.value,
        observaciones_compensacion: observacionesCompensacion.value,
        observaciones_reembolso: observacionesReembolso.value,
        flag_notify: flagNotify.value ? 1 : 0,
        corepr: codigoReservaProveedor.value,
        numfre: numeroFrecuencia.value,
        falta: falta.value,
        sancion: sancionProveedor.value,
        accion_sancion: accionSancion.value,
        autorizado_por: autorizado.value,
      };

      const response_ = await nonConformingProductsApi.put('/update', data);

      if (response_.data.data.type === 'success') {
        message.success('Producto actualizado correctamente');
        if (response_.data.data.updated) {
          changeView('L');
          search();
        }
      } else {
        message.error('Error al actualizar el producto');
      }
    } catch (error) {
      // MISMA LÓGICA DE MANEJO DE ERRORES
      if (error.response?.data?.error) {
        message.error(error.response.data.error);
      } else if (error.response?.data) {
        message.error(JSON.stringify(error.response.data));
      } else if (error.message) {
        message.error('Error: ' + error.message);
      } else {
        message.error('Error desconocido');
      }
    } finally {
      loading.value = false;
    }
  };

  // Handlers para el estado de upload
  const handleImageUploadStart = () => {
    imageUploading.value = true;
    allFilesCompleted.value = false;
  };

  const handleImageUploadComplete = () => {
    imageUploading.value = false;
    allFilesCompleted.value = true;
  };

  const close = () => {
    selectedStatus.value = 'P-3';
    update();
  };

  const updateOptionProduct = async (campo) => {
    // Validación CORRECTA - solo permite si el producto ya está guardado
    if (!product.value.codref || product.value.codref === 0) {
      console.log('El producto no está activo - no se puede actualizar opciones individuales');
      // message.warning('Guarde el producto primero antes de actualizar opciones individuales');
      return;
    }

    try {
      const value =
        campo === 'INFUNDADO'
          ? infundado.value
            ? 1
            : 0
          : campo === 'SUBJETIVO'
            ? subjetivo.value
              ? 1
              : 0
            : asociado.value
              ? 1
              : 0;

      await nonConformingProductsApi.put('/update-option', {
        usuario: user_,
        codref: product.value.codref,
        nroref: nrofile.value,
        campo,
        value,
      });

      message.success(`${campo} actualizado correctamente`);
    } catch (error) {
      message.error('Error al actualizar: ' + error.message);
    }
  };

  const editProduct = async (record) => {
    resetFormFields();

    product.value = { ...record };
    nrofile.value = record.nroref;
    blocked.value = 1;
    editable.value = 1;

    codigoReservaProveedor.value = record.corepr || '';
    numeroFrecuencia.value = record.numfre || '';
    falta.value = record.descri_falta || '';
    sancionProveedor.value = record.sancion_proveedor || '';
    flag_autorizado.value = true;

    selectedTypeService.value = record.tipser?.trim() || '';
    selectedService.value = record.prefac?.trim() || '';
    selectedType.value = record.tipo?.trim() || '';
    selectedSubcategory.value = record.descri_subcategoria?.trim() || '';

    asumidoPor.value = record.asupor || '';
    montoCompensacion.value = parseFloat(record.moncom) || 0;
    montoReembolso.value = parseFloat(record.monree) || 0;
    totalCompensacion.value = parseFloat(record.montot) || 0;
    observacionesCompensacion.value = record.obscom || '';
    observacionesReembolso.value = record.obsree || '';

    comment.value = record.coment || '';
    response.value = record.respue || '';
    prevResponse.value = record.respue || '';

    infundado.value = record.infundado === 1;
    subjetivo.value = record.subjetivo === 1;
    asociado.value = record.asociado === 1;

    view_comment.value = 0; // Comentario en modo lectura inicialmente
    view_response.value = 0; // Respuesta oculta inicialmente

    allStatus.value = Object.values(status.value);
    allStatus.value.forEach((item) => {
      if (item.codigo + '-' + item.tipniv === record.estado + '-' + record.tipniv) {
        selectedStatus.value = item.codigo + '-' + item.tipniv;
        product.value.descri_estado = item.desc;
        changeStatus();
      }
    });

    if (record.estado + '-' + record.tipniv === 'P-2') {
      product.value.descri_estado = 'EN PROCESO';
      view_response.value = 1;
    }

    await searchDetail();
    if (record.prefac) {
      await searchFechasDisponibles(record.prefac);
    }

    if (record.fecing) {
      if (record.fecing.includes('/')) {
        const parts = record.fecing.split('/');
        if (parts.length > 2) {
          fechaDisponible.value = `${parts[0]}-${parts[1]}-${parts[2]}`;
        } else {
          // Ya está en DD-MM-YYYY
          fechaDisponible.value = record.fecing;
        }
      } else {
        fechaDisponible.value = record.fecing;
      }
    } else {
      fechaDisponible.value = '';
    }

    if (record.fecing) {
      if (fechasDisponibles.value.length === 0) {
        // Solo establecer el datepicker si NO hay fechas disponibles de la API
        if (record.fecing.includes('/')) {
          const [day, month, year] = record.fecing.split('/');
          fechaDisponibleCalendario.value = dayjs(`${year}-${month}-${day}`);
        } else if (record.fecing.includes('-')) {
          const [day, month, year] = record.fecing.split('-');
          fechaDisponibleCalendario.value = dayjs(`${year}-${month}-${day}`);
        } else {
          fechaDisponibleCalendario.value = dayjs(record.fecing);
        }
      }
    } else {
      fechaDisponibleCalendario.value = null;
    }

    await filterTypes();
    await filterSubcategories();

    searchTracings(record.nrocom);
    searchHistorial(record.nrocom);

    changeView('F', true);
  };

  const openProduct = async (record) => {
    Modal.confirm({
      title: '¿Desea re-abrir este seguimiento?',
      content: 'El seguimiento volverá al estado "EN PROCESO"',
      onOk: async () => {
        try {
          const fechaFormateada = formatDateForAPI(record.fecing);
          // CORRECCIÓN: Usar el endpoint /update en lugar de /update-option
          await nonConformingProductsApi.put('/update', {
            usuario: user_,
            codref: record.nrocom,
            nroord: record.itecom || 1, // Asegurar que nroord esté presente
            nroref: record.nroref,
            passenger: '', // Mantener datos existentes
            type: record.tipo || '',
            type_service: record.tipser || '',
            subcategory: record.descri_subcategoria || '',
            falta: record.descri_falta || '',
            service: record.prefac || '',
            comment: record.coment || '',
            prev_respue: record.respue || '',
            respue: record.respue || '', // Mantener respuesta existente
            date: fechaFormateada,
            estado: 'P-2', // Cambiar a EN PROCESO
            infundado: record.infundado || 0,
            subjetivo: record.subjetivo || 0,
            asociado: record.asociado || 0,
            asumido_por: record.asupor || '',
            total_compensacion: record.montot || 0,
            monto_compensacion: record.moncom || 0,
            monto_reembolso: record.monree || 0,
            observaciones_compensacion: record.obscom || '',
            observaciones_reembolso: record.obsree || '',
            sancion: record.sancion_proveedor || '',
            flag_notify: flagNotify.value ? 1 : 0,
            corepr: record.corepr || '',
            numfre: record.numfre || '',
          });

          message.success('Seguimiento re-abierto correctamente');
          search();
        } catch (error) {
          message.error('Error al re-abrir: ' + error.message);
        }
      },
    });
  };

  const resendMail = async (record) => {
    try {
      console.log(record);
      await nonConformingProductsApi.post('/resend-mail', {
        codref: record.nrocom,
        coment: record.coment,
        respue: record.respue,
        tipo_servicio: record.descri_tipo,
        subcategory: record.descri_subcategoria,
      });
      message.success('Correo enviado correctamente');
    } catch (error) {
      message.error('Error al enviar correo: ' + error.message);
    }
  };

  const deleteProduct = (record) => {
    Modal.confirm({
      title: '¿Está seguro de eliminar este producto?',
      content: 'Esta acción no se puede deshacer',
      okText: 'Sí, eliminar',
      okType: 'danger',
      cancelText: 'Cancelar',
      onOk: async () => {
        try {
          await nonConformingProductsApi.delete(`/delete/${record.nrocom || record.codref}`, {
            data: {
              usuario: user_,
              comentario: record.coment || comment.value,
              respuesta: record.respue || response.value,
              estado: 'X-0',
              subcategory: '',
              flag_notify: flagNotify.value ? 1 : 0,
            },
          });
          message.success('Producto eliminado correctamente');
          if (typeView.value === 'L') {
            search();
          } else {
            changeView('L');
          }
        } catch (error) {
          message.error('Error al eliminar: ' + error.message);
        }
      },
    });
  };

  const showDetailModal = (record) => {
    selectedProduct.value = record;
    detailModalVisible.value = true;
  };

  const addStates = () => {
    if (state.value === 'ALL') {
      statesSelected.value = [];
      return;
    }

    const statusObj = status.value.find((s) => `${s.codigo}-${s.tipniv}` === state.value);
    if (
      statusObj &&
      !statesSelected.value.find(
        (s) => s.codigo === statusObj.codigo && s.tipniv === statusObj.tipniv
      )
    ) {
      statesSelected.value.push(statusObj);
    }
  };

  const removeStates = (index) => {
    statesSelected.value.splice(index, 1);
  };

  const addFields = () => {
    if (field.value === 'ALL') {
      fieldsSelected.value = [];
      return;
    }

    if (
      !fieldsSelected.value.find((f) => f.key === field.value) &&
      field.value !== undefined &&
      field.value.trim() !== ''
    ) {
      fieldsSelected.value.push({
        key: field.value,
        desc: fields.value[field.value],
      });
    }
  };

  const removeFields = (index) => {
    fieldsSelected.value.splice(index, 1);
  };
  // Helper para manejar la respuesta dual (Archivo vs Mensaje)
  const handleResponse = async (response, filename) => {
    const contentType = response.headers['content-type'];

    // CASO 1: Es JSON (El servidor respondió 200 OK pero con mensaje de proceso en background)
    if (contentType && contentType.includes('application/json')) {
      // Como pedimos responseType: 'blob', el JSON viene dentro del blob. Hay que leerlo.
      const textData = await response.data.text();
      const jsonData = JSON.parse(textData);

      // Mostramos mensaje informativo (azul) en lugar de éxito
      message.info(jsonData.text || 'El reporte se está procesando y llegará a su correo.');
      return;
    }

    // CASO 2: Es un Archivo (Excel)
    downloadFile(response.data, filename);
    message.success('Excel descargado correctamente');
  };

  const exportExcel = async () => {
    loading.value = true;

    // Validación de fechas
    const date1 = dateRange.value[0] ? dateRange.value[0].format('YYYY-MM-DD') : '';
    const date2 = dateRange.value[1] ? dateRange.value[1].format('YYYY-MM-DD') : '';

    if (date1 && date2) {
      const dayjsDate1 = dayjs(date1, 'YYYY-MM-DD');
      const dayjsDate2 = dayjs(date2, 'YYYY-MM-DD');

      if (dayjsDate1.isAfter(dayjsDate2)) {
        message.warning('Fechas incorrectas');
        loading.value = false;
        return;
      }
    }

    try {
      const params = {
        type: 'products',
        user: user_,
        fecin: date1,
        fecout: date2,
        state: statesSelected.value.length > 0 ? statesSelected.value : 'ALL',
        _fields: fieldsSelected.value,
      };

      const response = await exportsApi.get('/excel', {
        params,
        responseType: 'blob', // Importante
      });

      // Usamos el manejador inteligente
      await handleResponse(response, `productos-nc-${moment().format('YYYY-MM-DD')}.xlsx`);
    } catch (error) {
      console.error('Error exportExcel:', error);
      // Si el blob contiene un error JSON 400/500, podríamos querer leerlo también,
      // pero por simplicidad mostramos error genérico o el statusText.
      message.error('Error al procesar la solicitud del Excel');
    } finally {
      loading.value = false;
    }
  };

  const exportExcelDesglosed = async () => {
    loading.value = true;

    const date1 = dateRange.value[0] ? dateRange.value[0].format('YYYY-MM-DD') : '';
    const date2 = dateRange.value[1] ? dateRange.value[1].format('YYYY-MM-DD') : '';

    if (date1 && date2) {
      const dayjsDate1 = dayjs(date1, 'YYYY-MM-DD');
      const dayjsDate2 = dayjs(date2, 'YYYY-MM-DD');

      if (dayjsDate1.isAfter(dayjsDate2)) {
        message.warning('Fechas incorrectas');
        loading.value = false;
        return;
      }
    }

    try {
      const params = {
        type: 'products',
        user: user_,
        tipfec: tipfec.value,
        fecin: date1,
        fecout: date2,
      };

      const response = await exportsApi.get('/excel-desglosed', {
        params,
        responseType: 'blob',
      });

      // Usamos el manejador inteligente
      await handleResponse(response, `conteo-faltas-${moment().format('YYYYMMDDHHmmss')}.xlsx`);
    } catch (error) {
      console.error('Error exportExcelDesglosed:', error);
      message.error('Error al procesar la solicitud del Excel desglosado');
    } finally {
      loading.value = false;
    }
  };

  // Función auxiliar para descargar archivos
  const downloadFile = (blobData, filename) => {
    const url = window.URL.createObjectURL(new Blob([blobData]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', filename);
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.URL.revokeObjectURL(url);
  };

  // Cargar datos iniciales
  onMounted(async () => {
    updateTableScroll();
    window.addEventListener('resize', updateTableScroll);

    user_ = localStorage.getItem('user_code');
    try {
      const response = await nonConformingProductsApi.get('/search-type-services', {
        params: { module: '', usuario: user_ },
      });
      allTypeservices.value = response.data.data;

      // Obtener usuario actual (desde store o API)
      userSelected.value = user_;

      await search();

      filterCustomers();
    } catch (error) {
      message.error('Error al cargar datos iniciales: ' + error.message);
    }
  });

  onUnmounted(() => {
    window.removeEventListener('resize', updateTableScroll);
  });
</script>

<style scoped>
  .nonconforming-product-container {
    padding: 16px;
  }

  .filters-card {
    margin-bottom: 16px;
    padding: 16px;
  }

  .filter-section {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 16px;
    flex-wrap: wrap;
  }

  .filter-group {
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .filter-label {
    font-size: 13px;
    font-weight: 500;
    color: #595959;
    white-space: nowrap;
  }

  .tags-section {
    margin-top: 16px;
    padding-top: 16px;
    border-top: 1px solid #f0f0f0;
  }

  .tags-label {
    font-size: 13px;
    font-weight: 500;
    color: #666;
    margin-right: 12px;
  }

  .filter-tag {
    margin: 2px 4px 2px 0;
    font-size: 12px;
  }

  /* Responsive */
  @media (max-width: 768px) {
    .filter-section {
      flex-direction: column;
      align-items: flex-start;
      gap: 12px;
    }

    .filter-group {
      width: 100%;
    }

    .filter-group :deep(.ant-select) {
      flex: 1;
    }

    .filter-label {
      min-width: 100px;
      text-align: left;
    }
  }

  @media (max-width: 576px) {
    .nonconforming-product-container {
      padding: 12px;
    }

    .filters-card {
      padding: 12px;
    }

    .filter-section {
      gap: 8px;
    }
  }

  .responsive-table {
    font-size: 12px;
    transition: all 0.3s ease;
  }

  .responsive-table :deep(.ant-table-thead > tr > th) {
    white-space: normal;
    word-wrap: break-word;
    line-height: 1.2;
    padding: 8px 4px;
    font-size: 11px;
    font-weight: 600;
    background-color: #fafafa;
  }

  .responsive-table :deep(.ant-table-tbody > tr > td) {
    white-space: normal;
    word-wrap: break-word;
    line-height: 1.3;
    padding: 6px 4px;
    vertical-align: top;
    font-size: 11px;
  }

  .multiline-cell {
    min-height: 32px;
    display: flex;
    align-items: center;
    word-wrap: break-word;
    word-break: break-word;
  }

  .responsive-table :deep(.ant-btn-sm) {
    height: 24px;
    padding: 0 6px;
    font-size: 11px;
  }

  /* Breakpoints específicos */
  @media (min-width: 1600px) {
    .responsive-table {
      /* Sin scroll - se ajusta automáticamente */
    }
  }

  @media (max-width: 1599px) and (min-width: 1200px) {
    .responsive-table {
      font-size: 11px;
    }

    .responsive-table :deep(.ant-table-thead > tr > th) {
      padding: 6px 3px;
      font-size: 10px;
    }

    .responsive-table :deep(.ant-table-tbody > tr > td) {
      padding: 4px 3px;
      font-size: 10px;
    }

    .multiline-cell {
      min-height: 28px;
      font-size: 10px;
    }
  }

  @media (max-width: 1199px) and (min-width: 992px) {
    .responsive-table {
      font-size: 11px;
    }

    .responsive-table :deep(.ant-table-thead > tr > th) {
      padding: 5px 2px;
      font-size: 10px;
    }

    .responsive-table :deep(.ant-table-tbody > tr > td) {
      padding: 3px 2px;
      font-size: 10px;
    }
  }

  @media (max-width: 991px) {
    .responsive-table {
      font-size: 10px;
    }

    .responsive-table :deep(.ant-table-thead > tr > th) {
      padding: 4px 2px;
      font-size: 9px;
    }

    .responsive-table :deep(.ant-table-tbody > tr > td) {
      padding: 2px 2px;
      font-size: 9px;
    }

    .multiline-cell {
      min-height: 24px;
      font-size: 9px;
    }

    .responsive-table :deep(.ant-btn-sm) {
      height: 22px;
      padding: 0 4px;
      font-size: 9px;
    }
  }

  /* Mejorar la experiencia del scroll */
  .responsive-table :deep(.ant-table-body) {
    scrollbar-width: thin;
    scrollbar-color: #d9d9d9 #f5f5f5;
  }

  .responsive-table :deep(.ant-table-body::-webkit-scrollbar) {
    height: 8px;
  }

  .responsive-table :deep(.ant-table-body::-webkit-scrollbar-track) {
    background: #f5f5f5;
  }

  .responsive-table :deep(.ant-table-body::-webkit-scrollbar-thumb) {
    background: #d9d9d9;
    border-radius: 4px;
  }

  .responsive-table :deep(.ant-table-body::-webkit-scrollbar-thumb:hover) {
    background: #bfbfbf;
  }

  .historial-container {
    display: flex;
    flex-direction: column;
    gap: 16px;
  }

  .historial-card {
    border: 1px solid #ffe58f;
    border-radius: 6px;
    background: #fffbf0;
    overflow: hidden;
  }

  .historial-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 16px;
    background: #fff7e6;
    border-bottom: 1px solid #ffe58f;
  }

  .historial-number {
    font-weight: 600;
    color: #d46b08;
  }

  .historial-content {
    padding: 16px;
    background: white;
    max-height: 300px;
    overflow-y: auto;
  }

  /* Estilos para el contenido HTML renderizado */
  .historial-content :deep(*) {
    max-width: 100%;
  }

  .historial-content :deep(img) {
    max-width: 100%;
    height: auto;
  }

  .historial-content :deep(table) {
    width: 100%;
    border-collapse: collapse;
  }

  .historial-content :deep(table, th, td) {
    border: 1px solid #d9d9d9;
    padding: 4px 8px;
  }

  .html-content-preview {
    padding: 12px;
    background: #f5f5f5;
    border-radius: 4px;
    border: 1px solid #d9d9d9;
    min-height: 40px;
    max-height: 300px;
    overflow-y: auto;
  }

  .html-content-empty {
    padding: 12px;
    background: #f5f5f5;
    border-radius: 4px;
    border: 1px dashed #d9d9d9;
    color: #999;
    text-align: center;
  }

  /* Estilos para imágenes en el preview */
  .html-content-preview :deep(img) {
    max-width: 100%;
    height: auto;
    border-radius: 4px;
  }

  .simulation-report-card {
    margin-top: 20px;
    background-color: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    font-family: sans-serif;
  }

  .report-header {
    background-color: #f3f4f6;
    padding: 12px 20px;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .report-header h3 {
    margin: 0;
    color: #374151;
    font-size: 1.1rem;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .close-btn {
    background: transparent;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: #9ca3af;
  }

  .close-btn:hover {
    color: #4b5563;
  }

  .report-body {
    padding: 20px;
  }

  .status-msg {
    background-color: #d1fae5; /* Verde claro */
    color: #065f46;
    padding: 10px;
    border-radius: 6px;
    margin-bottom: 15px;
    font-weight: 500;
  }

  .email-section {
    margin-bottom: 15px;
  }

  .email-section h4 {
    margin: 0 0 8px 0;
    font-size: 0.9rem;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.05em;
  }

  .email-list {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
  }

  .email-tag {
    padding: 4px 10px;
    border-radius: 9999px;
    font-size: 0.85rem;
    border: 1px solid;
  }

  .email-tag.to {
    background-color: #eff6ff;
    color: #1d4ed8;
    border-color: #bfdbfe;
  }

  .email-tag.bcc {
    background-color: #fff7ed;
    color: #c2410c;
    border-color: #fed7aa;
  }

  .debug-section {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px dashed #d1d5db;
    font-size: 0.85rem;
    color: #4b5563;
  }

  .debug-section ul {
    list-style: none;
    padding: 0;
    margin: 0;
  }

  .fade-enter-active,
  .fade-leave-active {
    transition: opacity 0.5s;
  }
  .fade-enter,
  .fade-leave-to {
    opacity: 0;
  }
</style>
