<template>
  <div class="configuracion-section">
    <div class="section-title">Configuración</div>
    <div class="section-divider" />

    <!-- Rango de pasajeros + Resumen -->
    <div class="pasajeros-row">
      <!-- Rango Input -->
      <div class="field-group">
        <div class="field-label-row">
          <label class="field-label">Rango de pasajeros</label>
          <a-tooltip
            title="Ingrese números separados por (-) para grupo de pasajeros o (;) para pasajeros en específico"
            :overlay-style="{ whiteSpace: 'nowrap', maxWidth: 'none' }"
            :overlay-inner-style="{ fontSize: '12px' }"
            placement="topLeft"
          >
            <svg
              width="14"
              height="14"
              viewBox="0 0 14 14"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
              style="display: block; outline: none"
            >
              <g clip-path="url(#clip0_23357_78119)">
                <path
                  d="M7 0C3.13359 0 0 3.13359 0 7C0 10.8664 3.13359 14 7 14C10.8664 14 14 10.8664 14 7C14 3.13359 10.8664 0 7 0ZM7 3.5C7.48316 3.5 7.875 3.89184 7.875 4.375C7.875 4.85816 7.48316 5.25 7 5.25C6.51684 5.25 6.125 4.85898 6.125 4.375C6.125 3.89102 6.51602 3.5 7 3.5ZM8.09375 10.5H5.90625C5.54531 10.5 5.25 10.2074 5.25 9.84375C5.25 9.48008 5.54395 9.1875 5.90625 9.1875H6.34375V7.4375H6.125C5.7627 7.4375 5.46875 7.14355 5.46875 6.78125C5.46875 6.41895 5.76406 6.125 6.125 6.125H7C7.3623 6.125 7.65625 6.41895 7.65625 6.78125V9.1875H8.09375C8.45605 9.1875 8.75 9.48145 8.75 9.84375C8.75 10.2061 8.45742 10.5 8.09375 10.5Z"
                  fill="#2F353A"
                />
              </g>
              <defs>
                <clipPath id="clip0_23357_78119">
                  <rect width="14" height="14" fill="white" />
                </clipPath>
              </defs>
            </svg>
          </a-tooltip>
        </div>
        <a-input
          v-model:value="formState.rangoInput"
          placeholder="Ej: 1-5; 10; 15-20"
          class="rango-input"
          @keyup.enter="addPasajero"
        >
          <template #suffix>
            <button class="add-btn-suffix" @click.stop="addPasajero">
              <font-awesome-icon :icon="['fas', 'plus']" />
            </button>
          </template>
        </a-input>
      </div>

      <!-- Resumen de pasajeros -->
      <div class="field-group field-group--wide">
        <div class="field-label-row">
          <label class="field-label">Resumen de pasajeros ingresados</label>
          <a-tooltip
            title="Ingrese números separados por (-) para grupo de pasajeros o (;) para pasajeros en específico"
            placement="topLeft"
            :overlay-style="{ whiteSpace: 'nowrap', maxWidth: 'none' }"
            :overlay-inner-style="{ fontSize: '12px' }"
          >
            <svg
              width="14"
              height="14"
              viewBox="0 0 14 14"
              fill="none"
              xmlns="http://www.w3.org/2000/svg"
              style="display: block; outline: none"
            >
              <g clip-path="url(#clip0_23357_78119)">
                <path
                  d="M7 0C3.13359 0 0 3.13359 0 7C0 10.8664 3.13359 14 7 14C10.8664 14 14 10.8664 14 7C14 3.13359 10.8664 0 7 0ZM7 3.5C7.48316 3.5 7.875 3.89184 7.875 4.375C7.875 4.85816 7.48316 5.25 7 5.25C6.51684 5.25 6.125 4.85898 6.125 4.375C6.125 3.89102 6.51602 3.5 7 3.5ZM8.09375 10.5H5.90625C5.54531 10.5 5.25 10.2074 5.25 9.84375C5.25 9.48008 5.54395 9.1875 5.90625 9.1875H6.34375V7.4375H6.125C5.7627 7.4375 5.46875 7.14355 5.46875 6.78125C5.46875 6.41895 5.76406 6.125 6.125 6.125H7C7.3623 6.125 7.65625 6.41895 7.65625 6.78125V9.1875H8.09375C8.45605 9.1875 8.75 9.48145 8.75 9.84375C8.75 10.2061 8.45742 10.5 8.09375 10.5Z"
                  fill="#2F353A"
                />
              </g>
              <defs>
                <clipPath id="clip0_23357_78119">
                  <rect width="14" height="14" fill="white" />
                </clipPath>
              </defs>
            </svg>
          </a-tooltip>
        </div>
        <div class="tags-container">
          <a-tag
            v-for="(pasajero, index) in formState.pasajeros"
            :key="index"
            closable
            class="passenger-tag"
            @close="removePasajero(index)"
          >
            {{ pasajero }}
          </a-tag>
        </div>
      </div>
    </div>

    <!-- Incluir staff -->
    <div class="staff-section">
      <div class="staff-label">Incluir staff</div>
      <div class="staff-checkboxes">
        <label class="staff-checkbox-item">
          <a-checkbox v-model:checked="formState.incluirGuia" class="red-checkbox" />
          <span class="checkbox-text">Guía</span>
        </label>
        <label class="staff-checkbox-item">
          <a-checkbox v-model:checked="formState.incluirScort" class="red-checkbox" />
          <span class="checkbox-text">Scort o Tour Conductor</span>
        </label>
        <label class="staff-checkbox-item">
          <a-checkbox v-model:checked="formState.incluirChofer" class="red-checkbox" />
          <span class="checkbox-text">Chofer</span>
        </label>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
  import { useCompoundsComposable } from '../../../composables/use-compounds.composable';

  defineOptions({ name: 'ConfiguracionSection' });

  const { formState, addPasajero, removePasajero } = useCompoundsComposable();
