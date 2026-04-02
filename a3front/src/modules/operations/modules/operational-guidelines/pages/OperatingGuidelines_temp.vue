:
<template>
  <div class="module-operations">
    <a-title-section
      title="Pautas Operativas"
      icon="calendar"
      @handlerShowDrawer="handlerShowDrawer($event)"
    />

    <a-form layout="vertical">
      <a-row gutter="16">
        <a-col span="8">
          <a-form-item label="Escoger:">
            <a-radio-group v-model="selectedOption">
              <a-radio-button value="cliente">Cliente</a-radio-button>
              <a-radio-button value="mercado">Mercado</a-radio-button>
              <a-radio-button value="serie">Serie</a-radio-button>
            </a-radio-group>
          </a-form-item>
        </a-col>
        <a-col span="8">
          <a-form-item label="Escoger cliente">
            <a-select v-model="selectedClient" placeholder="Nombre del cliente">
              <a-select-option v-for="client in clients" :key="client" :value="client">
                {{ client }}
              </a-select-option>
            </a-select>
          </a-form-item>
          <a-form-item>
            <a-button @click="clearFilters" type="link" icon="fas fa-broom"
              >Limpiar filtros</a-button
            >
          </a-form-item>
        </a-col>
        <a-col span="8">
          <a-form-item>
            <a-button type="primary" icon="fas fa-search">Buscar</a-button>
          </a-form-item>
        </a-col>
      </a-row>
    </a-form>
    <a-col span="24">
      <a-card>
        <a-row>
          <a-col span="1">
            <div>
              <strong>[Código] Nombre del cliente</strong>
              <a-tag color="purple">Vegetariano</a-tag>
              <a-tag color="blue">LGTB</a-tag>
            </div>
            <a-divider />
            <div>
              <p><i class="fas fa-user"></i> Nombre Apellido (ABC)</p>
              <p><i class="fas fa-user"></i> Nombre Apellido (ABC)</p>
              <p><i class="fas fa-user"></i> Nombre Apellido (ABC)</p>
            </div>
            <a-divider />
            <div>
              <p><i class="fas fa-user"></i> Nombre del contacto</p>
              <p><i class="fas fa-phone"></i> 123 456 789</p>
              <p><i class="fas fa-envelope"></i> mail@correo.com</p>
            </div>
          </a-col>
          <a-col span="4">
            <a-button type="primary" icon="fas fa-plus" style="float: right"
              >Crear nueva pauta</a-button
            >
            <p><a href="#">Ver logo especial</a></p>
          </a-col>
        </a-row>
      </a-card>
    </a-col>
    <a-col span="24">
      <a-card title="Pautas operativas:">
        <a-empty description="No hay coincidencias" />
      </a-card>
    </a-col>
  </div>
</template>

<script setup lang="ts">
  import ATitleSection from '@/components/backend/ATitleSection.vue';
</script>

<style lang="scss" scoped>
  // @import '@/modules/operations/shared/styles/tailwind.css';
</style>
