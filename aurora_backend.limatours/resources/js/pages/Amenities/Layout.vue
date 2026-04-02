<template>
    <div class="card">
        <div class="card-header">
            <font-awesome-icon :icon="['fas', 'concierge-bell']" class="mr-1"/>
            {{ $t('amenities.title') }}
            <div class="card-header-actions">
                <router-link :to="{ name: 'AmenitiesAdd' }" v-if="showAdd">
                    <font-awesome-icon :icon="['fas', 'plus']" class="nav-icon"/>
                    {{ $t('global.buttons.add') }}
                </router-link>
                <input type="file" v-if="!loading" name="file_import" id="file_import" class="d-none"
                  v-on:change="onChangeFileUpload" ref="file" />
                <label for="file_import" class="btn btn-success">Importar Excel</label>
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
        loading: false
      }
    },
    computed: {
      showAdd () {
        return this.$route.meta.breadcrumb === 'Lista' && this.$ability.can('create', 'amenities')
      }
    },
    mounted: function () {
      this.$i18n.locale = localStorage.getItem('lang')
    },
    methods: {
      onChangeFileUpload: function () {
        let vm = this
        this.file = this.$refs.file.files[0]

        setTimeout(() => {
            vm.importAmenities()
        }, 10)
      },
      importAmenities: function () {

        let formData = new FormData()
        formData.append('file', this.file)

        this.loading = true

        API({
          method: 'POST',
          url: 'amenities/import',
          data: formData,
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        })
          .then((result) => {
            this.file = ''
            this.loading = false
            // this.$router.push('/amenities/list')
          })
          .catch((error) => {
            this.file = ''
            this.loading = false
            console.log(error)
          })
      },
    }
  }
</script>

<style lang="stylus">

</style>


