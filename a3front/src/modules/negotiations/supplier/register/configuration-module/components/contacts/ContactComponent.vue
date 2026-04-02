<template>
  <div class="custom-tab-content">
    <div class="header-table">
      <div class="title-table">Listado de contactos</div>
      <div>
        <a-button type="primary" class="button-option" @click="showDrawerForm = true">
          <font-awesome-icon icon="fa-solid fa-plus" :style="{ fontSize: '16px' }" />
          Crear contacto
        </a-button>
      </div>
    </div>
    <div>
      <ContactListComponent />
    </div>

    <ContactFormComponent v-model:showDrawerForm="showDrawerForm" />
  </div>
</template>

<script setup lang="ts">
  import { ref } from 'vue';
  import ContactListComponent from '@/modules/negotiations/supplier/register/configuration-module/components/contacts/ContactListComponent.vue';
  import ContactFormComponent from '@/modules/negotiations/supplier/register/configuration-module/components/contacts/ContactFormComponent.vue';

  const showDrawerForm = ref<boolean>(false);
</script>
<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .header-table {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    margin-bottom: 15px;

    .title-table {
      font-size: 14px;
      font-weight: 400;
      color: $color-black-2;
      align-self: flex-end;
    }

    .button-option {
      height: 45px;
      font-weight: 500;
      font-size: 16px;
      line-height: 24px;
      text-align: center;
    }
  }
</style>
