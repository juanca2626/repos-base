<template>
  <div class="generic-image-marketing-component">
    <div v-if="!isEditingContent" class="read-images-section">
      <div class="read-images-header">
        <span class="edit-content-section-title">Imágenes para el servicio</span>
        <!-- agregar una linea aqui -->
        <a-tooltip placement="topLeft" overlay-class-name="images-guidelines-tooltip">
          <template #title>
            <div class="images-tooltip-content">
              <div class="images-tooltip-title">Considerar</div>
              <ul class="images-tooltip-list">
                <li>Usar fotos que describan el servicio o experiencia (no el proveedor).</li>
                <li>Formato horizontal.</li>
                <li>Tamaño: 1280 x 720 píxeles, resolución 72 ppi.</li>
                <li>Asegurar buena composición e iluminación.</li>
                <li>No incluir fotos de menores de edad.</li>
                <li>Utilizar solo imágenes con <strong>derechos de uso autorizados</strong>.</li>
              </ul>
            </div>
          </template>
          <IconCircleInfo />
        </a-tooltip>
      </div>
      <div class="edit-content-section-divider"></div>
      <div class="images-gallery">
        <div v-for="(img, index) in previewImages" :key="index" class="image-thumbnail-card">
          <div class="image-thumbnail-wrap">
            <img :src="img.src" :alt="img.name" class="image-thumbnail" />
          </div>
          <p class="image-filename">{{ img.name }}</p>
        </div>
      </div>
    </div>
    <div class="edit-content-section" v-if="isEditingContent">
      <div class="edit-content-section-header">
        <span class="edit-content-section-title">Imágenes para el servicio</span>
        <a-tooltip placement="topLeft" overlay-class-name="images-guidelines-tooltip">
          <template #title>
            <div class="images-tooltip-content">
              <div class="images-tooltip-title">Considerar</div>
              <ul class="images-tooltip-list">
                <li>Usar fotos que describan el servicio o experiencia (no el proveedor).</li>
                <li>Formato horizontal.</li>
                <li>Tamaño: 1280 x 720 píxeles, resolución 72 ppi.</li>
                <li>Asegurar buena composición e iluminación.</li>
                <li>No incluir fotos de menores de edad.</li>
                <li>Utilizar solo imágenes con <strong>derechos de uso autorizados</strong>.</li>
              </ul>
            </div>
          </template>
          <IconCircleInfo />
        </a-tooltip>
      </div>

      <div class="image-upload-area">
        <a-upload-dragger
          v-model:fileList="fileList"
          :multiple="true"
          :before-upload="beforeUpload"
          :custom-request="customRequest"
          :show-upload-list="false"
          accept=".jpg,.jpeg,.png"
        >
          <p class="upload-text">Haz clic aquí o arrastra tus imágenes para subirlos</p>
          <p class="upload-hint">Formato JPG o PNG. Peso máximo de 300KB</p>
        </a-upload-dragger>
      </div>

      <div class="url-section mt-4">
        <p class="url-section-subtitle">También puedes agregar la url de la imagen</p>
        <a-form layout="vertical">
          <a-form-item required>
            <template #label>
              <span>Ingresa la url <span class="required-asterisk">*</span></span>
            </template>
            <a-input v-model:value="imageUrl" placeholder="Ingresar" class="url-input" />
          </a-form-item>
          <a-button type="link" class="add-url-btn" @click="addAnotherUrl">
            <PlusOutlined />
            Agregar otra url
          </a-button>
        </a-form>
      </div>

      <div class="button-actions mt-4">
        <a-button type="primary" size="large" @click="handleSave"> Guardar Datos </a-button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
  import { PlusOutlined } from '@ant-design/icons-vue';
  import IconCircleInfo from '@/modules/negotiations/products/configuration/icons/IconCircleInfo.vue';
  import { useGenericImageMarketingComposable } from '../composables/useGenericImageMarketingComposable';

  const {
    fileList,
    imageUrl,
    isEditingContent,
    previewImages,
    beforeUpload,
    customRequest,
    addAnotherUrl,
    handleSave,
  } = useGenericImageMarketingComposable();
</script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .generic-image-marketing-component {
    max-width: 100%;
  }

  .read-images-section {
    display: flex;
    flex-direction: column;
    gap: 16px;
  }

  .read-images-header {
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .images-gallery {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 50px;
  }

  .image-thumbnail-card {
    display: flex;
    flex-direction: column;
    gap: 8px;
  }

  .image-thumbnail-wrap {
    width: 100%;
    aspect-ratio: 16 / 10;
    border-radius: 8px;
    overflow: hidden;
    background-color: #f0f0f0;
  }

  .image-thumbnail {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .image-filename {
    margin: 0;
    font-size: 14px;
    color: #595959;
  }

  .edit-content-section {
    display: flex;
    flex-direction: column;
    gap: 16px;
  }

  .edit-content-section-header {
    display: flex;
    align-items: center;
    margin-bottom: 15px;
  }

  .edit-content-section-title {
    font-size: 20px;
    font-weight: 600;
    color: $color-black;
  }

  .edit-content-section-divider {
    width: 100%;
    height: 1px;
    background-color: #e7e7e7;
    display: block;
  }

  .image-upload-area {
    background: #ebeff2;
    border-radius: 8px;
    padding: 20px;

    :deep(.ant-upload-drag) {
      border: 1.5px dashed #1791cf;
      border-radius: 8px;
      background-color: #f0faff;

      &:hover {
        border-color: #1791cf;
        background-color: #f0faff;
      }
    }

    :deep(.ant-upload-drag-container) {
      padding: 60px 20px;
    }

    :deep(.ant-upload-drag .ant-upload-btn) {
      padding-top: 40px;
      padding-bottom: 40px;
    }
  }

  .upload-text {
    margin: 0 0 8px;
    font-size: 14px;
    color: #292b2e;
    font-weight: 600;
  }

  .upload-hint {
    margin: 0;
    font-size: 12px;
    color: #8c8c8c;
  }

  .url-section-subtitle {
    font-size: 14px;
    font-weight: 600;
    color: $color-black;
    margin-bottom: 16px;
  }

  .url-input {
    max-width: 100%;
  }

  .url-section {
    .required-asterisk {
      color: $color-primary-strong;
      margin-left: 4px;
      font-weight: bold;
    }
  }

  .add-url-btn {
    padding: 0;
    height: auto;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    color: #1890ff;
    font-size: 14px;
  }

  .button-actions {
    margin-top: 24px;
  }

  .mt-4 {
    margin-top: 16px;
  }
</style>

<style lang="scss">
  .images-guidelines-tooltip {
    max-width: 360px !important;

    .ant-tooltip-inner {
      padding: 10px;
      background-color: #262626;
      color: #fff;
      border-radius: 8px;
    }

    .images-tooltip-title {
      font-size: 14px;
      font-weight: 600;
      color: #fff;
      margin-bottom: 12px;
    }

    .images-tooltip-list {
      margin: 0;
      padding-left: 20px;
      font-size: 13px;
      line-height: 1.3;
      color: #fff;

      li {
        margin-bottom: 6px;

        &:last-child {
          margin-bottom: 0;
        }
      }

      strong {
        font-weight: 600;
      }
    }
  }
</style>
