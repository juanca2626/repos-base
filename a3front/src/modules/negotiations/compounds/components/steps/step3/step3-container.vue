<template>
  <div class="step3-container">
    <div class="section-title">Matriz de costos</div>

    <div class="cost-matrix-card">
      <div class="matrix-header">
        <span class="header-title">Nombre del servicio compuesto estructurado</span>
        <a-tooltip
          title="Agregar punto de equilibrio"
          placement="topRight"
          color="#fff"
          :overlayInnerStyle="{
            color: '#2f353a',
            fontWeight: '500',
            boxShadow: '0 2px 8px rgba(0, 0, 0, 0.15)',
          }"
        >
          <svg
            class="action-icon add-icon cursor-pointer"
            @click="isDrawerVisible = true"
            @mousedown.prevent
            style="outline: none"
            width="24"
            height="24"
            viewBox="0 0 24 24"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z"
              stroke="#2f353a"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
            <path
              d="M12 8V16"
              stroke="#2f353a"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
            <path
              d="M8 12H16"
              stroke="#2f353a"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
          </svg>
        </a-tooltip>
      </div>

      <div class="matrix-table">
        <!-- Table Header -->
        <div class="table-header-row">
          <div class="col-ord">Ord.</div>
          <div class="col-servicio">Servicio</div>
          <div class="col-paxes">
            <div class="col-pax" v-for="pax in paxColumns" :key="pax.label">
              <span v-if="pax.icon" class="pax-icon">✈</span>
              {{ pax.label }}
            </div>
          </div>
        </div>

        <!-- Totals Row -->
        <div class="table-totals-row">
          <div class="col-ord"></div>
          <div class="col-servicio"></div>
          <div class="col-paxes">
            <div class="col-pax" v-for="(pax, idx) in paxColumns" :key="idx">
              <div class="total-pill">{{ pax.total }}</div>
            </div>
          </div>
        </div>

        <!-- Data Rows -->
        <div class="table-data-rows">
          <template v-for="(item, index) in dummyData" :key="item.id">
            <div class="data-row" :class="{ 'is-expanded': expandedIds.includes(item.id) }">
              <div class="col-ord">
                <div class="ord-pill">{{ (index + 1).toString().padStart(2, '0') }}</div>
              </div>
              <div class="col-servicio">
                <font-awesome-icon
                  :icon="['fas', expandedIds.includes(item.id) ? 'angle-down' : 'angle-right']"
                  class="arrow-icon cursor-pointer"
                  @click="toggleRow(item.id)"
                />
                <div class="servicio-info">
                  <div class="servicio-title">{{ item.codigo }} - {{ item.nombre }}</div>
                  <div class="servicio-subtitle">{{ item.subtitulo }}</div>
                </div>
              </div>
              <div class="col-paxes">
                <div class="col-pax-value" v-for="(val, vIdx) in item.valores" :key="vIdx">
                  {{ formatValue(val) }}
                </div>
              </div>
            </div>

            <!-- Expanded Details -->
            <div v-if="expandedIds.includes(item.id)" class="expanded-details-container">
              <div v-for="(detail, dIdx) in dummyDetails" :key="dIdx" class="detail-row">
                <div class="col-ord"></div>
                <div class="col-servicio">
                  <div class="detail-title">{{ detail.title }}</div>
                </div>
                <div class="col-paxes">
                  <div class="col-pax-box-wrap" v-for="(val, vIdx) in detail.valores" :key="vIdx">
                    <div
                      class="detail-val-box"
                      :class="{ 'is-computed': detail.computed, 'is-input': detail.isInput }"
                    >
                      {{ val }}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </template>
        </div>
      </div>
    </div>

    <!-- Drawer Punto de Equilibrio -->
    <a-drawer
      v-model:open="isDrawerVisible"
      placement="right"
      :width="420"
      :closable="true"
      class="equilibrium-drawer"
    >
      <template #title>
        <div class="drawer-title-custom">
          <font-awesome-icon :icon="['fas', 'chart-bar']" class="title-icon" />
          <span>Punto de equilibrio</span>
        </div>
      </template>

      <div class="drawer-content">
        <div class="percentage-card">
          <div class="current-percentage">
            <svg
              width="16"
              height="14"
              viewBox="0 0 16 14"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M15.8214 12.0317L9.1555 0.656273C8.64517 -0.218758 7.35731 -0.218758 6.84354 0.656273L0.180799 12.0317C-0.332032 12.9036 0.30671 14.0005 1.33459 14.0005H14.6663C15.6901 14.0005 16.3308 12.9067 15.8214 12.0317ZM7.24918 4.25015C7.24918 3.83607 7.58513 3.50012 7.99921 3.50012C8.41328 3.50012 8.74923 3.83764 8.74923 4.25015V8.25029C8.74923 8.66437 8.41328 9.00032 8.02733 9.00032C7.64138 9.00032 7.24918 8.66593 7.24918 8.25029V4.25015ZM7.99921 12.0004C7.45669 12.0004 7.01667 11.5604 7.01667 11.0179C7.01667 10.4754 7.45637 10.0354 7.99921 10.0354C8.54204 10.0354 8.98174 10.4754 8.98174 11.0179C8.98049 11.5598 8.54297 12.0004 7.99921 12.0004Z"
                fill="#FFCC00"
              />
            </svg>

            <span class="text">Porcentaje actual : </span>
            <span class="value">$ 60,650.00</span>
            <span class="percent">(15%)</span>
          </div>
          <div class="input-group">
            <label>Modificar porcentaje</label>
            <div class="input-wrapper">
              <input type="text" class="custom-input" value="20" />
              <span class="suffix">%</span>
            </div>
          </div>
        </div>

        <div class="drawer-table">
          <div class="table-head">
            <div class="th-pax">Cantidad pax</div>
            <div class="th-cost">Costo total $</div>
          </div>
          <div class="table-body">
            <div v-for="i in 10" :key="i" class="tr" :class="{ highlighted: i === 10 }">
              <div class="td-pax">{{ i }}</div>
              <div class="td-cost">$ 60,650.00</div>
            </div>
          </div>
        </div>
      </div>

      <template #footer>
        <div class="drawer-footer">
          <a-button type="primary" class="save-button"> Guardar Porcentaje </a-button>
        </div>
      </template>
    </a-drawer>
  </div>
