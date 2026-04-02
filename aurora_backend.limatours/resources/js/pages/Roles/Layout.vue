<template>
    <div class="card">
        <div class="card-header">
            <font-awesome-icon :icon="['fas', 'user-tie']" class="mr-1"/>
            {{$t('roles.title')}}
            <div class="card-header-actions">
                <router-link :to="{ name: 'RolesAdd' }" v-if="showAdd">
                    <font-awesome-icon :icon="['fas', 'plus']" class="nav-icon"/>
                    {{$t('global.buttons.add')}}
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
    computed: {
      showAdd () {
        return this.$route.meta.breadcrumb === 'Lista' && this.$ability.can('create', 'roles')
      }
    }
  }
</script>

<style lang="stylus">

</style>


