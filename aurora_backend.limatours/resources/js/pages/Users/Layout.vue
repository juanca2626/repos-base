<template>
    <div class="card">
        <div class="card-header">
            <font-awesome-icon :icon="['fas', 'user-friends']" class="mr-1"/>
            {{ $t('users.title') }}
            <div class="card-header-actions">
                <router-link :to="{ name: 'UsersAdd' }" v-if="showAdd">
                    <font-awesome-icon :icon="['fas', 'plus']" class="nav-icon"/>
                    {{ $t('global.buttons.add') }}
                </router-link>
            </div>
        </div>
        <div class="card-body">
            <router-view></router-view>
        </div>
    </div>
</template>

<script>
  export default {
    data () {
      return {}
    },
    computed: {
      showAdd () {
        return this.$route.meta.breadcrumb === 'Lista' && this.$ability.can('create', 'users')
      }
    },
    mounted: function () {
      this.$i18n.locale = localStorage.getItem('lang')
    }
  }
</script>

<style lang="stylus">

</style>
