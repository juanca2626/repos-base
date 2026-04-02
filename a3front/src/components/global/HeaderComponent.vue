<template>
  <div class="header">
    <img alt="logo-lito" class="header-logo" height="61" src="/images/logo_limatours.png" />
    <div class="header-items" style="width: 100%">
      <MainMenu />
    </div>
    <div class="header__nav">
      <div v-if="getUserType() == 3" class="header__nav-item">
        <a-select
          v-model:value="clientSelected"
          style="width: 250px"
          ghost
          show-search
          :placeholder="t('quotes.search_client')"
          :show-arrow="false"
          :filter-option="false"
          :not-found-content="null"
          :options="clientsStore.getClients"
          :loading="clientsStore.isLoading"
          @search="handleSearchClients"
          @change="handleChangeClient"
        />
      </div>
      <div class="header__nav-item">
        <a-select
          v-model:value="locale"
          style="min-width: 110px"
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
            {{ lang.label }}
          </a-select-option>
        </a-select>
      </div>
      <div class="header__nav-item">
        <header-notifications />
      </div>
      <div class="header__nav-item">
        <svg
          class="d-flex"
          fill="none"
          height="18px"
          width="22px"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            d="M3 1h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H3c-1.1 0-2-.9-2-2V3c0-1.1.9-2 2-2Z"
            stroke="#EB5757"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
          />
          <path
            d="m21 3-10 7L1 3"
            stroke="#EB5757"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
          />
        </svg>
      </div>
      <div class="header__nav-item pageReport">
        <a :href="'/quotes/reports'" class="d-flex">
          <svg
            width="22"
            height="18"
            viewBox="0 0 22 18"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              d="M21 14.8333C21 15.2754 20.7893 15.6993 20.4142 16.0118C20.0391 16.3244 19.5304 16.5 19 16.5H3C2.46957 16.5 1.96086 16.3244 1.58579 16.0118C1.21071 15.6993 1 15.2754 1 14.8333V3.16667C1 2.72464 1.21071 2.30072 1.58579 1.98816C1.96086 1.67559 2.46957 1.5 3 1.5H8L10 4H19C19.5304 4 20.0391 4.17559 20.4142 4.48816C20.7893 4.80072 21 5.22464 21 5.66667V14.8333Z"
              stroke="#EB5757"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
          </svg>
        </a>
      </div>
      <div class="header__nav-item-profile">
        <a-popover placement="bottomRight" style="margin-top: -10px">
          <template #content>
            <a-button size="small" type="link">
              {{ t('global.label.hi') }}, {{ getUserName() }}
            </a-button>
            <br />
            <a-button size="small" type="link">
              <a :href="url + 'account'">
                <svg
                  fill="none"
                  height="24"
                  viewBox="0 0 24 24"
                  width="24"
                  xmlns="http://www.w3.org/2000/svg"
                >
                  <path
                    d="M20 21V19C20 17.9391 19.5786 16.9217 18.8284 16.1716C18.0783 15.4214 17.0609 15 16 15H8C6.93913 15 5.92172 15.4214 5.17157 16.1716C4.42143 16.9217 4 17.9391 4 19V21"
                    stroke="#EB5757"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                  />
                  <path
                    d="M12 11C14.2091 11 16 9.20914 16 7C16 4.79086 14.2091 3 12 3C9.79086 3 8 4.79086 8 7C8 9.20914 9.79086 11 12 11Z"
                    stroke="#EB5757"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                  />
                </svg>
                {{ t('global.label.profile') }}
              </a>
            </a-button>
            <br />
            <a-button size="small" type="link" @click="clickLogout">
              <svg
                fill="none"
                height="24"
                viewBox="0 0 24 24"
                width="24"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M9 21H5C4.46957 21 3.96086 20.7893 3.58579 20.4142C3.21071 20.0391 3 19.5304 3 19V5C3 4.46957 3.21071 3.96086 3.58579 3.58579C3.96086 3.21071 4.46957 3 5 3H9"
                  stroke="#EB5757"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                />
                <path
                  d="M16 17L21 12L16 7"
                  stroke="#EB5757"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                />
                <path
                  d="M21 12H9"
                  stroke="#EB5757"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                />
              </svg>
              {{ t('global.label.logout') }}
            </a-button>
          </template>
          <files-popover-avatar
            :style="`width: 40px; height: 40px; line-height: 40px; font-size: 20px; margin-top: -10px;`"
            class="cursor-pointer"
            :size="40"
            :photo="baseURLPhoto()"
            :active="true"
          />
        </a-popover>
      </div>
    </div>
  </div>
</template>

<script setup>
  import { ref } from 'vue';
  import { logout } from '@service/auth/servicesAuth';
  import { useRouter } from 'vue-router';
  import MainMenu from './MainMenu.vue';
  import HeaderNotifications from '@/components/global/HeaderNotifications.vue';
  import FilesPopoverAvatar from '@/components/files/edit/FilesPopoverAvatar.vue';
  import { useLanguagesStore } from '@/stores/global';
  import {
    getUrlAuroraFront,
    getUserName,
    getUserType,
    setUserClientId,
    getUserClientId,
  } from '@/utils/auth';
  import { useI18n } from 'vue-i18n';
  import { useClientsStore } from '@store/files/clients-store';
  import { debounce } from 'lodash-es';
  import { onMounted } from 'vue';

  defineProps({
    data: {
      type: Object,
      default: () => ({}),
    },
  });

  const languagesStore = useLanguagesStore();
  const { locale, t } = useI18n({
    useScope: 'global',
  });

  const route = useRouter();
  const url = ref(getUrlAuroraFront());

  const clickLogout = async () => {
    await logout();
    await route.push('/login');
  };

  const handleChangeLanguage = (value) => {
    locale.value = value;
    languagesStore.setCurrentLanguage(value);
  };

  const baseURLPhoto = () => {
    const photo = localStorage.getItem('photo_user_a3');
    return photo ? `${window.url_front_a2}images/users/${photo}` : false;
  };

  /* Client Selector Logic */
  const clientsStore = useClientsStore();
  const clientSelected = ref('');

  const handleSearchClients = debounce((value) => {
    if (value && value.length >= 2) {
      clientsStore.filterAll(value);
    }
  }, 500);

  const handleChangeClient = (value) => {
    if (value) {
      const client_code = clientsStore.getClients.find((client) => client.value === value).code;
      localStorage.setItem('client_code', client_code);
      setUserClientId(value);
      window.location.reload();
    }
  };

  onMounted(() => {
    const clientID = String(getUserClientId() ?? '');
    clientSelected.value = clientID;
    clientsStore.filterAll(clientID);
  });
</script>
