<template>
  <div class="seguimiento-container">
    <!-- Contenido Principal -->
    <div class="content-wrapper">
      <a-card class="seguimiento-card">
        <template #title>
          <img
            src="https://a3.limatours.com.pe/images/logo_limatours.png"
            alt="Limatours"
            style="
              max-height: 60px;
              height: auto;
              width: auto;
              display: block;
              float: left;
              margin-right: 24px;
              padding: 5px;
            "
          />
          <h4 style="margin: 5px">Customer Service / Producto No Conforme</h4>
        </template>

        <a-form layout="vertical" :model="formData">
          <!-- Fila 1: Nro File y EC -->
          <a-row :gutter="16">
            <a-col :span="12">
              <a-form-item label="Nro File" class="form-label-bold">
                <a-input v-model:value="formData.nroref" readonly class="readonly-input" />
              </a-form-item>
            </a-col>
            <a-col :span="12">
              <a-form-item label="EC" class="form-label-bold">
                <a-input v-model:value="formData.ec" readonly class="readonly-input" />
              </a-form-item>
            </a-col>
          </a-row>

          <!-- Fila 2: Nombre RVA y Pasajero -->
          <a-row :gutter="16">
            <a-col :span="12">
              <a-form-item label="Nombre RVA" class="form-label-bold">
                <a-input v-model:value="formData.nombre_rva" readonly class="readonly-input" />
              </a-form-item>
            </a-col>
            <a-col :span="12">
              <a-form-item label="Pasajero" class="form-label-bold">
                <a-input v-model:value="formData.pasajero" readonly class="readonly-input" />
              </a-form-item>
            </a-col>
          </a-row>

          <!-- Fila 3: Cliente -->
          <a-row :gutter="16">
            <a-col :span="12">
              <a-form-item label="Cliente (Nombre)" class="form-label-bold">
                <a-input v-model:value="formData.nombre" readonly class="readonly-input" />
              </a-form-item>
            </a-col>
          </a-row>

          <!-- Fila 4: Área -->
          <a-row :gutter="16">
            <a-col :span="12">
              <a-form-item label="Área" class="form-label-bold">
                <a-input v-model:value="formData.area" readonly class="readonly-input" />
              </a-form-item>
            </a-col>
          </a-row>

          <!-- Fila 5: Servicio, Proveedor y Fecha Incidente -->
          <a-row :gutter="16">
            <a-col :span="8">
              <a-form-item label="Tipo de Servicio" class="form-label-bold">
                <a-input v-model:value="formData.servicio" readonly class="readonly-input" />
              </a-form-item>
            </a-col>
            <a-col :span="8">
              <a-form-item label="Proveedor" class="form-label-bold">
                <a-input v-model:value="formData.proveedor" readonly class="readonly-input" />
              </a-form-item>
            </a-col>
            <a-col :span="8">
              <a-form-item label="Fecha del Incidente" class="form-label-bold">
                <a-input v-model:value="formData.fecha_incidente" readonly class="readonly-input" />
              </a-form-item>
            </a-col>
          </a-row>

          <!-- Campos adicionales (Código Reserva y Frecuencia) -->
          <a-row :gutter="16" style="display: none">
            <a-col :span="12">
              <a-form-item label="CÓDIGO DE RESERVA DEL PROVEEDOR" class="form-label-bold">
                <a-input
                  v-model:value="formData.codigo_reserva_proveedor"
                  class="additional-field"
                />
              </a-form-item>
            </a-col>
            <a-col :span="12">
              <a-form-item label="NÚMERO DE FRECUENCIA" class="form-label-bold">
                <a-input v-model:value="formData.numero_frecuencia" class="additional-field" />
              </a-form-item>
            </a-col>
          </a-row>

          <!-- Fila 6: Categoría, Subcategoría y Estado -->
          <a-row :gutter="16">
            <a-col :span="8">
              <a-form-item label="Categoría" class="form-label-bold">
                <a-input v-model:value="formData.tipo" readonly class="readonly-input" />
              </a-form-item>
            </a-col>
            <a-col :span="8">
              <a-form-item label="Subcategoría" class="form-label-bold">
                <a-input v-model:value="formData.subcategoria" readonly class="readonly-input" />
              </a-form-item>
            </a-col>
            <a-col :span="8">
              <a-form-item label="Estado" class="form-label-bold">
                <a-select
                  v-model:value="selectedEstado"
                  :options="statusOptions"
                  class="status-select"
                />
              </a-form-item>
            </a-col>
          </a-row>

          <!-- Comentario -->
          <a-row :gutter="16">
            <a-col :span="24">
              <a-form-item label="Comentario" class="form-label-bold">
                <div class="comment-box" v-html="formData.comentario"></div>
              </a-form-item>
            </a-col>
          </a-row>

          <!-- Respuesta -->
          <a-row :gutter="16">
            <a-col :span="24">
              <a-form-item label="Respuesta" class="form-label-bold">
                <div class="response-box">
                  <div v-if="!formData.respuesta_enviada">
                    <CloudinaryQuillEditor
                      ref="responseEditorRef"
                      v-model:modelValue="formData.respuesta"
                      :placeholder="'Ingrese la respuesta...'"
                      @image-upload-start="handleImageUploadStart"
                      @image-upload-complete="handleImageUploadComplete"
                    />
                    <div v-if="imageUploading" class="upload-indicator">
                      <a-alert message="Subiendo imágenes..." type="info" show-icon />
                    </div>
                  </div>
                  <div v-else v-html="formData.respuesta" class="response-display"></div>
                </div>
              </a-form-item>
            </a-col>
          </a-row>

          <!-- Acción y Autorización -->
          <a-row :gutter="16">
            <a-col :span="12">
              <a-form-item label="Acción / Sanción" class="form-label-bold">
                <a-input
                  v-model:value="formData.accionSancion"
                  @blur="updateAction"
                  placeholder="Ingrese acción/sanción"
                />
              </a-form-item>
            </a-col>
            <a-col :span="12">
              <a-form-item label="Autorizado por" class="form-label-bold">
                <a-input
                  v-model:value="formData.autorizado"
                  @blur="updateAutorized"
                  placeholder="Ingrese autorizado por"
                />
              </a-form-item>
            </a-col>
          </a-row>

          <!-- Botones de Acción -->
          <a-row :gutter="16" v-if="!formData.respuesta_enviada">
            <a-col :span="24">
              <div class="action-buttons">
                <a-button
                  v-if="isEditable"
                  type="primary"
                  danger
                  @click="save"
                  :loading="loading"
                  class="action-btn"
                >
                  {{ loading ? 'Enviando...' : 'Enviar Respuesta' }}
                </a-button>
                <a-button
                  v-if="isClosable"
                  type="primary"
                  danger
                  @click="closeSeguimiento"
                  :loading="loading"
                  class="action-btn"
                >
                  {{ loading ? 'Cerrando...' : 'Cerrar Seguimiento' }}
                </a-button>
              </div>
            </a-col>
          </a-row>
        </a-form>

        <!-- Alertas -->
        <div class="alert-container">
          <a-alert
            v-if="alertMessage"
            :message="alertMessage"
            :type="alertType"
            show-icon
            closable
            @close="clearAlert"
          />
        </div>
      </a-card>
    </div>
  </div>
