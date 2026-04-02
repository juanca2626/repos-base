<template>
  <div class="summary-step-container">
    <!-- Header -->
    <div class="summary-header">
      <svg
        width="24"
        height="24"
        viewBox="0 0 24 24"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
      >
        <g clip-path="url(#clip0_19180_64445)">
          <path
            d="M15.7031 8.20312L10.5 13.4109L8.25469 11.2031C7.81523 10.7637 7.10344 10.7637 6.66375 11.2031C6.22406 11.6426 6.2243 12.3544 6.66375 12.7941L9.66375 15.7941C9.92344 16.0172 10.2094 16.125 10.5 16.125C10.7906 16.125 11.0756 16.0151 11.2955 15.7954L17.2955 9.79542C17.7349 9.35597 17.7349 8.64417 17.2955 8.20448C16.856 7.7648 16.1437 7.76719 15.7031 8.20312ZM12 0C5.37188 0 0 5.37188 0 12C0 18.6281 5.37188 24 12 24C18.6281 24 24 18.6281 24 12C24 5.37188 18.6281 0 12 0ZM12 21.75C6.62344 21.75 2.25 17.3761 2.25 12C2.25 6.62391 6.62344 2.25 12 2.25C17.3766 2.25 21.75 6.62391 21.75 12C21.75 17.3761 17.3766 21.75 12 21.75Z"
            fill="#1284ED"
          />
        </g>
        <defs>
          <clipPath id="clip0_19180_64445">
            <rect width="24" height="24" fill="white" />
          </clipPath>
        </defs>
      </svg>
      <h2 class="summary-title">Resumen de plan tarifario</h2>
    </div>

    <div class="summary-content">
      <!-- Left Column: Periodos y Precios -->
      <div class="summary-left">
        <div class="card-panel">
          <h3 class="panel-title">Periodos y Precios Configurados</h3>
          <div class="table-container">
            <table class="custom-table">
              <thead>
                <tr>
                  <th>Tipo</th>
                  <th>Periodo</th>
                  <th>Adulto</th>
                  <th>Niño</th>
                  <th>Infante</th>
                  <th>Estado</th>
                  <th>Cupos</th>
                  <th>Release</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, index) in periods" :key="index">
                  <td>
                    <span class="type-badge">Periodos</span>
                  </td>
                  <td class="period-text">{{ item.period }}</td>
                  <td class="price-text">{{ item.adult }}</td>
                  <td class="price-text">
                    {{ item.child }} <span class="discount">({{ item.childDiscount }})</span>
                  </td>
                  <td class="infant-text">Gratis</td>
                  <td class="status-cell">
                    <svg
                      width="18"
                      height="18"
                      viewBox="0 0 18 18"
                      fill="none"
                      xmlns="http://www.w3.org/2000/svg"
                    >
                      <g clip-path="url(#clip0_19180_71549)">
                        <path
                          d="M9 0.5C13.695 0.5 17.5 4.30505 17.5 9C17.5 13.695 13.695 17.5 9 17.5C4.30505 17.5 0.5 13.695 0.5 9C0.5 4.30505 4.30505 0.5 9 0.5ZM9 1.1875C4.69142 1.1875 1.1875 4.6918 1.1875 9C1.1875 13.3082 4.69142 16.8125 9 16.8125C13.3086 16.8125 16.8125 13.3082 16.8125 9C16.8125 4.6918 13.3086 1.1875 9 1.1875ZM12.1289 6.50781C12.266 6.37222 12.4852 6.37384 12.6182 6.50684C12.7526 6.64142 12.7524 6.85893 12.6182 6.99316L8.11816 11.4932C8.05023 11.561 7.9649 11.5938 7.875 11.5938C7.79043 11.5938 7.69159 11.5646 7.58008 11.4707L5.35156 9.24219C5.21694 9.10749 5.21736 8.88999 5.35156 8.75586C5.48616 8.62157 5.70369 8.62165 5.83789 8.75586L5.84082 8.75879L7.52441 10.415L7.87793 10.7627L8.22852 10.4111L12.1289 6.50781Z"
                          fill="black"
                          stroke="#00A25B"
                        />
                      </g>
                      <defs>
                        <clipPath id="clip0_19180_71549">
                          <rect width="18" height="18" fill="white" />
                        </clipPath>
                      </defs>
                    </svg>
                  </td>
                  <td class="cupos-cell">
                    <div class="cell-content">
                      <font-awesome-icon :icon="['fas', 'calendar-alt']" class="icon-grey" />
                      {{ item.cupos }}
                    </div>
                  </td>
                  <td class="release-cell">
                    <div class="cell-content" v-if="item.release">
                      <font-awesome-icon :icon="['fas', 'user-clock']" class="icon-grey" />
                      {{ item.release }}
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Right Column: Validaciones -->
      <div class="summary-right">
        <div class="validation-panel">
          <div class="validation-header">
            <div class="validation-title-wrapper">
              <svg
                width="20"
                height="20"
                viewBox="0 0 20 20"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M10 0C4.48 0 0 4.48 0 10C0 15.52 4.48 20 10 20C15.52 20 20 15.52 20 10C20 4.48 15.52 0 10 0ZM8 15L3 10L4.41 8.59L8 12.17L15.59 4.58L17 6L8 15Z"
                  fill="#1284ED"
                />
              </svg>
              <h3 class="panel-title">Validaciones Detectadas</h3>
            </div>
            <div class="validation-counts">
              <span class="count-success">
                <svg
                  width="16"
                  height="16"
                  viewBox="0 0 16 16"
                  fill="none"
                  xmlns="http://www.w3.org/2000/svg"
                >
                  <circle cx="8" cy="8" r="7" stroke="#00C851" stroke-width="1.5" />
                  <path
                    d="M4.5 8.5L6.5 10.5L11.5 5.5"
                    stroke="#00C851"
                    stroke-width="1.5"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                </svg>
                3
              </span>
              <span class="count-warning">
                <svg
                  width="16"
                  height="16"
                  viewBox="0 0 16 16"
                  fill="none"
                  xmlns="http://www.w3.org/2000/svg"
                >
                  <circle cx="8" cy="8" r="7" stroke="#FFBB33" stroke-width="1.5" />
                  <path d="M8 4V9" stroke="#FFBB33" stroke-width="1.5" stroke-linecap="round" />
                  <circle cx="8" cy="11.5" r="1" fill="#FFBB33" />
                </svg>
                2
              </span>
            </div>
          </div>

          <div class="validation-list">
            <div
              v-for="(val, index) in validations"
              :key="index"
              class="validation-item"
              :class="val.type"
            >
              <div class="validation-icon">
                <svg
                  v-if="val.type === 'success'"
                  width="16"
                  height="16"
                  viewBox="0 0 16 16"
                  fill="none"
                  xmlns="http://www.w3.org/2000/svg"
                >
                  <circle cx="8" cy="8" r="7" stroke="#00C851" stroke-width="1.5" />
                  <path
                    d="M4.5 8.5L6.5 10.5L11.5 5.5"
                    stroke="#00C851"
                    stroke-width="1.5"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                </svg>
                <svg
                  v-else
                  width="16"
                  height="16"
                  viewBox="0 0 16 16"
                  fill="none"
                  xmlns="http://www.w3.org/2000/svg"
                >
                  <circle cx="8" cy="8" r="7" stroke="#FFBB33" stroke-width="1.5" />
                  <path d="M8 4V9" stroke="#FFBB33" stroke-width="1.5" stroke-linecap="round" />
                  <circle cx="8" cy="11.5" r="1" fill="#FFBB33" />
                </svg>
              </div>
              <span class="validation-text">{{ val.text }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Confirmation Section -->
    <div class="confirmation-section">
      <div class="confirmation-card">
        <label class="checkbox-container">
          <input type="checkbox" v-model="confirmed" />
          <span class="checkmark"></span>
          <span class="confirmation-title">Confirmo que:</span>
        </label>
        <ul class="confirmation-list">
          <li>Los precios son correctos para cada período</li>
          <li>La validez anual está completamente definida</li>
          <li>Los períodos no se superponen</li>
          <li>Los cupos están asignados correctamente</li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
  defineOptions({
    name: 'SummaryStep',
  });

  /* const confirmed = ref(false); */
  const confirmed = defineModel<boolean>('confirmed', { default: false });

  const periods = [
    {
      period: '01/01/2027 - 31/12/2027',
      adult: '$184.50',
      child: '$138.50',
      childDiscount: '25%',
      cupos: '5',
      release: '48 horas',
    },
    {
      period: '01/01/2027 - 31/12/2027',
      adult: '$150.50',
      child: '$115.50',
      childDiscount: '25%',
      cupos: '2',
      release: '10 días',
    },
    {
      period: '01/01/2027 - 31/12/2027',
      adult: '$190.50',
      child: '$159.50',
      childDiscount: '25%',
      cupos: '0',
      release: null,
    },
    {
      period: '01/01/2027 - 31/12/2027',
      adult: '$145.50',
      child: '$148.50',
      childDiscount: '25%',
      cupos: null,
      release: null,
    },
  ];

  const validations = [
    { type: 'success', text: 'Todos los períodos del año cubiertos' },
    { type: 'success', text: 'Precios coherentes por temporada' },
    { type: 'success', text: 'Descuentos aplicados correctamente' },
    { type: 'warning', text: 'Noviembre tiene días bloqueados (sin cupos)' },
    { type: 'warning', text: 'Diciembre sin tarifa especial definida' },
  ];

  defineExpose({
    confirmed,
  });
</script>

<style lang="scss" scoped>
  .summary-step-container {
    font-family: 'Inter', sans-serif;
  }

  .summary-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 24px;
    background: white; /* Or transparent depending on parent */
    padding: 16px;
    border-radius: 8px;
    /* box-shadow: 0 1px 3px rgba(0,0,0,0.1); Optional, based on design context */
  }

  .summary-title {
    font-weight: 700;
    font-size: 24px !important;
    line-height: 32px;
    color: #2f353a;
    margin: 0;
  }

  .summary-content {
    display: flex;
    gap: 24px;
    margin-bottom: 24px;
  }

  .summary-left {
    flex: 1;
  }

  .summary-right {
    width: 320px; /* Fixed width for validation panel */
  }

  .card-panel {
    background: #ffffff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 24px;
    height: 100%;
  }

  .panel-title {
    font-weight: 600;
    font-size: 16px !important;
    line-height: 20px !important;
    color: #2f353a;
    margin-bottom: 16px;
    margin-top: 0;
  }

  /* Table Styles */
  .table-container {
    width: 100%;
    overflow-x: auto;
  }

  .custom-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0 12px; /* Row spacing */
  }

  .custom-table th {
    text-align: left;
    font-weight: 600;
    font-size: 14px;
    color: #2f353a;
    padding: 12px 16px; /* Increased horizontal padding */
    border-bottom: 1px solid #e0e0e0;
    white-space: nowrap; /* Prevent headers from wrapping */
  }

  .custom-table td {
    padding: 16px; /* Match header padding */
    vertical-align: middle;
    font-size: 14px;
    color: #575b5f;
    border-bottom: none;
  }

  /* Periodos items styling */
  .type-badge {
    background: #f5f5f5;
    border-radius: 4px;
    padding: 4px 8px;
    font-size: 12px;
    font-weight: 600;
    color: #2f353a;
  }

  .period-text {
    color: #babcbd !important; /* Force overrides */
  }

  .price-text {
    font-weight: 500;
    color: #2f353a;
  }

  .infant-text {
    color: #babcbd;
  }

  .discount {
    color: #babcbd;
    font-size: 12px;
  }

  .status-cell {
    text-align: center;
  }

  .cupos-cell,
  .release-cell {
    color: #babcbd;
  }

  .cell-content {
    display: flex;
    align-items: center;
    gap: 2px; /* Very tight spacing */
  }

  .icon-grey {
    color: #babcbd;
    margin: 0 !important;
    padding: 0 !important;
  }

  /* Validation Panel */
  .validation-panel {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 24px;
    height: 100%;
  }

  .validation-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
  }

  .validation-title-wrapper {
    display: flex;
    align-items: center;
    gap: 8px;

    .panel-title {
      margin-bottom: 0;
      font-size: 14px;
    }
  }

  .validation-counts {
    display: flex;
    gap: 8px;
    font-size: 12px;
    font-weight: 600;
  }

  .count-success {
    display: flex;
    align-items: center;
    gap: 4px;
    color: #00c851;
  }

  .count-warning {
    display: flex;
    align-items: center;
    gap: 4px;
    color: #ffbb33;
  }

  .validation-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
  }

  .validation-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 12px;
    border-radius: 6px;
    font-size: 13px;
    line-height: 20px;
  }

  .validation-item.success {
    background: #f2fcf5;
    color: #2f353a;
  }

  .validation-item.warning {
    background: #fffcf2;
    color: #2f353a;
  }

  .validation-icon {
    flex-shrink: 0;
    margin-top: 2px;
  }

  /* Confirmation Section */
  .confirmation-section {
    background: white;
    border-radius: 8px;
    /* border: 1px solid #E0E0E0; */ /* Image shows it as a separate white card maybe? */
  }

  .confirmation-card {
    background: #ffffff;
    border-radius: 8px;
    padding: 24px;
  }

  /* Custom Checkbox */
  .checkbox-container {
    display: flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
    user-select: none;
    margin-bottom: 12px;
  }

  .checkbox-container input {
    position: absolute;
    opacity: 0;
    cursor: pointer;
    height: 0;
    width: 0;
  }

  .checkmark {
    height: 18px; /* Match SVG size */
    width: 18px;
    background-color: #fff;
    border: 1px solid #1284ed; /* Thinner border */
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    transition: all 0.2s;
  }

  /* When the checkbox is checked, remove border color if icon covers it, or keep it.
     The SVG provided has its own stroke, so we might want to just show the SVG.
     The user said "es radiocheck", meaning it looks like a radio but acts like a check?
     Or just rounded square.
     The SVG provided has a stroke #00A25B (Green?) but previous was blue.
     Let's use the SVG provided.
  */
  /* When the checkbox is checked, we want the SVG to show.
     The SVG has a white background in the clip path? No, fill='white' in clipPath doesn't render.
     The SVG itself has no background rect, just the path.
     The user wants the border to be the same 1px solid #1284ed when checked?
     Or maybe the SVG covers everything?
     The SVG is 18x18. The container is 18x18.
     If we make background transparent, we see through.
     Let's keep the white background and border, and just overlay the SVG check.
  */
  .checkbox-container input:checked ~ .checkmark {
    background-color: #fff;
    border: 1px solid #1284ed;
  }

  /* Create the checkmark/indicator */
  .checkmark:after {
    content: '';
    position: absolute;
    width: 18px;
    height: 18px;
    /* The SVG provided by user, updated with stroke="#1284ed" (blue) */
    background-image: url("data:image/svg+xml,%3Csvg width='18' height='18' viewBox='0 0 18 18' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cg clip-path='url(%23clip0_19180_64734)'%3E%3Cpath d='M9 0.5C13.695 0.5 17.5 4.30505 17.5 9C17.5 13.695 13.695 17.5 9 17.5C4.30505 17.5 0.5 13.695 0.5 9C0.5 4.30505 4.30505 0.5 9 0.5ZM9 1.1875C4.69142 1.1875 1.1875 4.6918 1.1875 9C1.1875 13.3082 4.69142 16.8125 9 16.8125C13.3086 16.8125 16.8125 13.3082 16.8125 9C16.8125 4.6918 13.3086 1.1875 9 1.1875ZM12.1289 6.50781C12.266 6.37222 12.4852 6.37384 12.6182 6.50684C12.7526 6.64142 12.7524 6.85893 12.6182 6.99316L8.11816 11.4932C8.05023 11.561 7.9649 11.5938 7.875 11.5938C7.79043 11.5938 7.69159 11.5646 7.58008 11.4707L5.35156 9.24219C5.21694 9.10749 5.21736 8.88999 5.35156 8.75586C5.48616 8.62157 5.70369 8.62165 5.83789 8.75586L5.84082 8.75879L7.52441 10.415L7.87793 10.7627L8.22852 10.4111L12.1289 6.50781Z' fill='black' stroke='%231284ed'/%3E%3C/g%3E%3Cdefs%3E%3CclipPath id='clip0_19180_64734'%3E%3Crect width='18' height='18' fill='white'/%3E%3C/clipPath%3E%3C/defs%3E%3C/svg%3E");
    background-size: contain;
    background-repeat: no-repeat;
    display: none;
  }

  /* Show the checkmark when checked */
  .checkbox-container input:checked ~ .checkmark:after {
    display: block;
  }

  .confirmation-title {
    font-weight: 600;
    font-size: 16px;
    color: #2f353a;
  }

  .confirmation-list {
    list-style: none;
    padding: 0;
    margin: 0 0 0 32px; /* Indent under title */
  }

  .confirmation-list li {
    position: relative;
    padding-left: 12px;
    margin-bottom: 8px;
    color: #7e8285; /* Grey text */
    font-size: 14px;
  }

  .confirmation-list li::before {
    content: '•';
    position: absolute;
    left: 0;
    color: #7e8285;
    font-weight: bold;
  }
</style>
