<template>
  <div class="step4-container">
    <div class="section-title">Restricción de servicios</div>

    <div class="restrictions-table">
      <!-- Header -->
      <div class="table-header">
        <div class="col-servicios">Servicios</div>
        <div class="col-switch">Producto</div>
        <div class="col-switch">Proveedores</div>
        <div class="col-switch">Opciones</div>
        <div class="col-switch">Ciudad</div>
        <div class="col-config">Configurar</div>
      </div>

      <!-- Data Rows -->
      <div class="table-body">
        <div v-for="(item, index) in dummyData" :key="item.id" class="table-row">
          <div class="col-servicios">
            <div class="ord-pill">01</div>
            <div class="servicio-info">
              <div class="servicio-title">{{ item.codigo }} - {{ item.nombre }}</div>
              <div class="servicio-subtitle">{{ item.subtitulo }}</div>
            </div>
          </div>

          <div class="col-switch">
            <a-switch v-model:checked="item.producto" class="custom-switch" />
          </div>
          <div class="col-switch">
            <a-switch v-model:checked="item.proveedores" class="custom-switch" />
          </div>
          <div class="col-switch">
            <a-switch v-model:checked="item.opciones" class="custom-switch" />
          </div>
          <div class="col-switch">
            <a-switch v-model:checked="item.ciudad" class="custom-switch" />
          </div>

          <div class="col-config">
            <font-awesome-icon
              :icon="['fas', 'gear']"
              class="gear-icon"
              :class="{ 'is-disabled': !hasActiveSwitch(item) }"
              @click="openConfigDrawer(item)"
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Drawer para configurar servicios -->
    <a-drawer
      v-model:open="isConfigDrawerVisible"
      placement="right"
      :width="420"
      :closable="true"
      class="config-drawer"
    >
      <template #title>
        <div class="drawer-title-custom">
          <font-awesome-icon :icon="['fas', 'gear']" class="title-gear-icon" />
          <span>Configurar servicios</span>
        </div>
      </template>

      <div class="drawer-content">
        <!-- Modificar producto -->
        <div class="form-group">
          <label>Modificar producto :</label>
          <a-select
            v-model:value="formConfig.producto"
            mode="multiple"
            placeholder="Seleccionar producto"
            class="custom-select"
            :options="productoOptions"
            :maxTagCount="responsiveMaxTagCount"
          />
        </div>

        <!-- Modificar proveedores -->
        <div class="form-group">
          <label>Modificar proveedores :</label>
          <a-select
            v-model:value="formConfig.proveedores"
            mode="multiple"
            placeholder="Seleccionar proveedor"
            class="custom-select"
            :options="proveedoresOptions"
            :maxTagCount="responsiveMaxTagCount"
          />
        </div>

        <!-- Modificar opciones -->
        <div class="form-group">
          <label>Modificar opciones :</label>
          <a-select
            v-model:value="formConfig.opciones"
            mode="multiple"
            placeholder="Seleccionar opción"
            class="custom-select"
            :options="opcionesOptions"
            :maxTagCount="responsiveMaxTagCount"
          />
        </div>

        <!-- Modificar ciudad -->
        <div class="form-group">
          <label>Modificar ciudad</label>
          <a-select
            v-model:value="formConfig.ciudad"
            mode="multiple"
            placeholder="Seleccionar ciudad"
            class="custom-select"
            :options="ciudadOptions"
            :maxTagCount="responsiveMaxTagCount"
          />
        </div>
      </div>

      <template #footer>
        <div class="drawer-footer">
          <button class="btn-siguiente">Siguiente</button>
        </div>
      </template>
    </a-drawer>
  </div>
</template>