</template>

<script setup>
  import { ref, computed, onMounted, onUnmounted } from 'vue';
  import { useRoute } from 'vue-router';
  // import { message, Modal } from 'ant-design-vue'
  import CloudinaryQuillEditor from '../../components/CloudinaryQuillEditor.vue';
  import { nonConformingProductsApi_PUBLIC } from '../../services/api';
  import { sha1 as jsSha1 } from 'js-sha1';

  // Props y route
  const route = useRoute();

  // Refs
  const loading = ref(false);
  const alertMessage = ref('');
  const alertType = ref('');
  const responseEditorRef = ref(null);
  const imageUploading = ref(false);

  // Datos del formulario
  const formData = ref({
    nroref: '',
    ec: '',
    nombre_rva: '',
    pasajero: '',
    nombre: '',
    area: '',
    servicio: '',
    proveedor: '',
    fecha_incidente: '',
    tipo: '',
    subcategoria: '',
    codigo_reserva_proveedor: '',
    numero_frecuencia: '',
    comentario: '',
    respuesta: '',
    accionSancion: '',
    autorizado: '',
    respuesta_enviada: false,
  });

  // Estados y opciones
  const statusList = ref([]);
  const selectedEstado = ref('P-3');
  const isEditable = ref(false);
  const isClosable = ref(false);

  // Variables internas
  const nrocom = ref('');
  const nroord = ref('');
  const nroref = ref('');
  // const allFiles = ref(0)
  // const allFilesCompleted = ref(0)

  // Computed
  const statusOptions = computed(() => {
    return statusList.value.map((status) => ({
      value: `${status.codigo}-${status.tipniv}`,
      label: status.desc,
    }));
  });

  // Métodos
  const init = async () => {
    // Obtener token de la URL
    nrocom.value = route.query.token || '';

    if (!nrocom.value) {
      showAlert('Token no válido', 'error');
      return;
    }

    // Verificar permisos
    await verifyPermissions();

    // Cargar datos del seguimiento
    await loadSeguimientoData();
  };

  const verifyPermissions = () => {
    const { token, _token, _editable, _close } = route.query;

    console.log('Parámetros URL recibidos:', { token, _token, _editable, _close });

    // RÉPLICA EXACTA DE LA LÓGICA PHP ORIGINAL
    if (_editable && _token) {
      try {
        const encodedEditable = jsSha1(_editable);
        isEditable.value = encodedEditable === _token;
        console.log('Verificación editable:', {
          _editable,
          encoded: encodedEditable,
          _token,
          resultado: isEditable.value,
        });
      } catch (error) {
        console.error('Error en verificación editable:', error);
        isEditable.value = false;
      }
    } else {
      isEditable.value = false;
    }

    // RÉPLICA EXACTA DE LA LÓGICA PHP ORIGINAL
    if (_close && _token) {
      try {
        const encodedClose = jsSha1(_close);
        isClosable.value = encodedClose === _token;
        console.log('Verificación close:', {
          _close,
          encoded: encodedClose,
          _token,
          resultado: isClosable.value,
        });
      } catch (error) {
        console.error('Error en verificación close:', error);
        isClosable.value = false;
      }
    } else {
      isClosable.value = false;
    }

    console.log('Permisos finales:', {
      editable: isEditable.value,
      closable: isClosable.value,
    });
  };

  // Implementación SHA1 similar a PHP
  // const sha1 = async (message) => {
  //   // Para compatibilidad con el SHA1 de PHP
  //   const msgBuffer = new TextEncoder().encode(message);
  //   const hashBuffer = await crypto.subtle.digest('SHA-1', msgBuffer);
  //   const hashArray = Array.from(new Uint8Array(hashBuffer));
  //   return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
  // }

  const loadSeguimientoData = async () => {
    try {
      loading.value = true;
      const response = await nonConformingProductsApi_PUBLIC.get(
        `/seguimiento/product/${nrocom.value}`
      );
      const data = response.data.data;

      if (!data.seguimiento) {
        showAlert('El seguimiento no existe. Por favor, revise si su enlace es correcto.', 'error');
        return;
      }

      const seguimiento = data.seguimiento;
      statusList.value = data.status || [];

      // Llenar datos del formulario
      formData.value.nroref = seguimiento.nroref || '';
      formData.value.ec = seguimiento.codope || '';
      formData.value.pasajero = seguimiento.nombre || '';
      formData.value.servicio = seguimiento.servicio || 'OTROS PROVEEDORES';
      formData.value.proveedor = seguimiento.proveedor_servicio || '';
      formData.value.fecha_incidente = seguimiento.fecing || '';
      formData.value.tipo = seguimiento.descri_tipo || '';
      formData.value.subcategoria = seguimiento.descri_subcategoria || '';

      // Están ocultos
      formData.value.codigo_reserva_proveedor = seguimiento.corepr || '';
      formData.value.numero_frecuencia = seguimiento.numfre || '';

      nroord.value = seguimiento.itecom;
      nroref.value = seguimiento.nroref;

      // Configurar estado seleccionado
      selectedEstado.value = 'P-3';

      // Verificar si se debe mostrar botón de cerrar
      if (
        !(
          (seguimiento.tipniv?.trim() == 2 && seguimiento.estado?.trim() == 'P') ||
          (seguimiento.tipniv?.trim() == 0 && seguimiento.estado?.trim() == 'P')
        )
      ) {
        isClosable.value = false;
      }

      // Cargar comentario y respuesta
      await loadCommentAndResponse(seguimiento.nrocom);
      await loadDetailData(seguimiento.nroref);
    } catch (error) {
      console.error('Error loading seguimiento data:', error);
      showAlert('Error al cargar los datos del seguimiento', 'error');
    } finally {
      loading.value = false;
    }
  };

  const loadCommentAndResponse = async (nrocom) => {
    try {
      const response = await nonConformingProductsApi_PUBLIC.get('/search-product-text', {
        params: { nrocom, identi: 'S' },
      });

      const data = response.data.data;
      formData.value.comentario = data.coment || '';
      formData.value.respuesta = data.respue || '';
      formData.value.accionSancion = data.accion_sancion || '';
      formData.value.autorizado = data.autorizado || '';

      // Verificar si ya se envió respuesta
      if (data.respue && data.respue.trim() !== '') {
        formData.value.respuesta_enviada = true;
        showAlert('Ya se envió una respuesta del seguimiento.', 'warning');
      }
    } catch (error) {
      console.error('Error loading comment and response:', error);
    }
  };

  const loadDetailData = async (nroref) => {
    try {
      const response = await nonConformingProductsApi_PUBLIC.get(`/search-detail/${nroref}`);

      const data = response.data.data;
      formData.value.nombre_rva = data.descri || '';
      formData.value.nombre = data.razon || '';
      formData.value.area = data.codsec || '';
    } catch (error) {
      console.error('Error loading detail data:', error);
    }
  };

  const updateAction = async () => {
    try {
      await nonConformingProductsApi_PUBLIC.put('/update-action', {
        codref: nrocom.value,
        accion: formData.value.accionSancion,
      });
      // message.success('Acción/sanción actualizada correctamente')
    } catch (error) {
      console.error('Error updating action:', error);
      // message.error('Error al actualizar la acción')
    }
  };

  const updateAutorized = async () => {
    try {
      await nonConformingProductsApi_PUBLIC.put('/update-authorized', {
        codref: nrocom.value,
        autorizado: formData.value.autorizado,
      });
      // message.success('Autorizado por actualizado correctamente')
    } catch (error) {
      console.error('Error updating autorized:', error);
      // message.error('Error al actualizar el autorizado')
    }
  };

  const checkAllImagesUploaded = async () => {
    if (responseEditorRef.value) {
      const responseFiles = responseEditorRef.value.getAllFiles();
      if (responseFiles.total !== responseFiles.completed) return false;
    }
    return true;
  };

  const save = async () => {
    // Validaciones
    if (!formData.value.accionSancion.trim()) {
      showAlert('Debe completar el campo "acción / sanción" para continuar.', 'error');
      return false;
    }

    if (!formData.value.autorizado.trim()) {
      showAlert('Debe completar el campo "autorizado por" para continuar.', 'error');
      return false;
    }

    if (!formData.value.respuesta.trim()) {
      showAlert('Ingrese una respuesta para poder actualizar el seguimiento.', 'error');
      return false;
    }

    loading.value = true;
    showAlert('Cargando...', 'info');

    try {
      // Verificar que todas las imágenes estén subidas
      const allUploaded = await checkAllImagesUploaded();
      if (!allUploaded) {
        showAlert('Espere a que terminen de subir todas las imágenes', 'warning');
        loading.value = false;
        return false;
      }

      // Limitar longitud del texto
      if (formData.value.respuesta && formData.value.respuesta.length >= 30000) {
        showAlert(
          'No está permitido la cantidad de texto ingresado en la respuesta. Por favor, trate de acortar el contenido para continuar.',
          'error'
        );
        loading.value = false;
        return;
      }

      const data = {
        codref: nrocom.value,
        nroord: nroord.value,
        nroref: nroref.value,
        coment: formData.value.comentario,
        respue: formData.value.respuesta,
        estado: selectedEstado.value,
      };

      const response = await nonConformingProductsApi_PUBLIC.put('/seguimiento', data);

      if (response.data.success) {
        showAlert('Producto no conforme actualizado correctamente.', 'success');
        formData.value.respuesta_enviada = true;

        setTimeout(() => {
          window.location.reload();
        }, 3000);
      } else {
        showAlert('Ocurrió un error. Por favor, intente nuevamente.', 'error');
      }
    } catch (error) {
      console.error('Error saving seguimiento:', error);
      showAlert('Error al guardar el seguimiento', 'error');
    } finally {
      loading.value = false;
    }
  };

  const closeSeguimiento = async () => {
    Modal.confirm({
      title: 'Cerrar Seguimiento',
      content: '¿Está seguro de que desea cerrar este seguimiento?',
      okText: 'Sí, cerrar',
      cancelText: 'Cancelar',
      onOk: async () => {
        loading.value = true;
        showAlert('Cargando...', 'info');

        try {
          // Verificar que todas las imágenes estén subidas
          const allUploaded = await checkAllImagesUploaded();
          if (!allUploaded) {
            showAlert('Espere a que terminen de subir todas las imágenes', 'warning');
            loading.value = false;
            return false;
          }

          // Limitar longitud del texto
          if (formData.value.respuesta && formData.value.respuesta.length >= 30000) {
            showAlert(
              'No está permitido la cantidad de texto ingresado en la respuesta. Por favor, trate de acortar el contenido para continuar.',
              'error'
            );
            loading.value = false;
            return;
          }

          const data = {
            codref: nrocom.value,
            nroord: nroord.value,
            nroref: nroref.value,
            coment: formData.value.comentario,
            response: formData.value.respuesta,
            estado: 'P-3',
          };

          const response = await nonConformingProductsApi_PUBLIC.put('/update', data);

          if (response.data.success) {
            showAlert('Producto no conforme cerrado correctamente.', 'success');
            formData.value.respuesta_enviada = true;

            setTimeout(() => {
              window.location.reload();
            }, 3000);
          } else {
            showAlert('Ocurrió un error. Por favor, intente nuevamente.', 'error');
          }
        } catch (error) {
          console.error('Error closing seguimiento:', error);
          showAlert('Error al cerrar el seguimiento', 'error');
        } finally {
          loading.value = false;
        }
      },
    });
  };

  const handleImageUploadStart = () => {
    imageUploading.value = true;
  };

  const handleImageUploadComplete = () => {
    imageUploading.value = false;
  };

  const showAlert = (message, type) => {
    alertMessage.value = message;
    alertType.value = type;
  };

  const clearAlert = () => {
    alertMessage.value = '';
    alertType.value = '';
  };

  // Lifecycle
  onMounted(() => {
    init();
  });

  onUnmounted(() => {
    // Cleanup si es necesario
  });
