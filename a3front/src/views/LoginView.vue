<template>
  <div
    style="position: absolute; top: 15px; right: 15px"
    v-if="languagesStore.getLanguages.length > 0"
  >
    <a-select
      v-model:value="currentLang"
      style="min-width: 60px"
      ghost
      size="large"
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
  </div>
  <div class="login" v-if="languagesStore.getLanguages.length > 0">
    <a-row style="height: 100vh" type="flex" justify="space-around" align="middle">
      <a-col :sm="{ span: 10 }">
        <div class="login__card">
          <a-row>
            <img
              class="aurora-logo"
              src="https://aurora.limatours.com.pe/images/logo/logo-aurora.png"
              alt=""
              srcset=""
            />
          </a-row>
          <a-row>
            <a-alert
              :message="
                t(`global.message.${message.content.toLowerCase()}`, { time: message.time })
              "
              :type="message.type"
              v-if="message.type != '' && message.content != ''"
              style="margin-bottom: 1rem; width: 100%; max-width: 100%"
            />
          </a-row>
          <a-form
            ref="formRef"
            :model="formState"
            name="normal_login"
            class="login-form"
            @finish="submit"
            @finishFailed="onFinishFailed"
          >
            <a-form-item name="username" :rules="usernameRules">
              <a-input
                v-model:value="formState.username"
                :readonly="disabled"
                :placeholder="`${t('global.label.username')}`"
              >
                <template #prefix>
                  <font-awesome-icon :icon="['fas', 'user']" class="text-dark-gray" />
                </template>
              </a-input>
            </a-form-item>

            <a-form-item name="password" :rules="passwordRules">
              <a-input-password
                v-model:value="formState.password"
                :readonly="disabled"
                :placeholder="`${t('global.label.password')}`"
              >
                <template #prefix>
                  <font-awesome-icon :icon="['fas', 'lock']" class="text-dark-gray" />
                </template>
              </a-input-password>
            </a-form-item>

            <a-form-item>
              <a-button
                :loading="disabled"
                type="primary"
                html-type="submit"
                class="login-form-button text-uppercase"
              >
                {{ t('global.button.login') }}
              </a-button>
            </a-form-item>
          </a-form>
          <p>
            <a v-bind:href="linkRecover()" class="text-danger">{{
              t('global.label.recover_password')
            }}</a>
          </p>
          <a-row>
            <img class="logo" src="/images/logo_limatours.png" alt="logo-lito" />
          </a-row>
        </div>
      </a-col>
    </a-row>
  </div>
</template>

<script setup>
  import { ref, reactive, onMounted, computed, watch } from 'vue';
  import { login_a2 } from '@service/auth/servicesAuth';
  import {
    getAccessToken,
    isAuthenticated,
    isAuthenticatedCognito,
    removeCookiesCross,
  } from '@/utils/auth';
  import router from '@/router';
  import { useLanguagesStore } from '@/stores/global';
  import { useSocketsStore } from '@/stores/global';

  import { useI18n } from 'vue-i18n';
  const { t, locale } = useI18n({
    useScope: 'global',
  });

  watch(locale, () => {
    clearErrors();
  });

  const formRef = ref(null);

  const clearForm = () => {
    message.type = '';
    message.content = '';
    message.time = '';
  };

  const clearErrors = () => {
    if (formRef) {
      clearForm();
      formRef.value?.clearValidate();
    }
  };

  const usernameRules = computed(() => [
    { required: true, message: t('global.message.username_required') },
  ]);

  const passwordRules = computed(() => [
    { required: true, message: t('global.message.password_required') },
  ]);

  const socketsStore = useSocketsStore();
  const languagesStore = useLanguagesStore();

  const currentLang = ref('');

  const handleChangeLanguage = (value) => {
    locale.value = value;
    languagesStore.setCurrentLanguage(value);
  };

  const message = reactive({
    type: '',
    content: '',
    time: '',
  });

  const disabled = ref(false);

  const authState = reactive({
    token: null,
  });

  onMounted(async () => {
    currentLang.value = languagesStore.currentLanguage;
    socketsStore.disconnect();

    if (isAuthenticated() || isAuthenticatedCognito()) {
      authState.token = getAccessToken();
    } else {
      removeCookiesCross();
    }
  });

  const submit = async (values) => {
    disabled.value = true;
    clearForm();

    const responseLogin = await login_a2(values);

    if (responseLogin.success) {
      router.push({ name: responseLogin.redirect });
    } else {
      disabled.value = false;
      message.type = 'error';
      message.content = responseLogin.message;
      message.time = responseLogin.time ?? '';
    }
  };

  const linkRecover = () => {
    return window.url_back_a2 + '#reset-password';
  };

  const formState = reactive({
    username: '',
    password: '',
    remember: true,
  });

  const onFinishFailed = (errorInfo) => {
    console.log('Errors:', errorInfo);
    clearForm();
  };
</script>

<style lang="sass" scoped>
  .login
    background: url('https://aurora.limatours.com.pe/images/bg-login.jpg')
    background-size: cover
    .ant-card-body
      padding: 15px 32px 44px
    .ant-form-item
      margin-bottom: 10px
    .ant-input-affix-wrapper
      padding: 6.5px 11px
    .aurora-logo
      width: 100%
      max-width: 236px
      margin: 0 auto 25px
    .logo
      width: 105px
      margin: 10px auto 0
    button
      width: 100%
      height: 52px
      background-color: #EB5757
      border-color: #EB5757
      &:hover
        background-color: #C63838
        border-color: #C63838
        box-shadow: inherit
    &__card
      background: rgba(0,0,0,0.4)
      border: none
      border-radius: 12px
      margin: 0 auto
      width: 300px
      padding: 2rem
</style>
