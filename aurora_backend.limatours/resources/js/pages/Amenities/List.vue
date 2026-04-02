<template>
    <table-client :columns="table.columns" :data="amenities" :loading="loading" :options="tableOptions" id="dataTable"
                  theme="bootstrap4">
        <div class="table-actions" slot="actions" slot-scope="props">
            <menu-edit :id="props.row.id" :name="props.row.translations[0].value" :options="menuOptions"
                       @remove="remove(props.row.id)"/>
        </div>
        <div class="table-translations" slot="translations" slot-scope="props">
            {{ props.row.translations[0].value }}
        </div>
        <div class="table-images" slot="image" slot-scope="props">
            <span v-if="props.row.galeries.length > 0">
                <img :src="'/images/galeries/'+props.row.galeries[0].url+ '?' + Date.now()" alt="" height="45"
                     width="45">
            </span>
        </div>
        <div class="table-state" slot="status" slot-scope="props" style="font-size: 0.9em">
            <b-form-checkbox
                    :checked="checkboxChecked(props.row.status)"
                    :id="'checkbox_'+props.row.id"
                    :name="'checkbox_'+props.row.id"
                    @change="changeStatus(props.row.id,props.row.status)"
                    switch>
            </b-form-checkbox>
        </div>
        <div class="table-loading text-center" slot="loading">
            <img alt="loading" height="51px" src="/images/loading.svg"/>
        </div>
    </table-client>
</template>

<script>
  import { API } from './../../api'
  import TableClient from './../../components/TableClient'
  import MenuEdit from './../../components/MenuEdit'
  import BFormCheckbox from 'bootstrap-vue/es/components/form-checkbox/form-checkbox'

  export default {
    components: {
      'table-client': TableClient,
      'menu-edit': MenuEdit,
      BFormCheckbox
    },
    data: () => {
      return {
        loading: false,
        amenities: [],
        table: {
          columns: ['actions','id', 'translations', 'image', 'status']
        }
      }
    },
    mounted () {
      this.fetchData(this.$i18n.locale)
    },
    computed: {
      menuOptions: function () {

        let options = []

        if (this.$can('update', 'amenities')) {
          options.push({
            type: 'edit',
            text: '',
            link: 'amenities/edit/',
            icon: 'dot-circle',
            callback: '',
            type_action: 'link'
          })
        }
        if (this.$can('delete', 'amenities')) {
          options.push({
            type: 'delete',
            text: '',
            link: '',
            icon: 'trash',
            type_action: 'button',
            callback_delete: 'remove'
          })
        }
        return options
      },
      tableOptions: function () {
        return {
          headings: {
            id: 'ID',
            translations: this.$i18n.t('amenities.amenity_name'),
            image: this.$i18n.t('amenities.amenity_image'),
            status: this.$i18n.t('amenities.status.title'),
            actions: this.$i18n.t('global.table.actions')
          },
          sortable: ['id'],
          filterable: ['id','translations']
        }
      }
    },
    created () {
      this.$parent.$parent.$on('langChange', (payload) => {
        this.fetchData(payload.lang)
      })
    },
    methods: {
      checkboxChecked: function (amenity_status) {
        if (amenity_status) {
          return 'true'
        } else {
          return 'false'
        }
      },
      changeStatus: function (amenity_id, status) {
        API({
          method: 'put',
          url: 'amenities/update/' + amenity_id + '/state',
          data: { status: status }
        })
          .then((result) => {
            if (result.data.success === false) {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.rooms'),
                text: this.$t('amenities.error.messages.information_error')
              })
            }
          })
      },
      fetchData: function (lang) {
        this.loading = true
        API.get('amenities?lang=' + lang).then((result) => {
          this.loading = false
          if (result.data.success === true) {
            this.amenities = result.data.data
          } else {
            this.$notify({
              group: 'main',
              type: 'error',
              title: 'Fetch Error',
              text: result.data.message
            })
          }
        })
          .catch(() => {
            this.$notify({
              group: 'main',
              type: 'error',
              title: 'Fetch Error',
              text: 'Cannot load data'
            })
          })
      },
      remove (id) {
        API({
          method: 'DELETE',
          url: 'amenities/' + id
        })
          .then((result) => {
            if (result.data.success === true) {
              this.fetchData(localStorage.getItem('lang'))
            } else {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.amenities'),
                text: result.data.message
              })
            }
          })
      },
    }
  }
</script>

<style lang="stylus"></style>