</script>

<style scoped>
  .seguimiento-container {
    min-height: 100vh;
    background-color: #f0f2f5;
  }

  .header {
    background: white;
    padding: 16px 24px;
    border-bottom: 1px solid #e8e8e8;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  }

  .sidebar-toggle-box {
    display: flex;
    align-items: center;
  }

  .logo img {
    height: 40px;
  }

  .title-section h2 {
    margin: 0;
    color: #262626;
    font-size: 20px;
    font-weight: 600;
  }

  .content-wrapper {
    padding: 24px;
    max-width: 1200px;
    margin: 0 auto;
  }

  .seguimiento-card {
    border-radius: 8px;
    box-shadow:
      0 3px 6px -4px rgba(0, 0, 0, 0.12),
      0 6px 16px 0 rgba(0, 0, 0, 0.08);
  }

  :deep(.ant-card-head-title) {
    font-size: 18px;
    font-weight: 600;
    color: #262626;
  }

  .form-label-bold :deep(.ant-form-item-label > label) {
    font-weight: 600;
    color: #262626;
  }

  .readonly-input {
    background-color: #fafafa;
    color: #8c8c8c;
  }

  .readonly-input:deep(.ant-input) {
    background-color: #fafafa;
    color: #8c8c8c;
    cursor: not-allowed;
  }

  .comment-box,
  .response-box {
    border: 1px dashed #d9d9d9;
    padding: 16px;
    border-radius: 6px;
    background-color: #fafafa;
    min-height: 120px;
  }

  .response-display {
    min-height: 120px;
    line-height: 1.6;
  }

  .response-display :deep(*) {
    max-width: 100%;
  }

  .response-display :deep(img) {
    max-width: 100%;
    height: auto;
    border-radius: 4px;
  }

  .additional-field:deep(.ant-input) {
    background-color: #fff;
  }

  .status-select {
    width: 100%;
  }

  .action-buttons {
    display: flex;
    gap: 12px;
    justify-content: center;
    margin-top: 24px;
  }

  .action-btn {
    min-width: 160px;
    height: 40px;
    font-weight: 500;
  }

  .alert-container {
    margin-top: 16px;
  }

  .upload-indicator {
    margin-top: 12px;
  }

  /* Responsive */
  @media (max-width: 768px) {
    .header {
      padding: 12px 16px;
      flex-direction: column;
      gap: 12px;
      text-align: center;
    }

    .content-wrapper {
      padding: 16px;
    }

    .action-buttons {
      flex-direction: column;
    }

    .action-btn {
      width: 100%;
    }
  }

  @media (max-width: 576px) {
    .content-wrapper {
      padding: 12px;
    }

    :deep(.ant-row) {
      margin: 0 -8px;
    }

    :deep(.ant-col) {
      padding: 0 8px;
    }
  }
</style>
