<template>
    <div class="card">
        <div class="card-header">
            <font-awesome-icon :icon="['fas', 'bars']" class="mr-1" />
            Highlights
            <div class="card-header-actions">
                <router-link :to="{ name: 'PackageHighLightsFormAdd' }" v-if="showAdd">
                    <font-awesome-icon :icon="['fas', 'plus']" class="nav-icon" />
                    {{ $t('global.buttons.add') }}
                </router-link>
            </div>
            <div class="card-header-actions mr-2">
                <router-link :to="{ name: 'FormAssignPackagesHighlights' }" v-if="showAdd">
                    <i class="fas fa-list-ul"></i> Asignar HighLights
                </router-link>
            </div>
<!--            <div class="card-header-actions">-->
<!--                <button class="btn btn-danger" @click="goEdit()" v-if="showEdit">-->
<!--                    <font-awesome-icon :icon="['fas', 'edit']" class="nav-icon" />-->
<!--                    {{ $t('global.buttons.edit') }}-->
<!--                </button>-->
<!--            </div>-->
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
        console.log(this.$route.meta.breadcrumb)
        return this.$route.meta.breadcrumb === 'Lista' && this.$ability.can('create', 'highlights')
      },
      showEdit () {
        return this.$route.meta.breadcrumb !== 'Lista' && this.$route.meta.breadcrumb == 'Editar' && this.$ability.can('update', 'highlights')
      },
      showManage () {
        return this.$route.meta.breadcrumb === 'Editar' && this.$ability.can('update', 'highlights')
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
