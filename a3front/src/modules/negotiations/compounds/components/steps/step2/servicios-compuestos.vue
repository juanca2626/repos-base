<template>
  <div class="servicios-compuestos-section">
    <div class="section-title">Servicios compuestos</div>

    <div class="dias-list">
      <div v-for="(dia, index) in formState.dias" :key="dia.id" class="dia-item">
        <div
          class="dia-header"
          :class="{ 'is-expanded': expandedDias.includes(dia.id) }"
          @click="toggleDia(dia.id)"
        >
          <div class="dia-header-left">
            <font-awesome-icon
              :icon="expandedDias.includes(dia.id) ? ['fas', 'caret-down'] : ['fas', 'caret-right']"
              class="caret-icon"
            />
            <span class="dia-name">{{ dia.nombre }}</span>
          </div>
          <div class="dia-header-right">
            <span class="services-count"
              >{{
                (dia.servicios?.length || 0) > 0 ? dia.servicios?.length || 0 : '10'
              }}
              servicios</span
            >

            <div class="header-actions">
              <a-tooltip title="Agregar servicio">
                <svg
                  class="action-icon add-icon"
                  width="24"
                  height="24"
                  viewBox="0 0 24 24"
                  fill="none"
                  xmlns="http://www.w3.org/2000/svg"
                  @click.stop="addService(dia.id)"
                >
                  <path
                    d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z"
                    stroke="#1284ED"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                  <path
                    d="M12 8V16"
                    stroke="#1284ED"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                  <path
                    d="M8 12H16"
                    stroke="#1284ED"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                </svg>
              </a-tooltip>

              <a-tooltip v-if="formState.dias.length > 1" title="Eliminar día">
                <svg
                  class="action-icon delete-icon-blue"
                  width="24"
                  height="24"
                  viewBox="0 0 24 24"
                  fill="none"
                  xmlns="http://www.w3.org/2000/svg"
                  @click.stop="removeDia(index)"
                >
                  <path
                    d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z"
                    stroke="#1284ED"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                  <path
                    d="M15 9L9 15"
                    stroke="#1284ED"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                  <path
                    d="M9 9L15 15"
                    stroke="#1284ED"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                </svg>
              </a-tooltip>
            </div>
          </div>
        </div>

        <div v-show="expandedDias.includes(dia.id)" class="dia-content">
          <div v-if="dia.servicios.length === 0" class="empty-state">
            Día sin servicios seleccionados
          </div>
          <div v-else class="services-list">
            <div v-for="(servicio, idx) in dia.servicios" :key="servicio.id" class="service-item">
              <div class="service-col service-col-main">
                <font-awesome-icon :icon="['fas', 'bars']" class="drag-icon" />
                <div class="service-index">{{ (idx + 1).toString().padStart(2, '0') }}</div>
                <span class="service-code">{{ servicio.codigo }} - {{ servicio.nombre }}</span>
              </div>

              <div class="service-col service-col-shield">
                <svg
                  v-if="servicio.tieneEscudo"
                  class="shield-icon"
                  width="14"
                  height="15"
                  viewBox="0 0 14 15"
                  fill="none"
                  xmlns="http://www.w3.org/2000/svg"
                >
                  <g clip-path="url(#clip0_23762_95845)">
                    <path
                      d="M12.7559 2.35434L7.50586 0.104344C7.37187 0.0469688 7.14492 0 7 0C6.85508 0 6.63086 0.0469687 6.49687 0.104119L1.24687 2.35412C0.757422 2.56219 0.4375 3.05437 0.4375 3.57469C0.4375 10.8084 5.61094 14.3747 6.97539 14.3747C8.37266 14.3747 13.5379 10.7691 13.5379 3.57469C13.5625 3.05437 13.2426 2.56219 12.7559 2.35434ZM7 12.5578L7.00064 1.83937C7.0008 1.83872 7.00064 1.83937 7.00064 1.83937L11.8104 3.89728C11.6977 8.99156 8.72266 11.7309 7 12.5578Z"
                      fill="#F99500"
                    />
                  </g>
                  <defs>
                    <clipPath id="clip_shield">
                      <rect width="14" height="14.4" fill="white" />
                    </clipPath>
                  </defs>
                </svg>
              </div>

              <div class="service-col service-col-provider">
                <span class="provider-name">{{ servicio.proveedor }}</span>
              </div>

              <div class="service-col service-col-type">
                <span class="type-name">{{ servicio.tipoServicio }}</span>
              </div>

              <div class="service-col service-col-actions">
                <font-awesome-icon
                  :icon="['far', 'trash-can']"
                  class="delete-service-icon"
                  @click="removeService(dia.id, servicio.id)"
                />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
  import { ref } from 'vue';
  import { useCompoundsComposable } from '../../../composables/use-compounds.composable';

  defineOptions({ name: 'ServiciosCompuestos' });

  const { formState } = useCompoundsComposable();

  const expandedDias = ref<string[]>(['1']);

  const toggleDia = (id: string) => {
    const index = expandedDias.value.indexOf(id);
    if (index === -1) {
      expandedDias.value.push(id);
    } else {
      expandedDias.value.splice(index, 1);
    }
  };

  const addService = (diaId: string) => {
    // Placeholder functionality to show UI interaction
    const dia = formState.value.dias.find((d) => d.id === diaId);
    if (dia) {
      const idx = dia.servicios.length + 1;
      dia.servicios.push({
        id: Date.now().toString(),
        codigo: `Código`,
        nombre: 'Nombre del servicio',
        proveedor: 'Nombre del proveedor',
        tipoServicio: 'Tipo de servicio',
        tieneEscudo: idx % 2 !== 0,
      });
      // Auto expand on add
      if (!expandedDias.value.includes(diaId)) {
        expandedDias.value.push(diaId);
      }
    }
  };

  const removeService = (diaId: string, servicioId: string) => {
    const dia = formState.value.dias.find((d) => d.id === diaId);
    if (dia) {
      dia.servicios = dia.servicios.filter((s) => s.id !== servicioId);
    }
  };

  const removeDia = (index: number) => {
    formState.value.dias.splice(index, 1);
  };
