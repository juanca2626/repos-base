<template>
  <div class="header-menu">
    <img class="logo" src="/images/logo_limatours.png" alt="logo-lito" width="180" />
    <div class="header-menu__nav pe-3">
      <div class="header-menu__nav-item">
        <a-button type="primary" danger @click="clickLogout">
          <font-awesome-icon :icon="['fas', 'power-off']" class="me-2" />
          Cerrar sesión
        </a-button>
      </div>
      <div class="header-menu__nav-item">
        <span class="username">
          <font-awesome-icon :icon="['fas', 'user']" class="me-2" />
          {{ getUserName() }}
        </span>
      </div>
      <div class="header-menu__nav-item">
        <a-select
          v-model:value="currentLang"
          style="min-width: 60px"
          ghost
          @change="handleChangeLanguage"
        >
          <template #suffixIcon>
            <svg
              fill="none"
              height="12"
              viewBox="0 0 12 12"
              width="12"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M1.84107 3.08087H10.1589C10.9068 3.08087 11.2808 3.98605 10.7531 4.51378L6.59413 8.67271C6.26561 9.00123 5.73439 9.00123 5.40936 8.67271L1.24694 4.51378C0.719209 3.98605 1.09316 3.08087 1.84107 3.08087Z"
                fill="#EB5757"
              />
            </svg>
            <!--<font-awesome-icon icon="fa-solid fa-angle-down" size="lg" />-->
          </template>
          <a-select-option
            v-for="lang in languagesStore.getLanguages"
            :key="lang.id"
            :value="lang.value"
          >
            {{ lang.value.toUpperCase() }}
          </a-select-option>
        </a-select>
        <!-- a-select class="language-selector" value="es" size="small">
          <template #suffixIcon>
            <font-awesome-icon icon="fa-solid fa-angle-down" />
          </template>
          <a-select-option value="es">es</a-select-option>
          <a-select-option value="en">en</a-select-option>
          <a-select-option value="pt">pt</a-select-option>
        </a-select -->
      </div>
    </div>
  </div>
</template>

<script setup>
  import { onMounted, ref } from 'vue';
  import { useRouter } from 'vue-router';
  import { getUserName } from '../../utils/auth';
  import { logout } from '@/services/auth/servicesAuth.js';
  import { useLanguagesStore } from '@/stores/global';

  import { useI18n } from 'vue-i18n';
  const { locale } = useI18n({
    useScope: 'global',
  });

  const languagesStore = useLanguagesStore();

  const currentLang = ref('');

  const handleChangeLanguage = (value) => {
    locale.value = value;
    languagesStore.setCurrentLanguage(value);
  };

  const route = useRouter();

  const clickLogout = async () => {
    await logout();
    route.push('/login');
  };

  // Obtener user_code del localStorage al montar el componente
  onMounted(async () => {
    currentLang.value = languagesStore.currentLanguage;
    // socketsStore.connect();
  });
</script>
