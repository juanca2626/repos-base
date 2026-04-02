<template>
    <div class="card">
        <div class="card-header">
            <font-awesome-icon :icon="['fas', 'bars']" class="mr-1" />
            {{ $t('global.modules.photos') }} {{name_service}}
            <div class="card-header-actions">
                <router-link :to="{ name: 'PhotosAdd' }" v-if="showAdd">
                    <font-awesome-icon :icon="['fas', 'plus']" class="nav-icon" />
                    {{ $t('global.buttons.add') }}
                </router-link>
            </div>
            <div class="card-header-actions mr-2">
                <router-link :to="{ name: 'PhotosManageFilter' }" v-if="showAdd">
                    <font-awesome-icon :icon="['fas', 'filter']" class="nav-icon" />
                    Administrador de filtros
                </router-link>
            </div>
            <div class="card-header-actions">
                <button class="btn btn-danger" @click="goEdit()" v-if="showEdit">
                    <font-awesome-icon :icon="['fas', 'edit']" class="nav-icon" />
                    {{ $t('global.buttons.edit') }}
                </button>
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
      console.log(this.$route.params)
      this.name_service = ''
      this.service_id = (this.$route.params.service_id === undefined) ? this.$route.params.id : this.$route.params.service_id
      this.$root.$on('updateTitleService', (payload) => {
        if(this.name_service === ''){
          console.log(payload)
          this.getNameService(payload.service_id)
        }
      })

      this.$root.$on('updateTitleServiceList', () => {
        this.name_service = ''
      })


    },
    computed: {
      showAdd () {
        console.log(this.$route.meta.breadcrumb)
        return this.$route.meta.breadcrumb === 'Lista' && this.$ability.can('create', 'photos')
      },
      showEdit () {
        return this.$route.meta.breadcrumb !== 'Lista' && this.$route.meta.breadcrumb == 'Editar' && this.$ability.can('update', 'photos')
      },
      showManage () {
        return this.$route.meta.breadcrumb === 'Editar' && this.$ability.can('update', 'photos')
      }
    },
    mounted: function () {
      this.service_id = (this.$route.params.service_id === undefined) ? this.$route.params.id : this.$route.params.service_id
      this.getNameService(this.service_id)
    },
    methods: {
      goEdit () {
        this.service_id = this.$route.params.service_id
        this.getNameService(this.service_id)
        this.$router.push('/services_new/edit/' + this.service_id)
      },
      goAdmin () {
        this.service_id = this.$route.params.id
        this.getNameService(this.service_id)
        this.$router.push('/services_new/' + this.service_id + '/manage_service')
      },
      getNameService(service_id){
        // API.get('service/' + service_id + '/configuration')
        //   .then((result) => {
        //     this.name_service = '[' + result.data.data.aurora_code + ']' + ' - ' + result.data.data.name
        //     console.log(result)
        //   }).catch(() => {
        //   this.name_service = ''
        // })
      }
    }
  }
</script>

<style lang="stylus">

</style>