</script>

<style lang="scss" scoped>
  .configuracion-section {
    background: #fff;
    border: 1px solid #e7e7e7;
    border-radius: 8px;
    padding: 20px 24px 24px;
    width: 100%;
    box-sizing: border-box;

    .section-title {
      font-size: 16px;
      font-weight: 700;
      color: #2f353a;
      margin-bottom: 12px;
    }

    .section-divider {
      height: 1px;
      background: #e7e7e7;
      margin-bottom: 20px;
    }

    .pasajeros-row {
      display: flex;
      gap: 16px;
      margin-bottom: 20px;
      align-items: flex-start;
    }

    .field-group {
      display: flex;
      flex-direction: column;
      gap: 6px;
      width: 212px;
      flex-shrink: 0;

      &--wide {
        width: 282px;
      }
    }

    .field-label-row {
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .field-label {
      font-size: 13px;
      font-weight: 500;
      color: #575b5f;
      line-height: 1.4;
    }

    .rango-input {
      height: 38px;
      border-radius: 6px;

      :deep(.ant-input) {
        font-size: 14px;
      }

      :deep(.ant-input-suffix) {
        display: flex;
        align-items: center;
        padding-left: 4px;
      }
    }

    .add-btn-suffix {
      width: 24px;
      height: 24px;
      border-radius: 4px;
      border: none;
      background: transparent;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 14px;
      color: #595959;
      transition: color 0.2s;
      padding: 0;

      &:hover {
        color: #c8102e;
      }
    }

    .tags-container {
      display: flex;
      flex-wrap: wrap;
      gap: 6px;
      min-height: 38px;
      padding: 6px 10px;
      border: 1px solid #d9d9d9;
      border-radius: 6px;
      align-items: center;
      background: #fafafa;
    }

    .passenger-tag {
      background: #f0f0f0;
      border: 1px solid #d9d9d9;
      color: #2f353a;
      font-size: 13px;
      border-radius: 4px;
      display: flex;
      align-items: center;
      gap: 4px;
      padding: 2px 8px;
      margin: 0;

      :deep(.ant-tag-close-icon) {
        color: #8c8c8c;
        font-size: 11px;
      }
    }

    /* Staff section */
    .staff-section {
      margin-top: 4px;
    }

    .staff-label {
      font-size: 13px;
      font-weight: 500;
      color: #575b5f;
      margin-bottom: 10px;
    }

    .staff-checkboxes {
      display: flex;
      gap: 20px;
      flex-wrap: wrap;
      align-items: center;
    }

    .staff-checkbox-item {
      display: flex;
      align-items: center;
      gap: 8px;
      cursor: pointer;
    }

    .checkbox-text {
      font-size: 14px;
      color: #2f353a;
    }

    /* Checkboxes estilo planes tarifarios — tamaño 18x18 y rojo #cb202d */
    :deep(.red-checkbox) {
      display: flex;
      align-items: center;

      .ant-checkbox-inner {
        width: 18px;
        height: 18px;
        border-radius: 3px;

        &::after {
          width: 5.5px;
          height: 9px;
          left: 26%;
          top: 46%;
        }
      }

      .ant-checkbox-checked .ant-checkbox-inner {
        background-color: #cb202d;
        border-color: #cb202d;
      }

      .ant-checkbox-checked::after {
        border-color: #cb202d;
      }

      &:hover .ant-checkbox-inner,
      .ant-checkbox:hover .ant-checkbox-inner {
        border-color: #cb202d;
      }
    }

    :deep(.ant-checkbox-checked .ant-checkbox-inner) {
      background-color: #cb202d;
      border-color: #cb202d;
    }
  }
</style>
