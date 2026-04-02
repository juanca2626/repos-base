<template>
  <div class="module-negotiations">
    <div class="p-5">
      <router-view></router-view>
    </div>
  </div>
</template>
<script setup lang="ts">
  import { onMounted } from 'vue';
  import { useI18n } from 'vue-i18n';

  const { locale } = useI18n();

  onMounted(() => {
    const savedLang = localStorage.getItem('lang');
    if (savedLang) {
      locale.value = savedLang;
    }
  });
</script>
<style scoped lang="scss"></style>