</template>

<script setup lang="ts">
  import { ref } from 'vue';

  defineOptions({ name: 'Step3Container' });

  // Dummy data matching screenshot exactly
  const paxColumns = ref([
    { label: 'Pax 1', icon: false, total: '980.00' },
    { label: 'Pax 2', icon: false, total: '920.00' },
    { label: 'Pax 3', icon: false, total: '920.00' },
    { label: 'Pax 4', icon: false, total: '920.00' },
    { label: 'Pax 5', icon: true, total: '920.00' },
    { label: 'Pax 10', icon: true, total: '920.00' },
    { label: 'Pax 15', icon: false, total: '920.00' },
    { label: 'Pax 16', icon: false, total: '920.00' },
  ]);

  const dummyData = ref([
    {
      id: '1',
      codigo: 'HD123',
      nombre: 'Traslado Ope Propia',
      subtitulo: 'Tf Traslado - Diez Ases',
      valores: [320.0, 160.0, 106.665, 80.0, 64.0, 32.0, 21.32, 20.0],
    },
    {
      id: '2',
      codigo: 'HD007',
      nombre: 'Entrada',
      subtitulo: 'Ms Museo - Museo Lima',
      valores: [320.0, 160.0, 106.665, 80.0, 64.0, 32.0, 21.32, 20.0],
    },
    {
      id: '3',
      codigo: 'HD008',
      nombre: 'Propina',
      subtitulo: 'XX xxx - Xxxxxxxx',
      valores: [20, 20, 20, 20, 20, 20, 20, 20],
    },
  ]);

  const expandedIds = ref<string[]>([]);
  const isDrawerVisible = ref(false);

  const toggleRow = (id: string) => {
    if (expandedIds.value.includes(id)) {
      expandedIds.value = expandedIds.value.filter((i) => i !== id);
    } else {
      expandedIds.value.push(id);
    }
  };

  const dummyDetails = ref([
    {
      title: 'Costo unidad',
      isInput: true,
      computed: false,
      valores: [100, 100, 100, 100, 100, 100, 100, 100],
    },
    {
      title: 'Elemento divisor/calculador',
      isInput: true,
      computed: false,
      valores: [1, 2, 3, 4, 5, 10, 15, 16],
    },
    {
      title: 'Costo por pasajero',
      isInput: false,
      computed: true,
      valores: [100, 50, 33.333, 25, 20, 10, 6.666, 6.25],
    },
    {
      title: 'staff guía',
      isInput: true,
      computed: false,
      valores: [110, 110, 110, 110, 110, 110, 110, 110],
    },
    {
      title: 'Elemento divisor/calculador',
      isInput: true,
      computed: false,
      valores: [1, 2, 3, 4, 5, 10, 15, 16],
    },
    {
      title: 'Costo por staff guía',
      isInput: false,
      computed: true,
      valores: [110, 55, 36.666, 27.5, 22, 11, 7.333, 6.875],
    },
    {
      title: 'staff chofer',
      isInput: true,
      computed: false,
      valores: [90, 90, 90, 90, 90, 90, 90, 90],
    },
    {
      title: 'Elemento divisor/calculador',
      isInput: true,
      computed: false,
      valores: [1, 2, 3, 4, 5, 10, 15, 16],
    },
    {
      title: 'Costo por staff chofer',
      isInput: false,
      computed: true,
      valores: [90, 45, 30, 22.5, 18, 9, 6, 5.625],
    },
  ]);

  const formatValue = (val: number) => {
    // Exact formatting to match screenshot based on integer/decimals
    if (val === 106.665) return '106.665';
    if (Number.isInteger(val) && val === 20) return '20';
    if (Number.isInteger(val)) return val.toFixed(2);
    return val.toFixed(2);
  };
