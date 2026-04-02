<template>
  <div class="services-items">
    <a-row class="pb-4">
      <a-col :span="4">
        <p class="text-selected">Has seleccionado</p>
      </a-col>
      <a-col :span="20">
        <a-row :gutter="[16, 8]">
          <a-col :span="12" v-if="isVisible">
            <div
              class="item-selected item-selected-default d-flex justify-content-between align-items-center rounded"
            >
              <font-awesome-icon class="item-selected-icon" icon="fa-solid fa-file-import" />
              <p class="item-selected-title">Lunch at Café del Museo Larco Option 2</p>
              <span class="item-selected-divider"> | </span>
              <div>
                <p class="item-selected-span">Tarifa regular</p>
                <div class="d-flex justify-content-between align-items-center">
                  <div class="item-selected-price">
                    <font-awesome-icon class="pr-1" icon="fa-solid fa-dollar-sign" />
                    <b>100</b>
                  </div>
                  <a-tag class="ant-tag-ok">OK</a-tag>
                </div>
              </div>
              <div>
                <span class="ml-2 text-dark-gray cursor-pointer" @click="removeItem()">
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="16"
                    height="16"
                    fill="currentColor"
                    class="bi bi-x-lg d-flex"
                    viewBox="0 0 16 16"
                  >
                    <path
                      d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"
                    />
                  </svg>
                </span>
              </div>
            </div>
          </a-col>
          <a-col :span="12" v-if="isVisible">
            <div
              class="item-selected item-selected-default d-flex justify-content-between align-items-center rounded"
            >
              <font-awesome-icon class="item-selected-icon" icon="fa-solid fa-file-import" />
              <p class="item-selected-title">Lunch at Café del Museo Larco Option 2</p>
              <span class="item-selected-divider"> | </span>
              <div>
                <p class="item-selected-span">Tarifa regular</p>
                <div class="d-flex justify-content-between align-items-center">
                  <div class="item-selected-price">
                    <font-awesome-icon class="pr-1" icon="fa-solid fa-dollar-sign" />
                    <b>100</b>
                  </div>
                  <a-tag class="ant-tag-ok">OK</a-tag>
                </div>
              </div>
              <div>
                <span class="ml-2 text-dark-gray cursor-pointer" @click="removeItem()">
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="16"
                    height="16"
                    fill="currentColor"
                    class="bi bi-x-lg d-flex"
                    viewBox="0 0 16 16"
                  >
                    <path
                      d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"
                    />
                  </svg>
                </span>
              </div>
            </div>
          </a-col>
          <a-col :span="12" v-if="isVisible">
            <div
              class="item-selected item-selected-default d-flex justify-content-between align-items-center rounded"
            >
              <font-awesome-icon class="item-selected-icon" icon="fa-solid fa-file-import" />
              <p class="item-selected-title">Lunch at Café del Museo Larco Option 2</p>
              <span class="item-selected-divider"> | </span>
              <div>
                <p class="item-selected-span">Tarifa regular</p>
                <div class="d-flex justify-content-between align-items-center">
                  <div class="item-selected-price">
                    <font-awesome-icon class="pr-1" icon="fa-solid fa-dollar-sign" />
                    <b>100</b>
                  </div>
                  <a-tag class="ant-tag-ok">OK</a-tag>
                </div>
              </div>
              <div>
                <span class="ml-2 text-dark-gray cursor-pointer" @click="removeItem()">
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="16"
                    height="16"
                    fill="currentColor"
                    class="bi bi-x-lg d-flex"
                    viewBox="0 0 16 16"
                  >
                    <path
                      d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"
                    />
                  </svg>
                </span>
              </div>
            </div>
          </a-col>
          <a-col :span="12" v-if="isVisible">
            <div
              class="item-selected item-selected-default d-flex justify-content-between align-items-center rounded"
            >
              <font-awesome-icon class="item-selected-icon" icon="fa-solid fa-file-import" />
              <p class="item-selected-title">Lunch at Café del Museo Larco Option 2</p>
              <span class="item-selected-divider"> | </span>
              <div>
                <p class="item-selected-span">Tarifa regular</p>
                <div class="d-flex justify-content-between align-items-center">
                  <div class="item-selected-price">
                    <font-awesome-icon class="pr-1" icon="fa-solid fa-dollar-sign" />
                    <b>100</b>
                  </div>
                  <a-tag class="ant-tag-ok">OK</a-tag>
                </div>
              </div>
              <div>
                <span class="ml-2 text-dark-gray cursor-pointer" @click="removeItem()">
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="16"
                    height="16"
                    fill="currentColor"
                    class="bi bi-x-lg d-flex"
                    viewBox="0 0 16 16"
                  >
                    <path
                      d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"
                    />
                  </svg>
                </span>
              </div>
            </div>
          </a-col>
        </a-row>
      </a-col>
    </a-row>
    <a-alert
      message="Agregar servicios"
      description="Al presionar agregar se añadirán al file los servicios seleccionados que no tienen comunicación e irá al paso 2 para servicios con comunicación."
      type="info"
      show-icon
    />
    <div class="price-total">
      <p class="price-total-info">Total de servicios pagar:</p>
      <div class="price-total-amount">
        <font-awesome-icon class="pr-1" icon="fa-solid fa-dollar-sign" />
        <b>100</b>
      </div>
      <div class="actions">
        <a-button type="button" class="btn btn-cancel btn-sm mx-4"> cancelar </a-button>
        <a-button
          type="primary"
          v-on:click="addService()"
          :loading="useFile.isLoadingAsync"
          size="large"
          class="btn btn-primary btn-sm"
        >
          Agregar servicios
        </a-button>
      </div>
    </div>
    <hr class="mb-3 w-100 border-0 height-1" style="background-color: #c4c4c4" />
  </div>
</template>
<script setup>
  import { ref } from 'vue';
  import { useFilesStore } from '@store/files';

  // Variables
  const useFile = useFilesStore();
  const isVisible = ref(true);

  const removeItem = () => {
    isVisible.value = false;
  };
  const addService = () => {
    // implementation of addService function
  };
</script>
