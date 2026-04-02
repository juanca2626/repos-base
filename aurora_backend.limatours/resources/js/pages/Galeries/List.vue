<template>
    <table-client :columns="table.columns" :data="galeries" :loading="loading" :options="tableOptions" id="dataTable"
                  theme="bootstrap4">
        <div class="table-actions" slot="actions" slot-scope="props">
          <menu-edit :id="props.row.id" :name="props.row.slug +' '+ props.row.id" :options="menuOptions"
                     @remove="remove(props.row.id)"/>
        </div>
        <div class="table-image" slot="image" slot-scope="props">
            <img :src="'/images/galeries/'+props.row.url" alt="" height="45" v-if="!validURL(props.row.url)" width="45">
            <img :src="props.row.url" alt="" v-else>
        </div>
        <div class="table-state" slot="state" slot-scope="props">
            <img src="/images/loading.svg" v-if="loading" width="20px"/>
            <b-form-checkbox
                    :checked="checkboxChecked(props.row.state)"
                    :id="'checkbox_'+props.row.id"
                    :name="'checkbox_'+props.row.id"
                    @change="changeState(props.row.id, props.row.state)"
                    switch>
            </b-form-checkbox>
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
        galeries: [],
        table: {
          columns: ['actions', 'id', 'type', 'image', 'position','state']
        }
      }
    },
    mounted () {
      this.fetchData(this.$i18n.locale)
    },
    computed: {
      menuOptions: function () {

        let options = []

        if (this.$can('update', 'galeries')) {
          options.push({
            type: 'edit',
            text: '',
            link: 'galeries/edit/',
            icon: 'dot-circle',
            callback: '',
            type_action: 'link'
          })
        }
        if (this.$can('delete', 'galeries')) {
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
            type: this.$i18n.t('galeries.galery_type'),
            image: this.$i18n.t('galeries.galery_image'),
            state: 'Status',
            position: this.$i18n.t('galeries.galery_position'),
            actions: this.$i18n.t('global.table.actions')
          },
          sortable: ['id', 'type', 'position'],
          filterable: ['id', 'type', 'position']
        }
      }
    },
    created () {
      this.$parent.$parent.$on('langChange', (payload) => {
        this.fetchData(payload.lang)
      })
    },
    methods: {
      checkboxChecked: function (image_state) {

        if (image_state) {
          return 'true'
        } else {
          return 'false'
        }
      },
      changeState: function (gallery_id, state) {
        API({
          method: 'put',
          url: 'galeries/update/' + gallery_id + '/status',
          data: { state: state }
        })
          .then((result) => {
            if (result.data.success === false) {
              this.$notify({
                group: 'main',
                type: 'error',
                title: this.$t('global.modules.galeries'),
                text: this.$t('galeries.error.messages.information_error')
              })
            }
          })
      },
      validURL: function (url, obligatory, ftp) {
        // Si no se especifica el paramatro "obligatory", interpretamos

        // que no es obligatorio

        if (obligatory == undefined)

          obligatory = 0

        // Si no se especifica el parametro "ftp", interpretamos que la

        // direccion no puede ser una direccion a un servidor ftp

        if (ftp == undefined)

          ftp = 0

        if (url == '' && obligatory == 0)

          return true

        if (ftp)

          var pattern = /^(http|https|ftp)\:\/\/[a-z0-9\.-]+\.[a-z]{2,4}/gi

        else

          var pattern = /^(http|https)\:\/\/[a-z0-9\.-]+\.[a-z]{2,4}/gi

        if (url.match(pattern))

          return true

        else

          return false
      },
      fetchData: function (lang) {
        this.loading = true
        API.get('galeries').then((result) => {
          this.loading = false
          if (result.data.success === true) {
            this.galeries = result.data.data
            console.log(this.galeries)
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
      }
    }
  }
</script>

<style lang="stylus"></style>