</script>

<style lang="scss" scoped>
  .step3-container {
    display: flex;
    flex-direction: column;
    gap: 20px;
  }

  .section-title {
    font-size: 18px;
    font-weight: 700;
    color: #2f353a;
  }

  .cost-matrix-card {
    background-color: #fff;
    border-radius: 8px;
    border: 1px solid #e7e7e7;
    overflow: hidden;
  }

  .matrix-header {
    background-color: #fbfbfb; // Light gray as per screenshot
    padding: 16px 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #e7e7e7;

    .header-title {
      font-weight: 600;
      font-size: 14px;
      color: #2f353a;
    }

    .action-icon {
      cursor: pointer;
      outline: none;
      -webkit-tap-highlight-color: transparent;

      &:hover {
        opacity: 0.8;
      }

      &:focus,
      &:active {
        outline: none;
        box-shadow: none;
      }
    }
  }

  .matrix-table {
    display: flex;
    flex-direction: column;
  }

  /* Grid Layout for Rows */
  .table-header-row,
  .table-totals-row,
  .data-row {
    display: flex;
    padding: 12px 24px;
    border-bottom: 1px solid #e7e7e7;
    align-items: center;
  }

  .data-row:last-child {
    border-bottom: none;
  }

  /* Column Widths */
  .col-ord {
    flex: 0 0 60px;
  }
  .col-servicio {
    flex: 1; /* Takes remaining space */
    min-width: 280px;
    display: flex;
    align-items: flex-start;
  }
  .col-paxes {
    display: flex;
    flex: 2; /* Adjust based on exact proportion needed */
    justify-content: space-between;
  }

  /* Specific column styles */
  .col-pax {
    flex: 1;
    text-align: center;
    font-size: 13px;
    color: #595959;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
  }

  .col-pax-value {
    flex: 1;
    text-align: center;
    font-size: 13px;
    font-weight: 500;
    color: #2f353a;
  }

  .table-header-row {
    background-color: #fff;
    font-size: 13px;
    color: #8c8c8c;
    padding-top: 16px;
    padding-bottom: 12px;
  }

  .table-totals-row {
    background-color: #fff;
    padding-top: 8px;
    padding-bottom: 16px;
  }

  .total-pill {
    background-color: #e2e2e2; // Gray background
    border-radius: 4px;
    padding: 4px 12px;
    font-size: 12px;
    font-weight: 700;
    color: #595959;
    display: inline-block;
  }

  .data-row {
    background-color: #fff;
    padding-top: 16px;
    padding-bottom: 16px;
  }

  .ord-pill {
    background-color: #ededff;
    color: #2e2b9e;
    font-size: 12px;
    font-weight: 600;
    border-radius: 4px;
    padding: 4px 8px;
    display: inline-block;
  }

  .arrow-icon {
    color: #8c8c8c;
    font-size: 12px;
    margin-right: 12px;
    margin-top: 4px;
  }

  .servicio-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
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

  .data-row.is-expanded {
    border-bottom: none;
  }

  .cursor-pointer {
    cursor: pointer;
  }

  .expanded-details-container {
    padding-bottom: 16px;
    border-bottom: 1px solid #e7e7e7;
  }

  .table-data-rows > template:last-child .expanded-details-container {
    border-bottom: none;
  }

  .detail-row {
    display: flex;
    padding: 6px 24px;
    align-items: center;
  }

  .detail-title {
    font-size: 13px;
    color: #595959;
    padding-left: 28px;
  }

  .col-pax-box-wrap {
    flex: 1;
    display: flex;
    justify-content: center;
    align-items: center;
  }

  .detail-val-box {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 32px;
    width: 100%;
    max-width: 72px;
    border-radius: 4px;
    font-size: 13px;
    font-weight: 600;
  }

  .detail-val-box.is-input {
    background-color: #fff;
    border: 1px solid #e7e7e7;
    color: #595959;
  }

  .detail-val-box.is-computed {
    background-color: #ededff;
    color: #2f353a;
  }

  /* Drawer specific styles */
  .drawer-title-custom {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 16px;
    font-weight: 700;
    color: #2f353a;

    .title-icon {
      font-size: 18px;
    }
  }

  .drawer-content {
    display: flex;
    flex-direction: column;
    gap: 20px;
    padding-top: 4px;
  }

  .percentage-card {
    border: 1px solid #e7e7e7;
    border-radius: 6px;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 16px;
  }

  .current-percentage {
    display: flex;
    align-items: center;
    font-size: 13px;

    .warning-icon {
      color: #faad14;
      font-size: 16px;
      margin-right: 6px;
    }

    .text {
      color: #2f353a;
      font-weight: 600;
      margin-right: 4px;
      padding-left: 5px;
    }

    .value {
      color: #595959;
      margin-right: 4px;
    }

    .percent {
      color: #2e2b9e;
      font-weight: 600;
    }
  }

  .input-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
    width: 100%;
    align-items: flex-start;

    label {
      font-size: 12px;
      color: #2f353a;
      text-align: left;
    }

    .input-wrapper {
      position: relative;
      display: flex;
      align-items: center;
      width: 100%;

      .custom-input {
        width: 100%;
        height: 38px;
        border: 1px solid #e7e7e7;
        border-radius: 4px;
        padding: 4px 12px;
        font-size: 14px;
        color: #2f353a;
        outline: none;

        &:focus {
          border-color: #2e2b9e;
          box-shadow: 0 0 0 2px rgba(46, 43, 158, 0.1);
        }
      }

      .suffix {
        position: absolute;
        right: 12px;
        font-weight: 600;
        color: #2f353a;
        font-size: 14px;
      }
    }
  }

  .drawer-table {
    border: 1px solid #e7e7e7;
    border-radius: 6px;
    overflow: hidden;

    .table-head {
      display: flex;
      background-color: #f2f2f2;
      border-bottom: 1px solid #e7e7e7;

      div {
        flex: 1;
        padding: 12px 16px;
        font-size: 13px;
        font-weight: 600;
        color: #595959;
        text-align: center;
      }
      .th-pax {
        border-right: 1px solid #e7e7e7;
      }
    }

    .table-body {
      display: flex;
      flex-direction: column;

      .tr {
        display: flex;
        border-bottom: 1px solid #e7e7e7;

        &:last-child {
          border-bottom: none;
        }

        &.highlighted {
          background-color: #f4f6f8;
        }

        div {
          flex: 1;
          padding: 10px 16px;
          font-size: 13px;
          color: #2f353a;
          text-align: center;
          display: flex;
          align-items: center;
          justify-content: center;
        }

        .td-pax {
          font-weight: 500;
          border-right: 1px solid #e7e7e7;
        }

        .td-cost {
          font-weight: 600;
        }
      }
    }
  }

  .drawer-footer {
    display: flex;
    justify-content: center;
    margin-top: 8px;

    .save-button {
      width: 100%;
      height: 48px;
      padding: 0 40px;
      border-radius: 5px;
      font-size: 14px;
      font-weight: 500;
      background-color: #bd0d12;
      border-color: #bd0d12;
      color: #fff;
      cursor: pointer;

      &:hover,
      &:focus,
      &:active {
        background-color: #bd0d12;
        border-color: #bd0d12;
        color: #fff;
      }

      &:disabled {
        background-color: #bfbfbf;
        border-color: #bfbfbf;
        color: #fff;
        cursor: not-allowed;

        &:hover,
        &:focus {
          background-color: #bfbfbf;
          border-color: #bfbfbf;
          color: #fff;
        }
      }
    }
  }
</style>