</script>

<style lang="scss" scoped>
  .servicios-compuestos-section {
    width: 100%;
    box-sizing: border-box;

    .section-title {
      font-size: 16px;
      font-weight: 700;
      color: #2f353a;
      margin-bottom: 16px;
    }

    .dias-list {
      display: flex;
      flex-direction: column;
      gap: 16px;
    }

    .dia-item {
      display: flex;
      flex-direction: column;
      background-color: #fff;
      border: 1px solid #e7e7e7;
      border-radius: 8px;
    }

    .dia-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 16px 24px;
      cursor: pointer;

      &.is-expanded {
        border-bottom: 1px solid #e7e7e7;
      }
    }

    .dia-header-left {
      display: flex;
      align-items: center;
      gap: 16px;

      .caret-icon {
        color: #595959;
        font-size: 14px;
        width: 14px;
        text-align: center;
      }

      .dia-name {
        font-weight: 600;
        font-size: 14px;
        color: #2f353a;
      }
    }

    .dia-header-right {
      display: flex;
      align-items: center;

      .services-count {
        font-size: 13px;
        color: #8c8c8c;
        margin-right: 24px;
      }

      .header-actions {
        display: flex;
        align-items: center;
        gap: 8px;
      }
    }

    .action-icon {
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      outline: none; // Added to remove focus border

      &:focus,
      &:active,
      &:focus-visible {
        outline: none;
        box-shadow: none;
      }

      &:hover {
        opacity: 0.8;
      }
    }

    .dia-content {
      padding: 0;
    }

    .empty-state {
      padding: 16px;
      color: #8c8c8c;
      font-size: 13px;
    }

    .services-list {
      display: flex;
      flex-direction: column;
    }

    .service-item {
      display: flex;
      align-items: center;
      padding: 12px 24px;
      background-color: #fff;
      border-bottom: 1px solid #e7e7e7;

      &:last-child {
        border-bottom: none;
      }
    }

    .service-col {
      display: flex;
      align-items: center;
    }

    .service-col-main {
      flex: 3.5;
      gap: 16px;
    }

    .service-col-shield {
      flex: 0 0 40px;
      justify-content: center;
    }

    .service-col-provider {
      flex: 3;
    }

    .service-col-type {
      flex: 2;
    }

    .service-col-actions {
      flex: 0 0 32px;
      justify-content: flex-end;
    }

    .drag-icon {
      color: #7e8285;
      cursor: grab;
      font-size: 14px;
      width: 14px;
      text-align: center;
    }

    .service-index {
      background-color: #ededff;
      color: #2e2b9e;
      font-size: 13px;
      font-weight: 700;
      padding: 2px 8px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      min-width: 24px;
    }

    .service-code {
      font-size: 13px;
      font-weight: 700;
      color: #2f353a;
    }

    .provider-name,
    .type-name {
      font-size: 13px;
      color: #7e8285;
    }

    .delete-service-icon {
      color: #1284ed;
      cursor: pointer;
      font-size: 16px;

      &:hover {
        opacity: 0.7;
      }
    }
  }
</style>
