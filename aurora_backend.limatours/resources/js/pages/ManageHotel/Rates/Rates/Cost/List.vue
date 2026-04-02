<template>
    <div class="container">
        <div class="row cost-table-container">
            <router-link :to="'/manage_hotels/rates/rates/cost/add'" class="nav-link" v-if="$can('create', 'rates')">
                <button class="btn btn-primary" type="button">+ {{$t('global.buttons.add')}}</button>
            </router-link>
            <table-client
                    :columns="table.columns"
                    :data="meals"
                    :loading="loading"
                    :options="tableOptions"
                    id="dataTable"
                    theme="bootstrap4">
                <div class="table-actions" slot="actions" slot-scope="props">
                    <menu-edit :id="props.row.id" :options="options"/>
                </div>
                <div class="table-meal" slot="meal" slot-scope="props">
                    {{ props.row.meal.translations[0].value }}
                </div>
                <div class="table-meal" slot="room" slot-scope="props">
                    {{ props.row.room.translations[0].value }}
                </div>
                <div class="table-loading text-center" slot="loading">
                    <img alt="loading" height="51px" src="/images/loading.svg"/>
                </div>
            </table-client>
        </div>
    </div>
</template>

<script>
  import { API } from './../../../../../api'
  import TableClient from './../../../../../components/TableClient'
  import MenuEdit from './../../../../../components/MenuEdit'

  export default {
    components: {
      'table-client': TableClient,
      'menu-edit': MenuEdit
    },
    data: () => {
      return {
        loading: false,
        meals: [],
        table: {
          columns: ['id', 'name', 'room', 'meal', 'status', 'actions']
        },
        options: [
          {
            type: 'edit',
            link: 'rates/edit/',
            icon: 'dot-circle'
          },
          {
            type: 'delete',
            link: 'rates/edit/',
            icon: 'times'
          }
        ]
      }
    },
    mounted () {
      this.fetchData(this.$i18n.locale)
    },
    computed: {
      tableOptions: function () {
        return {
          headings: {
            id: 'ID',
            name: this.$i18n.t('name'),
            room: this.$i18n.t('room'),
            meal: this.$i18n.t('meal'),
            status: this.$i18n.t('status'),
            actions: this.$i18n.t('table.actions')
          },
          sortable: ['id', 'name', 'room', 'meal'],
          filterable: ['id', 'name', 'room', 'meal']
        }
      }
    },
    created () {
      this.$parent.$parent.$on('langChange', (payload) => {
        this.fetchData(payload.lang)
      })
    },
    methods: {
      fetchData: function (lang) {
        this.loading = true
        API.get('rates/cost?lang=' + lang).then((result) => {
          this.loading = false
          if (result.data.success === true) {
            this.meals = result.data.data
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

<style lang="stylus">
    .cost-table-container
        position relative

        .nav-link
            position absolute
            top 0
            right 0
            z-index 10
</style>