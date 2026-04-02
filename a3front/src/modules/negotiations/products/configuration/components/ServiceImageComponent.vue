<template>
  <div class="service-image">
    <div class="service-image-container">
      <div class="title-container">
        <span class="service-title"> Imágenes para el servicio </span>
        <a-tooltip placement="top">
          <template #title>
            <span class="text-content-tooltip"> Imágenes cargadas desde Marketing </span>
          </template>
          <font-awesome-icon :icon="['fa', 'circle-info']" class="info-icon" />
        </a-tooltip>
      </div>
      <hr class="custom-hr" />

      <div class="body-container">
        <div class="image-container">
          <img src="/images/dots.jpg" alt="dots" class="custom-image" />
          <div class="mt-3">
            <span class="image-name"> Nombre del archivo. jpg </span>
          </div>
        </div>
        <div class="image-container">
          <img src="/images/dots.jpg" alt="dots" class="custom-image" />
          <div class="mt-3">
            <span class="image-name"> Nombre del archivo. jpg </span>
          </div>
        </div>
        <div class="image-container">
          <img src="/images/dots.jpg" alt="dots" class="custom-image" />
          <div class="mt-3">
            <span class="image-name"> Nombre del archivo. jpg </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts"></script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .service-image {
    padding: 0 24px 24px 24px;

    &-container {
      border-radius: 8px;
      border: 1px solid $color-black-4;
      padding: 24px;
    }

    .title-container {
      display: flex;
      align-items: center;
      gap: 10px;
      padding-bottom: 10px;

      .service-title {
        font-size: 20px;
        font-weight: 600;
        color: #212121;
      }
    }

    .text-content-tooltip {
      font-size: 12px;
      font-weight: 500;
    }

    .info-icon {
      width: 16px;
      height: 16px;
    }

    .custom-hr {
      border: none;
      height: 1px;
      background-color: $color-black-4;
    }

    .body-container {
      display: flex;
      align-items: center;
      gap: 20px;
      margin-top: 24px;
    }

    .image-container {
      width: 239px;
      height: 196px;
      padding: 16px;

      .custom-image {
        width: 207px;
        height: 128px;
        border-radius: 8px;
      }

      .image-name {
        font-size: 14px;
        font-weight: 400;
        padding-top: 16px !important;
      }
    }
  }
</style>
