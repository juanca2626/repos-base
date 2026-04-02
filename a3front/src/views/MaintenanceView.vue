<template>
  <a-layout class="completed-layout">
    <img
      src="https://lito.pe/landing/assets/images/logo-lito.jpg"
      style="max-height: 80px"
      class="mb-3"
    />
    <a-result
      status="500"
      :title="t('global.error.500.title')"
      :sub-title="t('global.error.500.subtitle')"
    >
      <template #extra>
        <a-button v-on:click="returnBack()">
          <font-awesome-icon :icon="['fas', 'arrow-left']" size="lg" />
        </a-button>
        <a-button type="primary" v-on:click="returnToHome()">
          <font-awesome-icon :icon="['fas', 'house']" size="lg" />
        </a-button>
      </template>
    </a-result>
  </a-layout>
</template>

<script setup>
  import { useI18n } from 'vue-i18n';
  const { t } = useI18n({
    useScope: 'global',
  });

  const returnToHome = () => {
    window.location.href = window.url_app;
  };

  const returnBack = () => {
    window.history.back(); // equivalente a history.go(-1)
  };
</script>

<style scoped>
  .completed-layout {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .a-result {
    max-width: 600px;
  }
</style>
