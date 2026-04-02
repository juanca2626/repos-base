<template>
  <div class="header-menu">
    <img class="logo" src="/images/logo_limatours.png" alt="logo-lito" width="180" />
    <div class="header-menu__nav">
      <div class="header-menu__nav-item">
        <a-button class="power" shape="circle" :size="'12px'" @click="clickLogout">
          <template #icon> <poweroff-outlined /> </template>
        </a-button>
      </div>
      <div class="header-menu__nav-item">
        <a-badge :count="1">
          <a-avatar style="background-color: #1890ff">A</a-avatar>
        </a-badge>
        <span class="username">ADMIN</span>
      </div>
      <div class="header-menu__nav-item">
        <a-select class="langselector" value="ES" size="small">
          <template #suffixIcon>
            <font-awesome-icon icon="fa-solid fa-angle-down" size="lg" />
          </template>
          <a-select-option value="ES">ES</a-select-option>
          <a-select-option value="EN">EN</a-select-option>
        </a-select>
      </div>
    </div>
  </div>
</template>
<script setup>
  import { logout } from '@service/auth/servicesAuth';
  import { PoweroffOutlined } from '@ant-design/icons-vue';
  import { useRouter } from 'vue-router';
  const route = useRouter();

  const clickLogout = async () => {
    await logout();
    route.push('/login');
  };
</script>
<style lang="sass">
  .header-menu
    display: flex
    width: 100%
    justify-content: space-between
    &__nav
      display: flex
      flex-direction: row-reverse
      text-align: right
      &-item
        padding-left: 25px
        color: #FFF
        .username
          font-size: 14px
          padding-left: 10px
    .langselector
      color: var(--color-primary)
      .ant-select-selector
        border-radius: 4px
      span
        font-weight: bold
      .ant-select-arrow
        color: var(--color-primary)

    .power
      .anticon
        color: var(--color-black-3)
</style>
