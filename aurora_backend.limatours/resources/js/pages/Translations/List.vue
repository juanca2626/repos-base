<template>
    <div class="row">
        <div class="col-12">
            <div class="row">
                <div class="col-3">
                    <v-select :options="languages"
                              :placeholder="this.$t('languages.language_name')"
                              @input="fetchData"
                              autocomplete="true"
                              label="text"
                              v-model="languageSelected">
                    </v-select>
                </div>
                <div class="col-3">
                    <v-select :options="modules"
                              @input="fetchData"
                              autocomplete="true"
                              label="text"
                              placeholder="Módulo"
                              v-model="modulesSelected">
                    </v-select>
                </div>
            </div>
        </div>
        <div class="col-12">
            <table-client :columns="table.columns" :data="translations" :options="tableOptions" class="text-center"
                          ref="table">
                <div class="table-actions" slot="actions" slot-scope="props">
                    <menu-edit :id="props.row.id" :name="props.row.slug + ' ' + props.row.id" :options="menuOptions"
                               @remove="remove(props.row.id)"/>
                </div>
                <div class="table-actions" slot="language" slot-scope="props">
                    {{props.row.language.name}}
                </div>
                <div class="table-type" slot="module" slot-scope="props">
                    {{ props.row.slug.split('.')[0] }}
                </div>
                <div class="table-type" slot="slug" slot-scope="props">
                    {{ props.row.slug.split('.').slice(1).join('.') }}
                </div>
            </table-client>
        </div>
    </div>
</template>

<script>
  import { API } from './../../api'
  import TableClient from '../../components/TableClient'
  import MenuEdit from './../../components/MenuEdit'
  import vSelect from 'vue-select'
  import 'vue-select/dist/vue-select.css'

  export default {
    components: {
      'table-client': TableClient,
      'menu-edit': MenuEdit,
      vSelect,
    },
    data: () => {
      return {
        translations: [],
        languages: [],
        languageSelected: '',
        modules: [],
        modulesSelected: '',
        table: {
          columns: ['id', 'language', 'module', 'slug', 'value', 'actions'],
        }
      }
    },
    mounted () {
      API.get('language/selectbox').then((result) => {
        let data = result.data.data
        data.unshift({ text: 'Todos los idiomas', value: 'all' })

        this.languages = data
      })
      this.fetchData()
    },
    computed: {
      tableOptions: function () {
        return {
          headings: {
            id: 'ID',
            language: this.$i18n.t('translations.languages'),
            module: 'Module',
            slug: 'Llave',
            value: this.$i18n.t('translations.translate'),
            actions: this.$i18n.t('global.table.actions')
          },
          sortable: [],
          filterable: false,
        }
      },
      menuOptions: function () {

        let options = []

        if (this.$can('update', 'translations')) {
          options.push({
            type: 'edit',
            text: '',
            link: 'translations/edit/',
            icon: 'dot-circle',
            callback: '',
            type_action: 'link'
          })
        }
        if (this.$can('delete', 'translations')) {
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
      }
    },
    methods: {
      fetchData: function () {
        API.get('translations', {
          params: {
            language: this.languageSelected.value,
            modules: this.modulesSelected
          }
        }).then((result) => {
          if (result.data.success === true) {
            this.translations = result.data.data

            result.data.modules.unshift({ text: 'Todos los modulos', value: 'all' })
            this.modules = result.data.modules.sort()
          } else {
            this.$notify({
              group: 'main',
              type: 'error',
              title: 'Fetch Error',
              text: result.data.message
            })
          }
        })
      },
    }
  }
</script>

<style lang="stylus">
    .table-actions
        display flex
</style>