<script setup lang="ts">
  import { ref } from 'vue';

  defineOptions({ name: 'Step4Container' });

  const dummyData = ref([
    {
      id: '1',
      codigo: 'HD123',
      nombre: 'Traslado Ope Propia',
      subtitulo: 'Tf Traslado - Diez Ases',
      producto: false,
      proveedores: false,
      opciones: false,
      ciudad: false,
    },
    {
      id: '2',
      codigo: 'HD007',
      nombre: 'Entrada',
      subtitulo: 'Museo - Museo Lima',
      producto: true,
      proveedores: true,
      opciones: true,
      ciudad: true,
    },
    {
      id: '3',
      codigo: 'HD008',
      nombre: 'Almuerzo',
      subtitulo: 'Restaurant - Mangos',
      producto: false,
      proveedores: false,
      opciones: false,
      ciudad: true,
    },
    {
      id: '4',
      codigo: 'HD123',
      nombre: 'Traslado Ope Propia',
      subtitulo: 'Tf Traslado - Diez Ases',
      producto: true,
      proveedores: true,
      opciones: true,
      ciudad: true,
    },
  ]);

  const hasActiveSwitch = (item: any) => {
    return item.producto || item.proveedores || item.opciones || item.ciudad;
  };

  const isConfigDrawerVisible = ref(false);
  const responsiveMaxTagCount = ref('responsive');

  const formConfig = ref({
    producto: [] as string[],
    proveedores: [] as string[],
    opciones: [] as string[],
    ciudad: [] as string[],
  });

  const productoOptions = ref([
    { label: 'City Tour', value: 'city_tour' },
    { label: 'Tour bicileta', value: 'tour_bicicleta' },
    { label: 'Degustación', value: 'degustacion' },
  ]);

  const proveedoresOptions = ref([
    { label: 'Museo Lima', value: 'museo_lima' },
    { label: 'Bicicletas Perú', value: 'bicicletas_peru' },
    { label: 'Mangos', value: 'mangos' },
  ]);

  const opcionesOptions = ref([
    { label: 'Opción 2', value: 'opcion_2' },
    { label: 'Opción 3', value: 'opcion_3' },
    { label: 'Opción 4', value: 'opcion_4' },
  ]);

  const ciudadOptions = ref([
    { label: 'Lima', value: 'lima' },
    { label: 'Trujillo', value: 'trujillo' },
    { label: 'Ica', value: 'ica' },
    { label: 'Nazca', value: 'nazca' },
  ]);

  const openConfigDrawer = (item: any) => {
    if (!hasActiveSwitch(item)) return;

    // Set initial values based on figma dummy image
    formConfig.value.producto = ['Entrada'];
    formConfig.value.proveedores = ['Entrada'];
    formConfig.value.opciones = ['Opción 1'];
    formConfig.value.ciudad = ['Lima', 'Ica', 'Nazca'];

    // In a real app we would map this item specifically, but for now we also need to have the defaults exist in the options to display correctly:
    if (!productoOptions.value.some((o) => o.value === 'Entrada'))
      productoOptions.value.push({ label: 'Entrada', value: 'Entrada' });
    if (!proveedoresOptions.value.some((o) => o.value === 'Entrada'))
      proveedoresOptions.value.push({ label: 'Entrada', value: 'Entrada' });
    if (!opcionesOptions.value.some((o) => o.value === 'Opción 1'))
      opcionesOptions.value.push({ label: 'Opción 1', value: 'Opción 1' });
    if (!ciudadOptions.value.some((o) => o.value === 'Lima'))
      ciudadOptions.value.push({ label: 'Lima', value: 'Lima' });
    if (!ciudadOptions.value.some((o) => o.value === 'Ica'))
      ciudadOptions.value.push({ label: 'Ica', value: 'Ica' });
    if (!ciudadOptions.value.some((o) => o.value === 'Nazca'))
      ciudadOptions.value.push({ label: 'Nazca', value: 'Nazca' });

    isConfigDrawerVisible.value = true;
  };
</script>

<style lang="scss" scoped>
  .step4-container {
    display: flex;
    flex-direction: column;
    gap: 20px;
  }

  .section-title {
    font-size: 18px;
    font-weight: 700;
    color: #2f353a;
  }

  .restrictions-table {
    display: flex;
    flex-direction: column;
    background-color: #fff;
    border: 1px solid #e7e7e7;
    border-radius: 8px;
    overflow: hidden;
  }

  .table-header {
    display: flex;
    background-color: #fbfbfb; // light gray
    border-bottom: 1px solid #e7e7e7;
    padding: 16px 24px;
    font-size: 14px;
    font-weight: 600;
    color: #595959;
  }

  .table-row {
    display: flex;
    border-bottom: 1px solid #e7e7e7;
    padding: 16px 24px;
    align-items: center;

    &:last-child {
      border-bottom: none;
    }
  }

  /* Columns Grid Layout */
  .col-servicios {
    flex: 3;
    display: flex;
    align-items: flex-start;
    gap: 12px;
  }

  .col-switch {
    flex: 1;
    display: flex;
    justify-content: center;
    align-items: center;
  }

  .col-config {
    flex: 1;
    display: flex;
    justify-content: center;
    align-items: center;
  }

  /* Content Styling */
  .ord-pill {
    background-color: #ededff;
    color: #2e2b9e;
    font-size: 12px;
    font-weight: 600;
    border-radius: 4px;
    padding: 4px 8px;
    display: inline-block;
  }

  .servicio-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
  }

  .servicio-title {
    font-size: 13px;
    font-weight: 600;
    color: #2f353a;
  }

  .servicio-subtitle {
    font-size: 12px;
    color: #8c8c8c;
  }

  .gear-icon {
    font-size: 18px;
    color: #2f353a;
    cursor: pointer;
    transition: color 0.2s;

    &:hover {
      opacity: 0.8;
    }

    &.is-disabled {
      color: #bfbfbf;
      cursor: not-allowed;
      &:hover {
        opacity: 1;
      }
    }
  }

  /* Custom Switch colors based on Figma request */
  :deep(.custom-switch) {
    &.ant-switch-checked {
      background-color: #c8102e;

      &:hover {
        background-color: #a40b24;
      }
    }
  }

  /* Drawer styles */
  .drawer-title-custom {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 16px;
    font-weight: 700;
    color: #2f353a;

    .title-gear-icon {
      font-size: 18px;
    }
  }

  .drawer-content {
    display: flex;
    flex-direction: column;
    gap: 20px;
    padding-top: 8px;
  }

  .form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;

    label {
      font-size: 13px;
      color: #2f353a;
    }

    .custom-select {
      width: 100%;

      :deep(.ant-select-selector) {
        border-radius: 4px;
        border-color: #d9d9d9;
        min-height: 38px;
        padding-top: 2px;
        padding-bottom: 2px;
      }
    }
  }

  .drawer-footer {
    display: flex;
    justify-content: flex-end;

    .btn-siguiente {
      background-color: #c4c4c4;
      color: #fff;
      border: none;
      border-radius: 4px;
      padding: 10px 24px;
      font-size: 14px;
      font-weight: 500;
      cursor: pointer;
      min-width: 120px;

      &:hover {
        background-color: #a6a6a6;
      }
    }
  }
</style>
