<template>
    <div class="card">
        <div class="card-header">
            <font-awesome-icon :icon="['fas', 'bars']" class="mr-1" />
            Políticas de cancelación - General
            <div class="card-header-actions">
                <router-link :to="{ name: 'PackageCancellationPoliciesFormAdd' }" v-if="showAdd">
                    <font-awesome-icon :icon="['fas', 'plus']" class="nav-icon" />
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
  import { API } from './../../api'

  export default {
    data () {
      return {
        service_id: '',
        name_service: ''
      }
    },
    created: function () {

    },
    computed: {
      showAdd () {
        return this.$route.meta.breadcrumb === 'Lista' && this.$ability.can('create', 'packagecancellationpolicies')
      },
      showEdit () {
        return this.$route.meta.breadcrumb !== 'Lista' && this.$route.meta.breadcrumb == 'Editar' && this.$ability.can('update', 'packagecancellationpolicies')
      },
      showManage () {
        return this.$route.meta.breadcrumb === 'Editar' && this.$ability.can('update', 'packagecancellationpolicies')
      }
    },
    mounted: function () {
    },
    methods: {

    }
  }
</script>

<style lang="stylus">

</style>
